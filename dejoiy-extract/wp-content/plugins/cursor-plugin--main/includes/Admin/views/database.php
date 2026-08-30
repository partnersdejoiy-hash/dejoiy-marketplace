<?php
/**
 * Database manager view.
 *
 * @package Dejoiy\AiControlBridge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<p><?php esc_html_e( 'SELECT queries are allowed by default. UPDATE, INSERT, DELETE, and ALTER require elevated permissions and may require approval.', 'dejoiy-ai-control-bridge' ); ?></p>
<ul>
	<li><code>GET <?php echo esc_html( $api_tables ); ?></code></li>
	<li><code>GET <?php echo esc_html( $api_schema ); ?></code></li>
	<li><code>GET/POST <?php echo esc_html( rest_url( DEJOIY_ACB_REST_NAMESPACE . '/database/query' ) ); ?>?sql=SELECT...</code></li>
</ul>
