<?php
/**
 * DEJOIY Universal Checkout — shared layout, fewer shipping/map fields, works on all flows.
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return string
 */
function dejoiy_universal_checkout_title() {
	return apply_filters(
		'dejoiy_universal_checkout_title',
		__( 'Your Universal Checkout for Your Joy', 'dejoiy' )
	);
}

/**
 * @return bool True when every cart line is virtual / does not need shipping.
 */
function dejoiy_universal_cart_is_virtual_only() {
	if ( ! dejoiy_universal_is_checkout_screen() || ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
		return false;
	}
	foreach ( WC()->cart->get_cart() as $item ) {
		$product = isset( $item['data'] ) && is_a( $item['data'], 'WC_Product' ) ? $item['data'] : null;
		if ( ! $product ) {
			continue;
		}
		if ( $product->needs_shipping() && ! $product->is_virtual() ) {
			return false;
		}
	}
	return true;
}

/**
 * Remove map / delivery / geo fields from checkout field groups.
 *
 * @param array $fields Checkout fields.
 * @return array
 */
function dejoiy_universal_strip_delivery_fields( $fields ) {
	if ( ! is_array( $fields ) ) {
		return $fields;
	}

	$needles = array( 'map', 'delivery', 'location', 'geo', 'latitude', 'longitude', 'coordinates', 'wcfm', 'pickup', 'drop' );

	foreach ( array( 'billing', 'shipping', 'order' ) as $group ) {
		if ( empty( $fields[ $group ] ) || ! is_array( $fields[ $group ] ) ) {
			continue;
		}
		foreach ( $fields[ $group ] as $key => $field ) {
			$label = isset( $field['label'] ) ? (string) $field['label'] : '';
			$hay   = strtolower( $key . ' ' . $label );
			foreach ( $needles as $needle ) {
				if ( false !== strpos( $hay, $needle ) ) {
					unset( $fields[ $group ][ $key ] );
					break;
				}
			}
		}
	}

	return $fields;
}

/**
 * @param array $fields Checkout fields.
 * @return array
 */
function dejoiy_universal_checkout_fields( $fields ) {
	if ( ! dejoiy_universal_is_checkout_screen() ) {
		return $fields;
	}

	$fields = dejoiy_universal_strip_delivery_fields( $fields );

	if ( dejoiy_universal_cart_is_virtual_only() ) {
		unset( $fields['shipping'] );
	}

	return $fields;
}

/**
 * @param bool $needs_shipping Needs shipping.
 * @return bool
 */
function dejoiy_universal_cart_needs_shipping( $needs_shipping ) {
	if ( dejoiy_universal_is_checkout_screen() && dejoiy_universal_cart_is_virtual_only() ) {
		return false;
	}
	return $needs_shipping;
}

/**
 * @param bool $needs_address Needs shipping address.
 * @return bool
 */
function dejoiy_universal_needs_shipping_address( $needs_address ) {
	if ( dejoiy_universal_is_checkout_screen() && dejoiy_universal_cart_is_virtual_only() ) {
		return false;
	}
	return $needs_address;
}

/**
 * Checkout hero + wrapper open.
 */
function dejoiy_universal_checkout_open() {
	if ( ! dejoiy_universal_is_checkout_screen() ) {
		return;
	}
	echo '<div class="dejoiy-uc-wrap">';
	echo '<div class="dejoiy-uc-hero">';
	echo '<h1 class="dejoiy-uc-title">' . esc_html( dejoiy_universal_checkout_title() ) . '</h1>';
	echo '<p class="dejoiy-uc-sub">';
	esc_html_e( 'One peaceful checkout for books, studio creations, and marketplace finds — made for your joy.', 'dejoiy' );
	echo '</p></div>';
}

/**
 * Wrapper close.
 */
function dejoiy_universal_checkout_close() {
	if ( ! dejoiy_universal_is_checkout_screen() ) {
		return;
	}
	echo '</div><!-- .dejoiy-uc-wrap -->';
}

/**
 * @param array $classes Body classes.
 * @return array
 */
function dejoiy_universal_checkout_body_class( $classes ) {
	if ( dejoiy_universal_is_checkout_screen() ) {
		$classes[] = 'dejoiy-universal-checkout';
		if ( dejoiy_universal_cart_is_virtual_only() ) {
			$classes[] = 'dejoiy-universal-checkout--virtual';
		}
	}
	return $classes;
}

/**
 * Enqueue universal checkout styles.
 */
function dejoiy_universal_checkout_assets() {
	if ( ! dejoiy_universal_is_checkout_screen() ) {
		return;
	}
	$path = get_stylesheet_directory() . '/dejoiy-universal-checkout.css';
	if ( ! is_readable( $path ) ) {
		return;
	}
	wp_enqueue_style(
		'dejoiy-universal-checkout',
		get_stylesheet_directory_uri() . '/dejoiy-universal-checkout.css',
		array(),
		(string) filemtime( $path )
	);
}

/**
 * Amazon-style purchase protection trust seal
 */
function dejoiy_universal_checkout_trust_seal() {
	?>
	<div class="dejoiy-checkout-trust-badge" style="margin:18px 0;padding:14px 18px;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:12px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;font-size:12.5px;color:#334155;font-family:'Inter',sans-serif;">
		<div style="display:flex;align-items:center;gap:10px;">
			<span style="font-size:20px;">🛡️</span>
			<div>
				<strong style="color:#0f172a;font-size:13px;">DEJOIY 100% Purchase Protection</strong>
				<div style="font-size:11.5px;color:#64748b;margin-top:2px;">Genuine Products • Direct Seller Guarantee • Safe Delivery</div>
			</div>
		</div>
		<div style="display:flex;align-items:center;gap:14px;font-weight:600;font-size:12px;color:#475569;">
			<span>🔒 256-Bit SSL</span>
			<span>💳 Razorpay / UPI</span>
			<span>🔄 7-Day Returns</span>
		</div>
	</div>
	<?php
}

/**
 * Register universal checkout hooks.
 */
function dejoiy_universal_checkout_init() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	add_filter( 'woocommerce_checkout_fields', 'dejoiy_universal_checkout_fields', 99 );
	add_filter( 'woocommerce_cart_needs_shipping', 'dejoiy_universal_cart_needs_shipping', 99 );
	add_filter( 'woocommerce_cart_needs_shipping_address', 'dejoiy_universal_needs_shipping_address', 99 );
	add_filter( 'body_class', 'dejoiy_universal_checkout_body_class' );
	add_action( 'woocommerce_before_checkout_form', 'dejoiy_universal_checkout_open', 4 );
	add_action( 'woocommerce_after_checkout_form', 'dejoiy_universal_checkout_close', 99 );
	add_action( 'woocommerce_review_order_after_payment', 'dejoiy_universal_checkout_trust_seal', 15 );
	add_action( 'wp_enqueue_scripts', 'dejoiy_universal_checkout_assets', 1015 );
}
