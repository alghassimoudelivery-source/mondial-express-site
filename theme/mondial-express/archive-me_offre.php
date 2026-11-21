<?php
/**
 * Liste des offres / destinations.
 *
 * @package MondialExpress
 */

get_header();
me_page_hero( __( 'Destinations & offres', 'mondial-express' ), __( "Billets d'avion au départ de Conakry. Les prix sont donnés à titre indicatif et varient selon les dates : demandez votre devis.", 'mondial-express' ) );
?>
<section class="section section--tight">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<div class="grid grid--4"><?php while ( have_posts() ) : the_post(); get_template_part( 'template-parts/offer-card' ); endwhile; ?></div>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'Aucune offre pour le moment.', 'mondial-express' ); ?></p>
		<?php endif; ?>
	</div>
</section>
<div class="container"><?php get_template_part( 'template-parts/app-promo' ); ?></div>
<?php
get_footer();
