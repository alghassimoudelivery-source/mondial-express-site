<?php
/**
 * Page de connexion de l'équipe : adresse personnalisée (/espace-gestion/) et habillage Mondial Express.
 * - wp-login.php et /wp-admin/ (hors connexion) répondent « page introuvable » ;
 * - tous les liens de WordPress (connexion, déconnexion, mot de passe oublié, formulaire de l'espace client) pointent vers la nouvelle adresse.
 *
 * @package MondialExpress
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'ME_LOGIN_SLUG' ) ) {
	define( 'ME_LOGIN_SLUG', 'espace-gestion' );
}

/**
 * Chemin demandé, sans le dossier d'installation ni la chaîne de requête.
 */
function me_login_request_path() {
	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$path = (string) wp_parse_url( $uri, PHP_URL_PATH );
	$base = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	if ( $base && '/' !== $base && str_starts_with( $path, $base ) ) {
		$path = '/' . substr( $path, strlen( $base ) );
	}
	return '/' . trim( $path, '/' );
}

/**
 * Adresse de connexion : remplace wp-login.php dans une URL.
 */
function me_login_rewrite_url( $url ) {
	if ( str_contains( $url, 'wp-login.php' ) ) {
		$url = str_replace( 'wp-login.php', ME_LOGIN_SLUG . '/', $url );
	}
	return $url;
}
add_filter( 'site_url', fn( $url ) => me_login_rewrite_url( $url ), 10, 1 );
add_filter( 'network_site_url', fn( $url ) => me_login_rewrite_url( $url ), 10, 1 );
add_filter( 'wp_redirect', fn( $url ) => me_login_rewrite_url( $url ), 10, 1 );
add_filter( 'login_url', fn( $url ) => me_login_rewrite_url( $url ), 10, 1 );
add_filter( 'logout_url', fn( $url ) => me_login_rewrite_url( $url ), 10, 1 );
add_filter( 'lostpassword_url', fn( $url ) => me_login_rewrite_url( $url ), 10, 1 );
add_filter( 'register_url', fn( $url ) => me_login_rewrite_url( $url ), 10, 1 );

// WordPress redirige /login et /admin vers les adresses d'origine : on le désactive.
remove_action( 'template_redirect', 'wp_redirect_admin_locations', 1000 );

/**
 * Sert la page de connexion sur /espace-gestion/ et masque les anciennes adresses.
 */
function me_login_router() {
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return;
	}
	$path = me_login_request_path();

	// Nouvelle adresse : on charge la vraie page de connexion de WordPress.
	if ( '/' . ME_LOGIN_SLUG === $path ) {
		global $pagenow, $error, $interim_login, $action, $user_login, $user, $redirect_to; // phpcs:ignore
		$pagenow = 'wp-login.php'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
		require_once ABSPATH . 'wp-login.php';
		exit;
	}

	$script = isset( $_SERVER['SCRIPT_NAME'] ) ? basename( sanitize_text_field( wp_unslash( $_SERVER['SCRIPT_NAME'] ) ) ) : '';

	// Ancienne adresse : introuvable (sauf mot de passe d'un article protégé et confirmation de suppression de données).
	if ( 'wp-login.php' === $script || '/wp-login.php' === $path ) {
		$act = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		if ( in_array( $act, array( 'postpass', 'confirmaction' ), true ) ) {
			return;
		}
		me_login_not_found();
	}

	// Administration hors connexion : introuvable plutôt qu'une redirection qui révélerait l'adresse.
	if ( is_admin() && ! is_user_logged_in() && ! wp_doing_ajax() && ! in_array( $script, array( 'admin-post.php', 'admin-ajax.php' ), true ) ) {
		me_login_not_found();
	}
}
// Le thème est chargé après plugins_loaded : after_setup_theme est le premier moment disponible.
add_action( 'after_setup_theme', 'me_login_router', 0 );

/**
 * Page 404 du site.
 */
function me_login_not_found() {
	nocache_headers();
	wp_safe_redirect( home_url( '/404-introuvable/' ) );
	exit;
}

/* ------------------------------------------------------------------ */
/* Habillage                                                            */
/* ------------------------------------------------------------------ */

add_filter( 'login_headerurl', fn() => home_url( '/' ) );
add_filter( 'login_headertext', fn() => get_bloginfo( 'name' ) );
add_filter( 'login_display_language_dropdown', '__return_false' );
add_filter( 'login_title', fn() => __( 'Espace de gestion', 'mondial-express' ) . ' · ' . get_bloginfo( 'name' ) );

add_action(
	'login_head',
	function () {
		echo '<link rel="icon" href="' . esc_url( ME_URI . '/assets/img/favicon.svg' ) . '" type="image/svg+xml">';
		echo '<meta name="theme-color" content="#5B2D90">';
	}
);

add_action(
	'login_enqueue_scripts',
	function () {
		$logo  = esc_url( ME_URI . '/assets/img/logo-mondial-express-blanc.svg' );
		$font  = esc_url( ME_URI . '/assets/fonts/montserrat-var.woff2' );
		$font2 = esc_url( ME_URI . '/assets/fonts/inter-var.woff2' );
		$css   = <<<CSS
@font-face{font-family:"Montserrat";src:url("$font") format("woff2");font-weight:100 900;font-display:swap}
@font-face{font-family:"Inter";src:url("$font2") format("woff2");font-weight:100 900;font-display:swap}
body.login{min-height:100vh;display:flex;flex-direction:column;justify-content:center;background:#2A1645 linear-gradient(135deg,#E8730C 0%,#C2561F 30%,#5B2D90 75%,#2A1645 100%) fixed;font-family:"Inter",system-ui,sans-serif;color:#1D1530}
body.login::before{content:"";position:fixed;inset:0;background:radial-gradient(circle at 15% 10%,rgba(255,255,255,.16),transparent 40%),radial-gradient(circle at 90% 90%,rgba(100,167,11,.22),transparent 45%);pointer-events:none}
#login{position:relative;width:min(400px,calc(100% - 32px));padding:5vh 0 24px;margin:0 auto}
.login h1 a{background:url("$logo") center/contain no-repeat;width:260px;height:74px;margin:0 auto 18px;filter:drop-shadow(0 6px 18px rgba(0,0,0,.18))}
.login h1::after{content:"Espace de gestion";display:block;text-align:center;color:#fff;font:700 13px/1 "Montserrat",sans-serif;letter-spacing:.14em;text-transform:uppercase;opacity:.9;margin-bottom:22px}
.login form{margin-top:0;padding:30px 28px 26px;border:0;border-radius:24px;background:#fff;box-shadow:0 30px 60px -20px rgba(20,8,40,.55)}
.login form .input,.login input[type=text],.login input[type=password],.login input[type=email]{min-height:50px;border:1.5px solid #ECE7F2;border-radius:14px;background:#FAF8FC;padding:0 14px;font-size:16px;box-shadow:none}
.login form .input:focus,.login input[type=text]:focus,.login input[type=password]:focus{border-color:#E8730C;box-shadow:0 0 0 3px rgba(232,115,12,.18);background:#fff;outline:0}
.login label{font:600 14px/1.4 "Montserrat",sans-serif;color:#1D1530}
.login .button.wp-hide-pw{height:50px;color:#5B2D90}
.login .button.wp-hide-pw:focus{box-shadow:none;border-color:transparent}
.login input[type=checkbox]{border-color:#C9BFD8;border-radius:5px}
.login input[type=checkbox]:checked::before{filter:hue-rotate(160deg) saturate(2)}
.wp-core-ui .button-primary{width:100%;margin-top:18px;min-height:52px;border:0;border-radius:14px;background:#D9650A;font:700 16px/1 "Montserrat",sans-serif;text-shadow:none;box-shadow:0 10px 22px -10px rgba(217,101,10,.8);transition:background .2s,transform .2s}
.wp-core-ui .button-primary:hover,.wp-core-ui .button-primary:focus{background:#B4560A;box-shadow:0 0 0 3px rgba(232,115,12,.3)}
.login .forgetmenot{float:none;margin:6px 0 0}
.login .submit{display:block}
.login #nav,.login #backtoblog{text-align:center;padding:0;margin:16px 0 0}
.login #nav a,.login #backtoblog a,.login .privacy-policy-page-link a{color:#fff;font-weight:600;text-decoration:none;opacity:.92}
.login #nav a:hover,.login #backtoblog a:hover{color:#fff;opacity:1;text-decoration:underline}
.login .message,.login .notice,.login .success,.login #login_error{border:0;border-left:4px solid #E8730C;border-radius:14px;background:#fff;box-shadow:0 12px 30px -18px rgba(0,0,0,.4)}
.login #login_error{border-left-color:#C0392B}
.login .privacy-policy-page-link{margin:18px 0 0;text-align:center}
.me-login-credit{position:relative;text-align:center;color:rgba(255,255,255,.75);font:500 12.5px/1.4 "Inter",sans-serif;padding:8px 16px 24px}
.me-login-credit a{color:#fff;font-weight:700;text-decoration:none}
.me-login-credit a:hover{text-decoration:underline}
@media (max-width:480px){#login{padding-top:32px}.login form{padding:24px 20px 22px}.login h1 a{width:220px;height:62px}}
CSS;
		wp_register_style( 'me-login', false, array(), ME_VERSION );
		wp_enqueue_style( 'me-login' );
		wp_add_inline_style( 'me-login', $css );
	}
);

add_action(
	'login_footer',
	function () {
		echo '<p class="me-login-credit">' . esc_html__( 'Site réalisé par', 'mondial-express' ) . ' <a href="https://confordev.com" target="_blank" rel="noopener">ConforDev</a></p>';
	}
);
