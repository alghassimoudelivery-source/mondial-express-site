<?php
/**
 * Page « Demande de devis » : un formulaire par type de demande.
 *
 * @package MondialExpress
 */

get_header();
the_post();
$types = array_diff_key( me_demande_types(), array( 'contact' => 1 ) );
$cur   = isset( $_GET['type'] ) && isset( $types[ sanitize_key( $_GET['type'] ) ] ) ? sanitize_key( $_GET['type'] ) : 'billet'; // phpcs:ignore WordPress.Security.NonceVerification
me_page_hero( get_the_title(), __( 'Choisissez votre besoin et laissez-nous vos coordonnées : un conseiller vous répond rapidement.', 'mondial-express' ) );
?>
<div class="container page-body">
	<div class="card-form">
		<?php echo me_form_notice(); // phpcs:ignore ?>
		<div class="pills" role="tablist">
			<?php foreach ( $types as $k => $l ) : ?>
				<a class="pill-link <?php echo $k === $cur ? 'is-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'type', $k, get_permalink() ) ); ?>#formulaire"><?php echo esc_html( $l ); ?></a>
			<?php endforeach; ?>
		</div>
		<div id="formulaire"><?php echo me_request_form( $cur ); // phpcs:ignore ?></div>
		<?php echo me_payment_box(); // phpcs:ignore ?>
	</div>
</div>
<?php
get_footer();
