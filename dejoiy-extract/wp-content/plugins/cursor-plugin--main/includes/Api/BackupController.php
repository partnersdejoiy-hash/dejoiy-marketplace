<?php
/**
 * Backup REST API controller.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge\Api;

use Dejoiy\AiControlBridge\Auth\RestAuthMiddleware;
use Dejoiy\AiControlBridge\Security\ActionClassifier;
use Dejoiy\AiControlBridge\Security\ApprovalQueue;
use Dejoiy\AiControlBridge\Security\AuditLogger;
use Dejoiy\AiControlBridge\Services\BackupService;
use WP_REST_Request;
use WP_REST_Server;

/**
 * Backup and restore API.
 */
class BackupController extends BaseController {

	/**
	 * @var BackupService
	 */
	private $backup;

	/**
	 * @var RestAuthMiddleware
	 */
	private $auth;

	/**
	 * @param AuditLogger        $audit      Audit.
	 * @param ActionClassifier   $classifier Classifier.
	 * @param ApprovalQueue      $approvals  Approvals.
	 * @param BackupService      $backup     Backup.
	 * @param RestAuthMiddleware $auth       Auth.
	 */
	public function __construct(
		AuditLogger $audit,
		ActionClassifier $classifier,
		ApprovalQueue $approvals,
		BackupService $backup,
		RestAuthMiddleware $auth
	) {
		parent::__construct( $audit, $classifier, $approvals );
		$this->backup = $backup;
		$this->auth   = $auth;
	}

	/**
	 * Register routes.
	 */
	public function register_routes(): void {
		$ns = DEJOIY_ACB_REST_NAMESPACE;

		register_rest_route( $ns, '/backup', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'list' ),
			'permission_callback' => $this->auth->require( 'backup.read' ),
		) );

		register_rest_route( $ns, '/backup/create', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'create' ),
			'permission_callback' => $this->auth->require( 'backup.create' ),
		) );

		register_rest_route( $ns, '/backup/restore', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'restore' ),
			'permission_callback' => $this->auth->require( 'backup.restore' ),
		) );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function list( WP_REST_Request $request ) {
		return $this->respond( $this->backup->list_backups(), $request, 'backup.read' );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function create( WP_REST_Request $request ) {
		$agent = $this->agent();
		$result = $this->backup->create(
			$request->get_param( 'type' ) ?: 'database',
			$request->get_json_params() ?: array(),
			$agent ? (int) $agent['id'] : null
		);
		return $this->respond( $result, $request, 'backup.create' );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function restore( WP_REST_Request $request ) {
		return $this->maybe_approve(
			$request,
			'backup.restore',
			function () use ( $request ) {
				return $this->backup->restore( (int) $request->get_param( 'backup_id' ) );
			}
		);
	}
}
