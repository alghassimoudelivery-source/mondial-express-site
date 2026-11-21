<?php
/**
 * Référencement : méta-description, balises de partage (Open Graph, X), données structurées, robots.txt, sitemap.
 * Si une extension SEO (Yoast, Rank Math, SEOPress) est activée, le thème lui laisse la main.
 *
 * @package MondialExpress
 */

defined( 'ABSPATH' ) || exit;

function me_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'SEOPRESS_VERSION' );
}

/**
 * Champs SEO par contenu (titre SEO et méta-description), dans une boîte de l'éditeur.
 */
add_action(
	'add_meta_boxes',
	function () {
		if ( me_seo_plugin_active() ) {
			return;
		}
		foreach ( array( 'post', 'page', 'me_offre', 'me_forfait' ) as $pt ) {
			add_meta_box( 'me_seo', __( 'Référencement (SEO)', 'mondial-express' ), 'me_seo_box', $pt, 'normal', 'low' );
		}
	}
);
function me_seo_box( $post ) {
	wp_nonce_field( 'me_seo', 'me_seo_nonce' );
	$t = get_post_meta( $post->ID, '_me_seo_titre', true );
	$d = get_post_meta( $post->ID, '_me_seo_desc', true );
	echo '<p><label for="me_seo_titre"><strong>' . esc_html__( 'Titre SEO', 'mondial-express' ) . '</strong> (' . esc_html__( '60 caractères max., vide = titre de la page', 'mondial-express' ) . ')</label><br><input class="large-text" id="me_seo_titre" name="me_seo_titre" maxlength="70" value="' . esc_attr( $t ) . '"></p>';
	echo '<p><label for="me_seo_desc"><strong>' . esc_html__( 'Méta-description', 'mondial-express' ) . '</strong> (' . esc_html__( '155 caractères max., vide = extrait', 'mondial-express' ) . ')</label><br><textarea class="large-text" id="me_seo_desc" name="me_seo_desc" rows="2" maxlength="170">' . esc_textarea( $d ) . '</textarea></p>';
}
add_action(
	'save_post',
	function ( $id ) {
		if ( ! isset( $_POST['me_seo_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['me_seo_nonce'] ), 'me_seo' ) || ! current_user_can( 'edit_post', $id ) ) {
			return;
		}
		update_post_meta( $id, '_me_seo_titre', sanitize_text_field( wp_unslash( $_POST['me_seo_titre'] ?? '' ) ) );
		update_post_meta( $id, '_me_seo_desc', sanitize_textarea_field( wp_unslash( $_POST['me_seo_desc'] ?? '' ) ) );
	}
);

/**
 * Titre SEO personnalisé.
 */
add_filter(
	'pre_get_document_title',
	function ( $title ) {
		if ( me_seo_plugin_active() || ! is_singular() ) {
			return $title;
		}
		$t = get_post_meta( get_queried_object_id(), '_me_seo_titre', true );
		return $t ? $t : $title;
	}
);
add_filter( 'document_title_separator', fn() => '|' );

/**
 * Description de la page courante.
 */
function me_meta_description() {
	$default = get_bloginfo( 'description' );
	if ( is_singular() ) {
		$id = get_queried_object_id();
		$d  = get_post_meta( $id, '_me_seo_desc', true );
		if ( ! $d ) {
			$p = get_post( $id );
			$d = has_excerpt( $id ) ? get_the_excerpt( $id ) : wp_trim_words( wp_strip_all_tags( strip_shortcodes( $p->post_content ) ), 28, '…' );
		}
		return $d ? $d : $default;
	}
	if ( is_post_type_archive( 'me_offre' ) ) {
		return __( "Offres de billets d'avion au départ de Conakry : Dubaï, Paris, Istanbul, Casablanca… Demandez votre devis à Mondial Express.", 'mondial-express' );
	}
	if ( is_category() || is_tag() ) {
		$d = term_description();
		return $d ? wp_strip_all_tags( $d ) : $default;
	}
	return $default;
}

/**
 * Image de partage.
 */
function me_share_image() {
	if ( is_singular() && has_post_thumbnail() ) {
		return get_the_post_thumbnail_url( null, 'large' );
	}
	return ME_URI . '/assets/img/partage-mondial-express.jpg';
}

add_action(
	'wp_head',
	function () {
		if ( me_seo_plugin_active() ) {
			return;
		}
		$desc  = esc_attr( wp_strip_all_tags( me_meta_description() ) );
		$title = esc_attr( wp_get_document_title() );
		$url   = esc_url( is_singular() ? get_permalink() : home_url( add_query_arg( array(), $GLOBALS['wp']->request ?? '' ) ) );
		$img   = esc_url( me_share_image() );
		$type  = is_singular( 'post' ) ? 'article' : 'website';
		echo "\n<!-- SEO Mondial Express -->\n";
		echo '<meta name="description" content="' . $desc . '">' . "\n";
		echo '<meta property="og:locale" content="' . esc_attr( me_languages()[ me_lang() ][1] ) . '">' . "\n";
		echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";
		echo '<meta property="og:type" content="' . $type . '">' . "\n";
		echo '<meta property="og:title" content="' . $title . '">' . "\n";
		echo '<meta property="og:description" content="' . $desc . '">' . "\n";
		echo '<meta property="og:url" content="' . $url . '">' . "\n";
		echo '<meta property="og:image" content="' . $img . '">' . "\n";
		if ( ! ( is_singular() && has_post_thumbnail() ) ) {
			echo '<meta property="og:image:width" content="1200"><meta property="og:image:height" content="630">' . "\n";
		}
		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
		echo '<meta name="twitter:title" content="' . $title . '">' . "\n";
		echo '<meta name="twitter:description" content="' . $desc . '">' . "\n";
		echo '<meta name="twitter:image" content="' . $img . '">' . "\n";
		if ( is_singular( 'post' ) ) {
			echo '<meta property="article:published_time" content="' . esc_attr( get_the_date( 'c' ) ) . '">' . "\n";
			echo '<meta property="article:modified_time" content="' . esc_attr( get_the_modified_date( 'c' ) ) . '">' . "\n";
		}
		$x = me_opt( 'x' );
		if ( $x && preg_match( '#(?:x|twitter)\.com/([A-Za-z0-9_]+)#', $x, $m ) ) {
			echo '<meta name="twitter:site" content="@' . esc_attr( $m[1] ) . '">' . "\n";
		}
		me_json_ld();
	},
	3
);

/**
 * Données structurées JSON-LD.
 */
function me_json_ld() {
	$same = array_values( array_filter( array_map( 'me_opt', array_keys( me_social_networks() ) ) ) );
	$org  = array(
		'@type'       => 'TravelAgency',
		'@id'         => home_url( '/#agence' ),
		'name'        => get_bloginfo( 'name' ),
		'description' => get_bloginfo( 'description' ),
		'url'         => home_url( '/' ),
		'logo'        => ME_URI . '/assets/img/android-chrome-512.png',
		'image'       => ME_URI . '/assets/img/voyageuse.jpg',
		'telephone'   => me_opt( 'telephone' ),
		'slogan'      => 'Chaque voyage est une histoire !',
		'priceRange'  => '$$',
		'paymentAccepted' => implode( ', ', wp_list_pluck( me_payment_methods(), 0 ) ),
		'currenciesAccepted' => 'GNF',
		'areaServed'  => 'GN',
		'address'     => array_filter(
			array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => me_opt( 'adresse' ),
				'addressLocality' => 'Conakry',
				'addressCountry'  => 'GN',
			)
		),
	);
	if ( me_opt( 'email' ) ) {
		$org['email'] = me_opt( 'email' );
	}
	if ( $same ) {
		$org['sameAs'] = $same;
	}
	$graph = array(
		$org,
		array(
			'@type'     => 'WebSite',
			'@id'       => home_url( '/#site' ),
			'url'       => home_url( '/' ),
			'name'      => get_bloginfo( 'name' ),
			'inLanguage'=> 'fr-FR',
			'publisher' => array( '@id' => home_url( '/#agence' ) ),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => home_url( '/?s={search_term_string}' ),
				'query-input' => 'required name=search_term_string',
			),
		),
	);
	if ( is_singular( 'post' ) ) {
		$graph[] = array(
			'@type'            => 'BlogPosting',
			'headline'         => get_the_title(),
			'description'      => wp_strip_all_tags( me_meta_description() ),
			'datePublished'    => get_the_date( 'c' ),
			'dateModified'     => get_the_modified_date( 'c' ),
			'image'            => me_share_image(),
			'mainEntityOfPage' => get_permalink(),
			'author'           => array( '@type' => 'Organization', 'name' => get_bloginfo( 'name' ) ),
			'publisher'        => array( '@id' => home_url( '/#agence' ) ),
			'inLanguage'       => 'fr-FR',
		);
	}
	if ( is_singular( array( 'me_offre', 'me_forfait' ) ) ) {
		$prix = (int) get_post_meta( get_the_ID(), '_me_prix', true );
		$item = array(
			'@type'       => is_singular( 'me_forfait' ) ? 'TouristTrip' : 'Trip',
			'name'        => get_the_title(),
			'description' => wp_strip_all_tags( me_meta_description() ),
			'url'         => get_permalink(),
			'provider'    => array( '@id' => home_url( '/#agence' ) ),
		);
		if ( $prix ) {
			$item['offers'] = array( '@type' => 'Offer', 'price' => $prix, 'priceCurrency' => 'GNF', 'availability' => 'https://schema.org/InStock', 'url' => get_permalink() );
		}
		$graph[] = $item;
	}
	if ( ! is_front_page() && ( is_singular() || is_archive() ) ) {
		$items = array( array( '@type' => 'ListItem', 'position' => 1, 'name' => 'Accueil', 'item' => home_url( '/' ) ) );
		$items[] = array( '@type' => 'ListItem', 'position' => 2, 'name' => is_singular() ? get_the_title() : wp_strip_all_tags( get_the_archive_title() ) );
		$graph[] = array( '@type' => 'BreadcrumbList', 'itemListElement' => $items );
	}
	if ( is_page( 'faq' ) ) {
		$faq = array();
		foreach ( me_faq_items() as $q ) {
			$faq[] = array( '@type' => 'Question', 'name' => $q[0], 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $q[1] ) );
		}
		$graph[] = array( '@type' => 'FAQPage', 'mainEntity' => $faq );
	}
	echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graph ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}

/**
 * Questions fréquentes (page FAQ + données structurées).
 */
function me_faq_items() {
	$items = array(
		array( 'Comment demander un billet d’avion ?', 'Remplissez le formulaire de demande sur le site ou écrivez-nous sur WhatsApp au +224 613 00 39 70. Un conseiller vous envoie les meilleures options et le prix.' ),
		array( 'Quels documents faut-il pour une demande de visa ?', 'En général : passeport valide au moins 6 mois, photos d’identité, justificatifs de ressources, réservation d’hôtel et de vol, et selon le pays une attestation d’accueil ou une lettre de l’employeur. Nous vous donnons la liste exacte pour votre pays.' ),
		array( 'Combien de temps prend un visa ?', 'Cela dépend du pays et de la saison : de quelques jours pour certaines destinations à plusieurs semaines pour l’espace Schengen, le Canada ou les États-Unis. Déposez votre dossier le plus tôt possible.' ),
		array( 'Comment suivre mon dossier ?', 'Avec votre numéro de dossier et votre téléphone, sur la page Suivi de dossier, dans votre espace client ou dans l’application mobile Mondial Express.' ),
		array( 'Quels moyens de paiement acceptez-vous ?', '' ),
		array( 'Organisez-vous le Hajj et la Oumra ?', 'Oui, nous proposons chaque année des forfaits accompagnés : vols, visa, hébergement près des lieux saints, transferts et encadrement.' ),
	);
	// Textes traduits (fichiers de langue) ; la réponse sur les paiements suit les moyens cochés dans le back-office.
	foreach ( $items as $i => $it ) {
		$items[ $i ] = array( __( $it[0], 'mondial-express' ), $it[1] ? __( $it[1], 'mondial-express' ) : ( me_payment_methods() ? implode( ', ', wp_list_pluck( me_payment_methods(), 0 ) ) . '. ' : '' ) . me_opt( 'pay_note' ) ); // phpcs:ignore WordPress.WP.I18n
	}
	return $items;
}

/**
 * robots.txt : autorise l'exploration et indique le sitemap.
 */
add_filter(
	'robots_txt',
	function ( $output, $public ) {
		if ( ! $public ) {
			return $output;
		}
		$out  = "User-agent: *\n";
		$out .= "Disallow: /wp-admin/\n";
		$out .= "Allow: /wp-admin/admin-ajax.php\n";
		$out .= "Disallow: /mon-espace/\n";
		$out .= "Disallow: /?s=\n";
		$out .= "Disallow: /wp-content/uploads/documents-clients/\n";
		$out .= "Disallow: /app/\n\n";
		$out .= 'Sitemap: ' . home_url( '/wp-sitemap.xml' ) . "\n";
		return $out;
	},
	10,
	2
);

/**
 * Sitemap WordPress : on retire les pages techniques et les auteurs.
 */
add_filter(
	'wp_sitemaps_add_provider',
	fn( $provider, $name ) => 'users' === $name ? false : $provider,
	10,
	2
);
add_filter(
	'wp_sitemaps_posts_query_args',
	function ( $args, $post_type ) {
		if ( 'page' === $post_type ) {
			$ex = array();
			foreach ( array( 'mon-espace' ) as $slug ) {
				$p = get_page_by_path( $slug );
				if ( $p ) {
					$ex[] = $p->ID;
				}
			}
			$args['post__not_in'] = $ex;
		}
		return $args;
	},
	10,
	2
);
add_filter( 'wp_robots', function ( $robots ) {
	if ( is_page( 'mon-espace' ) || is_search() || is_404() ) {
		$robots['noindex'] = true;
	}
	return $robots;
} );

/**
 * Texte alternatif par défaut des images sans « alt » : le titre du contenu.
 */
add_filter(
	'wp_get_attachment_image_attributes',
	function ( $attr, $attachment ) {
		if ( empty( $attr['alt'] ) ) {
			$parent      = $attachment->post_parent ? get_the_title( $attachment->post_parent ) : '';
			$attr['alt'] = $parent ? $parent : get_the_title( $attachment );
		}
		return $attr;
	},
	10,
	2
);
