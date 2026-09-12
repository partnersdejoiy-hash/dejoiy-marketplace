<?php
/**
 * DEJOIY Amazon-compete layer — honest catalog UX, aliases, PDP truth, headers.
 *
 * Does not invent sellers, reviews, or inventory. Safe to load on every request.
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Seller Hub writes WooCommerce `product` posts in this same WordPress install.
 * Those listings are what https://dejoiy.com shows. Host cookies differ
 * (sellerhub.dejoiy.com vs dejoiy.com) but the catalogue is one database.
 */

function dejoiy_amazon_compete_assets() {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return;
	}
	$dir = get_stylesheet_directory();
	$uri = get_stylesheet_directory_uri();
	$css = $dir . '/dejoiy-amazon-compete.css';
	if ( is_readable( $css ) ) {
		wp_enqueue_style(
			'dejoiy-amazon-compete',
			$uri . '/dejoiy-amazon-compete.css',
			array( 'dejoiy-global-header' ),
			(string) filemtime( $css )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'dejoiy_amazon_compete_assets', 10110 );

/**
 * Customer-app security headers (Seller Hub already sends these at nginx).
 * Nginx CORS * cannot be fully removed from PHP; this still sets HSTS/frame/nosniff.
 */
function dejoiy_amazon_compete_send_headers() {
	if ( headers_sent() ) {
		return;
	}
	$host = (string) ( $_SERVER['HTTP_HOST'] ?? '' );
	if ( false !== strpos( $host, 'sellerhub' ) ) {
		return;
	}
	header( 'Strict-Transport-Security: max-age=31536000; includeSubDomains', false );
	header( 'X-Frame-Options: SAMEORIGIN', false );
	header( 'X-Content-Type-Options: nosniff', false );
	header( 'Referrer-Policy: strict-origin-when-cross-origin', false );
}
add_action( 'send_headers', 'dejoiy_amazon_compete_send_headers', 2 );

/**
 * Amazon-style short URLs → real destinations (no rewrite flush required).
 */
function dejoiy_amazon_compete_aliases() {
	if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}
	$path = (string) parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH );
	$path = strtolower( trim( $path, '/' ) );
	if ( '' === $path ) {
		return;
	}

	$map = array(
		'wishlist'     => home_url( '/my-account/?et-wishlist-page' ),
		'favorites'    => home_url( '/my-account/?et-wishlist-page' ),
		'nexus'        => home_url( '/dejoiy-library/?dejoiy_library=1' ),
		'library'      => home_url( '/dejoiy-library/' ),
		'track-order'  => function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'orders' ) : home_url( '/my-account/orders/' ),
		'track'        => function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'orders' ) : home_url( '/my-account/orders/' ),
		'orders'       => function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'orders' ) : home_url( '/my-account/orders/' ),
		'become-a-seller' => home_url( '/sell-on-dejoiy/' ),
		'seller'       => 'https://sellerhub.dejoiy.com/',
		'custom-studio'=> home_url( '/dejoiy-custom-studio/' ),
		'studio'       => home_url( '/dejoiy-custom-studio/' ),
	);

	if ( isset( $map[ $path ] ) ) {
		wp_safe_redirect( $map[ $path ], 301 );
		exit;
	}
}
add_action( 'template_redirect', 'dejoiy_amazon_compete_aliases', 1 );

/**
 * Hide duplicate Woo "Copy" listings from storefront loops (they remain in admin / Seller Hub).
 *
 * @param WP_Query $query Query.
 */
function dejoiy_amazon_compete_hide_copy_listings( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	$pt = $query->get( 'post_type' );
	$is_products = ( 'product' === $pt )
		|| $query->is_post_type_archive( 'product' )
		|| ( function_exists( 'is_shop' ) && is_shop() )
		|| ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() )
		|| $query->is_search();
	if ( ! $is_products ) {
		return;
	}

	$ids = dejoiy_amazon_compete_copy_product_ids();
	if ( empty( $ids ) ) {
		return;
	}
	$existing = $query->get( 'post__not_in' );
	if ( ! is_array( $existing ) ) {
		$existing = array();
	}
	$query->set( 'post__not_in', array_values( array_unique( array_merge( $existing, $ids ) ) ) );
}
add_action( 'pre_get_posts', 'dejoiy_amazon_compete_hide_copy_listings', 40 );

/**
 * @return array<int, int>
 */
function dejoiy_amazon_compete_copy_product_ids() {
	$cached = get_transient( 'dejoiy_copy_product_ids' );
	if ( is_array( $cached ) ) {
		return $cached;
	}
	$q = new WP_Query(
		array(
			'post_type'              => 'product',
			'post_status'            => 'publish',
			'posts_per_page'         => 80,
			's'                      => '(Copy)',
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);
	$ids = array();
	foreach ( (array) $q->posts as $id ) {
		$title = get_the_title( (int) $id );
		if ( false !== stripos( $title, '(copy)' ) ) {
			$ids[] = (int) $id;
		}
	}
	wp_reset_postdata();
	set_transient( 'dejoiy_copy_product_ids', $ids, 10 * MINUTE_IN_SECONDS );
	return $ids;
}

add_action(
	'save_post_product',
	function () {
		delete_transient( 'dejoiy_copy_product_ids' );
	}
);

/**
 * Real Product JSON-LD (Offer + optional AggregateRating).
 */
function dejoiy_amazon_compete_product_schema() {
	if ( ! function_exists( 'is_product' ) || ! is_product() || ! function_exists( 'wc_get_product' ) ) {
		return;
	}
	$product = wc_get_product( get_queried_object_id() );
	if ( ! $product ) {
		return;
	}
	$desc = $product->get_short_description();
	if ( '' === trim( wp_strip_all_tags( (string) $desc ) ) ) {
		$desc = $product->get_description();
	}
	$desc = wp_strip_all_tags( (string) $desc );
	if ( '' === $desc ) {
		$desc = $product->get_name() . ' on DEJOIY.';
	}

	$data = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Product',
		'name'        => $product->get_name(),
		'description' => wp_trim_words( $desc, 40, '…' ),
		'sku'         => $product->get_sku(),
		'url'         => get_permalink( $product->get_id() ),
		'image'       => wp_get_attachment_url( $product->get_image_id() ) ?: '',
		'brand'       => array(
			'@type' => 'Brand',
			'name'  => 'DEJOIY',
		),
		'offers'      => array(
			'@type'         => 'Offer',
			'priceCurrency' => get_woocommerce_currency(),
			'price'         => $product->get_price(),
			'availability'  => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
			'url'           => get_permalink( $product->get_id() ),
		),
	);

	$rating = (float) $product->get_average_rating();
	$count  = (int) $product->get_review_count();
	if ( $rating > 0 && $count > 0 ) {
		$data['aggregateRating'] = array(
			'@type'       => 'AggregateRating',
			'ratingValue' => $rating,
			'reviewCount' => $count,
		);
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $data ) . '</script>' . "\n";
}
add_action( 'wp_head', 'dejoiy_amazon_compete_product_schema', 32 );

add_filter( 'woocommerce_single_product_zoom_enabled', '__return_true', 20 );
add_filter( 'woocommerce_single_product_photoswipe_enabled', '__return_true', 20 );

/**
 * Checkout: visible UPI / COD / cards strip (does not change gateways).
 */
function dejoiy_amazon_compete_checkout_pay_strip() {
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
		return;
	}
	echo '<p class="djy-pay-strip" role="note">Pay with UPI, cards, net banking, or COD where available. Secure checkout on HTTPS.</p>';
}
add_action( 'woocommerce_checkout_before_customer_details', 'dejoiy_amazon_compete_checkout_pay_strip', 4 );

/**
 * Related products on desktop PDP (mobile already has a custom shelf).
 */
function dejoiy_amazon_compete_related_heading() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	if ( function_exists( 'dejoiy_mobile_product_is_product_focus' ) && dejoiy_mobile_product_is_product_focus() ) {
		return;
	}
	add_filter( 'woocommerce_output_related_products_args', 'dejoiy_amazon_compete_related_args', 20 );
}
add_action( 'wp', 'dejoiy_amazon_compete_related_heading', 30 );

/**
 * @param array<string, mixed> $args Args.
 * @return array<string, mixed>
 */
function dejoiy_amazon_compete_related_args( $args ) {
	$args['posts_per_page'] = 4;
	$args['columns']        = 4;
	return $args;
}

/**
 * Honest Open Graph description from the product, not a generic sentence.
 *
 * @param string $desc Description.
 * @return string
 */
function dejoiy_amazon_compete_og_desc( $desc ) {
	if ( ! function_exists( 'is_product' ) || ! is_product() || ! function_exists( 'wc_get_product' ) ) {
		return $desc;
	}
	$product = wc_get_product( get_queried_object_id() );
	if ( ! $product ) {
		return $desc;
	}
	$text = wp_strip_all_tags( (string) $product->get_short_description() );
	if ( '' === $text ) {
		$text = wp_strip_all_tags( (string) $product->get_description() );
	}
	if ( '' === $text ) {
		$price = $product->get_price();
		$text  = $product->get_name() . ( $price ? ' — ' . wp_strip_all_tags( wc_price( $price ) ) : '' );
	}
	return wp_trim_words( $text, 32, '…' );
}
add_filter( 'aioseo_description', 'dejoiy_amazon_compete_og_desc', 20 );
add_filter( 'rank_math/frontend/description', 'dejoiy_amazon_compete_og_desc', 20 );

/**
 * Do not render XStore Elementor sticky cart (bottom "Select options" bar).
 * PDP already has Add to cart and Buy now in the product summary.
 *
 * @param bool                 $should_render Whether to render.
 * @param \Elementor\Widget_Base $widget Widget.
 * @return bool
 */
function dejoiy_amazon_compete_skip_sticky_cart_widget( $should_render, $widget ) {
	if ( ! $should_render || ! is_object( $widget ) || ! method_exists( $widget, 'get_name' ) ) {
		return $should_render;
	}
	if ( 'woocommerce-product-etheme_sticky_cart' === $widget->get_name() ) {
		return false;
	}
	return $should_render;
}
add_filter( 'elementor/frontend/widget/should_render', 'dejoiy_amazon_compete_skip_sticky_cart_widget', 10, 2 );
