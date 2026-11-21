<?php
/**
 * Bandeau vert « Chaque voyage est une histoire ».
 *
 * @package MondialExpress
 */
?>
<section class="band">
	<div class="band__text">
		<h2><?php esc_html_e( 'Chaque voyage est une histoire !', 'mondial-express' ); ?></h2>
		<p><?php esc_html_e( "Suivez votre dossier en ligne ou dans l'application Mondial Express.", 'mondial-express' ); ?></p>
	</div>
	<dl class="band__stats">
		<div><dt><?php esc_html_e( 'voyageurs accompagnés', 'mondial-express' ); ?></dt><dd><?php echo esc_html( me_opt( 'stat_voyageurs' ) ); ?></dd></div>
		<div><dt><?php esc_html_e( 'destinations', 'mondial-express' ); ?></dt><dd><?php echo esc_html( me_opt( 'stat_dest' ) ); ?></dd></div>
		<div><dt><?php esc_html_e( 'de visas obtenus', 'mondial-express' ); ?></dt><dd><?php echo esc_html( me_opt( 'stat_visas' ) ); ?></dd></div>
	</dl>
</section>
