<?php
/**
 * Audit logs view.
 *
 * @package Dejoiy\AiControlBridge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<table class="widefat striped">
	<thead>
		<tr>
			<th>ID</th>
			<th><?php esc_html_e( 'Timestamp', 'dejoiy-ai-control-bridge' ); ?></th>
			<th><?php esc_html_e( 'Agent', 'dejoiy-ai-control-bridge' ); ?></th>
			<th><?php esc_html_e( 'Action', 'dejoiy-ai-control-bridge' ); ?></th>
			<th><?php esc_html_e( 'IP', 'dejoiy-ai-control-bridge' ); ?></th>
			<th><?php esc_html_e( 'Status', 'dejoiy-ai-control-bridge' ); ?></th>
			<th><?php esc_html_e( 'Approval', 'dejoiy-ai-control-bridge' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $logs as $log ) : ?>
		<tr>
			<td><?php echo esc_html( (string) $log['id'] ); ?></td>
			<td><?php echo esc_html( $log['created_at'] ); ?></td>
			<td><?php echo esc_html( $log['agent_name'] ?? '—' ); ?></td>
			<td><?php echo esc_html( $log['action'] ); ?></td>
			<td><?php echo esc_html( $log['ip_address'] ?? '' ); ?></td>
			<td><?php echo esc_html( (string) ( $log['response_status'] ?? '' ) ); ?></td>
			<td><?php echo esc_html( $log['approval_status'] ); ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
