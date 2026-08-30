<?php
/**
 * Plugin management service.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge\Services;

use WP_Error;

/**
 * Install, activate, deactivate, update, and delete plugins.
 */
class PluginManagerService {

	/**
	 * List installed plugins.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function list_plugins(): array {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all     = get_plugins();
		$active  = get_option( 'active_plugins', array() );
		$result  = array();

		foreach ( $all as $file => $data ) {
			$result[] = array(
				'file'    => $file,
				'name'    => $data['Name'],
				'version' => $data['Version'],
				'active'  => in_array( $file, $active, true ),
				'author'  => $data['Author'] ?? '',
			);
		}

		return $result;
	}

	/**
	 * Install plugin from WordPress.org slug or ZIP URL.
	 *
	 * @param string $source Slug or URL.
	 * @return array<string, mixed>|WP_Error
	 */
	public function install( string $source ) {
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		$skin     = new \WP_Ajax_Upgrader_Skin();
		$upgrader = new \Plugin_Upgrader( $skin );

		if ( filter_var( $source, FILTER_VALIDATE_URL ) ) {
			$result = $upgrader->install( $source );
		} else {
			$api = plugins_api( 'plugin_information', array( 'slug' => sanitize_key( $source ) ) );
			if ( is_wp_error( $api ) ) {
				return $api;
			}
			$result = $upgrader->install( $api->download_link );
		}

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		if ( true === $result ) {
			return array( 'installed' => true, 'source' => $source );
		}

		return new WP_Error( 'install_failed', __( 'Plugin installation failed.', 'dejoiy-ai-control-bridge' ), array( 'status' => 500 ) );
	}

	/**
	 * Activate plugin.
	 *
	 * @param string $plugin_file Plugin file path.
	 * @return array<string, mixed>|WP_Error
	 */
	public function activate( string $plugin_file ) {
		if ( ! file_exists( WP_PLUGIN_DIR . '/' . $plugin_file ) ) {
			return new WP_Error( 'not_found', __( 'Plugin not found.', 'dejoiy-ai-control-bridge' ), array( 'status' => 404 ) );
		}

		$result = activate_plugin( $plugin_file );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return array( 'activated' => true, 'file' => $plugin_file );
	}

	/**
	 * Deactivate plugin.
	 *
	 * @param string $plugin_file Plugin file.
	 * @return array<string, mixed>
	 */
	public function deactivate( string $plugin_file ): array {
		deactivate_plugins( $plugin_file );
		return array( 'deactivated' => true, 'file' => $plugin_file );
	}

	/**
	 * Delete plugin.
	 *
	 * @param string $plugin_file Plugin file.
	 * @return array<string, mixed>|WP_Error
	 */
	public function delete( string $plugin_file ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		if ( ! is_plugin_inactive( $plugin_file ) ) {
			deactivate_plugins( $plugin_file );
		}

		$deleted = delete_plugins( array( $plugin_file ) );
		if ( is_wp_error( $deleted ) ) {
			return $deleted;
		}

		return array( 'deleted' => true, 'file' => $plugin_file );
	}

	/**
	 * Update plugin.
	 *
	 * @param string $plugin_file Plugin file.
	 * @return array<string, mixed>|WP_Error
	 */
	public function update( string $plugin_file ) {
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		$skin     = new \WP_Ajax_Upgrader_Skin();
		$upgrader = new \Plugin_Upgrader( $skin );
		$result   = $upgrader->upgrade( $plugin_file );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return array( 'updated' => true, 'file' => $plugin_file );
	}

	/**
	 * Create a new plugin scaffold.
	 *
	 * @param string $slug Plugin slug.
	 * @param string $name Plugin name.
	 * @return array<string, mixed>|WP_Error
	 */
	public function create_plugin( string $slug, string $name ): array {
		$slug = sanitize_key( $slug );
		$dir  = WP_PLUGIN_DIR . '/' . $slug;

		if ( file_exists( $dir ) ) {
			return new WP_Error( 'exists', __( 'Plugin directory exists.', 'dejoiy-ai-control-bridge' ), array( 'status' => 409 ) );
		}

		wp_mkdir_p( $dir );

		$main_file = "<?php\n/**\n * Plugin Name: {$name}\n * Version: 1.0.0\n */\n\ndeclare(strict_types=1);\n\nif ( ! defined( 'ABSPATH' ) ) {\n\texit;\n}\n";
		file_put_contents( $dir . '/' . $slug . '.php', $main_file );

		return array( 'created' => true, 'slug' => $slug, 'path' => $slug . '/' . $slug . '.php' );
	}
}
