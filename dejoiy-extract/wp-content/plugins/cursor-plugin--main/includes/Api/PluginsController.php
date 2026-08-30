<?php
/**
 * Plugins REST API controller.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge\Api;

use Dejoiy\AiControlBridge\Auth\RestAuthMiddleware;
use Dejoiy\AiControlBridge\Security\ActionClassifier;
use Dejoiy\AiControlBridge\Security\ApprovalQueue;
use Dejoiy\AiControlBridge\Security\AuditLogger;
use Dejoiy\AiControlBridge\Services\PluginManagerService;
use WP_REST_Request;
use WP_REST_Server;

/**
 * Plugin management API.
 */
class PluginsController extends BaseController {

	/**
	 * @var PluginManagerService
	 */
	private $plugins;

	/**
	 * @var RestAuthMiddleware
	 */
	private $auth;

	/**
	 * @param AuditLogger          $audit      Audit.
	 * @param ActionClassifier     $classifier Classifier.
	 * @param ApprovalQueue        $approvals  Approvals.
	 * @param PluginManagerService $plugins    Plugins.
	 * @param RestAuthMiddleware   $auth       Auth.
	 */
	public function __construct(
		AuditLogger $audit,
		ActionClassifier $classifier,
		ApprovalQueue $approvals,
		PluginManagerService $plugins,
		RestAuthMiddleware $auth
	) {
		parent::__construct( $audit, $classifier, $approvals );
		$this->plugins = $plugins;
		$this->auth    = $auth;
	}

	/**
	 * Register routes.
	 */
	public function register_routes(): void {
		$ns = DEJOIY_ACB_REST_NAMESPACE;

		register_rest_route( $ns, '/plugins', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'list' ),
			'permission_callback' => $this->auth->require( 'plugins.read' ),
		) );

		register_rest_route( $ns, '/plugins/install', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'install' ),
			'permission_callback' => $this->auth->require( 'plugins.install' ),
		) );

		register_rest_route( $ns, '/plugins/activate', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'activate' ),
			'permission_callback' => $this->auth->require( 'plugins.activate' ),
		) );

		register_rest_route( $ns, '/plugins/deactivate', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'deactivate' ),
			'permission_callback' => $this->auth->require( 'plugins.deactivate' ),
		) );

		register_rest_route( $ns, '/plugins/delete', array(
			'methods'             => WP_REST_Server::DELETABLE,
			'callback'            => array( $this, 'delete' ),
			'permission_callback' => $this->auth->require( 'plugins.delete' ),
		) );

		register_rest_route( $ns, '/plugins/update', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'update' ),
			'permission_callback' => $this->auth->require( 'plugins.update' ),
		) );

		register_rest_route( $ns, '/plugins/create', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'create' ),
			'permission_callback' => $this->auth->require( 'plugins.install' ),
		) );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function list( WP_REST_Request $request ) {
		return $this->respond( $this->plugins->list_plugins(), $request, 'plugins.read' );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function install( WP_REST_Request $request ) {
		$result = $this->plugins->install( $request->get_param( 'source' ) ?: '' );
		return $this->respond( $result, $request, 'plugins.install' );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function activate( WP_REST_Request $request ) {
		return $this->respond( $this->plugins->activate( $request->get_param( 'file' ) ?: '' ), $request, 'plugins.activate' );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function deactivate( WP_REST_Request $request ) {
		return $this->maybe_approve(
			$request,
			'plugins.deactivate',
			function () use ( $request ) {
				return $this->plugins->deactivate( $request->get_param( 'file' ) ?: '' );
			}
		);
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function delete( WP_REST_Request $request ) {
		return $this->maybe_approve(
			$request,
			'plugins.delete',
			function () use ( $request ) {
				return $this->plugins->delete( $request->get_param( 'file' ) ?: '' );
			}
		);
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function update( WP_REST_Request $request ) {
		return $this->respond( $this->plugins->update( $request->get_param( 'file' ) ?: '' ), $request, 'plugins.update' );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function create( WP_REST_Request $request ) {
		return $this->respond(
			$this->plugins->create_plugin( $request->get_param( 'slug' ) ?: '', $request->get_param( 'name' ) ?: '' ),
			$request,
			'plugins.install'
		);
	}
}
