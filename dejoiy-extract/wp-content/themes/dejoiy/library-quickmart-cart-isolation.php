<?php
/**
 * QuickMart cart — isolated from marketplace (Flipkart / Minutes pattern).
 *
 * Hooks register on `wp` and are skipped on the main DEJOIY /cart/ page so the
 * marketplace cart is never altered or broken by QuickMart logic.
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'DEJOIY_QUICKMART_FLOW' ) ) {
	define( 'DEJOIY_QUICKMART_FLOW', 'dejoiy_quickmart' );
}

/**
 * Main DEJOIY WooCommerce cart page (not QuickMart).
 *
 * @return bool
 */
function dejoiy_quickmart_is_marketplace_cart_request() {
	if ( dejoiy_quickmart_has_flow_request() ) {
		return false;
	}
	$uri = strtolower( (string) wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) );
	if ( false !== strpos( $uri, 'dejoiy-quick-mart' ) ) {
		return false;
	}
	if ( false !== strpos( $uri, '/cart' ) || false !== strpos( $uri, 'page_id=' ) && false !== strpos( $uri, 'cart' ) ) {
		if ( function_exists( 'is_cart' ) && did_action( 'wp' ) ) {
			return is_cart();
		}
		return (bool) preg_match( '#/cart(/|\?|$)#', $uri );
	}
	if ( function_exists( 'is_cart' ) && did_action( 'wp' ) ) {
		return is_cart();
	}
	return false;
}

/**
 * @return bool
 */
function dejoiy_quickmart_should_register_hooks() {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return false;
	}
	if ( wp_doing_ajax() ) {
		return true;
	}
	if ( dejoiy_quickmart_is_marketplace_cart_request() ) {
		return false;
	}
	return true;
}

/**
 * True when QuickMart cart splitting / filtering should run.
 *
 * @return bool
 */
function dejoiy_quickmart_split_cart_active() {
	if ( dejoiy_quickmart_has_flow_request() ) {
		return true;
	}
	if ( function_exists( 'dejoiy_quickmart_is_cart_view' ) && dejoiy_quickmart_is_cart_view() ) {
		return true;
	}
	if ( function_exists( 'dejoiy_quickmart_is_checkout_view' ) && dejoiy_quickmart_is_checkout_view() ) {
		return true;
	}
	if ( function_exists( 'dejoiy_quickmart_is_quickmart_page' ) && dejoiy_quickmart_is_quickmart_page() ) {
		if ( function_exists( 'dejoiy_quickmart_is_add_request' ) && dejoiy_quickmart_is_add_request() ) {
			return true;
		}
	}
	return false;
}

/**
 * @return bool
 */
function dejoiy_quickmart_is_product( $product_id ) {
	$product_id = (int) $product_id;
	if ( $product_id < 1 ) {
		return false;
	}
	if ( function_exists( 'dejoiy_get_product_ecosystem' ) && 'quickmart' === dejoiy_get_product_ecosystem( $product_id ) ) {
		return true;
	}
	$terms = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'slugs' ) );
	return ! is_wp_error( $terms ) && in_array( 'quick-products', $terms, true );
}

/**
 * @param array $cart_item Cart line.
 * @return bool
 */
function dejoiy_quickmart_cart_line_is_quickmart( $cart_item ) {
	if ( ! empty( $cart_item['dejoiy_quickmart_item'] ) ) {
		return true;
	}
	$product_id = isset( $cart_item['product_id'] ) ? (int) $cart_item['product_id'] : 0;
	return $product_id && dejoiy_quickmart_is_product( $product_id );
}

/**
 * @return bool
 */
function dejoiy_quickmart_has_flow_request() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	return isset( $_GET[ DEJOIY_QUICKMART_FLOW ] ) && '1' === (string) wp_unslash( $_GET[ DEJOIY_QUICKMART_FLOW ] );
}

/**
 * @return bool QuickMart cart, checkout, or app pages.
 */
function dejoiy_quickmart_is_cart_context() {
	if ( function_exists( 'dejoiy_quickmart_is_cart_view' ) && dejoiy_quickmart_is_cart_view() ) {
		return true;
	}
	if ( function_exists( 'dejoiy_quickmart_is_checkout_view' ) && dejoiy_quickmart_is_checkout_view() ) {
		return true;
	}
	if ( dejoiy_quickmart_has_flow_request() ) {
		return true;
	}
	return false;
}

/**
 * @return bool Adding from QuickMart UI.
 */
function dejoiy_quickmart_is_add_request() {
	if ( function_exists( 'dejoiy_quickmart_is_quickmart_page' ) && dejoiy_quickmart_is_quickmart_page() ) {
		return true;
	}
	if ( dejoiy_quickmart_has_flow_request() ) {
		return true;
	}
	$referer = wp_get_referer();
	return $referer && false !== strpos( $referer, 'dejoiy-quick-mart' );
}

/**
 * @return string
 */
function dejoiy_quickmart_get_cart_url() {
	if ( function_exists( 'dejoiy_quickmart_cart_url' ) ) {
		return dejoiy_quickmart_cart_url();
	}
	$url = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );
	return add_query_arg( DEJOIY_QUICKMART_FLOW, '1', $url );
}

/**
 * @return string
 */
function dejoiy_quickmart_get_checkout_url() {
	if ( function_exists( 'dejoiy_quickmart_checkout_url' ) ) {
		return dejoiy_quickmart_checkout_url();
	}
	$url = function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/checkout/' );
	return add_query_arg( DEJOIY_QUICKMART_FLOW, '1', $url );
}

/**
 * @param bool   $visible   Visible.
 * @param array  $cart_item Item.
 * @param string $cart_key  Key.
 * @return bool
 */
function dejoiy_quickmart_cart_item_visible( $visible, $cart_item, $cart_key ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	if ( ! $visible || ! dejoiy_quickmart_split_cart_active() ) {
		return $visible;
	}
	$is_qm = dejoiy_quickmart_cart_line_is_quickmart( $cart_item );
	if ( dejoiy_quickmart_is_cart_context() ) {
		return $is_qm;
	}
	return ! $is_qm;
}

/**
 * @param array $cart_item_data Data.
 * @param int   $product_id     Product.
 * @param int   $variation_id   Variation.
 * @return array
 */
function dejoiy_quickmart_add_cart_item_data( $cart_item_data, $product_id, $variation_id ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	if ( ! dejoiy_quickmart_is_product( $product_id ) ) {
		return $cart_item_data;
	}
	if ( dejoiy_quickmart_is_add_request() || dejoiy_quickmart_is_cart_context() ) {
		$cart_item_data['dejoiy_quickmart_item'] = 1;
	}
	return $cart_item_data;
}

/**
 * @param array  $cart_item     Item.
 * @param array  $values        Session.
 * @param string $cart_item_key Key.
 * @return array
 */
function dejoiy_quickmart_get_cart_item_from_session( $cart_item, $values, $cart_item_key ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	if ( isset( $values['dejoiy_quickmart_item'] ) ) {
		$cart_item['dejoiy_quickmart_item'] = $values['dejoiy_quickmart_item'];
	}
	return $cart_item;
}

/**
 * @param bool $passed     Passed.
 * @param int  $product_id Product.
 * @param int  $quantity   Qty.
 * @return bool
 */
function dejoiy_quickmart_add_to_cart_validation( $passed, $product_id, $quantity ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	if ( ! $passed ) {
		return false;
	}
	$is_qm  = dejoiy_quickmart_is_product( $product_id );
	$qm_ctx = dejoiy_quickmart_is_cart_context() || dejoiy_quickmart_is_add_request();

	if ( $is_qm && ! $qm_ctx && function_exists( 'dejoiy_quickmart_is_quickmart_page' ) && ! dejoiy_quickmart_is_quickmart_page() ) {
		wc_add_notice(
			__( 'This item is available on QuickMart. Open QuickMart to add it to your quick cart.', 'dejoiy' ),
			'error'
		);
		return false;
	}

	if ( ! $is_qm && dejoiy_quickmart_is_cart_context() ) {
		wc_add_notice(
			__( 'Only QuickMart items can be added here. Marketplace products use the main cart.', 'dejoiy' ),
			'error'
		);
		return false;
	}

	return $passed;
}

/**
 * @return int
 */
function dejoiy_quickmart_get_cart_count() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return 0;
	}
	$qty = 0;
	foreach ( WC()->cart->get_cart() as $item ) {
		if ( dejoiy_quickmart_cart_line_is_quickmart( $item ) ) {
			$qty += isset( $item['quantity'] ) ? (int) $item['quantity'] : 0;
		}
	}
	return max( 0, $qty );
}

/**
 * Send legacy /cart/?dejoiy_quickmart=1 links to in-app QuickMart cart.
 */
function dejoiy_quickmart_redirect_cart() {
	if ( is_admin() || wp_doing_ajax() || 'GET' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) ) ) {
		return;
	}
	if ( ! function_exists( 'is_cart' ) || ! is_cart() || is_wc_endpoint_url() ) {
		return;
	}
	if ( ! dejoiy_quickmart_has_flow_request() ) {
		return;
	}
	if ( function_exists( 'dejoiy_quickmart_cart_url' ) ) {
		wp_safe_redirect( dejoiy_quickmart_get_cart_url() );
		exit;
	}
}

/**
 * @return bool
 */
function dejoiy_quickmart_cart_has_items() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return false;
	}
	foreach ( WC()->cart->get_cart() as $item ) {
		if ( dejoiy_quickmart_cart_line_is_quickmart( $item ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Marketplace mini-cart count excludes QuickMart lines (header only).
 *
 * @param int $count Count.
 * @return int
 */
function dejoiy_quickmart_filter_cart_contents_count( $count ) {
	if ( dejoiy_quickmart_is_cart_context() ) {
		return dejoiy_quickmart_get_cart_count();
	}
	if ( function_exists( 'is_cart' ) && is_cart() ) {
		return $count;
	}
	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		return $count;
	}
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return $count;
	}
	$qm = 0;
	foreach ( WC()->cart->get_cart() as $item ) {
		if ( dejoiy_quickmart_cart_line_is_quickmart( $item ) ) {
			$qm += isset( $item['quantity'] ) ? (int) $item['quantity'] : 0;
		}
	}
	return max( 0, (int) $count - $qm );
}

/**
 * Legacy QuickMart checkout URLs only — never redirect main DEJOIY checkout.
 */
function dejoiy_quickmart_redirect_checkout() {
	if ( is_admin() || wp_doing_ajax() ) {
		return;
	}
	if ( ! dejoiy_quickmart_has_flow_request() ) {
		return;
	}
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() || is_wc_endpoint_url( 'order-received' ) ) {
		return;
	}
	if ( function_exists( 'dejoiy_quickmart_is_checkout_view' ) && dejoiy_quickmart_is_checkout_view() ) {
		return;
	}
	if ( function_exists( 'dejoiy_quickmart_checkout_url' ) ) {
		wp_safe_redirect( dejoiy_quickmart_get_checkout_url() );
		exit;
	}
}

/**
 * Register WooCommerce hooks (skipped on main marketplace cart page).
 */
function dejoiy_quickmart_register_cart_hooks() {
	static $done = false;
	if ( $done || ! dejoiy_quickmart_should_register_hooks() ) {
		return;
	}
	$done = true;

	add_filter( 'woocommerce_cart_item_visible', 'dejoiy_quickmart_cart_item_visible', 11, 3 );
	add_filter( 'woocommerce_add_cart_item_data', 'dejoiy_quickmart_add_cart_item_data', 15, 3 );
	add_filter( 'woocommerce_get_cart_item_from_session', 'dejoiy_quickmart_get_cart_item_from_session', 15, 3 );
	add_filter( 'woocommerce_add_to_cart_validation', 'dejoiy_quickmart_add_to_cart_validation', 15, 3 );
	add_filter( 'woocommerce_cart_contents_count', 'dejoiy_quickmart_filter_cart_contents_count', 18 );
	add_action( 'template_redirect', 'dejoiy_quickmart_redirect_cart', 12 );
	add_action( 'template_redirect', 'dejoiy_quickmart_redirect_checkout', 11 );
}

add_action( 'wp', 'dejoiy_quickmart_register_cart_hooks', 1 );
add_action( 'wc_ajax_add_to_cart', 'dejoiy_quickmart_register_cart_hooks', 0 );
add_action( 'wp_ajax_woocommerce_add_to_cart', 'dejoiy_quickmart_register_cart_hooks', 0 );
add_action( 'wp_ajax_nopriv_woocommerce_add_to_cart', 'dejoiy_quickmart_register_cart_hooks', 0 );
