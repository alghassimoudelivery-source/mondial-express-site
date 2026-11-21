<?php
/**
 * Contenus de l'agence : offres, forfaits Hajj & Oumra, demandes reçues et dossiers clients.
 *
 * @package MondialExpress
 */

defined( 'ABSPATH' ) || exit;

function me_register_post_types() {
	register_post_type(
		'me_offre',
		array(
			'labels'        => array(
				'name'          => __( 'Offres & destinations', 'mondial-express' ),
				'singular_name' => __( 'Offre', 'mondial-express' ),
				'add_new_item'  => __( 'Ajouter une offre', 'mondial-express' ),
				'edit_item'     => __( "Modifier l'offre", 'mondial-express' ),
				'menu_name'     => __( 'Offres', 'mondial-express' ),
			),
			'public'        => true,
			'has_archive'   => 'destinations',
			'rewrite'       => array( 'slug' => 'destination', 'with_front' => false ),
			'menu_icon'     => 'dashicons-airplane',
			'menu_position' => 21,
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
			'show_in_rest'  => true,
		)
	);
	register_post_type(
		'me_forfait',
		array(
			'labels'        => array(
				'name'          => __( 'Forfaits Hajj & Oumra', 'mondial-express' ),
				'singular_name' => __( 'Forfait', 'mondial-express' ),
				'add_new_item'  => __( 'Ajouter un forfait', 'mondial-express' ),
				'menu_name'     => __( 'Hajj & Oumra', 'mondial-express' ),
			),
			'public'        => true,
			'has_archive'   => false,
			'rewrite'       => array( 'slug' => 'forfait', 'with_front' => false ),
			'menu_icon'     => 'dashicons-building',
			'menu_position' => 22,
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
			'show_in_rest'  => true,
		)
	);
	register_post_type(
		'me_demande',
		array(
			'labels'          => array(
				'name'          => __( 'Demandes reçues', 'mondial-express' ),
				'singular_name' => __( 'Demande', 'mondial-express' ),
				'edit_item'     => __( 'Traiter la demande', 'mondial-express' ),
				'menu_name'     => __( 'Demandes', 'mondial-express' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'menu_icon'       => 'dashicons-email-alt',
			'menu_position'   => 3,
			'supports'        => array( 'title' ),
			'capability_type' => 'post',
			'map_meta_cap'    => true,
			'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
		)
	);
	register_post_type(
		'me_dossier',
		array(
			'labels'        => array(
				'name'          => __( 'Dossiers clients', 'mondial-express' ),
				'singular_name' => __( 'Dossier', 'mondial-express' ),
				'add_new_item'  => __( 'Ouvrir un dossier', 'mondial-express' ),
				'edit_item'     => __( 'Modifier le dossier', 'mondial-express' ),
				'menu_name'     => __( 'Dossiers', 'mondial-express' ),
			),
			'public'        => false,
			'show_ui'       => true,
			'menu_icon'     => 'dashicons-portfolio',
			'menu_position' => 4,
			'supports'      => array( 'title' ),
		)
	);
}
add_action( 'init', 'me_register_post_types' );

/**
 * Étapes d'un dossier, dans l'ordre.
 */
function me_dossier_steps() {
	return array(
		'recu'       => __( 'Demande reçue', 'mondial-express' ),
		'documents'  => __( 'Documents validés', 'mondial-express' ),
		'traitement' => __( 'En traitement', 'mondial-express' ),
		'rendez-vous'=> __( 'Rendez-vous / dépôt', 'mondial-express' ),
		'termine'    => __( 'Visa obtenu / billet émis', 'mondial-express' ),
	);
}

function me_demande_types() {
	return array(
		'billet'    => __( "Billet d'avion", 'mondial-express' ),
		'visa'      => __( 'Assistance visa', 'mondial-express' ),
		'hotel'     => __( 'Hôtel & tourisme', 'mondial-express' ),
		'hajj'      => __( 'Hajj & Oumra', 'mondial-express' ),
		'evacuation'=> __( 'Transport & évacuation', 'mondial-express' ),
		'affaires'  => __( "Voyage d'affaires", 'mondial-express' ),
		'contact'   => __( 'Message', 'mondial-express' ),
	);
}

/**
 * Champs personnalisés de chaque type de contenu.
 */
function me_meta_fields() {
	return array(
		'me_offre'   => array(
			'_me_prix'           => array( __( 'Prix « à partir de » (GNF)', 'mondial-express' ), 'number' ),
			'_me_prix_indicatif' => array( __( 'Afficher « prix indicatif »', 'mondial-express' ), 'checkbox' ),
			'_me_code'           => array( __( 'Code aéroport (ex. DXB)', 'mondial-express' ), 'text' ),
			'_me_pays'           => array( __( 'Pays', 'mondial-express' ), 'text' ),
			'_me_type_prix'      => array( __( 'Libellé du prix (ex. Aller-retour)', 'mondial-express' ), 'text' ),
			'_me_visuel'         => array( __( 'Couleur du visuel sans photo', 'mondial-express' ), 'select', array( 'orange' => 'Orange', 'bleu' => 'Bleu', 'violet' => 'Violet', 'vert' => 'Vert' ) ),
			'_me_vedette'        => array( __( "Mettre en avant sur l'accueil", 'mondial-express' ), 'checkbox' ),
		),
		'me_forfait' => array(
			'_me_prix'           => array( __( 'Prix (GNF)', 'mondial-express' ), 'number' ),
			'_me_prix_indicatif' => array( __( 'Afficher « prix indicatif »', 'mondial-express' ), 'checkbox' ),
			'_me_type'           => array( __( 'Type', 'mondial-express' ), 'select', array( 'oumra' => 'Oumra', 'hajj' => 'Hajj' ) ),
			'_me_dates'          => array( __( 'Période / dates', 'mondial-express' ), 'text' ),
			'_me_duree'          => array( __( 'Durée', 'mondial-express' ), 'text' ),
			'_me_inclus'         => array( __( 'Inclus (une ligne par élément)', 'mondial-express' ), 'textarea' ),
			'_me_non_inclus'     => array( __( 'Non inclus (une ligne par élément)', 'mondial-express' ), 'textarea' ),
		),
		'me_demande' => array(
			'_me_statut'    => array( __( 'Statut', 'mondial-express' ), 'select', array( 'nouvelle' => 'Nouvelle', 'en-cours' => 'En cours', 'traitee' => 'Traitée', 'archivee' => 'Archivée' ) ),
			'_me_type'      => array( __( 'Type de demande', 'mondial-express' ), 'select', me_demande_types() ),
			'_me_nom'       => array( __( 'Nom', 'mondial-express' ), 'text' ),
			'_me_telephone' => array( __( 'Téléphone / WhatsApp', 'mondial-express' ), 'text' ),
			'_me_email'     => array( __( 'E-mail', 'mondial-express' ), 'text' ),
			'_me_details'   => array( __( 'Détails de la demande', 'mondial-express' ), 'textarea' ),
			'_me_note'      => array( __( 'Note interne', 'mondial-express' ), 'textarea' ),
		),
		'me_dossier' => array(
			'_me_numero'    => array( __( 'Numéro de dossier', 'mondial-express' ), 'text' ),
			'_me_type'      => array( __( 'Type', 'mondial-express' ), 'select', me_demande_types() ),
			'_me_etape'     => array( __( 'Étape actuelle', 'mondial-express' ), 'select', me_dossier_steps() ),
			'_me_telephone' => array( __( 'Téléphone du client (sert au suivi en ligne)', 'mondial-express' ), 'text' ),
			'_me_client'    => array( __( 'Compte client (identifiant WordPress)', 'mondial-express' ), 'user' ),
			'_me_rdv'       => array( __( 'Prochain rendez-vous (affiché au client)', 'mondial-express' ), 'text' ),
			'_me_message'   => array( __( 'Message au client', 'mondial-express' ), 'textarea' ),
			'_me_paiement'  => array( __( 'Paiement', 'mondial-express' ), 'select', array( 'aucun' => 'Rien à payer', 'acompte' => 'Acompte versé', 'solde' => 'Solde à régler', 'paye' => 'Payé' ) ),
		),
	);
}

/**
 * Boîtes de champs dans l'administration.
 */
add_action(
	'add_meta_boxes',
	function () {
		foreach ( array_keys( me_meta_fields() ) as $pt ) {
			add_meta_box( 'me_fields', __( 'Informations', 'mondial-express' ), 'me_render_meta_box', $pt, 'normal', 'high' );
		}
		add_meta_box( 'me_docs', __( 'Documents envoyés par le client', 'mondial-express' ), 'me_render_docs_box', 'me_dossier', 'side' );
	}
);

function me_render_meta_box( $post ) {
	$fields = me_meta_fields()[ $post->post_type ] ?? array();
	wp_nonce_field( 'me_save_meta', 'me_meta_nonce' );
	echo '<table class="form-table me-meta">';
	foreach ( $fields as $key => $f ) {
		$val = get_post_meta( $post->ID, $key, true );
		echo '<tr><th><label for="' . esc_attr( $key ) . '">' . esc_html( $f[0] ) . '</label></th><td>';
		switch ( $f[1] ) {
			case 'textarea':
				echo '<textarea class="large-text" rows="4" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '">' . esc_textarea( $val ) . '</textarea>';
				break;
			case 'checkbox':
				echo '<input type="checkbox" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="1" ' . checked( $val, '1', false ) . '>';
				break;
			case 'select':
				echo '<select id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '">';
				foreach ( $f[2] as $k => $l ) {
					echo '<option value="' . esc_attr( $k ) . '" ' . selected( $val, $k, false ) . '>' . esc_html( $l ) . '</option>';
				}
				echo '</select>';
				break;
			case 'user':
				wp_dropdown_users(
					array(
						'name'              => $key,
						'id'                => $key,
						'selected'          => (int) $val,
						'show_option_none'  => __( '— Aucun —', 'mondial-express' ),
						'option_none_value' => 0,
						'role__in'          => array( 'me_client', 'subscriber' ),
					)
				);
				break;
			default:
				echo '<input class="regular-text" type="' . ( 'number' === $f[1] ? 'number' : 'text' ) . '" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $val ) . '">';
		}
		echo '</td></tr>';
	}
	echo '</table>';
}

function me_render_docs_box( $post ) {
	$docs = get_children( array( 'post_parent' => $post->ID, 'post_type' => 'attachment' ) );
	if ( ! $docs ) {
		echo '<p>' . esc_html__( 'Aucun document pour le moment.', 'mondial-express' ) . '</p>';
		return;
	}
	echo '<ul>';
	foreach ( $docs as $d ) {
		echo '<li><a href="' . esc_url( wp_get_attachment_url( $d->ID ) ) . '" target="_blank">' . esc_html( get_the_title( $d ) ) . '</a></li>';
	}
	echo '</ul>';
}

add_action(
	'save_post',
	function ( $post_id, $post ) {
		if ( ! isset( $_POST['me_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['me_meta_nonce'] ), 'me_save_meta' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$fields = me_meta_fields()[ $post->post_type ] ?? array();
		$old    = get_post_meta( $post_id, '_me_etape', true );
		foreach ( $fields as $key => $f ) {
			if ( 'checkbox' === $f[1] ) {
				update_post_meta( $post_id, $key, isset( $_POST[ $key ] ) ? '1' : '' );
				continue;
			}
			if ( ! isset( $_POST[ $key ] ) ) {
				continue;
			}
			$raw = wp_unslash( $_POST[ $key ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$val = 'textarea' === $f[1] ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
			update_post_meta( $post_id, $key, $val );
		}
		if ( 'me_dossier' === $post->post_type ) {
			if ( ! get_post_meta( $post_id, '_me_numero', true ) ) {
				update_post_meta( $post_id, '_me_numero', me_new_dossier_number() );
			}
			$new = get_post_meta( $post_id, '_me_etape', true );
			if ( $old && $new && $old !== $new ) {
				do_action( 'me_dossier_etape_changee', $post_id, $old, $new );
			}
		}
	},
	10,
	2
);

/**
 * Numéro de dossier unique, ex. ME-2611-4821.
 */
function me_new_dossier_number() {
	do {
		$n = 'ME-' . gmdate( 'ym' ) . '-' . wp_rand( 1000, 9999 );
	} while ( get_posts( array( 'post_type' => 'me_dossier', 'meta_key' => '_me_numero', 'meta_value' => $n, 'fields' => 'ids', 'post_status' => 'any' ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	return $n;
}

/**
 * Prévient le client par e-mail quand son dossier avance (s'il a un compte avec e-mail).
 */
add_action(
	'me_dossier_etape_changee',
	function ( $post_id, $old, $new ) {
		$uid  = (int) get_post_meta( $post_id, '_me_client', true );
		$user = $uid ? get_userdata( $uid ) : null;
		if ( ! $user || ! is_email( $user->user_email ) ) {
			return;
		}
		$steps = me_dossier_steps();
		$num   = get_post_meta( $post_id, '_me_numero', true );
		/* translators: 1: numéro, 2: étape */
		$subject = sprintf( __( 'Votre dossier %1$s : %2$s', 'mondial-express' ), $num, $steps[ $new ] ?? $new );
		$body    = sprintf( __( "Bonjour,\n\nVotre dossier %1\$s vient de passer à l'étape : %2\$s.\nSuivez-le ici : %3\$s\n\nMondial Express Guinée\n%4\$s", 'mondial-express' ), $num, $steps[ $new ] ?? $new, home_url( '/mon-espace/' ), me_opt( 'telephone' ) );
		wp_mail( $user->user_email, $subject, $body );
	},
	10,
	3
);
