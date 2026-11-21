<?php
/**
 * Application mobile Mondial Express (application web installable, servie sur /app/).
 *
 * Android : « Ajouter à l'écran d'accueil » ou publication Play Store via TWA.
 * iPhone : Safari > Partager > « Sur l'écran d'accueil ».
 *
 * @package MondialExpress
 */

defined( 'ABSPATH' ) || exit;

define( 'ME_APP_VERSION', '1.1.0' );

add_action(
	'init',
	function () {
		add_rewrite_rule( '^app/?$', 'index.php?me_app=shell', 'top' );
		add_rewrite_rule( '^app/sw\.js$', 'index.php?me_app=sw', 'top' );
		add_rewrite_rule( '^app/manifest\.webmanifest$', 'index.php?me_app=manifest', 'top' );
		if ( get_option( 'me_app_rewrite' ) !== ME_APP_VERSION ) {
			flush_rewrite_rules( false );
			update_option( 'me_app_rewrite', ME_APP_VERSION );
		}
	}
);
add_filter(
	'query_vars',
	function ( $v ) {
		$v[] = 'me_app';
		return $v;
	}
);
add_action( 'after_switch_theme', fn() => delete_option( 'me_app_rewrite' ) );

add_action(
	'template_redirect',
	function () {
		$what = get_query_var( 'me_app' );
		if ( ! $what ) {
			return;
		}
		$base = ME_URI . '/app/';
		if ( 'manifest' === $what ) {
			header( 'Content-Type: application/manifest+json; charset=utf-8' );
			echo wp_json_encode(
				array(
					'id'               => home_url( '/app/' ),
					'name'             => 'Mondial Express Guinée',
					'short_name'       => 'Mondial Express',
					'description'      => 'Billets, visas, Hajj & Oumra et suivi de dossier avec votre agence Mondial Express à Conakry.',
					'lang'             => 'fr',
					'start_url'        => home_url( '/app/?source=installee' ),
					'scope'            => home_url( '/app/' ),
					'display'          => 'standalone',
					'orientation'      => 'portrait',
					'background_color' => '#ffffff',
					'theme_color'      => '#E8730C',
					'categories'       => array( 'travel' ),
					'icons'            => array(
						array( 'src' => ME_URI . '/assets/img/android-chrome-192.png', 'sizes' => '192x192', 'type' => 'image/png' ),
						array( 'src' => ME_URI . '/assets/img/android-chrome-512.png', 'sizes' => '512x512', 'type' => 'image/png' ),
						array( 'src' => ME_URI . '/assets/img/icone-app-maskable-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable' ),
					),
					'shortcuts'        => array(
						array( 'name' => 'Demander un billet', 'url' => home_url( '/app/#/demande/billet' ) ),
						array( 'name' => 'Suivre mon dossier', 'url' => home_url( '/app/#/suivi' ) ),
					),
				),
				JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
			);
			exit;
		}
		if ( 'sw' === $what ) {
			header( 'Content-Type: application/javascript; charset=utf-8' );
			header( 'Service-Worker-Allowed: /app/' );
			header( 'Cache-Control: no-cache' );
			echo 'const ME_V=' . wp_json_encode( ME_APP_VERSION . '-' . ME_VERSION ) . ';const ME_BASE=' . wp_json_encode( $base ) . ';const ME_HOME=' . wp_json_encode( home_url( '/app/' ) ) . ";\n";
			readfile( ME_DIR . '/app/sw.js' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			exit;
		}
		nocache_headers();
		$boot = array(
			'api'         => esc_url_raw( rest_url( 'mondial/v1/' ) ),
			'nonce'       => is_user_logged_in() ? wp_create_nonce( 'wp_rest' ) : '',
			'utilisateur' => me_api_user(),
			'config'      => me_api_config(),
			'base'        => $base,
			'home'        => home_url( '/app/' ),
		);
		include ME_DIR . '/app/index.php';
		exit;
	}
);

/**
 * Lien vers l'application dans l'en-tête du site (installation sur mobile).
 */
add_action(
	'wp_head',
	function () {
		echo '<link rel="manifest" href="' . esc_url( home_url( '/app/manifest.webmanifest' ) ) . '">' . "\n";
	},
	3
);

/**
 * Pas de redirection « canonique » (barre oblique finale) sur les fichiers de l'application.
 */
add_filter( 'redirect_canonical', fn( $url ) => get_query_var( 'me_app' ) ? false : $url );
