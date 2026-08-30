<?php
/**
 * Structure introspection REST API controller.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge\Api;

use Dejoiy\AiControlBridge\Auth\RestAuthMiddleware;
use Dejoiy\AiControlBridge\Security\ActionClassifier;
use Dejoiy\AiControlBridge\Security\ApprovalQueue;
use Dejoiy\AiControlBridge\Security\AuditLogger;
use Dejoiy\AiControlBridge\Services\StructureService;
use WP_REST_Request;
use WP_REST_Server;

/**
 * WordPress structure API.
 */
class StructureController extends BaseController {

	/**
	 * @var StructureService
	 */
	private $structure;

	/**
	 * @var RestAuthMiddleware
	 */
	private $auth;

	/**
	 * @param AuditLogger        $audit      Audit.
	 * @param ActionClassifier   $classifier Classifier.
	 * @param ApprovalQueue      $approvals  Approvals.
	 * @param StructureService   $structure  Structure.
	 * @param RestAuthMiddleware $auth       Auth.
	 */
	public function __construct(
		AuditLogger $audit,
		ActionClassifier $classifier,
		ApprovalQueue $approvals,
		StructureService $structure,
		RestAuthMiddleware $auth
	) {
		parent::__construct( $audit, $classifier, $approvals );
		$this->structure = $structure;
		$this->auth      = $auth;
	}

	/**
	 * Register routes.
	 */
	public function register_routes(): void {
		$ns = DEJOIY_ACB_REST_NAMESPACE;

		register_rest_route( $ns, '/structure', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'full' ),
			'permission_callback' => $this->auth->require( 'structure.read' ),
		) );

		register_rest_route( $ns, '/logs/read', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'read_log' ),
			'permission_callback' => $this->auth->require( 'structure.read' ),
		) );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function full( WP_REST_Request $request ) {
		return $this->respond( $this->structure->get_full_structure(), $request, 'structure.read' );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function read_log( WP_REST_Request $request ) {
		return $this->respond(
			$this->structure->read_log( $request->get_param( 'path' ) ?: '', (int) ( $request->get_param( 'lines' ) ?: 100 ) ),
			$request,
			'structure.read'
		);
	}
}
