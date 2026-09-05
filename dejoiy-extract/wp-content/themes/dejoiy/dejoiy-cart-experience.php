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
	define( 'DEJOIY_CART_XP_VERSION', '1.0.0' );
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
					'proceed' => __( 'Proceed Securely', 'dejoiy' ),
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
 * Single visible <h1> on the legacy cart page.
 *
 * The DEJOIY Cart experience already renders its own hero title; this fallback
 * covers every other cart layout so the page always has exactly one heading.
 */
function dejoiy_cart_xp_page_heading() {
	if ( dejoiy_cart_xp_is_active() ) {
		return;
	}
	if ( ! function_exists( 'is_cart' ) || ! is_cart() || is_wc_endpoint_url() ) {
		return;
	}
	?>
	<h1 class="dcart-page-title"><?php esc_html_e( 'Your Cart', 'dejoiy' ); ?></h1>
	<?php
}
add_action( 'woocommerce_before_cart', 'dejoiy_cart_xp_page_heading', 3 );

/**
 * Proceed to checkout button text.
 *
 * @param string $translated Translated.
 * @param string $text       Original.
 * @param string $domain     Domain.
 * @return string
 */
function dejoiy_cart_xp_gettext( $translated, $text, $domain ) {
	if ( ! dejoiy_cart_xp_is_active() ) {
		return $translated;
	}
	if ( 'woocommerce' === $domain && 'Proceed to checkout' === $text ) {
		return __( 'Proceed Securely', 'dejoiy' );
	}
	if ( 'woocommerce' === $domain && 'Update cart' === $text ) {
		return __( 'Update bag', 'dejoiy' );
	}
	return $translated;
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
 * Open cart shell + hero.
 */
function dejoiy_cart_xp_open_shell() {
	if ( ! dejoiy_cart_xp_is_active() ) {
		return;
	}
	$checkout = dejoiy_cart_xp_checkout_url();
	?>
	<div class="dcart-shell" id="dejoiy-cart-xp">
		<section class="dcart-hero" aria-labelledby="dcart-hero-title">
			<div class="dcart-hero__glow" aria-hidden="true"></div>
			<div class="dcart-hero__inner">
				<p class="dcart-hero__kicker"><?php esc_html_e( 'DEJOIY Cart', 'dejoiy' ); ?></p>
				<h1 class="dcart-hero__title" id="dcart-hero-title"><?php esc_html_e( 'Almost Yours.', 'dejoiy' ); ?></h1>
				<p class="dcart-hero__sub"><?php esc_html_e( 'Review your selections and complete your order with confidence.', 'dejoiy' ); ?></p>
				<ul class="dcart-trust-row">
					<li><span aria-hidden="true">✓</span> <?php esc_html_e( 'Secure Payments', 'dejoiy' ); ?></li>
					<li><span aria-hidden="true">✓</span> <?php esc_html_e( 'Fast Delivery', 'dejoiy' ); ?></li>
					<li><span aria-hidden="true">✓</span> <?php esc_html_e( 'Buyer Protection', 'dejoiy' ); ?></li>
					<li><span aria-hidden="true">✓</span> <?php esc_html_e( 'Easy Returns', 'dejoiy' ); ?></li>
				</ul>
			</div>
		</section>

		<nav class="dcart-progress" aria-label="<?php esc_attr_e( 'Checkout progress', 'dejoiy' ); ?>">
			<ol class="dcart-progress__track">
				<li class="dcart-progress__step is-active" aria-current="step">
					<span class="dcart-progress__dot"></span>
					<span class="dcart-progress__label"><?php esc_html_e( 'Cart', 'dejoiy' ); ?></span>
				</li>
				<li class="dcart-progress__step">
					<a href="<?php echo esc_url( $checkout ); ?>" class="dcart-progress__link">
						<span class="dcart-progress__dot"></span>
						<span class="dcart-progress__label"><?php esc_html_e( 'Checkout', 'dejoiy' ); ?></span>
					</a>
				</li>
				<li class="dcart-progress__step">
					<span class="dcart-progress__dot"></span>
					<span class="dcart-progress__label"><?php esc_html_e( 'Payment', 'dejoiy' ); ?></span>
				</li>
				<li class="dcart-progress__step">
					<span class="dcart-progress__dot"></span>
					<span class="dcart-progress__label"><?php esc_html_e( 'Delivered', 'dejoiy' ); ?></span>
				</li>
			</ol>
			<div class="dcart-progress__bar" aria-hidden="true"><span class="dcart-progress__fill"></span></div>
		</nav>

		<div class="dcart-layout" data-dcart-layout>
			<div class="dcart-layout__main" data-dcart-main>
	<?php
}
add_action( 'woocommerce_before_cart', 'dejoiy_cart_xp_open_shell', 4 );

/**
 * Smart savings + free shipping bar.
 */
function dejoiy_cart_xp_savings_block() {
	if ( ! dejoiy_cart_xp_is_active() || ! WC()->cart || WC()->cart->is_empty() ) {
		return;
	}
	$saved    = dejoiy_cart_xp_cart_savings();
	$ship     = dejoiy_cart_xp_shipping_progress();
	$currency = function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '₹';
	?>
	<div class="dcart-savings" data-dcart-savings>
		<?php if ( $saved > 0 ) : ?>
			<p class="dcart-savings__headline">
				<?php
				echo esc_html(
					sprintf(
						/* translators: %s: formatted savings amount */
						__( 'You saved %s%s today', 'dejoiy' ),
						$currency,
						wc_format_decimal( $saved, wc_get_price_decimals() )
					)
				);
				?>
			</p>
		<?php endif; ?>
		<div class="dcart-savings__ship">
			<div class="dcart-savings__ship-top">
				<span><?php esc_html_e( 'Unlock free shipping', 'dejoiy' ); ?></span>
				<?php if ( $ship['remaining'] > 0 ) : ?>
					<strong>
						<?php
						echo esc_html(
							sprintf(
								/* translators: %s: amount remaining */
								__( 'Only %s%s more needed', 'dejoiy' ),
								$currency,
								wc_format_decimal( $ship['remaining'], wc_get_price_decimals() )
							)
						);
						?>
					</strong>
				<?php else : ?>
					<strong class="dcart-savings__unlocked"><?php esc_html_e( 'Free shipping unlocked', 'dejoiy' ); ?></strong>
				<?php endif; ?>
			</div>
			<div class="dcart-savings__bar"><span style="width:<?php echo esc_attr( (string) $ship['percent'] ); ?>%"></span></div>
		</div>
	</div>
	<?php
}
add_action( 'woocommerce_before_cart_table', 'dejoiy_cart_xp_savings_block', 8 );

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
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
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
		<h2 class="dcart-reco__title" id="dcart-reco-title"><?php esc_html_e( 'Customers Also Loved', 'dejoiy' ); ?></h2>
		<div class="dcart-reco__track" tabindex="0">
			<?php
			while ( $query->have_posts() ) {
				$query->the_post();
				$product = wc_get_product( get_the_ID() );
				if ( ! $product ) {
					continue;
				}
				$badge = dejoiy_cart_xp_product_eco_badge( $product );
				?>
				<a class="dcart-reco__card" href="<?php echo esc_url( $product->get_permalink() ); ?>">
					<span class="dcart-reco__badge" style="--dcart-eco:<?php echo esc_attr( $badge['color'] ); ?>"><?php echo esc_html( $badge['label'] ); ?></span>
					<span class="dcart-reco__img"><?php echo $product->get_image( 'woocommerce_thumbnail' ); // phpcs:ignore ?></span>
					<span class="dcart-reco__name"><?php echo esc_html( $product->get_name() ); ?></span>
					<span class="dcart-reco__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
				</a>
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
 * Why buy with DEJOIY.
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
 * Close main column; open summary aside before collaterals.
 */
function dejoiy_cart_xp_open_summary() {
	if ( ! dejoiy_cart_xp_is_active() || ! WC()->cart || WC()->cart->is_empty() ) {
		return;
	}
	echo '</div><aside class="dcart-layout__summary" data-dcart-summary aria-label="' . esc_attr__( 'Order summary', 'dejoiy' ) . '">';
	echo '<div class="dcart-summary-card">';
	echo '<h2 class="dcart-summary-card__title">' . esc_html__( 'Order Summary', 'dejoiy' ) . '</h2>';
}
add_action( 'woocommerce_after_cart', 'dejoiy_cart_xp_open_summary', 4 );

/**
 * Emotional conversion block before checkout CTA.
 */
function dejoiy_cart_xp_conversion_block() {
	if ( ! dejoiy_cart_xp_is_active() ) {
		return;
	}
	?>
	<div class="dcart-convert" data-dcart-convert>
		<p class="dcart-convert__lead"><?php esc_html_e( "You're one step away from receiving your order.", 'dejoiy' ); ?></p>
		<ul class="dcart-convert__list">
			<li><?php esc_html_e( 'Secure Checkout', 'dejoiy' ); ?></li>
			<li><?php esc_html_e( 'Encrypted Payments', 'dejoiy' ); ?></li>
			<li><?php esc_html_e( 'Dedicated Support', 'dejoiy' ); ?></li>
			<li><?php esc_html_e( 'Trusted Delivery', 'dejoiy' ); ?></li>
		</ul>
		<p class="dcart-convert__note"><?php esc_html_e( 'Protected by DEJOIY Buyer Protection', 'dejoiy' ); ?></p>
	</div>
	<?php
}
add_action( 'woocommerce_proceed_to_checkout', 'dejoiy_cart_xp_conversion_block', 4 );

/**
 * Close summary shell.
 */
function dejoiy_cart_xp_close_shell() {
	if ( ! dejoiy_cart_xp_is_active() ) {
		return;
	}
	if ( WC()->cart && ! WC()->cart->is_empty() ) {
		echo '</div></aside></div>';
	} else {
		echo '</div></div>';
	}
	echo '</div>';

	if ( WC()->cart && ! WC()->cart->is_empty() ) {
		?>
		<div class="dcart-mobile-bar" data-dcart-mobile-bar>
			<div class="dcart-mobile-bar__total" data-dcart-mobile-total></div>
			<a class="dcart-mobile-bar__cta" href="<?php echo esc_url( dejoiy_cart_xp_checkout_url() ); ?>"><?php esc_html_e( 'Proceed Securely', 'dejoiy' ); ?></a>
		</div>
		<?php
	}
}
add_action( 'woocommerce_after_cart', 'dejoiy_cart_xp_close_shell', 99 );

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

	$thumb   = $product->get_image( 'woocommerce_thumbnail', array( 'class' => 'dcart-item__img' ) );
	$badge   = dejoiy_cart_xp_product_eco_badge( $product );
	$meta    = wc_get_formatted_cart_item_data( $cart_item, true );
	$regular = (float) $product->get_regular_price();
	$price   = (float) $product->get_price();
	$qty     = isset( $cart_item['quantity'] ) ? (int) $cart_item['quantity'] : 1;
	$disc    = ( $regular > $price && $price > 0 ) ? (int) round( ( ( $regular - $price ) / $regular ) * 100 ) : 0;

	$wishlist_url = $product->get_permalink();
	if ( function_exists( 'etheme_wishlist_page_url' ) ) {
		$wishlist_url = etheme_wishlist_page_url();
	}

	ob_start();
	?>
	<div class="dcart-item" data-dcart-item>
		<div class="dcart-item__media"><?php echo $thumb; // phpcs:ignore ?></div>
		<div class="dcart-item__body">
			<span class="dcart-item__badge" style="--dcart-eco:<?php echo esc_attr( $badge['color'] ); ?>"><?php echo esc_html( $badge['label'] ); ?></span>
			<div class="dcart-item__title"><?php echo wp_kses_post( $name ); ?></div>
			<?php if ( $meta ) : ?>
				<div class="dcart-item__meta"><?php echo $meta; // phpcs:ignore ?></div>
			<?php endif; ?>
			<div class="dcart-item__actions">
				<a href="<?php echo esc_url( $wishlist_url ); ?>" class="dcart-item__link"><?php esc_html_e( 'Save for later', 'dejoiy' ); ?></a>
				<span class="dcart-item__sep">·</span>
				<a href="<?php echo esc_url( $wishlist_url ); ?>" class="dcart-item__link"><?php esc_html_e( 'Move to wishlist', 'dejoiy' ); ?></a>
			</div>
		</div>
		<div class="dcart-item__side">
			<?php if ( $disc > 0 ) : ?>
				<span class="dcart-item__discount">-<?php echo esc_html( (string) $disc ); ?>%</span>
			<?php endif; ?>
			<?php if ( $regular > $price && $price > 0 ) : ?>
				<del class="dcart-item__was"><?php echo wp_kses_post( wc_price( $regular ) ); ?></del>
			<?php endif; ?>
			<div class="dcart-item__price" data-title="<?php esc_attr_e( 'Price', 'dejoiy' ); ?>"></div>
		</div>
	</div>
	<?php
	return (string) ob_get_clean();
}
add_filter( 'woocommerce_cart_item_name', 'dejoiy_cart_xp_cart_item_name', 18, 3 );
