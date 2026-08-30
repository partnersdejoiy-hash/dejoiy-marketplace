<?php
/**
 * Agents view.
 *
 * @package Dejoiy\AiControlBridge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$new_key = get_transient( 'dejoiy_acb_new_api_key_' . get_current_user_id() );
if ( $new_key ) {
	delete_transient( 'dejoiy_acb_new_api_key_' . get_current_user_id() );
}
?>
<?php if ( $new_key ) : ?>
<div class="notice notice-success">
	<p><strong><?php esc_html_e( 'API Key (save now — shown once):', 'dejoiy-ai-control-bridge' ); ?></strong></p>
	<code style="word-break:break-all;"><?php echo esc_html( $new_key ); ?></code>
</div>
<?php endif; ?>

<h2><?php esc_html_e( 'Create Agent', 'dejoiy-ai-control-bridge' ); ?></h2>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<?php wp_nonce_field( 'dejoiy_acb_create_agent' ); ?>
	<input type="hidden" name="action" value="dejoiy_acb_create_agent" />
	<table class="form-table">
		<tr>
			<th><label for="agent_name"><?php esc_html_e( 'Agent Name', 'dejoiy-ai-control-bridge' ); ?></label></th>
			<td><input type="text" name="agent_name" id="agent_name" class="regular-text" required placeholder="Cursor Agent" /></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Permission Set', 'dejoiy-ai-control-bridge' ); ?></th>
			<td>
				<select name="permission_set" id="permission_set">
					<option value="read_only"><?php esc_html_e( 'Read Only', 'dejoiy-ai-control-bridge' ); ?></option>
					<option value="developer"><?php esc_html_e( 'Developer', 'dejoiy-ai-control-bridge' ); ?></option>
					<option value="admin"><?php esc_html_e( 'Admin (Full)', 'dejoiy-ai-control-bridge' ); ?></option>
				</select>
			</td>
		</tr>
	</table>
	<?php submit_button( __( 'Create Agent & API Key', 'dejoiy-ai-control-bridge' ) ); ?>
</form>

<h2><?php esc_html_e( 'Connected Agents', 'dejoiy-ai-control-bridge' ); ?></h2>
<table class="widefat striped">
	<thead>
		<tr>
			<th>ID</th>
			<th><?php esc_html_e( 'Name', 'dejoiy-ai-control-bridge' ); ?></th>
			<th><?php esc_html_e( 'Key Prefix', 'dejoiy-ai-control-bridge' ); ?></th>
			<th><?php esc_html_e( 'Permissions', 'dejoiy-ai-control-bridge' ); ?></th>
			<th><?php esc_html_e( 'Status', 'dejoiy-ai-control-bridge' ); ?></th>
			<th><?php esc_html_e( 'Last Seen', 'dejoiy-ai-control-bridge' ); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $agents as $agent ) : ?>
		<tr>
			<td><?php echo esc_html( (string) $agent['id'] ); ?></td>
			<td><?php echo esc_html( $agent['name'] ); ?></td>
			<td><code><?php echo esc_html( $agent['api_key_prefix'] ); ?>…</code></td>
			<td><?php echo esc_html( is_array( $agent['permissions'] ) ? implode( ', ', $agent['permissions'] ) : '' ); ?></td>
			<td><?php echo esc_html( $agent['status'] ); ?></td>
			<td><?php echo esc_html( $agent['last_seen'] ?? '—' ); ?></td>
			<td>
				<?php if ( 'active' === $agent['status'] ) : ?>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
					<?php wp_nonce_field( 'dejoiy_acb_revoke_agent' ); ?>
					<input type="hidden" name="action" value="dejoiy_acb_revoke_agent" />
					<input type="hidden" name="agent_id" value="<?php echo esc_attr( (string) $agent['id'] ); ?>" />
					<button type="submit" class="button button-link-delete"><?php esc_html_e( 'Revoke', 'dejoiy-ai-control-bridge' ); ?></button>
				</form>
				<?php endif; ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
