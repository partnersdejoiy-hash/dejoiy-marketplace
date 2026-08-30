<?php
/**
 * Nexus chrome on WooCommerce pay / thank-you pages + hide marketplace header on mobile.
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/library-nexus-branding.php';

/**
 * @param WC_Order|null $order Order.
 * @return bool
 */
function dejoiy_library_order_has_nexus_items( $order ) {
	if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
		return false;
	}
	foreach ( $order->get_items() as $item ) {
		$product_id = (int) $item->get_product_id();
		if ( $product_id > 0 && function_exists( 'dejoiy_library_is_nexus_product' ) && dejoiy_library_is_nexus_product( $product_id ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Checkout order-pay or order-received after Nexus purchase.
 *
 * @return bool
 */
function dejoiy_library_is_nexus_wc_order_endpoint() {
	if ( ! did_action( 'wp' ) || ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
		return false;
	}
	if ( ! is_wc_endpoint_url( 'order-pay' ) && ! is_wc_endpoint_url( 'order-received' ) ) {
		return false;
	}
	if ( function_exists( 'dejoiy_library_is_nexus_flow_request' ) && dejoiy_library_is_nexus_flow_request() ) {
		return true;
	}
	if ( function_exists( 'dejoiy_library_is_active_cart_nexus' ) && dejoiy_library_is_active_cart_nexus() ) {
		return true;
	}
	$order_id = absint( get_query_var( 'order-pay' ) );
	if ( ! $order_id ) {
		$order_id = absint( get_query_var( 'order-received' ) );
	}
	if ( $order_id && function_exists( 'wc_get_order' ) ) {
		return dejoiy_library_order_has_nexus_items( wc_get_order( $order_id ) );
	}
	return false;
}

/**
 * Any Nexus storefront screen (for body class + hiding theme header).
 *
 * @return bool
 */
function dejoiy_library_is_nexus_chrome_screen() {
	if ( function_exists( 'dejoiy_library_is_screen' ) && dejoiy_library_is_screen() ) {
		return true;
	}
	if ( dejoiy_library_is_nexus_wc_order_endpoint() ) {
		return true;
	}
	if ( function_exists( 'dejoiy_library_is_nexus_flow_request' ) && dejoiy_library_is_nexus_flow_request() ) {
		if ( function_exists( 'dejoiy_library_is_wc_cart_page_raw' ) && dejoiy_library_is_wc_cart_page_raw() ) {
			return true;
		}
		if ( function_exists( 'dejoiy_library_is_wc_checkout_page_raw' ) && dejoiy_library_is_wc_checkout_page_raw() ) {
			return true;
		}
	}
	return false;
}

/**
 * @param array $classes Body classes.
 * @return array
 */
function dejoiy_library_nexus_body_class( $classes ) {
	if ( dejoiy_library_is_nexus_chrome_screen() ) {
		$classes[] = 'dejoiy-library-active';
		$classes[] = 'dejoiy-nexus-active';
		$classes[] = 'dlu-screen';
	}
	return $classes;
}

/**
 * Keep ?dejoiy_library=1 on Razorpay / order-pay links for Nexus orders.
 *
 * @param string   $url   Payment URL.
 * @param WC_Order $order Order.
 * @return string
 */
function dejoiy_library_nexus_order_pay_url( $url, $order ) {
	if ( ! defined( 'DEJOIY_LIBRARY_FLOW' ) || ! dejoiy_library_order_has_nexus_items( $order ) ) {
		return $url;
	}
	if ( false !== strpos( $url, DEJOIY_LIBRARY_FLOW . '=' ) ) {
		return $url;
	}
	return add_query_arg( DEJOIY_LIBRARY_FLOW, '1', $url );
}

/**
 * Minimal Nexus bar on order-pay (marketplace theme header hidden via CSS).
 */
function dejoiy_library_nexus_order_pay_bar() {
	if ( ! dejoiy_library_is_nexus_wc_order_endpoint() ) {
		return;
	}
	$home = function_exists( 'dejoiy_library_get_landing_url' ) ? dejoiy_library_get_landing_url() : home_url( '/dejoiy-library/' );
	$logo = function_exists( 'dejoiy_library_get_logo_url' ) ? dejoiy_library_get_logo_url( 96 ) : '';
	?>
	<div class="dlu-hdr dlu-hdr--compact dlu-hdr--pay-bar" role="banner">
		<div class="dlu-hdr-in">
			<div class="dlu-hdr-bar">
				<a class="dlu-brand" href="<?php echo esc_url( $home ); ?>">
					<?php if ( $logo ) : ?>
						<img class="dlu-brand-logo" src="<?php echo esc_url( $logo ); ?>" alt="" width="32" height="32" decoding="async" />
					<?php endif; ?>
					<span class="dlu-brand-text">DEJOIY <em>Nexus</em></span>
				</a>
				<a class="dlu-btn-pass dlu-pay-back" href="<?php echo esc_url( add_query_arg( DEJOIY_LIBRARY_FLOW, '1', $home ) ); ?>"><?php esc_html_e( '← Nexus', 'dejoiy' ); ?></a>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Register Nexus chrome helpers.
 */
function dejoiy_library_nexus_chrome_init() {
	if ( is_admin() ) {
		return;
	}
	add_filter( 'body_class', 'dejoiy_library_nexus_body_class', 25 );
	add_filter( 'woocommerce_get_checkout_payment_url', 'dejoiy_library_nexus_order_pay_url', 10, 2 );
	add_action( 'wp_body_open', 'dejoiy_library_nexus_order_pay_bar', 2 );
}
