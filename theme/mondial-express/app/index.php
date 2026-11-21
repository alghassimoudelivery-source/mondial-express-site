<?php
/**
 * Coquille de l'application mobile. Variables : $boot, $base.
 *
 * @package MondialExpress
 */

defined( 'ABSPATH' ) || exit;

$icons = array();
foreach ( array( 'plane', 'pass', 'bed', 'kaaba', 'med', 'case', 'whatsapp', 'facebook', 'x', 'instagram', 'tiktok', 'youtube', 'search', 'arrow', 'user', 'phone', 'mail', 'pin', 'clock', 'check', 'share', 'upload', 'bell', 'folder', 'card' ) as $n ) {
	$icons[ $n ] = me_icon( $n );
}
$boot['icones'] = $icons;
?><!doctype html>
<html lang="fr" dir="ltr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Mondial Express – Application</title>
<meta name="description" content="Billets, visas, Hajj & Oumra et suivi de dossier avec Mondial Express Guinée.">
<meta name="robots" content="noindex">
<meta name="theme-color" content="#E8730C">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="Mondial Express">
<link rel="manifest" href="<?php echo esc_url( home_url( '/app/manifest.webmanifest' ) ); ?>">
<link rel="icon" href="<?php echo esc_url( ME_URI . '/assets/img/favicon.svg' ); ?>" type="image/svg+xml">
<link rel="apple-touch-icon" href="<?php echo esc_url( ME_URI . '/assets/img/apple-touch-icon.png' ); ?>">
<link rel="preload" href="<?php echo esc_url( ME_URI . '/assets/fonts/montserrat-var.woff2' ); ?>" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?php echo esc_url( $base . 'app.css?v=' . ME_APP_VERSION ); ?>">
<style>@font-face{font-family:"Montserrat";src:url(<?php echo esc_url( ME_URI . '/assets/fonts/montserrat-var.woff2' ); ?>) format("woff2");font-weight:100 900;font-display:swap}@font-face{font-family:"Inter";src:url(<?php echo esc_url( ME_URI . '/assets/fonts/inter-var.woff2' ); ?>) format("woff2");font-weight:100 900;font-display:swap}</style>
</head>
<body>
<div id="splash" aria-hidden="true">
	<img src="<?php echo esc_url( ME_URI . '/assets/img/logo-mondial-express-blanc.svg' ); ?>" alt="">
</div>
<div id="app" class="app">
	<header class="topbar" id="topbar"></header>
	<main id="view" class="view" tabindex="-1"></main>
	<nav class="tabbar" id="tabbar" aria-label="Navigation principale"></nav>
</div>
<div id="toast" class="toast" role="status" aria-live="polite"></div>
<script>window.ME=<?php echo wp_json_encode( $boot, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?>;window.ME.logo=<?php echo wp_json_encode( ME_URI . '/assets/img/logo-mondial-express-sans-slogan.svg' ); ?>;window.ME.symbole=<?php echo wp_json_encode( ME_URI . '/assets/img/symbole-mondial-express.svg' ); ?>;</script>
<script src="<?php echo esc_url( $base . 'i18n.js?v=' . ME_APP_VERSION ); ?>" defer></script>
<script src="<?php echo esc_url( $base . 'app.js?v=' . ME_APP_VERSION ); ?>" defer></script>
</body>
</html>
