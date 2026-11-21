<?php
/**
 * Article du blog.
 *
 * @package MondialExpress
 */

get_header();
the_post();
$cat = get_the_category();
?>
<header class="page-hero page-hero--post">
	<div class="container container--narrow">
		<?php me_breadcrumb(); ?>
		<p class="kicker"><?php echo $cat ? esc_html( $cat[0]->name ) : '#MondialTrip'; ?></p>
		<h1><?php the_title(); ?></h1>
		<p class="post-meta"><?php echo esc_html( get_the_date() ); ?> · <?php echo esc_html( max( 1, round( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 200 ) ) ); ?> <?php esc_html_e( 'min de lecture', 'mondial-express' ); ?></p>
		<?php echo me_share_buttons(); // phpcs:ignore ?>
	</div>
</header>
<?php if ( has_post_thumbnail() ) : ?>
	<div class="container container--narrow post-cover"><?php the_post_thumbnail( 'me-hero' ); ?></div>
<?php endif; ?>
<div class="container container--narrow page-body">
	<article class="prose prose--article">
		<?php the_content(); ?>
	</article>
	<div class="post-end">
		<?php the_tags( '<p class="tags">', ' ', '</p>' ); ?>
		<?php echo me_share_buttons(); // phpcs:ignore ?>
	</div>
	<aside class="post-cta">
		<div><h2><?php esc_html_e( 'Un projet de voyage ?', 'mondial-express' ); ?></h2><p><?php esc_html_e( 'Nos conseillers s’occupent de votre billet et de votre visa.', 'mondial-express' ); ?></p></div>
		<a class="btn" href="<?php echo esc_url( home_url( '/demande-de-devis/' ) ); ?>"><?php esc_html_e( 'Demander un devis', 'mondial-express' ); ?></a>
	</aside>
</div>
<?php
$rel = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => 3, 'post__not_in' => array( get_the_ID() ), 'category__in' => wp_list_pluck( $cat, 'term_id' ), 'no_found_rows' => true ) );
if ( $rel->have_posts() ) :
	?>
<section class="section section--tight"><div class="container"><header class="section__head"><h2><?php esc_html_e( 'À lire aussi', 'mondial-express' ); ?></h2></header><div class="grid grid--3"><?php while ( $rel->have_posts() ) : $rel->the_post(); get_template_part( 'template-parts/post-card' ); endwhile; wp_reset_postdata(); ?></div></div></section>
<?php endif; ?>
<?php
get_footer();
