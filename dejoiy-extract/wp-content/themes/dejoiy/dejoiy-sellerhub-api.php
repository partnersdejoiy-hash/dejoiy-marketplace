<?php
/**
 * DEJOIY SellerHub — REST data layer (WooCommerce + WCFM, vendor-scoped).
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return int
 */
function dejoiy_sellerhub_vendor_id() {
	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		return 0;
	}
	if ( function_exists( 'wcfm_is_vendor' ) && wcfm_is_vendor( $user_id ) ) {
		return (int) $user_id;
	}
	if ( user_can( $user_id, 'manage_woocommerce' ) && ! empty( $_GET['dejoiy_vendor_preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return absint( $_GET['dejoiy_vendor_preview'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}
	return 0;
}

/**
 * @return bool
 */
function dejoiy_sellerhub_can_access() {
	return dejoiy_sellerhub_vendor_id() > 0;
}

/**
 * Product IDs owned by vendor.
 *
 * @param int $vendor_id Vendor user ID.
 * @return int[]
 */
function dejoiy_sellerhub_vendor_product_ids( $vendor_id ) {
	$vendor_id = absint( $vendor_id );
	if ( ! $vendor_id ) {
		return array();
	}
	$ids = get_posts(
		array(
			'post_type'              => 'product',
			'post_status'            => array( 'publish', 'pending', 'draft', 'private' ),
			'author'                 => $vendor_id,
			'posts_per_page'         => -1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);
	return array_map( 'absint', (array) $ids );
}

/**
 * Service product IDs (DEJOIY services categories).
 *
 * @param int $vendor_id Vendor user ID.
 * @return int[]
 */
function dejoiy_sellerhub_vendor_service_ids( $vendor_id ) {
	$all = dejoiy_sellerhub_vendor_product_ids( $vendor_id );
	if ( empty( $all ) ) {
		return array();
	}
	$slugs = array( 'services-marketplace', 'web-development', 'graphic-design', 'digital-marketing', 'content-writing' );
	$out   = array();
	foreach ( $all as $pid ) {
		$terms = wp_get_post_terms( $pid, 'product_cat', array( 'fields' => 'slugs' ) );
		if ( is_wp_error( $terms ) ) {
			continue;
		}
		foreach ( $terms as $slug ) {
			if ( in_array( $slug, $slugs, true ) ) {
				$out[] = $pid;
				break;
			}
		}
	}
	return $out;
}

/**
 * Order IDs containing vendor products.
 *
 * @param int   $vendor_id Vendor user ID.
 * @param array $args      status, limit, offset.
 * @return int[]
 */
function dejoiy_sellerhub_vendor_order_ids( $vendor_id, $args = array() ) {
	$vendor_id = absint( $vendor_id );
	$pids      = dejoiy_sellerhub_vendor_product_ids( $vendor_id );
	if ( empty( $pids ) ) {
		return array();
	}

	global $wpdb;
	$table = $wpdb->prefix . 'wc_order_product_lookup';
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
		return dejoiy_sellerhub_vendor_order_ids_fallback( $vendor_id, $pids, $args );
	}

	$placeholders = implode( ',', array_fill( 0, count( $pids ), '%d' ) );
	$sql          = "SELECT DISTINCT order_id FROM {$table} WHERE product_id IN ({$placeholders})"; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$prepared     = $wpdb->get_col( $wpdb->prepare( $sql, $pids ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

	$order_ids = array_map( 'absint', (array) $prepared );
	if ( empty( $order_ids ) ) {
		return array();
	}

	$query_args = array(
		'limit'   => isset( $args['limit'] ) ? absint( $args['limit'] ) : 50,
		'offset'  => isset( $args['offset'] ) ? absint( $args['offset'] ) : 0,
		'orderby' => 'date',
		'order'   => 'DESC',
		'return'  => 'ids',
		'type'    => 'shop_order',
	);

	if ( ! empty( $args['status'] ) ) {
		$query_args['status'] = sanitize_key( $args['status'] );
	}

	$query_args['post__in'] = $order_ids;

	return wc_get_orders( $query_args );
}

/**
 * Fallback when lookup table missing.
 *
 * @param int   $vendor_id Vendor ID.
 * @param int[] $pids      Product IDs.
 * @param array $args      Query args.
 * @return int[]
 */
function dejoiy_sellerhub_vendor_order_ids_fallback( $vendor_id, $pids, $args ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	$found = array();
	$limit = isset( $args['limit'] ) ? absint( $args['limit'] ) : 50;
	$page  = isset( $args['offset'] ) ? (int) floor( absint( $args['offset'] ) / max( 1, $limit ) ) + 1 : 1;

	$orders = wc_get_orders(
		array(
			'limit'   => min( 100, $limit * 3 ),
			'page'    => $page,
			'orderby' => 'date',
			'order'   => 'DESC',
			'status'  => ! empty( $args['status'] ) ? sanitize_key( $args['status'] ) : array_keys( wc_get_order_statuses() ),
			'return'  => 'objects',
		)
	);

	foreach ( $orders as $order ) {
		if ( ! $order instanceof WC_Order ) {
			continue;
		}
		foreach ( $order->get_items() as $item ) {
			$pid = (int) $item->get_product_id();
			if ( in_array( $pid, $pids, true ) ) {
				$found[] = $order->get_id();
				break;
			}
		}
		if ( count( $found ) >= $limit ) {
			break;
		}
	}

	return array_values( array_unique( $found ) );
}

/**
 * Serialize product for API.
 *
 * @param WC_Product $product Product.
 * @return array<string,mixed>
 */
function dejoiy_sellerhub_format_product( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return array();
	}
	$img_id = $product->get_image_id();
	$thumb  = $img_id ? wp_get_attachment_image_url( $img_id, 'thumbnail' ) : wc_placeholder_img_src( 'thumbnail' );
	$cats   = wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'names' ) );
	return array(
		'id'            => $product->get_id(),
		'name'          => $product->get_name(),
		'sku'           => $product->get_sku(),
		'status'        => $product->get_status(),
		'price'         => (float) $product->get_price(),
		'regular_price' => (float) $product->get_regular_price(),
		'stock_quantity'=> $product->managing_stock() ? $product->get_stock_quantity() : null,
		'stock_status'  => $product->get_stock_status(),
		'manage_stock'  => $product->get_manage_stock(),
		'image'         => $thumb ? (string) $thumb : '',
		'permalink'     => get_permalink( $product->get_id() ),
		'categories'    => is_wp_error( $cats ) ? array() : array_values( (array) $cats ),
		'date_created'  => $product->get_date_created() ? $product->get_date_created()->date( 'c' ) : '',
		'total_sales'   => (int) $product->get_total_sales(),
	);
}

/**
 * Serialize order for API.
 *
 * @param WC_Order $order Order.
 * @param int      $vendor_id Vendor ID.
 * @return array<string,mixed>
 */
function dejoiy_sellerhub_format_order( $order, $vendor_id ) {
	if ( ! $order instanceof WC_Order ) {
		return array();
	}
	$pids    = dejoiy_sellerhub_vendor_product_ids( $vendor_id );
	$lines   = array();
	$total   = 0.0;
	foreach ( $order->get_items() as $item ) {
		$pid = (int) $item->get_product_id();
		if ( ! in_array( $pid, $pids, true ) ) {
			continue;
		}
		$sub = (float) $item->get_total();
		$total += $sub;
		$lines[] = array(
			'product_id' => $pid,
			'name'       => $item->get_name(),
			'quantity'   => (int) $item->get_quantity(),
			'total'      => $sub,
		);
	}
	return array(
		'id'           => $order->get_id(),
		'number'       => $order->get_order_number(),
		'status'       => $order->get_status(),
		'status_label' => wc_get_order_status_name( $order->get_status() ),
		'date'         => $order->get_date_created() ? $order->get_date_created()->date( 'c' ) : '',
		'customer'     => trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ),
		'email'        => $order->get_billing_email(),
		'vendor_total' => round( $total, 2 ),
		'currency'     => $order->get_currency(),
		'line_items'   => $lines,
		'view_url'     => $order->get_view_order_url(),
	);
}

/**
 * Dashboard aggregates for vendor.
 *
 * @param int $vendor_id Vendor ID.
 * @return array<string,mixed>
 */
function dejoiy_sellerhub_dashboard_stats( $vendor_id ) {
	$vendor_id = absint( $vendor_id );
	$pids      = dejoiy_sellerhub_vendor_product_ids( $vendor_id );
	$active    = count(
		get_posts(
			array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'author'         => $vendor_id,
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		)
	);

	$order_ids = dejoiy_sellerhub_vendor_order_ids( $vendor_id, array( 'limit' => 500 ) );
	$revenue   = 0.0;
	$pending   = 0;
	$completed = 0;

	foreach ( $order_ids as $oid ) {
		$order = wc_get_order( $oid );
		if ( ! $order ) {
			continue;
		}
		$formatted = dejoiy_sellerhub_format_order( $order, $vendor_id );
		$revenue  += (float) ( $formatted['vendor_total'] ?? 0 );
		$st = $order->get_status();
		if ( in_array( $st, array( 'pending', 'on-hold', 'processing' ), true ) ) {
			++$pending;
		}
		if ( in_array( $st, array( 'completed' ), true ) ) {
			++$completed;
		}
	}

	$low_stock = 0;
	foreach ( $pids as $pid ) {
		$product = wc_get_product( $pid );
		if ( ! $product || ! $product->managing_stock() ) {
			continue;
		}
		$qty = $product->get_stock_quantity();
		if ( null !== $qty && (int) $qty <= (int) get_option( 'woocommerce_notify_low_stock_amount', 2 ) ) {
			++$low_stock;
		}
	}

	$views = 0;
	foreach ( array_slice( $pids, 0, 50 ) as $pid ) {
		$views += (int) get_post_meta( $pid, 'total_sales', true );
	}
	$conversion = $active > 0 && count( $order_ids ) > 0
		? round( ( count( $order_ids ) / max( 1, $views ) ) * 100, 2 )
		: 0.0;

	return array(
		'total_revenue'    => round( $revenue, 2 ),
		'total_orders'     => count( $order_ids ),
		'active_products'  => $active,
		'conversion_rate'  => min( 100, $conversion ),
		'pending_orders'   => $pending,
		'low_stock_count'  => $low_stock,
		'completed_orders' => $completed,
	);
}

/**
 * Sales chart series (last N days).
 *
 * @param int $vendor_id Vendor ID.
 * @param int $days      Days.
 * @return array<int,array{date:string,revenue:float,orders:int}>
 */
function dejoiy_sellerhub_sales_series( $vendor_id, $days = 30 ) {
	$days      = max( 7, min( 90, absint( $days ) ) );
	$order_ids = dejoiy_sellerhub_vendor_order_ids( $vendor_id, array( 'limit' => 300 ) );
	$series    = array();

	for ( $i = $days - 1; $i >= 0; $i-- ) {
		$key            = gmdate( 'Y-m-d', strtotime( "-{$i} days" ) );
		$series[ $key ] = array(
			'date'    => $key,
			'revenue' => 0.0,
			'orders'  => 0,
		);
	}

	foreach ( $order_ids as $oid ) {
		$order = wc_get_order( $oid );
		if ( ! $order || ! $order->get_date_created() ) {
			continue;
		}
		$key = $order->get_date_created()->date( 'Y-m-d' );
		if ( ! isset( $series[ $key ] ) ) {
			continue;
		}
		$formatted = dejoiy_sellerhub_format_order( $order, $vendor_id );
		$series[ $key ]['revenue'] += (float) ( $formatted['vendor_total'] ?? 0 );
		++$series[ $key ]['orders'];
	}

	return array_values( $series );
}

/**
 * Earnings / commission from WCFM marketplace table when available.
 *
 * @param int $vendor_id Vendor ID.
 * @return array<string,mixed>
 */
function dejoiy_sellerhub_earnings( $vendor_id ) {
	$vendor_id = absint( $vendor_id );
	$total     = 0.0;
	$monthly   = array();
	$history   = array();

	global $wpdb;
	$table = $wpdb->prefix . 'wcfm_marketplace_orders';
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT order_id, product_id, item_total, commission_amount, created FROM {$table} WHERE vendor_id = %d ORDER BY created DESC LIMIT 200", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$vendor_id
			),
			ARRAY_A
		);
		foreach ( (array) $rows as $row ) {
			$comm   = (float) ( $row['commission_amount'] ?? 0 );
			$total += $comm;
			$month  = ! empty( $row['created'] ) ? gmdate( 'Y-m', strtotime( $row['created'] ) ) : gmdate( 'Y-m' );
			if ( ! isset( $monthly[ $month ] ) ) {
				$monthly[ $month ] = 0.0;
			}
			$monthly[ $month ] += $comm;
			$history[] = array(
				'order_id'   => (int) ( $row['order_id'] ?? 0 ),
				'product_id' => (int) ( $row['product_id'] ?? 0 ),
				'commission' => round( $comm, 2 ),
				'item_total' => round( (float) ( $row['item_total'] ?? 0 ), 2 ),
				'date'       => ! empty( $row['created'] ) ? gmdate( 'c', strtotime( $row['created'] ) ) : '',
			);
		}
	} else {
		$stats = dejoiy_sellerhub_dashboard_stats( $vendor_id );
		$total = (float) ( $stats['total_revenue'] ?? 0 );
		$monthly[ gmdate( 'Y-m' ) ] = $total;
	}

	$withdrawals = array();
	if ( function_exists( 'get_wcfm_vendor_withdrawals' ) ) {
		$raw = get_wcfm_vendor_withdrawals( $vendor_id );
		if ( is_array( $raw ) ) {
			foreach ( $raw as $w ) {
				$withdrawals[] = array(
					'id'     => isset( $w->ID ) ? (int) $w->ID : 0,
					'amount' => isset( $w->withdrawal_amount ) ? (float) $w->withdrawal_amount : 0,
					'status' => isset( $w->withdrawal_status ) ? (string) $w->withdrawal_status : '',
					'date'   => isset( $w->apply_date ) ? (string) $w->apply_date : '',
				);
			}
		}
	}

	$balance = (float) get_user_meta( $vendor_id, '_wcfm_withdrawable_amount', true );
	if ( ! $balance && function_exists( 'wcfm_get_vendor_store_withdrawal_balance' ) ) {
		$balance = (float) wcfm_get_vendor_store_withdrawal_balance( $vendor_id );
	}

	return array(
		'total_commission' => round( $total, 2 ),
		'withdrawable'     => round( $balance, 2 ),
		'monthly'          => $monthly,
		'history'          => array_slice( $history, 0, 50 ),
		'withdrawals'      => $withdrawals,
	);
}

/**
 * Recent activity feed.
 *
 * @param int $vendor_id Vendor ID.
 * @return array<int,array<string,mixed>>
 */
function dejoiy_sellerhub_activity_feed( $vendor_id, $limit = 15 ) {
	$feed = array();
	$oids = dejoiy_sellerhub_vendor_order_ids( $vendor_id, array( 'limit' => $limit ) );
	foreach ( $oids as $oid ) {
		$order = wc_get_order( $oid );
		if ( ! $order ) {
			continue;
		}
		$feed[] = array(
			'type'    => 'order',
			'id'      => $oid,
			'title'   => sprintf( /* translators: %s order number */ __( 'Order #%s', 'dejoiy' ), $order->get_order_number() ),
			'status'  => $order->get_status(),
			'amount'  => dejoiy_sellerhub_format_order( $order, $vendor_id )['vendor_total'] ?? 0,
			'date'    => $order->get_date_created() ? $order->get_date_created()->date( 'c' ) : '',
		);
	}
	return $feed;
}

/**
 * Vendor profile payload.
 *
 * @param int $vendor_id Vendor ID.
 * @return array<string,mixed>
 */
function dejoiy_sellerhub_vendor_profile( $vendor_id ) {
	$user = get_userdata( $vendor_id );
	if ( ! $user ) {
		return array();
	}
	$store_name = get_user_meta( $vendor_id, 'store_name', true );
	if ( ! $store_name && function_exists( 'wcfm_get_vendor_store_name' ) ) {
		$store_name = wcfm_get_vendor_store_name( $vendor_id );
	}
	$store_url = function_exists( 'wcfmmp_get_store_url' ) ? wcfmmp_get_store_url( $vendor_id ) : '';
	$avatar    = get_avatar_url( $vendor_id, array( 'size' => 96 ) );
	return array(
		'id'          => $vendor_id,
		'display_name'=> $user->display_name,
		'email'       => $user->user_email,
		'store_name'  => $store_name ? (string) $store_name : $user->display_name,
		'store_url'   => $store_url ? (string) $store_url : '',
		'avatar'      => $avatar ? (string) $avatar : '',
		'wcfm_url'    => function_exists( 'get_wcfm_page' ) ? (string) get_wcfm_page() : home_url( '/store-manager/' ),
	);
}

/**
 * Top products by sales.
 *
 * @param int $vendor_id Vendor ID.
 * @param int $limit     Limit.
 * @return array<int,array<string,mixed>>
 */
function dejoiy_sellerhub_top_products( $vendor_id, $limit = 10 ) {
	$pids = dejoiy_sellerhub_vendor_product_ids( $vendor_id );
	$rows = array();
	foreach ( $pids as $pid ) {
		$product = wc_get_product( $pid );
		if ( ! $product ) {
			continue;
		}
		$rows[] = array(
			'id'    => $pid,
			'name'  => $product->get_name(),
			'sales' => (int) $product->get_total_sales(),
			'price' => (float) $product->get_price(),
		);
	}
	usort(
		$rows,
		static function ( $a, $b ) {
			return ( $b['sales'] ?? 0 ) <=> ( $a['sales'] ?? 0 );
		}
	);
	return array_slice( $rows, 0, $limit );
}

/**
 * Seller search (products + orders).
 *
 * @param int    $vendor_id Vendor ID.
 * @param string $q         Query.
 * @return array<string,mixed>
 */
function dejoiy_sellerhub_search( $vendor_id, $q ) {
	$q = sanitize_text_field( $q );
	if ( strlen( $q ) < 2 ) {
		return array( 'products' => array(), 'orders' => array() );
	}
	$products = array();
	$query    = new WP_Query(
		array(
			'post_type'      => 'product',
			'author'         => $vendor_id,
			's'              => $q,
			'posts_per_page' => 10,
			'post_status'    => array( 'publish', 'draft', 'pending' ),
		)
	);
	while ( $query->have_posts() ) {
		$query->the_post();
		$p = wc_get_product( get_the_ID() );
		if ( $p ) {
			$products[] = dejoiy_sellerhub_format_product( $p );
		}
	}
	wp_reset_postdata();

	$orders = array();
	foreach ( dejoiy_sellerhub_vendor_order_ids( $vendor_id, array( 'limit' => 30 ) ) as $oid ) {
		$order = wc_get_order( $oid );
		if ( ! $order ) {
			continue;
		}
		if ( false === stripos( (string) $order->get_order_number(), $q ) && false === stripos( $order->get_billing_email(), $q ) ) {
			continue;
		}
		$orders[] = dejoiy_sellerhub_format_order( $order, $vendor_id );
		if ( count( $orders ) >= 10 ) {
			break;
		}
	}

	return array(
		'products' => $products,
		'orders'   => $orders,
	);
}
