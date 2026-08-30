<?php
/**
 * Database migration runner.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge\Services;

use WP_Error;

/**
 * Runs plugin-internal and custom SQL migrations.
 */
class MigrationService {

	/**
	 * Run pending migrations from plugin migrations directory.
	 *
	 * @return array<string, mixed>
	 */
	public function run(): array {
		$dir = DEJOIY_ACB_PLUGIN_DIR . 'migrations';
		if ( ! is_dir( $dir ) ) {
			return array( 'ran' => 0, 'message' => 'No migrations directory.' );
		}

		$ran     = get_option( 'dejoiy_acb_migrations_ran', array() );
		$files   = glob( $dir . '/*.sql' ) ?: array();
		sort( $files );
		$executed = array();

		global $wpdb;

		foreach ( $files as $file ) {
			$name = basename( $file );
			if ( in_array( $name, $ran, true ) ) {
				continue;
			}

			$sql = file_get_contents( $file );
			if ( empty( $sql ) ) {
				continue;
			}

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->query( $sql );
			$ran[]       = $name;
			$executed[]  = $name;
		}

		update_option( 'dejoiy_acb_migrations_ran', $ran );

		return array(
			'ran'       => count( $executed ),
			'executed'  => $executed,
		);
	}
}
