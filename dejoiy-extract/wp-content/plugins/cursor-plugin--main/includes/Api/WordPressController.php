<?php
/**
 * WordPress management REST API controller.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge\Api;

use Dejoiy\AiControlBridge\Auth\JwtAuth;
use Dejoiy\AiControlBridge\Auth\RestAuthMiddleware;
use Dejoiy\AiControlBridge\Security\ActionClassifier;
use Dejoiy\AiControlBridge\Security\ApprovalQueue;
use Dejoiy\AiControlBridge\Security\AuditLogger;
use Dejoiy\AiControlBridge\Services\AgentService;
use Dejoiy\AiControlBridge\Services\CacheService;
use Dejoiy\AiControlBridge\Services\MigrationService;
use Dejoiy\AiControlBridge\Services\WordPressService;
use WP_REST_Request;
use WP_REST_Server;

/**
 * WordPress content and settings API.
 */
class WordPressController extends BaseController {

	/**
	 * @var WordPressService
	 */
	private $wp;

	/**
	 * @var CacheService
	 */
	private $cache;

	/**
	 * @var MigrationService
	 */
	private $migrations;

	/**
	 * @var RestAuthMiddleware
	 */
	private $auth;

	/**
	 * @var JwtAuth
	 */
	private $jwt;

	/**
	 * @var AgentService
	 */
	private $agents;

	/**
	 * @param AuditLogger        $audit      Audit.
	 * @param ActionClassifier   $classifier Classifier.
	 * @param ApprovalQueue      $approvals  Approvals.
	 * @param WordPressService   $wp         WP service.
	 * @param CacheService       $cache      Cache.
	 * @param MigrationService   $migrations Migrations.
	 * @param RestAuthMiddleware $auth       Auth.
	 * @param JwtAuth            $jwt        JWT.
	 * @param AgentService       $agents     Agents.
	 */
	public function __construct(
		AuditLogger $audit,
		ActionClassifier $classifier,
		ApprovalQueue $approvals,
		WordPressService $wp,
		CacheService $cache,
		MigrationService $migrations,
		RestAuthMiddleware $auth,
		JwtAuth $jwt,
		AgentService $agents
	) {
		parent::__construct( $audit, $classifier, $approvals );
		$this->wp         = $wp;
		$this->cache      = $cache;
		$this->migrations = $migrations;
		$this->auth       = $auth;
		$this->jwt        = $jwt;
		$this->agents     = $agents;
	}

	/**
	 * Register routes.
	 */
	public function register_routes(): void {
		$ns = DEJOIY_ACB_REST_NAMESPACE;

		register_rest_route( $ns, '/wordpress/posts', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'create_post' ),
			'permission_callback' => $this->auth->require( 'wordpress.write' ),
		) );

		register_rest_route( $ns, '/wordpress/products', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'create_product' ),
			'permission_callback' => $this->auth->require( 'wordpress.write' ),
		) );

		register_rest_route( $ns, '/wordpress/menus', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'create_menu' ),
			'permission_callback' => $this->auth->require( 'wordpress.write' ),
		) );

		register_rest_route( $ns, '/wordpress/users', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'create_user' ),
			'permission_callback' => $this->auth->require( 'wordpress.write' ),
		) );

		register_rest_route( $ns, '/wordpress/post-types', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'register_post_type' ),
			'permission_callback' => $this->auth->require( 'wordpress.write' ),
		) );

		register_rest_route( $ns, '/wordpress/options', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_options' ),
				'permission_callback' => $this->auth->require( 'wordpress.read' ),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'update_option' ),
				'permission_callback' => $this->auth->require( 'wordpress.write' ),
			),
		) );

		register_rest_route( $ns, '/wordpress/woocommerce', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'woocommerce' ),
			'permission_callback' => $this->auth->require( 'wordpress.read' ),
		) );

		register_rest_route( $ns, '/wordpress/wcfm', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'wcfm' ),
			'permission_callback' => $this->auth->require( 'wordpress.read' ),
		) );

		register_rest_route( $ns, '/cache/clear', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'clear_cache' ),
			'permission_callback' => $this->auth->require( 'cache.clear' ),
		) );

		register_rest_route( $ns, '/migrations/run', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'run_migrations' ),
			'permission_callback' => $this->auth->require( 'migrations.run' ),
		) );

		register_rest_route( $ns, '/auth/token', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'issue_token' ),
			'permission_callback' => $this->auth->require( '' ),
		) );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function create_post( WP_REST_Request $request ) {
		$params = $request->get_json_params() ?: array();
		return $this->respond( $this->wp->create_post( $params ), $request, 'wordpress.write' );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function create_product( WP_REST_Request $request ) {
		$params = $request->get_json_params() ?: array();
		return $this->respond( $this->wp->create_product( $params ), $request, 'wordpress.write' );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function create_menu( WP_REST_Request $request ) {
		$name = $request->get_param( 'name' ) ?: 'New Menu';
		return $this->respond( $this->wp->create_menu( $name ), $request, 'wordpress.write' );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function create_user( WP_REST_Request $request ) {
		$params = $request->get_json_params() ?: array();
		return $this->respond( $this->wp->create_user( $params ), $request, 'wordpress.write' );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function register_post_type( WP_REST_Request $request ) {
		$params = $request->get_json_params() ?: array();
		return $this->respond( $this->wp->register_post_type( $params ), $request, 'wordpress.write' );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_options( WP_REST_Request $request ) {
		return $this->respond( $this->wp->get_options( $request->get_param( 'key' ) ), $request, 'wordpress.read' );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function update_option( WP_REST_Request $request ) {
		return $this->respond(
			$this->wp->update_option( $request->get_param( 'key' ) ?: '', $request->get_param( 'value' ) ),
			$request,
			'wordpress.write'
		);
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function woocommerce( WP_REST_Request $request ) {
		return $this->respond( $this->wp->get_woocommerce_config(), $request, 'wordpress.read' );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function wcfm( WP_REST_Request $request ) {
		return $this->respond( $this->wp->get_wcfm_config(), $request, 'wordpress.read' );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function clear_cache( WP_REST_Request $request ) {
		return $this->respond( $this->cache->clear_all(), $request, 'cache.clear' );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function run_migrations( WP_REST_Request $request ) {
		return $this->respond( $this->migrations->run(), $request, 'migrations.run' );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function issue_token( WP_REST_Request $request ) {
		$agent = $this->agent();
		if ( ! $agent ) {
			return new \WP_Error( 'unauthorized', __( 'Authentication required.', 'dejoiy-ai-control-bridge' ), array( 'status' => 401 ) );
		}

		$token = $this->jwt->issue_token( $agent );
		return $this->respond( array( 'token' => $token, 'expires_in' => (int) get_option( 'dejoiy_acb_jwt_expiry', 3600 ) ), $request, 'auth.token' );
	}
}
