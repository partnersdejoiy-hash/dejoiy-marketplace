<?php
/**
 * DEJOIY catalog serialization for headless API.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge\Services;

use WP_Query;

/**
 * Read-only product/vendor/book payloads for dejoiy/v1 REST.
 */
class DejoiyCatalogService {

	/**
	 * @param int    $page Page.
	 * @param int    $per_page Per page.
	 * @param string $search Search term.
	 * @param string $eco Ecosystem filter.
	 * @return array<string, mixed>
	 */
	public function list_products( int $page, int $per_page, string $search, string $eco ): array {
		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => max( 1, $per_page ),
			'paged'          => max( 1, $page ),
		);
		if ( '' !== $search ) {
			$args['s'] = $search;
		}
		$q = new WP_Query( $args );
		$items = array();
		foreach ( $q->posts as $post ) {
			$row = $this->serialize_product( (int) $post->ID );
			if ( $row && ( '' === $eco || $eco === ( $row['ecosystem'] ?? '' ) ) ) {
				$items[] = $row;
			}
		}
		return array(
			'items'       => $items,
			'total'       => (int) $q->found_posts,
			'page'        => max( 1, $page ),
			'per_page'    => max( 1, $per_page ),
			'total_pages' => (int) $q->max_num_pages,
		);
	}

	/**
	 * @param int $product_id Product ID.
	 * @return array<string, mixed>|null
	 */
	public function get_product( int $product_id ): ?array {
		if ( $product_id < 1 || 'product' !== get_post_type( $product_id ) ) {
			return null;
		}
		return $this->serialize_product( $product_id );
	}

	/**
	 * @param string $dpin DPIN.
	 * @return array<string, mixed>|null
	 */
	public function get_product_by_dpin( string $dpin ): ?array {
		if ( function_exists( 'dejoiy_dpin_resolve_product_id' ) ) {
			$id = dejoiy_dpin_resolve_product_id( $dpin );
			return $id > 0 ? $this->get_product( $id ) : null;
		}
		return null;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function list_vendors(): array {
		$vendors = array();
		if ( function_exists( 'get_users' ) ) {
			$users = get_users(
				array(
					'role'   => 'wcfm_vendor',
					'number' => 50,
					'fields' => array( 'ID', 'display_name', 'user_nicename' ),
				)
			);
			foreach ( $users as $user ) {
				$vendors[] = array(
					'id'       => (int) $user->ID,
					'name'     => (string) $user->display_name,
					'slug'     => (string) $user->user_nicename,
					'store_url'=> function_exists( 'wcfmmp_get_store_url' ) ? wcfmmp_get_store_url( (int) $user->ID ) : '',
				);
			}
		}
		return array( 'items' => $vendors );
	}

	/**
	 * @param int    $page Page.
	 * @param int    $per_page Per page.
	 * @param string $lang Language code.
	 * @return array<string, mixed>
	 */
	public function list_books( int $page, int $per_page, string $lang ): array {
		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => max( 1, $per_page ),
			'paged'          => max( 1, $page ),
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'   => '_dejoiy_library_book',
					'value' => '1',
				),
			),
		);
		$q = new WP_Query( $args );
		$items = array();
		foreach ( $q->posts as $post ) {
			$row = $this->serialize_product( (int) $post->ID );
			if ( $row ) {
				$row['type'] = 'book';
				$items[] = $row;
			}
		}
		return array(
			'items'    => $items,
			'total'    => (int) $q->found_posts,
			'page'     => max( 1, $page ),
			'per_page' => max( 1, $per_page ),
			'lang'     => $lang,
		);
	}

	/**
	 * @param string $query Query.
	 * @param string $eco Ecosystem.
	 * @return array<string, mixed>
	 */
	public function search( string $query, string $eco ): array {
		$products = $this->list_products( 1, 24, $query, $eco );
		return array(
			'query'    => $query,
			'eco'      => $eco,
			'products' => $products['items'],
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function list_orders_for_current_user(): array {
		if ( ! is_user_logged_in() || ! function_exists( 'wc_get_orders' ) ) {
			return array( 'items' => array() );
		}
		$orders = wc_get_orders(
			array(
				'customer_id' => get_current_user_id(),
				'limit'       => 20,
				'orderby'     => 'date',
				'order'       => 'DESC',
			)
		);
		$items = array();
		foreach ( $orders as $order ) {
			$items[] = array(
				'id'     => $order->get_id(),
				'number' => $order->get_order_number(),
				'status' => $order->get_status(),
				'total'  => $order->get_total(),
				'date'   => $order->get_date_created() ? $order->get_date_created()->format( DATE_ATOM ) : '',
			);
		}
		return array( 'items' => $items );
	}

	/**
	 * @param int $product_id Product ID.
	 * @return array<string, mixed>|null
	 */
	private function serialize_product( int $product_id ): ?array {
		if ( ! function_exists( 'wc_get_product' ) ) {
			return null;
		}
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return null;
		}
		$eco = function_exists( 'dejoiy_get_product_ecosystem' ) ? dejoiy_get_product_ecosystem( $product_id ) : 'marketplace';
		$url = function_exists( 'dejoiy_ecosystem_product_url' ) ? dejoiy_ecosystem_product_url( $product_id, $eco ) : get_permalink( $product_id );
		$dpin = function_exists( 'dejoiy_get_product_dpin' ) ? dejoiy_get_product_dpin( $product_id ) : '';
		if ( '' === $dpin && function_exists( 'dejoiy_ensure_product_dpin' ) ) {
			$dpin = dejoiy_ensure_product_dpin( $product_id );
		}
		return array(
			'id'          => $product_id,
			'dpin'        => $dpin,
			'name'        => $product->get_name(),
			'slug'        => $product->get_slug(),
			'ecosystem'   => $eco,
			'price'       => $product->get_price(),
			'price_html'  => $product->get_price_html(),
			'image'       => get_the_post_thumbnail_url( $product_id, 'woocommerce_thumbnail' ) ?: '',
			'url'         => $url,
			'vendor_id'   => (int) get_post_field( 'post_author', $product_id ),
			'in_stock'    => $product->is_in_stock(),
			'type'        => $product->get_type(),
		);
	}
}
