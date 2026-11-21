<?php
/**
 * Bloc de promotion de l'application mobile.
 *
 * @package MondialExpress
 */
?>
<section class="app-promo" aria-labelledby="app-promo-titre">
	<div class="app-promo__phone" aria-hidden="true">
		<div class="mini-phone">
			<div class="mini-phone__head"><small><?php esc_html_e( 'Bonjour Aïssatou', 'mondial-express' ); ?></small><b><?php esc_html_e( 'Où partez-vous ?', 'mondial-express' ); ?></b></div>
			<div class="mini-phone__card"><b>CKY</b><?php echo me_icon( 'plane' ); ?><b>DXB</b></div>
			<ul class="mini-phone__steps"><li class="ok"><?php esc_html_e( 'Demande reçue', 'mondial-express' ); ?></li><li class="ok"><?php esc_html_e( 'Documents validés', 'mondial-express' ); ?></li><li class="now"><?php esc_html_e( 'Rendez-vous le 25 nov.', 'mondial-express' ); ?></li></ul>
		</div>
	</div>
	<div class="app-promo__text">
		<p class="kicker"><?php esc_html_e( 'Application mobile', 'mondial-express' ); ?></p>
		<h2 id="app-promo-titre"><?php esc_html_e( 'Votre agence dans votre poche', 'mondial-express' ); ?></h2>
		<p><?php esc_html_e( "Demandez un billet, envoyez vos documents de visa et suivez votre dossier en temps réel avec l'application Mondial Express.", 'mondial-express' ); ?></p>
		<ul class="checks"><li><?php esc_html_e( 'Suivi de dossier', 'mondial-express' ); ?></li><li><?php esc_html_e( 'Alertes promotions', 'mondial-express' ); ?></li><li><?php esc_html_e( 'Documents en photo', 'mondial-express' ); ?></li></ul>
	</div>
	<div class="app-promo__dl">
		<a class="btn" href="<?php echo esc_url( home_url( '/app/' ) ); ?>"><?php echo me_icon( 'arrow' ); ?> <?php esc_html_e( "Ouvrir l'application", 'mondial-express' ); ?></a>
		<?php echo me_app_badges(); // phpcs:ignore ?>
		<a class="link-arrow" href="<?php echo esc_url( home_url( '/application-mobile/' ) ); ?>"><?php esc_html_e( "Découvrir l'application", 'mondial-express' ); ?> <?php echo me_icon( 'arrow' ); ?></a>
	</div>
</section>
