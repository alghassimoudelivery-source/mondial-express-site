<?php
/**
 * Page « Application mobile ».
 *
 * @package MondialExpress
 */

get_header();
the_post();
me_page_hero( get_the_title(), __( "Demandes, documents, suivi de dossier et offres exclusives : l'agence Mondial Express dans votre téléphone.", 'mondial-express' ), __( 'Android et iPhone', 'mondial-express' ) );
?>
<div class="container"><?php get_template_part( 'template-parts/app-promo' ); ?></div>
<section class="section section--tight app-screens">
	<div class="container">
		<div class="section__head section__head--center"><p class="kicker"><?php esc_html_e( 'Disponible dès maintenant', 'mondial-express' ); ?></p><h2><?php esc_html_e( "Essayez l'application", 'mondial-express' ); ?></h2><p><?php esc_html_e( "Elle fonctionne directement depuis votre téléphone, sans téléchargement lourd. Installez-la sur votre écran d'accueil en un geste.", 'mondial-express' ); ?></p></div>
		<div class="app-screens__row">
			<?php
			foreach ( array( 'app-accueil' => __( 'Accueil et recherche', 'mondial-express' ), 'app-suivi' => __( 'Suivi du dossier', 'mondial-express' ), 'app-compte' => __( 'Espace client', 'mondial-express' ) ) as $img => $cap ) :
				?>
				<figure class="app-shot"><img src="<?php echo esc_url( ME_URI . '/assets/img/' . $img . '.webp' ); ?>" width="260" height="563" loading="lazy" alt="<?php echo esc_attr( sprintf( /* translators: %s: écran */ __( "Application Mondial Express : %s", 'mondial-express' ), $cap ) ); ?>"><figcaption><?php echo esc_html( $cap ); ?></figcaption></figure>
			<?php endforeach; ?>
		</div>
		<div class="app-install">
			<?php echo me_app_badges(); // phpcs:ignore ?>
			<a class="btn" href="<?php echo esc_url( home_url( '/app/' ) ); ?>"><?php echo me_icon( 'arrow' ); ?> <?php esc_html_e( "Ouvrir l'application", 'mondial-express' ); ?></a>
			<div class="app-install__how">
				<p><strong><?php esc_html_e( 'Android', 'mondial-express' ); ?></strong> · <?php esc_html_e( 'téléchargez-la gratuitement sur Google Play.', 'mondial-express' ); ?></p>
				<p><strong><?php esc_html_e( 'iPhone', 'mondial-express' ); ?></strong> · <?php esc_html_e( "dans Safari, touchez Partager puis « Sur l'écran d'accueil ».", 'mondial-express' ); ?></p>
			</div>
		</div>
	</div>
</section>
<section class="section section--tight">
	<div class="container">
		<div class="grid grid--3 features">
			<?php
			$feat = array(
				array( 'plane', __( 'Demande de billet en 30 secondes', 'mondial-express' ), __( 'Départ, destination, dates : votre conseiller vous répond avec les meilleures options.', 'mondial-express' ) ),
				array( 'upload', __( 'Documents envoyés en photo', 'mondial-express' ), __( "Passeport, photos, relevés : prenez-les en photo, ils arrivent directement dans votre dossier.", 'mondial-express' ) ),
				array( 'bell', __( 'Notifications à chaque étape', 'mondial-express' ), __( 'Documents validés, rendez-vous, visa obtenu, billet émis : vous êtes prévenu.', 'mondial-express' ) ),
				array( 'folder', __( 'Pensée pour les petits réseaux', 'mondial-express' ), __( 'Légère et rapide : offres et dossiers restent consultables même quand la connexion faiblit.', 'mondial-express' ) ),
				array( 'card', __( 'Paiement mobile', 'mondial-express' ), __( 'Orange Money, MTN MoMo ou carte, dès que le paiement en ligne est activé.', 'mondial-express' ) ),
				array( 'whatsapp', __( 'Votre conseiller en un geste', 'mondial-express' ), __( 'Appel ou WhatsApp directement depuis votre dossier.', 'mondial-express' ) ),
			);
			foreach ( $feat as $f ) :
				?>
				<div class="feature"><span class="service__ic"><?php echo me_icon( $f[0] ); ?></span><h3><?php echo esc_html( $f[1] ); ?></h3><p><?php echo esc_html( $f[2] ); ?></p></div>
			<?php endforeach; ?>
		</div>
		<div class="prose prose--center"><?php the_content(); ?></div>
	</div>
</section>
<?php
get_footer();
