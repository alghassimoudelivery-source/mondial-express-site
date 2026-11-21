<?php
/**
 * Page introuvable.
 *
 * @package MondialExpress
 */

get_header();
?>
<section class="error404-box container">
	<p class="error404-box__code">404</p>
	<h1><?php esc_html_e( 'Ce vol n’existe pas…', 'mondial-express' ); ?></h1>
	<p><?php esc_html_e( 'La page demandée est introuvable. Revenez à l’accueil ou dites-nous ce que vous cherchez.', 'mondial-express' ); ?></p>
	<p class="hero__cta"><a class="btn" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( "Retour à l'accueil", 'mondial-express' ); ?></a> <a class="btn btn--green" href="<?php echo esc_url( me_whatsapp_url() ); ?>" target="_blank" rel="noopener"><?php echo me_icon( 'whatsapp' ); ?> WhatsApp</a></p>
</section>
<?php
get_footer();
