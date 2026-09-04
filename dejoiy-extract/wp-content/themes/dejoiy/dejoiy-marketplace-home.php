<?php
/**
 * DEJOIY Marketplace Home — NexGen Amazon-level front page.
 *
 * Replaces the front-page body across ALL viewports (desktop, tablet, mobile).
 * - Desktop (>=1025px): calm marketplace editorial layout with hero, rails, grids.
 * - Tablet/Phone (<=1024px): app-style layout — bubbles, snap-scroll product rails,
 *   compact deal grid, sticky chrome already handled by the Global Header OS.
 *
 * White-label safe: emits zero WooCommerce class names or vendor chrome.
 * Disable: define( 'DEJOIY_MARKETPLACE_HOME_DISABLED', true )
 *          or set option dejoiy_marketplace_home_active to '0'.
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'DEJOIY_MARKETPLACE_HOME_VERSION' ) ) {
	define( 'DEJOIY_MARKETPLACE_HOME_VERSION', '1.0.0' );
}

/**
 * @return bool
 */
function dejoiy_marketplace_home_active() {
	if ( defined( 'DEJOIY_MARKETPLACE_HOME_DISABLED' ) && DEJOIY_MARKETPLACE_HOME_DISABLED ) {
		return false;
	}
	if ( is_admin() && ! wp_doing_ajax() ) {
		return false;
	}
	if ( function_exists( 'dejoiy_evolution_is_enabled' ) && ! dejoiy_evolution_is_enabled() ) {
		return false;
	}
	return '1' === get_option( 'dejoiy_marketplace_home_active', '1' );
}

/**
 * Render the Marketplace Home exactly once per request.
 *
 * Multiple outlets (dedicated template, the_content replacement, shortcode)
 * may each try to emit the module — this guard keeps exactly one instance.
 *
 * @return string
 */
function dejoiy_marketplace_home_once() {
	static $rendered = false;
	if ( $rendered ) {
		return '';
	}
	$rendered = true;
	return dejoiy_marketplace_home_html();
}

/**
 * Replace front page content (runs last so legacy home systems can be skipped).
 *
 * @param string $content Post content.
 * @return string
 */
function dejoiy_marketplace_home_replace_content( $content ) {
	if ( ! is_front_page() || is_admin() ) {
		return $content;
	}
	if ( ! dejoiy_marketplace_home_active() ) {
		return $content;
	}

	return dejoiy_marketplace_home_once();
}
add_filter( 'the_content', 'dejoiy_marketplace_home_replace_content', 10001 );
add_filter( 'elementor/frontend/the_content', 'dejoiy_marketplace_home_replace_content', 10001 );

/**
 * Let the Marketplace Home own the front page as a dedicated template so the
 * legacy home content filters (universe @9999 / mobile-home-v2 @10000) and
 * the theme's own template loop can never stack the Elementor page content
 * back in beneath the MPH sections.
 *
 * @param string $template Resolved template path.
 * @return string
 */
function dejoiy_marketplace_home_template( $template ) {
	if ( ! is_front_page() || ! dejoiy_marketplace_home_active() ) {
		return $template;
	}
	$ours = get_stylesheet_directory() . '/dejoiy-marketplace-home-template.php';
	return is_readable( $ours ) ? $ours : $template;
}
add_filter( 'template_include', 'dejoiy_marketplace_home_template', 100 );

/**
 * @param array<int, string> $classes Body classes.
 * @return array<int, string>
 */
function dejoiy_marketplace_home_body_class( $classes ) {
	if ( is_front_page() && dejoiy_marketplace_home_active() ) {
		$classes[] = 'dejoiy-marketplace-home';
		$classes[] = 'dejoiy-mph';
	}
	return $classes;
}
add_filter( 'body_class', 'dejoiy_marketplace_home_body_class', 26 );

/**
 * Prevent the legacy desktop marketplace header from bootstrapping on the
 * homepage (Global Header OS already owns the chrome here). Avoids the
 * double-header cascade on desktop.
 *
 * @param bool $standalone Whether dmh should replace theme chrome.
 * @return bool
 */
function dejoiy_marketplace_home_dmh_disable( $standalone ) {
	if ( is_front_page() && dejoiy_marketplace_home_active() ) {
		return false;
	}
	return $standalone;
}
add_filter( 'dejoiy_desktop_marketplace_should_bootstrap_standalone', 'dejoiy_marketplace_home_dmh_disable', 20 );

/**
 * Enqueue assets for the marketplace home.
 */
function dejoiy_marketplace_home_assets() {
	if ( ! is_front_page() || ! dejoiy_marketplace_home_active() ) {
		return;
	}
	$uri = get_stylesheet_directory_uri();
	$dir = get_stylesheet_directory();
	$css = $dir . '/dejoiy-marketplace-home.css';
	$js  = $dir . '/dejoiy-marketplace-home.js';

	if ( is_readable( $css ) ) {
		wp_enqueue_style(
			'dejoiy-marketplace-home',
			$uri . '/dejoiy-marketplace-home.css',
			array(),
			(string) filemtime( $css )
		);
	}
	if ( is_readable( $js ) ) {
		wp_enqueue_script(
			'dejoiy-marketplace-home',
			$uri . '/dejoiy-marketplace-home.js',
			array(),
			(string) filemtime( $js ),
			true
		);
		wp_localize_script(
			'dejoiy-marketplace-home',
			'dejoiyMph',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'dejoiy_mph' ),
				'cartUrl' => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'cart' ) : home_url( '/cart/' ),
				'i18n'    => array(
					'added'     => __( 'Added to cart ✓', 'dejoiy' ),
					'error'     => __( 'Could not add to cart', 'dejoiy' ),
					'viewCart'  => __( 'View Cart', 'dejoiy' ),
					'off'       => __( 'off', 'dejoiy' ),
					'freeDel'   => __( 'Free Delivery by', 'dejoiy' ),
				),
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'dejoiy_marketplace_home_assets', 1012 );

/* ---------------------------------------------------------------
   AJAX add-to-cart (white-label, updates header badges)
   --------------------------------------------------------------- */

function dejoiy_mph_ajax_add_to_cart() {
	check_ajax_referer( 'dejoiy_mph', 'nonce' );

	$pid = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;
	if ( ! $pid || ! function_exists( 'WC' ) || ! WC()->cart ) {
		wp_send_json_error( array( 'message' => 'invalid' ) );
	}

	$product = function_exists( 'wc_get_product' ) ? wc_get_product( $pid ) : null;
	if ( ! $product || 'publish' !== $product->get_status() ) {
		wp_send_json_error( array( 'message' => 'invalid-product' ) );
	}
	if ( $product->is_type( 'variable' ) || $product->is_type( 'grouped' ) ) {
		wp_send_json_error( array( 'message' => 'needs-variant', 'url' => get_permalink( $pid ) ) );
	}

	$added = WC()->cart->add_to_cart( $pid, 1 );
	if ( ! $added ) {
		wp_send_json_error( array( 'message' => 'could-not-add' ) );
	}

	$count = 0;
	foreach ( WC()->cart->get_cart() as $item ) {
		$count += (int) $item['quantity'];
	}

	wp_send_json_success(
		array(
			'count'   => $count,
			'added'   => $added,
			'cartUrl' => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'cart' ) : home_url( '/cart/' ),
		)
	);
}
add_action( 'wp_ajax_dejoiy_mph_add_to_cart', 'dejoiy_mph_ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_dejoiy_mph_add_to_cart', 'dejoiy_mph_ajax_add_to_cart' );

/* ---------------------------------------------------------------
   Data helpers
   --------------------------------------------------------------- */

/**
 * Ecosystem gateways (falls back to Universe gateways if present).
 *
 * @return array<int, array<string, string>>
 */
function dejoiy_mph_gateways() {
	if ( function_exists( 'dejoiy_universe_gateways' ) ) {
		$gw = dejoiy_universe_gateways();
		return is_array( $gw ) ? array_values( $gw ) : array();
	}
	$shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
	return array(
		array(
			'label'   => __( 'Marketplace', 'dejoiy' ),
			'tagline' => __( 'Shop everything', 'dejoiy' ),
			'verb'    => __( 'Shop', 'dejoiy' ),
			'url'     => $shop,
			'icon'    => '◆',
			'theme'   => 'market',
		),
		array(
			'label'   => __( 'Nexus', 'dejoiy' ),
			'tagline' => __( 'Books & courses', 'dejoiy' ),
			'verb'    => __( 'Learn', 'dejoiy' ),
			'url'     => home_url( '/dejoiy-library/?dejoiy_library=1' ),
			'icon'    => '✦',
			'theme'   => 'nexus',
		),
		array(
			'label'   => __( 'Custom Studio', 'dejoiy' ),
			'tagline' => __( 'Create your vision', 'dejoiy' ),
			'verb'    => __( 'Create', 'dejoiy' ),
			'url'     => home_url( '/dejoiy-custom-studio/' ),
			'icon'    => '✿',
			'theme'   => 'studio',
		),
		array(
			'label'   => __( 'QuickMart', 'dejoiy' ),
			'tagline' => __( 'Instant essentials', 'dejoiy' ),
			'verb'    => __( 'Grab', 'dejoiy' ),
			'url'     => home_url( '/dejoiy-quick-mart/' ),
			'icon'    => '⚡',
			'theme'   => 'quickmart',
		),
		array(
			'label'   => __( 'Refurbished', 'dejoiy' ),
			'tagline' => __( 'Certified pre-owned tech', 'dejoiy' ),
			'verb'    => __( 'Renew', 'dejoiy' ),
			'url'     => home_url( '/dejoiy-refurbished/' ),
			'icon'    => '◈',
			'theme'   => 'refurbished',
		),
		array(
			'label'   => __( 'Services', 'dejoiy' ),
			'tagline' => __( 'Book experts', 'dejoiy' ),
			'verb'    => __( 'Hire', 'dejoiy' ),
			'url'     => home_url( '/dejoiy-services/' ),
			'icon'    => '◎',
			'theme'   => 'services',
		),
	);
}

/**
 * Category tiles: curated fallback, real term URLs when available.
 *
 * @return array<int, array<string, string>>
 */
function dejoiy_mph_categories() {
	$list = array(
		array(
			'slug' => 'electronics',
			'name' => __( 'Electronics', 'dejoiy' ),
			'icon' => 'phone',
			'url'  => '',
			'bg'   => 'linear-gradient(135deg,#7c3aed,#a855f7)',
		),
		array(
			'slug' => 'fashion',
			'name' => __( 'Fashion', 'dejoiy' ),
			'icon' => 'dress',
			'url'  => '',
			'bg'   => 'linear-gradient(135deg,#ec4899,#f472b6)',
		),
		array(
			'slug' => 'home-kitchen',
			'name' => __( 'Home & Kitchen', 'dejoiy' ),
			'icon' => 'home',
			'url'  => home_url( '/home-kitchen/home-kitchen/' ),
			'bg'   => 'linear-gradient(135deg,#06b6d4,#22d3ee)',
		),
		array(
			'slug' => 'beauty-personal-care',
			'name' => __( 'Beauty & Care', 'dejoiy' ),
			'icon' => 'beauty',
			'url'  => home_url( '/beauty-personal-care/beauty-personal-care/' ),
			'bg'   => 'linear-gradient(135deg,#f59e0b,#fbbf24)',
		),
		array(
			'slug' => 'nexus',
			'name' => __( 'Books & Learning', 'dejoiy' ),
			'icon' => 'book',
			'url'  => home_url( '/dejoiy-library/?dejoiy_library=1' ),
			'bg'   => 'linear-gradient(135deg,#8b5cf6,#d946ef)',
		),
		array(
			'slug' => 'toys-games',
			'name' => __( 'Toys & Games', 'dejoiy' ),
			'icon' => 'game',
			'url'  => home_url( '/toys-games/toys-games/' ),
			'bg'   => 'linear-gradient(135deg,#10b981,#34d399)',
		),
		array(
			'slug' => 'sports-fitness',
			'name' => __( 'Sports & Fitness', 'dejoiy' ),
			'icon' => 'fitness',
			'url'  => home_url( '/sports-fitness/sports-fitness/' ),
			'bg'   => 'linear-gradient(135deg,#ef4444,#f87171)',
		),
		array(
			'slug' => 'studio',
			'name' => __( 'Custom Studio', 'dejoiy' ),
			'icon' => 'art',
			'url'  => home_url( '/dejoiy-custom-studio/' ),
			'bg'   => 'linear-gradient(135deg,#f97316,#fb923c)',
		),
	);

	$out = array();
	foreach ( $list as $cat ) {
		if ( '' === $cat['url'] && taxonomy_exists( 'product_cat' ) ) {
			$term = get_term_by( 'slug', $cat['slug'], 'product_cat' );
			if ( $term && ! is_wp_error( $term ) ) {
				$link = get_term_link( $term );
				if ( $link && ! is_wp_error( $link ) ) {
					$cat['url'] = $link;
				}
			}
		}
		if ( '' === $cat['url'] ) {
			$cat['url'] = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
		}
		$out[] = $cat;
	}
	return $out;
}

/**
 * Product query wrapper (reuses Universe helper when available).
 *
 * @param array<string, mixed> $args Query args.
 * @return array<int, WP_Post>
 */
function dejoiy_mph_products( $args = array() ) {
	if ( function_exists( 'dejoiy_universe_get_products' ) ) {
		return dejoiy_universe_get_products( $args );
	}
	$defaults = array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => 8,
		'no_found_rows'  => true,
	);
	$q = new WP_Query( array_merge( $defaults, $args ) );
	$p = $q->posts;
	wp_reset_postdata();
	return is_array( $p ) ? $p : array();
}

/**
 * Recently viewed product ids.
 *
 * @return array<int, int>
 */
function dejoiy_mph_viewed_ids() {
	if ( function_exists( 'dejoiy_home_get_viewed_ids' ) ) {
		$ids = dejoiy_home_get_viewed_ids();
		return array_map( 'absint', (array) $ids );
	}
	if ( isset( $_COOKIE['dejoiy_viewed'] ) ) {
		$raw = explode( ',', sanitize_text_field( wp_unslash( $_COOKIE['dejoiy_viewed'] ) ) );
		$ids = array_map( 'absint', $raw );
		return array_values( array_filter( $ids ) );
	}
	return array();
}

/**
 * First image or fallback.
 *
 * @param WP_Post $post Product post.
 * @return string
 */
function dejoiy_mph_image( $post ) {
	$url = (string) get_the_post_thumbnail_url( $post, 'woocommerce_thumbnail' );
	if ( '' === $url ) {
		$url = (string) get_the_post_thumbnail_url( $post, 'medium' );
	}
	return $url;
}

/**
 * Price payload.
 *
 * @param WC_Product $product Product.
 * @return array<string, mixed>
 */
function dejoiy_mph_price( $product ) {
	$out = array(
		'current'  => '',
		'regular'  => '',
		'pct'      => '',
		'sale'     => false,
	);
	if ( ! $product ) {
		return $out;
	}
	$cur = $product->get_price();
	if ( $cur > 0 ) {
		$out['current'] = wc_price( wc_get_price_to_display( $product ) );
		$reg = $product->get_regular_price();
		if ( is_numeric( $reg ) && $reg > 0 && (float) $reg > (float) $cur ) {
			$out['regular'] = wc_price( $reg );
			$out['pct']     = round( ( ( (float) $reg - (float) $cur ) / (float) $reg ) * 100 );
			$out['sale']    = true;
		}
	}
	return $out;
}

/**
 * Rating payload (deterministic fallback when no reviews exist).
 *
 * @param WC_Product $product Product.
 * @return array{rating:float,reviews:int}
 */
function dejoiy_mph_rating( $product ) {
	$id      = $product instanceof WC_Product ? (int) $product->get_id() : 0;
	$rating  = $product && is_callable( array( $product, 'get_average_rating' ) ) ? (float) $product->get_average_rating() : 0.0;
	$reviews = $product && is_callable( array( $product, 'get_review_count' ) ) ? (int) $product->get_review_count() : 0;

	if ( $rating <= 0 ) {
		$rating = 3.0 + ( ( $id * 7 ) % 19 ) / 10.0; // 3.0 – 4.9
		$rating = min( 4.9, $rating );
	}
	if ( $reviews <= 0 ) {
		$reviews = 8 + ( ( $id * 13 ) % 240 );
	}
	return array(
		'rating'  => round( $rating * 2 ) / 2,
		'reviews' => $reviews,
	);
}

/**
 * Delivery ETA string.
 *
 * @param int $id Product ID.
 * @return string
 */
function dejoiy_mph_delivery( $id ) {
	$days = 2 + ( $id % 4 );
	$date = strtotime( '+' . $days . ' days', current_time( 'timestamp' ) );
	return date_i18n( 'D, d M', $date );
}

/**
 * Ecosystem meta for a product.
 *
 * @param int $id Product ID.
 * @return array<string, string>
 */
function dejoiy_mph_eco( $id ) {
	$map = array(
		'marketplace' => array( 'Shop', 'linear-gradient(135deg,#7c3aed,#a855f7)' ),
		'nexus'       => array( 'Nexus', 'linear-gradient(135deg,#8b5cf6,#d946ef)' ),
		'studio'      => array( 'Studio', 'linear-gradient(135deg,#f97316,#fb923c)' ),
		'quickmart'   => array( 'QuickMart', 'linear-gradient(135deg,#06b6d4,#22d3ee)' ),
		'refurbished' => array( 'Renew', 'linear-gradient(135deg,#059669,#34d399)' ),
		'services'    => array( 'Services', 'linear-gradient(135deg,#ec4899,#f472b6)' ),
	);
	if ( function_exists( 'dejoiy_get_product_ecosystem' ) ) {
		$slug = (string) dejoiy_get_product_ecosystem( $id );
		if ( isset( $map[ $slug ] ) ) {
			return array( 'label' => $map[ $slug ][0], 'bg' => $map[ $slug ][1], 'slug' => $slug );
		}
	}
	return array( 'label' => $map['marketplace'][0], 'bg' => $map['marketplace'][1], 'slug' => 'marketplace' );
}

/**
 * Product URL (ecosystem-aware).
 *
 * @param int $id Product ID.
 * @return string
 */
function dejoiy_mph_product_url( $id ) {
	if ( function_exists( 'dejoiy_ecosystem_product_url' ) ) {
		return (string) dejoiy_ecosystem_product_url( $id );
	}
	return (string) get_permalink( $id );
}

/**
 * Add-to-cart safe (only simple products get AJAX add).
 *
 * @param WC_Product $product Product.
 * @return bool
 */
function dejoiy_mph_can_add( $product ) {
	return $product && $product->is_type( 'simple' ) && $product->is_in_stock();
}

/* ---------------------------------------------------------------
   View fragments
   --------------------------------------------------------------- */

/**
 * @param string $name Icon key.
 * @return string
 */
function dejoiy_mph_icon( $name ) {
	$s = '<svg class="mph-ic" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">%s</svg>';
	$icons = array(
		'star'  => '<svg class="mph-star" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/></svg>',
		'star-o' => '<svg class="mph-star mph-star--o" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/></svg>',
		'truck' => '<svg class="mph-card__truck" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.62l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/></svg>',
		'phone' => sprintf( $s, '<rect x="6" y="2" width="12" height="20" rx="2"/><path d="M11 18h2"/>' ),
		'dress' => sprintf( $s, '<path d="M9 3 5 6l-1.5 3L9 12v9h6v-9l5.5-3L19 6l-4-3-3 2-3-2z"/><path d="M12 5v16"/>' ),
		'home'  => sprintf( $s, '<path d="M3 11 12 3l9 8"/><path d="M5 10v10h14V10"/><path d="M10 20v-6h4v6"/>' ),
		'beauty'=> sprintf( $s, '<rect x="7" y="2" width="10" height="5" rx="1.4"/><path d="M7 7h10l-1.2 5H8.2z"/><rect x="8.2" y="12" width="7.6" height="9" rx="1.6"/>' ),
		'book'  => sprintf( $s, '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20V2H6.5A2.5 2.5 0 0 0 4 4.5z"/><path d="M4 19.5A2.5 2.5 0 0 0 6.5 22H20v-5"/>' ),
		'game'  => sprintf( $s, '<path d="M6 6h12a4 4 0 0 1 3.9 4.8l-.8 4A4 4 0 0 1 15.6 18l-2.1-2H10.5L8.4 18a4 4 0 0 1-5.5-3.2l-.8-4A4 4 0 0 1 6 6z"/><path d="M8 6v4M6 8h4"/>' ),
		'fitness'=> sprintf( $s, '<path d="M6.5 6.5v11M4 9v6M17.5 6.5v11M20 9v6M8.5 12h7"/>' ),
		'art'   => sprintf( $s, '<circle cx="12" cy="12" r="9"/><path d="M12 7l5 5-5 5-5-5z"/><path d="M12 7v10M7 12h10"/>' ),
		'spark' => sprintf( $s, '<path d="M12 2 14.5 9.5 22 12l-7.5 2.5L12 22l-2.5-7.5L2 12l7.5-2.5z"/>' ),
		'bolt'  => sprintf( $s, '<path d="M13 2 4 14h6l-1 8 9-12h-6z"/>' ),
		'gem'   => sprintf( $s, '<path d="M6 3h12l4 6-10 12L2 9z"/><path d="M2 9h20M6 3 4 9l8 12L20 9l-2-6M10 3l2 6 2-6"/>' ),
		'mark'  => sprintf( $s, '<path d="M12 3l7 9-7 9-7-9z"/>' ),
		'orb'   => sprintf( $s, '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/>' ),
		// Glyph aliases coming from the universe gateway helper.
		'◆' => sprintf( $s, '<path d="M12 3l7 9-7 9-7-9z"/>' ),
		'✦' => sprintf( $s, '<path d="M12 2 14.5 9.5 22 12l-7.5 2.5L12 22l-2.5-7.5L2 12l7.5-2.5z"/>' ),
		'✿' => sprintf( $s, '<circle cx="12" cy="12" r="9"/><path d="M12 7l5 5-5 5-5-5z"/><path d="M12 7v10M7 12h10"/>' ),
		'⚡' => sprintf( $s, '<path d="M13 2 4 14h6l-1 8 9-12h-6z"/>' ),
		'◈' => sprintf( $s, '<path d="M6 3h12l4 6-10 12L2 9z"/><path d="M2 9h20M6 3 4 9l8 12L20 9l-2-6M10 3l2 6 2-6"/>' ),
		'◎' => sprintf( $s, '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/>' ),
		'chev-l' => '<svg class="mph-rarr" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>',
		'chev-r' => '<svg class="mph-rarr" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>',
		'check'  => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>',
	);
	return isset( $icons[ $name ] ) ? $icons[ $name ] : '';
}

/**
 * Resolve an icon renderer: SVG when available, else the raw glyph.
 *
 * @param string $name Icon key / glyph.
 * @return string
 */
function dejoiy_mph_icon_or( $name ) {
	$svg = dejoiy_mph_icon( $name );
	return '' !== $svg ? $svg : esc_html( $name );
}

/**
 * Section heading.
 *
 * @param string $id    Anchor id.
 * @param string $title Title HTML.
 * @param string $link  See-all URL.
 * @return string
 */
function dejoiy_mph_section_head( $id, $title, $link = '' ) {
	$seeall = '';
	if ( '' !== $link ) {
		$seen   = esc_html__( 'See all', 'dejoiy' );
		$seeall = '<a class="mph-seeall" href="' . esc_url( $link ) . '">' . $seen . ' →</a>';
	}
	return '<div class="mph-head" id="' . esc_attr( $id ) . '"><h2 class="mph-head__title">' . $title . '</h2>' . $seeall . '</div>';
}

/* ---------------------------------------------------------------
   Product card
   --------------------------------------------------------------- */

/**
 * @param WP_Post      $post  Product.
 * @param array<string,mixed> $opts  Options.
 * @return string
 */
function dejoiy_mph_card( $post, $opts = array() ) {
	$product = function_exists( 'wc_get_product' ) ? wc_get_product( $post->ID ) : null;
	if ( ! $product ) {
		return '';
	}

	$img    = dejoiy_mph_image( $post );
	$price  = dejoiy_mph_price( $product );
	$rating = dejoiy_mph_rating( $product );
	$eco    = dejoiy_mph_eco( $product->get_id() );
	$url    = dejoiy_mph_product_url( $product->get_id() );
	$eta    = dejoiy_mph_delivery( $product->get_id() );
	$can    = dejoiy_mph_can_add( $product );

	$badges = '';
	if ( ! empty( $price['pct'] ) ) {
		$badges .= '<span class="mph-badge mph-badge--deal">-' . esc_html( (string) $price['pct'] ) . '%</span>';
	}
	if ( 'marketplace' !== $eco['slug'] ) {
		$badges .= '<span class="mph-badge mph-badge--eco">' . esc_html( $eco['label'] ) . '</span>';
	}

	$media = '';
	if ( '' !== $img ) {
		$media = '<img class="mph-card__img" src="' . esc_url( $img ) . '" alt="' . esc_attr( get_the_title( $post ) ) . '" loading="lazy" decoding="async">';
	} else {
		$letter = mb_substr( get_the_title( $post ), 0, 1 );
		$media  = '<span class="mph-card__ph" style="background:' . esc_attr( $eco['bg'] ) . '">' . esc_html( $letter ) . '</span>';
	}

	$action = '';
	if ( $can ) {
		$action = '<button type="button" class="mph-card__add" data-add="' . esc_attr( (string) $product->get_id() ) . '"><span>' . esc_html__( 'Add to Cart', 'dejoiy' ) . '</span></button>';
	} else {
		$action = '<a class="mph-card__add mph-card__add--ghost" href="' . esc_url( $url ) . '">' . esc_html__( 'View Options', 'dejoiy' ) . '</a>';
	}

	$title = '<h3 class="mph-card__title"><a href="' . esc_url( $url ) . '">' . esc_html( get_the_title( $post ) ) . '</a></h3>';

	$price_html = '<p class="mph-card__price">' . $price['current'];
	if ( ! empty( $price['regular'] ) ) {
		$price_html .= ' <span class="mph-card__strike">' . $price['regular'] . '</span>';
	}
	$price_html .= '</p>';

	$stars = '';
	for ( $i = 1; $i <= 5; $i++ ) {
		$frac  = (int) floor( $rating['rating'] );
		$stars .= ( $i <= $frac ) ? dejoiy_mph_icon( 'star' ) : dejoiy_mph_icon( 'star-o' );
	}
	$rating_html = '<div class="mph-card__stars" title="' . esc_attr( (string) $rating['rating'] ) . '">' . $stars
		. '<span class="mph-card__reviews">(' . esc_html( (string) $rating['reviews'] ) . ')</span></div>';

	$delivery_html = '<p class="mph-card__delivery">' . dejoiy_mph_icon( 'truck' ) . '<span>' . esc_html__( 'Free Delivery by', 'dejoiy' ) . ' <b>' . esc_html( $eta ) . '</b></span></p>';

	return '<article class="mph-card">
		<a class="mph-card__media" href="' . esc_url( $url ) . '" tabindex="-1" aria-hidden="true">' . $media . $badges . '</a>
		<div class="mph-card__body">
			' . $price_html . '
			' . $title . '
			' . $rating_html . '
			' . $delivery_html . '
			' . $action . '
		</div>
	</article>';
}

/**
 * Horizontal rail wrapper.
 *
 * @param string $id     Section id.
 * @param string $title  Section title.
 * @param array<int, WP_Post> $posts Products.
 * @param string $link   See-all link.
 * @param string $class  Extra class.
 * @return string
 */
function dejoiy_mph_rail( $id, $title, $posts, $link = '', $class = '' ) {
	if ( empty( $posts ) ) {
		return '';
	}
	$items = '';
	foreach ( $posts as $post ) {
		$items .= '<div class="mph-rail__item">' . dejoiy_mph_card( $post ) . '</div>';
	}
	return '<section class="mph-section mph-rail-wrap ' . esc_attr( $class ) . '">'
		. dejoiy_mph_section_head( $id, $title, $link )
		. '<div class="mph-rail" data-mph-rail>
			<button type="button" class="mph-rail__arrow mph-rail__arrow--l" data-mph-raila-l aria-label="' . esc_attr__( 'Scroll left', 'dejoiy' ) . '">' . dejoiy_mph_icon( 'chev-l' ) . '</button>
			<div class="mph-rail__track" data-mph-rail-track>' . $items . '</div>
			<button type="button" class="mph-rail__arrow mph-rail__arrow--r" data-mph-raila-r aria-label="' . esc_attr__( 'Scroll right', 'dejoiy' ) . '">' . dejoiy_mph_icon( 'chev-r' ) . '</button>
		</div>
	</section>';
}

/* ---------------------------------------------------------------
   Curated sections
   --------------------------------------------------------------- */

/**
 * Hero slides (curated + one real product visual each).
 *
 * @return array<int, array<string, string>>
 */
function dejoiy_mph_hero_slides() {
	$shop      = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
	$deals     = home_url( '/dejoiy-festival-sale/' );
	$studio    = home_url( '/dejoiy-custom-studio/' );
	$nexus     = home_url( '/dejoiy-library/?dejoiy_library=1' );

	$deal_img  = '';
	$instant   = dejoiy_mph_products(
		array( 'posts_per_page' => 3, 'meta_key' => 'total_sales', 'orderby' => 'meta_value_num', 'order' => 'DESC' )
	);
	if ( ! empty( $instant[0] ) ) {
		$deal_img = dejoiy_mph_image( $instant[0] );
	}
	if ( '' === $deal_img ) {
		$on   = dejoiy_mph_products(
			array(
				'posts_per_page' => 3,
				'meta_query'     => array(
					array(
						'key'     => '_sale_price',
						'compare' => 'EXISTS',
					),
				),
			)
		);
		if ( ! empty( $on[0] ) ) {
			$deal_img = dejoiy_mph_image( $on[0] );
		}
	}

	$studio_img = '';
	$st         = dejoiy_mph_products(
		array( 'posts_per_page' => 3, 'orderby' => 'rand', 'meta_key' => '_dejoiy_ecosystem', 'meta_value' => 'studio' )
	);
	if ( ! empty( $st[0] ) ) {
		$studio_img = dejoiy_mph_image( $st[0] );
	}

	$nexus_img = '';
	$nx        = dejoiy_mph_products(
		array( 'posts_per_page' => 3, 'orderby' => 'rand', 'meta_key' => '_dejoiy_ecosystem', 'meta_value' => 'nexus' )
	);
	if ( ! empty( $nx[0] ) ) {
		$nexus_img = dejoiy_mph_image( $nx[0] );
	}

	return array(
		array(
			'id'     => 'deals',
			'kicker' => __( 'JOY FESTIVAL SALE', 'dejoiy' ),
			'title'  => __( 'Deals that make you smile', 'dejoiy' ),
			'sub'    => __( 'Big offers on electronics, fashion, home & more. Best-price guarantee, UPI & easy returns.', 'dejoiy' ),
			'cta'    => __( 'Shop the Sale', 'dejoiy' ),
			'url'    => $deals,
			'bg'     => 'linear-gradient(118deg,#7c3aed 0%,#a855f7 45%,#ec4899 100%)',
			'icon'   => 'spark',
			'img'    => $deal_img,
			'chip1'  => __( 'UPTO 60% OFF', 'dejoiy' ),
			'chip2'  => __( 'UPI · Cards · COD', 'dejoiy' ),
		),
		array(
			'id'     => 'studio',
			'kicker' => __( 'DEJOIY CUSTOM STUDIO', 'dejoiy' ),
			'title'  => __( 'Design it. Create it. Own it.', 'dejoiy' ),
			'sub'    => __( 'Custom t-shirts, mugs, caps & tote bags made just for you. Your style, your words.', 'dejoiy' ),
			'cta'    => __( 'Open the Studio', 'dejoiy' ),
			'url'    => $studio,
			'bg'     => 'linear-gradient(118deg,#0ea5e9 0%,#6366f1 50%,#8b5cf6 100%)',
			'icon'   => 'art',
			'img'    => $studio_img,
			'chip1'  => __( '100% YOUR DESIGN', 'dejoiy' ),
			'chip2'  => __( 'START ₹349', 'dejoiy' ),
		),
		array(
			'id'     => 'nexus',
			'kicker' => __( 'DEJOIY NEXUS', 'dejoiy' ),
			'title'  => __( 'Read. Learn. Grow.', 'dejoiy' ),
			'sub'    => __( 'Books, eBooks & courses for every curious mind. Gyaan se badho, khush raho.', 'dejoiy' ),
			'cta'    => __( 'Enter Nexus', 'dejoiy' ),
			'url'    => $nexus,
			'bg'     => 'linear-gradient(118deg,#059669 0%,#10b981 45%,#22d3ee 100%)',
			'icon'   => '✦',
			'img'    => $nexus_img,
			'chip1'  => __( 'FREE CLASSICS', 'dejoiy' ),
			'chip2'  => __( 'COURSES INSIDE', 'dejoiy' ),
		),
	);
}

/**
 * Trust items.
 *
 * @return array<int, array<string, string>>
 */
function dejoiy_mph_trust() {
	return array(
		array( 'icon' => 'truck', 'title' => __( 'FREE Delivery', 'dejoiy' ), 'sub' => __( 'On eligible orders', 'dejoiy' ) ),
		array( 'icon' => 'lock', 'title' => __( '100% Secure', 'dejoiy' ), 'sub' => __( 'UPI · Cards · COD', 'dejoiy' ) ),
		array( 'icon' => 'return', 'title' => __( 'Easy Returns', 'dejoiy' ), 'sub' => __( 'Hassle-free policy', 'dejoiy' ) ),
		array( 'icon' => 'wallet', 'title' => __( 'Best Price', 'dejoiy' ), 'sub' => __( 'Guaranteed fair deals', 'dejoiy' ) ),
	);
}

/**
 * Poster strip with an animated "share frame" canvas — vivid brand colours.
 *
 * @param string $deals   Festival-sale URL.
 * @param string $studio  Custom-studio URL.
 * @param string $library Library URL.
 * @return string
 */
function dejoiy_mph_presents_html( $deals, $studio, $library ) {
	$posters = array(
		array(
			'url'     => $deals,
			'eyebrow' => __( 'Limited time', 'dejoiy' ),
			'title'   => __( 'Festival Deals', 'dejoiy' ),
			'sub'     => __( 'Big savings across every world', 'dejoiy' ),
			'cta'     => __( 'Shop the sale', 'dejoiy' ),
			'bg'      => 'linear-gradient(135deg, #ff7c02 0%, #ff2d55 100%)',
			'accent'  => '#ff7c02',
		),
		array(
			'url'     => $studio,
			'eyebrow' => __( 'Made by you', 'dejoiy' ),
			'title'   => __( 'Custom Studio', 'dejoiy' ),
			'sub'     => __( 'Design tees, mugs, caps & more', 'dejoiy' ),
			'cta'     => __( 'Start designing', 'dejoiy' ),
			'bg'      => 'linear-gradient(135deg, #7c5cff 0%, #431406 120%)',
			'accent'  => '#9d7bff',
		),
		array(
			'url'     => $library,
			'eyebrow' => __( 'Read & learn', 'dejoiy' ),
			'title'   => __( 'DEJOIY Library', 'dejoiy' ),
			'sub'     => __( 'Books that expand your universe', 'dejoiy' ),
			'cta'     => __( 'Explore the Library', 'dejoiy' ),
			'bg'      => 'linear-gradient(135deg, #ffd000 0%, #ff7c02 110%)',
			'accent'  => '#ffd000',
		),
	);

	$cards = '';
	foreach ( $posters as $p ) {
		$cards .= '<a class="mph-poster" href="' . esc_url( $p['url'] ) . '" style="--mph-post-bg:' . $p['bg'] . ';--mph-post-ac:' . esc_attr( $p['accent'] ) . ';">'
			. '<span class="mph-poster__glow" aria-hidden="true"></span>'
			. '<span class="mph-poster__eyebrow">' . esc_html( $p['eyebrow'] ) . '</span>'
			. '<span class="mph-poster__title">' . esc_html( $p['title'] ) . '</span>'
			. '<span class="mph-poster__sub">' . esc_html( $p['sub'] ) . '</span>'
			. '<span class="mph-poster__cta">' . esc_html( $p['cta'] ) . ' →</span>'
			. '</a>';
	}

	return '<section class="mph-section mph-presents" aria-labelledby="mph-h-presents" data-mph-presents>'
		. '<canvas class="mph-presents__canvas" data-mph-presents-canvas aria-hidden="true"></canvas>'
		. '<div class="mph-presents__in">'
		. '<h2 id="mph-h-presents" class="mph-presents__title">' . esc_html__( 'Explore fresh stories', 'dejoiy' ) . '</h2>'
		. '<div class="mph-presents__grid">' . $cards . '</div>'
		. '</div>'
		. '</section>';
}

/* ---------------------------------------------------------------
   Main renderer
   --------------------------------------------------------------- */

/**
 * @return string
 */
function dejoiy_marketplace_home_html() {
	if ( ! function_exists( 'wc_get_product' ) ) {
		return '';
	}

	$shop     = wc_get_page_permalink( 'shop' );
	$deals    = home_url( '/dejoiy-festival-sale/' );
	$seller   = home_url( '/sell-on-dejoiy/' );
	$vreg     = home_url( '/vendor-register/' );
	$services = home_url( '/dejoiy-services/' );
	$author   = home_url( '/dejoiy-library/?dejoiy_library=1' );
	$studio   = home_url( '/dejoiy-custom-studio/' );
	$library  = home_url( '/dejoiy-library/?dejoiy_library=1' );

	$gateways = dejoiy_mph_gateways();
	$cats     = dejoiy_mph_categories();
	$slides   = dejoiy_mph_hero_slides();

	$trending = dejoiy_mph_products(
		array(
			'posts_per_page' => 10,
			'meta_key'       => 'total_sales',
			'orderby'        => 'meta_value_num',
			'order'          => 'DESC',
		)
	);

	$fresh = dejoiy_mph_products( array( 'posts_per_page' => 8, 'orderby' => 'date', 'order' => 'DESC' ) );

	$deals_posts = dejoiy_mph_products(
		array(
			'posts_per_page' => 10,
			'meta_key'       => '_sale_price',
			'orderby'        => 'meta_value_num',
			'order'          => 'DESC',
			'meta_query'     => array(
				array(
					'key'     => '_sale_price',
					'compare' => 'EXISTS',
				),
			),
		)
	);

	if ( empty( $deals_posts ) ) {
		$deals_posts = $trending;
	}

	$viewed_ids = dejoiy_mph_viewed_ids();
	$viewed     = array();
	if ( ! empty( $viewed_ids ) ) {
		$viewed = dejoiy_mph_products(
			array(
				'post__in'       => array_slice( $viewed_ids, 0, 8 ),
				'orderby'        => 'post__in',
				'posts_per_page' => 8,
			)
		);
	}

	if ( empty( $trending ) && empty( $fresh ) && empty( $deals_posts ) && empty( $viewed ) ) {
		$trending = dejoiy_mph_products( array( 'posts_per_page' => 8, 'orderby' => 'rand' ) );
		$fresh    = $trending;
		$deals_posts = $trending;
	}

	ob_start();
	?>
	<div class="mph" data-mph>
		<?php
		/* ============ HERO ============ */
		?>
		<section class="mph-hero" aria-label="Featured" data-mph-hero>
			<div class="mph-hero__track" data-mph-hero-track>
				<?php foreach ( $slides as $i => $s ) : ?>
					<article class="mph-hero__slide" data-mph-slide style="--mph-slide-bg:<?php echo esc_attr( $s['bg'] ); ?>">
						<div class="mph-hero__in">
							<div class="mph-hero__copy">
								<p class="mph-hero__kicker"><?php echo esc_html( $s['kicker'] ); ?></p>
								<h1 class="mph-hero__title"><?php echo esc_html( $s['title'] ); ?></h1>
								<p class="mph-hero__sub"><?php echo esc_html( $s['sub'] ); ?></p>
								<div class="mph-hero__chips">
									<span class="mph-chip"><?php echo esc_html( $s['chip1'] ); ?></span>
									<span class="mph-chip"><?php echo esc_html( $s['chip2'] ); ?></span>
								</div>
								<a class="mph-btn mph-btn--hero" href="<?php echo esc_url( $s['url'] ); ?>"><?php echo esc_html( $s['cta'] ); ?> →</a>
							</div>
							<div class="mph-hero__media">
								<span class="mph-hero__icon" aria-hidden="true"><?php echo dejoiy_mph_icon_or( $s['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								<?php if ( '' !== $s['img'] ) : ?>
									<img class="mph-hero__img" src="<?php echo esc_url( $s['img'] ); ?>" alt="" loading="<?php echo 0 === $i ? 'eager' : 'lazy'; ?>" decoding="async">
								<?php else : ?>
									<span class="mph-hero__mock" aria-hidden="true"><?php echo dejoiy_mph_icon_or( $s['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								<?php endif; ?>
							</div>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
			<div class="mph-hero__nav">
				<button type="button" class="mph-hero__arrow" data-mph-hero-prev aria-label="<?php esc_attr_e( 'Previous slide', 'dejoiy' ); ?>">‹</button>
				<div class="mph-hero__dots" data-mph-hero-dots></div>
				<button type="button" class="mph-hero__arrow" data-mph-hero-next aria-label="<?php esc_attr_e( 'Next slide', 'dejoiy' ); ?>">›</button>
			</div>
		</section>

		<?php
		/* ============ POSTER STRIP + SHARE-FRAME CANVAS ============ */
		echo dejoiy_mph_presents_html( $deals, $studio, $library ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		/* ============ QUICK CATEGORIES (app bubbles, also grid on desktop) ============ */
		?>
		<section class="mph-section mph-apps" aria-label="<?php esc_attr_e( 'Shop by category', 'dejoiy' ); ?>">
			<div class="mph-apps__scroller" data-mph-cats>
				<?php foreach ( $cats as $c ) : ?>
					<a class="mph-app" href="<?php echo esc_url( $c['url'] ); ?>">
						<span class="mph-app__bubble" style="--mph-cat-bg:<?php echo esc_attr( $c['bg'] ); ?>"><span class="mph-ic-wrap"><?php echo dejoiy_mph_icon_or( $c['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></span>
						<span class="mph-app__label"><?php echo esc_html( $c['name'] ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
			<div class="mph-catgrid" aria-hidden="false">
				<?php foreach ( $cats as $c ) : ?>
					<a class="mph-catgrid__tile" href="<?php echo esc_url( $c['url'] ); ?>" style="--mph-cat-bg:<?php echo esc_attr( $c['bg'] ); ?>">
						<span class="mph-catgrid__icon"><?php echo dejoiy_mph_icon_or( $c['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="mph-catgrid__name"><?php echo esc_html( $c['name'] ); ?></span>
						<span class="mph-catgrid__go">→</span>
					</a>
				<?php endforeach; ?>
			</div>
		</section>

		<?php
		/* ============ DEALS + COUNTDOWN ============ */
		if ( ! empty( $deals_posts ) ) :
			?>
			<section class="mph-section mph-deals" aria-labelledby="mph-h-deals">
				<?php
				echo dejoiy_mph_section_head( 'mph-h-deals', __( '<span>Deals of the Day</span> <span class="mph-head__tag">ends tonight</span>', 'dejoiy' ), $deals );
				?>
				<div class="mph-count" data-mph-count role="timer" aria-live="off">
					<span class="mph-count__label"><?php esc_html_e( 'Ends in', 'dejoiy' ); ?></span>
					<span class="mph-count__cell" data-mph-count-h>00</span><span class="mph-count__sep">:</span>
					<span class="mph-count__cell" data-mph-count-m>00</span><span class="mph-count__sep">:</span>
					<span class="mph-count__cell" data-mph-count-s>00</span>
				</div>
				<div class="mph-grid mph-grid--deals">
					<?php foreach ( $deals_posts as $post ) : ?>
						<?php echo dejoiy_mph_card( $post ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>

		<?php
		/* ============ ECOSYSTEM WORLDS ============ */
		if ( ! empty( $gateways ) ) :
			?>
			<section class="mph-section mph-eco" aria-labelledby="mph-h-eco">
				<?php
				echo dejoiy_mph_section_head( 'mph-h-eco', __( 'Explore the DEJOIY universe', 'dejoiy' ), $shop );
				?>
				<div class="mph-eco__grid">
					<?php foreach ( $gateways as $g ) : ?>
						<a class="mph-eco__card mph-eco__card--<?php echo esc_attr( $g['theme'] ); ?>" href="<?php echo esc_url( $g['url'] ); ?>">
							<span class="mph-eco__art" aria-hidden="true"></span>
							<span class="mph-eco__icon" aria-hidden="true"><?php echo dejoiy_mph_icon_or( $g['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span class="mph-eco__verb"><?php echo esc_html( $g['verb'] ); ?></span>
							<span class="mph-eco__label"><?php echo esc_html( $g['label'] ); ?></span>
							<span class="mph-eco__tag"><?php echo esc_html( $g['tagline'] ); ?></span>
							<span class="mph-eco__go">→</span>
						</a>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>

		<?php
		/* ============ TRENDING RAIL ============ */
		echo dejoiy_mph_rail( 'mph-h-trending', __( 'Trending this week', 'dejoiy' ), $trending, $shop ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		/* ============ FRESH RAIL ============ */
		echo dejoiy_mph_rail( 'mph-h-fresh', __( 'Fresh arrivals', 'dejoiy' ), $fresh, $shop ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		/* ============ RECENTLY VIEWED ============ */
		if ( ! empty( $viewed ) ) {
			echo dejoiy_mph_rail( 'mph-h-viewed', __( 'You last checked out', 'dejoiy' ), $viewed, $shop ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		?>

		<?php
		/* ============ JOI CTA ============ */
		?>
		<section class="mph-section mph-joi" aria-labelledby="mph-h-joi">
			<div class="mph-joi__in">
				<span class="mph-joi__orb" aria-hidden="true"></span>
				<div class="mph-joi__copy">
					<p class="mph-joi__badge"><?php esc_html_e( 'Powered by DEJOIY intelligence', 'dejoiy' ); ?></p>
					<h2 id="mph-h-joi" class="mph-joi__title"><?php esc_html_e( 'Ask JOI anything', 'dejoiy' ); ?></h2>
					<p class="mph-joi__sub"><?php esc_html_e( 'Fast search, smart picks, and instant answers across every DEJOIY world.', 'dejoiy' ); ?></p>
					<form class="mph-joi__form" action="<?php echo esc_url( $shop ); ?>" method="get" role="search">
						<input type="hidden" name="post_type" value="product">
						<input class="mph-joi__input" type="search" name="s" placeholder="<?php esc_attr_e( 'Search products, books, services…', 'dejoiy' ); ?>" autocomplete="off">
						<button class="mph-joi__btn" type="submit"><?php esc_html_e( 'Search', 'dejoiy' ); ?></button>
					</form>
				</div>
			</div>
		</section>

		<?php
		/* ============ TRUST ============ */
		?>
		<section class="mph-section mph-trust" aria-label="<?php esc_attr_e( 'Why shop on DEJOIY', 'dejoiy' ); ?>">
			<?php foreach ( dejoiy_mph_trust() as $t ) : ?>
				<div class="mph-trust__item">
					<span class="mph-trust__icon" aria-hidden="true">
						<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
							<?php if ( 'truck' === $t['icon'] ) : ?>
								<path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.62l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/>
							<?php elseif ( 'lock' === $t['icon'] ) : ?>
								<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
							<?php elseif ( 'return' === $t['icon'] ) : ?>
								<path d="M3 7v6h6"/><path d="M21 17a9 9 0 0 1-15 2.72L3 15"/><path d="M3 13a9 9 0 0 1 15-2.72L21 13"/>
							<?php else : ?>
								<path d="M12 2 14.4 8.1 21 9.27l-5 4.87 1.18 6.88L12 17.77 6.82 21.02 8 14.14 3 9.27 9.6 8.1z"/>
							<?php endif; ?>
						</svg>
					</span>
					<div class="mph-trust__copy">
						<b><?php echo esc_html( $t['title'] ); ?></b>
						<small><?php echo esc_html( $t['sub'] ); ?></small>
					</div>
				</div>
			<?php endforeach; ?>
		</section>

		<?php
		/* ============ SELL CTA ============ */
		?>
		<section class="mph-section mph-sell" aria-labelledby="mph-h-sell">
			<div class="mph-sell__in">
				<h2 id="mph-h-sell" class="mph-sell__title"><?php esc_html_e( 'Build your business on DEJOIY', 'dejoiy' ); ?></h2>
				<p class="mph-sell__sub"><?php esc_html_e( 'Sell products, offer services or publish books — grow with India\u2019s next-gen marketplace.', 'dejoiy' ); ?></p>
				<div class="mph-sell__actions">
					<a class="mph-btn mph-btn--light" href="<?php echo esc_url( $seller ); ?>"><?php esc_html_e( 'Become a Seller', 'dejoiy' ); ?></a>
					<a class="mph-btn mph-btn--outline" href="<?php echo esc_url( $vreg ); ?>"><?php esc_html_e( 'Vendor Registration', 'dejoiy' ); ?></a>
					<a class="mph-btn mph-btn--outline" href="<?php echo esc_url( $services ); ?>"><?php esc_html_e( 'Offer Services', 'dejoiy' ); ?></a>
					<a class="mph-btn mph-btn--outline" href="<?php echo esc_url( $author ); ?>"><?php esc_html_e( 'Become an Author', 'dejoiy' ); ?></a>
				</div>
			</div>
		</section>

		<div class="mph-toast" data-mph-toast role="status" aria-live="polite"></div>
	</div>
	<?php
	return (string) ob_get_clean();
}