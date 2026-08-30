<?php
/**
 * Activity feed view.
 *
 * @package Dejoiy\AiControlBridge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php if ( ! empty( $pending ) ) : ?>
<h2><?php esc_html_e( 'Approval Queue', 'dejoiy-ai-control-bridge' ); ?></h2>
<table class="widefat striped">
	<thead>
		<tr>
			<th>ID</th>
			<th><?php esc_html_e( 'Action', 'dejoiy-ai-control-bridge' ); ?></th>
			<th><?php esc_html_e( 'Requested', 'dejoiy-ai-control-bridge' ); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $pending as $item ) : ?>
		<tr>
			<td><?php echo esc_html( (string) $item['id'] ); ?></td>
			<td><code><?php echo esc_html( $item['action'] ); ?></code></td>
			<td><?php echo esc_html( $item['requested_at'] ); ?></td>
			<td>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
					<?php wp_nonce_field( 'dejoiy_acb_approve' ); ?>
					<input type="hidden" name="action" value="dejoiy_acb_approve_action" />
					<input type="hidden" name="approval_id" value="<?php echo esc_attr( (string) $item['id'] ); ?>" />
					<button type="submit" class="button button-primary"><?php esc_html_e( 'Approve', 'dejoiy-ai-control-bridge' ); ?></button>
				</form>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
					<?php wp_nonce_field( 'dejoiy_acb_reject' ); ?>
					<input type="hidden" name="action" value="dejoiy_acb_reject_action" />
					<input type="hidden" name="approval_id" value="<?php echo esc_attr( (string) $item['id'] ); ?>" />
					<button type="submit" class="button"><?php esc_html_e( 'Reject', 'dejoiy-ai-control-bridge' ); ?></button>
				</form>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php endif; ?>

<h2><?php esc_html_e( 'Activity Feed', 'dejoiy-ai-control-bridge' ); ?></h2>
<table class="widefat striped">
	<thead>
		<tr>
			<th><?php esc_html_e( 'Timestamp', 'dejoiy-ai-control-bridge' ); ?></th>
			<th><?php esc_html_e( 'Agent', 'dejoiy-ai-control-bridge' ); ?></th>
			<th><?php esc_html_e( 'Action', 'dejoiy-ai-control-bridge' ); ?></th>
			<th><?php esc_html_e( 'Path', 'dejoiy-ai-control-bridge' ); ?></th>
			<th><?php esc_html_e( 'Result', 'dejoiy-ai-control-bridge' ); ?></th>
			<th><?php esc_html_e( 'Approval', 'dejoiy-ai-control-bridge' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $activity as $log ) : ?>
		<tr>
			<td><?php echo esc_html( $log['created_at'] ); ?></td>
			<td><?php echo esc_html( $log['agent_name'] ?? '—' ); ?></td>
			<td><?php echo esc_html( $log['action'] ); ?></td>
			<td><code><?php echo esc_html( $log['request_path'] ?? '' ); ?></code></td>
			<td><?php echo esc_html( (string) ( $log['response_status'] ?? '' ) ); ?></td>
			<td><?php echo esc_html( $log['approval_status'] ); ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
