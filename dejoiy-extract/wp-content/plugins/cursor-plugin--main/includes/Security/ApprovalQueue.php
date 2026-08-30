<?php
/**
 * Action approval queue.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge\Security;

/**
 * Queues critical actions for admin approval before execution.
 */
class ApprovalQueue {

	/**
	 * @var AuditLogger
	 */
	private $audit;

	/**
	 * @param AuditLogger $audit Audit logger.
	 */
	public function __construct( AuditLogger $audit ) {
		$this->audit = $audit;
	}

	/**
	 * @return string
	 */
	private function table(): string {
		global $wpdb;
		return $wpdb->prefix . 'dejoiy_acb_approvals';
	}

	/**
	 * Queue action for approval.
	 *
	 * @param int                  $agent_id Agent ID.
	 * @param string               $action   Action slug.
	 * @param array<string, mixed> $payload  Action payload.
	 * @return int Approval ID.
	 */
	public function queue( int $agent_id, string $action, array $payload ): int {
		global $wpdb;

		$expires = gmdate( 'Y-m-d H:i:s', time() + DAY_IN_SECONDS );

		$wpdb->insert(
			$this->table(),
			array(
				'agent_id'   => $agent_id,
				'action'     => sanitize_text_field( $action ),
				'payload'    => wp_json_encode( $payload ),
				'status'     => 'pending',
				'expires_at' => $expires,
			),
			array( '%d', '%s', '%s', '%s', '%s' )
		);

		return (int) $wpdb->insert_id;
	}

	/**
	 * Get pending approvals.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function get_pending(): array {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results(
			"SELECT * FROM {$this->table()} WHERE status = 'pending' ORDER BY requested_at DESC",
			ARRAY_A
		);

		return $rows ?: array();
	}

	/**
	 * Approve and return payload for execution.
	 *
	 * @param int $approval_id Approval ID.
	 * @param int $user_id     Approving user ID.
	 * @return array<string, mixed>|\WP_Error
	 */
	public function approve( int $approval_id, int $user_id ) {
		return $this->resolve( $approval_id, $user_id, 'approved' );
	}

	/**
	 * Reject approval request.
	 *
	 * @param int    $approval_id Approval ID.
	 * @param int    $user_id     User ID.
	 * @param string $note        Rejection note.
	 * @return bool|\WP_Error
	 */
	public function reject( int $approval_id, int $user_id, string $note = '' ) {
		return $this->resolve( $approval_id, $user_id, 'rejected', $note );
	}

	/**
	 * @param int    $approval_id ID.
	 * @param int    $user_id     User.
	 * @param string $status      Status.
	 * @param string $note        Note.
	 * @return array<string, mixed>|\WP_Error
	 */
	private function resolve( int $approval_id, int $user_id, string $status, string $note = '' ) {
		global $wpdb;

		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$this->table()} WHERE id = %d", $approval_id ),
			ARRAY_A
		);

		if ( ! $row ) {
			return new \WP_Error( 'not_found', __( 'Approval not found.', 'dejoiy-ai-control-bridge' ), array( 'status' => 404 ) );
		}

		if ( 'pending' !== $row['status'] ) {
			return new \WP_Error( 'invalid_status', __( 'Approval already resolved.', 'dejoiy-ai-control-bridge' ), array( 'status' => 400 ) );
		}

		$wpdb->update(
			$this->table(),
			array(
				'status'          => $status,
				'resolved_at'     => current_time( 'mysql', true ),
				'resolved_by'     => $user_id,
				'resolution_note' => $note,
			),
			array( 'id' => $approval_id ),
			array( '%s', '%s', '%d', '%s' ),
			array( '%d' )
		);

		if ( 'approved' === $status ) {
			$payload = json_decode( $row['payload'], true );
			return is_array( $payload ) ? $payload : array();
		}

		return true;
	}

	/**
	 * Get approval by ID.
	 *
	 * @param int $id Approval ID.
	 * @return array<string, mixed>|null
	 */
	public function get( int $id ): ?array {
		global $wpdb;

		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$this->table()} WHERE id = %d", $id ),
			ARRAY_A
		);

		return $row ?: null;
	}

	/**
	 * Expire old pending approvals.
	 */
	public function expire_old(): void {
		global $wpdb;

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$this->table()} SET status = %s, resolved_at = %s WHERE status = 'pending' AND expires_at < %s",
				'expired',
				current_time( 'mysql', true ),
				current_time( 'mysql', true )
			)
		);
	}
}
