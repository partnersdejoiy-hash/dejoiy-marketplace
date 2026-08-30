<?php
/**
 * REST API authentication middleware.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge\Auth;

use Dejoiy\AiControlBridge\Security\IpAllowlist;
use Dejoiy\AiControlBridge\Services\AgentService;
use WP_REST_Request;

/**
 * Central authentication for all DEJOIY AI endpoints.
 */
class RestAuthMiddleware {

	/**
	 * @var ApiKeyAuth
	 */
	private $api_key_auth;

	/**
	 * @var JwtAuth
	 */
	private $jwt_auth;

	/**
	 * @var PermissionChecker
	 */
	private $permissions;

	/**
	 * @var IpAllowlist
	 */
	private $ip_allowlist;

	/**
	 * @var AgentService
	 */
	private $agents;

	/**
	 * Current authenticated agent (request-scoped).
	 *
	 * @var array<string, mixed>|null
	 */
	private static $current_agent = null;

	/**
	 * @param ApiKeyAuth         $api_key_auth API key auth.
	 * @param JwtAuth            $jwt_auth     JWT auth.
	 * @param PermissionChecker  $permissions  Permission checker.
	 * @param IpAllowlist        $ip_allowlist IP allowlist.
	 * @param AgentService       $agents       Agent service.
	 */
	public function __construct(
		ApiKeyAuth $api_key_auth,
		JwtAuth $jwt_auth,
		PermissionChecker $permissions,
		IpAllowlist $ip_allowlist,
		AgentService $agents
	) {
		$this->api_key_auth  = $api_key_auth;
		$this->jwt_auth      = $jwt_auth;
		$this->permissions   = $permissions;
		$this->ip_allowlist  = $ip_allowlist;
		$this->agents        = $agents;
	}

	/**
	 * Permission callback for REST routes.
	 *
	 * @param WP_REST_Request $request Request.
	 * @param string          $permission Required permission.
	 * @return bool|\WP_Error
	 */
	public function check( WP_REST_Request $request, string $permission = '' ) {
		// Public discovery endpoints.
		$public_routes = array( '/mcp/manifest', '/mcp/tools', '/openapi' );
		$route         = $request->get_route();
		foreach ( $public_routes as $public ) {
			if ( false !== strpos( $route, $public ) ) {
				return true;
			}
		}

		$agent = $this->api_key_auth->authenticate();
		if ( ! $agent ) {
			$agent = $this->jwt_auth->authenticate();
		}

		if ( ! $agent ) {
			return new \WP_Error(
				'dejoiy_unauthorized',
				__( 'Valid API key or JWT required.', 'dejoiy-ai-control-bridge' ),
				array( 'status' => 401 )
			);
		}

		if ( ! $this->ip_allowlist->is_allowed( $agent ) ) {
			return new \WP_Error(
				'dejoiy_ip_denied',
				__( 'Request IP not in allowlist.', 'dejoiy-ai-control-bridge' ),
				array( 'status' => 403 )
			);
		}

		if ( $permission && ! $this->permissions->can( $agent, $permission ) ) {
			return new \WP_Error(
				'dejoiy_forbidden',
				__( 'Insufficient permissions.', 'dejoiy-ai-control-bridge' ),
				array( 'status' => 403 )
			);
		}

		self::$current_agent = $agent;
		$this->agents->touch_last_seen( (int) $agent['id'] );

		$request->set_param( '_dejoiy_agent', $agent );
		return true;
	}

	/**
	 * @return array<string, mixed>|null
	 */
	public static function current_agent(): ?array {
		return self::$current_agent;
	}

	/**
	 * Create permission callback closure.
	 *
	 * @param string $permission Permission slug.
	 * @return callable
	 */
	public function require( string $permission ): callable {
		return function ( WP_REST_Request $request ) use ( $permission ) {
			return $this->check( $request, $permission );
		};
	}
}
