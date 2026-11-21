<?php
/**
 * Page « À propos » : présentation complète de l'agence.
 *
 * @package MondialExpress
 */

get_header();
the_post();
$p = me_presentation();
me_page_hero( get_the_title(), esc_html__( 'Agence de voyages et de tourisme à Conakry depuis 2015.', 'mondial-express' ), __( 'Qui nous sommes', 'mondial-express' ) );
?>
<section class="section section--tight">
	<div class="container about">
		<div class="about__intro">
			<div>
				<p class="about__lead"><?php echo esc_html( $p['intro'] ); ?></p>
				<p><?php echo esc_html( $p['intro2'] ); ?></p>
			</div>
			<div class="about__stats">
				<?php foreach ( $p['chiffres'] as $c ) : ?>
					<div><b><?php echo esc_html( $c[0] ); ?></b><span><?php echo esc_html( $c[1] ); ?></span></div>
				<?php endforeach; ?>
			</div>
		</div>

		<h2 class="about__h"><?php esc_html_e( 'Nos valeurs', 'mondial-express' ); ?></h2>
		<div class="values">
			<?php foreach ( $p['valeurs'] as $v ) : ?>
				<article class="value value--<?php echo esc_attr( $v[3] ); ?>"><span class="value__ic"><?php echo me_icon( $v[0] ); // phpcs:ignore ?></span><h3><?php echo esc_html( $v[1] ); ?></h3><p><?php echo esc_html( $v[2] ); ?></p></article>
			<?php endforeach; ?>
		</div>

		<div class="mission">
			<div class="mission__txt">
				<p class="kicker"><?php esc_html_e( 'Notre mission', 'mondial-express' ); ?></p>
				<p class="mission__lead"><?php echo esc_html( $p['mission'] ); ?></p>
				<ol class="mission__steps">
					<?php foreach ( $p['etapes'] as $i => $e ) : ?>
						<li><b><?php echo esc_html( ( $i + 1 ) . '. ' . $e[0] ); ?></b><span><?php echo esc_html( $e[1] ); ?></span></li>
					<?php endforeach; ?>
				</ol>
			</div>
			<div class="mission__vision">
				<p class="kicker"><?php esc_html_e( 'Notre vision', 'mondial-express' ); ?></p>
				<p><?php echo esc_html( $p['vision'] ); ?></p>
				<p class="mission__slogan"><?php esc_html_e( 'Chaque voyage est une histoire !', 'mondial-express' ); ?></p>
			</div>
		</div>

		<h2 class="about__h" id="equipe"><?php esc_html_e( 'Notre équipe', 'mondial-express' ); ?></h2>
		<div class="team">
			<?php foreach ( $p['equipe'] as $m ) : $photo = me_team_photo( $m[0] ); ?>
				<article class="member">
					<?php if ( $photo ) : ?>
						<img src="<?php echo esc_url( $photo ); ?>" alt="<?php echo esc_attr( $m[1] ); ?>" loading="lazy" width="320" height="320">
					<?php else : ?>
						<span class="member__ph" aria-hidden="true"><?php echo esc_html( me_initials( $m[1] ) ); ?></span>
					<?php endif; ?>
					<h3><?php echo esc_html( $m[1] ); ?></h3><p><?php echo esc_html( $m[2] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>

		<h2 class="about__h" id="partenaires"><?php esc_html_e( 'Nos compagnies partenaires', 'mondial-express' ); ?></h2>
		<p class="about__sub"><?php esc_html_e( 'Nous émettons vos billets sur les principales compagnies qui desservent Conakry et le monde.', 'mondial-express' ); ?></p>
		<?php me_partners_strip(); ?>

		<h2 class="about__h"><?php esc_html_e( 'Notre histoire', 'mondial-express' ); ?></h2>
		<ol class="history">
			<?php foreach ( $p['histoire'] as $h ) : ?>
				<li><b><?php echo esc_html( $h[0] ); ?></b><p><?php echo esc_html( $h[1] ); ?></p></li>
			<?php endforeach; ?>
		</ol>

		<h2 class="about__h"><?php esc_html_e( 'Nos clients témoignent', 'mondial-express' ); ?></h2>
		<div class="quotes">
			<?php foreach ( $p['temoignages'] as $q ) : ?>
				<figure class="quote"><?php echo me_icon( 'quote' ); // phpcs:ignore ?><blockquote><?php echo esc_html( $q[2] ); ?></blockquote><figcaption><b><?php echo esc_html( $q[0] ); ?></b><span><?php echo esc_html( $q[1] ); ?></span></figcaption></figure>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<div class="container"><?php get_template_part( 'template-parts/cta-band' ); ?></div>
<div class="container"><?php get_template_part( 'template-parts/app-promo' ); ?></div>
<?php
get_footer();
