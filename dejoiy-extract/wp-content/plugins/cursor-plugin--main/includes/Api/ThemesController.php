<?php
/**
 * Themes REST API controller.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge\Api;

use Dejoiy\AiControlBridge\Auth\RestAuthMiddleware;
use Dejoiy\AiControlBridge\Security\ActionClassifier;
use Dejoiy\AiControlBridge\Security\ApprovalQueue;
use Dejoiy\AiControlBridge\Security\AuditLogger;
use Dejoiy\AiControlBridge\Services\ThemeManagerService;
use WP_REST_Request;
use WP_REST_Server;

/**
 * Theme management API.
 */
class ThemesController extends BaseController {

	/**
	 * @var ThemeManagerService
	 */
	private $themes;

	/**
	 * @var RestAuthMiddleware
	 */
	private $auth;

	/**
	 * @param AuditLogger         $audit      Audit.
	 * @param ActionClassifier    $classifier Classifier.
	 * @param ApprovalQueue       $approvals  Approvals.
	 * @param ThemeManagerService $themes     Themes.
	 * @param RestAuthMiddleware  $auth       Auth.
	 */
	public function __construct(
		AuditLogger $audit,
		ActionClassifier $classifier,
		ApprovalQueue $approvals,
		ThemeManagerService $themes,
		RestAuthMiddleware $auth
	) {
		parent::__construct( $audit, $classifier, $approvals );
		$this->themes = $themes;
		$this->auth   = $auth;
	}

	/**
	 * Register routes.
	 */
	public function register_routes(): void {
		$ns = DEJOIY_ACB_REST_NAMESPACE;

		register_rest_route( $ns, '/themes', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'list' ),
			'permission_callback' => $this->auth->require( 'themes.read' ),
		) );

		register_rest_route( $ns, '/themes/templates', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'templates' ),
			'permission_callback' => $this->auth->require( 'themes.read' ),
		) );

		register_rest_route( $ns, '/themes/template/read', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'read_template' ),
			'permission_callback' => $this->auth->require( 'themes.read' ),
		) );

		register_rest_route( $ns, '/themes/template/write', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'write_template' ),
			'permission_callback' => $this->auth->require( 'themes.write' ),
		) );

		register_rest_route( $ns, '/themes/child', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'create_child' ),
			'permission_callback' => $this->auth->require( 'themes.create' ),
		) );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function list( WP_REST_Request $request ) {
		return $this->respond( $this->themes->list_themes(), $request, 'themes.read' );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function templates( WP_REST_Request $request ) {
		return $this->respond( $this->themes->get_templates( $request->get_param( 'theme' ) ), $request, 'themes.read' );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function read_template( WP_REST_Request $request ) {
		return $this->respond(
			$this->themes->read_template( $request->get_param( 'theme' ) ?: get_stylesheet(), $request->get_param( 'template' ) ?: '' ),
			$request,
			'themes.read'
		);
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function write_template( WP_REST_Request $request ) {
		return $this->maybe_approve(
			$request,
			'themes.deploy',
			function () use ( $request ) {
				return $this->themes->write_template(
					$request->get_param( 'theme' ) ?: get_stylesheet(),
					$request->get_param( 'template' ) ?: '',
					$request->get_param( 'content' ) ?: ''
				);
			}
		);
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function create_child( WP_REST_Request $request ) {
		return $this->respond(
			$this->themes->create_child_theme(
				$request->get_param( 'slug' ) ?: '',
				$request->get_param( 'parent' ) ?: get_template(),
				$request->get_param( 'name' ) ?: ''
			),
			$request,
			'themes.create'
		);
	}
}
