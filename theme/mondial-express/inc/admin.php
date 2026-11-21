<?php
/**
 * Back-office de l'agence : colonnes utiles, filtres, tableau de bord.
 *
 * @package MondialExpress
 */

defined( 'ABSPATH' ) || exit;

/**
 * Colonnes des demandes et des dossiers.
 */
add_filter(
	'manage_me_demande_posts_columns',
	fn( $c ) => array( 'cb' => $c['cb'], 'title' => __( 'Demande', 'mondial-express' ), 'me_tel' => __( 'Téléphone', 'mondial-express' ), 'me_statut' => __( 'Statut', 'mondial-express' ), 'date' => $c['date'] )
);
add_filter(
	'manage_me_dossier_posts_columns',
	fn( $c ) => array( 'cb' => $c['cb'], 'title' => __( 'Dossier', 'mondial-express' ), 'me_num' => __( 'Numéro', 'mondial-express' ), 'me_etape' => __( 'Étape', 'mondial-express' ), 'me_tel' => __( 'Téléphone', 'mondial-express' ), 'date' => $c['date'] )
);
add_filter(
	'manage_me_offre_posts_columns',
	function ( $c ) {
		$c['me_prix'] = __( 'Prix', 'mondial-express' );
		return $c;
	}
);
add_action(
	'manage_posts_custom_column',
	function ( $col, $id ) {
		$colors = array( 'nouvelle' => '#E8730C', 'en-cours' => '#5B2D90', 'traitee' => '#64A70B', 'archivee' => '#999' );
		switch ( $col ) {
			case 'me_tel':
				$t = get_post_meta( $id, '_me_telephone', true );
				echo esc_html( $t ) . ( $t ? ' <a href="' . esc_url( 'https://wa.me/' . preg_replace( '/\D/', '', $t ) ) . '" target="_blank">WhatsApp</a>' : '' );
				break;
			case 'me_statut':
				$s = get_post_meta( $id, '_me_statut', true );
				echo '<span style="background:' . esc_attr( $colors[ $s ] ?? '#999' ) . ';color:#fff;padding:2px 8px;border-radius:10px;font-size:12px">' . esc_html( ucfirst( str_replace( '-', ' ', $s ) ) ) . '</span>';
				break;
			case 'me_num':
				echo esc_html( get_post_meta( $id, '_me_numero', true ) );
				break;
			case 'me_etape':
				$steps = me_dossier_steps();
				echo esc_html( $steps[ get_post_meta( $id, '_me_etape', true ) ] ?? '' );
				break;
			case 'me_prix':
				echo esc_html( me_price( get_post_meta( $id, '_me_prix', true ) ) );
				break;
		}
	},
	10,
	2
);

/**
 * Pastille du nombre de nouvelles demandes dans le menu.
 */
add_action(
	'admin_menu',
	function () {
		global $menu;
		$n = count( get_posts( array( 'post_type' => 'me_demande', 'numberposts' => 99, 'fields' => 'ids', 'meta_key' => '_me_statut', 'meta_value' => 'nouvelle' ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		if ( ! $n ) {
			return;
		}
		foreach ( $menu as $k => $item ) {
			if ( 'edit.php?post_type=me_demande' === $item[2] ) {
				$menu[ $k ][0] .= ' <span class="awaiting-mod">' . (int) $n . '</span>'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
			}
		}
	},
	99
);

/**
 * Widget du tableau de bord.
 */
add_action(
	'wp_dashboard_setup',
	function () {
		wp_add_dashboard_widget(
			'me_resume',
			'Mondial Express',
			function () {
				$count = fn( $pt, $k = null, $v = null ) => count( get_posts( array_filter( array( 'post_type' => $pt, 'numberposts' => 999, 'fields' => 'ids', 'meta_key' => $k, 'meta_value' => $v ) ) ) );
				echo '<ul style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;text-align:center">';
				echo '<li><strong style="font-size:24px;color:#E8730C">' . (int) $count( 'me_demande', '_me_statut', 'nouvelle' ) . '</strong><br>' . esc_html__( 'nouvelles demandes', 'mondial-express' ) . '</li>';
				echo '<li><strong style="font-size:24px;color:#5B2D90">' . (int) $count( 'me_dossier' ) . '</strong><br>' . esc_html__( 'dossiers', 'mondial-express' ) . '</li>';
				echo '<li><strong style="font-size:24px;color:#64A70B">' . (int) $count( 'me_offre' ) . '</strong><br>' . esc_html__( 'offres en ligne', 'mondial-express' ) . '</li></ul>';
				echo '<p><a class="button button-primary" href="' . esc_url( admin_url( 'edit.php?post_type=me_demande' ) ) . '">' . esc_html__( 'Voir les demandes', 'mondial-express' ) . '</a> <a class="button" href="' . esc_url( admin_url( 'post-new.php?post_type=me_dossier' ) ) . '">' . esc_html__( 'Ouvrir un dossier', 'mondial-express' ) . '</a> <a class="button" href="' . esc_url( admin_url( 'customize.php' ) ) . '">' . esc_html__( 'Coordonnées et réseaux', 'mondial-express' ) . '</a></p>';
			}
		);
	}
);

/**
 * Bouton « Créer un dossier » depuis une demande.
 */
add_action(
	'post_submitbox_misc_actions',
	function ( $post ) {
		if ( 'me_demande' !== $post->post_type ) {
			return;
		}
		$url = wp_nonce_url( admin_url( 'admin-post.php?action=me_demande_vers_dossier&demande=' . $post->ID ), 'me_conv_' . $post->ID );
		echo '<div class="misc-pub-section"><a class="button" href="' . esc_url( $url ) . '">' . esc_html__( 'Ouvrir un dossier client', 'mondial-express' ) . '</a></div>';
	}
);
add_action(
	'admin_post_me_demande_vers_dossier',
	function () {
		$did = absint( $_GET['demande'] ?? 0 );
		check_admin_referer( 'me_conv_' . $did );
		if ( ! current_user_can( 'edit_post', $did ) ) {
			wp_die( esc_html__( 'Action non autorisée.', 'mondial-express' ) );
		}
		$id = wp_insert_post(
			array(
				'post_type'   => 'me_dossier',
				'post_status' => 'publish',
				'post_title'  => get_the_title( $did ),
				'meta_input'  => array(
					'_me_numero'    => me_new_dossier_number(),
					'_me_type'      => get_post_meta( $did, '_me_type', true ),
					'_me_etape'     => 'recu',
					'_me_telephone' => get_post_meta( $did, '_me_telephone', true ),
					'_me_client'    => (int) get_post_meta( $did, '_me_client', true ),
					'_me_paiement'  => 'aucun',
				),
			)
		);
		update_post_meta( $did, '_me_statut', 'en-cours' );
		wp_safe_redirect( admin_url( 'post.php?post=' . $id . '&action=edit' ) );
		exit;
	}
);

/**
 * Pied de page de l'administration.
 */
add_filter( 'admin_footer_text', fn() => 'Mondial Express Guinée · Site réalisé par <a href="https://confordev.com" target="_blank">ConforDev</a>' );
