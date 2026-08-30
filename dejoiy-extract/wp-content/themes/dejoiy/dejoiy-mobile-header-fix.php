<?php
/**
 * Disable mobile header loading lines (XStore Ajaxify + nav overlay).
 *
 * @package dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Turn off XStore Ajaxify lazy-load shimmer on Elementor widgets (header icons, logo, search).
 */
add_filter( 'etheme_ajaxify_elementor_widget', '__return_false' );
add_filter( 'etheme_ajaxify_lazyload_widget', '__return_false' );
add_filter( 'etheme_ajaxify_script', '__return_false' );

/**
 * Enqueue mobile header fix assets (site-wide; CSS scoped to mobile breakpoints).
 */
function dejoiy_mobile_header_fix_enqueue() {
	if ( is_admin() ) {
		return;
	}

	$dir = get_stylesheet_directory();
	$uri = get_stylesheet_directory_uri();

	$css = $dir . '/dejoiy-mobile-header-fix.css';
	$js  = $dir . '/dejoiy-mobile-header-fix.js';

	if ( is_readable( $css ) ) {
		wp_enqueue_style(
			'dejoiy-mobile-header-fix',
			$uri . '/dejoiy-mobile-header-fix.css',
			array(),
			(string) filemtime( $css )
		);
	}

	if ( is_readable( $js ) ) {
		wp_enqueue_script(
			'dejoiy-mobile-header-fix',
			$uri . '/dejoiy-mobile-header-fix.js',
			array(),
			(string) filemtime( $js ),
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'dejoiy_mobile_header_fix_enqueue', 10050 );
