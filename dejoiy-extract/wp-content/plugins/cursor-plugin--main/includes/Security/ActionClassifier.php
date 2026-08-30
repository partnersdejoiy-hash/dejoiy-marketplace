<?php
/**
 * Classifies actions requiring approval.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge\Security;

/**
 * Determines which API actions require human approval.
 */
class ActionClassifier {

	/**
	 * Actions that always require approval when enabled.
	 *
	 * @var array<int, string>
	 */
	private const CRITICAL_ACTIONS = array(
		'files.delete',
		'plugins.delete',
		'plugins.deactivate',
		'database.write',
		'database.alter',
		'themes.deploy',
		'deployment.deploy',
		'backup.restore',
		'wordpress.delete',
	);

	/**
	 * @param string $action Action identifier.
	 * @return bool
	 */
	public function requires_approval( string $action ): bool {
		if ( ! get_option( 'dejoiy_acb_require_approval', true ) ) {
			return false;
		}

		return in_array( $action, self::CRITICAL_ACTIONS, true );
	}

	/**
	 * Map REST route + method to action slug.
	 *
	 * @param string $route  Route path.
	 * @param string $method HTTP method.
	 * @return string
	 */
	public function from_route( string $route, string $method ): string {
		$method = strtoupper( $method );

		if ( false !== strpos( $route, '/files/delete' ) ) {
			return 'files.delete';
		}
		if ( false !== strpos( $route, '/files/write' ) || false !== strpos( $route, '/files/create' ) ) {
			return 'files.write';
		}
		if ( false !== strpos( $route, '/plugins/delete' ) ) {
			return 'plugins.delete';
		}
		if ( false !== strpos( $route, '/plugins/deactivate' ) ) {
			return 'plugins.deactivate';
		}
		if ( false !== strpos( $route, '/database/query' ) && in_array( $method, array( 'POST', 'PUT' ), true ) ) {
			return 'database.write';
		}
		if ( false !== strpos( $route, '/backup/restore' ) ) {
			return 'backup.restore';
		}
		if ( false !== strpos( $route, '/deployment/deploy' ) ) {
			return 'deployment.deploy';
		}

		return 'general';
	}
}
