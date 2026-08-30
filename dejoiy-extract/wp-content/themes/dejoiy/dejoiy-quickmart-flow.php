<?php
/**
 * QuickMart — isolated cart, checkout, and product surfaces.
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return bool
 */
function dejoiy_quickmart_is_product_surface() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return false;
	}
	$pid = get_queried_object_id();
	return $pid && function_exists( 'dejoiy_quickmart_is_product' ) && dejoiy_quickmart_is_product( $pid );
}

/**
 * @return bool
 */
function dejoiy_quickmart_is_flow_surface() {
	if ( function_exists( 'dejoiy_quickmart_is_app_page' ) && dejoiy_quickmart_is_app_page() ) {
		return true;
	}
	if ( function_exists( 'dejoiy_quickmart_is_cart_context' ) && dejoiy_quickmart_is_cart_context() ) {
		return true;
	}
	return dejoiy_quickmart_is_product_surface();
}

/**
 * @return string home|search|flow
 */
function dejoiy_quickmart_chrome_mode() {
	if ( function_exists( 'dejoiy_quickmart_is_search_view' ) && dejoiy_quickmart_is_search_view() ) {
		return 'search';
	}
	if ( function_exists( 'is_page' ) && is_page( 'dejoiy-quick-mart' ) ) {
		return 'home';
	}
	if ( function_exists( 'dejoiy_quickmart_is_cart_context' ) && dejoiy_quickmart_is_cart_context() ) {
		return 'flow';
	}
	if ( dejoiy_quickmart_is_product_surface() ) {
		return 'flow';
	}
	return 'off';
}

/**
 * @param array<int, string> $classes Body classes.
 * @return array<int, string>
 */
function dejoiy_quickmart_flow_body_class( $classes ) {
	if ( dejoiy_quickmart_is_product_surface() ) {
		$classes[] = 'dejoiy-quickmart-product-view';
		$classes[] = 'dejoiy-quickmart-flow';
		$classes[] = 'dejoiy-mobile-os-off';
	}
	if ( function_exists( 'dejoiy_quickmart_is_cart_context' ) && dejoiy_quickmart_is_cart_context() ) {
		$classes[] = 'dejoiy-quickmart-flow';
		$classes[] = 'dejoiy-mobile-os-off';
		if ( function_exists( 'is_cart' ) && is_cart() ) {
			$classes[] = 'dejoiy-quickmart-cart-view';
		}
		if ( function_exists( 'is_checkout' ) && is_checkout() ) {
			$classes[] = 'dejoiy-quickmart-checkout-view';
		}
	}
	return $classes;
}
add_filter( 'body_class', 'dejoiy_quickmart_flow_body_class', 23 );

/**
 * Enqueue flow styles (cart / checkout / product).
 */
function dejoiy_quickmart_flow_assets() {
	if ( ! dejoiy_quickmart_is_cart_context() && ! dejoiy_quickmart_is_product_surface() ) {
		return;
	}
	$dir = get_stylesheet_directory();
	$uri = get_stylesheet_directory_uri();
	$css = $dir . '/dejoiy-quickmart-flow.css';
	if ( is_readable( $css ) ) {
		wp_enqueue_style(
			'dejoiy-quickmart-flow',
			$uri . '/dejoiy-quickmart-flow.css',
			array( 'dejoiy-quickmart-blinkit' ),
			(string) filemtime( $css )
		);
	}
}

/**
 * If QuickMart cart URL has no quick lines (only marketplace items), use normal cart.
 */
function dejoiy_quickmart_flow_empty_cart_redirect() {
	if ( is_admin() || wp_doing_ajax() ) {
		return;
	}
	if ( ! function_exists( 'is_cart' ) || ! is_cart() || ! dejoiy_quickmart_has_flow_request() ) {
		return;
	}
	if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
		return;
	}
	if ( dejoiy_quickmart_get_cart_count() > 0 ) {
		return;
	}
	wp_safe_redirect( remove_query_arg( DEJOIY_QUICKMART_FLOW ) );
	exit;
}
add_action( 'template_redirect', 'dejoiy_quickmart_flow_empty_cart_redirect', 13 );

add_action( 'wp_enqueue_scripts', 'dejoiy_quickmart_flow_assets', 10075 );

/**
 * Auto-append flow flag to QuickMart product add-to-cart on product page.
 *
 * @param string     $url     URL.
 * @param WC_Product $product Product.
 * @return string
 */
function dejoiy_quickmart_flow_add_to_cart_url( $url, $product ) {
	if ( ! $product || ! dejoiy_quickmart_is_product_surface() ) {
		return $url;
	}
	if ( ! dejoiy_quickmart_is_product( $product->get_id() ) ) {
		return $url;
	}
	return add_query_arg( DEJOIY_QUICKMART_FLOW, '1', $url );
}
add_filter( 'woocommerce_product_add_to_cart_url', 'dejoiy_quickmart_flow_add_to_cart_url', 20, 2 );
/**
 * Flow header (cart / checkout / product) — printed from UI module.
 *
 * @return void
 */
function dejoiy_quickmart_print_flow_chrome() {
	if ( 'flow' !== dejoiy_quickmart_chrome_mode() ) {
		return;
	}
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;

	$home    = function_exists( 'dejoiy_quickmart_base_url' ) ? dejoiy_quickmart_base_url() : home_url( '/dejoiy-quick-mart/' );
	$cart    = dejoiy_quickmart_get_cart_url();
	$search  = function_exists( 'dejoiy_quickmart_search_url' ) ? dejoiy_quickmart_search_url() : $home;
	$count   = dejoiy_quickmart_get_cart_count();
	$eta     = function_exists( 'dejoiy_quickmart_eta_label' ) ? dejoiy_quickmart_eta_label() : '10 mins';
	$title   = __( 'QuickMart', 'dejoiy' );

	if ( function_exists( 'is_cart' ) && is_cart() ) {
		$title = __( 'Your QuickMart cart', 'dejoiy' );
	} elseif ( function_exists( 'is_checkout' ) && is_checkout() ) {
		$title = __( 'Quick checkout', 'dejoiy' );
	} elseif ( dejoiy_quickmart_is_product_surface() ) {
		$title = __( 'Product', 'dejoiy' );
	}

	?>
	<div id="dejoiy-quickmart-chrome" class="dejoiy-quickmart-chrome dejoiy-quickmart-chrome--flow">
		<header class="qm-flow-bar" role="banner">
			<a class="qm-flow-bar__back" href="<?php echo esc_url( $home ); ?>">←</a>
			<div class="qm-flow-bar__center">
				<strong class="qm-flow-bar__title"><?php echo esc_html( $title ); ?></strong>
				<span class="qm-flow-bar__eta"><?php echo esc_html( str_replace( '–', ' ', $eta ) ); ?></span>
			</div>
			<a class="qm-flow-bar__cart" href="<?php echo esc_url( $cart ); ?>">
				<?php esc_html_e( 'Cart', 'dejoiy' ); ?>
				<?php if ( $count > 0 ) : ?>
					<span class="qm-top__cart-badge"><?php echo esc_html( (string) $count ); ?></span>
				<?php endif; ?>
			</a>
		</header>
		<nav class="qm-bottom-nav qm-bottom-nav--flow" aria-label="<?php esc_attr_e( 'QuickMart', 'dejoiy' ); ?>">
			<a href="<?php echo esc_url( $home ); ?>" class="qm-bottom-nav__item"><span aria-hidden="true">⌂</span><?php esc_html_e( 'Home', 'dejoiy' ); ?></a>
			<a href="<?php echo esc_url( $search ); ?>" class="qm-bottom-nav__item"><span aria-hidden="true">🔍</span><?php esc_html_e( 'Search', 'dejoiy' ); ?></a>
			<a href="<?php echo esc_url( $cart ); ?>" class="qm-bottom-nav__item is-active"><span aria-hidden="true">🛒</span><?php esc_html_e( 'Basket', 'dejoiy' ); ?></a>
		</nav>
	</div>
	<?php
	if ( function_exists( 'dejoiy_quickmart_print_shared_modals' ) ) {
		dejoiy_quickmart_print_shared_modals();
	}
}

