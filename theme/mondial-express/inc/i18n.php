<?php
/**
 * Multilingue intégré : français (par défaut), anglais, espagnol, arabe (de droite à gauche).
 *
 * - Adresses : /en/…, /es/…, /ar/… ; le français garde les adresses sans préfixe.
 * - Textes du thème : fichiers languages/{locale}.po / .mo (modifiables avec Loco Translate ou Poedit).
 * - Contenus : encadré « Traductions » dans l'éditeur (titre, extrait, contenu et champs utiles).
 * - Application et API : paramètre ?lang=en|es|ar.
 *
 * @package MondialExpress
 */

defined( 'ABSPATH' ) || exit;

/**
 * Langues du site : code => array( nom, locale, sens ).
 */
function me_languages() {
	return array(
		'fr' => array( 'Français', 'fr_FR', 'ltr' ),
		'en' => array( 'English', 'en_US', 'ltr' ),
		'es' => array( 'Español', 'es_ES', 'ltr' ),
		'ar' => array( 'العربية', 'ar', 'rtl' ),
	);
}

function me_lang() {
	return $GLOBALS['me_lang'] ?? 'fr';
}

function me_is_rtl() {
	return 'rtl' === me_languages()[ me_lang() ][2];
}

/**
 * Détection de la langue, avant que WordPress n'analyse l'adresse.
 */
( function () {
	$GLOBALS['me_lang']      = 'fr';
	$GLOBALS['me_lang_path'] = '/';
	if ( is_admin() && ! wp_doing_ajax() ) {
		return;
	}
	$langs = array_keys( me_languages() );
	// API (application) : ?lang=xx.
	if ( isset( $_GET['lang'] ) && in_array( $_GET['lang'], $langs, true ) ) { // phpcs:ignore WordPress.Security
		$GLOBALS['me_lang'] = sanitize_key( $_GET['lang'] ); // phpcs:ignore WordPress.Security
	}
	if ( empty( $_SERVER['REQUEST_URI'] ) ) {
		return;
	}
	$home = rtrim( (string) wp_parse_url( get_option( 'home' ), PHP_URL_PATH ), '/' );
	$uri  = wp_unslash( $_SERVER['REQUEST_URI'] ); // phpcs:ignore WordPress.Security
	$rest = substr( $uri, strlen( $home ) );
	if ( preg_match( '#^/(en|es|ar)(/|\?|$)#', $rest, $m ) ) {
		$GLOBALS['me_lang']      = $m[1];
		$rest                    = substr( $rest, strlen( $m[1] ) + 1 );
		$rest                    = ( '' === $rest || '?' === $rest[0] ) ? '/' . $rest : $rest;
		$_SERVER['REQUEST_URI']  = $home . $rest;
	}
	$GLOBALS['me_lang_path'] = strtok( $rest, '?' );
} )();

/**
 * Langue de WordPress (textes du thème) selon la langue demandée.
 */
add_filter(
	'locale',
	function ( $locale ) {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return $locale;
		}
		// Le français est la langue source du thème : on force fr_FR (même si WordPress est installé en anglais).
		return me_languages()[ me_lang() ][1];
	}
);

/**
 * Les liens internes gardent la langue choisie.
 */
add_filter(
	'home_url',
	function ( $url, $path ) {
		if ( 'fr' === me_lang() || ( is_admin() && ! wp_doing_ajax() ) ) {
			return $url;
		}
		$p = ltrim( (string) $path, '/' );
		if ( preg_match( '#^(wp-|app(/|$)|feed|xmlrpc|\?rest_route)#', $p ) || ( function_exists( 'rest_get_url_prefix' ) && 0 === strpos( $p, rest_get_url_prefix() ) ) ) {
			return $url;
		}
		return me_lang_url_for( $url, me_lang() );
	},
	10,
	2
);

/**
 * Ajoute (ou retire) le préfixe de langue d'une adresse du site.
 */
function me_lang_url_for( $url, $lang ) {
	remove_all_filters( 'me_home_raw' );
	$home = untrailingslashit( get_option( 'home' ) );
	if ( 0 !== strpos( $url, $home ) ) {
		return $url;
	}
	$rest = substr( $url, strlen( $home ) );
	$rest = preg_replace( '#^/(en|es|ar)(?=/|\?|$)#', '', $rest );
	$rest = '' === $rest ? '/' : $rest;
	return $home . ( 'fr' === $lang ? '' : '/' . $lang ) . $rest;
}

/**
 * Adresse de la page actuelle dans une autre langue.
 */
function me_switch_url( $lang ) {
	$home = untrailingslashit( get_option( 'home' ) );
	$qs   = isset( $_SERVER['QUERY_STRING'] ) && '' !== $_SERVER['QUERY_STRING'] ? '?' . wp_unslash( $_SERVER['QUERY_STRING'] ) : ''; // phpcs:ignore WordPress.Security
	$qs   = preg_replace( '/(^\?|&)lang=[a-z]{2}/', '$1', $qs );
	$qs   = in_array( $qs, array( '?', '' ), true ) ? '' : $qs;
	return esc_url( $home . ( 'fr' === $lang ? '' : '/' . $lang ) . $GLOBALS['me_lang_path'] . $qs );
}

/**
 * Pas de redirection « canonique » sur les adresses traduites (elles seraient renvoyées vers le français).
 */
add_filter( 'redirect_canonical', fn( $url ) => 'fr' === me_lang() ? $url : false, 5 );

/**
 * Langue et sens de lecture de la page.
 */
add_filter(
	'language_attributes',
	function ( $out ) {
		if ( is_admin() ) {
			return $out;
		}
		$code = 'fr' === me_lang() ? 'fr-FR' : str_replace( '_', '-', me_languages()[ me_lang() ][1] );
		return 'lang="' . esc_attr( $code ) . '" dir="' . ( me_is_rtl() ? 'rtl' : 'ltr' ) . '"';
	}
);
add_filter( 'body_class', fn( $c ) => array_merge( $c, array( 'lang-' . me_lang() ), me_is_rtl() ? array( 'rtl' ) : array() ) );

/**
 * Balises hreflang pour Google.
 */
add_action(
	'wp_head',
	function () {
		if ( is_404() || is_search() ) {
			return;
		}
		foreach ( me_languages() as $code => $l ) {
			echo '<link rel="alternate" hreflang="' . esc_attr( $code ) . '" href="' . me_switch_url( $code ) . '">' . "\n"; // phpcs:ignore
		}
		echo '<link rel="alternate" hreflang="x-default" href="' . me_switch_url( 'fr' ) . '">' . "\n"; // phpcs:ignore
	},
	2
);

/**
 * Sélecteur de langue.
 */
function me_lang_switcher( $class = '' ) {
	$cur = me_lang();
	$out = '<div class="lang ' . esc_attr( $class ) . '"><button class="lang__btn" type="button" aria-expanded="false" aria-haspopup="true" aria-label="' . esc_attr__( 'Choisir la langue', 'mondial-express' ) . '"><svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><g fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 3.8 5.6 3.8 9s-1.3 6.4-3.8 9c-2.5-2.6-3.8-5.6-3.8-9S9.5 5.6 12 3z"/></g></svg><span>' . esc_html( strtoupper( $cur ) ) . '</span></button><ul class="lang__menu">';
	foreach ( me_languages() as $code => $l ) {
		$out .= '<li><a href="' . me_switch_url( $code ) . '" hreflang="' . esc_attr( $code ) . '" lang="' . esc_attr( $code ) . '"' . ( $code === $cur ? ' aria-current="true"' : '' ) . '><b>' . esc_html( strtoupper( $code ) ) . '</b> ' . esc_html( $l[0] ) . '</a></li>';
	}
	return $out . '</ul></div>';
}

/* ------------------------------------------------------------------ */
/* Traduction des contenus                                             */
/* ------------------------------------------------------------------ */

/**
 * Champs traduisibles par type de contenu (en plus du titre, de l'extrait et du contenu).
 */
function me_translatable_meta( $post_type = '' ) {
	$map = array(
		'me_offre'   => array( '_me_pays' => __( 'Pays', 'mondial-express' ), '_me_type_prix' => __( 'Libellé du prix', 'mondial-express' ) ),
		'me_forfait' => array( '_me_dates' => __( 'Période / dates', 'mondial-express' ), '_me_duree' => __( 'Durée', 'mondial-express' ), '_me_inclus' => __( 'Inclus (une ligne par élément)', 'mondial-express' ), '_me_non_inclus' => __( 'Non inclus', 'mondial-express' ) ),
	);
	$all = array( '_me_seo_titre' => __( 'Titre SEO', 'mondial-express' ), '_me_seo_desc' => __( 'Méta-description', 'mondial-express' ) );
	if ( $post_type ) {
		return ( $map[ $post_type ] ?? array() ) + $all;
	}
	return array_merge( ...array_values( $map ) ) + $all;
}

function me_tr_key( $lang, $field ) {
	return '_me_' . $lang . '_' . ltrim( $field, '_' );
}

/**
 * Valeur traduite d'un champ d'un contenu (titre, contenu, extrait ou champ).
 */
function me_tr( $post_id, $field, $fallback = '', $lang = null ) {
	$lang = $lang ? $lang : me_lang();
	if ( 'fr' === $lang || ! $post_id ) {
		return $fallback;
	}
	$v = get_post_meta( $post_id, me_tr_key( $lang, $field ), true );
	return '' !== $v && null !== $v ? $v : $fallback;
}

add_filter(
	'the_title',
	function ( $title, $id = 0 ) {
		return ( 'fr' === me_lang() || is_admin() ) ? $title : me_tr( $id, 'titre', $title );
	},
	5,
	2
);
add_filter( 'single_post_title', fn( $t, $p ) => is_admin() ? $t : me_tr( $p->ID, 'titre', $t ), 5, 2 );
add_filter(
	'the_content',
	function ( $content ) {
		$p = get_post();
		if ( 'fr' === me_lang() || is_admin() || ! $p || $content !== $p->post_content ) {
			return $content;
		}
		return me_tr( $p->ID, 'contenu', $content );
	},
	1
);
// Liens internes écrits dans les contenus (« /services/… » ou adresse complète) : on garde la langue en cours.
add_filter(
	'the_content',
	function ( $content ) {
		if ( 'fr' === me_lang() || is_admin() ) {
			return $content;
		}
		$home = untrailingslashit( get_option( 'home' ) );
		return preg_replace_callback(
			'#href="(' . preg_quote( $home, '#' ) . ')?(/(?!/|wp-|app/|en/|es/|ar/)[^"]*)"#',
			fn( $m ) => 'href="' . $home . '/' . me_lang() . $m[2] . '"',
			$content
		);
	},
	20
);
add_filter( 'get_the_excerpt', fn( $e, $p = null ) => ( 'fr' === me_lang() || is_admin() || ! $p ) ? $e : me_tr( $p->ID, 'extrait', $e ), 5, 2 );

/**
 * Champs traduits (prix, inclusions, SEO…) lus avec get_post_meta().
 */
add_filter(
	'get_post_metadata',
	function ( $value, $object_id, $meta_key, $single ) {
		static $keys = null;
		if ( 'fr' === me_lang() || is_admin() || ! $meta_key ) {
			return $value;
		}
		$keys = $keys ?? me_translatable_meta();
		if ( ! isset( $keys[ $meta_key ] ) ) {
			return $value;
		}
		$t = get_post_meta( $object_id, me_tr_key( me_lang(), $meta_key ), true );
		return '' !== $t ? ( $single ? $t : array( $t ) ) : $value;
	},
	10,
	4
);

/**
 * Menus : titres traduits.
 */
add_filter(
	'wp_nav_menu_objects',
	function ( $items ) {
		if ( 'fr' === me_lang() ) {
			return $items;
		}
		foreach ( $items as $it ) {
			if ( 'post_type' === $it->type && get_post_field( 'post_title', (int) $it->object_id, 'raw' ) === $it->title ) {
				$it->title = me_tr( (int) $it->object_id, 'titre', $it->title );
			} elseif ( 'post_type' === $it->type ) {
				// Libellé personnalisé (ex. « Blog ») : traduit par les fichiers de langue.
				$it->title = __( $it->title, 'mondial-express' ); // phpcs:ignore WordPress.WP.I18n
			} else {
				$it->title = __( $it->title, 'mondial-express' ); // phpcs:ignore WordPress.WP.I18n
				$it->url   = me_lang_url_for( $it->url, me_lang() );
			}
		}
		return $items;
	}
);

/**
 * Catégories : nom traduit (champ sur l'écran de la catégorie).
 */
function me_tr_term( $term ) {
	if ( 'fr' !== me_lang() && $term instanceof WP_Term ) {
		$n = get_term_meta( $term->term_id, 'me_nom_' . me_lang(), true );
		if ( $n ) {
			$term->name = $n;
		}
	}
	return $term;
}
add_filter( 'get_the_terms', fn( $terms ) => is_array( $terms ) && ! is_admin() ? array_map( 'me_tr_term', $terms ) : $terms );
add_filter( 'get_terms', fn( $terms ) => is_array( $terms ) && ! is_admin() ? array_map( fn( $t ) => is_object( $t ) ? me_tr_term( $t ) : $t, $terms ) : $terms );
add_filter( 'single_cat_title', fn( $t ) => is_admin() ? $t : me_tr_term( get_queried_object() )->name ?? $t );

/**
 * Réglages de l'agence (horaires, précision paiement…) et slogan du site.
 */
add_filter( 'option_blogdescription', fn( $v ) => ( 'fr' === me_lang() || is_admin() ) ? $v : __( $v, 'mondial-express' ) ); // phpcs:ignore WordPress.WP.I18n

/* ------------------------------------------------------------------ */
/* Back-office : encadré « Traductions »                               */
/* ------------------------------------------------------------------ */

add_action(
	'add_meta_boxes',
	function () {
		foreach ( array( 'post', 'page', 'me_offre', 'me_forfait' ) as $pt ) {
			add_meta_box( 'me_traductions', __( 'Traductions (English · Español · العربية)', 'mondial-express' ), 'me_render_translations_box', $pt, 'normal', 'default' );
		}
	}
);

function me_render_translations_box( $post ) {
	wp_nonce_field( 'me_tr_save', 'me_tr_nonce' );
	$langs = array_slice( me_languages(), 1, null, true );
	echo '<p>' . esc_html__( 'Laissez un champ vide pour afficher la version française.', 'mondial-express' ) . '</p>';
	echo '<div class="me-tr-tabs" style="display:flex;gap:6px;margin-bottom:10px">';
	foreach ( $langs as $code => $l ) {
		echo '<button type="button" class="button" onclick="document.querySelectorAll(\'.me-tr-pane\').forEach(function(p){p.hidden=p.dataset.l!==\'' . esc_js( $code ) . '\'})">' . esc_html( $l[0] ) . '</button>';
	}
	echo '</div>';
	$first = true;
	foreach ( $langs as $code => $l ) {
		$dir = 'rtl' === $l[2] ? ' dir="rtl"' : '';
		echo '<div class="me-tr-pane" data-l="' . esc_attr( $code ) . '"' . ( $first ? '' : ' hidden' ) . '>';
		echo '<p><label><strong>' . esc_html__( 'Titre', 'mondial-express' ) . ' (' . esc_html( $l[0] ) . ')</strong><br><input class="widefat" name="me_tr[' . esc_attr( $code ) . '][titre]" value="' . esc_attr( get_post_meta( $post->ID, me_tr_key( $code, 'titre' ), true ) ) . '"' . $dir . '></label></p>'; // phpcs:ignore
		echo '<p><label><strong>' . esc_html__( 'Extrait', 'mondial-express' ) . '</strong><br><textarea class="widefat" rows="2" name="me_tr[' . esc_attr( $code ) . '][extrait]"' . $dir . '>' . esc_textarea( get_post_meta( $post->ID, me_tr_key( $code, 'extrait' ), true ) ) . '</textarea></label></p>'; // phpcs:ignore
		echo '<p><strong>' . esc_html__( 'Contenu', 'mondial-express' ) . '</strong></p>';
		wp_editor( (string) get_post_meta( $post->ID, me_tr_key( $code, 'contenu' ), true ), 'me_tr_' . $code . '_contenu', array( 'textarea_name' => 'me_tr[' . $code . '][contenu]', 'textarea_rows' => 10, 'media_buttons' => false ) );
		foreach ( me_translatable_meta( $post->post_type ) as $key => $label ) {
			$v   = get_post_meta( $post->ID, me_tr_key( $code, $key ), true );
			$tag = false !== strpos( $key, 'inclus' ) || false !== strpos( $key, 'desc' ) ? 'textarea' : 'input';
			echo '<p><label><strong>' . esc_html( $label ) . '</strong><br>';
			echo 'textarea' === $tag ? '<textarea class="widefat" rows="3" name="me_tr[' . esc_attr( $code ) . '][' . esc_attr( $key ) . ']"' . $dir . '>' . esc_textarea( $v ) . '</textarea>' : '<input class="widefat" name="me_tr[' . esc_attr( $code ) . '][' . esc_attr( $key ) . ']" value="' . esc_attr( $v ) . '"' . $dir . '>'; // phpcs:ignore
			echo '</label></p>';
		}
		echo '</div>';
		$first = false;
	}
}

add_action(
	'save_post',
	function ( $post_id ) {
		if ( ! isset( $_POST['me_tr_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['me_tr_nonce'] ), 'me_tr_save' ) || ! current_user_can( 'edit_post', $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}
		$data = isset( $_POST['me_tr'] ) && is_array( $_POST['me_tr'] ) ? wp_unslash( $_POST['me_tr'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		foreach ( array_keys( array_slice( me_languages(), 1, null, true ) ) as $code ) {
			foreach ( (array) ( $data[ $code ] ?? array() ) as $field => $value ) {
				$field = sanitize_key( $field );
				$value = 'contenu' === $field ? wp_kses_post( $value ) : sanitize_textarea_field( $value );
				'' === trim( $value ) ? delete_post_meta( $post_id, me_tr_key( $code, $field ) ) : update_post_meta( $post_id, me_tr_key( $code, $field ), $value );
			}
		}
	}
);

/**
 * Catégories : champs « Nom en anglais / espagnol / arabe ».
 */
add_action(
	'category_edit_form_fields',
	function ( $term ) {
		foreach ( array_slice( me_languages(), 1, null, true ) as $code => $l ) {
			echo '<tr class="form-field"><th><label for="me_nom_' . esc_attr( $code ) . '">' . esc_html__( 'Nom', 'mondial-express' ) . ' (' . esc_html( $l[0] ) . ')</label></th><td><input id="me_nom_' . esc_attr( $code ) . '" name="me_nom_' . esc_attr( $code ) . '" value="' . esc_attr( get_term_meta( $term->term_id, 'me_nom_' . $code, true ) ) . '"></td></tr>';
		}
	}
);
add_action(
	'edited_category',
	function ( $term_id ) {
		if ( ! current_user_can( 'manage_categories' ) || ! isset( $_POST['_wpnonce'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}
		foreach ( array_keys( array_slice( me_languages(), 1, null, true ) ) as $code ) {
			if ( isset( $_POST[ 'me_nom_' . $code ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				update_term_meta( $term_id, 'me_nom_' . $code, sanitize_text_field( wp_unslash( $_POST[ 'me_nom_' . $code ] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
			}
		}
	}
);
