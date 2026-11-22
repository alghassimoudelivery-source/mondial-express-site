<?php
/**
 * Mise à jour « Présentation » (idempotente, lancée par setup.sh à chaque déploiement) :
 * - importe les photos de l'équipe depuis l'ancien site dans la médiathèque (une seule fois par photo) ;
 * - ajoute « L’agence » (page À propos) au menu principal si elle n'y est pas.
 * Usage : wp eval-file seed/presentation.php
 */
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$base   = 'https://media.mondialexpressgn.com/staff/avatars/';
$photos = array(
	'koundouno' => 'Mr_Koundouno_Edouard.png',
	'yha'       => 'Mr_Yha_Apholos.png',
	'balde'     => 'Mme_Bald%C3%A9_Sal%C3%A9matou.png',
	'kadouno'   => 'Mr_Kadouno_Raymond.png',
	'conte'     => 'Mme_Cont%C3%A9_Mariama.png',
	'camara'    => 'Mr_Camara_Eric.png',
	'deen'      => 'Mr_Deen_Manfred.png',
);
$saved = (array) get_option( 'me_equipe_photos', array() );
foreach ( $photos as $key => $file ) {
	if ( ! empty( $saved[ $key ] ) ) {
		continue;
	}
	$tmp = download_url( $base . $file, 30 );
	if ( is_wp_error( $tmp ) ) {
		WP_CLI::warning( "Photo $key non téléchargée : " . $tmp->get_error_message() );
		continue;
	}
	$id = media_handle_sideload( array( 'name' => 'equipe-' . $key . '.png', 'tmp_name' => $tmp ), 0, 'Équipe Mondial Express – ' . $key );
	if ( is_wp_error( $id ) ) {
		@unlink( $tmp ); // phpcs:ignore
		WP_CLI::warning( "Photo $key non importée : " . $id->get_error_message() );
		continue;
	}
	$img = wp_get_attachment_image_src( $id, 'medium_large' );
	$saved[ $key ] = $img ? $img[0] : wp_get_attachment_url( $id );
	update_option( 'me_equipe_photos', $saved, false );
}
WP_CLI::log( 'Photos de l’équipe : ' . count( $saved ) . ' / ' . count( $photos ) );

// Menu principal : « L’agence » juste après « Accueil ».
$about = get_page_by_path( 'a-propos' );
$locs  = get_theme_mod( 'nav_menu_locations', array() );
if ( $about && ! empty( $locs['principal'] ) ) {
	$items = wp_get_nav_menu_items( $locs['principal'] ) ?: array();
	$found = false;
	foreach ( $items as $it ) {
		if ( (int) $it->object_id === (int) $about->ID && 'page' === $it->object ) {
			$found = true;
		}
	}
	if ( ! $found ) {
		foreach ( $items as $it ) {
			if ( ! $it->menu_item_parent && $it->menu_order >= 2 ) {
				wp_update_post( array( 'ID' => $it->ID, 'menu_order' => $it->menu_order + 1 ) );
			}
		}
		wp_update_nav_menu_item( $locs['principal'], 0, array( 'menu-item-status' => 'publish', 'menu-item-title' => 'L’agence', 'menu-item-object' => 'page', 'menu-item-object-id' => $about->ID, 'menu-item-type' => 'post_type', 'menu-item-position' => 2 ) );
		WP_CLI::log( 'Menu : « L’agence » ajoutée.' );
	}
}

// Logos des compagnies partenaires (ancien site), importés une seule fois chacun.
$abase = 'https://mondialexpressgn.com/assets/images/airlines/';
$logos = (array) get_option( 'me_partenaires_logos', array() );
$pres  = function_exists( 'me_presentation' ) ? me_presentation() : array( 'partenaires' => array() );
foreach ( $pres['partenaires'] as $a ) {
	list( $nom, $code, $file ) = array_pad( $a, 3, '' );
	if ( ! $file || ! empty( $logos[ $code ] ) ) {
		continue;
	}
	$tmp = download_url( $abase . $file, 30 );
	if ( is_wp_error( $tmp ) ) {
		WP_CLI::warning( "Logo $code non téléchargé : " . $tmp->get_error_message() );
		continue;
	}
	$id = media_handle_sideload( array( 'name' => 'compagnie-' . strtolower( $code ) . '-' . $file, 'tmp_name' => $tmp ), 0, 'Logo ' . $nom );
	if ( is_wp_error( $id ) ) {
		@unlink( $tmp ); // phpcs:ignore
		WP_CLI::warning( "Logo $code non importé : " . $id->get_error_message() );
		continue;
	}
	update_post_meta( $id, '_wp_attachment_image_alt', 'Logo ' . $nom );
	$img = wp_get_attachment_image_src( $id, 'medium' );
	$logos[ $code ] = $img ? $img[0] : wp_get_attachment_url( $id );
	update_option( 'me_partenaires_logos', $logos, false );
}
WP_CLI::log( 'Logos des compagnies : ' . count( $logos ) . ' / ' . count( $pres['partenaires'] ) );

// Réseaux sociaux du client (complétés seulement s'ils sont vides dans le Personnaliseur).
foreach ( array(
	'facebook'  => 'https://www.facebook.com/profile.php?id=61566663756158',
	'instagram' => 'https://www.instagram.com/mondial_express/',
	'tiktok'    => 'https://www.tiktok.com/@mondial.express',
) as $k => $url ) {
	if ( '' === (string) get_theme_mod( 'me_' . $k, '' ) ) {
		set_theme_mod( 'me_' . $k, $url );
	}
}
WP_CLI::log( 'Réseaux sociaux : Facebook, Instagram, TikTok renseignés.' );
