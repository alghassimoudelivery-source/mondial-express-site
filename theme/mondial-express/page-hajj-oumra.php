<?php
/**
 * Page « Hajj & Oumra ».
 *
 * @package MondialExpress
 */

get_header();
the_post();
me_page_hero( get_the_title(), has_excerpt() ? get_the_excerpt() : '', __( 'Voyages religieux', 'mondial-express' ) );
$f = new WP_Query( array( 'post_type' => 'me_forfait', 'posts_per_page' => -1, 'orderby' => 'menu_order date', 'order' => 'ASC' ) );
?>
<section class="section section--tight">
	<div class="container">
		<div class="grid grid--3"><?php while ( $f->have_posts() ) : $f->the_post(); get_template_part( 'template-parts/forfait-card' ); endwhile; wp_reset_postdata(); ?></div>
	</div>
</section>
<div class="container page-body page-body--aside">
	<article class="prose"><?php the_content(); ?></article>
	<aside class="aside-form" id="demande"><h2><?php esc_html_e( 'Pré-inscription', 'mondial-express' ); ?></h2><?php echo me_form_notice(); echo me_request_form( 'hajj' ); // phpcs:ignore ?></aside>
</div>
<?php
get_footer();
