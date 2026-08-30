<?php
/**
 * Audit logging service.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge\Security;

/**
 * Persists audit trail for all AI agent actions.
 */
class AuditLogger {

	/**
	 * @return string Table name.
	 */
	private function table(): string {
		global $wpdb;
		return $wpdb->prefix . 'dejoiy_acb_audit_logs';
	}

	/**
	 * Log an action.
	 *
	 * @param array<string, mixed> $data Log data.
	 * @return int Log ID.
	 */
	public function log( array $data ): int {
		global $wpdb;

		$defaults = array(
			'agent_id'         => null,
			'agent_name'       => null,
			'action'           => 'unknown',
			'resource_type'    => null,
			'resource_id'      => null,
			'request_method'   => null,
			'request_path'     => null,
			'request_payload'  => null,
			'response_status'  => null,
			'result'           => null,
			'ip_address'       => $this->client_ip(),
			'approval_status'  => 'not_required',
			'approval_id'      => null,
		);

		$data = wp_parse_args( $data, $defaults );

		if ( is_array( $data['request_payload'] ) ) {
			$data['request_payload'] = wp_json_encode( $data['request_payload'] );
		}
		if ( is_array( $data['result'] ) ) {
			$data['result'] = wp_json_encode( $data['result'] );
		}

		$wpdb->insert(
			$this->table(),
			array(
				'agent_id'        => $data['agent_id'],
				'agent_name'      => sanitize_text_field( (string) $data['agent_name'] ),
				'action'          => sanitize_text_field( (string) $data['action'] ),
				'resource_type'   => $data['resource_type'] ? sanitize_text_field( (string) $data['resource_type'] ) : null,
				'resource_id'     => $data['resource_id'] ? sanitize_text_field( (string) $data['resource_id'] ) : null,
				'request_method'  => $data['request_method'],
				'request_path'    => $data['request_path'],
				'request_payload' => $data['request_payload'],
				'response_status' => $data['response_status'],
				'result'          => $data['result'],
				'ip_address'      => $data['ip_address'],
				'approval_status' => sanitize_text_field( (string) $data['approval_status'] ),
				'approval_id'     => $data['approval_id'],
			),
			array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%d' )
		);

		return (int) $wpdb->insert_id;
	}

	/**
	 * Get recent logs.
	 *
	 * @param int $limit  Limit.
	 * @param int $offset Offset.
	 * @return array<int, array<string, mixed>>
	 */
	public function get_recent( int $limit = 50, int $offset = 0 ): array {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$this->table()} ORDER BY created_at DESC LIMIT %d OFFSET %d",
				$limit,
				$offset
			),
			ARRAY_A
		);

		return $rows ?: array();
	}

	/**
	 * @return string
	 */
	private function client_ip(): string {
		$ip = isset( $_SERVER['REMOTE_ADDR'] )
			? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) )
			: '0.0.0.0';
		return $ip;
	}
}
