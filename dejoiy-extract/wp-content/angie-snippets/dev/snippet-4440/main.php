<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

define( 'HERO_BANNER_ASSETS_VERSION_5d75b94f', '1.1.0' );

function register_hero_banner_widget_5d75b94f( $widgets_manager ) {
	require_once __DIR__ . '/widget.php';
	$widgets_manager->register( new \AngieSnippets\Hero_Banner_5d75b94f() );
}
add_action( 'elementor/widgets/register', 'register_hero_banner_widget_5d75b94f' );

function enqueue_hero_banner_assets_5d75b94f() {
	wp_register_style(
		'hero-banner-style-5d75b94f',
		angie_cs_get_snippet_asset_url( __FILE__, 'style.css' ),
		[],
		HERO_BANNER_ASSETS_VERSION_5d75b94f
	);
}
add_action( 'elementor/frontend/after_register_styles', 'enqueue_hero_banner_assets_5d75b94f' );
