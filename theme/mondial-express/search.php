<?php
/**
 * Résultats de recherche.
 *
 * @package MondialExpress
 */

get_header();
/* translators: %s: recherche */
me_page_hero( sprintf( __( 'Résultats pour « %s »', 'mondial-express' ), esc_html( get_search_query() ) ) );
?>
<section class="section section--tight"><div class="container">
	<?php get_search_form(); ?>
	<?php if ( have_posts() ) : ?>
		<div class="grid grid--3"><?php while ( have_posts() ) : the_post(); get_template_part( 'template-parts/post-card' ); endwhile; ?></div>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Aucun résultat. Essayez un autre mot ou contactez-nous sur WhatsApp.', 'mondial-express' ); ?></p>
	<?php endif; ?>
</div></section>
<?php
get_footer();
