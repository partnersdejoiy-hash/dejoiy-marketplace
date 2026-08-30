<?php
/**
 * Deployment center view.
 *
 * @package Dejoiy\AiControlBridge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<h2><?php esc_html_e( 'Git Status', 'dejoiy-ai-control-bridge' ); ?></h2>
<pre><?php echo esc_html( wp_json_encode( $git, JSON_PRETTY_PRINT ) ); ?></pre>

<h2><?php esc_html_e( 'Deployment History', 'dejoiy-ai-control-bridge' ); ?></h2>
<table class="widefat striped">
	<thead>
		<tr>
			<th>ID</th>
			<th><?php esc_html_e( 'Version', 'dejoiy-ai-control-bridge' ); ?></th>
			<th><?php esc_html_e( 'Branch', 'dejoiy-ai-control-bridge' ); ?></th>
			<th><?php esc_html_e( 'Commit', 'dejoiy-ai-control-bridge' ); ?></th>
			<th><?php esc_html_e( 'Status', 'dejoiy-ai-control-bridge' ); ?></th>
			<th><?php esc_html_e( 'Created', 'dejoiy-ai-control-bridge' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $history as $row ) : ?>
		<tr>
			<td><?php echo esc_html( (string) $row['id'] ); ?></td>
			<td><?php echo esc_html( $row['version'] ); ?></td>
			<td><?php echo esc_html( $row['branch'] ?? '—' ); ?></td>
			<td><code><?php echo esc_html( substr( $row['commit_hash'] ?? '', 0, 8 ) ); ?></code></td>
			<td><?php echo esc_html( $row['status'] ); ?></td>
			<td><?php echo esc_html( $row['created_at'] ); ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
