<?php
/**
 * DEJOIY Nexus LMS-lite — purchase access, reading progress, My Nexus shelf.
 *
 * No external Moodle install; integrates with WooCommerce orders only.
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User meta key: owned Nexus book product IDs.
 */
define( 'DEJOIY_LIBRARY_LMS_BOOKS_META', 'dejoiy_library_lms_books' );

/**
 * User meta prefix for per-book progress (0–100).
 */
define( 'DEJOIY_LIBRARY_LMS_PROGRESS_PREFIX', 'dejoiy_library_lms_progress_' );

/**
 * User meta key: product IDs hidden from the Nexus shelf UI (access retained).
 */
define( 'DEJOIY_LIBRARY_LMS_HIDDEN_META', 'dejoiy_library_lms_hidden' );

/**
 * @return bool
 */
function dejoiy_library_lms_is_download_only_product( $product_id ) {
	$product_id = (int) $product_id;
	if ( $product_id < 1 || ! function_exists( 'wc_get_product' ) ) {
		return false;
	}
	$product = wc_get_product( $product_id );
	if ( ! $product || ! $product->is_downloadable() ) {
		return false;
	}
	$files = $product->get_downloads();
	return ! empty( $files );
}

/**
 * @param int $product_id Product ID.
 * @return bool
 */
function dejoiy_library_lms_is_free_read_product( $product_id ) {
	$product_id = (int) $product_id;
	if ( function_exists( 'dejoiy_library_is_gutenberg_edition' ) && dejoiy_library_is_gutenberg_edition( $product_id ) ) {
		return true;
	}
	if ( function_exists( 'wc_get_product' ) ) {
		$product = wc_get_product( $product_id );
		if ( $product && '' !== $product->get_price() && (float) $product->get_price() <= 0 ) {
			return true;
		}
	}
	return false;
}

/**
 * @param int $user_id    User ID (0 = current).
 * @param int $product_id Product ID.
 * @return bool
 */
function dejoiy_library_lms_user_owns_book( $user_id, $product_id ) {
	$user_id    = $user_id > 0 ? (int) $user_id : get_current_user_id();
	$product_id = (int) $product_id;
	if ( $user_id < 1 || $product_id < 1 ) {
		return false;
	}
	$owned = get_user_meta( $user_id, DEJOIY_LIBRARY_LMS_BOOKS_META, true );
	if ( ! is_array( $owned ) ) {
		return false;
	}
	return in_array( $product_id, array_map( 'intval', $owned ), true );
}

/**
 * @param int $user_id User ID (0 = current).
 * @return int[]
 */
function dejoiy_library_lms_get_owned_book_ids( $user_id = 0 ) {
	$user_id = $user_id > 0 ? (int) $user_id : get_current_user_id();
	if ( $user_id < 1 ) {
		return array();
	}
	$owned = get_user_meta( $user_id, DEJOIY_LIBRARY_LMS_BOOKS_META, true );
	if ( ! is_array( $owned ) ) {
		return array();
	}
	$ids = array_values( array_unique( array_filter( array_map( 'intval', $owned ) ) ) );
	return $ids;
}

/**
 * @param int $user_id    User ID (0 = current).
 * @param int $product_id Product ID.
 * @return bool
 */
function dejoiy_library_lms_is_book_hidden( $user_id, $product_id ) {
	$user_id    = $user_id > 0 ? (int) $user_id : get_current_user_id();
	$product_id = (int) $product_id;
	if ( $user_id < 1 || $product_id < 1 ) {
		return false;
	}
	$hidden = get_user_meta( $user_id, DEJOIY_LIBRARY_LMS_HIDDEN_META, true );
	if ( ! is_array( $hidden ) ) {
		return false;
	}
	return in_array( $product_id, array_map( 'intval', $hidden ), true );
}

/**
 * Hide a purchased book from the Nexus shelf (does not revoke access).
 *
 * @param int $user_id    User ID.
 * @param int $product_id Product ID.
 */
function dejoiy_library_lms_hide_book( $user_id, $product_id ) {
	$user_id    = (int) $user_id;
	$product_id = (int) $product_id;
	if ( $user_id < 1 || $product_id < 1 ) {
		return;
	}
	$hidden = get_user_meta( $user_id, DEJOIY_LIBRARY_LMS_HIDDEN_META, true );
	if ( ! is_array( $hidden ) ) {
		$hidden = array();
	}
	if ( ! in_array( $product_id, array_map( 'intval', $hidden ), true ) ) {
		$hidden[] = $product_id;
		update_user_meta( $user_id, DEJOIY_LIBRARY_LMS_HIDDEN_META, array_values( array_unique( array_map( 'intval', $hidden ) ) ) );
	}
}

/**
 * Grant LMS access for a product to a user.
 *
 * @param int $user_id    User ID.
 * @param int $product_id Product ID.
 */
function dejoiy_library_lms_grant_book( $user_id, $product_id ) {
	$user_id    = (int) $user_id;
	$product_id = (int) $product_id;
	if ( $user_id < 1 || $product_id < 1 ) {
		return;
	}
	if ( ! function_exists( 'dejoiy_library_is_nexus_product' ) || ! dejoiy_library_is_nexus_product( $product_id ) ) {
		return;
	}
	if ( dejoiy_library_lms_is_download_only_product( $product_id ) ) {
		return;
	}
	$owned = dejoiy_library_lms_get_owned_book_ids( $user_id );
	if ( in_array( $product_id, $owned, true ) ) {
		return;
	}
	$owned[] = $product_id;
	update_user_meta( $user_id, DEJOIY_LIBRARY_LMS_BOOKS_META, $owned );
}

/**
 * @param int $user_id    User ID (0 = current).
 * @param int $product_id Product ID.
 * @return bool
 */
function dejoiy_library_user_can_read_book( $user_id, $product_id ) {
	$product_id = (int) $product_id;
	if ( $product_id < 1 ) {
		return false;
	}
	if ( dejoiy_library_lms_is_download_only_product( $product_id ) ) {
		return false;
	}
	if ( dejoiy_library_lms_is_free_read_product( $product_id ) ) {
		return true;
	}
	$user_id = $user_id > 0 ? (int) $user_id : get_current_user_id();
	if ( $user_id < 1 ) {
		return false;
	}
	return dejoiy_library_lms_user_owns_book( $user_id, $product_id );
}

/**
 * @param int $user_id    User ID (0 = current).
 * @param int $product_id Product ID.
 * @return int 0–100
 */
function dejoiy_library_lms_get_progress( $user_id, $product_id ) {
	$user_id    = $user_id > 0 ? (int) $user_id : get_current_user_id();
	$product_id = (int) $product_id;
	if ( $user_id < 1 || $product_id < 1 ) {
		return 0;
	}
	$val = get_user_meta( $user_id, DEJOIY_LIBRARY_LMS_PROGRESS_PREFIX . $product_id, true );
	return max( 0, min( 100, (int) $val ) );
}

/**
 * @param int $user_id    User ID.
 * @param int $product_id Product ID.
 * @param int $percent    0–100.
 */
function dejoiy_library_lms_set_progress( $user_id, $product_id, $percent ) {
	$user_id    = (int) $user_id;
	$product_id = (int) $product_id;
	$percent    = max( 0, min( 100, (int) $percent ) );
	if ( $user_id < 1 || $product_id < 1 ) {
		return;
	}
	if ( ! dejoiy_library_user_can_read_book( $user_id, $product_id ) ) {
		return;
	}
	update_user_meta( $user_id, DEJOIY_LIBRARY_LMS_PROGRESS_PREFIX . $product_id, $percent );
}

/**
 * Grant books when an order reaches a paid status.
 *
 * @param int $order_id Order ID.
 */
function dejoiy_library_lms_on_order_paid( $order_id ) {
	$order = function_exists( 'wc_get_order' ) ? wc_get_order( $order_id ) : null;
	if ( ! $order ) {
		return;
	}
	$user_id = (int) $order->get_user_id();
	if ( $user_id < 1 ) {
		return;
	}
	foreach ( $order->get_items() as $item ) {
		$product_id = (int) $item->get_product_id();
		if ( $product_id ) {
			dejoiy_library_lms_grant_book( $user_id, $product_id );
		}
	}
	if ( function_exists( 'dejoiy_moodle_sync_order' ) ) {
		dejoiy_moodle_sync_order( $order );
	}
}

/**
 * @param int $user_id User ID (0 = current).
 * @return array<int, array<string, mixed>>
 */
function dejoiy_library_lms_get_shelf_entries( $user_id = 0 ) {
	$user_id = $user_id > 0 ? (int) $user_id : get_current_user_id();
	$entries = array();
	foreach ( dejoiy_library_lms_get_owned_book_ids( $user_id ) as $product_id ) {
		if ( dejoiy_library_lms_is_book_hidden( $user_id, $product_id ) ) {
			continue;
		}
		if ( dejoiy_library_lms_is_download_only_product( $product_id ) ) {
			continue;
		}
		$product = function_exists( 'wc_get_product' ) ? wc_get_product( $product_id ) : null;
		if ( ! $product ) {
			continue;
		}
		$entries[] = array(
			'id'       => $product_id,
			'title'    => $product->get_name(),
			'author'   => function_exists( 'dejoiy_library_get_author' ) ? dejoiy_library_get_author( $product_id ) : '',
			'cover'    => function_exists( 'dejoiy_library_get_cover_url' ) ? dejoiy_library_get_cover_url( $product_id, 'medium' ) : '',
			'progress' => dejoiy_library_lms_get_progress( $user_id, $product_id ),
			'read_url' => function_exists( 'dejoiy_library_reader_url' ) ? dejoiy_library_reader_url( $product_id ) : '',
		);
	}
	usort(
		$entries,
		static function ( $a, $b ) {
			return (int) $b['progress'] <=> (int) $a['progress'];
		}
	);
	return $entries;
}

/**
 * @param int $user_id User ID (0 = current).
 * @return array<int, array<string, mixed>>
 */
function dejoiy_library_lms_get_download_entries( $user_id = 0 ) {
	$user_id = $user_id > 0 ? (int) $user_id : get_current_user_id();
	$entries = array();
	foreach ( dejoiy_library_lms_get_owned_book_ids( $user_id ) as $product_id ) {
		if ( ! dejoiy_library_lms_is_download_only_product( $product_id ) ) {
			continue;
		}
		$product = function_exists( 'wc_get_product' ) ? wc_get_product( $product_id ) : null;
		if ( ! $product ) {
			continue;
		}
		$downloads = array();
		foreach ( $product->get_downloads() as $dl ) {
			$downloads[] = array(
				'name' => $dl->get_name(),
				'url'  => $dl->get_download_url(),
			);
		}
		$entries[] = array(
			'id'        => $product_id,
			'title'     => $product->get_name(),
			'cover'     => function_exists( 'dejoiy_library_get_cover_url' ) ? dejoiy_library_get_cover_url( $product_id, 'medium' ) : '',
			'downloads' => $downloads,
		);
	}
	return $entries;
}

/**
 * AJAX: save reading progress (logged-in customers).
 */
function dejoiy_library_lms_ajax_save_progress() {
	$book_id = isset( $_POST['book_id'] ) ? absint( $_POST['book_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$percent = isset( $_POST['percent'] ) ? absint( $_POST['percent'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( ! $book_id || ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => 'Unauthorized' ), 403 );
	}
	check_ajax_referer( 'dejoiy_library_lms', 'nonce' );
	if ( ! dejoiy_library_user_can_read_book( 0, $book_id ) ) {
		wp_send_json_error( array( 'message' => 'No access' ), 403 );
	}
	dejoiy_library_lms_set_progress( get_current_user_id(), $book_id, $percent );
	wp_send_json_success( array( 'percent' => $percent ) );
}

/**
 * AJAX: load reading progress.
 */
function dejoiy_library_lms_ajax_get_progress() {
	$book_id = isset( $_GET['book_id'] ) ? absint( $_GET['book_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! $book_id || ! is_user_logged_in() ) {
		wp_send_json_success( array( 'percent' => 0 ) );
	}
	if ( ! dejoiy_library_user_can_read_book( 0, $book_id ) ) {
		wp_send_json_success( array( 'percent' => 0 ) );
	}
	wp_send_json_success(
		array(
			'percent' => dejoiy_library_lms_get_progress( 0, $book_id ),
		)
	);
}

/**
 * Register LMS hooks (safe on front + AJAX only).
 */
function dejoiy_library_lms_init() {
	static $done = false;
	if ( $done || ! class_exists( 'WooCommerce' ) ) {
		return;
	}
	$done = true;

	add_action( 'woocommerce_order_status_completed', 'dejoiy_library_lms_on_order_paid', 20 );
	add_action( 'woocommerce_order_status_processing', 'dejoiy_library_lms_on_order_paid', 20 );
	add_action( 'woocommerce_order_status_completed', 'dejoiy_library_lms_clear_hidden_on_purchase', 25 );
	add_action( 'woocommerce_order_status_processing', 'dejoiy_library_lms_clear_hidden_on_purchase', 25 );
	add_action( 'template_redirect', 'dejoiy_library_lms_grant_free_reader_access', 6 );
	add_action( 'wp_ajax_dejoiy_library_lms_save_progress', 'dejoiy_library_lms_ajax_save_progress' );
	add_action( 'wp_ajax_dejoiy_library_lms_get_progress', 'dejoiy_library_lms_ajax_get_progress' );
}

/**
 * Unhide a book when the customer purchases it again.
 *
 * @param int $order_id Order ID.
 */
function dejoiy_library_lms_clear_hidden_on_purchase( $order_id ) {
	$order = function_exists( 'wc_get_order' ) ? wc_get_order( $order_id ) : null;
	if ( ! $order ) {
		return;
	}
	$user_id = (int) $order->get_user_id();
	if ( $user_id < 1 ) {
		return;
	}
	$hidden = get_user_meta( $user_id, DEJOIY_LIBRARY_LMS_HIDDEN_META, true );
	if ( ! is_array( $hidden ) || empty( $hidden ) ) {
		return;
	}
	$purchased = array();
	foreach ( $order->get_items() as $item ) {
		$purchased[] = (int) $item->get_product_id();
	}
	$hidden = array_values( array_diff( array_map( 'intval', $hidden ), $purchased ) );
	update_user_meta( $user_id, DEJOIY_LIBRARY_LMS_HIDDEN_META, $hidden );
}

/**
 * Grant free/public-domain editions to logged-in readers (no checkout hop).
 */
function dejoiy_library_lms_grant_free_reader_access() {
	if ( ! function_exists( 'dejoiy_library_is_reader_view' ) || ! dejoiy_library_is_reader_view() ) {
		return;
	}
	if ( ! is_user_logged_in() ) {
		return;
	}
	$book_id = isset( $_GET['dejoiy_reader'] ) ? absint( $_GET['dejoiy_reader'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( $book_id < 1 || ! dejoiy_library_lms_is_free_read_product( $book_id ) ) {
		return;
	}
	dejoiy_library_lms_grant_book( get_current_user_id(), $book_id );
}

/**
 * @return string Moodle base URL, configured by constant or option.
 */
function dejoiy_moodle_base_url() {
	$url = defined( 'DEJOIY_MOODLE_URL' ) ? DEJOIY_MOODLE_URL : get_option( 'dejoiy_moodle_url', '' );
	return $url ? untrailingslashit( esc_url_raw( (string) $url ) ) : '';
}

/**
 * @return string Moodle webservice token, configured by constant or option.
 */
function dejoiy_moodle_token() {
	$token = defined( 'DEJOIY_MOODLE_TOKEN' ) ? DEJOIY_MOODLE_TOKEN : get_option( 'dejoiy_moodle_token', '' );
	return is_string( $token ) ? trim( $token ) : '';
}

/**
 * @return bool Whether Moodle API integration has enough config to run.
 */
function dejoiy_moodle_is_configured() {
	return '' !== dejoiy_moodle_base_url() && '' !== dejoiy_moodle_token();
}

/**
 * Call Moodle Open Source REST webservice safely.
 *
 * @param string              $function Moodle wsfunction.
 * @param array<string,mixed> $params   Params.
 * @return mixed|\WP_Error
 */
function dejoiy_moodle_api( $function, $params = array() ) {
	$base  = dejoiy_moodle_base_url();
	$token = dejoiy_moodle_token();
	if ( '' === $base || '' === $token ) {
		return new WP_Error( 'dejoiy_moodle_not_configured', 'Moodle URL/token is not configured.' );
	}

	$params['wstoken']            = $token;
	$params['wsfunction']         = $function;
	$params['moodlewsrestformat'] = 'json';

	$response = wp_remote_post(
		$base . '/webservice/rest/server.php',
		array(
			'timeout' => 15,
			'body'    => $params,
		)
	);
	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body, true );
	if ( null === $data && '' !== $body ) {
		return new WP_Error( 'dejoiy_moodle_bad_response', 'Moodle returned a non-JSON response.' );
	}
	if ( is_array( $data ) && isset( $data['exception'] ) ) {
		return new WP_Error( 'dejoiy_moodle_exception', isset( $data['message'] ) ? $data['message'] : $data['exception'], $data );
	}
	return $data;
}

/**
 * Get Moodle course IDs mapped to a WooCommerce/WCFM product.
 *
 * Vendors/admins can set `_dejoiy_moodle_course_id` or
 * `_dejoiy_moodle_course_ids` product meta.
 *
 * @param int $product_id Product ID.
 * @return int[]
 */
function dejoiy_moodle_course_ids_for_product( $product_id ) {
	$product_id = (int) $product_id;
	if ( $product_id < 1 ) {
		return array();
	}

	$raw = get_post_meta( $product_id, '_dejoiy_moodle_course_ids', true );
	if ( '' === (string) $raw ) {
		$raw = get_post_meta( $product_id, '_dejoiy_moodle_course_id', true );
	}
	if ( is_array( $raw ) ) {
		$ids = $raw;
	} else {
		$ids = preg_split( '/[,\s]+/', (string) $raw );
	}

	return array_values( array_unique( array_filter( array_map( 'absint', (array) $ids ) ) ) );
}

/**
 * Find or create matching Moodle user for a WP user.
 *
 * @param int $user_id WordPress user ID.
 * @return int|\WP_Error Moodle user ID.
 */
function dejoiy_moodle_get_or_create_user_id( $user_id ) {
	$user_id = (int) $user_id;
	$user    = $user_id > 0 ? get_userdata( $user_id ) : false;
	if ( ! $user || empty( $user->user_email ) ) {
		return new WP_Error( 'dejoiy_moodle_no_user', 'Order has no WordPress user email.' );
	}

	$existing = (int) get_user_meta( $user_id, 'dejoiy_moodle_user_id', true );
	if ( $existing > 0 ) {
		return $existing;
	}

	$found = dejoiy_moodle_api(
		'core_user_get_users_by_field',
		array(
			'field'     => 'email',
			'values[0]' => $user->user_email,
		)
	);
	if ( is_wp_error( $found ) ) {
		return $found;
	}
	if ( is_array( $found ) && ! empty( $found[0]['id'] ) ) {
		update_user_meta( $user_id, 'dejoiy_moodle_user_id', (int) $found[0]['id'] );
		return (int) $found[0]['id'];
	}

	$created = dejoiy_moodle_api(
		'core_user_create_users',
		array(
			'users[0][username]'  => sanitize_user( current( explode( '@', $user->user_email ) ) . '-' . $user_id, true ),
			'users[0][password]'  => wp_generate_password( 24, true, true ),
			'users[0][firstname]' => $user->first_name ? $user->first_name : $user->display_name,
			'users[0][lastname]'  => $user->last_name ? $user->last_name : 'DEJOIY',
			'users[0][email]'     => $user->user_email,
			'users[0][auth]'      => 'manual',
		)
	);
	if ( is_wp_error( $created ) ) {
		return $created;
	}
	if ( is_array( $created ) && ! empty( $created[0]['id'] ) ) {
		update_user_meta( $user_id, 'dejoiy_moodle_user_id', (int) $created[0]['id'] );
		return (int) $created[0]['id'];
	}
	return new WP_Error( 'dejoiy_moodle_create_failed', 'Moodle user was not created.' );
}

/**
 * Enrol a WP user in mapped Moodle courses.
 *
 * @param int   $user_id    WordPress user ID.
 * @param int[] $course_ids Moodle course IDs.
 * @return void
 */
function dejoiy_moodle_enrol_user_in_courses( $user_id, $course_ids ) {
	if ( ! dejoiy_moodle_is_configured() || empty( $course_ids ) ) {
		return;
	}

	$moodle_user_id = dejoiy_moodle_get_or_create_user_id( $user_id );
	if ( is_wp_error( $moodle_user_id ) ) {
		update_user_meta( $user_id, 'dejoiy_moodle_last_error', $moodle_user_id->get_error_message() );
		return;
	}

	$params = array();
	$i      = 0;
	foreach ( array_values( array_unique( array_map( 'absint', $course_ids ) ) ) as $course_id ) {
		if ( $course_id < 1 ) {
			continue;
		}
		$params[ "enrolments[$i][roleid]" ]   = 5; // Moodle student role.
		$params[ "enrolments[$i][userid]" ]   = (int) $moodle_user_id;
		$params[ "enrolments[$i][courseid]" ] = $course_id;
		$i++;
	}
	if ( 0 === $i ) {
		return;
	}

	$result = dejoiy_moodle_api( 'enrol_manual_enrol_users', $params );
	if ( is_wp_error( $result ) ) {
		update_user_meta( $user_id, 'dejoiy_moodle_last_error', $result->get_error_message() );
		return;
	}
	update_user_meta( $user_id, 'dejoiy_moodle_last_sync', time() );
	delete_user_meta( $user_id, 'dejoiy_moodle_last_error' );
}

/**
 * Sync WooCommerce paid order items to Moodle course enrolments.
 *
 * @param WC_Order $order Order.
 * @return void
 */
function dejoiy_moodle_sync_order( $order ) {
	if ( ! dejoiy_moodle_is_configured() || ! $order || ! is_a( $order, 'WC_Order' ) ) {
		return;
	}
	$user_id = (int) $order->get_user_id();
	if ( $user_id < 1 ) {
		return;
	}

	$course_ids = array();
	foreach ( $order->get_items() as $item ) {
		$product_id = (int) $item->get_product_id();
		if ( $product_id > 0 ) {
			$course_ids = array_merge( $course_ids, dejoiy_moodle_course_ids_for_product( $product_id ) );
		}
	}
	dejoiy_moodle_enrol_user_in_courses( $user_id, $course_ids );
}

/**
 * @return string Safe Moodle iframe URL if configured.
 */
function dejoiy_moodle_embed_url() {
	$base = dejoiy_moodle_base_url();
	if ( '' === $base ) {
		return '';
	}
	return esc_url( $base . '/my/' );
}

if ( ! defined( 'DEJOIY_LMS_PAGE_SLUG' ) ) {
	define( 'DEJOIY_LMS_PAGE_SLUG', 'dejoiy-nexus-lms' );
}

/**
 * @return bool Whether this request is the isolated Moodle-style LMS page.
 */
function dejoiy_lms_is_request() {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return false;
	}
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return false;
	}
	if ( ! empty( $_GET['dejoiy_lms'] ) && '1' === (string) $_GET['dejoiy_lms'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return true;
	}

	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	$path = trim( (string) wp_parse_url( $uri, PHP_URL_PATH ), '/' );
	if ( DEJOIY_LMS_PAGE_SLUG === $path ) {
		return true;
	}

	return did_action( 'wp' ) && function_exists( 'is_page' ) && is_page( DEJOIY_LMS_PAGE_SLUG );
}

/**
 * Register isolated LMS page shortcodes.
 */
function dejoiy_lms_register_shortcode() {
	add_shortcode( 'dejoiy_moodle_lms', 'dejoiy_lms_render' );
	add_shortcode( 'dejoiy_nexus_lms', 'dejoiy_lms_render' );
}
add_action( 'init', 'dejoiy_lms_register_shortcode' );

/**
 * Force the LMS UI only on the LMS page.
 *
 * @param string $content Existing page content.
 * @return string
 */
function dejoiy_lms_force_content( $content ) {
	if ( ! dejoiy_lms_is_request() ) {
		return $content;
	}
	return dejoiy_lms_render();
}
add_filter( 'the_content', 'dejoiy_lms_force_content', 9998 );
add_filter( 'elementor/frontend/the_content', 'dejoiy_lms_force_content', 9998 );

/**
 * Some XStore templates print raw page content. Take over only the LMS route so
 * the shortcode cannot leak as plain text, without affecting any other page.
 */
function dejoiy_lms_render_route() {
	if ( ! dejoiy_lms_is_request() ) {
		return;
	}
	status_header( 200 );

	if ( function_exists( 'dejoiy_library_document_start' ) ) {
		dejoiy_library_document_start();
		$header = get_stylesheet_directory() . '/library-header.php';
		if ( is_readable( $header ) ) {
			require $header;
		}
		echo dejoiy_lms_render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$footer = get_stylesheet_directory() . '/library-footer.php';
		if ( is_readable( $footer ) ) {
			require $footer;
		}
		dejoiy_library_document_end();
		exit;
	}

	get_header();
	echo dejoiy_lms_render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	get_footer();
	exit;
}
add_action( 'template_redirect', 'dejoiy_lms_render_route', 2 );

/**
 * Enqueue isolated LMS page assets.
 */
function dejoiy_lms_enqueue() {
	if ( ! dejoiy_lms_is_request() ) {
		return;
	}

	$uri = get_stylesheet_directory_uri();
	$dir = get_stylesheet_directory();
	$ver = '1.0.0';

	if ( is_readable( $dir . '/library-universe.css' ) ) {
		wp_enqueue_style(
			'dejoiy-library-universe',
			$uri . '/library-universe.css',
			array(),
			(string) filemtime( $dir . '/library-universe.css' )
		);
	}

	wp_enqueue_style(
		'dejoiy-lms',
		$uri . '/library-lms.css',
		array( 'dejoiy-library-universe' ),
		(string) ( is_readable( $dir . '/library-lms.css' ) ? filemtime( $dir . '/library-lms.css' ) : $ver )
	);
	wp_enqueue_script(
		'dejoiy-lms',
		$uri . '/library-lms.js',
		array(),
		(string) ( is_readable( $dir . '/library-lms.js' ) ? filemtime( $dir . '/library-lms.js' ) : $ver ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'dejoiy_lms_enqueue', 1006 );

/**
 * LMS course data.
 *
 * @return array<int, array<string, mixed>>
 */
function dejoiy_lms_courses() {
	return array(
		array(
			'title'    => 'Moodle Launch Lab',
			'track'    => 'Admin',
			'level'    => 'Foundation',
			'progress' => 86,
			'lessons'  => 18,
			'color'    => '#7c3aed',
			'summary'  => 'Set up courses, cohorts, roles, quizzes, badges, and completion rules.',
		),
		array(
			'title'    => 'Seller Academy',
			'track'    => 'Marketplace',
			'level'    => 'Growth',
			'progress' => 64,
			'lessons'  => 24,
			'color'    => '#0891b2',
			'summary'  => 'Train vendors on listings, fulfillment, customer care, offers, and analytics.',
		),
		array(
			'title'    => 'Creator Certification',
			'track'    => 'Creators',
			'level'    => 'Pro',
			'progress' => 42,
			'lessons'  => 21,
			'color'    => '#f59e0b',
			'summary'  => 'Build creator workflows for assets, licensing, product drops, and launches.',
		),
		array(
			'title'    => 'Customer Success Path',
			'track'    => 'Customers',
			'level'    => 'Starter',
			'progress' => 73,
			'lessons'  => 15,
			'color'    => '#10b981',
			'summary'  => 'Guide buyers through discovery, wishlists, orders, support, and loyalty.',
		),
	);
}

/**
 * Render one course card.
 *
 * @param array<string, mixed> $course Course.
 * @return string
 */
function dejoiy_lms_course_card( $course ) {
	$progress = max( 0, min( 100, (int) $course['progress'] ) );
	ob_start();
	?>
	<article class="dlms-course" style="--dlms-accent:<?php echo esc_attr( $course['color'] ); ?>" data-dlms-reveal>
		<div class="dlms-course-top">
			<span><?php echo esc_html( $course['track'] ); ?></span>
			<strong><?php echo esc_html( $course['level'] ); ?></strong>
		</div>
		<h3><?php echo esc_html( $course['title'] ); ?></h3>
		<p><?php echo esc_html( $course['summary'] ); ?></p>
		<div class="dlms-progress" aria-label="<?php echo esc_attr( $progress . '% complete' ); ?>">
			<span style="width:<?php echo esc_attr( (string) $progress ); ?>%"></span>
		</div>
		<div class="dlms-course-meta">
			<span><?php echo esc_html( (string) $progress ); ?>% complete</span>
			<span><?php echo esc_html( (string) $course['lessons'] ); ?> lessons</span>
		</div>
		<a class="dlms-card-link" href="#dlms-classroom">Open classroom</a>
	</article>
	<?php
	return (string) ob_get_clean();
}

/**
 * Render the isolated LMS page.
 *
 * @return string
 */
function dejoiy_lms_render() {
	$courses = dejoiy_lms_courses();
	$moodle_url = dejoiy_moodle_embed_url();
	$moodle_connected = dejoiy_moodle_is_configured();
	ob_start();
	?>
	<div id="dlms" class="dlms-root dlu-root">
		<section class="dlms-hero dlu-hero" aria-label="DEJOIY Moodle LMS">
			<div class="dlms-hero-mesh dlu-hero-mesh" aria-hidden="true"></div>
			<canvas id="dlms-galaxy" class="dlms-galaxy dlu-galaxy-canvas" aria-hidden="true"></canvas>
			<div class="dlms-orb" aria-hidden="true"></div>
			<div class="dlms-hero-content dlu-hero-content">
				<p class="dlms-kicker dlu-kicker">DEJOIY Nexus LMS</p>
				<h1 class="dlms-title dlu-hero-title">
					<span>Learn. Build.</span>
					<span>Certify In The Nexus.</span>
				</h1>
				<p class="dlms-sub dlu-hero-sub">Actual Moodle Open Source bridge for customers, sellers, creators, and internal teams — styled to match the DEJOIY Nexus experience and connected to WooCommerce/WCFM order access.</p>
				<div class="dlms-hero-actions">
					<a class="dlms-btn dlms-btn-primary" href="#dlms-moodle">Open Moodle</a>
					<a class="dlms-btn dlms-btn-ghost" href="#dlms-paths">Explore paths</a>
				</div>
			</div>
		</section>

		<section class="dlms-stats" aria-label="Learning overview">
			<div class="dlms-stat"><strong><?php echo $moodle_connected ? 'Live' : 'Ready'; ?></strong><span>Moodle bridge</span></div>
			<div class="dlms-stat"><strong>Woo</strong><span>Order enrolment</span></div>
			<div class="dlms-stat"><strong>WCFM</strong><span>Vendor course meta</span></div>
			<div class="dlms-stat"><strong>JOI</strong><span>Learning guide</span></div>
		</section>

		<section id="dlms-moodle" class="dlms-moodle-shell">
			<div class="dlms-sec-head">
				<p class="dlms-eyebrow">Moodle Open Source</p>
				<h2>Injected inside the Nexus learning shell</h2>
				<p>When Moodle URL/token are configured, this panel loads the real Moodle dashboard and WooCommerce paid orders enrol buyers into mapped Moodle courses.</p>
			</div>
			<div class="dlms-moodle-grid">
				<div class="dlms-moodle-frame" data-dlms-reveal>
					<?php if ( $moodle_url ) : ?>
						<iframe title="DEJOIY Moodle LMS" src="<?php echo esc_url( $moodle_url ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
					<?php else : ?>
						<div class="dlms-moodle-empty">
							<div class="dlms-ai-core" aria-hidden="true"></div>
							<h3>Moodle bridge waiting for live URL</h3>
							<p>Add <code>DEJOIY_MOODLE_URL</code> and <code>DEJOIY_MOODLE_TOKEN</code> (or WordPress options <code>dejoiy_moodle_url</code>/<code>dejoiy_moodle_token</code>) to inject the actual Moodle Open Source dashboard here.</p>
						</div>
					<?php endif; ?>
				</div>
				<aside class="dlms-moodle-map" data-dlms-reveal>
					<h3>Commerce → Moodle sync</h3>
					<ul>
						<li><strong>WooCommerce orders:</strong> paid/processing orders trigger Moodle enrolment.</li>
						<li><strong>WCFM sellers:</strong> vendor products can map to Moodle course IDs with product meta.</li>
						<li><strong>Meta keys:</strong> <code>_dejoiy_moodle_course_id</code> or <code>_dejoiy_moodle_course_ids</code>.</li>
						<li><strong>Access:</strong> buyers keep Nexus shelf/progress and get Moodle student enrolment.</li>
					</ul>
				</aside>
			</div>
		</section>

		<section id="dlms-paths" class="dlms-sec">
			<div class="dlms-sec-head">
				<p class="dlms-eyebrow">Nexus-styled Moodle paths</p>
				<h2>Choose your learning galaxy</h2>
				<p>Course cards mirror Moodle categories, completion, badges, quizzes, and lesson modules while keeping the Nexus visual system.</p>
			</div>
			<div class="dlms-course-grid">
				<?php
				foreach ( $courses as $course ) {
					echo dejoiy_lms_course_card( $course ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				?>
			</div>
		</section>

		<section id="dlms-classroom" class="dlms-classroom">
			<div class="dlms-panel dlms-panel-main" data-dlms-reveal>
				<span class="dlms-eyebrow">Live classroom</span>
				<h2>Moodle tools, Nexus interface</h2>
				<div class="dlms-timeline">
					<div><strong>01</strong><span>Course modules</span><p>Topic sections, lesson cards, resources, and completion rules.</p></div>
					<div><strong>02</strong><span>Quizzes &amp; assignments</span><p>Assessment cards with due dates, attempts, scoring, and feedback states.</p></div>
					<div><strong>03</strong><span>Badges &amp; certificates</span><p>Reward learners with DEJOIY-styled achievements and path completion.</p></div>
					<div><strong>04</strong><span>Order-based enrolment</span><p>WooCommerce/WCFM purchases unlock mapped Moodle course access automatically when configured.</p></div>
				</div>
			</div>
			<aside class="dlms-panel dlms-tutor" data-dlms-reveal>
				<div class="dlms-ai-core" aria-hidden="true"></div>
				<h3>JOI Learning Guide</h3>
				<p>Ask for a learning plan and JOI maps a path across Moodle-style courses, quizzes, and resources.</p>
				<form class="dlms-prompt">
					<input type="text" value="Teach me how to sell on DEJOIY" aria-label="Learning prompt" readonly />
					<button type="button">Generate path</button>
				</form>
			</aside>
		</section>

		<section class="dlms-sec dlms-sec-dark">
			<div class="dlms-sec-head">
				<p class="dlms-eyebrow">Isolation guarantee</p>
				<h2>Only this page changes</h2>
				<p>This Moodle/Nexus UI is scoped to <code>/<?php echo esc_html( DEJOIY_LMS_PAGE_SLUG ); ?>/</code>. Marketplace, Studio, cart, checkout, and Library pages keep their existing UI and behavior.</p>
			</div>
		</section>
	</div>
	<?php
	return (string) ob_get_clean();
}
