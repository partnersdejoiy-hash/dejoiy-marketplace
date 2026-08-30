<?php
/**
 * DEJOIY Checkout Experience — premium ecosystem checkout (WooCommerce /checkout).
 *
 * Additive UI layer. Does not replace WooCommerce templates or payment flow.
 * Disable: define( 'DEJOIY_CHECKOUT_XP_DISABLED', true );
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'DEJOIY_CHECKOUT_XP_VERSION' ) ) {
	define( 'DEJOIY_CHECKOUT_XP_VERSION', '1.0.0' );
}

/**
 * @return bool
 */
function dejoiy_checkout_xp_enabled() {
	if ( defined( 'DEJOIY_CHECKOUT_XP_DISABLED' ) && DEJOIY_CHECKOUT_XP_DISABLED ) {
		return false;
	}
	if ( ! function_exists( 'dejoiy_evolution_is_enabled' ) || ! dejoiy_evolution_is_enabled() ) {
		return false;
	}
	if ( ! class_exists( 'WooCommerce' ) ) {
		return false;
	}
	return (bool) apply_filters( 'dejoiy_checkout_xp_enabled', true );
}

/**
 * Main marketplace checkout (not Nexus / thank-you).
 *
 * @return bool
 */
function dejoiy_checkout_xp_is_checkout_page() {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return false;
	}
	if ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'order-received' ) ) {
		return false;
	}
	$uri = strtolower( (string) wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) );
	if ( '' !== $uri && false !== strpos( $uri, '/nexus/checkout' ) ) {
		return false;
	}
	if ( function_exists( 'dejoiy_library_use_checkout_template' ) && dejoiy_library_use_checkout_template() ) {
		return false;
	}
	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		return true;
	}
	if ( function_exists( 'wc_get_page_id' ) ) {
		$checkout_id = (int) wc_get_page_id( 'checkout' );
		if ( $checkout_id > 0 && is_page( $checkout_id ) ) {
			return true;
		}
	}
	if ( is_page() ) {
		$slug = (string) get_post_field( 'post_name', get_queried_object_id() );
		if ( 'checkout' === $slug ) {
			return true;
		}
	}
	return '' !== $uri && (bool) preg_match( '#/checkout(/|\?|#|$)#', $uri );
}

/**
 * @return bool
 */
function dejoiy_checkout_xp_is_active() {
	return dejoiy_checkout_xp_enabled() && dejoiy_checkout_xp_is_checkout_page();
}

/**
 * @return string
 */
function dejoiy_checkout_xp_cart_url() {
	return function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );
}

/**
 * @return string
 */
function dejoiy_checkout_xp_shop_url() {
	return function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
}

/**
 * Ecosystem mini-badges data.
 *
 * @return array<int, array<string, string>>
 */
function dejoiy_checkout_xp_ecosystems() {
	$home = home_url( '/' );
	return array(
		array( 'label' => __( 'Marketplace', 'dejoiy' ), 'url' => dejoiy_checkout_xp_shop_url(), 'color' => '#7c3aed' ),
		array( 'label' => __( 'Studio', 'dejoiy' ), 'url' => home_url( '/dejoiy-custom-studio/' ), 'color' => '#ec4899' ),
		array( 'label' => __( 'Nexus', 'dejoiy' ), 'url' => home_url( '/dejoiy-library/' ), 'color' => '#06b6d4' ),
		array( 'label' => __( 'Services', 'dejoiy' ), 'url' => home_url( '/dejoiy-services/' ), 'color' => '#ea580c' ),
		array( 'label' => __( 'Refurbished', 'dejoiy' ), 'url' => home_url( '/dejoiy-refurbished/' ), 'color' => '#0d9488' ),
		array( 'label' => __( 'Quick', 'dejoiy' ), 'url' => home_url( '/dejoiy-quick-mart/' ), 'color' => '#facc15' ),
	);
}

/**
 * @param array<int, string> $classes Classes.
 * @return array<int, string>
 */
function dejoiy_checkout_xp_body_class( $classes ) {
	if ( dejoiy_checkout_xp_is_active() ) {
		$classes[] = 'dejoiy-checkout-xp';
		$classes[] = 'woocommerce-checkout';
	}
	return $classes;
}
add_filter( 'body_class', 'dejoiy_checkout_xp_body_class', 24 );

/**
 * Enqueue assets.
 */
function dejoiy_checkout_xp_assets() {
	if ( ! dejoiy_checkout_xp_is_active() ) {
		return;
	}
	$dir = get_stylesheet_directory();
	$uri = get_stylesheet_directory_uri();
	$css = $dir . '/dejoiy-checkout-experience.css';
	$js  = $dir . '/dejoiy-checkout-experience.js';
	$ver = DEJOIY_CHECKOUT_XP_VERSION;
	if ( is_readable( $css ) ) {
		wp_enqueue_style(
			'dejoiy-checkout-experience',
			$uri . '/dejoiy-checkout-experience.css',
			array(),
			$ver . '.' . (string) filemtime( $css )
		);
	}
	if ( is_readable( $js ) ) {
		wp_enqueue_script(
			'dejoiy-checkout-experience',
			$uri . '/dejoiy-checkout-experience.js',
			array( 'jquery' ),
			$ver . '.' . (string) filemtime( $js ),
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'dejoiy_checkout_xp_assets', 1015 );

/**
 * Hide legacy checkout chrome.
 */
function dejoiy_checkout_xp_hide_legacy() {
	if ( ! dejoiy_checkout_xp_is_active() ) {
		return;
	}
	echo '<style id="dejoiy-checkout-xp-guard">';
	echo 'body.dejoiy-checkout-xp .page-title,body.dejoiy-checkout-xp .woocommerce-breadcrumb,body.dejoiy-checkout-xp .cart-checkout-nav{display:none!important;}';
	echo '</style>';
}
add_action( 'wp_head', 'dejoiy_checkout_xp_hide_legacy', 3 );

/**
 * Hero + progress + express checkout shell.
 */
function dejoiy_checkout_xp_render_hero() {
	if ( ! dejoiy_checkout_xp_is_active() ) {
		return;
	}
	$cart_url = dejoiy_checkout_xp_cart_url();
	?>
	<div class="dcx-shell" id="dejoiy-checkout-xp">
		<section class="dcx-hero" aria-labelledby="dcx-hero-title">
			<div class="dcx-hero__glow" aria-hidden="true"></div>
			<div class="dcx-hero__inner">
				<p class="dcx-hero__kicker"><?php esc_html_e( 'DEJOIY Secure Checkout', 'dejoiy' ); ?></p>
				<h1 class="dcx-hero__title" id="dcx-hero-title"><?php esc_html_e( "You're One Step Away From Joy", 'dejoiy' ); ?></h1>
				<p class="dcx-hero__sub"><?php esc_html_e( 'Protected payments, trusted delivery, and buyer-first support.', 'dejoiy' ); ?></p>
				<ul class="dcx-trust-row" aria-label="<?php esc_attr_e( 'Trust indicators', 'dejoiy' ); ?>">
					<li><span aria-hidden="true">✓</span> <?php esc_html_e( 'Secure Checkout', 'dejoiy' ); ?></li>
					<li><span aria-hidden="true">✓</span> <?php esc_html_e( 'Buyer Protection', 'dejoiy' ); ?></li>
					<li><span aria-hidden="true">✓</span> <?php esc_html_e( 'Verified Sellers', 'dejoiy' ); ?></li>
					<li><span aria-hidden="true">✓</span> <?php esc_html_e( 'Fast Support', 'dejoiy' ); ?></li>
				</ul>
			</div>
		</section>

		<nav class="dcx-progress" aria-label="<?php esc_attr_e( 'Checkout progress', 'dejoiy' ); ?>">
			<ol class="dcx-progress__track">
				<li class="dcx-progress__step is-done">
					<a href="<?php echo esc_url( $cart_url ); ?>" class="dcx-progress__link">
						<span class="dcx-progress__dot"></span>
						<span class="dcx-progress__label"><?php esc_html_e( 'Cart', 'dejoiy' ); ?></span>
					</a>
				</li>
				<li class="dcx-progress__step is-active" aria-current="step">
					<span class="dcx-progress__dot"></span>
					<span class="dcx-progress__label"><?php esc_html_e( 'Checkout', 'dejoiy' ); ?></span>
				</li>
				<li class="dcx-progress__step">
					<span class="dcx-progress__dot"></span>
					<span class="dcx-progress__label"><?php esc_html_e( 'Payment', 'dejoiy' ); ?></span>
				</li>
				<li class="dcx-progress__step">
					<span class="dcx-progress__dot"></span>
					<span class="dcx-progress__label"><?php esc_html_e( 'Confirmation', 'dejoiy' ); ?></span>
				</li>
			</ol>
			<div class="dcx-progress__bar" aria-hidden="true"><span class="dcx-progress__fill"></span></div>
		</nav>

		<section class="dcx-express" aria-labelledby="dcx-express-title">
			<h2 class="dcx-express__title" id="dcx-express-title"><?php esc_html_e( 'Quick Checkout', 'dejoiy' ); ?></h2>
			<div class="dcx-express__grid">
				<button type="button" class="dcx-express__btn" disabled title="<?php esc_attr_e( 'Coming soon', 'dejoiy' ); ?>">
					<span class="dcx-express__icon">G</span>
					<span><?php esc_html_e( 'Google Pay', 'dejoiy' ); ?></span>
				</button>
				<button type="button" class="dcx-express__btn" disabled title="<?php esc_attr_e( 'Coming soon', 'dejoiy' ); ?>">
					<span class="dcx-express__icon">Pe</span>
					<span><?php esc_html_e( 'PhonePe', 'dejoiy' ); ?></span>
				</button>
				<button type="button" class="dcx-express__btn" disabled title="<?php esc_attr_e( 'Coming soon', 'dejoiy' ); ?>">
					<span class="dcx-express__icon">UPI</span>
					<span><?php esc_html_e( 'UPI', 'dejoiy' ); ?></span>
				</button>
				<button type="button" class="dcx-express__btn" disabled title="<?php esc_attr_e( 'Coming soon', 'dejoiy' ); ?>">
					<span class="dcx-express__icon">Pt</span>
					<span><?php esc_html_e( 'Paytm', 'dejoiy' ); ?></span>
				</button>
				<button type="button" class="dcx-express__btn" disabled title="<?php esc_attr_e( 'Coming soon', 'dejoiy' ); ?>">
					<span class="dcx-express__icon"></span>
					<span><?php esc_html_e( 'Apple Pay', 'dejoiy' ); ?></span>
				</button>
			</div>
			<p class="dcx-express__note"><?php esc_html_e( 'Express wallets connect soon — use the secure form below to complete your order today.', 'dejoiy' ); ?></p>
		</section>

	<?php
}
add_action( 'woocommerce_before_checkout_form', 'dejoiy_checkout_xp_render_hero', 4 );

/**
 * Open two-column layout inside checkout form.
 */
function dejoiy_checkout_xp_layout_open() {
	if ( ! dejoiy_checkout_xp_is_active() ) {
		return;
	}
	echo '<div class="dcx-layout" data-dcx-layout><div class="dcx-layout__main" data-dcx-main>';
	echo '<div class="dcx-card dcx-card--billing"><h2 class="dcx-card__title">' . esc_html__( 'Contact &amp; Delivery', 'dejoiy' ) . '</h2>';
}
add_action( 'woocommerce_checkout_before_customer_details', 'dejoiy_checkout_xp_layout_open', 4 );

/**
 * Close details card after customer fields.
 */
function dejoiy_checkout_xp_details_close() {
	if ( ! dejoiy_checkout_xp_is_active() ) {
		return;
	}
	echo '</div>';
}
add_action( 'woocommerce_checkout_after_customer_details', 'dejoiy_checkout_xp_details_close', 96 );

/**
 * Close main column, open summary column (still inside checkout form).
 */
function dejoiy_checkout_xp_open_summary() {
	if ( ! dejoiy_checkout_xp_is_active() ) {
		return;
	}
	echo '</div><aside class="dcx-layout__summary" data-dcx-summary aria-label="' . esc_attr__( 'Order summary', 'dejoiy' ) . '">';
	echo '<div class="dcx-summary-card" data-dcx-summary-card>';
	echo '<h2 class="dcx-summary-card__title">' . esc_html__( 'Order Summary', 'dejoiy' ) . '</h2>';
}
add_action( 'woocommerce_checkout_before_order_review', 'dejoiy_checkout_xp_open_summary', 4 );

/**
 * Trust block inside summary.
 */
function dejoiy_checkout_xp_trust_block() {
	if ( ! dejoiy_checkout_xp_is_active() ) {
		return;
	}
	?>
	<div class="dcx-trust-grid" aria-labelledby="dcx-trust-title">
		<h3 class="dcx-trust-grid__title" id="dcx-trust-title"><?php esc_html_e( 'Why Customers Trust DEJOIY', 'dejoiy' ); ?></h3>
		<ul class="dcx-trust-grid__list">
			<li class="dcx-trust-grid__item"><span aria-hidden="true">✓</span> <?php esc_html_e( 'Secure Payments', 'dejoiy' ); ?></li>
			<li class="dcx-trust-grid__item"><span aria-hidden="true">✓</span> <?php esc_html_e( 'Buyer Protection', 'dejoiy' ); ?></li>
			<li class="dcx-trust-grid__item"><span aria-hidden="true">✓</span> <?php esc_html_e( 'Fast Support', 'dejoiy' ); ?></li>
			<li class="dcx-trust-grid__item"><span aria-hidden="true">✓</span> <?php esc_html_e( 'Easy Returns', 'dejoiy' ); ?></li>
		</ul>
	</div>
	<?php
}
add_action( 'woocommerce_checkout_after_order_review', 'dejoiy_checkout_xp_trust_block', 8 );

/**
 * Close summary + layout + ecosystem reminder.
 */
function dejoiy_checkout_xp_close_shell() {
	if ( ! dejoiy_checkout_xp_is_active() ) {
		return;
	}
	echo '</div></aside></div><!-- .dcx-layout -->';

	$ecos = dejoiy_checkout_xp_ecosystems();
	?>
	<section class="dcx-eco" aria-labelledby="dcx-eco-title">
		<p class="dcx-eco__kicker" id="dcx-eco-title"><?php esc_html_e( 'You are shopping within the DEJOIY ecosystem', 'dejoiy' ); ?></p>
		<div class="dcx-eco__badges">
			<?php foreach ( $ecos as $eco ) : ?>
				<a class="dcx-eco__badge" href="<?php echo esc_url( $eco['url'] ); ?>" style="--dcx-eco:<?php echo esc_attr( $eco['color'] ); ?>">
					<?php echo esc_html( $eco['label'] ); ?>
				</a>
			<?php endforeach; ?>
		</div>
		<p class="dcx-eco__tagline"><?php esc_html_e( 'One account. One cart. One ecosystem.', 'dejoiy' ); ?></p>
	</section>
	</div>

	<div class="dcx-mobile-bar" data-dcx-mobile-bar hidden>
		<div class="dcx-mobile-bar__total" data-dcx-mobile-total></div>
		<button type="button" class="dcx-mobile-bar__toggle" data-dcx-drawer-open aria-expanded="false">
			<?php esc_html_e( 'View order', 'dejoiy' ); ?>
		</button>
	</div>
	<div class="dcx-drawer" data-dcx-drawer hidden aria-hidden="true">
		<div class="dcx-drawer__backdrop" data-dcx-drawer-close></div>
		<div class="dcx-drawer__panel" role="dialog" aria-label="<?php esc_attr_e( 'Order summary', 'dejoiy' ); ?>">
			<button type="button" class="dcx-drawer__close" data-dcx-drawer-close aria-label="<?php esc_attr_e( 'Close', 'dejoiy' ); ?>">×</button>
			<div class="dcx-drawer__body" data-dcx-drawer-body></div>
		</div>
	</div>
	<?php
}
add_action( 'woocommerce_after_checkout_form', 'dejoiy_checkout_xp_close_shell', 96 );

/**
 * Premium place-order button.
 *
 * @param string $html Button HTML.
 * @return string
 */
function dejoiy_checkout_xp_order_button( $html ) {
	if ( ! dejoiy_checkout_xp_is_active() ) {
		return $html;
	}
	$html = str_replace( 'class="button alt', 'class="button alt dcx-place-order', $html );
	return $html;
}
add_filter( 'woocommerce_order_button_html', 'dejoiy_checkout_xp_order_button', 20 );

/**
 * Add subtext after place order button.
 */
function dejoiy_checkout_xp_place_order_note() {
	if ( ! dejoiy_checkout_xp_is_active() ) {
		return;
	}
	echo '<p class="dcx-place-order-note">' . esc_html__( 'Protected by DEJOIY Buyer Protection', 'dejoiy' ) . '</p>';
}
add_action( 'woocommerce_review_order_after_submit', 'dejoiy_checkout_xp_place_order_note', 12 );

/**
 * Enhance cart line item name with product image (desktop card layout).
 *
 * @param string $name        Name HTML.
 * @param array  $cart_item   Cart item.
 * @param string $cart_item_key Key.
 * @return string
 */
function dejoiy_checkout_xp_cart_item_name( $name, $cart_item, $cart_item_key ) {
	if ( ! dejoiy_checkout_xp_is_active() || ! is_checkout() ) {
		return $name;
	}
	$product = isset( $cart_item['data'] ) ? $cart_item['data'] : null;
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return $name;
	}

	$thumb = $product->get_image(
		'woocommerce_thumbnail',
		array(
			'class' => 'dcx-line__img',
			'alt'   => '',
		)
	);
	$meta  = wc_get_formatted_cart_item_data( $cart_item, true );
	$qty   = isset( $cart_item['quantity'] ) ? (int) $cart_item['quantity'] : 1;
	$price = WC()->cart ? WC()->cart->get_product_subtotal( $product, $qty ) : '';

	$remove = '';
	if ( is_checkout() && function_exists( 'wc_get_cart_remove_url' ) ) {
		$remove = sprintf(
			'<a href="%s" class="dcx-line__remove" aria-label="%s">&times;</a>',
			esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
			esc_attr__( 'Remove item', 'dejoiy' )
		);
	}

	$out  = '<div class="dcx-line">';
	$out .= '<div class="dcx-line__media">' . $thumb . '</div>';
	$out .= '<div class="dcx-line__body">';
	$out .= '<div class="dcx-line__title">' . wp_kses_post( $name ) . '</div>';
	if ( $meta ) {
		$out .= '<div class="dcx-line__meta">' . $meta . '</div>';
	}
	$out .= '<div class="dcx-line__qty">' . esc_html__( 'Qty', 'dejoiy' ) . ': ' . esc_html( (string) $qty ) . '</div>';
	$out .= '</div>';
	$out .= '<div class="dcx-line__side">';
	$out .= '<div class="dcx-line__price">' . wp_kses_post( $price ) . '</div>';
	$out .= $remove;
	$out .= '</div>';
	$out .= '</div>';

	return $out;
}
add_filter( 'woocommerce_cart_item_name', 'dejoiy_checkout_xp_cart_item_name', 20, 3 );

/**
 * Add field wrapper classes for floating labels.
 *
 * @param array<string, array<string, mixed>> $fields Fields.
 * @return array<string, array<string, mixed>>
 */
function dejoiy_checkout_xp_checkout_fields( $fields ) {
	if ( ! dejoiy_checkout_xp_is_active() ) {
		return $fields;
	}
	foreach ( $fields as $group => &$group_fields ) {
		if ( ! is_array( $group_fields ) ) {
			continue;
		}
		foreach ( $group_fields as $key => &$field ) {
			if ( ! is_array( $field ) ) {
				continue;
			}
			$field['class'][]       = 'dcx-field';
			$field['input_class'][] = 'dcx-input';
			$field['label_class'][] = 'dcx-label';
		}
	}
	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'dejoiy_checkout_xp_checkout_fields', 20 );

/**
 * Custom order review heading (hide default).
 */
function dejoiy_checkout_xp_hide_order_heading() {
	if ( ! dejoiy_checkout_xp_is_active() ) {
		return;
	}
	echo '<style>.dejoiy-checkout-xp #order_review_heading{display:none!important;}</style>';
}
add_action( 'wp_head', 'dejoiy_checkout_xp_hide_order_heading', 4 );
