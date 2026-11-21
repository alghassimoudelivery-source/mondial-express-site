<?php
/**
 * Pied de page du site.
 *
 * @package MondialExpress
 */
?>
</main>

<footer class="site-footer">
	<div class="container site-footer__grid">
		<div class="site-footer__brand">
			<?php echo me_logo( 'blanc' ); // phpcs:ignore ?>
			<p><?php esc_html_e( 'Votre partenaire de confiance pour découvrir le monde depuis Conakry. Chaque voyage est une histoire !', 'mondial-express' ); ?></p>
			<?php echo me_socials(); // phpcs:ignore ?>
		</div>
		<div>
			<h2 class="site-footer__title"><?php esc_html_e( 'Services', 'mondial-express' ); ?></h2>
			<ul class="site-footer__links">
				<?php foreach ( me_services() as $slug => $s ) : ?>
					<li><a href="<?php echo esc_url( home_url( '/services/' . $slug . '/' ) ); ?>"><?php echo esc_html( $s[1] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<div>
			<h2 class="site-footer__title"><?php esc_html_e( 'Agence', 'mondial-express' ); ?></h2>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'pied',
					'container'      => false,
					'menu_class'     => 'site-footer__links',
					'depth'          => 1,
					'fallback_cb'    => false,
				)
			);
			?>
		</div>
		<div>
			<h2 class="site-footer__title"><?php esc_html_e( 'Contact', 'mondial-express' ); ?></h2>
			<ul class="site-footer__contact">
				<li><?php echo me_icon( 'phone' ); ?> <a href="<?php echo esc_url( me_tel_url() ); ?>"><bdi dir="ltr"><?php echo esc_html( me_opt( 'telephone' ) ); ?></bdi></a></li>
				<li><?php echo me_icon( 'whatsapp' ); ?> <a href="<?php echo esc_url( me_whatsapp_url() ); ?>" target="_blank" rel="noopener">WhatsApp</a></li>
				<?php if ( me_opt( 'email' ) ) : ?><li><?php echo me_icon( 'mail' ); ?> <a href="mailto:<?php echo esc_attr( me_opt( 'email' ) ); ?>"><?php echo esc_html( me_opt( 'email' ) ); ?></a></li><?php endif; ?>
				<li><?php echo me_icon( 'pin' ); ?> <?php echo me_opt( 'adresse' ) ? nl2br( esc_html( me_opt( 'adresse' ) ) ) : esc_html__( 'Conakry, Guinée', 'mondial-express' ); ?></li>
				<li><?php echo me_icon( 'clock' ); ?> <?php echo esc_html( me_opt( 'horaires' ) ); ?></li>
			</ul>
			<div class="site-footer__app">
				<p><?php esc_html_e( "Téléchargez l'application", 'mondial-express' ); ?></p>
				<?php echo me_app_badges(); // phpcs:ignore ?>
			</div>
		</div>
	</div>
	<?php if ( me_payment_methods() ) : ?>
	<div class="container site-footer__pay">
		<p class="site-footer__title"><?php esc_html_e( 'Paiements acceptés', 'mondial-express' ); ?></p>
		<?php echo me_payment_badges( false, 'pay--dark' ); // phpcs:ignore ?>
	</div>
	<?php endif; ?>
	<div class="site-footer__bottom">
		<div class="container site-footer__bottom-inner">
			<p>© <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?> · <a href="<?php echo esc_url( home_url( '/mentions-legales/' ) ); ?>"><?php esc_html_e( 'Mentions légales', 'mondial-express' ); ?></a> · <a href="<?php echo esc_url( get_privacy_policy_url() ? get_privacy_policy_url() : home_url( '/confidentialite/' ) ); ?>"><?php esc_html_e( 'Confidentialité', 'mondial-express' ); ?></a></p>
			<p class="credit">Site réalisé par <a href="https://confordev.com" target="_blank" rel="noopener">ConforDev</a></p>
		</div>
	</div>
</footer>

<a class="wa-float" href="<?php echo esc_url( me_whatsapp_url() ); ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Discuter sur WhatsApp', 'mondial-express' ); ?>"><?php echo me_icon( 'whatsapp' ); ?></a>
<?php wp_footer(); ?>
</body>
</html>
