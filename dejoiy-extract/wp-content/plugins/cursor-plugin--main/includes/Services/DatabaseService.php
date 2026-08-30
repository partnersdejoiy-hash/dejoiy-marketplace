<?php
/**
 * Database access service.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge\Services;

use WP_Error;

/**
 * Safe database query execution with permission tiers.
 */
class DatabaseService {

	/**
	 * List all tables.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function list_tables(): array {
		global $wpdb;

		$tables = $wpdb->get_results( 'SHOW TABLES', ARRAY_N );
		$result = array();

		foreach ( $tables as $row ) {
			$name = $row[0];
			if ( ! preg_match( '/^[a-zA-Z0-9_]+$/', $name ) ) {
				continue;
			}
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$name}`" );
			$result[] = array(
				'name'   => $name,
				'rows'   => $count,
				'prefix' => ( 0 === strpos( $name, $wpdb->prefix ) ),
			);
		}

		return $result;
	}

	/**
	 * Get schema for tables.
	 *
	 * @param string|null $table Specific table or all.
	 * @return array<string, mixed>
	 */
	public function get_schema( ?string $table = null ): array {
		global $wpdb;

		$schema = array();

		if ( $table ) {
			$table = $this->sanitize_table_name( $table );
			if ( is_wp_error( $table ) ) {
				return array( 'error' => $table->get_error_message() );
			}
			$tables = array( $table );
		} else {
			$all = $wpdb->get_col( 'SHOW TABLES' );
			$tables = $all ?: array();
		}

		foreach ( $tables as $tname ) {
			$columns = $wpdb->get_results( "DESCRIBE `{$tname}`", ARRAY_A ); // phpcs:ignore
			$schema[ $tname ] = array(
				'columns' => $columns,
				'indexes' => $wpdb->get_results( "SHOW INDEX FROM `{$tname}`", ARRAY_A ), // phpcs:ignore
			);
		}

		return $schema;
	}

	/**
	 * Execute SQL query with permission check.
	 *
	 * @param string $sql           SQL query.
	 * @param bool   $allow_write   Allow write operations.
	 * @param bool   $allow_alter   Allow ALTER operations.
	 * @return array<string, mixed>|WP_Error
	 */
	public function query( string $sql, bool $allow_write = false, bool $allow_alter = false ) {
		global $wpdb;

		$sql = trim( $sql );
		$type = $this->detect_query_type( $sql );

		if ( 'SELECT' === $type || 'SHOW' === $type || 'DESCRIBE' === $type ) {
			// Allowed by default.
		} elseif ( in_array( $type, array( 'INSERT', 'UPDATE', 'DELETE' ), true ) ) {
			if ( ! $allow_write ) {
				return new WP_Error(
					'write_not_allowed',
					__( 'Write queries require elevated database.write permission.', 'dejoiy-ai-control-bridge' ),
					array( 'status' => 403 )
				);
			}
		} elseif ( in_array( $type, array( 'ALTER', 'CREATE', 'DROP', 'TRUNCATE' ), true ) ) {
			if ( ! $allow_alter ) {
				return new WP_Error(
					'alter_not_allowed',
					__( 'Schema modifications require database.alter permission.', 'dejoiy-ai-control-bridge' ),
					array( 'status' => 403 )
				);
			}
		} else {
			return new WP_Error(
				'query_not_allowed',
				__( 'Query type not permitted.', 'dejoiy-ai-control-bridge' ),
				array( 'status' => 403 )
			);
		}

		// Block dangerous patterns.
		$blocked = array( 'INTO OUTFILE', 'INTO DUMPFILE', 'LOAD_FILE', 'LOAD DATA', 'GRANT ', 'REVOKE ' );
		foreach ( $blocked as $pattern ) {
			if ( false !== stripos( $sql, $pattern ) ) {
				return new WP_Error( 'blocked_query', __( 'Query contains blocked patterns.', 'dejoiy-ai-control-bridge' ), array( 'status' => 403 ) );
			}
		}

		if ( 'SELECT' === $type || 'SHOW' === $type || 'DESCRIBE' === $type ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$results = $wpdb->get_results( $sql, ARRAY_A );
			return array(
				'type'    => $type,
				'rows'    => $results,
				'count'   => is_array( $results ) ? count( $results ) : 0,
			);
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$affected = $wpdb->query( $sql );

		return array(
			'type'     => $type,
			'affected' => $affected,
			'last_id'  => $wpdb->insert_id,
		);
	}

	/**
	 * @param string $sql SQL.
	 * @return string
	 */
	private function detect_query_type( string $sql ): string {
		if ( preg_match( '/^\s*(\w+)/i', $sql, $matches ) ) {
			return strtoupper( $matches[1] );
		}
		return 'UNKNOWN';
	}

	/**
	 * @param string $table Table name.
	 * @return string|WP_Error
	 */
	private function sanitize_table_name( string $table ) {
		if ( ! preg_match( '/^[a-zA-Z0-9_]+$/', $table ) ) {
			return new WP_Error( 'invalid_table', __( 'Invalid table name.', 'dejoiy-ai-control-bridge' ) );
		}
		return $table;
	}
}
