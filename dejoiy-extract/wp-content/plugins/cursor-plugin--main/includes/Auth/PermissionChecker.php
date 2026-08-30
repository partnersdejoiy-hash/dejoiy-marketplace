<?php
/**
 * Role-based permission checker.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge\Auth;

/**
 * Validates agent permissions against required capabilities.
 */
class PermissionChecker {

	/**
	 * Default permission sets.
	 */
	public const PERMISSION_SETS = array(
		'read_only'  => array( 'files.read', 'database.read', 'structure.read', 'wordpress.read' ),
		'developer'  => array(
			'files.read', 'files.write', 'files.create', 'files.delete', 'files.rename',
			'database.read', 'structure.read', 'wordpress.read', 'wordpress.write',
			'plugins.read', 'themes.read', 'themes.write', 'cache.clear',
		),
		'admin'      => array( '*' ),
		'full'       => array( '*' ),
	);

	/**
	 * All available permissions.
	 */
	public const ALL_PERMISSIONS = array(
		'files.read', 'files.write', 'files.create', 'files.delete', 'files.rename',
		'database.read', 'database.write', 'database.alter',
		'structure.read',
		'wordpress.read', 'wordpress.write',
		'plugins.read', 'plugins.install', 'plugins.activate', 'plugins.deactivate', 'plugins.delete', 'plugins.update',
		'themes.read', 'themes.write', 'themes.create',
		'deployment.read', 'deployment.write',
		'backup.read', 'backup.create', 'backup.restore',
		'cache.clear',
		'migrations.run',
		'agents.manage',
	);

	/**
	 * Check if agent has permission.
	 *
	 * @param array<string, mixed> $agent      Agent record.
	 * @param string               $permission Required permission.
	 * @return bool
	 */
	public function can( array $agent, string $permission ): bool {
		$permissions = $this->normalize_permissions( $agent['permissions'] ?? array() );

		if ( in_array( '*', $permissions, true ) ) {
			return true;
		}

		if ( in_array( $permission, $permissions, true ) ) {
			return true;
		}

		// Wildcard matching: files.* matches files.read.
		$parts = explode( '.', $permission );
		if ( count( $parts ) >= 2 ) {
			$wildcard = $parts[0] . '.*';
			if ( in_array( $wildcard, $permissions, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param array<int, string>|string $permissions Permissions.
	 * @return array<int, string>
	 */
	public function normalize_permissions( $permissions ): array {
		if ( is_string( $permissions ) ) {
			$decoded = json_decode( $permissions, true );
			if ( is_array( $decoded ) ) {
				$permissions = $decoded;
			} else {
				$permissions = array( $permissions );
			}
		}

		if ( ! is_array( $permissions ) ) {
			return array();
		}

		$expanded = array();
		foreach ( $permissions as $perm ) {
			if ( isset( self::PERMISSION_SETS[ $perm ] ) ) {
				$expanded = array_merge( $expanded, self::PERMISSION_SETS[ $perm ] );
			} else {
				$expanded[] = $perm;
			}
		}

		return array_unique( $expanded );
	}
}
