<?php
/**
 * DEJOIY Studio — WooCommerce flow (single product + checkout).
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Customizable category term IDs. */
define( 'DEJOIY_STUDIO_CAT_IDS', array( 143, 153, 154, 155, 156 ) );

/**
 * Set studio flow cookie.
 */
function dejoiy_studio_maybe_set_cookie() {
	$set = ( defined( 'DEJOIY_STUDIO_PAGE_ID' ) && is_page( DEJOIY_STUDIO_PAGE_ID ) )
		|| dejoiy_studio_request_has_flow_flag()
		|| ( function_exists( 'dejoiy_studio_use_single_template' ) && dejoiy_studio_use_single_template() )
		|| ( function_exists( 'dejoiy_studio_use_cart_template' ) && dejoiy_studio_use_cart_template() );
	if ( $set && function_exists( 'dejoiy_studio_set_flow_cookie' ) ) {
		dejoiy_studio_set_flow_cookie();
	}
}
add_action( 'template_redirect', 'dejoiy_studio_maybe_set_cookie', 1 );

/**
 * Is customer in studio flow?
 *
 * @return bool
 */
function dejoiy_studio_is_flow() {
	return dejoiy_studio_request_has_flow_flag();
}

/**
 * Product in customizable categories?
 *
 * @param int $product_id Product ID.
 * @return bool
 */
function dejoiy_studio_is_customizable_product( $product_id ) {
	$terms = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return false;
	}
	return (bool) array_intersect( array_map( 'intval', $terms ), DEJOIY_STUDIO_CAT_IDS );
}

/**
 * Studio product URL (custom view when customizable).
 *
 * @param int $product_id Product ID.
 * @return string
 */
function dejoiy_studio_product_url( $product_id ) {
	$url = get_permalink( $product_id );
	if ( dejoiy_studio_is_customizable_product( $product_id ) ) {
		return add_query_arg( 'dejoiy_studio', '1', $url );
	}
	return $url;
}

/**
 * Use studio single template?
 *
 * @return bool
 */
function dejoiy_studio_use_single_template() {
	if ( ! is_singular( 'product' ) || ! dejoiy_studio_request_has_flow_flag() ) {
		return false;
	}
	$id = get_queried_object_id();
	return dejoiy_studio_is_customizable_product( $id );
}

/**
 * Use studio cart template?
 *
 * @return bool
 */
function dejoiy_studio_use_cart_template() {
	return function_exists( 'is_cart' ) && is_cart() && ! is_wc_endpoint_url() && dejoiy_studio_request_has_flow_flag();
}

/**
 * Is a dedicated Custom Studio screen (studio header/footer shown)?
 *
 * @return bool
 */
function dejoiy_studio_is_screen() {
	if ( defined( 'DEJOIY_STUDIO_PAGE_ID' ) && is_page( DEJOIY_STUDIO_PAGE_ID ) ) {
		return true;
	}
	if ( dejoiy_studio_use_single_template() ) {
		return true;
	}
	if ( dejoiy_studio_use_cart_template() ) {
		return true;
	}
	if ( function_exists( 'dejoiy_studio_use_checkout_template' ) && dejoiy_studio_use_checkout_template() ) {
		return true;
	}
	return false;
}

/**
 * Template swap: single product + cart + checkout.
 *
 * @param string $template Path.
 * @return string
 */
function dejoiy_studio_template_include( $template ) {
	if ( dejoiy_studio_use_single_template() ) {
		$custom = get_stylesheet_directory() . '/studio-single-product.php';
		if ( file_exists( $custom ) ) {
			return $custom;
		}
	}
	if ( dejoiy_studio_use_cart_template() ) {
		$cart = get_stylesheet_directory() . '/studio-cart.php';
		if ( file_exists( $cart ) ) {
			return $cart;
		}
	}
	if ( function_exists( 'dejoiy_studio_use_checkout_template' ) && dejoiy_studio_use_checkout_template() ) {
		$checkout = get_stylesheet_directory() . '/studio-checkout.php';
		if ( file_exists( $checkout ) ) {
			return $checkout;
		}
	}
	return $template;
}
add_filter( 'template_include', 'dejoiy_studio_template_include', 99 );

/**
 * Persist studio param in add-to-cart.
 *
 * @param string $url Add to cart URL.
 * @return string
 */
function dejoiy_studio_add_to_cart_url( $url ) {
	if ( ! dejoiy_studio_should_use_studio_urls() ) {
		return $url;
	}
	return add_query_arg( 'dejoiy_studio', '1', $url );
}
add_filter( 'woocommerce_product_add_to_cart_url', 'dejoiy_studio_add_to_cart_url' );

/**
 * Checkout URL in studio flow.
 *
 * @param string $url Checkout URL.
 * @return string
 */
function dejoiy_studio_checkout_url( $url ) {
	if ( ! dejoiy_studio_should_use_studio_urls() ) {
		return $url;
	}
	return add_query_arg( 'dejoiy_studio', '1', $url );
}
add_filter( 'woocommerce_get_checkout_url', 'dejoiy_studio_checkout_url' );

/**
 * Cart URL in studio flow.
 *
 * @param string $url Cart URL.
 * @return string
 */
function dejoiy_studio_cart_url( $url ) {
	if ( ! dejoiy_studio_should_use_studio_urls() ) {
		return $url;
	}
	return add_query_arg( 'dejoiy_studio', '1', $url );
}
add_filter( 'woocommerce_get_cart_url', 'dejoiy_studio_cart_url' );

/**
 * Keep studio flow after add to cart.
 *
 * @param string $url Redirect URL.
 * @return string
 */
function dejoiy_studio_add_to_cart_redirect( $url ) {
	if ( ! dejoiy_studio_request_has_flow_flag() ) {
		return $url;
	}
	return add_query_arg( 'dejoiy_studio', '1', $url );
}
add_filter( 'woocommerce_add_to_cart_redirect', 'dejoiy_studio_add_to_cart_redirect', 15 );

$dejoiy_studio_customize = get_stylesheet_directory() . '/studio-customize.php';
if ( is_readable( $dejoiy_studio_customize ) ) {
	require_once $dejoiy_studio_customize;
}

/**
 * AJAX search — custom studio products + marketplace pages only (no general catalog).
 */
if ( ! function_exists( 'dejoiy_studio_search_handler' ) ) {
function dejoiy_studio_search_handler() {
	$term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( strlen( $term ) < 2 ) {
		wp_send_json( array() );
	}

	$out   = array();
	$seen  = array();
	$push  = function ( $row ) use ( &$out, &$seen ) {
		$key = $row['type'] . '-' . $row['id'];
		if ( isset( $seen[ $key ] ) ) {
			return;
		}
		$seen[ $key ] = true;
		$out[]        = $row;
	};

	$studio_q = new WP_Query(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			's'              => $term,
			'posts_per_page' => 10,
			'no_found_rows'  => true,
			'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'term_id',
					'terms'    => DEJOIY_STUDIO_CAT_IDS,
					'operator' => 'IN',
				),
			),
		)
	);

	foreach ( $studio_q->posts as $p ) {
		$pr = function_exists( 'wc_get_product' ) ? wc_get_product( $p->ID ) : null;
		$push(
			array(
				'id'    => $p->ID,
				'title' => html_entity_decode( get_the_title( $p->ID ), ENT_QUOTES ),
				'url'   => dejoiy_studio_product_url( $p->ID ),
				'thumb' => (string) ( get_the_post_thumbnail_url( $p->ID, 'thumbnail' ) ?: '' ),
				'price' => $pr ? wp_strip_all_tags( $pr->get_price_html() ) : '',
				'type'  => 'studio-product',
				'badge' => 'Studio',
			)
		);
	}
	wp_reset_postdata();

	$page_exclude = array();
	if ( defined( 'DEJOIY_STUDIO_PAGE_ID' ) ) {
		$page_exclude[] = (int) DEJOIY_STUDIO_PAGE_ID;
	}

	$page_q = new WP_Query(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			's'              => $term,
			'posts_per_page' => 8,
			'no_found_rows'  => true,
			'post__not_in'   => $page_exclude,
		)
	);
	foreach ( $page_q->posts as $p ) {
		$push(
			array(
				'id'    => $p->ID,
				'title' => html_entity_decode( get_the_title( $p->ID ), ENT_QUOTES ),
				'url'   => get_permalink( $p->ID ),
				'thumb' => '',
				'price' => '',
				'type'  => 'page',
				'badge' => 'Page',
			)
		);
	}
	wp_reset_postdata();

	wp_send_json( $out );
}
	add_action( 'wp_ajax_dsu_search', 'dejoiy_studio_search_handler' );
	add_action( 'wp_ajax_nopriv_dsu_search', 'dejoiy_studio_search_handler' );
}
