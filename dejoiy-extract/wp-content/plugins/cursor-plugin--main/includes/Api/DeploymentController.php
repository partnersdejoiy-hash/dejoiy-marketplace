<?php
/**
 * Deployment REST API controller.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge\Api;

use Dejoiy\AiControlBridge\Auth\RestAuthMiddleware;
use Dejoiy\AiControlBridge\Security\ActionClassifier;
use Dejoiy\AiControlBridge\Security\ApprovalQueue;
use Dejoiy\AiControlBridge\Security\AuditLogger;
use Dejoiy\AiControlBridge\Services\DeploymentService;
use WP_REST_Request;
use WP_REST_Server;

/**
 * Deployment engine API.
 */
class DeploymentController extends BaseController {

	/**
	 * @var DeploymentService
	 */
	private $deployment;

	/**
	 * @var RestAuthMiddleware
	 */
	private $auth;

	/**
	 * @param AuditLogger        $audit      Audit.
	 * @param ActionClassifier   $classifier Classifier.
	 * @param ApprovalQueue      $approvals  Approvals.
	 * @param DeploymentService  $deployment Deployment.
	 * @param RestAuthMiddleware $auth       Auth.
	 */
	public function __construct(
		AuditLogger $audit,
		ActionClassifier $classifier,
		ApprovalQueue $approvals,
		DeploymentService $deployment,
		RestAuthMiddleware $auth
	) {
		parent::__construct( $audit, $classifier, $approvals );
		$this->deployment = $deployment;
		$this->auth       = $auth;
	}

	/**
	 * Register routes.
	 */
	public function register_routes(): void {
		$ns = DEJOIY_ACB_REST_NAMESPACE;

		register_rest_route( $ns, '/deployment/history', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'history' ),
			'permission_callback' => $this->auth->require( 'deployment.read' ),
		) );

		register_rest_route( $ns, '/deployment/git-status', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'git_status' ),
			'permission_callback' => $this->auth->require( 'deployment.read' ),
		) );

		register_rest_route( $ns, '/deployment/deploy', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'deploy' ),
			'permission_callback' => $this->auth->require( 'deployment.write' ),
		) );

		register_rest_route( $ns, '/deployment/rollback', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'rollback' ),
			'permission_callback' => $this->auth->require( 'deployment.write' ),
		) );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function history( WP_REST_Request $request ) {
		return $this->respond( $this->deployment->get_history(), $request, 'deployment.read' );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function git_status( WP_REST_Request $request ) {
		return $this->respond( $this->deployment->git_status(), $request, 'deployment.read' );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function deploy( WP_REST_Request $request ) {
		return $this->maybe_approve(
			$request,
			'deployment.deploy',
			function () use ( $request ) {
				$agent = $this->agent();
				return $this->deployment->deploy(
					$request->get_json_params() ?: array(),
					$agent ? (int) $agent['id'] : null
				);
			}
		);
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function rollback( WP_REST_Request $request ) {
		return $this->respond(
			$this->deployment->rollback( (int) $request->get_param( 'deployment_id' ) ),
			$request,
			'deployment.write'
		);
	}
}
