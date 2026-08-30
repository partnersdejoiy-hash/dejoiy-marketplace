<?php
/**
 * DEJOIY Universal Shop — Nexus / Studio product cards use their section look on the main shop.
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param int $product_id Product ID.
 * @return string nexus|studio|''
 */
function dejoiy_universal_shop_product_channel( $product_id ) {
	$product_id = (int) $product_id;
	if ( $product_id < 1 ) {
		return '';
	}
	if ( function_exists( 'dejoiy_library_is_nexus_product' ) && dejoiy_library_is_nexus_product( $product_id ) ) {
		return 'nexus';
	}
	if ( function_exists( 'dejoiy_studio_is_customizable_product' ) && dejoiy_studio_is_customizable_product( $product_id ) ) {
		return 'studio';
	}
	if ( get_post_meta( $product_id, '_dejoiy_studio_mock', true ) ) {
		return 'studio';
	}
	return '';
}

/**
 * @param array      $classes    Post classes.
 * @param string|array $class    Extra class.
 * @param int        $product_id Product ID.
 * @return array
 */
function dejoiy_universal_shop_post_class( $classes, $class = '', $product = null ) {
	if ( ! function_exists( 'dejoiy_universal_is_shop_screen' ) || ! dejoiy_universal_is_shop_screen() ) {
		return $classes;
	}
	$product_id = 0;
	if ( is_numeric( $product ) ) {
		$product_id = (int) $product;
	} elseif ( is_object( $product ) && isset( $product->ID ) ) {
		$product_id = (int) $product->ID;
	} elseif ( $class instanceof WC_Product ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		$product_id = (int) $class->get_id();
	}
	if ( $product_id < 1 && function_exists( 'wc_get_product' ) && $product instanceof WC_Product ) {
		$product_id = (int) $product->get_id();
	}
	$channel = dejoiy_universal_shop_product_channel( $product_id );
	if ( '' === $channel ) {
		return $classes;
	}
	$classes[] = 'dejoiy-shop-product-channel';
	$classes[] = 'dejoiy-shop-product-' . $channel;
	return $classes;
}

/**
 * Enqueue shop card backgrounds on the main store.
 */
function dejoiy_universal_shop_assets() {
	if ( ! function_exists( 'dejoiy_universal_is_shop_screen' ) || ! dejoiy_universal_is_shop_screen() ) {
		return;
	}
	$path = get_stylesheet_directory() . '/dejoiy-universal-shop.css';
	if ( ! is_readable( $path ) ) {
		return;
	}
	wp_enqueue_style(
		'dejoiy-universal-shop',
		get_stylesheet_directory_uri() . '/dejoiy-universal-shop.css',
		array(),
		(string) filemtime( $path )
	);
}

/**
 * @param array $classes Body classes.
 * @return array
 */
function dejoiy_universal_shop_body_class( $classes ) {
	if ( function_exists( 'dejoiy_universal_is_shop_screen' ) && dejoiy_universal_is_shop_screen() ) {
		$classes[] = 'dejoiy-universal-shop';
	}
	return $classes;
}

/**
 * Register universal shop styling (no catalog exclusion changes).
 */
function dejoiy_universal_shop_init() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}
	add_filter( 'woocommerce_post_class', 'dejoiy_universal_shop_post_class', 20, 2 );
	add_filter( 'body_class', 'dejoiy_universal_shop_body_class' );
	add_action( 'wp_enqueue_scripts', 'dejoiy_universal_shop_assets', 1012 );
}
