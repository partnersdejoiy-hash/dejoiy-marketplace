<?php
/**
 * Public DEJOIY OS REST API — headless readiness (read-only).
 *
 * Routes: /wp-json/dejoiy/v1/*
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge\Api;

use Dejoiy\AiControlBridge\Services\DejoiyCatalogService;
use WP_REST_Request;
use WP_REST_Server;

/**
 * Headless catalog endpoints for future Next.js frontend.
 */
class DejoiyOsController {

	/**
	 * @var DejoiyCatalogService
	 */
	private $catalog;

	/**
	 * @param DejoiyCatalogService $catalog Catalog service.
	 */
	public function __construct( DejoiyCatalogService $catalog ) {
		$this->catalog = $catalog;
	}

	/**
	 * Register routes.
	 */
	public function register_routes(): void {
		$ns = 'dejoiy/v1';

		register_rest_route(
			$ns,
			'/products',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'products' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'page'     => array( 'default' => 1, 'sanitize_callback' => 'absint' ),
					'per_page' => array( 'default' => 12, 'sanitize_callback' => 'absint' ),
					'search'   => array( 'sanitize_callback' => 'sanitize_text_field' ),
					'eco'      => array( 'sanitize_callback' => 'sanitize_key' ),
				),
			)
		);

		register_rest_route(
			$ns,
			'/products/(?P<id>\d+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'product' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'id' => array( 'sanitize_callback' => 'absint' ),
				),
			)
		);

		register_rest_route(
			$ns,
			'/products/dpin/(?P<dpin>[A-Z0-9]{11})',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'product_by_dpin' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			$ns,
			'/vendors',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'vendors' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			$ns,
			'/books',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'books' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'page'     => array( 'default' => 1, 'sanitize_callback' => 'absint' ),
					'per_page' => array( 'default' => 12, 'sanitize_callback' => 'absint' ),
					'lang'     => array( 'sanitize_callback' => 'sanitize_text_field' ),
				),
			)
		);

		register_rest_route(
			$ns,
			'/search',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'search' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'q'   => array( 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ),
					'eco' => array( 'sanitize_callback' => 'sanitize_key' ),
				),
			)
		);

		register_rest_route(
			$ns,
			'/orders',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'orders' ),
				'permission_callback' => array( $this, 'orders_permission' ),
			)
		);
	}

	/**
	 * Orders require authenticated customer (placeholder for headless checkout).
	 *
	 * @return bool
	 */
	public function orders_permission(): bool {
		return is_user_logged_in();
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function products( WP_REST_Request $request ) {
		return rest_ensure_response(
			$this->catalog->list_products(
				(int) $request->get_param( 'page' ),
				min( 48, (int) $request->get_param( 'per_page' ) ),
				(string) $request->get_param( 'search' ),
				(string) $request->get_param( 'eco' )
			)
		);
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function product( WP_REST_Request $request ) {
		$item = $this->catalog->get_product( (int) $request->get_param( 'id' ) );
		if ( ! $item ) {
			return new \WP_Error( 'not_found', __( 'Product not found.', 'dejoiy-ai-control-bridge' ), array( 'status' => 404 ) );
		}
		return rest_ensure_response( $item );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function product_by_dpin( WP_REST_Request $request ) {
		$item = $this->catalog->get_product_by_dpin( (string) $request->get_param( 'dpin' ) );
		if ( ! $item ) {
			return new \WP_Error( 'not_found', __( 'Product not found.', 'dejoiy-ai-control-bridge' ), array( 'status' => 404 ) );
		}
		return rest_ensure_response( $item );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 */
	public function vendors( WP_REST_Request $request ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		return rest_ensure_response( $this->catalog->list_vendors() );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 */
	public function books( WP_REST_Request $request ) {
		return rest_ensure_response(
			$this->catalog->list_books(
				(int) $request->get_param( 'page' ),
				min( 48, (int) $request->get_param( 'per_page' ) ),
				(string) $request->get_param( 'lang' )
			)
		);
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 */
	public function search( WP_REST_Request $request ) {
		return rest_ensure_response(
			$this->catalog->search(
				(string) $request->get_param( 'q' ),
				(string) $request->get_param( 'eco' )
			)
		);
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 */
	public function orders( WP_REST_Request $request ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		return rest_ensure_response( $this->catalog->list_orders_for_current_user() );
	}
}
