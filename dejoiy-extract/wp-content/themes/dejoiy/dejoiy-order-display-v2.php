<?php
/**
 * DEJOIY order display numbers v2 (presentation layer).
 *
 * Physical: YYYYMMDD-XXX-W{order_id}
 * Digital:  DDO-YYYYMMDD-W{order_id}
 *
 * Does not change WooCommerce internal order IDs.
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return string
 */
function dejoiy_order_v2_meta_key() {
	return '_dejoiy_display_order_v2';
}

/**
 * @param WC_Order $order Order.
 * @return bool
 */
function dejoiy_order_is_digital_v2( $order ) {
	if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
		return false;
	}
	foreach ( $order->get_items() as $item ) {
		$product = $item->get_product();
		if ( ! $product ) {
			continue;
		}
		if ( get_post_meta( $product->get_id(), '_dejoiy_library_book', true ) ) {
			return true;
		}
		if ( $product->is_virtual() || $product->is_downloadable() ) {
			return true;
		}
	}
	return false;
}

/**
 * Build v2 display order number.
 *
 * @param WC_Order $order Order.
 * @return string
 */
function dejoiy_build_display_order_v2( $order ) {
	if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
		return '';
	}
	$order_id  = $order->get_id();
	$date_part = gmdate( 'Ymd', $order->get_date_created() ? $order->get_date_created()->getTimestamp() : time() );
	$wc_part   = 'W' . (string) $order_id;

	if ( dejoiy_order_is_digital_v2( $order ) ) {
		return 'DDO-' . $date_part . '-' . $wc_part;
	}

	$random = str_pad( (string) wp_rand( 0, 999 ), 3, '0', STR_PAD_LEFT );
	return $date_part . '-' . $random . '-' . $wc_part;
}

/**
 * Persist v2 number once per order.
 *
 * @param int $order_id Order ID.
 */
function dejoiy_generate_display_order_v2( $order_id ) {
	$order_id = (int) $order_id;
	if ( $order_id < 1 ) {
		return;
	}
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}
	if ( $order->get_meta( dejoiy_order_v2_meta_key(), true ) ) {
		return;
	}
	$number = dejoiy_build_display_order_v2( $order );
	if ( $number ) {
		$order->update_meta_data( dejoiy_order_v2_meta_key(), $number );
		$order->save();
	}
}

add_action( 'woocommerce_checkout_order_processed', 'dejoiy_generate_display_order_v2', 25 );
add_action( 'woocommerce_new_order', 'dejoiy_generate_display_order_v2', 25 );

/**
 * Prefer v2 display number on customer-facing surfaces.
 *
 * @param string   $order_number Order number.
 * @param WC_Order $order        Order.
 * @return string
 */
function dejoiy_filter_display_order_v2( $order_number, $order ) {
	if ( ! $order ) {
		return $order_number;
	}
	$v2 = $order->get_meta( dejoiy_order_v2_meta_key(), true );
	if ( $v2 ) {
		return (string) $v2;
	}
	return $order_number;
}
add_filter( 'woocommerce_order_number', 'dejoiy_filter_display_order_v2', 999, 2 );
