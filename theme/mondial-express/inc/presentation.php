<?php
/**
 * Présentation de l'agence : histoire, valeurs, mission, équipe, compagnies partenaires, témoignages.
 * Utilisée par la page « À propos » du site et par l'application (API mondial/v1/presentation).
 *
 * @package MondialExpress
 */

defined( 'ABSPATH' ) || exit;

/**
 * Photos de l'équipe : adresses des images importées dans la médiathèque (option me_equipe_photos).
 */
function me_team_photo( $key ) {
	$photos = (array) get_option( 'me_equipe_photos', array() );
	return isset( $photos[ $key ] ) ? $photos[ $key ] : '';
}

/**
 * Logos des compagnies partenaires : adresses des images importées (option me_partenaires_logos, clé = code IATA).
 */
function me_partner_logo( $code ) {
	$logos = (array) get_option( 'me_partenaires_logos', array() );
	return isset( $logos[ $code ] ) ? $logos[ $code ] : '';
}

/**
 * Contenu de la présentation (traduisible).
 */
function me_presentation() {
	$years = max( 1, (int) gmdate( 'Y' ) - 2015 );
	return array(
		'intro'       => __( 'Fondée à Conakry en mars 2015, Mondial Express est une agence de voyages et de tourisme qui accompagne particuliers, entreprises et groupes dans leurs projets de déplacement, en Guinée comme à l’international. De la billetterie aérienne à l’assistance visa, en passant par le tourisme éducatif et médical, les voyages d’affaires, le transport et les séjours personnalisés, nous proposons des solutions pensées autour des besoins de chaque voyageur.', 'mondial-express' ),
		'intro2'      => __( 'Notre approche repose sur une bonne connaissance du secteur, un solide réseau de partenaires et un accompagnement personnalisé, pour rendre chaque déplacement plus simple, plus fluide et mieux organisé.', 'mondial-express' ),
		'valeurs'     => array(
			array( 'star', __( 'Excellence', 'mondial-express' ), __( 'La qualité dans chaque détail : des prestations fiables et des solutions adaptées à chaque client.', 'mondial-express' ), 'orange' ),
			array( 'shield', __( 'Intégrité', 'mondial-express' ), __( 'La confiance comme fondement : transparence, éthique et respect de nos engagements.', 'mondial-express' ), 'vert' ),
			array( 'heart', __( 'Engagement', 'mondial-express' ), __( 'Présents à chaque étape, avec des solutions personnalisées et un suivi attentif de chaque projet.', 'mondial-express' ), 'violet' ),
			array( 'users', __( 'Esprit d’équipe', 'mondial-express' ), __( 'Une expertise collective : la complémentarité de notre équipe au service de votre voyage.', 'mondial-express' ), 'orange' ),
		),
		'mission'     => __( 'Faciliter le voyage, pour tous : rendre le voyage plus accessible et plus simple, en associant expertise touristique, solutions flexibles, innovation et accompagnement personnalisé.', 'mondial-express' ),
		'etapes'      => array(
			array( __( 'Écouter', 'mondial-express' ), __( 'Comprendre chaque projet et ses besoins.', 'mondial-express' ) ),
			array( __( 'Conseiller', 'mondial-express' ), __( 'Orienter vers les solutions les plus adaptées.', 'mondial-express' ) ),
			array( __( 'Organiser', 'mondial-express' ), __( 'Coordonner chaque composante du voyage.', 'mondial-express' ) ),
			array( __( 'Accompagner', 'mondial-express' ), __( 'Assurer un suivi attentif à chaque étape.', 'mondial-express' ) ),
		),
		'vision'      => __( 'Devenir une référence du voyage et du tourisme en Afrique et dans le monde, avec des offres personnalisées, des expériences authentiques et des solutions adaptées aux nouvelles tendances.', 'mondial-express' ),
		'chiffres'    => array(
			array( '120+', __( 'réservations par mois', 'mondial-express' ) ),
			array( '96 %', __( 'de clients satisfaits', 'mondial-express' ) ),
			/* translators: %d: nombre d'années */
			array( (string) $years, __( 'années d’expérience', 'mondial-express' ) ),
			array( '16', __( 'compagnies partenaires', 'mondial-express' ) ),
		),
		'histoire'    => array(
			array( '2026', __( 'Nouvelle plateforme en ligne et application mobile : demandes, suivi des dossiers et actualités en temps réel.', 'mondial-express' ) ),
			array( '2025', __( 'Nouveaux services et création du département Tourisme, pour les circuits en Guinée et à l’étranger.', 'mondial-express' ) ),
			array( '2023', __( 'Lancement du programme de parrainage, qui récompense les clients pour chaque nouveau voyageur recommandé.', 'mondial-express' ) ),
			array( '2022', __( 'Programme de fidélité : des points à chaque réservation, des réductions et des avantages exclusifs.', 'mondial-express' ) ),
			array( '2018', __( 'L’agence se structure en départements pour mieux gérer chacune de ses activités.', 'mondial-express' ) ),
			array( '2015', __( 'Création de Mondial Express en mars 2015 par M. Edouard Koundouno, entrepreneur à la tête de plusieurs sociétés, dont SOCOGIM dans le bâtiment.', 'mondial-express' ) ),
		),
		'equipe'      => array(
			array( 'koundouno', 'Edouard Koundouno', __( 'Directeur général', 'mondial-express' ) ),
			array( 'yha', 'Séhi Apholos Yha', __( 'Responsable stratégie, développement, tourisme et ressources humaines', 'mondial-express' ) ),
			array( 'balde', 'Salématou Baldé', __( 'Cheffe d’agence', 'mondial-express' ) ),
			array( 'kadouno', 'Tamba Raymond Kadouno', __( 'Chef d’agence adjoint', 'mondial-express' ) ),
			array( 'diallo', 'Mamadou Mouctar Diallo', __( 'Agent assistant visa', 'mondial-express' ) ),
			array( 'conte', 'Mariama Conté', __( 'Responsable du service nettoyage', 'mondial-express' ) ),
			array( 'cece', 'M’balou Fatoumata Cece', __( 'Responsable commerciale et marketing', 'mondial-express' ) ),
			array( 'camara', 'Tamba Eric Brice Camara', __( 'Chef comptable', 'mondial-express' ) ),
			array( 'deen', 'Manfred Deen', __( 'Community manager', 'mondial-express' ) ),
		),
		'partenaires' => array(
			array( 'Brussels Airlines', 'SN', 'brussels.png' ),
			array( 'Ethiopian Airlines', 'ET', 'ethiopian.png' ),
			array( 'Air France', 'AF', 'france.webp' ),
			array( 'Air Sénégal', 'HC', 'senegal.png' ),
			array( 'Royal Air Maroc', 'AT', 'maroc.webp' ),
			array( 'ASKY Airlines', 'KP', 'asky.png' ),
			array( 'Air Côte d’Ivoire', 'HF', 'ivory-coast.png' ),
			array( 'Qatar Airways', 'QR', 'qatar.png' ),
			array( 'American Airlines', 'AA', 'american.png' ),
			array( 'British Airways', 'BA', 'british.png' ),
			array( 'Air China', 'CA', 'china.png' ),
			array( 'Emirates', 'EK', 'emirates.png' ),
			array( 'Air India', 'AI', 'india.png' ),
			array( 'Mauritania Airlines', 'L6', 'mauritanienne.png' ),
			array( 'Singapore Airlines', 'SQ', 'singapore.png' ),
			array( 'Turkish Airlines', 'TK', 'turkish.png' ),
		),
		'temoignages' => array(
			array( 'Aïssata Bangoura', __( 'Responsable commerciale, IBM', 'mondial-express' ), __( 'Pour tous les déplacements de nos équipes, Mondial Express trouve la bonne option au bon prix. Les billets arrivent toujours à temps, même quand nous nous y prenons tard.', 'mondial-express' ) ),
			array( 'Sékou Keïta', __( 'Entrepreneur, Conakry', 'mondial-express' ), __( 'Ils ont géré mon visa et mon billet en une semaine, avec un point d’étape à chaque avancée du dossier. Je recommande les yeux fermés.', 'mondial-express' ) ),
		),
	);
}

/**
 * Version pour l'API de l'application (photos incluses).
 */
function me_presentation_api() {
	$p = me_presentation();
	return array(
		'intro'       => array( $p['intro'], $p['intro2'] ),
		'valeurs'     => array_map( fn( $v ) => array( 'icone' => $v[0], 'titre' => $v[1], 'texte' => $v[2], 'couleur' => $v[3] ), $p['valeurs'] ),
		'mission'     => $p['mission'],
		'etapes'      => array_map( fn( $e ) => array( 'titre' => $e[0], 'texte' => $e[1] ), $p['etapes'] ),
		'vision'      => $p['vision'],
		'chiffres'    => array_map( fn( $c ) => array( 'valeur' => $c[0], 'libelle' => $c[1] ), $p['chiffres'] ),
		'histoire'    => array_map( fn( $h ) => array( 'annee' => $h[0], 'texte' => $h[1] ), $p['histoire'] ),
		'equipe'      => array_map( fn( $m ) => array( 'nom' => $m[1], 'role' => $m[2], 'photo' => me_team_photo( $m[0] ) ), $p['equipe'] ),
		'partenaires' => array_map( fn( $a ) => array( 'nom' => $a[0], 'code' => $a[1], 'logo' => me_partner_logo( $a[1] ) ), $p['partenaires'] ),
		'temoignages' => array_map( fn( $t ) => array( 'nom' => $t[0], 'role' => $t[1], 'texte' => $t[2] ), $p['temoignages'] ),
	);
}

/**
 * Initiales pour l'avatar de secours.
 */
function me_initials( $name ) {
	$parts = preg_split( '/\s+/', trim( $name ) );
	$out   = '';
	foreach ( array_slice( $parts, 0, 2 ) as $p ) {
		$out .= mb_strtoupper( mb_substr( $p, 0, 1 ) );
	}
	return $out;
}

/**
 * Bandeau « Nos compagnies partenaires » (utilisé aussi sur l'accueil).
 */
function me_partners_strip() {
	$p = me_presentation();
	echo '<div class="partners" role="list">';
	foreach ( $p['partenaires'] as $a ) {
		$logo = me_partner_logo( $a[1] );
		if ( $logo ) {
			echo '<span class="partner has-logo" role="listitem"><span class="partner-logo"><img src="' . esc_url( $logo ) . '" alt="' . esc_attr( sprintf( /* translators: %s: compagnie aérienne */ __( 'Logo %s', 'mondial-express' ), $a[0] ) ) . '" loading="lazy" decoding="async"></span><span class="partner-name">' . esc_html( $a[0] ) . '</span></span>';
		} else {
			echo '<span class="partner" role="listitem"><b>' . esc_html( $a[1] ) . '</b>' . esc_html( $a[0] ) . '</span>';
		}
	}
	echo '</div>';
}
