<?php
/**
 * Carte d'un forfait Hajj / Oumra.
 *
 * @package MondialExpress
 */
$pid = get_the_ID();
$typ = get_post_meta( $pid, '_me_type', true );
?>
<article class="forfait">
	<p class="forfait__type"><?php echo 'hajj' === $typ ? 'Hajj' : 'Oumra'; ?> · <?php echo esc_html( get_post_meta( $pid, '_me_dates', true ) ); ?></p>
	<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
	<p class="forfait__price"><b><?php echo esc_html( me_price( get_post_meta( $pid, '_me_prix', true ) ) ); ?></b> <?php echo me_price_note( $pid ); // phpcs:ignore ?></p>
	<ul class="checks checks--col">
		<?php foreach ( array_slice( array_filter( array_map( 'trim', explode( "\n", (string) get_post_meta( $pid, '_me_inclus', true ) ) ) ), 0, 4 ) as $li ) : ?>
			<li><?php echo esc_html( $li ); ?></li>
		<?php endforeach; ?>
	</ul>
	<a class="btn btn--outline" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Voir le forfait', 'mondial-express' ); ?></a>
</article>
