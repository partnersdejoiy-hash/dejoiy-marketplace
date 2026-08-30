<?php
/**
 * AI agent management service.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge\Services;

/**
 * CRUD operations for connected AI agents.
 */
class AgentService {

	/**
	 * @return string
	 */
	private function table(): string {
		global $wpdb;
		return $wpdb->prefix . 'dejoiy_acb_agents';
	}

	/**
	 * Create a new agent with API key.
	 *
	 * @param string              $name        Agent name.
	 * @param array<int, string>  $permissions Permission list.
	 * @param array<int, string>|null $ip_allowlist IP allowlist.
	 * @return array<string, mixed> Agent with plaintext api_key (shown once).
	 */
	public function create( string $name, array $permissions, ?array $ip_allowlist = null ): array {
		global $wpdb;

		$api_key    = 'dacb_' . wp_generate_password( 48, false );
		$key_hash   = wp_hash_password( $api_key );
		$key_prefix = substr( $api_key, 0, 12 );

		$wpdb->insert(
			$this->table(),
			array(
				'name'           => sanitize_text_field( $name ),
				'api_key_hash'   => $key_hash,
				'api_key_prefix' => $key_prefix,
				'permissions'    => wp_json_encode( $permissions ),
				'ip_allowlist'   => $ip_allowlist ? wp_json_encode( $ip_allowlist ) : null,
				'status'         => 'active',
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		$id = (int) $wpdb->insert_id;
		$agent = $this->get_by_id( $id );
		if ( $agent ) {
			$agent['api_key'] = $api_key;
		}
		return $agent ?: array();
	}

	/**
	 * Validate API key and return agent.
	 *
	 * @param string $api_key Plain API key.
	 * @return array<string, mixed>|null
	 */
	public function validate_api_key( string $api_key ): ?array {
		global $wpdb;

		$prefix = substr( $api_key, 0, 12 );
		$row    = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$this->table()} WHERE api_key_prefix = %s AND status = 'active' LIMIT 1",
				$prefix
			),
			ARRAY_A
		);

		if ( ! $row || ! wp_check_password( $api_key, $row['api_key_hash'] ) ) {
			return null;
		}

		$row['permissions'] = json_decode( $row['permissions'], true ) ?: array();
		return $row;
	}

	/**
	 * @param int $id Agent ID.
	 * @return array<string, mixed>|null
	 */
	public function get_by_id( int $id ): ?array {
		global $wpdb;

		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$this->table()} WHERE id = %d", $id ),
			ARRAY_A
		);

		if ( $row ) {
			$row['permissions'] = json_decode( $row['permissions'], true ) ?: array();
			unset( $row['api_key_hash'] );
		}

		return $row ?: null;
	}

	/**
	 * List all agents.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function list_all(): array {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results( "SELECT id, name, api_key_prefix, permissions, status, last_seen, created_at FROM {$this->table()} ORDER BY created_at DESC", ARRAY_A );

		foreach ( $rows as &$row ) {
			$row['permissions'] = json_decode( $row['permissions'], true ) ?: array();
		}

		return $rows ?: array();
	}

	/**
	 * Update last seen timestamp.
	 *
	 * @param int $id Agent ID.
	 */
	public function touch_last_seen( int $id ): void {
		global $wpdb;
		$wpdb->update(
			$this->table(),
			array( 'last_seen' => current_time( 'mysql', true ) ),
			array( 'id' => $id ),
			array( '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Revoke agent.
	 *
	 * @param int $id Agent ID.
	 * @return bool
	 */
	public function revoke( int $id ): bool {
		global $wpdb;
		return (bool) $wpdb->update(
			$this->table(),
			array( 'status' => 'revoked' ),
			array( 'id' => $id ),
			array( '%s' ),
			array( '%d' )
		);
	}
}
