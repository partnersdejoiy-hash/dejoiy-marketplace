<?php
/**
 * Backup center view.
 *
 * @package Dejoiy\AiControlBridge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<p><?php esc_html_e( 'Create backups via API: POST /backup/create with type: full, database, theme, or plugin.', 'dejoiy-ai-control-bridge' ); ?></p>
<table class="widefat striped">
	<thead>
		<tr>
			<th>ID</th>
			<th><?php esc_html_e( 'Type', 'dejoiy-ai-control-bridge' ); ?></th>
			<th><?php esc_html_e( 'Size', 'dejoiy-ai-control-bridge' ); ?></th>
			<th><?php esc_html_e( 'Status', 'dejoiy-ai-control-bridge' ); ?></th>
			<th><?php esc_html_e( 'Created', 'dejoiy-ai-control-bridge' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $backups as $b ) : ?>
		<tr>
			<td><?php echo esc_html( (string) $b['id'] ); ?></td>
			<td><?php echo esc_html( $b['type'] ); ?></td>
			<td><?php echo esc_html( size_format( (int) $b['file_size'] ) ); ?></td>
			<td><?php echo esc_html( $b['status'] ); ?></td>
			<td><?php echo esc_html( $b['created_at'] ); ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
