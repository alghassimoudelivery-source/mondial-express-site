<?php
/**
 * Thème Mondial Express Guinée — design « Ciel ouvert ».
 * Réalisé par ConforDev (https://confordev.com).
 *
 * @package MondialExpress
 */

defined( 'ABSPATH' ) || exit;

define( 'ME_VERSION', '1.0.0' );
define( 'ME_DIR', get_template_directory() );
define( 'ME_URI', get_template_directory_uri() );

require ME_DIR . '/inc/helpers.php';
require ME_DIR . '/inc/i18n.php';
require ME_DIR . '/inc/customizer.php';
require ME_DIR . '/inc/post-types.php';
require ME_DIR . '/inc/forms.php';
require ME_DIR . '/inc/espace-client.php';
require ME_DIR . '/inc/connexion.php';
require ME_DIR . '/inc/seo.php';
require ME_DIR . '/inc/admin.php';
require ME_DIR . '/inc/presentation.php';
require ME_DIR . '/inc/api.php';
require ME_DIR . '/inc/app.php';

/**
 * Réglages du thème.
 */
function me_setup() {
	load_theme_textdomain( 'mondial-express', ME_DIR . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo', array( 'height' => 120, 'width' => 420, 'flex-height' => true, 'flex-width' => true ) );
	add_image_size( 'me-card', 640, 420, true );
	add_image_size( 'me-hero', 1400, 700, true );
	register_nav_menus(
		array(
			'principal' => __( 'Menu principal', 'mondial-express' ),
			'pied'      => __( 'Menu du pied de page', 'mondial-express' ),
		)
	);
}
add_action( 'after_setup_theme', 'me_setup' );

/**
 * Styles et scripts.
 */
function me_assets() {
	wp_enqueue_style( 'mondial-express', get_stylesheet_uri(), array(), ME_VERSION );
	wp_enqueue_script( 'mondial-express', ME_URI . '/assets/js/main.js', array(), ME_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
	// Le thème n'utilise pas l'éditeur de blocs côté visiteurs : on allège la page.
	if ( ! is_singular( 'post' ) ) {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'classic-theme-styles' );
	}
}
add_action( 'wp_enqueue_scripts', 'me_assets', 20 );

/**
 * Précharge les polices et ajoute favicon / icônes.
 */
function me_head_links() {
	printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( ME_URI . '/assets/fonts/montserrat-var.woff2' ) );
	if ( ! has_site_icon() ) {
		printf( '<link rel="icon" href="%s" sizes="any">' . "\n", esc_url( ME_URI . '/assets/img/favicon.ico' ) );
		printf( '<link rel="icon" href="%s" type="image/svg+xml">' . "\n", esc_url( ME_URI . '/assets/img/favicon.svg' ) );
		printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( ME_URI . '/assets/img/apple-touch-icon.png' ) );
	}
	echo '<meta name="theme-color" content="#E8730C">' . "\n";
}
add_action( 'wp_head', 'me_head_links', 2 );

/**
 * Longueur et suite des extraits.
 */
add_filter( 'excerpt_length', fn() => 26 );
add_filter( 'excerpt_more', fn() => '…' );

/**
 * Classe du body pour la page d'accueil.
 */
add_filter(
	'body_class',
	function ( $classes ) {
		if ( is_front_page() ) {
			$classes[] = 'is-accueil';
		}
		return $classes;
	}
);
