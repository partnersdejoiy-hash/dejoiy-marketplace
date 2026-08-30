<?php
/**
 * MCP tool definitions registry.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge\MCP;

/**
 * Defines MCP-compatible tools for AI clients.
 */
class ToolRegistry {

	/**
	 * Get MCP manifest metadata.
	 *
	 * @return array<string, mixed>
	 */
	public function get_manifest(): array {
		return array(
			'name'        => 'dejoiy-ai-control-bridge',
			'version'     => DEJOIY_ACB_VERSION,
			'description' => 'DEJOIY AI Control Bridge — WordPress AI Operating Layer',
			'protocol'    => 'mcp-compatible-rest',
			'vendor'      => 'DEJOIY',
			'website'     => 'https://dejoiy.tech',
		);
	}

	/**
	 * Get all tool definitions.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function get_tools(): array {
		$tools_file = DEJOIY_ACB_PLUGIN_DIR . 'mcp/tools.json';
		if ( file_exists( $tools_file ) ) {
			$decoded = json_decode( file_get_contents( $tools_file ), true );
			if ( is_array( $decoded ) ) {
				return $decoded;
			}
		}

		return $this->default_tools();
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	private function default_tools(): array {
		return array(
			array(
				'name'        => 'read_file',
				'description' => 'Read a file from wp-content zones (themes, plugins, uploads, mu-plugins).',
				'inputSchema' => array(
					'type'       => 'object',
					'properties' => array(
						'zone' => array( 'type' => 'string', 'enum' => array( 'wp-content', 'themes', 'plugins', 'uploads', 'mu-plugins' ) ),
						'path' => array( 'type' => 'string' ),
					),
					'required'   => array( 'zone', 'path' ),
				),
			),
			array(
				'name'        => 'write_file',
				'description' => 'Write content to a file in an allowed zone.',
				'inputSchema' => array(
					'type'       => 'object',
					'properties' => array(
						'zone'    => array( 'type' => 'string' ),
						'path'    => array( 'type' => 'string' ),
						'content' => array( 'type' => 'string' ),
					),
					'required'   => array( 'zone', 'path', 'content' ),
				),
			),
			array(
				'name'        => 'list_files',
				'description' => 'List directory tree in a zone.',
				'inputSchema' => array(
					'type'       => 'object',
					'properties' => array(
						'zone'  => array( 'type' => 'string' ),
						'path'  => array( 'type' => 'string' ),
						'depth' => array( 'type' => 'integer' ),
					),
				),
			),
			array(
				'name'        => 'search_files',
				'description' => 'Search files by glob pattern.',
				'inputSchema' => array(
					'type'       => 'object',
					'properties' => array(
						'zone'    => array( 'type' => 'string' ),
						'pattern' => array( 'type' => 'string' ),
						'limit'   => array( 'type' => 'integer' ),
					),
				),
			),
			array(
				'name'        => 'install_plugin',
				'description' => 'Install a plugin from WordPress.org slug or ZIP URL.',
				'inputSchema' => array(
					'type'       => 'object',
					'properties' => array( 'source' => array( 'type' => 'string' ) ),
					'required'   => array( 'source' ),
				),
			),
			array(
				'name'        => 'update_plugin',
				'description' => 'Update an installed plugin.',
				'inputSchema' => array(
					'type'       => 'object',
					'properties' => array( 'file' => array( 'type' => 'string' ) ),
					'required'   => array( 'file' ),
				),
			),
			array(
				'name'        => 'activate_plugin',
				'description' => 'Activate a plugin.',
				'inputSchema' => array(
					'type'       => 'object',
					'properties' => array( 'file' => array( 'type' => 'string' ) ),
					'required'   => array( 'file' ),
				),
			),
			array(
				'name'        => 'deactivate_plugin',
				'description' => 'Deactivate a plugin.',
				'inputSchema' => array(
					'type'       => 'object',
					'properties' => array( 'file' => array( 'type' => 'string' ) ),
					'required'   => array( 'file' ),
				),
			),
			array(
				'name'        => 'create_page',
				'description' => 'Create a WordPress page.',
				'inputSchema' => array(
					'type'       => 'object',
					'properties' => array(
						'post_title'   => array( 'type' => 'string' ),
						'post_content' => array( 'type' => 'string' ),
						'post_status'  => array( 'type' => 'string' ),
					),
					'required'   => array( 'post_title' ),
				),
			),
			array(
				'name'        => 'create_product',
				'description' => 'Create a WooCommerce product.',
				'inputSchema' => array(
					'type'       => 'object',
					'properties' => array(
						'name'           => array( 'type' => 'string' ),
						'regular_price'  => array( 'type' => 'string' ),
						'description'    => array( 'type' => 'string' ),
					),
					'required'   => array( 'name' ),
				),
			),
			array(
				'name'        => 'run_query',
				'description' => 'Execute a SELECT SQL query (read-only by default).',
				'inputSchema' => array(
					'type'       => 'object',
					'properties' => array( 'sql' => array( 'type' => 'string' ) ),
					'required'   => array( 'sql' ),
				),
			),
			array(
				'name'        => 'create_backup',
				'description' => 'Create a backup (full, database, theme, or plugin).',
				'inputSchema' => array(
					'type'       => 'object',
					'properties' => array(
						'type' => array( 'type' => 'string', 'enum' => array( 'full', 'database', 'theme', 'plugin' ) ),
					),
				),
			),
			array(
				'name'        => 'restore_backup',
				'description' => 'Restore from a backup (requires approval).',
				'inputSchema' => array(
					'type'       => 'object',
					'properties' => array( 'backup_id' => array( 'type' => 'integer' ) ),
					'required'   => array( 'backup_id' ),
				),
			),
		);
	}
}
