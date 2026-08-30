<?php
/**
 * DEJOIY OS white-label — customer-facing copy only.
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return bool
 */
function dejoiy_os_branding_should_run() {
	if ( function_exists( 'dejoiy_library_is_dashboard_request' ) && dejoiy_library_is_dashboard_request() ) {
		return false;
	}
	return true;
}

/**
 * Replace vendor names in translated strings (never touches file paths or hooks).
 *
 * @param string $translated Translated.
 * @return string
 */
function dejoiy_os_branding_scrub_vendors( $translated ) {
	if ( ! is_string( $translated ) || '' === $translated ) {
		return $translated;
	}

	$map = array(
		'XStore'      => 'DEJOIY',
		'xstore'      => 'DEJOIY',
		'XSTORE'      => 'DEJOIY',
		'8theme'      => 'DEJOIY',
		'8Theme'      => 'DEJOIY',
		'EightTheme'  => 'DEJOIY',
		'Eight Theme' => 'DEJOIY',
	);

	return str_replace( array_keys( $map ), array_values( $map ), $translated );
}

/**
 * @param string $translated Translated.
 * @param string $text       Original.
 * @param string $domain     Text domain.
 * @return string
 */
function dejoiy_os_branding_gettext( $translated, $text, $domain ) {
	if ( ! dejoiy_os_branding_should_run() ) {
		return $translated;
	}

	$map = array(
		'My account'       => 'DEJOIY Space',
		'My Account'       => 'DEJOIY Space',
		'Account'          => 'DEJOIY Space',
		'Orders'           => 'DEJOIY Orders',
		'Wishlist'         => 'DEJOIY Favorites',
		'My wishlist'      => 'DEJOIY Favorites',
		'Vendor Dashboard' => 'DEJOIY Seller Hub',
		'Vendor dashboard' => 'DEJOIY Seller Hub',
		'WooCommerce'      => 'DEJOIY',
		'WordPress'        => 'DEJOIY',
		'Marketplace'      => 'DEJOIY Marketplace',
	);

	if ( isset( $map[ $text ] ) ) {
		return $map[ $text ];
	}

	$domains = array( 'xstore', 'xstore-core', 'et-core-plugin', 'woocommerce', 'default' );
	if ( in_array( $domain, $domains, true ) || '' === $domain ) {
		return dejoiy_os_branding_scrub_vendors( $translated );
	}

	$lower = strtolower( $text );
	$sub   = array(
		'woocommerce' => 'DEJOIY',
		'wordpress'   => 'DEJOIY',
		'wcfm'        => 'DEJOIY Seller Hub',
		'xstore'      => 'DEJOIY',
		'8theme'      => 'DEJOIY',
		'dokan'       => 'DEJOIY',
	);

	foreach ( $sub as $needle => $replace ) {
		if ( false !== strpos( $lower, $needle ) ) {
			return dejoiy_os_branding_scrub_vendors( str_ireplace( $needle, $replace, $translated ) );
		}
	}

	return $translated;
}
add_filter( 'gettext', 'dejoiy_os_branding_gettext', 20, 3 );
add_filter( 'gettext_woocommerce', 'dejoiy_os_branding_gettext', 20, 3 );

/**
 * Body class for OS styling scope.
 *
 * @param array<int, string> $classes Classes.
 * @return array<int, string>
 */
function dejoiy_os_body_class( $classes ) {
	if ( dejoiy_os_branding_should_run() ) {
		$classes[] = 'dejoiy-os-active';
	}
	return $classes;
}
add_filter( 'body_class', 'dejoiy_os_body_class' );

/**
 * Remove generator meta on frontend.
 */
function dejoiy_os_scrub_generator() {
	if ( ! dejoiy_os_branding_should_run() ) {
		return;
	}
	remove_action( 'wp_head', 'wp_generator' );
	add_filter( 'the_generator', '__return_empty_string' );
}
add_action( 'init', 'dejoiy_os_scrub_generator', 1 );
