<?php
/**
 * Formulaires : demande de devis / billet / visa / forfait, contact, suivi de dossier.
 * Chaque demande est enregistrée dans « Demandes » et l'agence reçoit un e-mail.
 *
 * @package MondialExpress
 */

defined( 'ABSPATH' ) || exit;

/**
 * Champs spécifiques à chaque type de demande.
 */
function me_form_fields( $type ) {
	$common_trip = array(
		'depart'      => array( __( 'Départ', 'mondial-express' ), 'text', 'Conakry (CKY)' ),
		'destination' => array( __( 'Destination', 'mondial-express' ), 'text', '' ),
		'aller'       => array( __( 'Aller', 'mondial-express' ), 'date', '' ),
		'retour'      => array( __( 'Retour', 'mondial-express' ), 'date', '' ),
		'passagers'   => array( __( 'Passagers', 'mondial-express' ), 'select', array( '1 adulte', '2 adultes', '3 adultes', '4 adultes', 'Famille avec enfants', 'Groupe' ) ),
		'classe'      => array( __( 'Classe', 'mondial-express' ), 'select', array( 'Économique', 'Premium', 'Affaires' ) ),
	);
	$map = array(
		'billet'     => $common_trip,
		'visa'       => array(
			'pays'   => array( __( 'Pays demandé', 'mondial-express' ), 'select', array( 'France / Schengen', 'Canada', 'États-Unis', 'Royaume-Uni', 'Turquie', 'Émirats arabes unis', 'Maroc', 'Chine', 'Autre' ) ),
			'motif'  => array( __( 'Motif', 'mondial-express' ), 'select', array( 'Tourisme / visite familiale', 'Études', 'Affaires', 'Soins médicaux', 'Autre' ) ),
			'date'   => array( __( 'Date de voyage souhaitée', 'mondial-express' ), 'date', '' ),
		),
		'hotel'      => array(
			'destination' => array( __( 'Ville', 'mondial-express' ), 'text', '' ),
			'arrivee'     => array( __( 'Arrivée', 'mondial-express' ), 'date', '' ),
			'nuits'       => array( __( 'Nombre de nuits', 'mondial-express' ), 'number', '' ),
			'personnes'   => array( __( 'Personnes', 'mondial-express' ), 'number', '' ),
		),
		'hajj'       => array(
			'forfait'   => array( __( 'Forfait', 'mondial-express' ), 'text', '' ),
			'personnes' => array( __( 'Nombre de pèlerins', 'mondial-express' ), 'number', '' ),
		),
		'evacuation' => array(
			'urgence'     => array( __( 'Degré d’urgence', 'mondial-express' ), 'select', array( 'Très urgent (24 h)', 'Sous quelques jours', 'Planifié' ) ),
			'destination' => array( __( 'Destination souhaitée', 'mondial-express' ), 'text', '' ),
		),
		'affaires'   => array(
			'entreprise' => array( __( 'Entreprise / ONG', 'mondial-express' ), 'text', '' ),
			'volume'     => array( __( 'Voyages par an (environ)', 'mondial-express' ), 'number', '' ),
		),
		'contact'    => array(),
	);
	$fields = $map[ $type ] ?? array();
	foreach ( $fields as $k => $f ) {
		if ( is_array( $f[2] ) ) {
			$fields[ $k ][2] = array_map( fn( $o ) => __( $o, 'mondial-express' ), $f[2] ); // phpcs:ignore WordPress.WP.I18n
		} elseif ( $f[2] ) {
			$fields[ $k ][2] = __( $f[2], 'mondial-express' ); // phpcs:ignore WordPress.WP.I18n
		}
	}
	return $fields;
}

/**
 * Affiche un formulaire de demande.
 *
 * @param string $type    Type de demande (billet, visa, hotel, hajj, evacuation, affaires, contact).
 * @param array  $args    'compact' => true pour la version barre de recherche, 'prefill' => valeurs.
 */
function me_request_form( $type = 'billet', $args = array() ) {
	$compact = ! empty( $args['compact'] );
	$prefill = $args['prefill'] ?? array();
	$types   = me_demande_types();
	$uid     = 'f' . wp_rand( 100, 999 );
	ob_start();
	?>
	<form class="me-form <?php echo $compact ? 'me-form--compact' : ''; ?>" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="me_demande">
		<input type="hidden" name="type" value="<?php echo esc_attr( $type ); ?>">
		<input type="hidden" name="retour" value="<?php echo esc_url( get_permalink() ? get_permalink() : home_url( '/' ) ); ?>">
		<?php wp_nonce_field( 'me_demande', 'me_nonce' ); ?>
		<p class="hp" aria-hidden="true"><label>Site web <input type="text" name="site_web" tabindex="-1" autocomplete="off"></label></p>
		<div class="me-form__grid">
			<?php foreach ( me_form_fields( $type ) as $name => $f ) : if ( $compact && in_array( $name, array( 'retour', 'classe', 'nuits', 'motif' ), true ) ) { continue; } $id = $uid . '-' . $name; $val = $prefill[ $name ] ?? ( is_string( $f[2] ) ? $f[2] : '' ); ?>
				<div class="fld">
					<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $f[0] ); ?></label>
					<?php if ( 'select' === $f[1] ) : ?>
						<select id="<?php echo esc_attr( $id ); ?>" name="champ[<?php echo esc_attr( $name ); ?>]">
							<?php foreach ( $f[2] as $o ) : ?><option <?php selected( $val, $o ); ?>><?php echo esc_html( $o ); ?></option><?php endforeach; ?>
						</select>
					<?php else : ?>
						<input id="<?php echo esc_attr( $id ); ?>" type="<?php echo esc_attr( $f[1] ); ?>" name="champ[<?php echo esc_attr( $name ); ?>]" value="<?php echo esc_attr( $val ); ?>" <?php echo 'number' === $f[1] ? 'min="1"' : ''; ?>>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
			<div class="fld">
				<label for="<?php echo esc_attr( $uid ); ?>-nom"><?php esc_html_e( 'Nom complet', 'mondial-express' ); ?></label>
				<input id="<?php echo esc_attr( $uid ); ?>-nom" type="text" name="nom" required autocomplete="name">
			</div>
			<div class="fld">
				<label for="<?php echo esc_attr( $uid ); ?>-tel"><?php esc_html_e( 'Téléphone / WhatsApp', 'mondial-express' ); ?></label>
				<input id="<?php echo esc_attr( $uid ); ?>-tel" type="tel" name="telephone" required autocomplete="tel" placeholder="+224 6xx xx xx xx">
			</div>
			<?php if ( ! $compact ) : ?>
				<div class="fld">
					<label for="<?php echo esc_attr( $uid ); ?>-email"><?php esc_html_e( 'E-mail (facultatif)', 'mondial-express' ); ?></label>
					<input id="<?php echo esc_attr( $uid ); ?>-email" type="email" name="email" autocomplete="email">
				</div>
				<div class="fld fld--full">
					<label for="<?php echo esc_attr( $uid ); ?>-msg"><?php echo 'contact' === $type ? esc_html__( 'Votre message', 'mondial-express' ) : esc_html__( 'Précisions (facultatif)', 'mondial-express' ); ?></label>
					<textarea id="<?php echo esc_attr( $uid ); ?>-msg" name="message" rows="4" <?php echo 'contact' === $type ? 'required' : ''; ?>></textarea>
				</div>
			<?php endif; ?>
			<div class="fld fld--submit">
				<button class="btn" type="submit"><?php echo me_icon( 'arrow' ); ?> <?php echo 'contact' === $type ? esc_html__( 'Envoyer le message', 'mondial-express' ) : ( $compact ? esc_html__( 'Envoyer', 'mondial-express' ) : esc_html__( 'Envoyer ma demande', 'mondial-express' ) ); ?></button>
			</div>
		</div>
		<?php if ( ! $compact ) : ?>
			<p class="me-form__note"><?php esc_html_e( 'Réponse rapide par WhatsApp ou par téléphone. Vos informations servent uniquement à traiter votre demande.', 'mondial-express' ); ?></p>
		<?php endif; ?>
	</form>
	<?php
	return ob_get_clean();
}

/**
 * Enregistre une demande (site ou application) et prévient l'agence.
 *
 * @param array $data type, nom, telephone, email, message, champs, source.
 * @return int|WP_Error Identifiant de la demande.
 */
function me_create_demande( $data ) {
	$types = me_demande_types();
	$type  = sanitize_key( $data['type'] ?? 'contact' );
	$type  = isset( $types[ $type ] ) ? $type : 'contact';
	$nom   = sanitize_text_field( $data['nom'] ?? '' );
	$tel   = sanitize_text_field( $data['telephone'] ?? '' );
	$email = sanitize_email( $data['email'] ?? '' );
	$msg   = sanitize_textarea_field( $data['message'] ?? '' );
	if ( ! $nom || ! preg_match( '/\d{6,}/', preg_replace( '/\D/', '', $tel ) ) ) {
		return new WP_Error( 'incomplet', __( 'Merci d’indiquer votre nom et un numéro de téléphone valide.', 'mondial-express' ) );
	}
	$lines  = array();
	$champs = is_array( $data['champs'] ?? null ) ? $data['champs'] : array();
	foreach ( me_form_fields( $type ) as $k => $f ) {
		if ( ! empty( $champs[ $k ] ) && is_scalar( $champs[ $k ] ) ) {
			$lines[] = $f[0] . ' : ' . sanitize_text_field( (string) $champs[ $k ] );
		}
	}
	if ( $msg ) {
		$lines[] = __( 'Message', 'mondial-express' ) . ' : ' . $msg;
	}
	if ( 'app' === ( $data['source'] ?? '' ) ) {
		$lines[] = __( 'Envoyée depuis l’application mobile', 'mondial-express' );
	}
	$details = implode( "\n", $lines );
	$id      = wp_insert_post(
		array(
			'post_type'   => 'me_demande',
			'post_status' => 'publish',
			/* translators: 1: type, 2: nom */
			'post_title'  => sprintf( '%1$s – %2$s', $types[ $type ], $nom ),
			'meta_input'  => array(
				'_me_statut'    => 'nouvelle',
				'_me_type'      => $type,
				'_me_nom'       => $nom,
				'_me_telephone' => $tel,
				'_me_email'     => $email,
				'_me_details'   => $details,
				'_me_source'    => sanitize_key( $data['source'] ?? 'site' ),
				'_me_client'    => get_current_user_id(),
			),
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		return $id;
	}
	$to = me_opt( 'email' ) ? me_opt( 'email' ) : get_option( 'admin_email' );
	/* translators: %s: type */
	wp_mail( $to, sprintf( __( 'Nouvelle demande : %s', 'mondial-express' ), $types[ $type ] ), $nom . "\n" . $tel . "\n" . $email . "\n\n" . $details . "\n\n" . admin_url( 'post.php?post=' . (int) $id . '&action=edit' ) );
	set_transient( 'me_wa_' . $id, $types[ $type ] . "\n" . $details, HOUR_IN_SECONDS );
	return $id;
}

/**
 * Traitement d'une demande.
 */
function me_handle_demande() {
	$back = isset( $_POST['retour'] ) ? esc_url_raw( wp_unslash( $_POST['retour'] ) ) : home_url( '/' );
	if ( ! isset( $_POST['me_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['me_nonce'] ), 'me_demande' ) ) {
		wp_safe_redirect( add_query_arg( 'demande', 'erreur', $back ) );
		exit;
	}
	if ( ! empty( $_POST['site_web'] ) ) { // Robot.
		wp_safe_redirect( add_query_arg( 'demande', 'ok', $back ) );
		exit;
	}
	$champs = isset( $_POST['champ'] ) && is_array( $_POST['champ'] ) ? wp_unslash( $_POST['champ'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$id     = me_create_demande(
		array(
			'type'      => sanitize_key( wp_unslash( $_POST['type'] ?? 'contact' ) ),
			'nom'       => wp_unslash( $_POST['nom'] ?? '' ),
			'telephone' => wp_unslash( $_POST['telephone'] ?? '' ),
			'email'     => wp_unslash( $_POST['email'] ?? '' ),
			'message'   => wp_unslash( $_POST['message'] ?? '' ),
			'champs'    => $champs,
			'source'    => 'site',
		)
	);
	if ( is_wp_error( $id ) ) {
		wp_safe_redirect( add_query_arg( 'demande', 'incomplet', $back ) );
		exit;
	}
	wp_safe_redirect( add_query_arg( array( 'demande' => 'ok', 'ref' => (int) $id ), $back ) . '#confirmation' );
	exit;
}
add_action( 'admin_post_me_demande', 'me_handle_demande' );
add_action( 'admin_post_nopriv_me_demande', 'me_handle_demande' );

/**
 * Message de confirmation après envoi (affiché en haut du contenu).
 */
function me_form_notice() {
	if ( empty( $_GET['demande'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return '';
	}
	$state = sanitize_key( $_GET['demande'] ); // phpcs:ignore WordPress.Security.NonceVerification
	if ( 'ok' === $state ) {
		$ref  = isset( $_GET['ref'] ) ? absint( $_GET['ref'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
		$txt  = $ref ? get_transient( 'me_wa_' . $ref ) : '';
		$wa   = me_whatsapp_url( __( 'Bonjour Mondial Express, je viens d’envoyer une demande sur le site.', 'mondial-express' ) . ( $txt ? "\n" . $txt : '' ) );
		return '<div class="notice notice--ok" id="confirmation" role="status"><strong>' . esc_html__( 'Merci, votre demande est bien envoyée.', 'mondial-express' ) . '</strong> ' . esc_html__( 'Un conseiller vous contacte rapidement. Pour aller plus vite, envoyez-la aussi sur WhatsApp.', 'mondial-express' ) . ' <a class="btn btn--wa btn--sm" href="' . esc_url( $wa ) . '" target="_blank" rel="noopener">' . me_icon( 'whatsapp' ) . ' WhatsApp</a></div>';
	}
	$msgs = array(
		'incomplet' => __( 'Merci d’indiquer votre nom et un numéro de téléphone valide.', 'mondial-express' ),
		'erreur'    => __( 'La page a expiré. Rechargez-la puis renvoyez votre demande.', 'mondial-express' ),
	);
	return isset( $msgs[ $state ] ) ? '<div class="notice notice--err" id="confirmation" role="alert">' . esc_html( $msgs[ $state ] ) . '</div>' : '';
}

/**
 * Recherche d'un dossier : numéro + 4 derniers chiffres du téléphone.
 */
function me_find_dossier( $numero, $tel4 ) {
	$numero = strtoupper( trim( $numero ) );
	$ids    = get_posts(
		array(
			'post_type'   => 'me_dossier',
			'post_status' => 'publish',
			'meta_key'    => '_me_numero', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'  => $numero, // phpcs:ignore WordPress.DB.SlowDBQuery
			'fields'      => 'ids',
			'numberposts' => 1,
		)
	);
	if ( ! $ids ) {
		return 0;
	}
	$tel = preg_replace( '/\D/', '', (string) get_post_meta( $ids[0], '_me_telephone', true ) );
	return ( strlen( $tel4 ) >= 4 && substr( $tel, -4 ) === substr( preg_replace( '/\D/', '', $tel4 ), -4 ) ) ? $ids[0] : 0;
}

/**
 * Frise d'avancement d'un dossier.
 */
function me_dossier_timeline( $id ) {
	$steps   = me_dossier_steps();
	$current = get_post_meta( $id, '_me_etape', true );
	$current = $current ? $current : 'recu';
	$keys    = array_keys( $steps );
	$pos     = array_search( $current, $keys, true );
	$types   = me_demande_types();
	$type    = get_post_meta( $id, '_me_type', true );
	$out     = '<div class="dossier">';
	$out    .= '<div class="dossier__head"><div><small>' . esc_html__( 'Dossier', 'mondial-express' ) . '</small><strong>' . esc_html( get_post_meta( $id, '_me_numero', true ) ) . '</strong></div><div><small>' . esc_html__( 'Type', 'mondial-express' ) . '</small><strong>' . esc_html( $types[ $type ] ?? '' ) . '</strong></div><span class="pill">' . esc_html( ( $pos + 1 ) . ' / ' . count( $keys ) ) . '</span></div>';
	$out    .= '<h3 class="dossier__title">' . esc_html( get_the_title( $id ) ) . '</h3><ol class="timeline">';
	foreach ( $keys as $i => $k ) {
		$cls  = $i < $pos ? 'is-done' : ( $i === $pos ? 'is-now' : '' );
		$out .= '<li class="' . $cls . '"><span class="timeline__dot">' . ( $i < $pos || ( $i === $pos && 'termine' === $k ) ? me_icon( 'check' ) : '' ) . '</span><span>' . esc_html( $steps[ $k ] ) . '</span></li>';
	}
	$out .= '</ol>';
	$rdv  = get_post_meta( $id, '_me_rdv', true );
	$msg  = get_post_meta( $id, '_me_message', true );
	if ( $rdv ) {
		$out .= '<p class="dossier__rdv">' . me_icon( 'clock' ) . ' ' . esc_html( $rdv ) . '</p>';
	}
	if ( $msg ) {
		$out .= '<p class="dossier__msg">' . nl2br( esc_html( $msg ) ) . '</p>';
	}
	$pay = get_post_meta( $id, '_me_paiement', true );
	if ( 'solde' === $pay ) {
		$out .= '<div class="dossier__pay">' . me_icon( 'card' ) . ' <span>' . esc_html__( 'Solde à régler.', 'mondial-express' ) . '</span> <span class="pay-methods">' . implode( '', array_map( fn( $m ) => '<span>' . esc_html( $m[0] ) . '</span>', me_payment_methods() ) ) . '</span> <a class="btn btn--sm" href="' . esc_url( me_whatsapp_url( 'Bonjour, je souhaite régler le solde du dossier ' . get_post_meta( $id, '_me_numero', true ) ) ) . '">' . esc_html__( 'Payer', 'mondial-express' ) . '</a></div>';
	}
	return $out . '</div>';
}
