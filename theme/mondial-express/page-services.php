<?php
/**
 * Page « Services ».
 *
 * @package MondialExpress
 */

get_header();
the_post();
me_page_hero( get_the_title(), __( "Billetterie, visas, hôtels, Hajj et Oumra, évacuation sanitaire et voyages d'affaires : une seule équipe pour tout votre voyage.", 'mondial-express' ) );
?>
<section class="section section--tight">
	<div class="container">
		<?php get_template_part( 'template-parts/service-grid' ); ?>
		<div class="prose prose--center"><?php the_content(); ?></div>
	</div>
</section>
<div class="container"><?php get_template_part( 'template-parts/cta-band' ); ?></div>
<div class="container"><?php get_template_part( 'template-parts/app-promo' ); ?></div>
<?php
get_footer();
