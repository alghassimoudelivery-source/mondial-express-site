<?php
/**
 * Détail d'une offre.
 *
 * @package MondialExpress
 */

get_header();
the_post();
$pid  = get_the_ID();
$code = get_post_meta( $pid, '_me_code', true );
?>
<header class="offer-hero">
	<?php echo me_offer_visual( $pid, 'me-hero' ); // phpcs:ignore ?>
	<div class="container offer-hero__text">
		<?php me_breadcrumb(); ?>
		<p class="kicker kicker--light"><?php echo esc_html( get_post_meta( $pid, '_me_pays', true ) ); ?></p>
		<h1><?php the_title(); ?><?php echo $code ? ' <small>' . esc_html( $code ) . '</small>' : ''; ?></h1>
	</div>
</header>
<div class="container page-body page-body--aside">
	<article class="prose">
		<p class="offer-price"><?php echo esc_html( get_post_meta( $pid, '_me_type_prix', true ) ? get_post_meta( $pid, '_me_type_prix', true ) : __( 'Aller-retour', 'mondial-express' ) ); ?> <?php esc_html_e( 'depuis Conakry dès', 'mondial-express' ); ?> <b><?php echo esc_html( me_price( get_post_meta( $pid, '_me_prix', true ) ) ); ?></b> <?php echo me_price_note( $pid ); // phpcs:ignore ?></p>
		<?php the_content(); ?>
		<?php echo me_share_buttons(); // phpcs:ignore ?>
	</article>
	<aside class="aside-form" id="demande">
		<h2><?php esc_html_e( 'Demander ce billet', 'mondial-express' ); ?></h2>
		<?php echo me_form_notice(); // phpcs:ignore ?>
		<?php echo me_request_form( 'billet', array( 'prefill' => array( 'destination' => get_the_title() . ( $code ? " ($code)" : '' ) ) ) ); // phpcs:ignore ?>
		<?php echo me_payment_box(); // phpcs:ignore ?>
	</aside>
</div>
<?php
$autres = new WP_Query( array( 'post_type' => 'me_offre', 'posts_per_page' => 4, 'post__not_in' => array( $pid ), 'no_found_rows' => true ) );
if ( $autres->have_posts() ) :
	?>
<section class="section section--tight"><div class="container"><header class="section__head"><h2><?php esc_html_e( 'Autres destinations', 'mondial-express' ); ?></h2></header><div class="grid grid--4"><?php while ( $autres->have_posts() ) : $autres->the_post(); get_template_part( 'template-parts/offer-card' ); endwhile; wp_reset_postdata(); ?></div></div></section>
<?php endif; ?>
<?php
get_footer();
