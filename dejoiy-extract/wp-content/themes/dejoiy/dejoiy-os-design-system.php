<?php
/**
 * DEJOIY OS Design System — tokens + global frontend shell.
 *
 * Headless-ready class naming: dejoiy-os-* / dejoiy-btn, dejoiy-card, etc.
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue design system on storefront (not wp-admin).
 */
function dejoiy_os_design_system_assets() {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return;
	}
	if ( function_exists( 'dejoiy_library_is_dashboard_request' ) && dejoiy_library_is_dashboard_request() ) {
		return;
	}

	$uri = get_stylesheet_directory_uri();
	$dir = get_stylesheet_directory();
	$css = $dir . '/dejoiy-os-design-system.css';
	if ( file_exists( $css ) ) {
		wp_enqueue_style(
			'dejoiy-os-design-system',
			$uri . '/dejoiy-os-design-system.css',
			array(),
			(string) filemtime( $css )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'dejoiy_os_design_system_assets', 1003 );

/**
 * Body class for OS layer (scopes overrides away from breaking admin).
 */
function dejoiy_os_design_body_class( $classes ) {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return $classes;
	}
	$classes[] = 'dejoiy-os-shell';
	return $classes;
}
add_filter( 'body_class', 'dejoiy_os_design_body_class' );

require_once get_stylesheet_directory() . '/dejoiy-os-components.php';
