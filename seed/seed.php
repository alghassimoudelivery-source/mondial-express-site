<?php
/**
 * Contenu de démonstration Mondial Express — ConforDev.
 *
 * À lancer UNE FOIS sur la nouvelle installation (ou le site de préproduction), jamais sur un site déjà rempli :
 *   wp eval-file seed.php            (WP-CLI, depuis le dossier WordPress)
 *   php seed.php /chemin/vers/wordpress
 * Le dossier « seed-images » doit se trouver à côté de ce fichier.
 * Idempotent : ne recrée pas un contenu qui existe déjà (recherche par slug).
 */
if ( ! defined( 'ABSPATH' ) ) {
	$root = $argv[1] ?? __DIR__ . '/site';
	if ( empty( $_SERVER['HTTP_HOST'] ) ) {
		$_SERVER['HTTP_HOST'] = 'localhost:8080';
	}
	define( 'WP_USE_THEMES', false );
	require $root . '/wp-load.php';
}
require_once ABSPATH . 'wp-admin/includes/post.php';
require_once ABSPATH . 'wp-admin/includes/taxonomy.php';
require_once ABSPATH . 'wp-admin/includes/nav-menu.php';

switch_theme( 'mondial-express' );
do_action( 'after_switch_theme' );
update_option( 'blogname', 'Mondial Express Guinée' );
update_option( 'blogdescription', "Agence de voyage à Conakry : billets d'avion, assistance visa, hôtels, Hajj et Oumra, évacuation sanitaire. Chaque voyage est une histoire !" );
update_option( 'permalink_structure', '/%postname%/' );
update_option( 'date_format', 'd/m/Y' );
update_option( 'timezone_string', 'Africa/Conakry' );
update_option( 'default_comment_status', 'closed' );
update_option( 'blog_public', 1 );

function me_seed_page( $slug, $title, $content, $excerpt = '', $parent = 0, $order = 0 ) {
	$path = $parent ? get_post_field( 'post_name', $parent ) . '/' . $slug : $slug;
	$p    = get_page_by_path( $path );
	$data = array( 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $title, 'post_content' => $content, 'post_excerpt' => $excerpt, 'post_parent' => $parent, 'menu_order' => $order );
	if ( $p ) {
		$data['ID'] = $p->ID;
		return wp_update_post( $data );
	}
	return wp_insert_post( $data );
}
function me_seed_post( $type, $slug, $title, $content, $excerpt, $meta = array(), $extra = array() ) {
	$p = get_page_by_path( $slug, OBJECT, $type );
	$data = array_merge( array( 'post_type' => $type, 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $title, 'post_content' => $content, 'post_excerpt' => $excerpt, 'meta_input' => $meta ), $extra );
	if ( $p ) {
		$data['ID'] = $p->ID;
		return wp_update_post( $data );
	}
	return wp_insert_post( $data );
}

// Nettoyage du contenu d'exemple de WordPress.
foreach ( array( 'hello-world', 'sample-page', 'bonjour-tout-le-monde', 'page-d-exemple' ) as $s ) {
	foreach ( array( 'post', 'page' ) as $t ) {
		$x = get_page_by_path( $s, OBJECT, $t );
		if ( $x ) { wp_delete_post( $x->ID, true ); }
	}
}

$wa = 'https://wa.me/224613003970';
// ---------- Pages ----------
$accueil  = me_seed_page( 'accueil', 'Accueil', '' );
$blog     = me_seed_page( 'blog', 'Blog #MondialTrip', '' );
$services = me_seed_page( 'services', 'Nos services', '<p>Depuis Conakry, Mondial Express Guinée accompagne particuliers, familles, pèlerins, entreprises et ONG dans tous leurs déplacements. Un seul interlocuteur s’occupe de votre billet, de votre visa, de votre hôtel et de votre assurance.</p>', '', 0, 2 );

$svc_content = array(
	'billetterie' => array( 'Billetterie', 'Billets d’avion au meilleur tarif sur toutes les compagnies, au départ de Conakry.', '<p>Nos conseillers comparent les compagnies desservant Conakry (Air France, Brussels Airlines, Royal Air Maroc, Turkish Airlines, Emirates, ASKY, Air Côte d’Ivoire…) pour vous proposer le meilleur rapport prix, horaires et bagages.</p><h2>Ce que nous faisons pour vous</h2><ul><li>Recherche des meilleurs tarifs et des escales les plus courtes</li><li>Réservation avec option pour bloquer un prix</li><li>Choix du siège, bagages supplémentaires, repas spéciaux</li><li>Modification ou annulation selon les conditions du billet</li><li>Billets de groupe : familles, étudiants, délégations</li></ul><h2>Comment ça marche ?</h2><ol><li>Vous envoyez votre demande (formulaire, WhatsApp ou à l’agence).</li><li>Nous vous proposons 2 ou 3 options avec les prix.</li><li>Vous validez et réglez (espèces, Orange Money, MTN MoMo ou carte).</li><li>Votre billet électronique vous est envoyé et reste disponible dans votre <a href="/mon-espace/">espace client</a>.</li></ol><p>Consultez aussi nos <a href="/destinations/">offres du moment</a> et nos <a href="/blog/">conseils bagages</a>.</p>' ),
	'assistance-visa' => array( 'Assistance visa', 'Constitution du dossier, prise de rendez-vous et suivi jusqu’à l’obtention du visa.', '<p>Un dossier de visa incomplet est la première cause de refus. Nous vous donnons la liste exacte des pièces pour votre pays, nous vérifions chaque document et nous prenons votre rendez-vous.</p><h2>Destinations les plus demandées</h2><ul><li>France et espace Schengen</li><li>Canada (visiteur, études, travail)</li><li>États-Unis</li><li>Royaume-Uni</li><li>Turquie, Émirats arabes unis, Maroc, Chine</li></ul><h2>Notre accompagnement</h2><ul><li>Liste personnalisée des pièces à fournir</li><li>Relecture et mise en forme du dossier</li><li>Réservations d’hôtel et de vol pour le dossier</li><li>Prise de rendez-vous et préparation à l’entretien</li><li>Suivi du dossier en ligne avec votre <a href="/suivi-de-dossier/">numéro de dossier</a></li></ul><p>À lire : <a href="/visa-schengen-depuis-conakry-les-5-etapes/">Visa Schengen depuis Conakry : les 5 étapes</a>.</p>' ),
	'hotels-tourisme' => array( 'Hôtels & tourisme', 'Séjours, circuits et réservations d’hôtel clés en main.', '<p>Vacances en famille à Dubaï, voyage de noces au Maroc, séjour d’affaires à Paris : nous réservons votre hébergement et organisons vos activités.</p><ul><li>Hôtels de toutes catégories, appartements</li><li>Circuits organisés et excursions</li><li>Transferts aéroport</li><li>Assurance voyage</li></ul>' ),
	'hajj-oumra' => array( 'Hajj & Oumra', 'Forfaits accompagnés pour le pèlerinage, du visa au retour.', '<p>Chaque année, Mondial Express accompagne des pèlerins guinéens à La Mecque et à Médine. Retrouvez nos forfaits sur la page <a href="/hajj-oumra/">Hajj & Oumra</a>.</p><ul><li>Visa et billets d’avion</li><li>Hébergement proche du Haram</li><li>Transferts et visites</li><li>Encadrement par nos guides</li></ul>' ),
	'evacuation-sanitaire' => array( 'Transport & évacuation sanitaire', 'Organisation rapide d’une évacuation sanitaire ou d’un transport médical.', '<p>En cas d’urgence médicale, chaque heure compte. Nous organisons le transport du patient et de son accompagnant vers le pays de soins : billets, visa médical en urgence, coordination avec l’hôpital d’accueil.</p><ul><li>Disponibilité 7 jours sur 7</li><li>Visa médical accéléré</li><li>Places adaptées et assistance aéroport</li><li>Hébergement de l’accompagnant</li></ul><p>Urgence : appelez ou écrivez directement au <a href="' . $wa . '">+224 613 00 39 70</a>.</p>' ),
	'voyages-affaires' => array( 'Voyages d’affaires', 'Gestion des déplacements des entreprises, institutions et ONG.', '<p>Nous gérons les voyages de vos équipes : billets, visas, hôtels, facturation mensuelle et interlocuteur dédié.</p><ul><li>Compte entreprise et conditions négociées</li><li>Facturation groupée</li><li>Gestion des changements de dernière minute</li><li>Délégations et séminaires</li></ul>' ),
);
$i = 0;
foreach ( $svc_content as $slug => $c ) {
	me_seed_page( $slug, $c[0], $c[2], $c[1], $services, $i++ );
}

$hajj  = me_seed_page( 'hajj-oumra', 'Hajj & Oumra', '<h2>Un pèlerinage serein</h2><p>Nos forfaits comprennent les démarches de visa, les vols, l’hébergement, les transferts et l’accompagnement par des guides expérimentés. Les places sont limitées : inscrivez-vous tôt pour bénéficier des meilleurs hôtels.</p><h2>Documents à prévoir</h2><ul><li>Passeport valide au moins 6 mois après le retour</li><li>Photos d’identité sur fond blanc</li><li>Carnet de vaccination (méningite obligatoire)</li><li>Pour les femmes de moins de 45 ans : accompagnement selon la réglementation saoudienne en vigueur</li></ul>', 'Forfaits Hajj et Oumra 2027 au départ de Conakry : vols, visa, hôtels proches du Haram et accompagnement.', 0, 3 );
$suivi = me_seed_page( 'suivi-de-dossier', 'Suivi de dossier', '<p>Votre numéro de dossier figure sur le reçu remis par l’agence ou dans le message de confirmation. Pour essayer : numéro <strong>ME-2611-2418</strong> et chiffres <strong>3970</strong> (dossier de démonstration).</p>', '', 0, 5 );
$espace = me_seed_page( 'mon-espace', 'Mon espace client', '' );
$app    = me_seed_page( 'application-mobile', 'Application mobile', '<p>L’application Mondial Express est disponible gratuitement sur <a href="https://play.google.com/store/apps/details?id=com.mondialexpressguinee.app" target="_blank" rel="noopener">Google Play</a> pour les téléphones Android. Sur iPhone, utilisez la version web en attendant l’App Store. Elle est reliée à votre espace client : vos demandes et dossiers sont les mêmes sur le site et dans l’application.</p>', '', 0, 6 );
$devis  = me_seed_page( 'demande-de-devis', 'Demande de devis', '' );
$about  = me_seed_page( 'a-propos', 'À propos', '<p>Mondial Express Guinée est une agence de voyage installée à Conakry. Notre mission : rendre chaque voyage simple et serein, que vous partiez étudier, travailler, rendre visite à votre famille, vous soigner ou accomplir votre pèlerinage.</p><h2>Nos valeurs</h2><ul><li><strong>Écoute</strong> : chaque voyageur est différent, nous adaptons nos services à vos besoins.</li><li><strong>Transparence</strong> : des prix clairs et des conseils honnêtes.</li><li><strong>Réactivité</strong> : une réponse rapide, par WhatsApp, téléphone ou à l’agence.</li></ul><h2>Notre équipe</h2><p>Des conseillers formés aux procédures des compagnies aériennes et des ambassades. (Présentation de l’équipe à compléter avec les photos et les noms.)</p><blockquote>Chaque voyage est une histoire !</blockquote><p>Découvrez <a href="/services/">nos services</a> ou <a href="/contact/">contactez-nous</a>.</p>', 'Agence de voyage à Conakry, Mondial Express Guinée accompagne ses clients dans le monde entier.', 0, 7 );
$faq    = me_seed_page( 'faq', 'Questions fréquentes', '', '', 0, 8 );
$contact = me_seed_page( 'contact', 'Contact', '', '', 0, 9 );
$ml     = me_seed_page( 'mentions-legales', 'Mentions légales', '<h2>Éditeur du site</h2><p>Mondial Express Guinée, agence de voyage, Conakry, République de Guinée. Téléphone : +224 613 00 39 70. (Raison sociale, RCCM, adresse et responsable de publication à compléter.)</p><h2>Hébergement</h2><p>Hostinger International Ltd. (à confirmer).</p><h2>Réalisation</h2><p>Site réalisé par <a href="https://confordev.com">ConforDev</a>.</p><h2>Prix</h2><p>Les prix affichés sur le site sont indicatifs et dépendent des disponibilités des compagnies au moment de la réservation.</p>' );
$conf   = me_seed_page( 'confidentialite', 'Politique de confidentialité', '<p>Les informations envoyées par les formulaires (nom, téléphone, e-mail, détails du voyage) servent uniquement à traiter votre demande. Les documents transmis dans l’espace client sont stockés dans un dossier protégé et accessibles seulement à nos conseillers.</p><p>Vous pouvez demander la consultation, la correction ou la suppression de vos données en nous contactant.</p><p>Le site peut utiliser un outil de mesure d’audience (Google Analytics) une fois activé par l’agence.</p>' );
update_option( 'wp_page_for_privacy_policy', $conf );
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $accueil );
update_option( 'page_for_posts', $blog );

// ---------- Offres ----------
$offres = array(
	array( 'dubai', 'Dubaï', 6900000, 'DXB', 'Émirats arabes unis', 'orange', 1, 'Shopping, désert et gratte-ciel : Dubaï est à environ 8 heures de vol de Conakry avec une escale.' ),
	array( 'paris', 'Paris', 7500000, 'CDG', 'France', 'bleu', 1, 'Vols directs ou avec escale vers Paris-Charles de Gaulle. Visa Schengen requis : nous vous accompagnons.' ),
	array( 'istanbul', 'Istanbul', 6200000, 'IST', 'Turquie', 'violet', 1, 'Porte entre l’Europe et l’Asie, Istanbul est desservie par Turkish Airlines depuis Conakry.' ),
	array( 'casablanca', 'Casablanca', 4300000, 'CMN', 'Maroc', 'vert', 1, 'Vols Royal Air Maroc vers Casablanca, porte d’entrée du Maroc et escale vers l’Europe et l’Amérique.' ),
	array( 'montreal', 'Montréal', 12800000, 'YUL', 'Canada', 'bleu', 0, 'Études, travail ou visite familiale : rejoignez le Québec avec une ou deux escales.' ),
	array( 'abidjan', 'Abidjan', 2900000, 'ABJ', 'Côte d’Ivoire', 'orange', 0, 'Vols directs vers Abidjan, idéal pour les voyages d’affaires dans la sous-région.' ),
	array( 'dakar', 'Dakar', 2500000, 'DSS', 'Sénégal', 'violet', 0, 'Dakar en vol direct, pour les affaires, la famille ou le tourisme.' ),
	array( 'guangzhou', 'Guangzhou', 11900000, 'CAN', 'Chine', 'vert', 0, 'Destination des commerçants : billets et visa d’affaires pour la Chine.' ),
);
foreach ( $offres as $o ) {
	me_seed_post( 'me_offre', $o[0], $o[1], '<p>' . $o[7] . '</p><h2>Bon à savoir</h2><ul><li>Prix aller-retour en classe économique, taxes comprises, à titre indicatif</li><li>Les tarifs varient selon les dates et la disponibilité</li><li>Bagages et conditions selon la compagnie choisie</li></ul><p>Besoin d’un visa ? Voir notre <a href="/services/assistance-visa/">assistance visa</a>.</p>', $o[7], array( '_me_prix' => $o[2], '_me_prix_indicatif' => '1', '_me_code' => $o[3], '_me_pays' => $o[4], '_me_visuel' => $o[5], '_me_vedette' => $o[6] ? '1' : '', '_me_type_prix' => 'Aller-retour' ) );
}

// ---------- Forfaits ----------
$forf = array(
	array( 'oumra-ramadan-2027', 'Oumra Ramadan 2027', 28000000, 'oumra', 'Mars 2027', '15 jours', "Billet d’avion aller-retour\nVisa Oumra\nHôtel 4★ à 500 m du Haram\nTransferts en car climatisé\nVisites guidées à Médine\nEncadrement par nos guides", "Repas du soir\nDépenses personnelles" ),
	array( 'oumra-confort-2027', 'Oumra Confort', 22500000, 'oumra', 'Toute l’année', '10 jours', "Billet d’avion aller-retour\nVisa Oumra\nHôtel 3★ proche du Haram\nTransferts\nAccompagnement", "Repas\nDépenses personnelles" ),
	array( 'hajj-2027', 'Hajj 2027', 65000000, 'hajj', 'Mai – juin 2027', '21 jours', "Billet d’avion aller-retour\nVisa Hajj\nHébergement à La Mecque et Médine\nTentes climatisées à Mina\nPension complète\nEncadrement religieux", "Sacrifice (hadiy)\nDépenses personnelles" ),
);
foreach ( $forf as $k => $f ) {
	me_seed_post( 'me_forfait', $f[0], $f[1], '<p>Forfait ' . $f[1] . ' au départ de Conakry, accompagné par l’équipe Mondial Express. Programme détaillé remis à l’inscription.</p>', 'Forfait ' . $f[1] . ' au départ de Conakry : vols, visa, hôtel et accompagnement.', array( '_me_prix' => $f[2], '_me_prix_indicatif' => '1', '_me_type' => $f[3], '_me_dates' => $f[4], '_me_duree' => $f[5], '_me_inclus' => $f[6], '_me_non_inclus' => $f[7] ), array( 'menu_order' => $k ) );
}

// ---------- Articles ----------
$cat_visa = wp_create_category( 'Visas' );
$cat_cons = wp_create_category( 'Conseils de voyage' );
$cat_dest = wp_create_category( 'Destinations' );
wp_update_term( 1, 'category', array( 'name' => 'Actualités', 'slug' => 'actualites' ) );
$posts = array(
	array( 'visa-schengen-depuis-conakry-les-5-etapes', 'Visa Schengen depuis Conakry : les 5 étapes', $cat_visa, 'Pièces à fournir, rendez-vous, délais : la méthode pour préparer un dossier de visa Schengen solide depuis la Guinée.', '<p>Le visa Schengen permet de voyager dans 29 pays européens, dont la France, la Belgique, l’Espagne et l’Italie. Voici les cinq étapes pour mettre toutes les chances de votre côté.</p><h2>1. Choisir le bon consulat</h2><p>Déposez votre demande auprès du pays où vous passerez le plus de temps, ou de votre premier pays d’entrée si les durées sont égales.</p><h2>2. Rassembler les pièces</h2><ul><li>Passeport valide 3 mois après le retour, avec 2 pages vierges</li><li>Photos d’identité récentes</li><li>Relevés bancaires des 3 derniers mois</li><li>Réservation de vol et d’hôtel ou attestation d’accueil</li><li>Assurance voyage couvrant 30 000 €</li></ul><h2>3. Prendre rendez-vous</h2><p>Les créneaux partent vite en haute saison : anticipez de 6 à 8 semaines.</p><h2>4. Déposer le dossier</h2><p>Arrivez à l’heure avec les originaux et les copies classés dans l’ordre.</p><h2>5. Suivre la demande</h2><p>Avec Mondial Express, vous suivez l’avancement sur la page <a href="/suivi-de-dossier/">Suivi de dossier</a> ou dans l’application.</p><p>Besoin d’aide ? Découvrez notre <a href="/services/assistance-visa/">assistance visa</a>.</p>' ),
	array( 'bagages-ce-qu-il-faut-savoir-avant-de-partir', 'Bagages : ce qu’il faut savoir avant de partir', $cat_cons, 'Franchise, poids, objets interdits : nos conseils pour éviter les mauvaises surprises à l’aéroport de Conakry.', '<p>Chaque compagnie a ses propres règles. Vérifiez toujours les conditions indiquées sur votre billet.</p><h2>Bagage en soute</h2><p>En classe économique, la franchise est souvent de 1 ou 2 valises de 23 kg. Au-delà, les kilos supplémentaires coûtent cher : pesez votre valise avant de partir.</p><h2>Bagage cabine</h2><p>En général 1 bagage de 7 à 12 kg et un petit sac. Les liquides doivent être dans des flacons de 100 ml maximum.</p><h2>Objets interdits</h2><ul><li>Produits inflammables</li><li>Batteries externes en soute (elles vont en cabine)</li><li>Certains produits alimentaires selon le pays d’arrivée</li></ul><p>Préparez votre voyage avec nos <a href="/destinations/">offres de billets</a>.</p>' ),
	array( 'dubai-que-faire-pour-un-premier-voyage', 'Dubaï : que faire pour un premier voyage ?', $cat_dest, 'Burj Khalifa, désert, souks : notre sélection pour découvrir Dubaï en 5 jours.', '<p>Dubaï attire chaque année de nombreux voyageurs guinéens, pour le tourisme comme pour les affaires.</p><h2>Incontournables</h2><ul><li>Le Burj Khalifa et la fontaine de Dubaï</li><li>Une excursion dans le désert</li><li>Les souks de l’or et des épices</li><li>Dubai Mall</li></ul><h2>Visa</h2><p>Un visa est nécessaire pour les ressortissants guinéens. Nous nous en occupons : voir notre <a href="/services/assistance-visa/">assistance visa</a>.</p><p>Voir l’offre <a href="/destination/dubai/">Conakry – Dubaï</a>.</p>' ),
	array( 'oumra-2027-bien-preparer-son-pelerinage', 'Oumra 2027 : bien préparer son pèlerinage', $cat_cons, 'Documents, vaccins, budget : la liste pour préparer sereinement votre Oumra.', '<p>La Oumra peut être accomplie toute l’année, mais la période du Ramadan est la plus demandée. Réservez tôt.</p><h2>Documents</h2><ul><li>Passeport valide 6 mois</li><li>Vaccin contre la méningite</li><li>Photos d’identité</li></ul><h2>Budget</h2><p>Le prix dépend surtout de la distance entre l’hôtel et le Haram. Comparez nos <a href="/hajj-oumra/">forfaits Hajj & Oumra</a>.</p>' ),
);
foreach ( $posts as $k => $p ) {
	$id = me_seed_post( 'post', $p[0], $p[1], $p[4], $p[3], array(), array( 'post_category' => array( $p[2] ), 'post_date' => gmdate( 'Y-m-d H:i:s', strtotime( '-' . ( $k * 6 + 2 ) . ' days' ) ) ) );
	wp_set_post_tags( $id, array( 'Conakry', 'Voyage' ) );
}

// ---------- Dossier de démonstration ----------
$demo = get_posts( array( 'post_type' => 'me_dossier', 'meta_key' => '_me_numero', 'meta_value' => 'ME-2611-2418', 'fields' => 'ids' ) );
if ( ! $demo ) {
	wp_insert_post( array( 'post_type' => 'me_dossier', 'post_status' => 'publish', 'post_title' => 'Visa Turquie – Aïssatou B. (démonstration)', 'meta_input' => array( '_me_numero' => 'ME-2611-2418', '_me_type' => 'visa', '_me_etape' => 'rendez-vous', '_me_telephone' => '+224 613 00 39 70', '_me_rdv' => 'Dépôt à l’ambassade le 25 novembre à 9h00', '_me_message' => 'Pensez à apporter votre passeport original et 2 photos.', '_me_paiement' => 'solde' ) ) );
}

// ---------- Menus ----------
function me_seed_menu( $name, $location, $items ) {
	$m = wp_get_nav_menu_object( $name );
	$id = $m ? $m->term_id : wp_create_nav_menu( $name );
	foreach ( wp_get_nav_menu_items( $id ) ?: array() as $it ) { wp_delete_post( $it->ID, true ); }
	foreach ( $items as $it ) {
		$args = array( 'menu-item-status' => 'publish', 'menu-item-title' => $it[0] );
		if ( is_numeric( $it[1] ) ) { $args += array( 'menu-item-object' => 'page', 'menu-item-object-id' => $it[1], 'menu-item-type' => 'post_type' ); }
		else { $args += array( 'menu-item-url' => $it[1], 'menu-item-type' => 'custom' ); }
		$pid = wp_update_nav_menu_item( $id, 0, $args );
		foreach ( $it[2] ?? array() as $sub ) {
			wp_update_nav_menu_item( $id, 0, array( 'menu-item-status' => 'publish', 'menu-item-title' => $sub[0], 'menu-item-object' => 'page', 'menu-item-object-id' => $sub[1], 'menu-item-type' => 'post_type', 'menu-item-parent-id' => $pid ) );
		}
	}
	$locs = get_theme_mod( 'nav_menu_locations', array() );
	$locs[ $location ] = $id;
	set_theme_mod( 'nav_menu_locations', $locs );
}
$sub = array();
foreach ( array_keys( $svc_content ) as $s ) { $pp = get_page_by_path( 'services/' . $s ); $sub[] = array( $pp->post_title, $pp->ID ); }
me_seed_menu( 'Principal', 'principal', array(
	array( 'Accueil', $accueil ),
	array( 'Services', $services, $sub ),
	array( 'Destinations', home_url( '/destinations/' ) ),
	array( 'Hajj & Oumra', $hajj ),
	array( 'Suivi de dossier', $suivi ),
	array( 'Blog', $blog ),
	array( 'Contact', $contact ),
) );
me_seed_menu( 'Pied de page', 'pied', array(
	array( 'À propos', $about ),
	array( 'Blog #MondialTrip', $blog ),
	array( 'Application mobile', $app ),
	array( 'Questions fréquentes', $faq ),
	array( 'Mon espace client', $espace ),
) );

// Images à la une des articles (visuels de marque générés).
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
$me_feat = array(
	'visa-schengen-depuis-conakry-les-5-etapes' => 'visa-schengen',
	'bagages-ce-qu-il-faut-savoir-avant-de-partir' => 'bagages',
	'dubai-que-faire-pour-un-premier-voyage' => 'dubai',
	'oumra-2027-bien-preparer-son-pelerinage' => 'oumra',
);
foreach ( $me_feat as $slug => $img ) {
	$p = get_page_by_path( $slug, OBJECT, 'post' );
	$src = __DIR__ . '/seed-images/' . $img . '.jpg';
	if ( ! $p || has_post_thumbnail( $p ) || ! file_exists( $src ) ) {
		continue;
	}
	$up = wp_upload_bits( 'mondial-express-' . $img . '.jpg', null, file_get_contents( $src ) );
	if ( $up['error'] ) {
		continue;
	}
	$aid = wp_insert_attachment( array( 'post_mime_type' => 'image/jpeg', 'post_title' => $p->post_title, 'post_status' => 'inherit' ), $up['file'], $p->ID );
	wp_update_attachment_metadata( $aid, wp_generate_attachment_metadata( $aid, $up['file'] ) );
	update_post_meta( $aid, '_wp_attachment_image_alt', $p->post_title . ' – Mondial Express Guinée' );
	set_post_thumbnail( $p, $aid );
}

// Traductions anglais, espagnol et arabe.
require __DIR__ . '/seed-i18n.php';

flush_rewrite_rules();
echo "Contenu créé.\n";
