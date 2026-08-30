<?php
/**
 * Channel checkouts — same WooCommerce checkout page as marketplace, Nexus/Studio chrome only.
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the native WooCommerce checkout page content (marketplace clone).
 */
function dejoiy_channel_render_wc_checkout_page_content() {
	if ( ! function_exists( 'wc_get_page_id' ) ) {
		if ( function_exists( 'woocommerce_checkout' ) ) {
			woocommerce_checkout();
		}
		return;
	}

	$page_id = (int) wc_get_page_id( 'checkout' );
	$post    = $page_id > 0 ? get_post( $page_id ) : null;

	if ( $post && '' !== trim( (string) $post->post_content ) ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- same as marketplace checkout page.
		echo apply_filters( 'the_content', $post->post_content );
		return;
	}

	if ( function_exists( 'woocommerce_checkout' ) ) {
		woocommerce_checkout();
	}
}

/**
 * Remove unsellable Nexus lines once (prevents AUTO-DRAFT add/remove notice loops).
 */
function dejoiy_library_nexus_purge_unsellable_shelf_lines() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return;
	}
	if ( ! function_exists( 'dejoiy_library_is_nexus_cart_context' ) || ! dejoiy_library_is_nexus_cart_context() ) {
		return;
	}

	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;

	$removed = array();

	foreach ( WC()->cart->get_cart() as $key => $item ) {
		if ( ! function_exists( 'dejoiy_library_cart_line_is_nexus' ) || ! dejoiy_library_cart_line_is_nexus( $item ) ) {
			continue;
		}
		$product_id = isset( $item['product_id'] ) ? (int) $item['product_id'] : 0;
		if ( $product_id < 1 ) {
			continue;
		}
		if ( function_exists( 'dejoiy_library_maybe_adopt_product' ) ) {
			dejoiy_library_maybe_adopt_product( $product_id );
		}
		if ( function_exists( 'dejoiy_library_ensure_nexus_product_sellable' ) && dejoiy_library_ensure_nexus_product_sellable( $product_id ) ) {
			continue;
		}
		$product = function_exists( 'wc_get_product' ) ? wc_get_product( $product_id ) : null;
		$removed[] = $product ? $product->get_name() : '#' . $product_id;
		WC()->cart->remove_cart_item( $key );
	}

	if ( empty( $removed ) ) {
		return;
	}

	if ( function_exists( 'wc_get_notices' ) && function_exists( 'wc_clear_notices' ) && function_exists( 'wc_add_notice' ) ) {
		$errors = wc_get_notices( 'error' );
		wc_clear_notices( 'error' );
		foreach ( $errors as $notice ) {
			$text = is_array( $notice ) && isset( $notice['notice'] ) ? (string) $notice['notice'] : (string) $notice;
			$plain = wp_strip_all_tags( $text );
			if ( false !== stripos( $plain, 'removed from your cart' ) || false !== stripos( $plain, 'AUTO-DRAFT' ) ) {
				continue;
			}
			if ( is_array( $notice ) && isset( $notice['notice'] ) ) {
				wc_add_notice( $notice['notice'], 'error', isset( $notice['data'] ) ? $notice['data'] : array() );
			} else {
				wc_add_notice( $notice, 'error' );
			}
		}
		wc_add_notice(
			sprintf(
				/* translators: %s: comma-separated product names */
				__( 'Some shelf items are not ready to purchase yet. Publish them in WooCommerce with a real title and price: %s', 'dejoiy' ),
				implode( ', ', array_unique( $removed ) )
			),
			'notice'
		);
	}

	WC()->cart->calculate_totals();
}

/**
 * Strip add-to-cart query args from channel URLs (avoids re-add loops on checkout).
 *
 * @param string $url URL.
 * @return string
 */
function dejoiy_channel_strip_cart_query_args( $url ) {
	return remove_query_arg(
		array( 'add-to-cart', 'dejoiy_buy_now', 'added-to-cart', 'quantity' ),
		$url
	);
}
