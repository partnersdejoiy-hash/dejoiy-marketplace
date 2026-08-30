<?php
/**
 * DEJOIY Services — booking engine (WooCommerce + WCFM).
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DEJOIY_SERVICES_BOOKING_FLOW', 'dejoiy_services_booking_flow' );
define( 'DEJOIY_SERVICES_BOOKING_META', '_dejoiy_service_booking' );
define( 'DEJOIY_SERVICES_BOOKING_STATUS', '_dejoiy_service_booking_status' );

/**
 * @return bool
 */
function dejoiy_services_booking_active() {
	return function_exists( 'dejoiy_services_enabled' ) && dejoiy_services_enabled() && class_exists( 'WooCommerce' );
}

/**
 * @return array<string, string>
 */
function dejoiy_services_booking_status_labels() {
	return array(
		'pending'     => __( 'Pending', 'dejoiy' ),
		'accepted'    => __( 'Accepted', 'dejoiy' ),
		'in_progress' => __( 'In Progress', 'dejoiy' ),
		'delivered'   => __( 'Delivered', 'dejoiy' ),
		'completed'   => __( 'Completed', 'dejoiy' ),
		'rejected'    => __( 'Rejected', 'dejoiy' ),
	);
}

/**
 * @param int $product_id Product ID.
 * @return string design|development|marketing|business|personal
 */
function dejoiy_services_detect_service_type( $product_id ) {
	$map = array(
		'graphic-design'    => 'design',
		'content-writing'   => 'design',
		'web-development'   => 'development',
		'digital-marketing' => 'marketing',
	);
	$terms = wp_get_post_terms( (int) $product_id, 'product_cat', array( 'fields' => 'slugs' ) );
	if ( is_wp_error( $terms ) ) {
		return 'business';
	}
	foreach ( $terms as $slug ) {
		if ( isset( $map[ $slug ] ) ) {
			return $map[ $slug ];
		}
	}
	return 'business';
}

/**
 * @param int $product_id Product ID.
 * @return string fixed|custom|hourly
 */
function dejoiy_services_detect_price_type( $product_id ) {
	$type = sanitize_key( (string) get_post_meta( (int) $product_id, '_dejoiy_service_price_type', true ) );
	if ( in_array( $type, array( 'fixed', 'custom', 'hourly' ), true ) ) {
		return $type;
	}
	return 'fixed';
}

/**
 * @param array<string, mixed> $cart_item Cart line.
 * @return bool
 */
function dejoiy_services_cart_line_is_booking( $cart_item ) {
	return ! empty( $cart_item['dejoiy_service_booking'] );
}

/**
 * @return bool
 */
function dejoiy_services_is_booking_cart_context() {
	if ( function_exists( 'dejoiy_services_is_services_page' ) && dejoiy_services_is_services_page() ) {
		return true;
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! empty( $_GET['dejoiy_services_booking'] ) ) {
		return true;
	}
	if ( function_exists( 'WC' ) && WC()->session ) {
		return '1' === (string) WC()->session->get( DEJOIY_SERVICES_BOOKING_FLOW );
	}
	return false;
}

/**
 * @return bool
 */
function dejoiy_services_doing_booking_submit() {
	return ! empty( $GLOBALS['dejoiy_services_doing_booking_submit'] );
}

/**
 * @param bool   $visible   Visible.
 * @param array  $cart_item Item.
 * @param string $cart_key  Key.
 * @return bool
 */
function dejoiy_services_booking_cart_item_visible( $visible, $cart_item, $cart_key ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	if ( ! dejoiy_services_booking_active() ) {
		return $visible;
	}
	$is_booking = dejoiy_services_cart_line_is_booking( $cart_item );
	if ( dejoiy_services_is_booking_cart_context() ) {
		return $is_booking ? $visible : false;
	}
	return $is_booking ? false : $visible;
}

/**
 * @param bool $passed     Passed.
 * @param int  $product_id Product.
 * @param int  $quantity   Qty.
 * @return bool
 */
function dejoiy_services_booking_add_to_cart_validation( $passed, $product_id, $quantity ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	if ( ! $passed || ! dejoiy_services_is_product( $product_id ) ) {
		return $passed;
	}
	if ( dejoiy_services_doing_booking_submit() || dejoiy_services_is_booking_cart_context() ) {
		return $passed;
	}
	if ( function_exists( 'wc_add_notice' ) ) {
		wc_add_notice(
			__( 'Please use “Book this service” on the DEJOIY Services page to submit your requirements.', 'dejoiy' ),
			'error'
		);
	}
	return false;
}

/**
 * @param array<string, mixed> $cart_item_data Data.
 * @param int                  $product_id     Product.
 * @param int                  $variation_id   Variation.
 * @return array<string, mixed>
 */
function dejoiy_services_booking_add_cart_item_data( $cart_item_data, $product_id, $variation_id ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	if ( ! dejoiy_services_doing_booking_submit() || ! dejoiy_services_is_product( $product_id ) ) {
		return $cart_item_data;
	}

	$payload = dejoiy_services_booking_payload_from_request();
	if ( empty( $payload ) ) {
		return $cart_item_data;
	}

	$vendor_id = (int) get_post_field( 'post_author', $product_id );

	$cart_item_data['dejoiy_service_booking']      = 1;
	$cart_item_data['dejoiy_service_booking_data'] = $payload;
	$cart_item_data['unique_key']                  = md5( $product_id . '|' . wp_generate_password( 12, false ) );

	return $cart_item_data;
}

/**
 * @param array<string, mixed> $cart_item Cart item.
 * @param array<string, mixed> $values    Session values.
 * @param string               $cart_key  Key.
 * @return array<string, mixed>
 */
function dejoiy_services_booking_get_cart_item_from_session( $cart_item, $values, $cart_key ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	if ( isset( $values['dejoiy_service_booking'] ) ) {
		$cart_item['dejoiy_service_booking'] = $values['dejoiy_service_booking'];
	}
	if ( isset( $values['dejoiy_service_booking_data'] ) ) {
		$cart_item['dejoiy_service_booking_data'] = $values['dejoiy_service_booking_data'];
	}
	return $cart_item;
}

/**
 * Build sanitized booking payload from POST.
 *
 * @return array<string, mixed>
 */
function dejoiy_services_booking_payload_from_request() {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$type = isset( $_POST['service_type'] ) ? sanitize_key( wp_unslash( $_POST['service_type'] ) ) : 'business';
	$allowed_types = array( 'design', 'development', 'marketing', 'business', 'personal' );
	if ( ! in_array( $type, $allowed_types, true ) ) {
		$type = 'business';
	}

	$delivery = isset( $_POST['delivery_timeline'] ) ? sanitize_text_field( wp_unslash( $_POST['delivery_timeline'] ) ) : '';
	$notes    = isset( $_POST['customer_notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['customer_notes'] ) ) : '';

	$fields = array();

	switch ( $type ) {
		case 'design':
			$fields['requirements'] = isset( $_POST['requirements'] ) ? sanitize_textarea_field( wp_unslash( $_POST['requirements'] ) ) : ''; // phpcs:ignore
			$fields['brand_name']     = isset( $_POST['brand_name'] ) ? sanitize_text_field( wp_unslash( $_POST['brand_name'] ) ) : ''; // phpcs:ignore
			break;
		case 'development':
			$fields['project_description'] = isset( $_POST['project_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['project_description'] ) ) : ''; // phpcs:ignore
			$fields['tech_stack']          = isset( $_POST['tech_stack'] ) ? sanitize_text_field( wp_unslash( $_POST['tech_stack'] ) ) : ''; // phpcs:ignore
			$fields['deadline']            = isset( $_POST['deadline'] ) ? sanitize_text_field( wp_unslash( $_POST['deadline'] ) ) : ''; // phpcs:ignore
			break;
		case 'marketing':
		case 'business':
		case 'personal':
		default:
			$fields['consultation_details'] = isset( $_POST['consultation_details'] ) ? sanitize_textarea_field( wp_unslash( $_POST['consultation_details'] ) ) : ''; // phpcs:ignore
			$fields['meeting_slot']         = isset( $_POST['meeting_slot'] ) ? sanitize_text_field( wp_unslash( $_POST['meeting_slot'] ) ) : ''; // phpcs:ignore
			if ( 'marketing' === $type && empty( $fields['consultation_details'] ) ) {
				$fields['consultation_details'] = isset( $_POST['requirements'] ) ? sanitize_textarea_field( wp_unslash( $_POST['requirements'] ) ) : ''; // phpcs:ignore
			}
			break;
	}

	$file_ids = dejoiy_services_booking_handle_uploads();
	if ( ! empty( $file_ids ) ) {
		$fields['uploaded_files'] = $file_ids;
	}

	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0; // phpcs:ignore

	return array(
		'service_id'         => $product_id,
		'seller_id'          => $product_id ? (int) get_post_field( 'post_author', $product_id ) : 0,
		'service_type'       => $type,
		'price_type'         => isset( $_POST['price_type'] ) ? sanitize_key( wp_unslash( $_POST['price_type'] ) ) : 'fixed', // phpcs:ignore
		'delivery_timeline'  => $delivery,
		'customer_notes'     => $notes,
		'fields'             => $fields,
		'submitted_at'       => gmdate( 'c' ),
	);
}

/**
 * @return array<int, int> Attachment IDs.
 */
function dejoiy_services_booking_handle_uploads() {
	if ( empty( $_FILES['booking_files'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return array();
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$ids      = array();
	$allowed  = array( 'jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'zip' );
	$max_size = 8 * 1024 * 1024;
	$files    = $_FILES['booking_files']; // phpcs:ignore

	$count = is_array( $files['name'] ) ? count( $files['name'] ) : 0;
	for ( $i = 0; $i < $count && $i < 5; $i++ ) {
		if ( empty( $files['name'][ $i ] ) || UPLOAD_ERR_OK !== (int) $files['error'][ $i ] ) {
			continue;
		}
		if ( (int) $files['size'][ $i ] > $max_size ) {
			continue;
		}
		$ext = strtolower( pathinfo( (string) $files['name'][ $i ], PATHINFO_EXTENSION ) );
		if ( ! in_array( $ext, $allowed, true ) ) {
			continue;
		}

		$file = array(
			'name'     => $files['name'][ $i ],
			'type'     => $files['type'][ $i ],
			'tmp_name' => $files['tmp_name'][ $i ],
			'error'    => $files['error'][ $i ],
			'size'     => $files['size'][ $i ],
		);

		$upload = wp_handle_upload( $file, array( 'test_form' => false ) );
		if ( isset( $upload['error'] ) ) {
			continue;
		}

		$attachment = array(
			'post_mime_type' => $upload['type'],
			'post_title'     => sanitize_file_name( (string) $files['name'][ $i ] ),
			'post_content'   => '',
			'post_status'    => 'private',
			'post_author'    => get_current_user_id() > 0 ? get_current_user_id() : 0,
		);
		$attach_id = wp_insert_attachment( $attachment, $upload['file'] );
		if ( $attach_id && ! is_wp_error( $attach_id ) ) {
			$meta = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
			wp_update_attachment_metadata( $attach_id, $meta );
			update_post_meta( $attach_id, '_dejoiy_service_booking_private', 1 );
			$ids[] = (int) $attach_id;
		}
	}

	return $ids;
}

/**
 * @param array<string, mixed> $item_data Item data.
 * @param array<string, mixed> $cart_item  Cart item.
 * @return array<string, mixed>
 */
function dejoiy_services_booking_cart_item_data_display( $item_data, $cart_item ) {
	if ( empty( $cart_item['dejoiy_service_booking_data'] ) || ! is_array( $cart_item['dejoiy_service_booking_data'] ) ) {
		return $item_data;
	}
	$data = $cart_item['dejoiy_service_booking_data'];
	$item_data[] = array(
		'key'   => __( 'Service booking', 'dejoiy' ),
		'value' => esc_html( dejoiy_services_booking_summary_text( $data ) ),
	);
	return $item_data;
}

/**
 * @param array<string, mixed> $data Booking data.
 * @return string
 */
function dejoiy_services_booking_summary_text( $data ) {
	$parts = array();
	if ( ! empty( $data['service_type'] ) ) {
		$parts[] = ucfirst( (string) $data['service_type'] );
	}
	if ( ! empty( $data['delivery_timeline'] ) ) {
		$parts[] = (string) $data['delivery_timeline'];
	}
	if ( ! empty( $data['fields']['requirements'] ) ) {
		$parts[] = wp_trim_words( (string) $data['fields']['requirements'], 12, '…' );
	} elseif ( ! empty( $data['fields']['project_description'] ) ) {
		$parts[] = wp_trim_words( (string) $data['fields']['project_description'], 12, '…' );
	} elseif ( ! empty( $data['fields']['consultation_details'] ) ) {
		$parts[] = wp_trim_words( (string) $data['fields']['consultation_details'], 12, '…' );
	}
	return $parts ? implode( ' · ', $parts ) : __( 'Custom service booking', 'dejoiy' );
}

/**
 * @param WC_Order_Item_Product $item          Item.
 * @param string                $cart_item_key Key.
 * @param array<string, mixed>  $values        Values.
 * @param WC_Order              $order         Order.
 */
function dejoiy_services_booking_checkout_create_order_line_item( $item, $cart_item_key, $values, $order ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	if ( empty( $values['dejoiy_service_booking_data'] ) || ! is_array( $values['dejoiy_service_booking_data'] ) ) {
		return;
	}
	$data = $values['dejoiy_service_booking_data'];
	$item->add_meta_data( '_dejoiy_service_booking', '1', true );
	$item->add_meta_data( '_dejoiy_service_booking_json', wp_json_encode( $data ), true );
	$item->add_meta_data( __( 'Service requirements', 'dejoiy' ), dejoiy_services_booking_summary_text( $data ), true );
}

/**
 * @param int       $order_id Order ID.
 * @param array     $posted   Posted.
 * @param WC_Order  $order    Order.
 */
function dejoiy_services_booking_checkout_order_processed( $order_id, $posted, $order ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	if ( ! $order instanceof WC_Order ) {
		$order = wc_get_order( $order_id );
	}
	if ( ! $order ) {
		return;
	}

	$has_booking = false;
	foreach ( $order->get_items() as $item ) {
		if ( $item->get_meta( '_dejoiy_service_booking' ) ) {
			$has_booking = true;
			break;
		}
	}

	if ( ! $has_booking ) {
		return;
	}

	$order->update_meta_data( DEJOIY_SERVICES_BOOKING_META, 'SERVICE_BOOKING' );
	$order->update_meta_data( DEJOIY_SERVICES_BOOKING_STATUS, 'pending' );
	$order->save();

	if ( function_exists( 'WC' ) && WC()->session ) {
		WC()->session->set( DEJOIY_SERVICES_BOOKING_FLOW, null );
	}

	$order->add_order_note( __( 'DEJOIY Service booking created. Awaiting seller acceptance.', 'dejoiy' ), false, true );
}

/**
 * @param WC_Order $order Order.
 */
function dejoiy_services_booking_render_order_panel( $order ) {
	if ( ! $order instanceof WC_Order || 'SERVICE_BOOKING' !== $order->get_meta( DEJOIY_SERVICES_BOOKING_META ) ) {
		return;
	}

	$status = (string) $order->get_meta( DEJOIY_SERVICES_BOOKING_STATUS );
	if ( '' === $status ) {
		$status = 'pending';
	}
	$labels = dejoiy_services_booking_status_labels();

	echo '<div class="dejoiy-service-booking-panel"><h3>' . esc_html__( 'Service booking', 'dejoiy' ) . '</h3>';
	echo '<p><strong>' . esc_html__( 'Status', 'dejoiy' ) . ':</strong> ' . esc_html( isset( $labels[ $status ] ) ? $labels[ $status ] : $status ) . '</p>';

	foreach ( $order->get_items() as $item ) {
		$json = $item->get_meta( '_dejoiy_service_booking_json' );
		if ( ! $json ) {
			continue;
		}
		$data = json_decode( $json, true );
		if ( ! is_array( $data ) ) {
			continue;
		}
		echo '<div class="dejoiy-service-booking-panel__block">';
		echo '<h4>' . esc_html( $item->get_name() ) . '</h4>';
		dejoiy_services_booking_echo_details( $data );
		echo '</div>';
	}
	echo '</div>';
}

/**
 * @param array<string, mixed> $data Data.
 */
function dejoiy_services_booking_echo_details( $data ) {
	echo '<ul class="dejoiy-service-booking-details">';
	if ( ! empty( $data['service_type'] ) ) {
		echo '<li><strong>' . esc_html__( 'Type', 'dejoiy' ) . ':</strong> ' . esc_html( ucfirst( (string) $data['service_type'] ) ) . '</li>';
	}
	if ( ! empty( $data['delivery_timeline'] ) ) {
		echo '<li><strong>' . esc_html__( 'Delivery', 'dejoiy' ) . ':</strong> ' . esc_html( (string) $data['delivery_timeline'] ) . '</li>';
	}
	if ( ! empty( $data['customer_notes'] ) ) {
		echo '<li><strong>' . esc_html__( 'Notes', 'dejoiy' ) . ':</strong> ' . esc_html( (string) $data['customer_notes'] ) . '</li>';
	}
	if ( ! empty( $data['fields'] ) && is_array( $data['fields'] ) ) {
		foreach ( $data['fields'] as $key => $val ) {
			if ( 'uploaded_files' === $key || ! is_string( $val ) || '' === $val ) {
				continue;
			}
			echo '<li><strong>' . esc_html( ucwords( str_replace( '_', ' ', $key ) ) ) . ':</strong> ' . esc_html( $val ) . '</li>';
		}
		if ( ! empty( $data['fields']['uploaded_files'] ) && is_array( $data['fields']['uploaded_files'] ) ) {
			echo '<li><strong>' . esc_html__( 'Files', 'dejoiy' ) . ':</strong> ';
			$links = array();
			foreach ( $data['fields']['uploaded_files'] as $aid ) {
				$url = wp_get_attachment_url( (int) $aid );
				if ( $url && dejoiy_services_booking_user_can_view_file( (int) $aid ) ) {
					$links[] = '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html( get_the_title( (int) $aid ) ) . '</a>';
				}
			}
			echo $links ? wp_kses_post( implode( ', ', $links ) ) : esc_html__( 'Attached (restricted)', 'dejoiy' );
			echo '</li>';
		}
	}
	echo '</ul>';
}

/**
 * @param int $attachment_id Attachment ID.
 * @return bool
 */
function dejoiy_services_booking_user_can_view_file( $attachment_id ) {
	if ( ! is_user_logged_in() ) {
		return false;
	}
	if ( current_user_can( 'manage_woocommerce' ) ) {
		return true;
	}
	$user_id = get_current_user_id();
	if ( (int) get_post_field( 'post_author', $attachment_id ) === $user_id ) {
		return true;
	}
	if ( function_exists( 'wcfm_is_vendor' ) && wcfm_is_vendor( $user_id ) ) {
		return true;
	}
	return false;
}

/**
 * Customer timeline on order view.
 *
 * @param WC_Order $order Order.
 */
function dejoiy_services_booking_customer_timeline( $order ) {
	if ( ! $order instanceof WC_Order || 'SERVICE_BOOKING' !== $order->get_meta( DEJOIY_SERVICES_BOOKING_META ) ) {
		return;
	}

	$current = (string) $order->get_meta( DEJOIY_SERVICES_BOOKING_STATUS );
	if ( '' === $current ) {
		$current = 'pending';
	}

	$steps = array( 'pending', 'accepted', 'in_progress', 'delivered', 'completed' );
	$labels = dejoiy_services_booking_status_labels();

	echo '<section class="dsv-booking-track" aria-label="' . esc_attr__( 'Booking progress', 'dejoiy' ) . '"><h3>' . esc_html__( 'Service booking progress', 'dejoiy' ) . '</h3><ol class="dsv-booking-track__steps">';
	$reached = true;
	foreach ( $steps as $step ) {
		$class = 'dsv-booking-track__step';
		if ( $step === $current ) {
			$class .= ' is-current';
			$reached = false;
		} elseif ( $reached ) {
			$class .= ' is-done';
		}
		echo '<li class="' . esc_attr( $class ) . '">' . esc_html( isset( $labels[ $step ] ) ? $labels[ $step ] : $step ) . '</li>';
	}
	echo '</ol></section>';
}

/**
 * AJAX: product data for modal.
 */
function dejoiy_services_ajax_booking_product() {
	check_ajax_referer( 'dejoiy_services_booking', 'nonce' );

	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0; // phpcs:ignore
	if ( $product_id < 1 || ! dejoiy_services_is_product( $product_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid service.', 'dejoiy' ) ) );
	}

	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		wp_send_json_error( array( 'message' => __( 'Service not found.', 'dejoiy' ) ) );
	}

	$vendor_id = (int) get_post_field( 'post_author', $product_id );
	$seller    = function_exists( 'dejoiy_universe_seller_label' ) ? dejoiy_universe_seller_label( $product_id ) : '';

	wp_send_json_success(
		array(
			'product_id'    => $product_id,
			'title'         => $product->get_name(),
			'price_html'    => wp_strip_all_tags( $product->get_price_html() ),
			'seller'        => $seller,
			'seller_id'     => $vendor_id,
			'service_type'  => dejoiy_services_detect_service_type( $product_id ),
			'price_type'    => dejoiy_services_detect_price_type( $product_id ),
			'delivery'      => dejoiy_services_delivery_label( $product_id ),
			'image'         => get_the_post_thumbnail_url( $product_id, 'woocommerce_thumbnail' ),
			'is_logged_in'  => is_user_logged_in(),
			'login_url'     => wc_get_page_permalink( 'myaccount' ),
		)
	);
}

/**
 * AJAX: submit booking → cart → checkout.
 */
function dejoiy_services_ajax_booking_submit() {
	check_ajax_referer( 'dejoiy_services_booking', 'nonce' );

	if ( ! is_user_logged_in() ) {
		wp_send_json_error(
			array(
				'message'   => __( 'Please sign in to book a service.', 'dejoiy' ),
				'login_url' => wc_get_page_permalink( 'myaccount' ),
			)
		);
	}

	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0; // phpcs:ignore
	if ( $product_id < 1 || ! dejoiy_services_is_product( $product_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid service.', 'dejoiy' ) ) );
	}

	$type = dejoiy_services_detect_service_type( $product_id );
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$posted_type = isset( $_POST['service_type'] ) ? sanitize_key( wp_unslash( $_POST['service_type'] ) ) : $type;

	if ( 'design' === $posted_type && empty( $_POST['requirements'] ) ) { // phpcs:ignore
		wp_send_json_error( array( 'message' => __( 'Please describe your requirements.', 'dejoiy' ) ) );
	}
	if ( 'development' === $posted_type && empty( $_POST['project_description'] ) ) { // phpcs:ignore
		wp_send_json_error( array( 'message' => __( 'Please add a project description.', 'dejoiy' ) ) );
	}

	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		wp_send_json_error( array( 'message' => __( 'Cart is unavailable.', 'dejoiy' ) ) );
	}

	$GLOBALS['dejoiy_services_doing_booking_submit'] = true;

	$cart_key = WC()->cart->add_to_cart( $product_id, 1 );
	unset( $GLOBALS['dejoiy_services_doing_booking_submit'] );

	if ( ! $cart_key ) {
		$notices = wc_get_notices( 'error' );
		$msg     = __( 'Could not add booking to cart. Please try again.', 'dejoiy' );
		if ( ! empty( $notices[0]['notice'] ) ) {
			$msg = wp_strip_all_tags( (string) $notices[0]['notice'] );
		}
		wc_clear_notices();
		wp_send_json_error( array( 'message' => $msg ) );
	}

	if ( WC()->session ) {
		WC()->session->set( DEJOIY_SERVICES_BOOKING_FLOW, '1' );
	}

	$checkout = add_query_arg( 'dejoiy_services_booking', '1', wc_get_checkout_url() );

	wp_send_json_success(
		array(
			'message'      => __( 'Booking added. Complete checkout to confirm.', 'dejoiy' ),
			'checkout_url' => $checkout,
			'cart_url'     => add_query_arg( 'dejoiy_services_booking', '1', wc_get_cart_url() ),
		)
	);
}

/**
 * AJAX: vendor updates booking status.
 */
function dejoiy_services_ajax_booking_status() {
	check_ajax_referer( 'dejoiy_services_booking_vendor', 'nonce' );

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'dejoiy' ) ) );
	}

	$order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0; // phpcs:ignore
	$status   = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : ''; // phpcs:ignore

	$labels = dejoiy_services_booking_status_labels();
	if ( ! isset( $labels[ $status ] ) || 'pending' === $status ) {
		wp_send_json_error( array( 'message' => __( 'Invalid status.', 'dejoiy' ) ) );
	}

	$order = wc_get_order( $order_id );
	if ( ! $order || 'SERVICE_BOOKING' !== $order->get_meta( DEJOIY_SERVICES_BOOKING_META ) ) {
		wp_send_json_error( array( 'message' => __( 'Not a service booking order.', 'dejoiy' ) ) );
	}

	if ( ! dejoiy_services_booking_user_can_manage_order( $order ) ) {
		wp_send_json_error( array( 'message' => __( 'You cannot update this booking.', 'dejoiy' ) ) );
	}

	$order->update_meta_data( DEJOIY_SERVICES_BOOKING_STATUS, $status );
	$order->save();

	/* translators: %s: status label */
	$order->add_order_note( sprintf( __( 'Service booking status updated to %s.', 'dejoiy' ), $labels[ $status ] ), false, true );

	if ( 'in_progress' === $status && 'processing' !== $order->get_status() ) {
		$order->update_status( 'processing', __( 'Service in progress.', 'dejoiy' ), true );
	}
	if ( 'completed' === $status && 'completed' !== $order->get_status() ) {
		$order->update_status( 'completed', __( 'Service booking completed.', 'dejoiy' ), true );
	}
	if ( 'rejected' === $status ) {
		$order->update_status( 'cancelled', __( 'Service booking rejected by seller.', 'dejoiy' ), true );
	}

	wp_send_json_success( array( 'status' => $status, 'label' => $labels[ $status ] ) );
}

/**
 * @param WC_Order $order Order.
 * @return bool
 */
function dejoiy_services_booking_user_can_manage_order( $order ) {
	if ( current_user_can( 'manage_woocommerce' ) ) {
		return true;
	}
	$user_id = get_current_user_id();
	foreach ( $order->get_items() as $item ) {
		$product_id = $item->get_product_id();
		if ( $product_id && (int) get_post_field( 'post_author', $product_id ) === $user_id ) {
			return true;
		}
	}
	return false;
}

/**
 * WCFM order details panel.
 *
 * @param int   $order_id Order ID.
 * @param mixed $order    Order object.
 */
function dejoiy_services_booking_wcfm_panel( $order_id, $order ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	$wc_order = wc_get_order( $order_id );
	if ( $wc_order ) {
		dejoiy_services_booking_render_vendor_actions( $wc_order );
		dejoiy_services_booking_render_order_panel( $wc_order );
	}
}

/**
 * @param WC_Order $order Order.
 */
function dejoiy_services_booking_render_vendor_actions( $order ) {
	if ( ! $order instanceof WC_Order || 'SERVICE_BOOKING' !== $order->get_meta( DEJOIY_SERVICES_BOOKING_META ) ) {
		return;
	}
	if ( ! dejoiy_services_booking_user_can_manage_order( $order ) ) {
		return;
	}

	$current = (string) $order->get_meta( DEJOIY_SERVICES_BOOKING_STATUS );
	if ( '' === $current ) {
		$current = 'pending';
	}

	$actions = array(
		'accepted'    => __( 'Accept', 'dejoiy' ),
		'rejected'    => __( 'Reject', 'dejoiy' ),
		'in_progress' => __( 'In Progress', 'dejoiy' ),
		'delivered'   => __( 'Delivered', 'dejoiy' ),
		'completed'   => __( 'Completed', 'dejoiy' ),
	);

	echo '<div class="dejoiy-service-booking-actions" data-order-id="' . esc_attr( (string) $order->get_id() ) . '">';
	echo '<p><strong>' . esc_html__( 'Service booking actions', 'dejoiy' ) . '</strong></p>';
	echo '<div class="dejoiy-service-booking-actions__btns">';
	foreach ( $actions as $key => $label ) {
		echo '<button type="button" class="button dejoiy-svc-status-btn" data-status="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</button>';
	}
	echo '</div><p class="dejoiy-service-booking-actions__current">' . esc_html__( 'Current:', 'dejoiy' ) . ' <span>' . esc_html( $current ) . '</span></p></div>';
}

/**
 * Register WooCommerce hooks.
 */
function dejoiy_services_booking_register_hooks() {
	if ( ! dejoiy_services_booking_active() ) {
		return;
	}

	add_filter( 'woocommerce_cart_item_visible', 'dejoiy_services_booking_cart_item_visible', 25, 3 );
	add_filter( 'woocommerce_widget_cart_item_visible', 'dejoiy_services_booking_cart_item_visible', 25, 3 );
	add_filter( 'woocommerce_checkout_cart_item_visible', 'dejoiy_services_booking_cart_item_visible', 25, 3 );
	add_filter( 'woocommerce_add_to_cart_validation', 'dejoiy_services_booking_add_to_cart_validation', 25, 3 );
	add_filter( 'woocommerce_add_cart_item_data', 'dejoiy_services_booking_add_cart_item_data', 25, 3 );
	add_filter( 'woocommerce_get_cart_item_from_session', 'dejoiy_services_booking_get_cart_item_from_session', 25, 3 );
	add_filter( 'woocommerce_get_item_data', 'dejoiy_services_booking_cart_item_data_display', 25, 2 );
	add_action( 'woocommerce_checkout_create_order_line_item', 'dejoiy_services_booking_checkout_create_order_line_item', 25, 4 );
	add_action( 'woocommerce_checkout_order_processed', 'dejoiy_services_booking_checkout_order_processed', 25, 3 );

	add_action( 'woocommerce_admin_order_data_after_billing_address', 'dejoiy_services_booking_render_order_panel' );
	add_action( 'woocommerce_order_details_after_order_table', 'dejoiy_services_booking_customer_timeline', 15 );
	add_action( 'woocommerce_view_order', 'dejoiy_services_booking_customer_timeline', 15 );

	add_action( 'wcfm_after_order_details', 'dejoiy_services_booking_wcfm_panel', 30, 2 );
	add_action( 'wp_ajax_dejoiy_services_booking_status', 'dejoiy_services_ajax_booking_status' );
}
add_action( 'woocommerce_init', 'dejoiy_services_booking_register_hooks' );
