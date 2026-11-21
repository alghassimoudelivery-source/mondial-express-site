<?php $me_sid = wp_unique_id( 's-' ); ?>
<form role="search" method="get" class="searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $me_sid ); ?>"><?php esc_html_e( 'Rechercher', 'mondial-express' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $me_sid ); ?>" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Rechercher un article, une destination…', 'mondial-express' ); ?>">
	<button class="btn btn--sm" type="submit"><?php echo me_icon( 'search' ); ?><span class="screen-reader-text"><?php esc_html_e( 'Rechercher', 'mondial-express' ); ?></span></button>
</form>
