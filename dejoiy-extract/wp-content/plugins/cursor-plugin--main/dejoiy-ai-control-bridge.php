<?php
/**
 * Plugin Name:       DEJOIY AI Control Bridge
 * Plugin URI:        https://dejoiy.tech/ai-control-bridge
 * Description:       Enterprise AI operating layer for WordPress — secure APIs for Cursor, Claude, OpenAI Agents, and MCP clients.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            DEJOIY
 * Author URI:        https://dejoiy.tech
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       dejoiy-ai-control-bridge
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Restore missing studio-woocommerce.php before child theme loads (prevents fatal error).
 */
(function () {
	$wc_file = WP_CONTENT_DIR . '/themes/dejoiy/studio-woocommerce.php';
	if ( is_readable( $wc_file ) ) {
		return;
	}
	$seed = __DIR__ . '/assets/studio-woocommerce-seed.php';
	if ( is_readable( $seed ) ) {
		$dir = dirname( $wc_file );
		if ( is_dir( $dir ) || wp_mkdir_p( $dir ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_copy
			@copy( $seed, $wc_file );
		}
		return;
	}
	$stub = '<?php if(!defined("ABSPATH"))exit;define("DEJOIY_STUDIO_CAT_IDS",array(143,153,154,155,156));';
	$stub .= 'if(!function_exists("dejoiy_studio_maybe_set_cookie")){function dejoiy_studio_maybe_set_cookie(){}}';
	$stub .= 'if(!function_exists("dejoiy_studio_is_flow")){function dejoiy_studio_is_flow(){return isset($_GET["dejoiy_studio"])&&$_GET["dejoiy_studio"]==="1";}}';
	$stub .= 'if(!function_exists("dejoiy_studio_is_customizable_product")){function dejoiy_studio_is_customizable_product($id){return true;}}';
	$stub .= 'if(!function_exists("dejoiy_studio_product_url")){function dejoiy_studio_product_url($id){return get_permalink($id);}}';
	$stub .= 'if(!function_exists("dejoiy_studio_use_single_template")){function dejoiy_studio_use_single_template(){return false;}}';
	$dir = dirname( $wc_file );
	if ( is_dir( $dir ) || wp_mkdir_p( $dir ) ) {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		@file_put_contents( $wc_file, $stub );
	}
})();

define( 'DEJOIY_ACB_VERSION', '1.0.0' );
define( 'DEJOIY_ACB_PLUGIN_FILE', __FILE__ );
define( 'DEJOIY_ACB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DEJOIY_ACB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'DEJOIY_ACB_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'DEJOIY_ACB_REST_NAMESPACE', 'dejoiy-ai/v1' );

require_once DEJOIY_ACB_PLUGIN_DIR . 'includes/autoload.php';

/**
 * Returns the plugin singleton instance.
 *
 * @return Plugin
 */
function dejoiy_acb(): Plugin {
	return Plugin::instance();
}

register_activation_hook( __FILE__, array( Activator::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( Deactivator::class, 'deactivate' ) );

dejoiy_acb()->boot();
