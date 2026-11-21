<?php
/**
 * Page « Contact ».
 *
 * @package MondialExpress
 */

get_header();
the_post();
me_page_hero( get_the_title(), __( 'Une question, un devis, une urgence ? Nos conseillers vous répondent.', 'mondial-express' ) );
?>
<div class="container page-body page-body--aside">
	<div>
		<ul class="contact-cards">
			<li><span class="service__ic"><?php echo me_icon( 'whatsapp' ); ?></span><span><b>WhatsApp</b><a href="<?php echo esc_url( me_whatsapp_url() ); ?>" target="_blank" rel="noopener"><bdi dir="ltr"><?php echo esc_html( me_opt( 'telephone' ) ); ?></bdi></a></span></li>
			<li><span class="service__ic"><?php echo me_icon( 'phone' ); ?></span><span><b><?php esc_html_e( 'Téléphone', 'mondial-express' ); ?></b><a href="<?php echo esc_url( me_tel_url() ); ?>"><bdi dir="ltr"><?php echo esc_html( me_opt( 'telephone' ) ); ?></bdi></a></span></li>
			<li><span class="service__ic"><?php echo me_icon( 'mail' ); ?></span><span><b>E-mail</b><?php echo me_opt( 'email' ) ? '<a href="mailto:' . esc_attr( me_opt( 'email' ) ) . '">' . esc_html( me_opt( 'email' ) ) . '</a>' : esc_html__( 'À compléter', 'mondial-express' ); ?></span></li>
			<li><span class="service__ic"><?php echo me_icon( 'pin' ); ?></span><span><b><?php esc_html_e( 'Agence', 'mondial-express' ); ?></b><?php echo me_opt( 'adresse' ) ? nl2br( esc_html( me_opt( 'adresse' ) ) ) : esc_html__( 'Conakry, Guinée (adresse à compléter)', 'mondial-express' ); ?><?php echo me_opt( 'carte' ) ? ' <a href="' . esc_url( me_opt( 'carte' ) ) . '" target="_blank" rel="noopener">' . esc_html__( 'Itinéraire', 'mondial-express' ) . '</a>' : ''; ?></span></li>
			<li><span class="service__ic"><?php echo me_icon( 'clock' ); ?></span><span><b><?php esc_html_e( 'Horaires', 'mondial-express' ); ?></b><?php echo esc_html( me_opt( 'horaires' ) ); ?></span></li>
		</ul>
		<div class="prose"><?php the_content(); ?></div>
		<?php if ( me_payment_methods() ) : ?>
			<h2 class="h-small"><?php esc_html_e( 'Moyens de paiement acceptés', 'mondial-express' ); ?></h2>
			<?php echo me_payment_badges( true ); // phpcs:ignore ?>
			<?php if ( me_opt( 'pay_note' ) ) : ?><p class="pay-note"><?php echo esc_html( me_opt( 'pay_note' ) ); ?></p><?php endif; ?>
		<?php endif; ?>
		<h2 class="h-small"><?php esc_html_e( 'Suivez-nous', 'mondial-express' ); ?></h2>
		<?php echo me_socials( 'socials socials--lg' ); // phpcs:ignore ?>
	</div>
	<aside class="aside-form" id="demande"><h2><?php esc_html_e( 'Écrivez-nous', 'mondial-express' ); ?></h2><?php echo me_form_notice(); echo me_request_form( 'contact' ); // phpcs:ignore ?></aside>
</div>
<?php
get_footer();
