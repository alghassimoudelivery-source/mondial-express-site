<?php
/**
 * Fonctions utilitaires du thème.
 *
 * @package MondialExpress
 */

defined( 'ABSPATH' ) || exit;

/**
 * Valeurs par défaut des réglages de l'agence (modifiables dans Apparence > Personnaliser > Mondial Express).
 */
function me_defaults() {
	return array(
		'telephone'      => '+224 613 00 39 70',
		'whatsapp'       => '224613003970',
		'email'          => '',
		'adresse'        => '',
		'horaires'       => 'Lun – Sam : 8h – 19h',
		'facebook'       => '',
		'x'              => '',
		'instagram'      => '',
		'tiktok'         => '',
		'youtube'        => '',
		'app_android'    => '',
		'app_ios'        => '',
		'code_entete'    => '',
		'code_pied'      => '',
		'carte'          => '',
		'pay_orange'     => 1,
		'pay_mtn'        => 1,
		'pay_carte'      => 1,
		'pay_especes'    => 1,
		'pay_virement'   => 1,
		'pay_note'       => 'Le moyen de paiement est confirmé avec votre conseiller au moment de la réservation.',
		'stat_voyageurs' => '+5 000',
		'stat_dest'      => '40+',
		'stat_visas'     => '98 %',
	);
}

/**
 * Lit un réglage de l'agence.
 */
function me_opt( $key ) {
	$d = me_defaults();
	$v = get_theme_mod( 'me_' . $key, $d[ $key ] ?? '' );
	// Textes de l'agence traduits par les fichiers de langue quand ils correspondent au texte par défaut.
	if ( is_string( $v ) && '' !== $v && in_array( $key, array( 'horaires', 'pay_note', 'adresse' ), true ) && function_exists( 'me_lang' ) && 'fr' !== me_lang() ) {
		$v = __( $v, 'mondial-express' ); // phpcs:ignore WordPress.WP.I18n
	}
	return $v;
}

/**
 * Lien WhatsApp avec message prérempli.
 */
function me_whatsapp_url( $message = '' ) {
	$num = preg_replace( '/\D/', '', (string) me_opt( 'whatsapp' ) );
	$msg = $message ? $message : __( 'Bonjour Mondial Express, je souhaite des informations pour un voyage.', 'mondial-express' );
	return 'https://wa.me/' . $num . '?text=' . rawurlencode( $msg );
}

/**
 * Lien téléphone.
 */
function me_tel_url() {
	return 'tel:' . preg_replace( '/[^\d+]/', '', (string) me_opt( 'telephone' ) );
}

/**
 * Icônes SVG en ligne (aucune requête externe).
 */
function me_icon( $name, $class = 'ico' ) {
	$p = array(
		'plane'    => '<path fill="currentColor" d="M21 16v-2l-8-5V3.5a1.5 1.5 0 0 0-3 0V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5z"/>',
		'pass'     => '<g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="5" y="3" width="14" height="18" rx="2"/><circle cx="12" cy="10" r="3"/><path d="M9 16h6"/></g>',
		'bed'      => '<g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 19V7M3 15h18v4M21 15v-3a3 3 0 0 0-3-3h-7v6"/><circle cx="7" cy="11" r="2"/></g>',
		'kaaba'    => '<g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"><path d="M4 7l8-4 8 4v10l-8 4-8-4z"/><path d="M4 7l8 4 8-4M12 11v10M4 10.5l8 4 8-4"/></g>',
		'med'      => '<path fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" d="M9.5 4h5v5.5H20v5h-5.5V20h-5v-5.5H4v-5h5.5z"/>',
		'case'     => '<g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2M3 12h18"/></g>',
		'whatsapp' => '<path fill="currentColor" d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm5.5 14.1c-.2.6-1.3 1.2-1.8 1.3-.5.1-1 .1-1.7-.1a15 15 0 0 1-1.6-.6 12.2 12.2 0 0 1-4.6-4.1 5.3 5.3 0 0 1-1.1-2.8c0-1.3.7-2 1-2.3.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.9 2.1c.1.2.1.3 0 .5l-.3.5-.4.5c-.1.1-.3.3-.1.6a8.6 8.6 0 0 0 1.6 2 7.7 7.7 0 0 0 2.3 1.4c.3.1.5.1.6-.1l.9-1c.2-.3.4-.2.6-.1l2 1c.3.1.4.2.5.3 0 .1 0 .7-.2 1.3z"/>',
		'facebook' => '<path fill="currentColor" d="M14 8.5V7c0-.6.4-1 1-1h2V2.5h-2.8A4.2 4.2 0 0 0 10 6.7v1.8H7.5V12H10v9.5h4V12h2.8l.7-3.5z"/>',
		'x'        => '<path fill="currentColor" d="M17.8 3h3.1l-6.8 7.7 8 10.3h-6.2l-4.9-6.3L5.4 21H2.3l7.3-8.3L2 3h6.4l4.4 5.8zm-1.1 16.2h1.7L7.4 4.7H5.6z"/>',
		'instagram'=> '<g fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/></g><circle cx="17.3" cy="6.7" r="1.2" fill="currentColor"/>',
		'tiktok'   => '<path fill="currentColor" d="M16.5 2h-3.3v13.2a2.9 2.9 0 1 1-2.1-2.8V9a6.2 6.2 0 1 0 5.4 6.2V8.6a7.6 7.6 0 0 0 4.4 1.4V6.7a4.4 4.4 0 0 1-4.4-4.7z"/>',
		'youtube'  => '<path fill="currentColor" d="M22 8.2a3 3 0 0 0-2.1-2.1C18 5.6 12 5.6 12 5.6s-6 0-7.9.5A3 3 0 0 0 2 8.2 31 31 0 0 0 1.6 12 31 31 0 0 0 2 15.8a3 3 0 0 0 2.1 2.1c1.9.5 7.9.5 7.9.5s6 0 7.9-.5a3 3 0 0 0 2.1-2.1c.3-1.2.4-2.5.4-3.8s-.1-2.6-.4-3.8zM10 15V9l5.2 3z"/>',
		'linkedin' => '<path fill="currentColor" d="M4.98 3.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5zM3 9.5h4V21H3zM9.5 9.5h3.8v1.6h.1c.5-1 1.8-2 3.8-2 4 0 4.8 2.6 4.8 6V21h-4v-5.1c0-1.2 0-2.8-1.7-2.8s-2 1.3-2 2.7V21h-4z"/>',
		'search'   => '<g fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="11" cy="11" r="6.5"/><path d="M16 16l4.5 4.5"/></g>',
		'arrow'    => '<path fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/>',
		'user'     => '<g fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></g>',
		'phone'    => '<path fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/>',
		'mail'     => '<g fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></g>',
		'pin'      => '<g fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s7-6.2 7-12a7 7 0 0 0-14 0c0 5.8 7 12 7 12z"/><circle cx="12" cy="9" r="2.5"/></g>',
		'clock'    => '<g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></g>',
		'star'     => '<path fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" d="M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9z"/>',
		'shield'   => '<g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l8 3v6c0 4.5-3.4 8-8 9-4.6-1-8-4.5-8-9V6z"/><path d="M8.5 12l2.5 2.5 4.5-5"/></g>',
		'heart'    => '<path fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" d="M12 20s-7.5-4.6-7.5-10A4.2 4.2 0 0 1 12 7.6 4.2 4.2 0 0 1 19.5 10c0 5.4-7.5 10-7.5 10z"/>',
		'users'    => '<g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="9" cy="8" r="3.2"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/><circle cx="17" cy="9" r="2.6"/><path d="M16 14.2c2.9.3 5 2.6 5 5.8"/></g>',
		'quote'    => '<path fill="currentColor" d="M10 7H6a3 3 0 0 0-3 3v4h4v-2.5A1.5 1.5 0 0 1 8.5 10H10zm11 0h-4a3 3 0 0 0-3 3v4h4v-2.5a1.5 1.5 0 0 1 1.5-1.5H21z"/>',
		'check'    => '<path fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" d="M5 12.5l4.5 4.5L19 7.5"/>',
		'share'    => '<g fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="2.5"/><circle cx="6" cy="12" r="2.5"/><circle cx="18" cy="19" r="2.5"/><path d="M8.2 10.8l7.6-4.4M8.2 13.2l7.6 4.4"/></g>',
		'link'     => '<path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" d="M10 14a5 5 0 0 0 7 0l3-3a5 5 0 0 0-7-7l-1 1M14 10a5 5 0 0 0-7 0l-3 3a5 5 0 0 0 7 7l1-1"/>',
		'play'     => '<path fill="currentColor" d="M4 2.8v18.4c0 .6.6 1 1.1.7l15.6-9.2c.5-.3.5-1 0-1.3L5.1 2.1C4.6 1.8 4 2.2 4 2.8z"/>',
		'apple'    => '<path fill="currentColor" d="M16.4 12.6c0-2.6 2.1-3.8 2.2-3.9-1.2-1.8-3.1-2-3.7-2-1.6-.2-3.1.9-3.9.9s-2-.9-3.4-.9a5 5 0 0 0-4.2 2.6c-1.8 3.1-.5 7.7 1.3 10.2.8 1.2 1.8 2.6 3.1 2.5 1.3-.1 1.7-.8 3.2-.8s1.9.8 3.2.8c1.3 0 2.2-1.2 3-2.5a10 10 0 0 0 1.4-2.8c-.1 0-2.2-.9-2.2-4.1zM13.9 5c.7-.8 1.2-2 1-3.1-1 0-2.2.7-2.9 1.5-.6.7-1.2 1.9-1 3 1.1.1 2.2-.6 2.9-1.4z"/>',
		'upload'   => '<path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 16V4M7 9l5-5 5 5M4 16v3a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-3"/>',
		'bell'     => '<path fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" d="M6 16V11a6 6 0 0 1 12 0v5l2 2H4zM10 20.5a2 2 0 0 0 4 0"/>',
		'folder'   => '<path fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" d="M3 6.5A1.5 1.5 0 0 1 4.5 5H9l2 2.5h8.5A1.5 1.5 0 0 1 21 9v9.5a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 18.5z"/>',
		'card'     => '<g fill="none" stroke="currentColor" stroke-width="2"><rect x="2.5" y="5" width="19" height="14" rx="2"/><path d="M2.5 10h19M6 15h4"/></g>',
	);
	if ( ! isset( $p[ $name ] ) ) {
		return '';
	}
	return '<svg class="' . esc_attr( $class ) . '" data-i="' . esc_attr( $name ) . '" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . $p[ $name ] . '</svg>';
}

/**
 * Logo de l'agence (logo personnalisé WordPress ou logo du thème).
 */
function me_logo( $variant = 'principal' ) {
	if ( 'principal' === $variant && has_custom_logo() ) {
		return get_custom_logo();
	}
	$files = array(
		'principal' => 'logo-mondial-express-sans-slogan.svg',
		'slogan'    => 'logo-mondial-express.svg',
		'mobile'    => 'logo-mondial-express-petits-ecrans.svg',
		'blanc'     => 'logo-mondial-express-blanc.svg',
	);
	$src = ME_URI . '/assets/img/' . ( $files[ $variant ] ?? $files['principal'] );
	return sprintf(
		'<a class="logo" href="%1$s" rel="home"><img src="%2$s" alt="%3$s" width="300" height="85"></a>',
		esc_url( home_url( '/' ) ),
		esc_url( $src ),
		esc_attr__( 'Mondial Express – Accueil', 'mondial-express' )
	);
}

/**
 * Réseaux sociaux de l'agence. Les boutons restent affichés même sans lien (à compléter dans le Personnaliseur).
 */
function me_social_networks() {
	return array(
		'facebook'  => 'Facebook',
		'x'         => 'X (Twitter)',
		'instagram' => 'Instagram',
		'tiktok'    => 'TikTok',
		'youtube'   => 'YouTube',
	);
}

function me_socials( $class = 'socials' ) {
	$out = '<ul class="' . esc_attr( $class ) . '">';
	foreach ( me_social_networks() as $key => $label ) {
		$url  = me_opt( $key );
		$attr = $url ? 'href="' . esc_url( $url ) . '" target="_blank" rel="noopener"' : 'href="#" data-a-completer="1" aria-disabled="true"';
		/* translators: %s: réseau social */
		$out .= '<li><a ' . $attr . ' aria-label="' . esc_attr( sprintf( __( 'Mondial Express sur %s', 'mondial-express' ), $label ) ) . '">' . me_icon( $key ) . '</a></li>';
	}
	$out .= '<li><a href="' . esc_url( me_whatsapp_url() ) . '" target="_blank" rel="noopener" aria-label="WhatsApp">' . me_icon( 'whatsapp' ) . '</a></li>';
	return $out . '</ul>';
}

/**
 * Boutons de partage (articles, offres, forfaits).
 */
function me_share_buttons( $post_id = null ) {
	$url   = rawurlencode( get_permalink( $post_id ) );
	$title = rawurlencode( get_the_title( $post_id ) );
	$links = array(
		'whatsapp' => array( 'WhatsApp', 'https://wa.me/?text=' . $title . '%20' . $url ),
		'facebook' => array( 'Facebook', 'https://www.facebook.com/sharer/sharer.php?u=' . $url ),
		'x'        => array( 'X', 'https://twitter.com/intent/tweet?url=' . $url . '&text=' . $title ),
		'linkedin' => array( 'LinkedIn', 'https://www.linkedin.com/sharing/share-offsite/?url=' . $url ),
	);
	$out = '<div class="share"><span class="share__label">' . esc_html__( 'Partager', 'mondial-express' ) . '</span>';
	foreach ( $links as $k => $l ) {
		/* translators: %s: réseau */
		$out .= '<a class="share__btn share__btn--' . $k . '" href="' . esc_url( $l[1] ) . '" target="_blank" rel="noopener" aria-label="' . esc_attr( sprintf( __( 'Partager sur %s', 'mondial-express' ), $l[0] ) ) . '">' . me_icon( $k ) . '</a>';
	}
	$out .= '<button type="button" class="share__btn share__copy" data-url="' . esc_url( get_permalink( $post_id ) ) . '" aria-label="' . esc_attr__( 'Copier le lien', 'mondial-express' ) . '">' . me_icon( 'link' ) . '</button>';
	return $out . '</div>';
}

/**
 * Boutons de téléchargement de l'application.
 */
function me_app_badges() {
	$stores = array(
		array( me_opt( 'app_android' ), 'play', __( 'Disponible sur', 'mondial-express' ), 'Google Play' ),
		array( me_opt( 'app_ios' ), 'apple', __( 'Télécharger dans', 'mondial-express' ), "l'App Store" ),
	);
	$out = '<div class="stores">';
	foreach ( $stores as $s ) {
		$href = $s[0] ? esc_url( $s[0] ) : esc_url( home_url( '/application-mobile/' ) );
		$out .= '<a class="store" href="' . $href . '">' . me_icon( $s[1] ) . '<span><small>' . esc_html( $s[2] ) . '</small><b>' . esc_html( $s[3] ) . '</b></span></a>';
	}
	return $out . '</div>';
}

/**
 * Moyens de paiement acceptés (cochés dans Apparence > Personnaliser > Mondial Express > Paiements acceptés).
 *
 * @return array clé => array( libellé, précision, classe ).
 */
function me_payment_methods() {
	$all = array(
		'orange'   => array( 'Orange Money', __( 'Paiement mobile', 'mondial-express' ), 'om' ),
		'mtn'      => array( 'MTN MoMo', __( 'Mobile Money', 'mondial-express' ), 'mtn' ),
		'carte'    => array( __( 'Carte bancaire', 'mondial-express' ), 'Visa · Mastercard', 'card' ),
		'especes'  => array( __( 'Espèces', 'mondial-express' ), __( 'À l’agence', 'mondial-express' ), 'cash' ),
		'virement' => array( __( 'Virement bancaire', 'mondial-express' ), __( 'Entreprises et ONG', 'mondial-express' ), 'bank' ),
	);
	return array_filter( $all, fn( $k ) => (bool) me_opt( 'pay_' . $k ), ARRAY_FILTER_USE_KEY );
}

/**
 * Pastilles des moyens de paiement.
 *
 * @param bool $detail Affiche la précision sous chaque moyen.
 */
function me_payment_badges( $detail = false, $class = '' ) {
	$methods = me_payment_methods();
	if ( ! $methods ) {
		return '';
	}
	$marks = array(
		'om'   => '<span class="pay__mark">OM</span>',
		'mtn'  => '<span class="pay__mark">MoMo</span>',
		'card' => me_icon( 'card' ),
		'cash' => '<svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><g fill="none" stroke="currentColor" stroke-width="2"><rect x="2.5" y="6" width="19" height="12" rx="2"/><circle cx="12" cy="12" r="2.6"/><path d="M6 9.5v5M18 9.5v5"/></g></svg>',
		'bank' => '<svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" d="M3 9.5L12 4l9 5.5M5 10v7M9.5 10v7M14.5 10v7M19 10v7M3 20h18"/></svg>',
	);
	$out = '<ul class="pay ' . esc_attr( $class ) . '" aria-label="' . esc_attr__( 'Moyens de paiement acceptés', 'mondial-express' ) . '">';
	foreach ( $methods as $m ) {
		$out .= '<li class="pay__item pay__item--' . esc_attr( $m[2] ) . '">' . $marks[ $m[2] ] . '<span><b>' . esc_html( $m[0] ) . '</b>' . ( $detail ? '<small>' . esc_html( $m[1] ) . '</small>' : '' ) . '</span></li>';
	}
	return $out . '</ul>';
}

/**
 * Encadré « Paiements acceptés » (sous les formulaires de réservation).
 */
function me_payment_box() {
	if ( ! me_payment_methods() ) {
		return '';
	}
	return '<div class="pay-box"><p class="pay-box__title">' . me_icon( 'card' ) . ' ' . esc_html__( 'Paiements acceptés', 'mondial-express' ) . '</p>' . me_payment_badges() . ( me_opt( 'pay_note' ) ? '<p class="pay-note">' . esc_html( me_opt( 'pay_note' ) ) . '</p>' : '' ) . '</div>';
}

/**
 * Les six services de l'agence.
 */
function me_services() {
	return array(
		'billetterie'        => array( 'plane', __( 'Billetterie', 'mondial-express' ), __( 'Toutes compagnies, meilleurs tarifs', 'mondial-express' ), 'orange' ),
		'assistance-visa'    => array( 'pass', __( 'Assistance visa', 'mondial-express' ), __( 'Schengen, Canada, USA, Turquie…', 'mondial-express' ), 'vert' ),
		'hotels-tourisme'    => array( 'bed', __( 'Hôtels & tourisme', 'mondial-express' ), __( 'Séjours et circuits clés en main', 'mondial-express' ), 'violet' ),
		'hajj-oumra'         => array( 'kaaba', __( 'Hajj & Oumra', 'mondial-express' ), __( 'Forfaits accompagnés', 'mondial-express' ), 'orange' ),
		'evacuation-sanitaire' => array( 'med', __( 'Transport & évacuation', 'mondial-express' ), __( 'Évacuation sanitaire urgente', 'mondial-express' ), 'vert' ),
		'voyages-affaires'   => array( 'case', __( "Voyages d'affaires", 'mondial-express' ), __( 'Entreprises et ONG', 'mondial-express' ), 'violet' ),
	);
}

/**
 * Silhouettes de villes pour les visuels de destination sans photo.
 */
function me_skyline( $name ) {
	$s = array(
		'dubai'  => 'M0 70V50h20V38h14v12h12V30h10v20h16V44h10V20l4-18 4 18v24h12V34h18v16h14V26h16v24h20V40h12v10h18V30l8-6 8 6v20h14V44h20v26z',
		'paris'  => 'M0 70V48h30V40h26v8h34V44h22v-6l12-30 2-8 2 8 12 30v6h4v-6h24v10h30V36h26v12h30V42h46v28z',
		'ist'    => 'M0 70V50h24V12h4v38h18V38a26 26 0 0 1 52 0v12h18V12h4v38h40V42h22v8h22V18h4v32h20V40h30v10h42v20z',
		'mecque' => 'M0 70V52h40V24h6v28h30V40h22v-8h30v8h40V8l3-8 3 8v44h26V30h6v22h50V44h44v26z',
		'ville'  => 'M0 70V46h26V30h22v16h18V36h30v10h20V20h28v26h24V34h22v12h30V28h26v18h30V40h24v30z',
	);
	$d = $s[ $name ] ?? $s['ville'];
	return '<svg class="skyline" viewBox="0 0 300 70" preserveAspectRatio="none" aria-hidden="true"><path fill="currentColor" d="' . $d . '"/></svg>';
}

/**
 * Visuel d'une offre : image à la une ou dégradé + silhouette.
 */
function me_offer_visual( $post_id, $size = 'me-card' ) {
	if ( has_post_thumbnail( $post_id ) ) {
		return '<div class="dest__img">' . get_the_post_thumbnail( $post_id, $size, array( 'loading' => 'lazy' ) ) . '</div>';
	}
	$theme = get_post_meta( $post_id, '_me_visuel', true );
	$theme = $theme ? $theme : 'orange';
	$sky   = array( 'orange' => 'dubai', 'bleu' => 'paris', 'violet' => 'ist', 'vert' => 'mecque' );
	return '<div class="dest__img dest__img--' . esc_attr( $theme ) . '">' . me_skyline( $sky[ $theme ] ?? 'ville' ) . '</div>';
}

/**
 * Prix formaté en francs guinéens.
 */
function me_price( $value ) {
	$value = (int) preg_replace( '/\D/', '', (string) $value );
	if ( ! $value ) {
		return '';
	}
	return number_format( $value, 0, ',', "\u{00A0}" ) . "\u{00A0}GNF"; // Espaces insécables : le prix ne se coupe pas.
}

/**
 * Mention « prix indicatif ».
 */
function me_price_note( $post_id ) {
	return get_post_meta( $post_id, '_me_prix_indicatif', true ) ? '<span class="price-note">' . esc_html__( 'prix indicatif', 'mondial-express' ) . '</span>' : '';
}

/**
 * Fil d'Ariane simple.
 */
function me_breadcrumb() {
	if ( is_front_page() ) {
		return;
	}
	echo '<nav class="breadcrumb" aria-label="' . esc_attr__( "Fil d'Ariane", 'mondial-express' ) . '"><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Accueil', 'mondial-express' ) . '</a>';
	if ( is_singular( 'post' ) ) {
		$blog = get_option( 'page_for_posts' );
		if ( $blog ) {
			echo ' <span>›</span> <a href="' . esc_url( get_permalink( $blog ) ) . '">' . esc_html( get_the_title( $blog ) ) . '</a>';
		}
	} elseif ( is_singular( 'me_offre' ) ) {
		echo ' <span>›</span> <a href="' . esc_url( get_post_type_archive_link( 'me_offre' ) ) . '">' . esc_html__( 'Destinations', 'mondial-express' ) . '</a>';
	} elseif ( is_singular( 'me_forfait' ) ) {
		$p = get_page_by_path( 'hajj-oumra' );
		if ( $p ) {
			echo ' <span>›</span> <a href="' . esc_url( get_permalink( $p ) ) . '">' . esc_html( get_the_title( $p ) ) . '</a>';
		}
	} elseif ( is_page() ) {
		foreach ( array_reverse( get_post_ancestors( get_the_ID() ) ) as $anc ) {
			echo ' <span>›</span> <a href="' . esc_url( get_permalink( $anc ) ) . '">' . esc_html( get_the_title( $anc ) ) . '</a>';
		}
	}
	if ( is_404() ) {
		$cur = __( 'Page introuvable', 'mondial-express' );
	} elseif ( is_search() ) {
		$cur = __( 'Recherche', 'mondial-express' );
	} elseif ( is_post_type_archive() ) {
		$cur = post_type_archive_title( '', false );
	} elseif ( is_archive() ) {
		$cur = wp_strip_all_tags( get_the_archive_title() );
	} elseif ( is_home() ) {
		$cur = single_post_title( '', false );
	} else {
		$cur = get_the_title();
	}
	echo ' <span>›</span> <span aria-current="page">' . esc_html( $cur ) . '</span></nav>';
}

/**
 * En-tête de page intérieure.
 */
function me_page_hero( $title, $subtitle = '', $kicker = '' ) {
	echo '<header class="page-hero"><div class="container">';
	me_breadcrumb();
	if ( $kicker ) {
		echo '<p class="kicker">' . esc_html( $kicker ) . '</p>';
	}
	echo '<h1>' . wp_kses_post( $title ) . '</h1>';
	if ( $subtitle ) {
		echo '<p class="page-hero__sub">' . wp_kses_post( $subtitle ) . '</p>';
	}
	echo '</div></header>';
}
