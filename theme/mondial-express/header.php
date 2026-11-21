<?php
/**
 * En-tête du site.
 *
 * @package MondialExpress
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#contenu"><?php esc_html_e( 'Aller au contenu', 'mondial-express' ); ?></a>

<div class="topbar">
	<div class="container topbar__inner">
		<p class="topbar__info">
			<a href="<?php echo esc_url( me_whatsapp_url() ); ?>" target="_blank" rel="noopener"><?php echo me_icon( 'whatsapp' ); ?> <bdi dir="ltr"><?php echo esc_html( me_opt( 'telephone' ) ); ?></bdi></a>
			<span class="hide-sm"><?php echo me_icon( 'pin' ); ?> <?php esc_html_e( 'Conakry, Guinée', 'mondial-express' ); ?></span>
			<span class="hide-sm"><?php echo me_icon( 'clock' ); ?> <?php echo esc_html( me_opt( 'horaires' ) ); ?></span>
		</p>
		<a class="topbar__app" href="<?php echo esc_url( home_url( '/application-mobile/' ) ); ?>"><?php esc_html_e( "Nouveau : téléchargez l'application Mondial Express", 'mondial-express' ); ?> <?php echo me_icon( 'arrow' ); ?></a>
		<?php echo me_socials( 'socials socials--top' ); // phpcs:ignore ?>
	</div>
</div>

<header class="site-header" id="haut">
	<div class="container site-header__inner">
		<?php echo me_logo(); // phpcs:ignore ?>
		<nav class="nav" id="menu-principal" aria-label="<?php esc_attr_e( 'Menu principal', 'mondial-express' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'principal',
					'container'      => false,
					'menu_class'     => 'nav__list',
					'fallback_cb'    => false,
					'depth'          => 2,
				)
			);
			?>
			<div class="nav__mobile-extra">
				<a class="btn btn--block" href="<?php echo esc_url( home_url( '/demande-de-devis/' ) ); ?>"><?php esc_html_e( 'Demander un devis', 'mondial-express' ); ?></a>
				<a class="btn btn--wa btn--block" href="<?php echo esc_url( me_whatsapp_url() ); ?>" target="_blank" rel="noopener"><?php echo me_icon( 'whatsapp' ); ?> <?php esc_html_e( 'Écrire sur WhatsApp', 'mondial-express' ); ?></a>
				<?php echo me_socials(); // phpcs:ignore ?>
			</div>
		</nav>
		<div class="site-header__actions">
			<?php echo me_lang_switcher(); // phpcs:ignore ?>
			<a class="icon-btn" href="<?php echo esc_url( home_url( '/mon-espace/' ) ); ?>" aria-label="<?php esc_attr_e( 'Mon espace client', 'mondial-express' ); ?>" title="<?php esc_attr_e( 'Mon espace', 'mondial-express' ); ?>"><?php echo me_icon( 'user' ); ?></a>
			<a class="btn hide-md" href="<?php echo esc_url( home_url( '/demande-de-devis/' ) ); ?>"><?php esc_html_e( 'Demander un devis', 'mondial-express' ); ?></a>
			<button class="burger" type="button" aria-controls="menu-principal" aria-expanded="false" aria-label="<?php esc_attr_e( 'Ouvrir le menu', 'mondial-express' ); ?>"><span></span></button>
		</div>
	</div>
</header>
<main id="contenu" class="site-main">
