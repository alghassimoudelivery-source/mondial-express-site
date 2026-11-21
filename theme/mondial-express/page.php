<?php
/**
 * Page standard. Les pages enfants de « Services » affichent en plus le formulaire de demande adapté.
 *
 * @package MondialExpress
 */

get_header();
while ( have_posts() ) :
	the_post();
	$parent   = wp_get_post_parent_id( get_the_ID() );
	$is_svc   = $parent && 'services' === get_post_field( 'post_name', $parent );
	$svc_map  = array( 'billetterie' => 'billet', 'assistance-visa' => 'visa', 'hotels-tourisme' => 'hotel', 'hajj-oumra' => 'hajj', 'evacuation-sanitaire' => 'evacuation', 'voyages-affaires' => 'affaires' );
	$svc      = me_services()[ get_post_field( 'post_name' ) ] ?? null;
	me_page_hero( get_the_title(), has_excerpt() ? get_the_excerpt() : '', $is_svc ? __( 'Nos services', 'mondial-express' ) : '' );
	?>
	<div class="container page-body <?php echo $is_svc ? 'page-body--aside' : ''; ?>">
		<article class="prose">
			<?php if ( $is_svc && $svc ) : ?>
				<span class="service__ic service__ic--lg service--<?php echo esc_attr( $svc[3] ); ?>"><?php echo me_icon( $svc[0] ); ?></span>
			<?php endif; ?>
			<?php the_content(); ?>
		</article>
		<?php if ( $is_svc ) : ?>
			<aside class="aside-form" id="demande">
				<h2><?php esc_html_e( 'Faire une demande', 'mondial-express' ); ?></h2>
				<p><?php esc_html_e( 'Réponse rapide par WhatsApp ou téléphone.', 'mondial-express' ); ?></p>
				<?php echo me_form_notice(); // phpcs:ignore ?>
				<?php echo me_request_form( $svc_map[ get_post_field( 'post_name' ) ] ?? 'contact' ); // phpcs:ignore ?>
			</aside>
		<?php endif; ?>
	</div>
	<?php if ( $is_svc ) : ?>
		<section class="section section--tight"><div class="container"><header class="section__head"><h2><?php esc_html_e( 'Nos autres services', 'mondial-express' ); ?></h2></header><?php get_template_part( 'template-parts/service-grid' ); ?></div></section>
	<?php endif; ?>
	<?php
endwhile;
get_footer();
