<?php
/**
 * Grille des six services.
 *
 * @package MondialExpress
 */
?>
<div class="services">
	<?php foreach ( me_services() as $slug => $s ) : ?>
		<a class="service service--<?php echo esc_attr( $s[3] ); ?>" href="<?php echo esc_url( home_url( '/services/' . $slug . '/' ) ); ?>">
			<span class="service__ic"><?php echo me_icon( $s[0] ); ?></span>
			<strong><?php echo esc_html( $s[1] ); ?></strong>
			<small><?php echo esc_html( $s[2] ); ?></small>
		</a>
	<?php endforeach; ?>
</div>
