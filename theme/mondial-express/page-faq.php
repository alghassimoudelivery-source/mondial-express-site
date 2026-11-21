<?php
/**
 * Page « Questions fréquentes ».
 *
 * @package MondialExpress
 */

get_header();
the_post();
me_page_hero( get_the_title(), __( 'Les réponses aux questions que nos voyageurs nous posent le plus souvent.', 'mondial-express' ) );
?>
<div class="container page-body page-body--narrow">
	<div class="faq">
		<?php foreach ( me_faq_items() as $i => $q ) : ?>
			<details <?php echo 0 === $i ? 'open' : ''; ?>><summary><?php echo esc_html( $q[0] ); ?></summary><p><?php echo esc_html( $q[1] ); ?></p></details>
		<?php endforeach; ?>
	</div>
	<div class="prose"><?php the_content(); ?></div>
	<p class="faq__more"><?php esc_html_e( 'Vous ne trouvez pas votre réponse ?', 'mondial-express' ); ?> <a class="btn btn--wa btn--sm" href="<?php echo esc_url( me_whatsapp_url() ); ?>" target="_blank" rel="noopener"><?php echo me_icon( 'whatsapp' ); ?> <?php esc_html_e( 'Posez-nous la question', 'mondial-express' ); ?></a></p>
</div>
<?php
get_footer();
