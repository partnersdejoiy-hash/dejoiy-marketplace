<?php
/**
 * Security settings view.
 *
 * @package Dejoiy\AiControlBridge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ip_list = is_array( $settings['global_ip_allowlist'] ) ? implode( "\n", $settings['global_ip_allowlist'] ) : '';
?>
<?php if ( isset( $_GET['saved'] ) ) : // phpcs:ignore ?>
<div class="notice notice-success"><p><?php esc_html_e( 'Settings saved.', 'dejoiy-ai-control-bridge' ); ?></p></div>
<?php endif; ?>

<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<?php wp_nonce_field( 'dejoiy_acb_security' ); ?>
	<input type="hidden" name="action" value="dejoiy_acb_save_security" />
	<table class="form-table">
		<tr>
			<th><?php esc_html_e( 'Require Approval for Critical Actions', 'dejoiy-ai-control-bridge' ); ?></th>
			<td><label><input type="checkbox" name="require_approval" value="1" <?php checked( $settings['require_approval'] ); ?> /> <?php esc_html_e( 'Enabled', 'dejoiy-ai-control-bridge' ); ?></label></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'IP Allowlist', 'dejoiy-ai-control-bridge' ); ?></th>
			<td>
				<label><input type="checkbox" name="ip_allowlist_enabled" value="1" <?php checked( $settings['ip_allowlist_enabled'] ); ?> /> <?php esc_html_e( 'Enforce global IP allowlist', 'dejoiy-ai-control-bridge' ); ?></label>
				<textarea name="global_ip_allowlist" rows="5" class="large-text" placeholder="192.168.1.1&#10;10.0.0.0/8"><?php echo esc_textarea( $ip_list ); ?></textarea>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Database Write (Global)', 'dejoiy-ai-control-bridge' ); ?></th>
			<td><label><input type="checkbox" name="db_write_enabled" value="1" <?php checked( $settings['db_write_enabled'] ); ?> /> <?php esc_html_e( 'Allow write queries when agent has permission', 'dejoiy-ai-control-bridge' ); ?></label></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Git Integration', 'dejoiy-ai-control-bridge' ); ?></th>
			<td>
				<label><input type="checkbox" name="git_enabled" value="1" <?php checked( $settings['git_enabled'] ); ?> /> <?php esc_html_e( 'Enabled', 'dejoiy-ai-control-bridge' ); ?></label>
				<input type="text" name="git_repo_path" class="large-text" value="<?php echo esc_attr( $settings['git_repo_path'] ); ?>" placeholder="<?php echo esc_attr( ABSPATH ); ?>" />
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'JWT Expiry (seconds)', 'dejoiy-ai-control-bridge' ); ?></th>
			<td><input type="number" name="jwt_expiry" value="<?php echo esc_attr( (string) $settings['jwt_expiry'] ); ?>" min="300" /></td>
		</tr>
	</table>
	<?php submit_button(); ?>
</form>

<h2><?php esc_html_e( 'Authentication', 'dejoiy-ai-control-bridge' ); ?></h2>
<ul>
	<li><?php esc_html_e( 'API Key header: X-Dejoiy-API-Key or Authorization: Bearer dacb_...', 'dejoiy-ai-control-bridge' ); ?></li>
	<li><?php esc_html_e( 'JWT: POST /auth/token with API key, then Authorization: Bearer <jwt>', 'dejoiy-ai-control-bridge' ); ?></li>
</ul>
