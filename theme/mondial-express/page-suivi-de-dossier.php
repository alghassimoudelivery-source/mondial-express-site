<?php
/**
 * Page « Suivi de dossier ».
 *
 * @package MondialExpress
 */

get_header();
the_post();
$num  = isset( $_GET['numero'] ) ? sanitize_text_field( wp_unslash( $_GET['numero'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$tel  = isset( $_GET['tel'] ) ? sanitize_text_field( wp_unslash( $_GET['tel'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$found = ( $num && $tel ) ? me_find_dossier( $num, $tel ) : 0;
me_page_hero( get_the_title(), __( 'Entrez votre numéro de dossier et les 4 derniers chiffres de votre téléphone pour voir où en est votre demande.', 'mondial-express' ) );
?>
<div class="container page-body page-body--track">
	<form class="track" method="get" action="<?php the_permalink(); ?>">
		<div class="fld"><label for="numero"><?php esc_html_e( 'Numéro de dossier', 'mondial-express' ); ?></label><input id="numero" name="numero" required placeholder="ME-2611-2418" value="<?php echo esc_attr( $num ); ?>" autocapitalize="characters"></div>
		<div class="fld"><label for="tel"><?php esc_html_e( '4 derniers chiffres du téléphone', 'mondial-express' ); ?></label><input id="tel" name="tel" required inputmode="numeric" maxlength="4" pattern="\d{4}" placeholder="3970" value="<?php echo esc_attr( $tel ); ?>"></div>
		<button class="btn" type="submit"><?php echo me_icon( 'search' ); ?> <?php esc_html_e( 'Voir mon dossier', 'mondial-express' ); ?></button>
	</form>
	<?php if ( $num && $tel && ! $found ) : ?>
		<div class="notice notice--err" role="alert"><?php esc_html_e( 'Aucun dossier ne correspond. Vérifiez le numéro (il figure sur votre reçu) et les 4 derniers chiffres de votre téléphone.', 'mondial-express' ); ?></div>
	<?php elseif ( $found ) : ?>
		<?php echo me_dossier_timeline( $found ); // phpcs:ignore ?>
	<?php endif; ?>
	<div class="prose"><?php the_content(); ?></div>
	<p class="track__app"><?php esc_html_e( 'Recevez une notification à chaque étape avec l’application mobile.', 'mondial-express' ); ?> <a href="<?php echo esc_url( home_url( '/application-mobile/' ) ); ?>"><?php esc_html_e( "Télécharger l'application", 'mondial-express' ); ?></a></p>
</div>
<?php
get_footer();
