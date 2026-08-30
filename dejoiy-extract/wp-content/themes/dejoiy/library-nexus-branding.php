<?php
/**
 * DEJOIY Nexus — white-label (hide third-party product names on Nexus screens).
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether Nexus branding filters should run.
 *
 * @return bool
 */
function dejoiy_library_is_nexus_branding_scope() {
	if ( function_exists( 'dejoiy_library_is_nexus_chrome_screen' ) && dejoiy_library_is_nexus_chrome_screen() ) {
		return true;
	}
	if ( function_exists( 'dejoiy_library_should_load_nexus_app' ) && dejoiy_library_should_load_nexus_app() ) {
		return true;
	}
	$uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	return '' !== $uri && (bool) preg_match( '#/nexus/|dejoiy-library|dejoiy_library=1#i', $uri );
}

/**
 * Badge count for header (owned + checkout queue).
 *
 * @param array|null $data Panel data from dejoiy_library_get_nexus_cart_panel_data().
 * @return int
 */
function dejoiy_library_nexus_badge_count( $data = null ) {
	if ( null === $data && function_exists( 'dejoiy_library_get_nexus_cart_panel_data' ) ) {
		if ( function_exists( 'dejoiy_library_ensure_cart_loaded' ) ) {
			dejoiy_library_ensure_cart_loaded();
		}
		$data = dejoiy_library_get_nexus_cart_panel_data();
	}
	if ( ! is_array( $data ) ) {
		return 0;
	}
	if ( isset( $data['total_badge'] ) ) {
		return max( 0, (int) $data['total_badge'] );
	}
	return max( 0, (int) ( $data['count'] ?? 0 ) + (int) ( $data['pending_count'] ?? 0 ) );
}

/**
 * Remove generator / platform meta from Nexus HTML output.
 *
 * @param string $html Page HTML.
 * @return string
 */
function dejoiy_library_nexus_scrub_platform_html( $html ) {
	if ( '' === $html || ! is_string( $html ) ) {
		return $html;
	}
	$html = preg_replace( '/<meta[^>]*\bname=["\']generator["\'][^>]*>\s*/i', '', $html );
	$html = preg_replace( '/<meta[^>]*\bonesignal-plugin[^>]*content=["\'][^"\']*wordpress[^"\']*["\'][^>]*>\s*/i', '', $html );
	$html = preg_replace( '/<!--\s*Manifest added by SuperPWA[^>]*-->\s*/i', '', $html );
	$html = preg_replace( '/<!--\s*Works with XStore[^>]*-->\s*/i', '', $html );
	return $html;
}

/**
 * @return void
 */
function dejoiy_library_nexus_branding_buffer_start() {
	if ( ! dejoiy_library_is_nexus_branding_scope() ) {
		return;
	}
	if ( ! defined( 'DEJOIY_NEXUS_BRANDING_OB' ) ) {
		define( 'DEJOIY_NEXUS_BRANDING_OB', true );
		ob_start( 'dejoiy_library_nexus_scrub_platform_html' );
	}
}

/**
 * @return void
 */
function dejoiy_library_nexus_branding_buffer_end() {
	if ( ! dejoiy_library_is_nexus_branding_scope() ) {
		return;
	}
	if ( defined( 'DEJOIY_NEXUS_BRANDING_OB' ) && DEJOIY_NEXUS_BRANDING_OB ) {
		ob_end_flush();
	}
}

/**
 * Strip platform names from WooCommerce / plugin gettext on Nexus only.
 *
 * @param string $translated Translated.
 * @param string $text       Original.
 * @param string $domain     Text domain.
 * @return string
 */
function dejoiy_library_nexus_gettext( $translated, $text, $domain ) {
	if ( ! dejoiy_library_is_nexus_branding_scope() ) {
		return $translated;
	}
	$domains = array( 'woocommerce', 'wc-frontend-manager', 'wcfm', 'hostinger-reach', 'default' );
	if ( ! in_array( $domain, $domains, true ) ) {
		return $translated;
	}
	$map = array(
		'WooCommerce' => 'DEJOIY Nexus',
		'woocommerce' => 'DEJOIY Nexus',
		'WordPress'   => 'DEJOIY',
		'wordpress'   => 'DEJOIY',
		'WCFM'        => 'DEJOIY',
		'Hostinger'   => 'DEJOIY',
	);
	foreach ( array( $translated, $text ) as $str ) {
		foreach ( $map as $from => $to ) {
			if ( false !== stripos( $str, $from ) ) {
				return str_ireplace( $from, $to, $translated );
			}
		}
	}
	return $translated;
}

/**
 * Dequeue marketplace plugin chrome on Nexus.
 *
 * @return void
 */
function dejoiy_library_nexus_dequeue_marketplace_assets() {
	if ( ! dejoiy_library_is_nexus_branding_scope() ) {
		return;
	}
	$styles = array(
		'wcfm_login_css',
		'wcfm_core_css',
		'wcfm_fa_icon_css',
		'etheme-wcfmmp-style',
		'hostinger-reach-subscription-block-css',
	);
	foreach ( $styles as $handle ) {
		wp_dequeue_style( $handle );
		wp_deregister_style( $handle );
	}
}

/**
 * Register Nexus branding.
 *
 * @return void
 */
function dejoiy_library_nexus_branding_init() {
	if ( is_admin() ) {
		return;
	}
	add_action( 'template_redirect', 'dejoiy_library_nexus_branding_buffer_start', 0 );
	add_action( 'shutdown', 'dejoiy_library_nexus_branding_buffer_end', 0 );
	add_action( 'wp_enqueue_scripts', 'dejoiy_library_nexus_dequeue_marketplace_assets', 9999 );
	add_filter( 'gettext', 'dejoiy_library_nexus_gettext', 20, 3 );
	add_filter( 'the_generator', '__return_empty_string' );
	add_filter( 'woocommerce_generator_tag', '__return_empty_string' );
	remove_action( 'wp_head', 'wp_generator' );
}

dejoiy_library_nexus_branding_init();
