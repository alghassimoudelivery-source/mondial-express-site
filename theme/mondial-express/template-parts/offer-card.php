<?php
/**
 * Carte d'une offre / destination.
 *
 * @package MondialExpress
 */
$pid   = get_the_ID();
$prix  = get_post_meta( $pid, '_me_prix', true );
$label = get_post_meta( $pid, '_me_type_prix', true );
$code  = get_post_meta( $pid, '_me_code', true );
?>
<article class="dest">
	<a class="dest__link" href="<?php the_permalink(); ?>">
		<?php echo me_offer_visual( $pid ); // phpcs:ignore ?>
		<span class="dest__city"><?php the_title(); ?><?php echo $code ? ' <small>' . esc_html( $code ) . '</small>' : ''; ?></span>
	</a>
	<div class="dest__meta">
		<span><?php echo esc_html( $label ? $label : __( 'Aller-retour', 'mondial-express' ) ); ?> <?php esc_html_e( 'dès', 'mondial-express' ); ?></span>
		<b><?php echo esc_html( me_price( $prix ) ); ?></b> <?php echo me_price_note( $pid ); // phpcs:ignore ?>
	</div>
</article>
