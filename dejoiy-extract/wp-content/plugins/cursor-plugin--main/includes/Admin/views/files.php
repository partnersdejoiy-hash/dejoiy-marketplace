<?php
/**
 * File manager view.
 *
 * @package Dejoiy\AiControlBridge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<p><?php esc_html_e( 'Use the REST API or MCP tools to manage files. Allowed zones:', 'dejoiy-ai-control-bridge' ); ?></p>
<ul>
	<?php foreach ( $zones as $zone ) : ?>
	<li><code><?php echo esc_html( $zone ); ?></code></li>
	<?php endforeach; ?>
</ul>
<h3><?php esc_html_e( 'API Endpoints', 'dejoiy-ai-control-bridge' ); ?></h3>
<ul>
	<li><code>GET <?php echo esc_html( rest_url( DEJOIY_ACB_REST_NAMESPACE . '/files/tree' ) ); ?></code></li>
	<li><code>GET <?php echo esc_html( rest_url( DEJOIY_ACB_REST_NAMESPACE . '/files/read' ) ); ?></code></li>
	<li><code>POST <?php echo esc_html( rest_url( DEJOIY_ACB_REST_NAMESPACE . '/files/write' ) ); ?></code></li>
	<li><code>POST <?php echo esc_html( rest_url( DEJOIY_ACB_REST_NAMESPACE . '/files/create' ) ); ?></code></li>
	<li><code>DELETE <?php echo esc_html( rest_url( DEJOIY_ACB_REST_NAMESPACE . '/files/delete' ) ); ?></code></li>
	<li><code>POST <?php echo esc_html( rest_url( DEJOIY_ACB_REST_NAMESPACE . '/files/rename' ) ); ?></code></li>
</ul>
