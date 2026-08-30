<?php
/**
 * Database REST API controller.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge\Api;

use Dejoiy\AiControlBridge\Auth\PermissionChecker;
use Dejoiy\AiControlBridge\Auth\RestAuthMiddleware;
use Dejoiy\AiControlBridge\Security\ActionClassifier;
use Dejoiy\AiControlBridge\Security\ApprovalQueue;
use Dejoiy\AiControlBridge\Security\AuditLogger;
use Dejoiy\AiControlBridge\Services\DatabaseService;
use WP_REST_Request;
use WP_REST_Server;

/**
 * Database API endpoints.
 */
class DatabaseController extends BaseController {

	/**
	 * @var DatabaseService
	 */
	private $database;

	/**
	 * @var RestAuthMiddleware
	 */
	private $auth;

	/**
	 * @var PermissionChecker
	 */
	private $permissions;

	/**
	 * @param AuditLogger        $audit       Audit.
	 * @param ActionClassifier   $classifier  Classifier.
	 * @param ApprovalQueue      $approvals   Approvals.
	 * @param DatabaseService    $database    Database.
	 * @param RestAuthMiddleware $auth        Auth.
	 * @param PermissionChecker  $permissions Permissions.
	 */
	public function __construct(
		AuditLogger $audit,
		ActionClassifier $classifier,
		ApprovalQueue $approvals,
		DatabaseService $database,
		RestAuthMiddleware $auth,
		PermissionChecker $permissions
	) {
		parent::__construct( $audit, $classifier, $approvals );
		$this->database    = $database;
		$this->auth        = $auth;
		$this->permissions = $permissions;
	}

	/**
	 * Register routes.
	 */
	public function register_routes(): void {
		$ns = DEJOIY_ACB_REST_NAMESPACE;

		register_rest_route( $ns, '/database/tables', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'tables' ),
			'permission_callback' => $this->auth->require( 'database.read' ),
		) );

		register_rest_route( $ns, '/database/schema', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'schema' ),
			'permission_callback' => $this->auth->require( 'database.read' ),
		) );

		register_rest_route( $ns, '/database/query', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'query_get' ),
				'permission_callback' => $this->auth->require( 'database.read' ),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'query_post' ),
				'permission_callback' => $this->auth->require( 'database.read' ),
			),
		) );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function tables( WP_REST_Request $request ) {
		return $this->respond( $this->database->list_tables(), $request, 'database.read' );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function schema( WP_REST_Request $request ) {
		return $this->respond(
			$this->database->get_schema( $request->get_param( 'table' ) ),
			$request,
			'database.read'
		);
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function query_get( WP_REST_Request $request ) {
		return $this->execute_query( $request );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function query_post( WP_REST_Request $request ) {
		return $this->execute_query( $request );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	private function execute_query( WP_REST_Request $request ) {
		$sql = $request->get_param( 'sql' ) ?: '';
		$agent = $this->agent();

		$allow_write = $agent && $this->permissions->can( $agent, 'database.write' );
		$allow_alter = $agent && $this->permissions->can( $agent, 'database.alter' );

		$type = strtoupper( trim( preg_replace( '/^\s*(\w+).*/s', '$1', $sql ) ) );

		if ( in_array( $type, array( 'INSERT', 'UPDATE', 'DELETE' ), true ) ) {
			return $this->maybe_approve(
				$request,
				'database.write',
				function () use ( $sql, $allow_write, $allow_alter ) {
					return $this->database->query( $sql, $allow_write, $allow_alter );
				}
			);
		}

		if ( in_array( $type, array( 'ALTER', 'CREATE', 'DROP', 'TRUNCATE' ), true ) ) {
			return $this->maybe_approve(
				$request,
				'database.alter',
				function () use ( $sql, $allow_write, $allow_alter ) {
					return $this->database->query( $sql, $allow_write, $allow_alter );
				}
			);
		}

		$result = $this->database->query( $sql, false, false );
		return $this->respond( $result, $request, 'database.read' );
	}
}
