<?php
/**
 * Détail d'un forfait Hajj / Oumra.
 *
 * @package MondialExpress
 */

get_header();
the_post();
$pid = get_the_ID();
me_page_hero( get_the_title(), esc_html( get_post_meta( $pid, '_me_dates', true ) . ( get_post_meta( $pid, '_me_duree', true ) ? ' · ' . get_post_meta( $pid, '_me_duree', true ) : '' ) ), 'hajj' === get_post_meta( $pid, '_me_type', true ) ? 'Hajj' : 'Oumra' );
$inc = array_filter( array_map( 'trim', explode( "\n", (string) get_post_meta( $pid, '_me_inclus', true ) ) ) );
$exc = array_filter( array_map( 'trim', explode( "\n", (string) get_post_meta( $pid, '_me_non_inclus', true ) ) ) );
?>
<div class="container page-body page-body--aside">
	<article class="prose">
		<p class="offer-price"><?php esc_html_e( 'Prix par personne', 'mondial-express' ); ?> <b><?php echo esc_html( me_price( get_post_meta( $pid, '_me_prix', true ) ) ); ?></b> <?php echo me_price_note( $pid ); // phpcs:ignore ?></p>
		<?php the_content(); ?>
		<div class="incl">
			<?php if ( $inc ) : ?><div><h2><?php esc_html_e( 'Inclus', 'mondial-express' ); ?></h2><ul class="checks checks--col"><?php foreach ( $inc as $l ) { echo '<li>' . esc_html( $l ) . '</li>'; } ?></ul></div><?php endif; ?>
			<?php if ( $exc ) : ?><div><h2><?php esc_html_e( 'Non inclus', 'mondial-express' ); ?></h2><ul class="crosses"><?php foreach ( $exc as $l ) { echo '<li>' . esc_html( $l ) . '</li>'; } ?></ul></div><?php endif; ?>
		</div>
		<?php echo me_share_buttons(); // phpcs:ignore ?>
	</article>
	<aside class="aside-form" id="demande">
		<h2><?php esc_html_e( "S'inscrire", 'mondial-express' ); ?></h2>
		<?php echo me_form_notice(); // phpcs:ignore ?>
		<?php echo me_request_form( 'hajj', array( 'prefill' => array( 'forfait' => get_the_title() ) ) ); // phpcs:ignore ?>
		<?php echo me_payment_box(); // phpcs:ignore ?>
	</aside>
</div>
<?php
get_footer();
