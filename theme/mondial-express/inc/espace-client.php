<?php
/**
 * Espace client : inscription, connexion, dossiers et envoi de documents.
 *
 * @package MondialExpress
 */

defined( 'ABSPATH' ) || exit;

/**
 * Rôle « Client » (lecture seule).
 */
add_action(
	'after_switch_theme',
	function () {
		add_role( 'me_client', __( 'Client', 'mondial-express' ), array( 'read' => true, 'upload_files' => false ) );
		flush_rewrite_rules();
	}
);
add_action(
	'init',
	function () {
		if ( ! get_role( 'me_client' ) ) {
			add_role( 'me_client', __( 'Client', 'mondial-express' ), array( 'read' => true ) );
		}
	}
);

/**
 * Les clients restent sur le site : pas de barre d'administration ni d'accès à wp-admin.
 */
add_filter( 'show_admin_bar', fn( $show ) => current_user_can( 'edit_posts' ) ? $show : false );
add_action(
	'admin_init',
	function () {
		if ( wp_doing_ajax() || current_user_can( 'edit_posts' ) || ( isset( $_SERVER['SCRIPT_NAME'] ) && str_contains( sanitize_text_field( wp_unslash( $_SERVER['SCRIPT_NAME'] ) ), 'admin-post.php' ) ) ) {
			return;
		}
		wp_safe_redirect( home_url( '/mon-espace/' ) );
		exit;
	}
);
add_filter(
	'login_redirect',
	function ( $redirect, $requested, $user ) {
		if ( $user instanceof WP_User && ! $user->has_cap( 'edit_posts' ) ) {
			return home_url( '/mon-espace/' );
		}
		return $redirect;
	},
	10,
	3
);

/**
 * Crée un compte client et lui rattache ses dossiers (même téléphone).
 *
 * @return int|WP_Error Identifiant de l'utilisateur.
 */
function me_create_client( $nom, $tel, $email, $pass ) {
	$nom   = sanitize_text_field( $nom );
	$tel   = sanitize_text_field( $tel );
	$email = sanitize_email( $email );
	if ( ! $nom || ! is_email( $email ) || strlen( (string) $pass ) < 8 ) {
		return new WP_Error( 'incomplet', __( 'Nom, e-mail valide et mot de passe de 8 caractères minimum.', 'mondial-express' ) );
	}
	if ( email_exists( $email ) ) {
		return new WP_Error( 'existe', __( 'Un compte existe déjà avec cet e-mail.', 'mondial-express' ) );
	}
	$login = sanitize_user( strstr( $email, '@', true ), true );
	$base  = $login ? $login : 'client';
	$i     = 1;
	while ( username_exists( $login ) || ! $login ) {
		$login = $base . $i++;
	}
	$uid = wp_insert_user(
		array(
			'user_login'   => $login,
			'user_email'   => $email,
			'user_pass'    => $pass,
			'display_name' => $nom,
			'first_name'   => $nom,
			'role'         => 'me_client',
		)
	);
	if ( is_wp_error( $uid ) ) {
		return new WP_Error( 'erreur', $uid->get_error_message() );
	}
	update_user_meta( $uid, 'me_telephone', $tel );
	// Rattache les dossiers déjà ouverts avec ce numéro de téléphone.
	$digits = substr( preg_replace( '/\D/', '', $tel ), -8 );
	if ( strlen( $digits ) >= 8 ) {
		// Les numéros sont saisis avec ou sans espaces : on compare uniquement les chiffres.
		foreach ( get_posts( array( 'post_type' => 'me_dossier', 'numberposts' => 500, 'fields' => 'ids', 'meta_key' => '_me_telephone', 'meta_compare' => 'EXISTS' ) ) as $did ) { // phpcs:ignore WordPress.DB.SlowDBQuery
			if ( substr( preg_replace( '/\D/', '', (string) get_post_meta( $did, '_me_telephone', true ) ), -8 ) === $digits && ! get_post_meta( $did, '_me_client', true ) ) {
				update_post_meta( $did, '_me_client', $uid );
			}
		}
	}
	return $uid;
}

/**
 * Inscription d'un client.
 */
function me_handle_inscription() {
	$back = home_url( '/mon-espace/' );
	if ( ! isset( $_POST['me_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['me_nonce'] ), 'me_inscription' ) || ! empty( $_POST['site_web'] ) ) {
		wp_safe_redirect( add_query_arg( 'compte', 'erreur', $back ) );
		exit;
	}
	$nom   = sanitize_text_field( wp_unslash( $_POST['nom'] ?? '' ) );
	$tel   = sanitize_text_field( wp_unslash( $_POST['telephone'] ?? '' ) );
	$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$pass  = (string) wp_unslash( $_POST['mot_de_passe'] ?? '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$uid = me_create_client( $nom, $tel, $email, $pass );
	if ( is_wp_error( $uid ) ) {
		wp_safe_redirect( add_query_arg( 'compte', $uid->get_error_code(), $back ) );
		exit;
	}
	wp_set_current_user( $uid );
	wp_set_auth_cookie( $uid, true );
	wp_safe_redirect( add_query_arg( 'compte', 'cree', $back ) );
	exit;
}
add_action( 'admin_post_nopriv_me_inscription', 'me_handle_inscription' );

/**
 * Envoi d'un document pour un dossier (PDF, JPG, PNG, 8 Mo max).
 */
function me_handle_document() {
	$back = home_url( '/mon-espace/' );
	$did  = absint( $_POST['dossier'] ?? 0 );
	if ( ! is_user_logged_in() || ! isset( $_POST['me_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['me_nonce'] ), 'me_document_' . $did ) ) {
		wp_safe_redirect( add_query_arg( 'document', 'erreur', $back ) );
		exit;
	}
	if ( (int) get_post_meta( $did, '_me_client', true ) !== get_current_user_id() || empty( $_FILES['fichier']['name'] ) ) {
		wp_safe_redirect( add_query_arg( 'document', 'erreur', $back ) );
		exit;
	}
	if ( (int) $_FILES['fichier']['size'] > 8 * MB_IN_BYTES ) {
		wp_safe_redirect( add_query_arg( 'document', 'lourd', $back ) );
		exit;
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$mimes = array( 'pdf' => 'application/pdf', 'jpg|jpeg' => 'image/jpeg', 'png' => 'image/png' );
	add_filter( 'upload_dir', 'me_private_upload_dir' );
	$att = media_handle_upload( 'fichier', $did, array( 'post_status' => 'private' ), array( 'test_form' => false, 'mimes' => $mimes ) );
	remove_filter( 'upload_dir', 'me_private_upload_dir' );
	if ( is_wp_error( $att ) ) {
		wp_safe_redirect( add_query_arg( 'document', 'format', $back ) );
		exit;
	}
	$label = sanitize_text_field( wp_unslash( $_POST['libelle'] ?? '' ) );
	if ( $label ) {
		wp_update_post( array( 'ID' => $att, 'post_title' => $label ) );
	}
	wp_mail( me_opt( 'email' ) ? me_opt( 'email' ) : get_option( 'admin_email' ), __( 'Nouveau document client', 'mondial-express' ), get_the_title( $did ) . "\n" . admin_url( 'post.php?post=' . $did . '&action=edit' ) );
	wp_safe_redirect( add_query_arg( 'document', 'ok', $back ) );
	exit;
}
add_action( 'admin_post_me_document', 'me_handle_document' );

/**
 * Les documents clients vont dans un dossier protégé (le fichier .htaccess bloque l'accès direct sur Apache / LiteSpeed).
 */
function me_private_upload_dir( $dirs ) {
	$dirs['subdir'] = '/documents-clients';
	$dirs['path']   = $dirs['basedir'] . $dirs['subdir'];
	$dirs['url']    = $dirs['baseurl'] . $dirs['subdir'];
	if ( ! file_exists( $dirs['path'] ) ) {
		wp_mkdir_p( $dirs['path'] );
		file_put_contents( $dirs['path'] . '/.htaccess', "Require all denied\nDeny from all\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		file_put_contents( $dirs['path'] . '/index.php', '<?php // Silence.' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	}
	return $dirs;
}

/**
 * Messages de l'espace client.
 */
function me_account_notice() {
	$msgs = array(
		'compte'   => array(
			'cree'      => array( 'ok', __( 'Votre compte est créé. Bienvenue !', 'mondial-express' ) ),
			'incomplet' => array( 'err', __( 'Indiquez votre nom, un e-mail valide et un mot de passe de 8 caractères minimum.', 'mondial-express' ) ),
			'existe'    => array( 'err', __( 'Un compte existe déjà avec cet e-mail. Connectez-vous ou réinitialisez votre mot de passe.', 'mondial-express' ) ),
			'erreur'    => array( 'err', __( 'La page a expiré. Rechargez-la puis réessayez.', 'mondial-express' ) ),
		),
		'document' => array(
			'ok'     => array( 'ok', __( 'Document bien reçu. Votre conseiller va le vérifier.', 'mondial-express' ) ),
			'lourd'  => array( 'err', __( 'Le fichier dépasse 8 Mo. Réduisez sa taille puis réessayez.', 'mondial-express' ) ),
			'format' => array( 'err', __( 'Formats acceptés : PDF, JPG ou PNG.', 'mondial-express' ) ),
			'erreur' => array( 'err', __( "Le document n'a pas pu être envoyé. Réessayez.", 'mondial-express' ) ),
		),
	);
	foreach ( $msgs as $param => $list ) {
		$v = isset( $_GET[ $param ] ) ? sanitize_key( $_GET[ $param ] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		if ( $v && isset( $list[ $v ] ) ) {
			return '<div class="notice notice--' . $list[ $v ][0] . '" role="status">' . esc_html( $list[ $v ][1] ) . '</div>';
		}
	}
	return '';
}
