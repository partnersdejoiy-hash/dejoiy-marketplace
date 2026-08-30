<?php
/**
 * DEJOIY SellerHub — WordPress bridge (REST + safe redirects).
 *
 * Separate layer: does not modify WooCommerce core, WCFM, cart, or marketplace pages.
 * Disable: define( 'DEJOIY_SELLERHUB_DISABLED', true );
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'DEJOIY_SELLERHUB_DISABLED' ) && DEJOIY_SELLERHUB_DISABLED ) {
	return;
}

$dejoiy_sellerhub_api = get_stylesheet_directory() . '/dejoiy-sellerhub-api.php';
if ( is_readable( $dejoiy_sellerhub_api ) ) {
	require_once $dejoiy_sellerhub_api;
}

/**
 * Allowed CORS origins for SellerHub frontend.
 *
 * @return string[]
 */
function dejoiy_sellerhub_allowed_origins() {
	$defaults = array(
		'https://sellerhub.dejoiy.tech',
		'http://localhost:3000',
		'http://127.0.0.1:3000',
	);
	if ( defined( 'DEJOIY_SELLERHUB_ORIGINS' ) && is_array( DEJOIY_SELLERHUB_ORIGINS ) ) {
		$defaults = array_merge( $defaults, DEJOIY_SELLERHUB_ORIGINS );
	}
	return array_unique( array_filter( array_map( 'esc_url_raw', $defaults ) ) );
}

/**
 * REST permission — WCFM vendors only.
 *
 * @return bool|\WP_Error
 */
function dejoiy_sellerhub_rest_permission() {
	if ( ! is_user_logged_in() ) {
		return new WP_Error( 'dejoiy_sellerhub_auth', __( 'Seller login required.', 'dejoiy' ), array( 'status' => 401 ) );
	}
	if ( ! dejoiy_sellerhub_can_access() ) {
		return new WP_Error( 'dejoiy_sellerhub_forbidden', __( 'WCFM vendor access only.', 'dejoiy' ), array( 'status' => 403 ) );
	}
	return true;
}

/**
 * Register REST routes.
 */
function dejoiy_sellerhub_register_routes() {
	$ns = 'dejoiy/sellerhub/v1';

	register_rest_route(
		$ns,
		'/me',
		array(
			'methods'             => 'GET',
			'callback'            => 'dejoiy_sellerhub_rest_me',
			'permission_callback' => 'dejoiy_sellerhub_rest_permission',
		)
	);

	register_rest_route(
		$ns,
		'/dashboard',
		array(
			'methods'             => 'GET',
			'callback'            => 'dejoiy_sellerhub_rest_dashboard',
			'permission_callback' => 'dejoiy_sellerhub_rest_permission',
		)
	);

	register_rest_route(
		$ns,
		'/products',
		array(
			'methods'             => 'GET',
			'callback'            => 'dejoiy_sellerhub_rest_products',
			'permission_callback' => 'dejoiy_sellerhub_rest_permission',
			'args'                => array(
				'page'     => array( 'default' => 1, 'sanitize_callback' => 'absint' ),
				'per_page' => array( 'default' => 20, 'sanitize_callback' => 'absint' ),
				'search'   => array( 'sanitize_callback' => 'sanitize_text_field' ),
				'status'   => array( 'sanitize_callback' => 'sanitize_key' ),
			),
		)
	);

	register_rest_route(
		$ns,
		'/products/(?P<id>\d+)',
		array(
			'methods'             => array( 'GET', 'POST', 'DELETE' ),
			'callback'            => 'dejoiy_sellerhub_rest_product',
			'permission_callback' => 'dejoiy_sellerhub_rest_permission',
			'args'                => array(
				'id' => array( 'sanitize_callback' => 'absint' ),
			),
		)
	);

	register_rest_route(
		$ns,
		'/orders',
		array(
			'methods'             => 'GET',
			'callback'            => 'dejoiy_sellerhub_rest_orders',
			'permission_callback' => 'dejoiy_sellerhub_rest_permission',
			'args'                => array(
				'page'     => array( 'default' => 1, 'sanitize_callback' => 'absint' ),
				'per_page' => array( 'default' => 20, 'sanitize_callback' => 'absint' ),
				'status'   => array( 'sanitize_callback' => 'sanitize_key' ),
			),
		)
	);

	register_rest_route(
		$ns,
		'/orders/(?P<id>\d+)',
		array(
			'methods'             => 'GET',
			'callback'            => 'dejoiy_sellerhub_rest_order',
			'permission_callback' => 'dejoiy_sellerhub_rest_permission',
		)
	);

	register_rest_route(
		$ns,
		'/analytics',
		array(
			'methods'             => 'GET',
			'callback'            => 'dejoiy_sellerhub_rest_analytics',
			'permission_callback' => 'dejoiy_sellerhub_rest_permission',
			'args'                => array(
				'days' => array( 'default' => 30, 'sanitize_callback' => 'absint' ),
			),
		)
	);

	register_rest_route(
		$ns,
		'/earnings',
		array(
			'methods'             => 'GET',
			'callback'            => 'dejoiy_sellerhub_rest_earnings',
			'permission_callback' => 'dejoiy_sellerhub_rest_permission',
		)
	);

	register_rest_route(
		$ns,
		'/inventory',
		array(
			'methods'             => 'GET',
			'callback'            => 'dejoiy_sellerhub_rest_inventory',
			'permission_callback' => 'dejoiy_sellerhub_rest_permission',
		)
	);

	register_rest_route(
		$ns,
		'/services',
		array(
			'methods'             => 'GET',
			'callback'            => 'dejoiy_sellerhub_rest_services',
			'permission_callback' => 'dejoiy_sellerhub_rest_permission',
		)
	);

	register_rest_route(
		$ns,
		'/notifications',
		array(
			'methods'             => 'GET',
			'callback'            => 'dejoiy_sellerhub_rest_notifications',
			'permission_callback' => 'dejoiy_sellerhub_rest_permission',
		)
	);

	register_rest_route(
		$ns,
		'/search',
		array(
			'methods'             => 'GET',
			'callback'            => 'dejoiy_sellerhub_rest_search',
			'permission_callback' => 'dejoiy_sellerhub_rest_permission',
			'args'                => array(
				'q' => array( 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ),
			),
		)
	);

	register_rest_route(
		$ns,
		'/settings',
		array(
			'methods'             => array( 'GET', 'POST' ),
			'callback'            => 'dejoiy_sellerhub_rest_settings',
			'permission_callback' => 'dejoiy_sellerhub_rest_permission',
		)
	);
}
add_action( 'rest_api_init', 'dejoiy_sellerhub_register_routes' );

/**
 * CORS for SellerHub API (credentials).
 *
 * @param bool             $served  Served.
 * @param WP_HTTP_Response $result  Result.
 * @param WP_REST_Request  $request Request.
 * @return bool
 */
function dejoiy_sellerhub_cors( $served, $result, $request ) {
	$route = $request->get_route();
	if ( strpos( $route, '/dejoiy/sellerhub/' ) !== 0 ) {
		return $served;
	}
	$origin = isset( $_SERVER['HTTP_ORIGIN'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_ORIGIN'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	if ( $origin && in_array( $origin, dejoiy_sellerhub_allowed_origins(), true ) ) {
		header( 'Access-Control-Allow-Origin: ' . $origin );
		header( 'Access-Control-Allow-Credentials: true' );
		header( 'Vary: Origin' );
	}
	if ( 'OPTIONS' === $request->get_method() ) {
		header( 'Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS' );
		header( 'Access-Control-Allow-Headers: Authorization, Content-Type, X-WP-Nonce' );
		exit;
	}
	return $served;
}
add_filter( 'rest_pre_serve_request', 'dejoiy_sellerhub_cors', 10, 3 );

/**
 * @return \WP_REST_Response|\WP_Error
 */
function dejoiy_sellerhub_rest_me() {
	$vid = dejoiy_sellerhub_vendor_id();
	return rest_ensure_response(
		array(
			'authenticated' => true,
			'vendor'        => dejoiy_sellerhub_vendor_profile( $vid ),
			'api_version'   => '1.0.0',
		)
	);
}

/**
 * @return \WP_REST_Response
 */
function dejoiy_sellerhub_rest_dashboard( WP_REST_Request $request ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	$vid = dejoiy_sellerhub_vendor_id();
	return rest_ensure_response(
		array(
			'stats'    => dejoiy_sellerhub_dashboard_stats( $vid ),
			'series'   => dejoiy_sellerhub_sales_series( $vid, 30 ),
			'activity' => dejoiy_sellerhub_activity_feed( $vid, 12 ),
			'pending'  => dejoiy_sellerhub_pending_orders( $vid, 8 ),
			'low_stock'=> dejoiy_sellerhub_low_stock_items( $vid, 8 ),
		)
	);
}

/**
 * @param int $vendor_id Vendor ID.
 * @param int $limit     Limit.
 * @return array<int,array<string,mixed>>
 */
function dejoiy_sellerhub_pending_orders( $vendor_id, $limit = 8 ) {
	$out = array();
	foreach ( dejoiy_sellerhub_vendor_order_ids( $vendor_id, array( 'limit' => 50, 'status' => 'processing' ) ) as $oid ) {
		$order = wc_get_order( $oid );
		if ( $order ) {
			$out[] = dejoiy_sellerhub_format_order( $order, $vendor_id );
		}
		if ( count( $out ) >= $limit ) {
			break;
		}
	}
	if ( count( $out ) < $limit ) {
		foreach ( dejoiy_sellerhub_vendor_order_ids( $vendor_id, array( 'limit' => 50, 'status' => 'pending' ) ) as $oid ) {
			$order = wc_get_order( $oid );
			if ( $order ) {
				$out[] = dejoiy_sellerhub_format_order( $order, $vendor_id );
			}
			if ( count( $out ) >= $limit ) {
				break;
			}
		}
	}
	return $out;
}

/**
 * @param int $vendor_id Vendor ID.
 * @param int $limit     Limit.
 * @return array<int,array<string,mixed>>
 */
function dejoiy_sellerhub_low_stock_items( $vendor_id, $limit = 8 ) {
	$out  = array();
	$th   = (int) get_option( 'woocommerce_notify_low_stock_amount', 2 );
	$pids = dejoiy_sellerhub_vendor_product_ids( $vendor_id );
	foreach ( $pids as $pid ) {
		$product = wc_get_product( $pid );
		if ( ! $product || ! $product->managing_stock() ) {
			continue;
		}
		$qty = $product->get_stock_quantity();
		if ( null !== $qty && (int) $qty <= $th ) {
			$out[] = dejoiy_sellerhub_format_product( $product );
		}
		if ( count( $out ) >= $limit ) {
			break;
		}
	}
	return $out;
}

/**
 * @param WP_REST_Request $request Request.
 * @return \WP_REST_Response
 */
function dejoiy_sellerhub_rest_products( WP_REST_Request $request ) {
	$vid      = dejoiy_sellerhub_vendor_id();
	$page     = max( 1, (int) $request->get_param( 'page' ) );
	$per_page = min( 50, max( 1, (int) $request->get_param( 'per_page' ) ) );
	$search   = (string) $request->get_param( 'search' );
	$status   = (string) $request->get_param( 'status' );

	$statuses = array( 'publish', 'draft', 'pending', 'private' );
	if ( $status && in_array( $status, $statuses, true ) ) {
		$statuses = array( $status );
	}

	$q = new WP_Query(
		array(
			'post_type'      => 'product',
			'author'         => $vid,
			'post_status'    => $statuses,
			'posts_per_page' => $per_page,
			'paged'          => $page,
			's'              => $search ? $search : '',
		)
	);

	$items = array();
	while ( $q->have_posts() ) {
		$q->the_post();
		$p = wc_get_product( get_the_ID() );
		if ( $p ) {
			$items[] = dejoiy_sellerhub_format_product( $p );
		}
	}
	wp_reset_postdata();

	return rest_ensure_response(
		array(
			'items'       => $items,
			'total'       => (int) $q->found_posts,
			'page'        => $page,
			'per_page'    => $per_page,
			'total_pages' => (int) $q->max_num_pages,
		)
	);
}

/**
 * @param WP_REST_Request $request Request.
 * @return \WP_REST_Response|\WP_Error
 */
function dejoiy_sellerhub_rest_product( WP_REST_Request $request ) {
	$vid = dejoiy_sellerhub_vendor_id();
	$id  = (int) $request['id'];
	$product = wc_get_product( $id );
	if ( ! $product || (int) get_post_field( 'post_author', $id ) !== $vid ) {
		return new WP_Error( 'not_found', __( 'Product not found.', 'dejoiy' ), array( 'status' => 404 ) );
	}

	$method = $request->get_method();
	if ( 'GET' === $method ) {
		return rest_ensure_response( dejoiy_sellerhub_format_product( $product ) );
	}

	if ( 'DELETE' === $method ) {
		wp_trash_post( $id );
		return rest_ensure_response( array( 'deleted' => true, 'id' => $id ) );
	}

	if ( 'POST' === $method ) {
		$params = $request->get_json_params();
		if ( ! is_array( $params ) ) {
			$params = array();
		}
		if ( isset( $params['name'] ) ) {
			$product->set_name( sanitize_text_field( $params['name'] ) );
		}
		if ( isset( $params['regular_price'] ) ) {
			$product->set_regular_price( wc_format_decimal( $params['regular_price'] ) );
		}
		if ( isset( $params['stock_quantity'] ) ) {
			$product->set_manage_stock( true );
			$product->set_stock_quantity( (int) $params['stock_quantity'] );
		}
		if ( isset( $params['stock_status'] ) ) {
			$product->set_stock_status( sanitize_key( $params['stock_status'] ) );
		}
		if ( isset( $params['status'] ) ) {
			wp_update_post(
				array(
					'ID'          => $id,
					'post_status' => sanitize_key( $params['status'] ),
				)
			);
		}
		$product->save();
		return rest_ensure_response( dejoiy_sellerhub_format_product( wc_get_product( $id ) ) );
	}

	return new WP_Error( 'method_not_allowed', __( 'Method not allowed.', 'dejoiy' ), array( 'status' => 405 ) );
}

/**
 * Create product (POST /products).
 */
function dejoiy_sellerhub_rest_create_product( WP_REST_Request $request ) {
	$vid    = dejoiy_sellerhub_vendor_id();
	$params = $request->get_json_params();
	if ( ! is_array( $params ) || empty( $params['name'] ) ) {
		return new WP_Error( 'invalid', __( 'Product name required.', 'dejoiy' ), array( 'status' => 400 ) );
	}
	$post_id = wp_insert_post(
		array(
			'post_type'   => 'product',
			'post_status' => isset( $params['status'] ) ? sanitize_key( $params['status'] ) : 'draft',
			'post_title'  => sanitize_text_field( $params['name'] ),
			'post_author' => $vid,
		),
		true
	);
	if ( is_wp_error( $post_id ) ) {
		return $post_id;
	}
	$product = wc_get_product( $post_id );
	if ( ! $product ) {
		return new WP_Error( 'fail', __( 'Could not create product.', 'dejoiy' ), array( 'status' => 500 ) );
	}
	if ( isset( $params['regular_price'] ) ) {
		$product->set_regular_price( wc_format_decimal( $params['regular_price'] ) );
	}
	$product->save();
	return rest_ensure_response( dejoiy_sellerhub_format_product( $product ) );
}

add_action(
	'rest_api_init',
	static function () {
		register_rest_route(
			'dejoiy/sellerhub/v1',
			'/products/create',
			array(
				'methods'             => 'POST',
				'callback'            => 'dejoiy_sellerhub_rest_create_product',
				'permission_callback' => 'dejoiy_sellerhub_rest_permission',
			)
		);
	}
);

/**
 * @param WP_REST_Request $request Request.
 * @return \WP_REST_Response
 */
function dejoiy_sellerhub_rest_orders( WP_REST_Request $request ) {
	$vid      = dejoiy_sellerhub_vendor_id();
	$page     = max( 1, (int) $request->get_param( 'page' ) );
	$per_page = min( 50, max( 1, (int) $request->get_param( 'per_page' ) ) );
	$status   = (string) $request->get_param( 'status' );

	$args = array(
		'limit'  => $per_page,
		'offset' => ( $page - 1 ) * $per_page,
	);
	if ( $status ) {
		$args['status'] = $status;
	}

	$ids   = dejoiy_sellerhub_vendor_order_ids( $vid, $args );
	$items = array();
	foreach ( $ids as $oid ) {
		$order = wc_get_order( $oid );
		if ( $order ) {
			$items[] = dejoiy_sellerhub_format_order( $order, $vid );
		}
	}

	return rest_ensure_response(
		array(
			'items'    => $items,
			'page'     => $page,
			'per_page' => $per_page,
		)
	);
}

/**
 * @param WP_REST_Request $request Request.
 * @return \WP_REST_Response|\WP_Error
 */
function dejoiy_sellerhub_rest_order( WP_REST_Request $request ) {
	$vid   = dejoiy_sellerhub_vendor_id();
	$order = wc_get_order( (int) $request['id'] );
	if ( ! $order ) {
		return new WP_Error( 'not_found', __( 'Order not found.', 'dejoiy' ), array( 'status' => 404 ) );
	}
	$formatted = dejoiy_sellerhub_format_order( $order, $vid );
	if ( empty( $formatted['line_items'] ) ) {
		return new WP_Error( 'forbidden', __( 'Order not assigned to your store.', 'dejoiy' ), array( 'status' => 403 ) );
	}
	return rest_ensure_response( $formatted );
}

/**
 * @param WP_REST_Request $request Request.
 * @return \WP_REST_Response
 */
function dejoiy_sellerhub_rest_analytics( WP_REST_Request $request ) {
	$vid  = dejoiy_sellerhub_vendor_id();
	$days = (int) $request->get_param( 'days' );
	return rest_ensure_response(
		array(
			'series'        => dejoiy_sellerhub_sales_series( $vid, $days ),
			'top_products'  => dejoiy_sellerhub_top_products( $vid, 10 ),
			'stats'         => dejoiy_sellerhub_dashboard_stats( $vid ),
		)
	);
}

/**
 * @return \WP_REST_Response
 */
function dejoiy_sellerhub_rest_earnings() {
	$vid = dejoiy_sellerhub_vendor_id();
	return rest_ensure_response( dejoiy_sellerhub_earnings( $vid ) );
}

/**
 * @return \WP_REST_Response
 */
function dejoiy_sellerhub_rest_inventory() {
	$vid  = dejoiy_sellerhub_vendor_id();
	$items = array();
	foreach ( dejoiy_sellerhub_vendor_product_ids( $vid ) as $pid ) {
		$p = wc_get_product( $pid );
		if ( $p && $p->managing_stock() ) {
			$items[] = dejoiy_sellerhub_format_product( $p );
		}
	}
	return rest_ensure_response(
		array(
			'items'     => $items,
			'low_stock' => dejoiy_sellerhub_low_stock_items( $vid, 20 ),
		)
	);
}

/**
 * @return \WP_REST_Response
 */
function dejoiy_sellerhub_rest_services() {
	$vid = dejoiy_sellerhub_vendor_id();
	$ids = dejoiy_sellerhub_vendor_service_ids( $vid );
	$items = array();
	foreach ( $ids as $pid ) {
		$p = wc_get_product( $pid );
		if ( $p ) {
			$items[] = dejoiy_sellerhub_format_product( $p );
		}
	}
	$bookings = array();
	foreach ( dejoiy_sellerhub_vendor_order_ids( $vid, array( 'limit' => 30 ) ) as $oid ) {
		$order = wc_get_order( $oid );
		if ( $order && $order->get_meta( 'SERVICE_BOOKING' ) ) {
			$bookings[] = dejoiy_sellerhub_format_order( $order, $vid );
		}
	}
	return rest_ensure_response(
		array(
			'services' => $items,
			'bookings' => $bookings,
		)
	);
}

/**
 * @return \WP_REST_Response
 */
function dejoiy_sellerhub_rest_notifications() {
	$vid  = dejoiy_sellerhub_vendor_id();
	$feed = array();
	$stats = dejoiy_sellerhub_dashboard_stats( $vid );
	if ( (int) ( $stats['pending_orders'] ?? 0 ) > 0 ) {
		$feed[] = array(
			'type'    => 'orders',
			'message' => sprintf( /* translators: %d count */ __( '%d orders need attention', 'dejoiy' ), (int) $stats['pending_orders'] ),
			'level'   => 'warning',
		);
	}
	if ( (int) ( $stats['low_stock_count'] ?? 0 ) > 0 ) {
		$feed[] = array(
			'type'    => 'inventory',
			'message' => sprintf( /* translators: %d count */ __( '%d products are low on stock', 'dejoiy' ), (int) $stats['low_stock_count'] ),
			'level'   => 'alert',
		);
	}
	$feed = array_merge( $feed, dejoiy_sellerhub_activity_feed( $vid, 10 ) );
	return rest_ensure_response( array( 'items' => $feed ) );
}

/**
 * @param WP_REST_Request $request Request.
 * @return \WP_REST_Response
 */
function dejoiy_sellerhub_rest_search( WP_REST_Request $request ) {
	$vid = dejoiy_sellerhub_vendor_id();
	return rest_ensure_response( dejoiy_sellerhub_search( $vid, (string) $request->get_param( 'q' ) ) );
}

/**
 * @param WP_REST_Request $request Request.
 * @return \WP_REST_Response|\WP_Error
 */
function dejoiy_sellerhub_rest_settings( WP_REST_Request $request ) {
	$vid = dejoiy_sellerhub_vendor_id();
	if ( 'GET' === $request->get_method() ) {
		return rest_ensure_response(
			array(
				'profile'      => dejoiy_sellerhub_vendor_profile( $vid ),
				'notifications'=> array(
					'email_orders' => (bool) get_user_meta( $vid, '_dejoiy_sh_email_orders', true ),
					'email_stock'  => (bool) get_user_meta( $vid, '_dejoiy_sh_email_stock', true ),
				),
			)
		);
	}
	$params = $request->get_json_params();
	if ( is_array( $params ) && isset( $params['notifications'] ) && is_array( $params['notifications'] ) ) {
		update_user_meta( $vid, '_dejoiy_sh_email_orders', ! empty( $params['notifications']['email_orders'] ) );
		update_user_meta( $vid, '_dejoiy_sh_email_stock', ! empty( $params['notifications']['email_stock'] ) );
	}
	if ( is_array( $params ) && ! empty( $params['store_name'] ) ) {
		update_user_meta( $vid, 'store_name', sanitize_text_field( $params['store_name'] ) );
	}
	return rest_ensure_response(
		array(
			'profile'       => dejoiy_sellerhub_vendor_profile( $vid ),
			'notifications' => array(
				'email_orders' => (bool) get_user_meta( $vid, '_dejoiy_sh_email_orders', true ),
				'email_stock'  => (bool) get_user_meta( $vid, '_dejoiy_sh_email_stock', true ),
			),
		)
	);
}

/**
 * Optional: redirect /sellerhub/ on main site to external app (no theme breakage).
 */
function dejoiy_sellerhub_maybe_redirect() {
	if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}
	if ( ! is_page() ) {
		return;
	}
	$slug = get_post_field( 'post_name', get_queried_object_id() );
	if ( 'sellerhub' !== $slug ) {
		return;
	}
	$target = defined( 'DEJOIY_SELLERHUB_URL' ) ? DEJOIY_SELLERHUB_URL : 'https://sellerhub.dejoiy.tech';
	if ( ! headers_sent() ) {
		wp_safe_redirect( $target, 302 );
		exit;
	}
}
add_action( 'template_redirect', 'dejoiy_sellerhub_maybe_redirect', 5 );
