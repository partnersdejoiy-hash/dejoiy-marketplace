<?php
/**
 * WordPress structure introspection.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge\Services;

/**
 * Read entire WordPress ecosystem structure for AI agents.
 */
class StructureService {

	/**
	 * Get complete WordPress structure overview.
	 *
	 * @return array<string, mixed>
	 */
	public function get_full_structure(): array {
		return array(
			'wordpress' => array(
				'version'     => get_bloginfo( 'version' ),
				'site_url'    => get_site_url(),
				'home_url'    => get_home_url(),
				'is_multisite' => is_multisite(),
				'language'    => get_locale(),
				'timezone'    => wp_timezone_string(),
			),
			'themes'    => $this->get_themes_summary(),
			'plugins'   => $this->get_plugins_summary(),
			'content'   => $this->get_content_summary(),
			'database'  => array(
				'prefix'  => $GLOBALS['wpdb']->prefix,
				'charset' => $GLOBALS['wpdb']->charset,
			),
			'uploads'   => $this->get_uploads_info(),
			'users'     => $this->get_users_summary(),
			'logs'      => $this->get_available_logs(),
		);
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	private function get_themes_summary(): array {
		$themes = wp_get_themes();
		$result = array();
		foreach ( $themes as $slug => $theme ) {
			$result[] = array(
				'slug'   => $slug,
				'name'   => $theme->get( 'Name' ),
				'active' => ( $slug === get_stylesheet() ),
			);
		}
		return $result;
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	private function get_plugins_summary(): array {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$all    = get_plugins();
		$active = get_option( 'active_plugins', array() );
		$result = array();
		foreach ( $all as $file => $data ) {
			$result[] = array(
				'file'   => $file,
				'name'   => $data['Name'],
				'active' => in_array( $file, $active, true ),
			);
		}
		return $result;
	}

	/**
	 * @return array<string, mixed>
	 */
	private function get_content_summary(): array {
		$types = get_post_types( array( 'public' => true ), 'objects' );
		$summary = array();
		foreach ( $types as $type => $obj ) {
			$count = wp_count_posts( $type );
			$summary[ $type ] = array(
				'label' => $obj->label,
				'count' => array_sum( (array) $count ),
			);
		}
		return $summary;
	}

	/**
	 * @return array<string, mixed>
	 */
	private function get_uploads_info(): array {
		$upload = wp_upload_dir();
		return array(
			'path'  => $upload['basedir'],
			'url'   => $upload['baseurl'],
			'year'  => gmdate( 'Y' ),
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	private function get_users_summary(): array {
		$counts = count_users();
		return array(
			'total' => $counts['total_users'] ?? 0,
			'roles' => $counts['avail_roles'] ?? array(),
		);
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	private function get_available_logs(): array {
		$logs = array();

		$debug_log = WP_CONTENT_DIR . '/debug.log';
		if ( file_exists( $debug_log ) && is_readable( $debug_log ) ) {
			$logs[] = array(
				'name' => 'debug.log',
				'path' => 'wp-content/debug.log',
				'size' => filesize( $debug_log ),
			);
		}

		$acb_log_dir = WP_CONTENT_DIR . '/dejoiy-acb-logs';
		if ( is_dir( $acb_log_dir ) ) {
			foreach ( glob( $acb_log_dir . '/*.log' ) ?: array() as $file ) {
				$logs[] = array(
					'name' => basename( $file ),
					'path' => 'wp-content/dejoiy-acb-logs/' . basename( $file ),
					'size' => filesize( $file ),
				);
			}
		}

		return $logs;
	}

	/**
	 * Read log file tail.
	 *
	 * @param string $log_path Relative log path.
	 * @param int    $lines    Lines to read.
	 * @return array<string, mixed>|\WP_Error
	 */
	public function read_log( string $log_path, int $lines = 100 ) {
		$allowed = array( 'wp-content/debug.log' );
		$full    = WP_CONTENT_DIR . '/' . ltrim( str_replace( 'wp-content/', '', $log_path ), '/' );

		if ( ! file_exists( $full ) || ! is_readable( $full ) ) {
			return new \WP_Error( 'not_found', __( 'Log not found.', 'dejoiy-ai-control-bridge' ), array( 'status' => 404 ) );
		}

		$content = file( $full );
		$tail    = array_slice( $content ?: array(), -$lines );

		return array(
			'path'    => $log_path,
			'lines'   => count( $tail ),
			'content' => implode( '', $tail ),
		);
	}
}
