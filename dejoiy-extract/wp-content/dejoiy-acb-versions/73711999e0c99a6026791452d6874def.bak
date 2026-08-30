<?php
/**
 * Marketplace shop/home — show all products; open Nexus / Studio on dedicated pages.
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return bool Main shop catalog (not Nexus app).
 */
function dejoiy_shop_is_unified_marketplace_catalog() {
	if ( ! did_action( 'wp' ) ) {
		return false;
	}
	if ( function_exists( 'dejoiy_library_has_nexus_flow_request' ) && dejoiy_library_has_nexus_flow_request() ) {
		return false;
	}
	if ( function_exists( 'dejoiy_studio_request_has_flow_flag' ) && dejoiy_studio_request_has_flow_flag() ) {
		return false;
	}
	if ( function_exists( 'is_shop' ) && is_shop() ) {
		return true;
	}
	if ( is_front_page() || is_home() ) {
		return true;
	}
	if ( function_exists( 'is_product_category' ) && is_product_category() ) {
		return true;
	}
	return false;
}

/**
 * @param int $term_id Category term ID.
 * @return bool
 */
function dejoiy_shop_is_studio_category_term( $term_id ) {
	$term_id = (int) $term_id;
	if ( $term_id < 1 ) {
		return false;
	}
	if ( ! defined( 'DEJOIY_STUDIO_CAT_IDS' ) || ! is_array( DEJOIY_STUDIO_CAT_IDS ) ) {
		return false;
	}
	return in_array( $term_id, array_map( 'intval', DEJOIY_STUDIO_CAT_IDS ), true );
}

/**
 * On main shop, include products marked hidden in catalog (Nexus books, etc.).
 *
 * @param array    $tax_query Tax query.
 * @param WC_Query $wc_query  Query.
 * @return array
 */
function dejoiy_shop_wc_product_query_tax_query( $tax_query, $wc_query ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	if ( ! dejoiy_shop_is_unified_marketplace_catalog() ) {
		return $tax_query;
	}
	if ( ! is_array( $tax_query ) ) {
		return $tax_query;
	}
	$out = array();
	foreach ( $tax_query as $key => $clause ) {
		if ( 'relation' === $key ) {
			$out[ $key ] = $clause;
			continue;
		}
		if ( ! is_array( $clause ) ) {
			continue;
		}
		if ( isset( $clause['taxonomy'] ) && 'product_visibility' === $clause['taxonomy'] ) {
			continue;
		}
		$out[] = $clause;
	}
	return $out;
}

/**
 * Studio product from marketplace shop → Custom Studio product page.
 */
function dejoiy_shop_redirect_studio_product_permalink() {
	if ( ! did_action( 'wp' ) || ! is_singular( 'product' ) ) {
		return;
	}
	if ( function_exists( 'dejoiy_studio_request_has_flow_flag' ) && dejoiy_studio_request_has_flow_flag() ) {
		return;
	}
	if ( function_exists( 'dejoiy_library_has_nexus_flow_request' ) && dejoiy_library_has_nexus_flow_request() ) {
		return;
	}

	$product_id = get_queried_object_id();
	if ( ! $product_id || ! function_exists( 'dejoiy_studio_is_customizable_product' ) ) {
		return;
	}
	if ( ! dejoiy_studio_is_customizable_product( $product_id ) ) {
		return;
	}

	$url = function_exists( 'dejoiy_studio_product_url' )
		? dejoiy_studio_product_url( $product_id )
		: add_query_arg( 'dejoiy_studio', '1', get_permalink( $product_id ) );

	wp_safe_redirect( $url );
	exit;
}

/**
 * ?filter_cat= with studio category → Custom Studio landing.
 */
function dejoiy_shop_redirect_studio_category_filter() {
	if ( is_admin() || empty( $_GET['filter_cat'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	if ( function_exists( 'dejoiy_library_is_nexus_catalog_context' ) && dejoiy_library_is_nexus_catalog_context() ) {
		return;
	}

	$raw   = sanitize_text_field( wp_unslash( (string) $_GET['filter_cat'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$slugs = array_filter( array_map( 'trim', explode( ',', $raw ) ) );
	$studio_url = home_url( '/dejoiy-custom-studio/' );

	foreach ( $slugs as $slug ) {
		$term = get_term_by( 'slug', $slug, 'product_cat' );
		if ( ! $term || is_wp_error( $term ) ) {
			continue;
		}
		if ( ! dejoiy_shop_is_studio_category_term( (int) $term->term_id ) ) {
			continue;
		}
		wp_safe_redirect( add_query_arg( 'dejoiy_studio', '1', $studio_url ) );
		exit;
	}
}

/**
 * Studio category archive on marketplace → Custom Studio.
 */
function dejoiy_shop_redirect_studio_category_archives() {
	if ( ! did_action( 'wp' ) || ! function_exists( 'is_product_category' ) || ! is_product_category() ) {
		return;
	}
	if ( function_exists( 'dejoiy_studio_request_has_flow_flag' ) && dejoiy_studio_request_has_flow_flag() ) {
		return;
	}

	$term = get_queried_object();
	if ( ! $term || ! isset( $term->term_id ) || ! dejoiy_shop_is_studio_category_term( (int) $term->term_id ) ) {
		return;
	}

	wp_safe_redirect( add_query_arg( 'dejoiy_studio', '1', home_url( '/dejoiy-custom-studio/' ) ) );
	exit;
}

/**
 * Product loop links from shop → dedicated channel URLs.
 *
 * @param string     $permalink Permalink.
 * @param WC_Product $product   Product.
 * @return string
 */
function dejoiy_shop_product_permalink( $permalink, $product ) {
	if ( ! dejoiy_shop_is_unified_marketplace_catalog() || ! $product ) {
		return $permalink;
	}
	$product_id = $product->get_id();
	if ( function_exists( 'dejoiy_library_is_nexus_product' ) && dejoiy_library_is_nexus_product( $product_id ) ) {
		return function_exists( 'dejoiy_library_reader_url' )
			? dejoiy_library_reader_url( $product_id )
			: $permalink;
	}
	if ( function_exists( 'dejoiy_studio_is_customizable_product' ) && dejoiy_studio_is_customizable_product( $product_id ) ) {
		return function_exists( 'dejoiy_studio_product_url' )
			? dejoiy_studio_product_url( $product_id )
			: $permalink;
	}
	return $permalink;
}

/**
 * Register shop routing (storefront only).
 */
function dejoiy_shop_routing_init() {
	if ( ! class_exists( 'WooCommerce' ) || is_admin() ) {
		return;
	}
	add_filter( 'woocommerce_product_query_tax_query', 'dejoiy_shop_wc_product_query_tax_query', 15, 2 );
	add_filter( 'woocommerce_product_get_permalink', 'dejoiy_shop_product_permalink', 20, 2 );
	add_action( 'template_redirect', 'dejoiy_shop_redirect_studio_product_permalink', 8 );
	add_action( 'template_redirect', 'dejoiy_shop_redirect_studio_category_filter', 6 );
	add_action( 'template_redirect', 'dejoiy_shop_redirect_studio_category_archives', 8 );
}
