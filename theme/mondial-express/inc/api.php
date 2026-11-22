<?php
/**
 * API de l'application mobile (espace de noms mondial/v1).
 *
 * L'application et le site partagent les mêmes données : offres, forfaits,
 * articles, demandes, dossiers et comptes clients.
 *
 * @package MondialExpress
 */

defined( 'ABSPATH' ) || exit;

/**
 * Limite simple par adresse IP (anti-abus des formulaires publics).
 */
function me_api_rate_limit( $action, $max = 8, $window = 600 ) {
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'x';
	$key = 'me_rl_' . md5( $action . $ip );
	$n   = (int) get_transient( $key );
	if ( $n >= $max ) {
		return new WP_Error( 'trop', __( 'Trop de tentatives. Réessayez dans quelques minutes.', 'mondial-express' ), array( 'status' => 429 ) );
	}
	set_transient( $key, $n + 1, $window );
	return true;
}

/**
 * Données d'un dossier pour l'application.
 */
function me_dossier_data( $id ) {
	$steps   = me_dossier_steps();
	$types   = me_demande_types();
	$current = get_post_meta( $id, '_me_etape', true );
	$current = $current ? $current : 'recu';
	$keys    = array_keys( $steps );
	$pos     = (int) array_search( $current, $keys, true );
	$docs    = array();
	foreach ( get_children( array( 'post_parent' => $id, 'post_type' => 'attachment' ) ) as $d ) {
		$docs[] = array( 'titre' => get_the_title( $d ), 'date' => get_the_date( 'd/m/Y', $d ) );
	}
	return array(
		'id'        => (int) $id,
		'numero'    => get_post_meta( $id, '_me_numero', true ),
		'titre'     => get_the_title( $id ),
		'type'      => $types[ get_post_meta( $id, '_me_type', true ) ] ?? '',
		'etape'     => $current,
		'position'  => $pos,
		'etapes'    => array_map( fn( $k ) => array( 'cle' => $k, 'libelle' => $steps[ $k ] ), $keys ),
		'rdv'       => get_post_meta( $id, '_me_rdv', true ),
		'message'   => get_post_meta( $id, '_me_message', true ),
		'paiement'  => get_post_meta( $id, '_me_paiement', true ),
		'documents' => $docs,
		'maj'       => get_post_modified_time( 'c', true, $id ),
	);
}

/**
 * Configuration publique (coordonnées, réseaux, services, formulaires).
 */
function me_api_config() {
	$socials = array();
	foreach ( me_social_networks() as $k => $label ) {
		$socials[] = array( 'cle' => $k, 'nom' => $label, 'url' => me_opt( $k ) );
	}
	$services = array();
	foreach ( me_services() as $slug => $s ) {
		$services[] = array( 'slug' => $slug, 'icone' => $s[0], 'titre' => $s[1], 'texte' => $s[2], 'couleur' => $s[3] );
	}
	$forms = array();
	foreach ( me_demande_types() as $type => $label ) {
		$fields = array();
		foreach ( me_form_fields( $type ) as $k => $f ) {
			$fields[] = array( 'cle' => $k, 'libelle' => $f[0], 'type' => $f[1], 'valeur' => is_array( $f[2] ) ? '' : $f[2], 'options' => is_array( $f[2] ) ? array_values( $f[2] ) : array() );
		}
		$forms[] = array( 'type' => $type, 'libelle' => $label, 'champs' => $fields );
	}
	return array(
		'nom'       => get_bloginfo( 'name' ),
		'slogan'    => __( 'Chaque voyage est une histoire !', 'mondial-express' ),
		'telephone' => me_opt( 'telephone' ),
		'whatsapp'  => preg_replace( '/\D/', '', me_opt( 'whatsapp' ) ),
		'email'     => me_opt( 'email' ),
		'adresse'   => me_opt( 'adresse' ) ? me_opt( 'adresse' ) : __( 'Conakry, Guinée', 'mondial-express' ),
		'horaires'  => me_opt( 'horaires' ),
		'site'      => home_url( '/' ),
		'reseaux'   => $socials,
		'services'  => $services,
		'demandes'  => $forms,
		'stats'     => array( me_opt( 'stat_voyageurs' ), me_opt( 'stat_dest' ), me_opt( 'stat_visas' ) ),
		'paiements' => array_values( array_map( fn( $m ) => array( 'nom' => $m[0], 'detail' => $m[1], 'cle' => $m[2] ), me_payment_methods() ) ),
		'paiement_note' => me_opt( 'pay_note' ),
	);
}

/**
 * Utilisateur connecté, avec ses dossiers.
 */
function me_api_user() {
	$u = wp_get_current_user();
	if ( ! $u->ID ) {
		return null;
	}
	$ids = get_posts( array( 'post_type' => 'me_dossier', 'numberposts' => 30, 'fields' => 'ids', 'meta_key' => '_me_client', 'meta_value' => $u->ID ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	return array(
		'nom'       => $u->display_name,
		'email'     => $u->user_email,
		'telephone' => get_user_meta( $u->ID, 'me_telephone', true ),
		'dossiers'  => array_map( 'me_dossier_data', $ids ),
	);
}

/**
 * Jeton de l'application native (Android / iOS) : en-tête « Authorization: Bearer <jeton> ».
 * Le jeton est stocké haché dans les métadonnées de l'utilisateur (plusieurs appareils possibles).
 */
function me_api_issue_token( $uid ) {
	$token  = wp_generate_password( 48, false, false );
	$hashes = (array) get_user_meta( $uid, 'me_app_tokens', true );
	$hashes = array_slice( array_filter( $hashes ), -9 );
	$hashes[] = hash( 'sha256', $token );
	update_user_meta( $uid, 'me_app_tokens', $hashes );
	return $uid . '.' . $token;
}

function me_api_token_from_header() {
	$h = $_SERVER['HTTP_AUTHORIZATION'] ?? ( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '' ); // phpcs:ignore WordPress.Security
	if ( ! $h && function_exists( 'getallheaders' ) ) {
		foreach ( getallheaders() as $k => $v ) {
			if ( 'authorization' === strtolower( $k ) ) {
				$h = $v;
			}
		}
	}
	return preg_match( '/^Bearer\s+(\d+)\.([A-Za-z0-9]{20,})$/', trim( (string) $h ), $m ) ? array( (int) $m[1], $m[2] ) : null;
}

add_filter(
	'determine_current_user',
	function ( $uid ) {
		if ( $uid || ! defined( 'REST_REQUEST' ) && false === strpos( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), '/wp-json/' ) ) { // phpcs:ignore WordPress.Security
			return $uid;
		}
		$t = me_api_token_from_header();
		if ( ! $t ) {
			return $uid;
		}
		$hashes = (array) get_user_meta( $t[0], 'me_app_tokens', true );
		return in_array( hash( 'sha256', $t[1] ), $hashes, true ) ? $t[0] : $uid;
	},
	30
);

/**
 * Pendant la connexion, rend le cookie disponible tout de suite pour créer un jeton REST valide.
 */
function me_api_capture_cookie( $cookie ) {
	$_COOKIE[ LOGGED_IN_COOKIE ] = $cookie;
}

add_action(
	'rest_api_init',
	function () {
		$ns   = 'mondial/v1';
		$open = '__return_true';

		register_rest_route( $ns, '/config', array( 'methods' => 'GET', 'permission_callback' => $open, 'callback' => fn() => me_api_config() ) );
		register_rest_route( $ns, '/presentation', array( 'methods' => 'GET', 'permission_callback' => $open, 'callback' => fn() => me_presentation_api() ) );

		register_rest_route(
			$ns,
			'/offres',
			array(
				'methods'             => 'GET',
				'permission_callback' => $open,
				'callback'            => function () {
					$out = array();
					foreach ( get_posts( array( 'post_type' => 'me_offre', 'numberposts' => 50, 'orderby' => 'menu_order date', 'order' => 'ASC' ) ) as $p ) {
						$out[] = array(
							'id'        => $p->ID,
							'titre'     => get_the_title( $p ),
							'code'      => get_post_meta( $p->ID, '_me_code', true ),
							'pays'      => get_post_meta( $p->ID, '_me_pays', true ),
							'prix'      => me_price( get_post_meta( $p->ID, '_me_prix', true ) ),
							'libelle'   => get_post_meta( $p->ID, '_me_type_prix', true ) ? get_post_meta( $p->ID, '_me_type_prix', true ) : __( 'Aller-retour', 'mondial-express' ),
							'indicatif' => (bool) get_post_meta( $p->ID, '_me_prix_indicatif', true ),
							'visuel'    => get_post_meta( $p->ID, '_me_visuel', true ) ? get_post_meta( $p->ID, '_me_visuel', true ) : 'orange',
							'image'     => get_the_post_thumbnail_url( $p, 'me-card' ),
							'vedette'   => (bool) get_post_meta( $p->ID, '_me_vedette', true ),
							'texte'     => wp_strip_all_tags( get_the_excerpt( $p ) ),
							'contenu'   => apply_filters( 'the_content', me_tr( $p->ID, 'contenu', $p->post_content ) ), // phpcs:ignore
							'lien'      => get_permalink( $p ),
						);
					}
					return $out;
				},
			)
		);

		register_rest_route(
			$ns,
			'/forfaits',
			array(
				'methods'             => 'GET',
				'permission_callback' => $open,
				'callback'            => function () {
					$out = array();
					foreach ( get_posts( array( 'post_type' => 'me_forfait', 'numberposts' => 30, 'orderby' => 'menu_order date', 'order' => 'ASC' ) ) as $p ) {
						$out[] = array(
							'id'        => $p->ID,
							'titre'     => get_the_title( $p ),
							'type'      => get_post_meta( $p->ID, '_me_type', true ),
							'prix'      => me_price( get_post_meta( $p->ID, '_me_prix', true ) ),
							'indicatif' => (bool) get_post_meta( $p->ID, '_me_prix_indicatif', true ),
							'dates'     => get_post_meta( $p->ID, '_me_dates', true ),
							'duree'     => get_post_meta( $p->ID, '_me_duree', true ),
							'inclus'    => array_values( array_filter( array_map( 'trim', explode( "\n", (string) get_post_meta( $p->ID, '_me_inclus', true ) ) ) ) ),
							'lien'      => get_permalink( $p ),
						);
					}
					return $out;
				},
			)
		);

		register_rest_route(
			$ns,
			'/actus',
			array(
				'methods'             => 'GET',
				'permission_callback' => $open,
				'callback'            => function () {
					$out = array();
					foreach ( get_posts( array( 'numberposts' => 12 ) ) as $p ) {
						$cat   = get_the_category( $p->ID );
						$out[] = array(
							'id'        => $p->ID,
							'titre'     => get_the_title( $p ),
							'date'      => get_the_date( 'd/m/Y', $p ),
							'categorie' => $cat ? $cat[0]->name : '',
							'image'     => get_the_post_thumbnail_url( $p, 'me-card' ),
							'texte'     => wp_strip_all_tags( get_the_excerpt( $p ) ),
							'contenu'   => apply_filters( 'the_content', me_tr( $p->ID, 'contenu', $p->post_content ) ), // phpcs:ignore
							'lien'      => get_permalink( $p ),
						);
					}
					return $out;
				},
			)
		);

		register_rest_route(
			$ns,
			'/demande',
			array(
				'methods'             => 'POST',
				'permission_callback' => $open,
				'callback'            => function ( WP_REST_Request $r ) {
					if ( $r->get_param( 'site_web' ) ) { // Robot.
						return array( 'ok' => true, 'ref' => 0 );
					}
					$rl = me_api_rate_limit( 'demande' );
					if ( is_wp_error( $rl ) ) {
						return $rl;
					}
					$champs = $r->get_param( 'champs' );
					if ( is_string( $champs ) ) {
						$champs = json_decode( $champs, true );
					}
					$id = me_create_demande(
						array(
							'type'      => (string) $r->get_param( 'type' ),
							'nom'       => (string) $r->get_param( 'nom' ),
							'telephone' => (string) $r->get_param( 'telephone' ),
							'email'     => (string) $r->get_param( 'email' ),
							'message'   => (string) $r->get_param( 'message' ),
							'champs'    => is_array( $champs ) ? $champs : array(),
							'source'    => 'app',
						)
					);
					if ( is_wp_error( $id ) ) {
						return new WP_Error( $id->get_error_code(), $id->get_error_message(), array( 'status' => 400 ) );
					}
					// Pièce jointe facultative (photo de passeport, etc.), rangée dans le dossier protégé.
					$files = $r->get_file_params();
					if ( ! empty( $files['piece']['name'] ) && (int) $files['piece']['size'] <= 8 * MB_IN_BYTES ) {
						require_once ABSPATH . 'wp-admin/includes/file.php';
						require_once ABSPATH . 'wp-admin/includes/media.php';
						require_once ABSPATH . 'wp-admin/includes/image.php';
						$_FILES['piece'] = $files['piece'];
						add_filter( 'upload_dir', 'me_private_upload_dir' );
						media_handle_upload( 'piece', $id, array( 'post_status' => 'private', 'post_title' => __( 'Pièce jointe (application)', 'mondial-express' ) ), array( 'test_form' => false, 'mimes' => array( 'pdf' => 'application/pdf', 'jpg|jpeg' => 'image/jpeg', 'png' => 'image/png' ) ) );
						remove_filter( 'upload_dir', 'me_private_upload_dir' );
					}
					return array( 'ok' => true, 'ref' => (int) $id );
				},
			)
		);

		register_rest_route(
			$ns,
			'/suivi',
			array(
				'methods'             => 'POST',
				'permission_callback' => $open,
				'callback'            => function ( WP_REST_Request $r ) {
					$rl = me_api_rate_limit( 'suivi', 15 );
					if ( is_wp_error( $rl ) ) {
						return $rl;
					}
					$id = me_find_dossier( sanitize_text_field( (string) $r->get_param( 'numero' ) ), sanitize_text_field( (string) $r->get_param( 'tel' ) ) );
					if ( ! $id ) {
						return new WP_Error( 'introuvable', __( 'Aucun dossier ne correspond. Vérifiez le numéro et les 4 derniers chiffres du téléphone.', 'mondial-express' ), array( 'status' => 404 ) );
					}
					return me_dossier_data( $id );
				},
			)
		);

		register_rest_route(
			$ns,
			'/connexion',
			array(
				'methods'             => 'POST',
				'permission_callback' => $open,
				'callback'            => function ( WP_REST_Request $r ) {
					$rl = me_api_rate_limit( 'connexion', 10 );
					if ( is_wp_error( $rl ) ) {
						return $rl;
					}
					add_action( 'set_logged_in_cookie', 'me_api_capture_cookie' );
					$user = wp_signon(
						array(
							'user_login'    => (string) $r->get_param( 'identifiant' ),
							'user_password' => (string) $r->get_param( 'mot_de_passe' ),
							'remember'      => true,
						),
						is_ssl()
					);
					if ( is_wp_error( $user ) ) {
						return new WP_Error( 'connexion', __( 'Identifiant ou mot de passe incorrect.', 'mondial-express' ), array( 'status' => 403 ) );
					}
					wp_set_current_user( $user->ID );
					return array( 'utilisateur' => me_api_user(), 'nonce' => wp_create_nonce( 'wp_rest' ), 'jeton' => me_api_issue_token( $user->ID ) );
				},
			)
		);

		register_rest_route(
			$ns,
			'/inscription',
			array(
				'methods'             => 'POST',
				'permission_callback' => $open,
				'callback'            => function ( WP_REST_Request $r ) {
					$rl = me_api_rate_limit( 'inscription', 5, 3600 );
					if ( is_wp_error( $rl ) ) {
						return $rl;
					}
					$uid = me_create_client( (string) $r->get_param( 'nom' ), (string) $r->get_param( 'telephone' ), (string) $r->get_param( 'email' ), (string) $r->get_param( 'mot_de_passe' ) );
					if ( is_wp_error( $uid ) ) {
						return new WP_Error( $uid->get_error_code(), $uid->get_error_message(), array( 'status' => 400 ) );
					}
					add_action( 'set_logged_in_cookie', 'me_api_capture_cookie' );
					wp_set_current_user( $uid );
					wp_set_auth_cookie( $uid, true, is_ssl() );
					return array( 'utilisateur' => me_api_user(), 'nonce' => wp_create_nonce( 'wp_rest' ), 'jeton' => me_api_issue_token( $uid ) );
				},
			)
		);

		$logged = fn() => is_user_logged_in();

		register_rest_route( $ns, '/moi', array( 'methods' => 'GET', 'permission_callback' => $logged, 'callback' => fn() => me_api_user() ) );

		register_rest_route(
			$ns,
			'/deconnexion',
			array(
				'methods'             => 'POST',
				'permission_callback' => $logged,
				'callback'            => function () {
					$t = me_api_token_from_header();
					if ( $t ) {
						$hashes = array_values( array_diff( (array) get_user_meta( $t[0], 'me_app_tokens', true ), array( hash( 'sha256', $t[1] ) ) ) );
						update_user_meta( $t[0], 'me_app_tokens', $hashes );
					}
					wp_logout();
					return array( 'ok' => true );
				},
			)
		);

		// Suppression du compte client depuis l'application (exigée par Google Play et l'App Store).
		register_rest_route(
			$ns,
			'/compte/supprimer',
			array(
				'methods'             => 'POST',
				'permission_callback' => $logged,
				'callback'            => function () {
					$user = wp_get_current_user();
					if ( $user->has_cap( 'edit_posts' ) ) {
						return new WP_Error( 'me_staff', __( 'Les comptes de l’équipe se suppriment depuis l’administration.', 'mondial-express' ), array( 'status' => 403 ) );
					}
					require_once ABSPATH . 'wp-admin/includes/user.php';
					wp_logout();
					wp_delete_user( $user->ID );
					return array( 'ok' => true );
				},
			)
		);

		register_rest_route(
			$ns,
			'/document',
			array(
				'methods'             => 'POST',
				'permission_callback' => $logged,
				'callback'            => function ( WP_REST_Request $r ) {
					$did   = absint( $r->get_param( 'dossier' ) );
					$files = $r->get_file_params();
					if ( (int) get_post_meta( $did, '_me_client', true ) !== get_current_user_id() || empty( $files['fichier']['name'] ) ) {
						return new WP_Error( 'erreur', __( 'Document refusé.', 'mondial-express' ), array( 'status' => 400 ) );
					}
					if ( (int) $files['fichier']['size'] > 8 * MB_IN_BYTES ) {
						return new WP_Error( 'lourd', __( 'Fichier trop lourd (8 Mo maximum).', 'mondial-express' ), array( 'status' => 400 ) );
					}
					require_once ABSPATH . 'wp-admin/includes/file.php';
					require_once ABSPATH . 'wp-admin/includes/media.php';
					require_once ABSPATH . 'wp-admin/includes/image.php';
					$_FILES['fichier'] = $files['fichier'];
					add_filter( 'upload_dir', 'me_private_upload_dir' );
					$att = media_handle_upload( 'fichier', $did, array( 'post_status' => 'private' ), array( 'test_form' => false, 'mimes' => array( 'pdf' => 'application/pdf', 'jpg|jpeg' => 'image/jpeg', 'png' => 'image/png' ) ) );
					remove_filter( 'upload_dir', 'me_private_upload_dir' );
					if ( is_wp_error( $att ) ) {
						return new WP_Error( 'format', __( 'Format accepté : PDF, JPG ou PNG.', 'mondial-express' ), array( 'status' => 400 ) );
					}
					$label = sanitize_text_field( (string) $r->get_param( 'libelle' ) );
					if ( $label ) {
						wp_update_post( array( 'ID' => $att, 'post_title' => $label ) );
					}
					wp_mail( me_opt( 'email' ) ? me_opt( 'email' ) : get_option( 'admin_email' ), __( 'Nouveau document client (application)', 'mondial-express' ), get_the_title( $did ) . "\n" . admin_url( 'post.php?post=' . $did . '&action=edit' ) );
					return me_dossier_data( $did );
				},
			)
		);
	}
);
