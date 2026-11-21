<?php
/**
 * Page d'accueil.
 *
 * @package MondialExpress
 */

get_header();
$tabs = array(
	'billet' => array( 'plane', __( "Billet d'avion", 'mondial-express' ) ),
	'visa'   => array( 'pass', __( 'Visa', 'mondial-express' ) ),
	'hotel'  => array( 'bed', __( 'Hôtel', 'mondial-express' ) ),
	'hajj'   => array( 'kaaba', __( 'Hajj & Oumra', 'mondial-express' ) ),
);
?>
<section class="hero">
	<div class="container hero__grid">
		<div class="hero__text">
			<p class="kicker">#MondialTrip · <?php esc_html_e( 'Agence de voyage à Conakry', 'mondial-express' ); ?></p>
			<h1><?php echo wp_kses_post( __( 'Un <em>voyage réussi</em> commence par une agence qui <strong>comprend ses voyageurs.</strong>', 'mondial-express' ) ); ?></h1>
			<p class="hero__lead"><?php esc_html_e( "Billets d'avion, visas, hôtels, Hajj et Oumra : nous adaptons chaque service à vos besoins, de la demande jusqu'à l'embarquement.", 'mondial-express' ); ?></p>
			<div class="hero__cta">
				<a class="btn" href="#recherche"><?php esc_html_e( 'Réserver un billet', 'mondial-express' ); ?> <?php echo me_icon( 'arrow' ); ?></a>
				<a class="btn btn--green" href="<?php echo esc_url( me_whatsapp_url() ); ?>" target="_blank" rel="noopener"><?php echo me_icon( 'whatsapp' ); ?> <?php esc_html_e( 'Écrire sur WhatsApp', 'mondial-express' ); ?></a>
			</div>
		</div>
		<div class="hero__visual">
			<div class="hero__photo">
				<picture>
					<source srcset="<?php echo esc_url( ME_URI . '/assets/img/voyageuse.webp' ); ?>" type="image/webp">
					<img src="<?php echo esc_url( ME_URI . '/assets/img/voyageuse.jpg' ); ?>" alt="<?php esc_attr_e( 'Voyageuse souriante avec sa valise, prête à embarquer avec Mondial Express', 'mondial-express' ); ?>" width="900" height="890" fetchpriority="high">
				</picture>
			</div>
			<div class="hero__badge hero__badge--a"><span class="hero__badge-ic hero__badge-ic--green"><?php echo me_icon( 'pass' ); ?></span><span><b><?php esc_html_e( 'Visa obtenu', 'mondial-express' ); ?></b><small><?php esc_html_e( 'Dossier ME-2611-2418', 'mondial-express' ); ?></small></span></div>
			<div class="hero__badge hero__badge--b"><span class="hero__badge-ic"><?php echo me_icon( 'plane' ); ?></span><span><b>CKY → IST</b><small><?php esc_html_e( 'Billet émis', 'mondial-express' ); ?></small></span></div>
		</div>
	</div>
	<div class="container">
		<div class="search" id="recherche">
			<div class="search__tabs" role="tablist" aria-label="<?php esc_attr_e( 'Type de demande', 'mondial-express' ); ?>">
				<?php $i = 0; foreach ( $tabs as $k => $t ) : ?>
					<button type="button" role="tab" id="tab-<?php echo esc_attr( $k ); ?>" aria-controls="panel-<?php echo esc_attr( $k ); ?>" aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>" <?php echo 0 === $i ? '' : 'tabindex="-1"'; ?>><?php echo me_icon( $t[0] ); ?> <?php echo esc_html( $t[1] ); ?></button>
				<?php $i++; endforeach; ?>
			</div>
			<?php echo me_form_notice(); // phpcs:ignore ?>
			<?php $i = 0; foreach ( $tabs as $k => $t ) : ?>
				<div class="search__panel" role="tabpanel" id="panel-<?php echo esc_attr( $k ); ?>" aria-labelledby="tab-<?php echo esc_attr( $k ); ?>" <?php echo 0 === $i ? '' : 'hidden'; ?>>
					<?php echo me_request_form( $k, array( 'compact' => true ) ); // phpcs:ignore ?>
				</div>
			<?php $i++; endforeach; ?>
		</div>
	</div>
</section>

<section class="section">
	<div class="container">
		<header class="section__head"><div><h2><?php esc_html_e( 'Nos services', 'mondial-express' ); ?></h2><p><?php esc_html_e( 'Un seul interlocuteur pour tout votre voyage.', 'mondial-express' ); ?></p></div><a class="link-arrow" href="<?php echo esc_url( home_url( '/services/' ) ); ?>"><?php esc_html_e( 'Tous les services', 'mondial-express' ); ?> <?php echo me_icon( 'arrow' ); ?></a></header>
		<?php get_template_part( 'template-parts/service-grid' ); ?>
	</div>
</section>

<?php
$offres = new WP_Query( array( 'post_type' => 'me_offre', 'posts_per_page' => 4, 'meta_key' => '_me_vedette', 'meta_value' => '1', 'no_found_rows' => true ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
if ( ! $offres->have_posts() ) {
	$offres = new WP_Query( array( 'post_type' => 'me_offre', 'posts_per_page' => 4, 'no_found_rows' => true ) );
}
if ( $offres->have_posts() ) :
	?>
<section class="section section--tight">
	<div class="container">
		<header class="section__head"><div><h2><?php esc_html_e( 'Offres du moment', 'mondial-express' ); ?></h2><p><?php esc_html_e( 'Départs depuis Conakry.', 'mondial-express' ); ?></p></div><a class="btn btn--green" href="<?php echo esc_url( get_post_type_archive_link( 'me_offre' ) ); ?>"><?php esc_html_e( 'Toutes les offres', 'mondial-express' ); ?></a></header>
		<div class="grid grid--4">
			<?php while ( $offres->have_posts() ) : $offres->the_post(); get_template_part( 'template-parts/offer-card' ); endwhile; wp_reset_postdata(); ?>
		</div>
	</div>
</section>
<?php endif; ?>

<div class="container"><?php get_template_part( 'template-parts/cta-band' ); ?></div>

<section class="section">
	<div class="container why">
		<div>
			<p class="kicker"><?php esc_html_e( 'Pourquoi Mondial Express', 'mondial-express' ); ?></p>
			<h2><?php esc_html_e( 'Une agence qui vous accompagne, du premier message au retour', 'mondial-express' ); ?></h2>
			<p><?php esc_html_e( 'Nos conseillers à Conakry connaissent les exigences des compagnies et des ambassades. Ils vous évitent les mauvaises surprises et vous font gagner du temps.', 'mondial-express' ); ?></p>
			<a class="btn" href="<?php echo esc_url( home_url( '/a-propos/' ) ); ?>"><?php esc_html_e( "Découvrir l'agence", 'mondial-express' ); ?></a>
		</div>
		<ul class="why__list">
			<li><?php echo me_icon( 'check' ); ?><span><b><?php esc_html_e( 'Meilleurs tarifs', 'mondial-express' ); ?></b><?php esc_html_e( 'Nous comparons les compagnies pour vous.', 'mondial-express' ); ?></span></li>
			<li><?php echo me_icon( 'pass' ); ?><span><b><?php esc_html_e( 'Dossiers visa soignés', 'mondial-express' ); ?></b><?php esc_html_e( 'Liste des pièces, vérification et prise de rendez-vous.', 'mondial-express' ); ?></span></li>
			<li><?php echo me_icon( 'bell' ); ?><span><b><?php esc_html_e( 'Suivi en temps réel', 'mondial-express' ); ?></b><?php esc_html_e( 'Votre dossier sur le site, dans l’app et par WhatsApp.', 'mondial-express' ); ?></span></li>
			<li><?php echo me_icon( 'card' ); ?><span><b><?php esc_html_e( 'Paiement simple', 'mondial-express' ); ?></b><?php esc_html_e( 'Orange Money, MTN MoMo, espèces ou carte.', 'mondial-express' ); ?></span></li>
		</ul>
	</div>
</section>

<?php
$forfaits = new WP_Query( array( 'post_type' => 'me_forfait', 'posts_per_page' => 3, 'no_found_rows' => true ) );
if ( $forfaits->have_posts() ) :
	?>
<section class="section section--hajj">
	<div class="container">
		<header class="section__head"><div><p class="kicker"><?php esc_html_e( 'Voyages religieux', 'mondial-express' ); ?></p><h2><?php esc_html_e( 'Hajj & Oumra 2027', 'mondial-express' ); ?></h2><p><?php esc_html_e( 'Vols, visa, hébergement près du Haram, transferts et accompagnement par nos guides.', 'mondial-express' ); ?></p></div><a class="link-arrow" href="<?php echo esc_url( home_url( '/hajj-oumra/' ) ); ?>"><?php esc_html_e( 'Voir les forfaits', 'mondial-express' ); ?> <?php echo me_icon( 'arrow' ); ?></a></header>
		<div class="grid grid--3">
			<?php while ( $forfaits->have_posts() ) : $forfaits->the_post(); get_template_part( 'template-parts/forfait-card' ); endwhile; wp_reset_postdata(); ?>
		</div>
	</div>
</section>
<?php endif; ?>

<section class="section">
	<div class="container">
		<header class="section__head"><div><h2><?php esc_html_e( 'Ils ont voyagé avec nous', 'mondial-express' ); ?></h2><p><?php esc_html_e( 'Témoignages d’exemple, à remplacer par les avis réels des clients.', 'mondial-express' ); ?></p></div></header>
		<div class="grid grid--3 testimonials">
			<?php
			$avis = array(
				array( 'Mon visa pour le Canada a été obtenu en trois semaines. L’équipe m’a suivie à chaque étape sur WhatsApp.', 'Fatoumata', 'Ratoma' ),
				array( 'Billet Conakry–Istanbul trouvé au meilleur prix, et un conseiller disponible même le samedi.', 'Mamadou', 'Kaloum' ),
				array( 'Oumra très bien organisée : hôtel proche du Haram et un guide attentif pour ma mère.', 'Aïssatou', 'Matoto' ),
			);
			foreach ( $avis as $a ) :
				?>
				<figure class="testimonial">
					<blockquote>« <?php echo esc_html( $a[0] ); ?> »</blockquote>
					<figcaption><span class="avatar" aria-hidden="true"><?php echo esc_html( mb_substr( $a[1], 0, 1 ) ); ?></span><span><b><?php echo esc_html( $a[1] ); ?></b><small><?php echo esc_html( $a[2] ); ?> · <?php esc_html_e( 'exemple', 'mondial-express' ); ?></small></span></figcaption>
				</figure>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<div class="container"><?php get_template_part( 'template-parts/app-promo' ); ?></div>

<?php
$blog = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => 3, 'no_found_rows' => true, 'ignore_sticky_posts' => true ) );
if ( $blog->have_posts() ) :
	?>
<section class="section">
	<div class="container">
		<header class="section__head"><div><h2><?php esc_html_e( 'Conseils & actualités #MondialTrip', 'mondial-express' ); ?></h2><p><?php esc_html_e( 'Visas, bagages, destinations : nos conseils pour bien préparer votre voyage.', 'mondial-express' ); ?></p></div><a class="link-arrow" href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>"><?php esc_html_e( 'Tous les articles', 'mondial-express' ); ?> <?php echo me_icon( 'arrow' ); ?></a></header>
		<div class="grid grid--3">
			<?php while ( $blog->have_posts() ) : $blog->the_post(); get_template_part( 'template-parts/post-card' ); endwhile; wp_reset_postdata(); ?>
		</div>
	</div>
</section>
<?php endif; ?>
<?php
get_footer();
