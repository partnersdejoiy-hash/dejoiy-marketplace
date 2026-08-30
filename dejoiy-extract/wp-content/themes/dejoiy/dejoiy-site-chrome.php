<?php
/**
 * Restore DEJOIY / Elementor site header & footer on DEJOIY Universe home.
 *
 * Skipped when the desktop marketplace header (dmh) is active — dmh replaces theme chrome.
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return bool
 */
function dejoiy_site_chrome_should_restore() {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return false;
	}
	if ( ! is_front_page() ) {
		return false;
	}
	if ( function_exists( 'dejoiy_universe_home_is_active' ) && ! dejoiy_universe_home_is_active() ) {
		return false;
	}
	if ( function_exists( 'dejoiy_desktop_marketplace_should_bootstrap_standalone' )
		&& dejoiy_desktop_marketplace_should_bootstrap_standalone() ) {
		return false;
	}
	return true;
}

/**
 * Output Elementor / DEJOIY header before main template content.
 */
function dejoiy_site_chrome_render_header() {
	if ( ! dejoiy_site_chrome_should_restore() ) {
		return;
	}

	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;

	if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'header' ) ) {
		return;
	}

	do_action( 'etheme_header_start' );
	do_action( 'etheme_header' );
	do_action( 'etheme_header_mobile' );
	do_action( 'etheme_header_end' );
}
add_action( 'etheme_header_before_template_content', 'dejoiy_site_chrome_render_header', 5 );
add_action( 'wp_body_open', 'dejoiy_site_chrome_render_header', 3 );
add_action( 'etheme_after_body_open', 'dejoiy_site_chrome_render_header', 3 );

/**
 * Output theme footer before wp_footer scripts.
 */
function dejoiy_site_chrome_render_footer() {
	if ( ! dejoiy_site_chrome_should_restore() ) {
		return;
	}

	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;

	echo '<div class="et-footers-wrapper dejoiy-site-chrome-footer">';

	if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'footer' ) ) {
		echo '</div>';
		return;
	}

	do_action( 'etheme_prefooter' );
	do_action( 'etheme_footer' );
	echo '</div>';
}
add_action( 'wp_footer', 'dejoiy_site_chrome_render_footer', 1 );

/**
 * Body class when site chrome is restored (hides duplicate Universe footer).
 *
 * @param array<int, string> $classes Classes.
 * @return array<int, string>
 */
function dejoiy_site_chrome_body_class( $classes ) {
	if ( dejoiy_site_chrome_should_restore() ) {
		$classes[] = 'dejoiy-site-chrome-on';
	}
	return $classes;
}
add_filter( 'body_class', 'dejoiy_site_chrome_body_class', 25 );
