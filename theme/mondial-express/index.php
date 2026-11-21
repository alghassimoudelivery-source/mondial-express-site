<?php
/**
 * Gabarit de secours.
 *
 * @package MondialExpress
 */

get_header();
?>
<section class="section"><div class="container">
	<?php if ( have_posts() ) : ?>
		<div class="grid grid--3"><?php while ( have_posts() ) : the_post(); get_template_part( 'template-parts/post-card' ); endwhile; ?></div>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Aucun contenu.', 'mondial-express' ); ?></p>
	<?php endif; ?>
</div></section>
<?php
get_footer();
