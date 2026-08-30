<?php
/**
 * Files REST API controller.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge\Api;

use Dejoiy\AiControlBridge\Auth\RestAuthMiddleware;
use Dejoiy\AiControlBridge\Security\ActionClassifier;
use Dejoiy\AiControlBridge\Security\ApprovalQueue;
use Dejoiy\AiControlBridge\Security\AuditLogger;
use Dejoiy\AiControlBridge\Services\FileSystemService;
use WP_REST_Request;
use WP_REST_Server;

/**
 * File system API endpoints.
 */
class FilesController extends BaseController {

	/**
	 * @var FileSystemService
	 */
	private $files;

	/**
	 * @var RestAuthMiddleware
	 */
	private $auth;

	/**
	 * @param AuditLogger       $audit      Audit.
	 * @param ActionClassifier  $classifier Classifier.
	 * @param ApprovalQueue     $approvals  Approvals.
	 * @param FileSystemService $files      Files service.
	 * @param RestAuthMiddleware $auth      Auth.
	 */
	public function __construct(
		AuditLogger $audit,
		ActionClassifier $classifier,
		ApprovalQueue $approvals,
		FileSystemService $files,
		RestAuthMiddleware $auth
	) {
		parent::__construct( $audit, $classifier, $approvals );
		$this->files = $files;
		$this->auth  = $auth;
	}

	/**
	 * Register routes.
	 */
	public function register_routes(): void {
		$ns = DEJOIY_ACB_REST_NAMESPACE;

		register_rest_route( $ns, '/files/tree', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'tree' ),
			'permission_callback' => $this->auth->require( 'files.read' ),
		) );

		register_rest_route( $ns, '/files/read', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'read' ),
			'permission_callback' => $this->auth->require( 'files.read' ),
		) );

		register_rest_route( $ns, '/files/write', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'write' ),
			'permission_callback' => $this->auth->require( 'files.write' ),
		) );

		register_rest_route( $ns, '/files/create', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'create' ),
			'permission_callback' => $this->auth->require( 'files.create' ),
		) );

		register_rest_route( $ns, '/files/delete', array(
			'methods'             => WP_REST_Server::DELETABLE,
			'callback'            => array( $this, 'delete' ),
			'permission_callback' => $this->auth->require( 'files.delete' ),
		) );

		register_rest_route( $ns, '/files/rename', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'rename' ),
			'permission_callback' => $this->auth->require( 'files.rename' ),
		) );

		register_rest_route( $ns, '/files/search', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'search' ),
			'permission_callback' => $this->auth->require( 'files.read' ),
		) );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function tree( WP_REST_Request $request ) {
		$result = $this->files->tree(
			$request->get_param( 'zone' ) ?: 'wp-content',
			$request->get_param( 'path' ) ?: '',
			(int) ( $request->get_param( 'depth' ) ?: 3 )
		);
		return $this->respond( $result, $request, 'files.read' );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function read( WP_REST_Request $request ) {
		$result = $this->files->read(
			$request->get_param( 'zone' ) ?: 'wp-content',
			$request->get_param( 'path' ) ?: ''
		);
		return $this->respond( $result, $request, 'files.read' );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function write( WP_REST_Request $request ) {
		return $this->maybe_approve(
			$request,
			'files.write',
			function () use ( $request ) {
				return $this->files->write(
					$request->get_param( 'zone' ) ?: 'wp-content',
					$request->get_param( 'path' ) ?: '',
					$request->get_param( 'content' ) ?: ''
				);
			}
		);
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function create( WP_REST_Request $request ) {
		$result = $this->files->create(
			$request->get_param( 'zone' ) ?: 'wp-content',
			$request->get_param( 'path' ) ?: '',
			$request->get_param( 'type' ) ?: 'file',
			$request->get_param( 'content' ) ?: ''
		);
		return $this->respond( $result, $request, 'files.create' );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function delete( WP_REST_Request $request ) {
		return $this->maybe_approve(
			$request,
			'files.delete',
			function () use ( $request ) {
				return $this->files->delete(
					$request->get_param( 'zone' ) ?: 'wp-content',
					$request->get_param( 'path' ) ?: ''
				);
			}
		);
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function rename( WP_REST_Request $request ) {
		$result = $this->files->rename(
			$request->get_param( 'zone' ) ?: 'wp-content',
			$request->get_param( 'from' ) ?: '',
			$request->get_param( 'to' ) ?: ''
		);
		return $this->respond( $result, $request, 'files.rename' );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function search( WP_REST_Request $request ) {
		$result = $this->files->search(
			$request->get_param( 'zone' ) ?: 'plugins',
			$request->get_param( 'pattern' ) ?: '*',
			(int) ( $request->get_param( 'limit' ) ?: 100 )
		);
		return $this->respond( $result, $request, 'files.read' );
	}
}
