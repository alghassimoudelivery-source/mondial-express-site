<?php
/**
 * Carte d'article du blog.
 *
 * @package MondialExpress
 */
?>
<article <?php post_class( 'post-card' ); ?>>
	<a class="post-card__img" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
		<?php
		if ( has_post_thumbnail() ) {
			the_post_thumbnail( 'me-card', array( 'loading' => 'lazy' ) );
		} else {
			$cols = array( 'orange', 'bleu', 'violet', 'vert' );
			echo '<span class="dest__img dest__img--' . esc_attr( $cols[ get_the_ID() % 4 ] ) . '">' . me_skyline( array( 'dubai', 'paris', 'ist', 'mecque' )[ get_the_ID() % 4 ] ) . '</span>'; // phpcs:ignore
		}
		?>
	</a>
	<div class="post-card__body">
		<p class="post-card__meta"><?php echo esc_html( get_the_date() ); ?><?php $cat = get_the_category(); echo $cat ? ' · ' . esc_html( $cat[0]->name ) : ''; ?></p>
		<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<p><?php echo esc_html( get_the_excerpt() ); ?></p>
	</div>
</article>
