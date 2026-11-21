<?php
/**
 * Archives (catégories, étiquettes, dates).
 *
 * @package MondialExpress
 */

get_header();
me_page_hero( wp_strip_all_tags( get_the_archive_title() ), wp_strip_all_tags( get_the_archive_description() ), '#MondialTrip' );
?>
<section class="section section--tight">
	<div class="container">
		<?php
		$cats = get_categories( array( 'hide_empty' => true ) );
		if ( $cats ) {
			echo '<nav class="pills" aria-label="' . esc_attr__( 'Catégories', 'mondial-express' ) . '">';
			foreach ( $cats as $c ) {
				echo '<a class="pill-link" href="' . esc_url( get_category_link( $c ) ) . '">' . esc_html( $c->name ) . '</a>';
			}
			echo '</nav>';
		}
		?>
		<?php if ( have_posts() ) : ?>
			<div class="grid grid--3"><?php while ( have_posts() ) : the_post(); get_template_part( 'template-parts/post-card' ); endwhile; ?></div>
			<?php the_posts_pagination( array( 'prev_text' => '‹', 'next_text' => '›' ) ); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'Aucun article pour le moment.', 'mondial-express' ); ?></p>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
