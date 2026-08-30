<?php
/**
 * Dashboard view.
 *
 * @package Dejoiy\AiControlBridge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="dejoiy-acb-grid">
	<div class="dejoiy-acb-card">
		<h2><?php esc_html_e( 'Connected Agents', 'dejoiy-ai-control-bridge' ); ?></h2>
		<p class="dejoiy-acb-stat"><?php echo esc_html( (string) count( $agents ) ); ?></p>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=dejoiy-acb-agents' ) ); ?>" class="button"><?php esc_html_e( 'Manage Agents', 'dejoiy-ai-control-bridge' ); ?></a>
	</div>
	<div class="dejoiy-acb-card">
		<h2><?php esc_html_e( 'Pending Approvals', 'dejoiy-ai-control-bridge' ); ?></h2>
		<p class="dejoiy-acb-stat"><?php echo esc_html( (string) count( $pending ) ); ?></p>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=dejoiy-acb-activity' ) ); ?>" class="button"><?php esc_html_e( 'Review', 'dejoiy-ai-control-bridge' ); ?></a>
	</div>
	<div class="dejoiy-acb-card">
		<h2><?php esc_html_e( 'API Base URL', 'dejoiy-ai-control-bridge' ); ?></h2>
		<code><?php echo esc_html( rest_url( DEJOIY_ACB_REST_NAMESPACE ) ); ?></code>
	</div>
	<div class="dejoiy-acb-card">
		<h2><?php esc_html_e( 'MCP Manifest', 'dejoiy-ai-control-bridge' ); ?></h2>
		<code><?php echo esc_html( rest_url( DEJOIY_ACB_REST_NAMESPACE . '/mcp/manifest' ) ); ?></code>
	</div>
</div>

<h2><?php esc_html_e( 'Recent Activity', 'dejoiy-ai-control-bridge' ); ?></h2>
<table class="widefat striped">
	<thead>
		<tr>
			<th><?php esc_html_e( 'Time', 'dejoiy-ai-control-bridge' ); ?></th>
			<th><?php esc_html_e( 'Agent', 'dejoiy-ai-control-bridge' ); ?></th>
			<th><?php esc_html_e( 'Action', 'dejoiy-ai-control-bridge' ); ?></th>
			<th><?php esc_html_e( 'Status', 'dejoiy-ai-control-bridge' ); ?></th>
			<th><?php esc_html_e( 'Approval', 'dejoiy-ai-control-bridge' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $activity as $log ) : ?>
		<tr>
			<td><?php echo esc_html( $log['created_at'] ); ?></td>
			<td><?php echo esc_html( $log['agent_name'] ?? '—' ); ?></td>
			<td><?php echo esc_html( $log['action'] ); ?></td>
			<td><?php echo esc_html( (string) ( $log['response_status'] ?? '' ) ); ?></td>
			<td><?php echo esc_html( $log['approval_status'] ); ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
