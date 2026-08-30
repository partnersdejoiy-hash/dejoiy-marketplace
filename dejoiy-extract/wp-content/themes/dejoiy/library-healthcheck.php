<?php
/**
 * Temporary Nexus diagnostics — delete after debugging.
 *
 * @package Dejoiy
 */

$wp_load = dirname( __DIR__, 3 ) . '/wp-load.php';
if ( ! is_readable( $wp_load ) ) {
	$wp_load = dirname( __DIR__, 4 ) . '/wp-load.php';
}
require_once $wp_load;

header( 'Content-Type: text/plain; charset=utf-8' );

$checks = array();

try {
	$checks['render_book_card'] = function_exists( 'dejoiy_library_render_book_card' ) ? 'yes' : 'no';
	$checks['reader_url']       = function_exists( 'dejoiy_library_reader_url' ) ? dejoiy_library_reader_url( 1 ) : 'missing';
	if ( function_exists( 'dejoiy_library_ensure_customize_loaded' ) ) {
		dejoiy_library_ensure_customize_loaded();
	}
	$q                          = function_exists( 'dejoiy_library_query_books' )
		? dejoiy_library_query_books( array( 'posts_per_page' => 1 ) )
		: null;
	$checks['query_count']      = $q instanceof WP_Query ? (string) $q->post_count : ( $q ? 'bad query' : 'customize not loaded' );
	if ( ! empty( $q->posts[0] ) && function_exists( 'dejoiy_library_render_book_card' ) ) {
		$html                     = dejoiy_library_render_book_card( $q->posts[0] );
		$checks['card_render']    = strlen( $html ) > 20 ? 'ok' : 'empty';
	}
	$checks['is_landing']       = function_exists( 'dejoiy_library_is_landing' ) ? ( dejoiy_library_is_landing() ? 'yes' : 'no' ) : 'n/a';
	$checks['covers_v2']        = (string) get_option( 'dejoiy_library_covers_v2', 'unset' );
	$checks['gutenberg_sync'] = (string) get_option( 'dejoiy_library_gutenberg_sync_done', 'pending' );
	$checks['gutenberg_offset'] = (string) get_option( 'dejoiy_library_gutenberg_offset', '0' );
	$checks['mocks_retired']  = (string) get_option( 'dejoiy_library_mocks_retired', 'no' );
	$checks['gutenberg_queue'] = function_exists( 'dejoiy_library_get_catalog_sync_queue' )
		? (string) count( dejoiy_library_get_catalog_sync_queue() )
		: 'n/a';
	$q_pg = new WP_Query(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'meta_key'       => '_dejoiy_gutenberg_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'fields'         => 'ids',
		)
	);
	$checks['gutenberg_live'] = (string) $q_pg->found_posts;
	wp_reset_postdata();
	$checks['remove_url_fn']    = function_exists( 'dejoiy_library_get_remove_item_url' ) ? 'yes' : 'no';
	$checks['use_cart_tpl_fn']  = function_exists( 'dejoiy_library_use_cart_template' ) ? 'yes' : 'no';
	if ( function_exists( 'dejoiy_library_get_remove_item_url' ) ) {
		$checks['remove_url_sample'] = dejoiy_library_get_remove_item_url( 'abc123' );
	}
	if ( function_exists( 'dejoiy_library_use_cart_template' ) ) {
		$checks['use_cart_tpl'] = dejoiy_library_use_cart_template() ? 'yes' : 'no';
	}
	$cart_resp = wp_remote_get(
		home_url( '/cart/?dejoiy_library=1' ),
		array( 'timeout' => 30, 'sslverify' => true )
	);
	if ( is_wp_error( $cart_resp ) ) {
		$checks['cart_http'] = $cart_resp->get_error_message();
	} else {
		$cart_body           = (string) wp_remote_retrieve_body( $cart_resp );
		$checks['cart_http'] = 'code=' . (int) wp_remote_retrieve_response_code( $cart_resp ) . ' len=' . strlen( $cart_body );
		$checks['cart_dlu']  = ( false !== strpos( $cart_body, 'dlu-cart-page' ) ) ? 'yes' : 'no';
		$checks['cart_err']  = ( false !== strpos( $cart_body, 'critical error' ) ) ? 'yes' : 'no';
	}

	if ( function_exists( 'dejoiy_library_universe_render' ) ) {
		$html                      = dejoiy_library_universe_render();
		$checks['universe_render'] = strlen( $html ) > 500 ? 'ok len=' . strlen( $html ) : 'short';
	}

	if ( function_exists( 'dejoiy_library_universe_force_content' ) ) {
		$checks['force_content'] = strlen( dejoiy_library_universe_force_content( '' ) ) > 500 ? 'ok' : 'short';
	}

	$page = get_page_by_path( 'dejoiy-library' );
	if ( $page ) {
		$resp = wp_remote_get(
			get_permalink( $page ),
			array( 'timeout' => 30, 'sslverify' => true )
		);
		if ( is_wp_error( $resp ) ) {
			$checks['page_http'] = $resp->get_error_message();
		} else {
			$code                = (int) wp_remote_retrieve_response_code( $resp );
			$body                = (string) wp_remote_retrieve_body( $resp );
			$checks['page_http']   = 'code=' . $code . ' len=' . strlen( $body );
			$checks['page_has_dlu'] = ( false !== strpos( $body, 'dlu-root' ) ) ? 'yes' : 'no';
			if ( false !== strpos( $body, 'critical error' ) ) {
				$checks['page_error'] = 'critical error in body';
			}
		}
	}
	$checks['shop_hidden_v1'] = (string) get_option( 'dejoiy_library_shop_hidden_v1', 'unset' );

	if ( function_exists( 'dejoiy_library_wc_product_query_tax_query' ) ) {
		try {
			$tax                         = dejoiy_library_wc_product_query_tax_query( array(), null );
			$checks['wc_tax_filter']     = is_array( $tax ) ? 'ok clauses=' . count( $tax ) : 'bad';
			$checks['nexus_term_count']  = function_exists( 'dejoiy_library_get_nexus_category_term_ids' )
				? (string) count( dejoiy_library_get_nexus_category_term_ids() )
				: 'n/a';
		} catch ( Throwable $inner ) {
			$checks['wc_tax_filter'] = $inner->getMessage();
		}
	}

	$shop_resp = wp_remote_get(
		home_url( '/shop/' ),
		array( 'timeout' => 45, 'sslverify' => true, 'headers' => array( 'Cache-Control' => 'no-cache' ) )
	);
	if ( is_wp_error( $shop_resp ) ) {
		$checks['shop_http'] = $shop_resp->get_error_message();
	} else {
		$shop_body             = (string) wp_remote_retrieve_body( $shop_resp );
		$checks['shop_http']   = 'code=' . (int) wp_remote_retrieve_response_code( $shop_resp );
		$checks['shop_fatal']  = ( false !== stripos( $shop_body, 'critical error' ) ) ? 'yes' : 'no';
		$checks['shop_len']    = (string) strlen( $shop_body );
	}

	if ( class_exists( 'WC_Product_Query' ) ) {
		try {
			$wc_q = new WC_Product_Query(
				array(
					'limit'  => 3,
					'status' => 'publish',
				)
			);
			$ids  = $wc_q->get_products();
			$checks['wc_product_query'] = is_array( $ids ) ? 'ok count=' . count( $ids ) : 'bad';
		} catch ( Throwable $wc_e ) {
			$checks['wc_product_query'] = $wc_e->getMessage();
		}
	}

	try {
		$tax = function_exists( 'dejoiy_library_append_nexus_tax_exclusion' )
			? dejoiy_library_append_nexus_tax_exclusion( array() )
			: array();
		$archive_q = new WP_Query(
			array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => 6,
				'tax_query'      => $tax,
			)
		);
		$checks['archive_query'] = 'posts=' . (int) $archive_q->post_count;
		wp_reset_postdata();
	} catch ( Throwable $archive_e ) {
		$checks['archive_query'] = $archive_e->getMessage();
	}

	if ( function_exists( 'dejoiy_library_product_is_visible' ) && ! empty( $ids[0] ) ) {
		try {
			$checks['is_visible_filter'] = dejoiy_library_product_is_visible( true, (int) $ids[0], wc_get_product( (int) $ids[0] ) )
				? 'visible'
				: 'hidden';
		} catch ( Throwable $vis_e ) {
			$checks['is_visible_filter'] = $vis_e->getMessage();
		}
	}
} catch ( Throwable $e ) {
	$checks['error'] = $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine();
}

if ( isset( $_GET['pause_exclusion'] ) && '1' === (string) $_GET['pause_exclusion'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	update_option( 'dejoiy_library_shop_exclusion_pause', '1' );
	$checks['exclusion_paused'] = 'yes';
}
if ( isset( $_GET['resume_exclusion'] ) && '1' === (string) $_GET['resume_exclusion'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	delete_option( 'dejoiy_library_shop_exclusion_pause' );
	$checks['exclusion_paused'] = 'no';
}

echo wp_json_encode( $checks, JSON_PRETTY_PRINT );
