<?php
/**
 * Réglages de l'agence dans Apparence > Personnaliser > Mondial Express.
 *
 * @package MondialExpress
 */

defined( 'ABSPATH' ) || exit;

function me_customize_register( $wp_customize ) {
	$wp_customize->add_panel( 'me_panel', array( 'title' => 'Mondial Express', 'priority' => 20 ) );

	$sections = array(
		'me_contact' => array(
			'title'  => __( 'Coordonnées', 'mondial-express' ),
			'fields' => array(
				'telephone' => array( __( 'Téléphone affiché', 'mondial-express' ), 'text' ),
				'whatsapp'  => array( __( 'Numéro WhatsApp (chiffres, avec indicatif 224)', 'mondial-express' ), 'text' ),
				'email'     => array( __( 'E-mail', 'mondial-express' ), 'email' ),
				'adresse'   => array( __( 'Adresse', 'mondial-express' ), 'textarea' ),
				'horaires'  => array( __( "Horaires d'ouverture", 'mondial-express' ), 'text' ),
				'carte'     => array( __( 'Lien Google Maps de l\'agence', 'mondial-express' ), 'url' ),
			),
		),
		'me_social'  => array(
			'title'       => __( 'Réseaux sociaux', 'mondial-express' ),
			'description' => __( "Collez l'adresse complète de chaque page. Les boutons sans lien restent visibles mais inactifs.", 'mondial-express' ),
			'fields'      => array(
				'facebook'  => array( 'Facebook', 'url' ),
				'x'         => array( 'X (Twitter)', 'url' ),
				'instagram' => array( 'Instagram', 'url' ),
				'tiktok'    => array( 'TikTok', 'url' ),
				'youtube'   => array( 'YouTube', 'url' ),
			),
		),
		'me_app'     => array(
			'title'  => __( 'Application mobile', 'mondial-express' ),
			'fields' => array(
				'app_android' => array( __( 'Lien Google Play', 'mondial-express' ), 'url' ),
				'app_ios'     => array( __( "Lien App Store", 'mondial-express' ), 'url' ),
			),
		),
		'me_paiement' => array(
			'title'       => __( 'Paiements acceptés', 'mondial-express' ),
			'description' => __( 'Cochez les moyens de paiement acceptés par l’agence. Ils s’affichent dans le pied de page, sur les pages Contact et Demande de devis, sur les offres, dans la FAQ et dans l’application.', 'mondial-express' ),
			'fields'      => array(
				'pay_orange'   => array( 'Orange Money', 'checkbox' ),
				'pay_mtn'      => array( 'MTN Mobile Money', 'checkbox' ),
				'pay_carte'    => array( __( 'Carte bancaire (Visa, Mastercard)', 'mondial-express' ), 'checkbox' ),
				'pay_especes'  => array( __( 'Espèces à l’agence', 'mondial-express' ), 'checkbox' ),
				'pay_virement' => array( __( 'Virement bancaire', 'mondial-express' ), 'checkbox' ),
				'pay_note'     => array( __( 'Précision affichée sous les moyens de paiement', 'mondial-express' ), 'text' ),
			),
		),
		'me_chiffres' => array(
			'title'  => __( 'Chiffres clés (accueil)', 'mondial-express' ),
			'fields' => array(
				'stat_voyageurs' => array( __( 'Voyageurs accompagnés', 'mondial-express' ), 'text' ),
				'stat_dest'      => array( __( 'Destinations', 'mondial-express' ), 'text' ),
				'stat_visas'     => array( __( 'Taux de visas obtenus', 'mondial-express' ), 'text' ),
			),
		),
		'me_codes'   => array(
			'title'       => __( 'Codes de suivi (Google Analytics…)', 'mondial-express' ),
			'description' => __( "Emplacement réservé : collez ici le code fourni par Google Analytics ou un autre outil de mesure. Il sera ajouté sur toutes les pages. Laissez vide tant que vous n'avez pas votre identifiant.", 'mondial-express' ),
			'fields'      => array(
				'code_entete' => array( __( "Code dans l'en-tête (<head>)", 'mondial-express' ), 'code' ),
				'code_pied'   => array( __( 'Code avant la fin de la page (</body>)', 'mondial-express' ), 'code' ),
			),
		),
	);

	$defaults = me_defaults();
	foreach ( $sections as $sid => $section ) {
		$wp_customize->add_section(
			$sid,
			array(
				'title'       => $section['title'],
				'description' => $section['description'] ?? '',
				'panel'       => 'me_panel',
			)
		);
		foreach ( $section['fields'] as $key => $f ) {
			$type     = $f[1];
			$sanitize = array(
				'url'      => 'esc_url_raw',
				'email'    => 'sanitize_email',
				'textarea' => 'sanitize_textarea_field',
				'code'     => 'me_sanitize_code',
				'checkbox' => 'me_sanitize_checkbox',
			)[ $type ] ?? 'sanitize_text_field';
			$wp_customize->add_setting(
				'me_' . $key,
				array(
					'default'           => $defaults[ $key ] ?? '',
					'sanitize_callback' => $sanitize,
					'capability'        => 'code' === $type ? 'unfiltered_html' : 'edit_theme_options',
				)
			);
			$wp_customize->add_control(
				'me_' . $key,
				array(
					'label'   => $f[0],
					'section' => $sid,
					'type'    => 'code' === $type ? 'textarea' : $type,
				)
			);
		}
	}
}
add_action( 'customize_register', 'me_customize_register' );

function me_sanitize_checkbox( $value ) {
	return $value ? 1 : 0;
}

/**
 * Les codes de suivi ne sont enregistrables que par un administrateur (droit unfiltered_html).
 */
function me_sanitize_code( $value ) {
	return current_user_can( 'unfiltered_html' ) ? $value : wp_kses_post( $value );
}

/**
 * Emplacements des codes de suivi : vides par défaut.
 */
add_action(
	'wp_head',
	function () {
		echo "\n<!-- Mondial Express : emplacement des codes de suivi (Apparence > Personnaliser > Mondial Express > Codes de suivi) -->\n";
		$code = me_opt( 'code_entete' );
		if ( $code ) {
			echo $code . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- code saisi par un administrateur.
		}
	},
	99
);
add_action(
	'wp_footer',
	function () {
		$code = me_opt( 'code_pied' );
		if ( $code ) {
			echo $code . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
		}
	},
	99
);
