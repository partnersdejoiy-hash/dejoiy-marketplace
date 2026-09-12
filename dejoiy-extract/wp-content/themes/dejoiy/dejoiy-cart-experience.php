<?php
/**
 * DEJOIY Cart Experience — premium marketplace cart (/cart).
 *
 * Additive UI on WooCommerce cart. Does not replace templates or cart logic.
 * Disable: define( 'DEJOIY_CART_XP_DISABLED', true );
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'DEJOIY_CART_XP_VERSION' ) ) {
	define( 'DEJOIY_CART_XP_VERSION', '1.2.0' );
}

/**
 * @return bool
 */
function dejoiy_cart_xp_enabled() {
	if ( defined( 'DEJOIY_CART_XP_DISABLED' ) && DEJOIY_CART_XP_DISABLED ) {
		return false;
	}
	if ( ! function_exists( 'dejoiy_evolution_is_enabled' ) || ! dejoiy_evolution_is_enabled() ) {
		return false;
	}
	if ( ! class_exists( 'WooCommerce' ) ) {
		return false;
	}
	return (bool) apply_filters( 'dejoiy_cart_xp_enabled', true );
}

/**
 * Marketplace cart page (not Nexus shelf / QuickMart mode).
 *
 * @return bool
 */
function dejoiy_cart_xp_is_cart_page() {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return false;
	}
	$uri = strtolower( (string) wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) );
	if ( '' !== $uri && preg_match( '~/nexus/cart(/|\?|$)~', $uri ) ) {
		return false;
	}
	if ( function_exists( 'dejoiy_library_use_cart_template' ) && dejoiy_library_use_cart_template() ) {
		return false;
	}
	if ( function_exists( 'dejoiy_mobile_os_is_quickmart_cart_context' ) && dejoiy_mobile_os_is_quickmart_cart_context() ) {
		return false;
	}
	if ( function_exists( 'is_cart' ) && is_cart() && ! is_wc_endpoint_url() ) {
		return true;
	}
	if ( function_exists( 'wc_get_page_id' ) ) {
		$cart_id = (int) wc_get_page_id( 'cart' );
		if ( $cart_id > 0 && is_page( $cart_id ) ) {
			return true;
		}
	}
	return '' !== $uri && (bool) preg_match( '~/cart(/|\?|#|$)~', $uri );
}

/**
 * @return bool
 */
function dejoiy_cart_xp_is_active() {
	return dejoiy_cart_xp_enabled() && dejoiy_cart_xp_is_cart_page();
}

/**
 * @return string
 */
function dejoiy_cart_xp_checkout_url() {
	return function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/checkout/' );
}

/**
 * @return string
 */
function dejoiy_cart_xp_shop_url() {
	return function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
}

/**
 * Ecosystem label + color for a product.
 *
 * @param WC_Product $product Product.
 * @return array{label: string, color: string}
 */
function dejoiy_cart_xp_product_eco_badge( $product ) {
	$map = array(
		'marketplace' => array( 'label' => __( 'Marketplace', 'dejoiy' ), 'color' => '#1e3a8a' ),
		'studio'      => array( 'label' => __( 'Studio', 'dejoiy' ), 'color' => '#0f172a' ),
		'nexus'       => array( 'label' => __( 'Nexus', 'dejoiy' ), 'color' => '#7c3aed' ),
		'services'    => array( 'label' => __( 'Services', 'dejoiy' ), 'color' => '#ea580c' ),
		'quickmart'   => array( 'label' => __( 'Quick', 'dejoiy' ), 'color' => '#eab308' ),
		'refurbished' => array( 'label' => __( 'Refurbished', 'dejoiy' ), 'color' => '#06b6d4' ),
	);
	$key = 'marketplace';
	if ( function_exists( 'dejoiy_get_product_ecosystem' ) && $product ) {
		$eco = dejoiy_get_product_ecosystem( $product->get_id() );
		if ( isset( $map[ $eco ] ) ) {
			$key = $eco;
		} elseif ( 'quick' === $eco ) {
			$key = 'quickmart';
		}
	}
	return $map[ $key ];
}

/**
 * Vendor / Seller info helper for cart items.
 *
 * @param WC_Product|null $product Product.
 * @return array{name: string, url: string}
 */
function dejoiy_cart_xp_seller_info( $product ) {
	if ( ! $product ) {
		return array( 'name' => 'DEJOIY Marketplace', 'url' => home_url( '/shop/' ) );
	}
	if ( function_exists( 'dejoiy_get_pdp_seller_info' ) ) {
		return dejoiy_get_pdp_seller_info( $product );
	}
	$product_id = $product->get_id();
	$vendor_id  = 0;
	$store_name = '';
	$store_url  = '';

	if ( class_exists( 'DSO_Messenger' ) ) {
		$info       = DSO_Messenger::get_item_vendor_info( $product_id );
		$vendor_id  = isset( $info['vendor_id'] ) ? (int) $info['vendor_id'] : 0;
		$store_name = isset( $info['store_name'] ) ? (string) $info['store_name'] : '';
	}
	if ( empty( $store_name ) && function_exists( 'wcfm_get_vendor_id_by_post' ) ) {
		$vendor_id = (int) wcfm_get_vendor_id_by_post( $product_id );
		if ( $vendor_id && function_exists( 'wcfm_get_vendor_store_name' ) ) {
			$store_name = wcfm_get_vendor_store_name( $vendor_id );
		}
	}
	if ( empty( $store_name ) ) {
		$post = get_post( $product_id );
		if ( $post && $post->post_author ) {
			$author = get_userdata( $post->post_author );
			if ( $author ) {
				$store_name = $author->display_name ?: $author->user_login;
			}
		}
	}
	if ( empty( $store_name ) ) {
		$store_name = 'DEJOIY Verified Merchant';
	}
	if ( $vendor_id ) {
		if ( function_exists( 'wcfmmp_get_store_url' ) ) {
			$store_url = wcfmmp_get_store_url( $vendor_id );
		}
		if ( empty( $store_url ) ) {
			$u = get_userdata( $vendor_id );
			if ( $u ) {
				$store_url = home_url( '/store/' . $u->user_nicename . '/' );
			}
		}
	}
	if ( empty( $store_url ) ) {
		$store_url = home_url( '/shop/' );
	}
	return array(
		'name' => $store_name,
		'url'  => $store_url,
	);
}

/**
 * Total savings from sale prices in cart.
 *
 * @return float
 */
function dejoiy_cart_xp_cart_savings() {
	if ( ! WC()->cart ) {
		return 0.0;
	}
	$saved = 0.0;
	foreach ( WC()->cart->get_cart() as $item ) {
		$product = $item['data'] ?? null;
		if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
			continue;
		}
		$regular = (float) $product->get_regular_price();
		$current = (float) $product->get_price();
		if ( $regular > $current && $current > 0 ) {
			$saved += ( $regular - $current ) * (int) $item['quantity'];
		}
	}
	return max( 0.0, $saved );
}

/**
 * Free-shipping progress data.
 *
 * @return array{threshold: float, subtotal: float, remaining: float, percent: int}
 */
function dejoiy_cart_xp_shipping_progress() {
	$threshold = (float) apply_filters( 'dejoiy_cart_xp_free_shipping_threshold', 500 );
	$subtotal  = WC()->cart ? (float) WC()->cart->get_displayed_subtotal() : 0.0;
	$remaining = max( 0.0, $threshold - $subtotal );
	$percent   = $threshold > 0 ? (int) min( 100, round( ( $subtotal / $threshold ) * 100 ) ) : 0;
	return compact( 'threshold', 'subtotal', 'remaining', 'percent' );
}

/**
 * @param array<int, string> $classes Classes.
 * @return array<int, string>
 */
function dejoiy_cart_xp_body_class( $classes ) {
	if ( dejoiy_cart_xp_is_active() ) {
		$classes[] = 'dejoiy-cart-xp';
	}
	return $classes;
}
add_filter( 'body_class', 'dejoiy_cart_xp_body_class', 24 );

/**
 * Enqueue assets.
 */
function dejoiy_cart_xp_assets() {
	if ( ! dejoiy_cart_xp_is_active() ) {
		return;
	}
	$dir = get_stylesheet_directory();
	$uri = get_stylesheet_directory_uri();
	$css = $dir . '/dejoiy-cart-experience.css';
	$js  = $dir . '/dejoiy-cart-experience.js';
	$ver = DEJOIY_CART_XP_VERSION;
	if ( is_readable( $css ) ) {
		wp_enqueue_style(
			'dejoiy-cart-experience',
			$uri . '/dejoiy-cart-experience.css',
			array(),
			$ver . '.' . (string) filemtime( $css )
		);
	}
	if ( is_readable( $js ) ) {
		wp_enqueue_script(
			'dejoiy-cart-experience',
			$uri . '/dejoiy-cart-experience.js',
			array( 'jquery' ),
			$ver . '.' . (string) filemtime( $js ),
			true
		);
		wp_localize_script(
			'dejoiy-cart-experience',
			'dejoiyCartXp',
			array(
				'checkoutUrl' => dejoiy_cart_xp_checkout_url(),
				'i18n'        => array(
					'proceed'   => __( 'Proceed to Buy', 'dejoiy' ),
					'protected' => __( 'Protected by DEJOIY Buyer Protection', 'dejoiy' ),
				),
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'dejoiy_cart_xp_assets', 1016 );

/**
 * Hide legacy / vendor chrome on cart.
 */
function dejoiy_cart_xp_hide_legacy() {
	if ( ! dejoiy_cart_xp_is_active() ) {
		return;
	}
	echo '<style id="dejoiy-cart-xp-guard">';
	echo 'body.dejoiy-cart-xp .page-title,body.dejoiy-cart-xp .woocommerce-breadcrumb,body.dejoiy-cart-xp .cart-checkout-nav{display:none!important;}';
	echo 'body.dejoiy-cart-xp .wcfmmp_become_vendor_link{display:none!important;}';
	echo 'body.dejoiy-cart-xp .product-sku,body.dejoiy-cart-xp .sku,body.dejoiy-cart-xp th.product-sku{display:none!important;}';
	echo '</style>';
}
add_action( 'wp_head', 'dejoiy_cart_xp_hide_legacy', 3 );

/**
 * Proceed to checkout button text.
 *
 * @param string $translated Translated.
 * @param string $text       Original.
 * @param string $domain     Domain.
 * @return string
 */
function dejoiy_cart_xp_gettext( $translated, $text, $domain ) {
	static $in_filter = false;
	if ( $in_filter || 'woocommerce' !== $domain ) {
		return $translated;
	}
	if ( 'Proceed to checkout' !== $text && 'Update cart' !== $text ) {
		return $translated;
	}
	$in_filter = true;
	if ( ! dejoiy_cart_xp_is_active() ) {
		$in_filter = false;
		return $translated;
	}
	$out = ( 'Proceed to checkout' === $text ) ? __( 'Proceed to Buy', 'dejoiy' ) : __( 'Update Bag', 'dejoiy' );
	$in_filter = false;
	return $out;
}
add_filter( 'gettext', 'dejoiy_cart_xp_gettext', 25, 3 );

/**
 * Remove default cross-sells (we render custom recommendations).
 */
function dejoiy_cart_xp_tweak_collaterals() {
	if ( ! dejoiy_cart_xp_is_active() ) {
		return;
	}
	remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display', 10 );
}
add_action( 'wp', 'dejoiy_cart_xp_tweak_collaterals', 20 );

/**
 * Smart savings + free shipping progress block.
 */
function dejoiy_cart_xp_savings_block() {
	if ( ! dejoiy_cart_xp_is_active() || ! WC()->cart || WC()->cart->is_empty() ) {
		return;
	}
	$saved      = dejoiy_cart_xp_cart_savings();
	$ship       = dejoiy_cart_xp_shipping_progress();
	$currency   = function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '₹';
	$item_count = (int) WC()->cart->get_cart_contents_count();
	?>
	<!-- Amazon Cart Top Header -->
	<div class="dcart-top-bar">
		<div class="dcart-top-bar__title-wrap">
			<h1 class="dcart-top-bar__title"><?php esc_html_e( 'Shopping Cart', 'dejoiy' ); ?></h1>
			<span class="dcart-top-bar__count"><?php echo esc_html( sprintf( _n( '(%d item)', '(%d items)', $item_count, 'dejoiy' ), $item_count ) ); ?></span>
		</div>
		<div class="dcart-top-bar__price-header dcart-hide-mobile"><?php esc_html_e( 'Price', 'dejoiy' ); ?></div>
	</div>

	<!-- Free Shipping Bar -->
	<div class="dcart-savings" data-dcart-savings>
		<div class="dcart-savings__main">
			<div class="dcart-savings__icon">
				<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
			</div>
			<div class="dcart-savings__details">
				<div class="dcart-savings__ship-top">
					<?php if ( $ship['remaining'] > 0 ) : ?>
						<span class="dcart-savings__threshold-text">
							<?php
							echo esc_html(
								sprintf(
									/* translators: 1: currency, 2: remaining amount */
									__( 'Add %1$s%2$s of eligible items to qualify for FREE Delivery', 'dejoiy' ),
									$currency,
									wc_format_decimal( $ship['remaining'], wc_get_price_decimals() )
								)
							);
							?>
						</span>
					<?php else : ?>
						<span class="dcart-savings__unlocked">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
							<?php esc_html_e( 'Your order is eligible for FREE Delivery.', 'dejoiy' ); ?>
						</span>
					<?php endif; ?>
				</div>
				<div class="dcart-savings__bar"><span style="width:<?php echo esc_attr( (string) $ship['percent'] ); ?>%"></span></div>
			</div>
		</div>
		<?php if ( $saved > 0 ) : ?>
			<div class="dcart-savings__saved-badge">
				<?php
				echo esc_html(
					sprintf(
						/* translators: %s: formatted savings amount */
						__( 'You saved %s%s on this order', 'dejoiy' ),
						$currency,
						wc_format_decimal( $saved, wc_get_price_decimals() )
					)
				);
				?>
			</div>
		<?php endif; ?>
	</div>
	<?php
}
add_action( 'woocommerce_before_cart_table', 'dejoiy_cart_xp_savings_block', 8 );

/**
 * Filter remove link to render clean Amazon-style delete action.
 *
 * @param string $link Original HTML link.
 * @param string $cart_item_key Cart item key.
 * @return string
 */
function dejoiy_cart_xp_remove_link( $link, $cart_item_key ) {
	if ( ! dejoiy_cart_xp_is_active() ) {
		return $link;
	}
	$remove_url = wc_get_cart_remove_url( $cart_item_key );
	return sprintf(
		'<a role="button" href="%s" class="dcart-action-link dcart-action-link--remove" data-cart-item-key="%s" title="%s"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg><span>%s</span></a>',
		esc_url( $remove_url ),
		esc_attr( $cart_item_key ),
		esc_attr__( 'Remove this item', 'dejoiy' ),
		esc_html__( 'Delete', 'dejoiy' )
	);
}
add_filter( 'woocommerce_cart_item_remove_link', 'dejoiy_cart_xp_remove_link', 20, 2 );

/**
 * Premium cart line card (replaces table cell product name).
 *
 * @param string $name          Name HTML.
 * @param array  $cart_item     Item.
 * @param string $cart_item_key Key.
 * @return string
 */
function dejoiy_cart_xp_cart_item_name( $name, $cart_item, $cart_item_key ) {
	if ( ! dejoiy_cart_xp_is_active() || ( function_exists( 'is_checkout' ) && is_checkout() ) ) {
		return $name;
	}
	$product = $cart_item['data'] ?? null;
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return $name;
	}

	$thumb_html  = $product->get_image( 'woocommerce_thumbnail', array( 'class' => 'dcart-item__img', 'loading' => 'lazy' ) );
	$badge       = dejoiy_cart_xp_product_eco_badge( $product );
	$seller      = dejoiy_cart_xp_seller_info( $product );
	$meta        = wc_get_formatted_cart_item_data( $cart_item, true );
	$regular     = (float) $product->get_regular_price();
	$price       = (float) $product->get_price();
	$qty         = isset( $cart_item['quantity'] ) ? (int) $cart_item['quantity'] : 1;
	$disc        = ( $regular > $price && $price > 0 ) ? (int) round( ( ( $regular - $price ) / $regular ) * 100 ) : 0;
	$in_stock    = $product->is_in_stock();
	$remove_url  = wc_get_cart_remove_url( $cart_item_key );
	$product_url = $product->get_permalink( $cart_item );

	$wishlist_url = $product_url;
	if ( function_exists( 'etheme_wishlist_page_url' ) ) {
		$wishlist_url = etheme_wishlist_page_url();
	}

	ob_start();
	?>
	<div class="dcart-item" data-dcart-item data-cart-key="<?php echo esc_attr( $cart_item_key ); ?>">
		<!-- Thumbnail -->
		<div class="dcart-item__media">
			<a href="<?php echo esc_url( $product_url ); ?>" class="dcart-item__img-link">
				<?php echo $thumb_html; // phpcs:ignore ?>
			</a>
		</div>

		<!-- Details & Meta -->
		<div class="dcart-item__body">
			<div class="dcart-item__badge-row">
				<span class="dcart-item__badge" style="--dcart-eco:<?php echo esc_attr( $badge['color'] ); ?>"><?php echo esc_html( $badge['label'] ); ?></span>
				<?php if ( $disc > 0 ) : ?>
					<span class="dcart-item__disc-pill"><?php echo esc_html( (string) $disc ); ?>% OFF</span>
				<?php endif; ?>
			</div>
			
			<h3 class="dcart-item__title">
				<a href="<?php echo esc_url( $product_url ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
			</h3>

			<div class="dcart-item__meta-stack">
				<div class="dcart-item__stock">
					<?php if ( $in_stock ) : ?>
						<span class="dcart-stock-indicator dcart-stock--in"></span>
						<span class="dcart-stock-text dcart-stock-text--in"><?php esc_html_e( 'In stock', 'dejoiy' ); ?></span>
					<?php else : ?>
						<span class="dcart-stock-indicator dcart-stock--out"></span>
						<span class="dcart-stock-text dcart-stock-text--out"><?php esc_html_e( 'Out of stock', 'dejoiy' ); ?></span>
					<?php endif; ?>
				</div>
				<div class="dcart-item__delivery">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
					<span class="dcart-delivery-text"><?php esc_html_e( 'Eligible for FREE Delivery', 'dejoiy' ); ?></span>
				</div>
				<div class="dcart-item__seller">
					<span class="dcart-seller-label"><?php esc_html_e( 'Sold by:', 'dejoiy' ); ?></span>
					<a href="<?php echo esc_url( $seller['url'] ); ?>" class="dcart-seller-link"><?php echo esc_html( $seller['name'] ); ?></a>
					<span class="dcart-seller-badge"><?php esc_html_e( 'Verified', 'dejoiy' ); ?></span>
				</div>
				<?php if ( $meta ) : ?>
					<div class="dcart-item__variations"><?php echo wp_kses_post( $meta ); ?></div>
				<?php endif; ?>
			</div>

			<!-- Amazon Actions Row -->
			<div class="dcart-item__actions">
				<div class="dcart-item__stepper">
					<span class="dcart-stepper-label"><?php esc_html_e( 'Qty:', 'dejoiy' ); ?></span>
					<div class="dcart-stepper-box">
						<button type="button" class="dcart-stepper-btn dcart-stepper-minus" data-key="<?php echo esc_attr( $cart_item_key ); ?>" aria-label="<?php esc_attr_e( 'Decrease quantity', 'dejoiy' ); ?>">−</button>
						<span class="dcart-stepper-val" data-key="<?php echo esc_attr( $cart_item_key ); ?>"><?php echo esc_html( (string) $qty ); ?></span>
						<button type="button" class="dcart-stepper-btn dcart-stepper-plus" data-key="<?php echo esc_attr( $cart_item_key ); ?>" aria-label="<?php esc_attr_e( 'Increase quantity', 'dejoiy' ); ?>">+</button>
					</div>
				</div>

				<span class="dcart-action-sep" aria-hidden="true">|</span>

				<a href="<?php echo esc_url( $remove_url ); ?>" class="dcart-action-link dcart-action-link--remove" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>" title="<?php echo esc_attr( sprintf( __( 'Remove %s from cart', 'dejoiy' ), $product->get_name() ) ); ?>">
					<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
					<span><?php esc_html_e( 'Delete', 'dejoiy' ); ?></span>
				</a>

				<span class="dcart-action-sep" aria-hidden="true">|</span>

				<a href="<?php echo esc_url( $wishlist_url ); ?>" class="dcart-action-link dcart-action-link--save">
					<span><?php esc_html_e( 'Save for later', 'dejoiy' ); ?></span>
				</a>

				<span class="dcart-action-sep dcart-hide-mobile" aria-hidden="true">|</span>

				<a href="<?php echo esc_url( $wishlist_url ); ?>" class="dcart-action-link dcart-action-link--wishlist dcart-hide-mobile">
					<span><?php esc_html_e( 'Move to wishlist', 'dejoiy' ); ?></span>
				</a>
			</div>
		</div>

		<!-- Pricing Block -->
		<div class="dcart-item__pricing">
			<div class="dcart-item__current-price"><?php echo wp_kses_post( wc_price( $price ) ); ?></div>
			<?php if ( $regular > $price && $price > 0 ) : ?>
				<div class="dcart-item__mrp-row">
					<span class="dcart-mrp-label"><?php esc_html_e( 'M.R.P.:', 'dejoiy' ); ?></span>
					<del class="dcart-mrp-price"><?php echo wp_kses_post( wc_price( $regular ) ); ?></del>
				</div>
				<div class="dcart-item__savings"><?php echo esc_html( sprintf( __( 'Save %s', 'dejoiy' ), wc_price( ( $regular - $price ) * $qty ) ) ); ?></div>
			<?php endif; ?>
		</div>
	</div>
	<?php
	return (string) ob_get_clean();
}
add_filter( 'woocommerce_cart_item_name', 'dejoiy_cart_xp_cart_item_name', 18, 3 );

/**
 * Subtotal bar under cart items table.
 */
function dejoiy_cart_xp_items_subtotal_bar() {
	if ( ! dejoiy_cart_xp_is_active() || ! WC()->cart || WC()->cart->is_empty() ) {
		return;
	}
	$count    = (int) WC()->cart->get_cart_contents_count();
	$subtotal = WC()->cart->get_cart_subtotal();
	?>
	<div class="dcart-items-subtotal" id="dcart-items-subtotal">
		<span class="dcart-items-subtotal__label">
			<?php
			echo esc_html(
				sprintf(
					/* translators: %s: number of items */
					_n( 'Subtotal (%s item):', 'Subtotal (%s items):', $count, 'dejoiy' ),
					(string) $count
				)
			);
			?>
		</span>
		<strong class="dcart-items-subtotal__value"><?php echo wp_kses_post( $subtotal ); ?></strong>
	</div>
	<?php
}
add_action( 'woocommerce_after_cart_table', 'dejoiy_cart_xp_items_subtotal_bar', 2 );

/**
 * Recommended products rail.
 */
function dejoiy_cart_xp_recommendations() {
	if ( ! dejoiy_cart_xp_is_active() || ! WC()->cart || WC()->cart->is_empty() ) {
		return;
	}

	$exclude = array();
	foreach ( WC()->cart->get_cart() as $item ) {
		if ( ! empty( $item['product_id'] ) ) {
			$exclude[] = (int) $item['product_id'];
		}
	}

	$query = new WP_Query(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 8,
			'post__not_in'   => $exclude,
			'orderby'        => 'rand',
			'meta_query'     => array(
				array(
					'key'     => '_price',
					'value'   => 0,
					'compare' => '>',
					'type'    => 'NUMERIC',
				),
			),
		)
	);

	if ( ! $query->have_posts() ) {
		return;
	}
	?>
	<section class="dcart-reco" aria-labelledby="dcart-reco-title">
		<div class="dcart-reco__header">
			<h2 class="dcart-reco__title" id="dcart-reco-title"><?php esc_html_e( 'Customers Also Loved', 'dejoiy' ); ?></h2>
			<span class="dcart-reco__subtitle"><?php esc_html_e( 'Popular recommendations inspired by items in your cart', 'dejoiy' ); ?></span>
		</div>
		<div class="dcart-reco__grid">
			<?php
			while ( $query->have_posts() ) {
				$query->the_post();
				$product = wc_get_product( get_the_ID() );
				if ( ! $product ) {
					continue;
				}
				$badge = dejoiy_cart_xp_product_eco_badge( $product );
				$add_url = add_query_arg( 'add-to-cart', $product->get_id(), wc_get_cart_url() );
				?>
				<div class="dcart-reco__card">
					<a class="dcart-reco__card-link" href="<?php echo esc_url( $product->get_permalink() ); ?>">
						<?php if ( ! empty( $badge['label'] ) ) : ?>
							<span class="dcart-reco__badge" style="--dcart-eco:<?php echo esc_attr( $badge['color'] ); ?>"><?php echo esc_html( $badge['label'] ); ?></span>
						<?php endif; ?>
						<div class="dcart-reco__img-box">
							<?php echo $product->get_image( 'woocommerce_thumbnail', array( 'class' => 'dcart-reco__thumb' ) ); // phpcs:ignore ?>
						</div>
						<div class="dcart-reco__details">
							<h3 class="dcart-reco__name"><?php echo esc_html( $product->get_name() ); ?></h3>
							<div class="dcart-reco__rating">
								<span class="dcart-reco__stars">★★★★★</span>
								<span class="dcart-reco__score">4.8</span>
							</div>
							<div class="dcart-reco__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
						</div>
					</a>
					<div class="dcart-reco__btn-wrap">
						<a href="<?php echo esc_url( $add_url ); ?>" class="dcart-reco__add-btn">
							<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
							<span><?php esc_html_e( 'Add to Cart', 'dejoiy' ); ?></span>
						</a>
					</div>
				</div>
				<?php
			}
			wp_reset_postdata();
			?>
		</div>
	</section>
	<?php
}
add_action( 'woocommerce_after_cart_table', 'dejoiy_cart_xp_recommendations', 12 );

/**
 * Why buy with DEJOIY benefits strip.
 */
function dejoiy_cart_xp_benefits() {
	if ( ! dejoiy_cart_xp_is_active() || ! WC()->cart || WC()->cart->is_empty() ) {
		return;
	}
	$items = array(
		__( 'Verified Sellers', 'dejoiy' ),
		__( 'Buyer Protection', 'dejoiy' ),
		__( 'Easy Returns', 'dejoiy' ),
		__( 'Dedicated Support', 'dejoiy' ),
		__( 'Secure Payments', 'dejoiy' ),
	);
	?>
	<section class="dcart-benefits" aria-labelledby="dcart-benefits-title">
		<h2 class="dcart-benefits__title" id="dcart-benefits-title"><?php esc_html_e( 'Why Buy With DEJOIY', 'dejoiy' ); ?></h2>
		<ul class="dcart-benefits__grid">
			<?php foreach ( $items as $item ) : ?>
				<li class="dcart-benefits__item"><span aria-hidden="true">✓</span> <?php echo esc_html( $item ); ?></li>
			<?php endforeach; ?>
		</ul>
	</section>
	<?php
}
add_action( 'woocommerce_after_cart_table', 'dejoiy_cart_xp_benefits', 14 );

/**
 * Emotional conversion block after proceed to checkout button.
 */
function dejoiy_cart_xp_conversion_block() {
	if ( ! dejoiy_cart_xp_is_active() ) {
		return;
	}
	?>
	<div class="dcart-convert" data-dcart-convert>
		<div class="dcart-convert__security">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
			<span><?php esc_html_e( 'DEJOIY 100% Buyer Protection', 'dejoiy' ); ?></span>
		</div>
		<ul class="dcart-convert__list">
			<li><span class="dcart-chk">✓</span> <?php esc_html_e( '256-bit Encrypted Checkout', 'dejoiy' ); ?></li>
			<li><span class="dcart-chk">✓</span> <?php esc_html_e( 'Fast Contactless Delivery', 'dejoiy' ); ?></li>
			<li><span class="dcart-chk">✓</span> <?php esc_html_e( '7 Days Replacement / Return', 'dejoiy' ); ?></li>
			<li><span class="dcart-chk">✓</span> <?php esc_html_e( '24/7 Dedicated Support', 'dejoiy' ); ?></li>
		</ul>
	</div>
	<?php
}
add_action( 'woocommerce_proceed_to_checkout', 'dejoiy_cart_xp_conversion_block', 20 );

/**
 * Render mobile floating sticky checkout bar in wp_footer.
 */
function dejoiy_cart_xp_mobile_bar() {
	if ( ! dejoiy_cart_xp_is_active() || ! WC()->cart || WC()->cart->is_empty() ) {
		return;
	}
	$count = (int) WC()->cart->get_cart_contents_count();
	$total = WC()->cart->get_total();
	?>
	<div class="dcart-mobile-bar" data-dcart-mobile-bar>
		<div class="dcart-mobile-bar__left">
			<span class="dcart-mobile-bar__label"><?php esc_html_e( 'Total:', 'dejoiy' ); ?></span>
			<strong class="dcart-mobile-bar__total" data-dcart-mobile-total><?php echo wp_kses_post( $total ); ?></strong>
		</div>
		<a class="dcart-mobile-bar__cta" href="<?php echo esc_url( dejoiy_cart_xp_checkout_url() ); ?>">
			<span><?php echo esc_html( sprintf( __( 'Proceed to Buy (%d)', 'dejoiy' ), $count ) ); ?></span>
			<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
		</a>
	</div>
	<?php
}
add_action( 'wp_footer', 'dejoiy_cart_xp_mobile_bar', 25 );

/**
 * Premium empty cart.
 */
function dejoiy_cart_xp_empty_cart() {
	if ( ! dejoiy_cart_xp_is_active() ) {
		return;
	}
	$links = array(
		array( 'label' => __( 'Explore Marketplace', 'dejoiy' ), 'url' => dejoiy_cart_xp_shop_url() ),
		array( 'label' => __( 'Visit Studio', 'dejoiy' ), 'url' => home_url( '/dejoiy-custom-studio/' ) ),
		array( 'label' => __( 'Browse Refurbished', 'dejoiy' ), 'url' => home_url( '/dejoiy-refurbished/' ) ),
		array( 'label' => __( 'Discover Nexus', 'dejoiy' ), 'url' => home_url( '/dejoiy-library/' ) ),
		array( 'label' => __( 'Explore Services', 'dejoiy' ), 'url' => home_url( '/dejoiy-services/' ) ),
	);
	?>
	<div class="dcart-empty">
		<div class="dcart-empty__art" aria-hidden="true">🛍️</div>
		<h2 class="dcart-empty__title"><?php esc_html_e( 'Your Cart Awaits Great Things', 'dejoiy' ); ?></h2>
		<p class="dcart-empty__sub"><?php esc_html_e( 'Discover products across the DEJOIY ecosystem — one account, one cart.', 'dejoiy' ); ?></p>
		<div class="dcart-empty__actions">
			<?php foreach ( $links as $link ) : ?>
				<a class="dcart-empty__btn" href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $link['label'] ); ?></a>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}
add_action( 'woocommerce_cart_is_empty', 'dejoiy_cart_xp_empty_cart', 5 );

/**
 * Hide default empty cart message when our empty UI shows.
 */
function dejoiy_cart_xp_empty_hide_default() {
	if ( ! dejoiy_cart_xp_is_active() ) {
		return;
	}
	echo '<style>body.dejoiy-cart-xp .cart-empty.empty-cart-block{display:none!important;}</style>';
}
add_action( 'wp_head', 'dejoiy_cart_xp_empty_hide_default', 4 );
