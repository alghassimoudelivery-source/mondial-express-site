<?php
/**
 * Traductions EN / ES / AR du contenu de démonstration (appelé à la fin de seed.php, ou seul).
 * N'écrit que des métadonnées de traduction (_me_{langue}_{champ}) : le contenu français n'est pas modifié.
 */
if ( ! defined( 'ABSPATH' ) ) {
	$root = $argv[1] ?? __DIR__ . '/site';
	if ( empty( $_SERVER['HTTP_HOST'] ) ) {
		$_SERVER['HTTP_HOST'] = 'localhost:8080';
	}
	define( 'WP_USE_THEMES', false );
	require $root . '/wp-load.php';
}
$me_pages = require __DIR__ . '/seed-i18n-pages.php';
$me_posts = require __DIR__ . '/seed-i18n-posts.php';
$me_n     = 0;
foreach ( $me_pages as $path => $langs ) {
	$p = get_page_by_path( $path );
	if ( ! $p ) {
		continue;
	}
	foreach ( $langs as $l => $v ) {
		foreach ( array( 'titre' => $v[0], 'extrait' => $v[1], 'contenu' => $v[2] ) as $f => $val ) {
			if ( '' !== $val ) {
				update_post_meta( $p->ID, '_me_' . $l . '_' . $f, wp_slash( $val ) );
				$me_n++;
			}
		}
	}
}
$me_cats = $me_posts['_categories'];
unset( $me_posts['_categories'] );
foreach ( $me_posts as $key => $langs ) {
	list( $type, $slug ) = explode( ':', $key, 2 );
	$p = get_page_by_path( $slug, OBJECT, $type );
	if ( ! $p ) {
		continue;
	}
	foreach ( $langs as $l => $fields ) {
		foreach ( $fields as $f => $val ) {
			update_post_meta( $p->ID, '_me_' . $l . '_' . ltrim( $f, '_' ), wp_slash( $val ) );
			$me_n++;
		}
	}
}
foreach ( $me_cats as $name => $langs ) {
	$t = get_term_by( 'name', $name, 'category' );
	if ( ! $t ) {
		continue;
	}
	foreach ( $langs as $l => $val ) {
		update_term_meta( $t->term_id, 'me_nom_' . $l, $val );
		$me_n++;
	}
}
echo "Traductions enregistrées : $me_n champs.\n";
