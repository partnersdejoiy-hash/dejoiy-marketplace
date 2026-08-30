<?php
/**
 * MCP-compatible REST endpoints.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge\Api;

use Dejoiy\AiControlBridge\Auth\RestAuthMiddleware;
use Dejoiy\AiControlBridge\MCP\ToolRegistry;
use Dejoiy\AiControlBridge\Security\ActionClassifier;
use Dejoiy\AiControlBridge\Security\ApprovalQueue;
use Dejoiy\AiControlBridge\Security\AuditLogger;
use Dejoiy\AiControlBridge\Services\BackupService;
use Dejoiy\AiControlBridge\Services\DatabaseService;
use Dejoiy\AiControlBridge\Services\FileSystemService;
use Dejoiy\AiControlBridge\Services\PluginManagerService;
use Dejoiy\AiControlBridge\Services\WordPressService;
use WP_REST_Request;
use WP_REST_Server;

/**
 * MCP manifest, tools, and tool execution proxy.
 */
class McpController extends BaseController {

	/**
	 * @var ToolRegistry
	 */
	private $tools;

	/**
	 * @var FileSystemService
	 */
	private $files;

	/**
	 * @var DatabaseService
	 */
	private $database;

	/**
	 * @var PluginManagerService
	 */
	private $plugins;

	/**
	 * @var WordPressService
	 */
	private $wordpress;

	/**
	 * @var BackupService
	 */
	private $backup;

	/**
	 * @var RestAuthMiddleware
	 */
	private $auth;

	/**
	 * @param AuditLogger          $audit      Audit.
	 * @param ActionClassifier     $classifier Classifier.
	 * @param ApprovalQueue        $approvals  Approvals.
	 * @param ToolRegistry         $tools      Tools.
	 * @param FileSystemService    $files      Files.
	 * @param DatabaseService      $database   Database.
	 * @param PluginManagerService $plugins    Plugins.
	 * @param WordPressService     $wordpress  WordPress.
	 * @param BackupService        $backup     Backup.
	 * @param RestAuthMiddleware   $auth       Auth.
	 */
	public function __construct(
		AuditLogger $audit,
		ActionClassifier $classifier,
		ApprovalQueue $approvals,
		ToolRegistry $tools,
		FileSystemService $files,
		DatabaseService $database,
		PluginManagerService $plugins,
		WordPressService $wordpress,
		BackupService $backup,
		RestAuthMiddleware $auth
	) {
		parent::__construct( $audit, $classifier, $approvals );
		$this->tools     = $tools;
		$this->files     = $files;
		$this->database  = $database;
		$this->plugins   = $plugins;
		$this->wordpress = $wordpress;
		$this->backup    = $backup;
		$this->auth      = $auth;
	}

	/**
	 * Register routes.
	 */
	public function register_routes(): void {
		$ns = DEJOIY_ACB_REST_NAMESPACE;

		register_rest_route( $ns, '/mcp/manifest', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'manifest' ),
			'permission_callback' => '__return_true',
		) );

		register_rest_route( $ns, '/mcp/tools', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'tools_list' ),
			'permission_callback' => '__return_true',
		) );

		register_rest_route( $ns, '/mcp/execute', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'execute' ),
			'permission_callback' => $this->auth->require( 'files.read' ),
		) );

		register_rest_route( $ns, '/openapi', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'openapi' ),
			'permission_callback' => '__return_true',
		) );
	}

	/**
	 * @return \WP_REST_Response
	 */
	public function manifest(): \WP_REST_Response {
		$manifest_path = DEJOIY_ACB_PLUGIN_DIR . 'mcp/manifest.json';
		$data = file_exists( $manifest_path )
			? json_decode( file_get_contents( $manifest_path ), true )
			: $this->tools->get_manifest();

		$data['endpoints'] = array(
			'base'     => rest_url( DEJOIY_ACB_REST_NAMESPACE ),
			'manifest' => rest_url( DEJOIY_ACB_REST_NAMESPACE . '/mcp/manifest' ),
			'tools'    => rest_url( DEJOIY_ACB_REST_NAMESPACE . '/mcp/tools' ),
			'execute'  => rest_url( DEJOIY_ACB_REST_NAMESPACE . '/mcp/execute' ),
			'openapi'  => rest_url( DEJOIY_ACB_REST_NAMESPACE . '/openapi' ),
		);

		return new \WP_REST_Response( $data, 200 );
	}

	/**
	 * @return \WP_REST_Response
	 */
	public function tools_list(): \WP_REST_Response {
		return new \WP_REST_Response( $this->tools->get_tools(), 200 );
	}

	/**
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function openapi(): \WP_REST_Response {
		$path = DEJOIY_ACB_PLUGIN_DIR . 'openapi/openapi.yaml';
		$content = file_exists( $path ) ? file_get_contents( $path ) : '';
		return new \WP_REST_Response(
			array(
				'format'  => 'yaml',
				'content' => $content,
				'url'     => DEJOIY_ACB_PLUGIN_URL . 'openapi/openapi.yaml',
			),
			200
		);
	}

	/**
	 * Execute MCP tool by name.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function execute( WP_REST_Request $request ) {
		$tool   = $request->get_param( 'tool' ) ?: '';
		$params = $request->get_param( 'arguments' ) ?: $request->get_json_params()['arguments'] ?? array();

		if ( ! is_array( $params ) ) {
			$params = array();
		}

		$result = $this->dispatch_tool( $tool, $params );
		return $this->respond( $result, $request, 'mcp.' . $tool );
	}

	/**
	 * @param string               $tool   Tool name.
	 * @param array<string, mixed> $params Parameters.
	 * @return mixed
	 */
	private function dispatch_tool( string $tool, array $params ) {
		switch ( $tool ) {
			case 'read_file':
				return $this->files->read( $params['zone'] ?? 'plugins', $params['path'] ?? '' );
			case 'write_file':
				return $this->files->write( $params['zone'] ?? 'plugins', $params['path'] ?? '', $params['content'] ?? '' );
			case 'list_files':
				return $this->files->tree( $params['zone'] ?? 'plugins', $params['path'] ?? '', (int) ( $params['depth'] ?? 3 ) );
			case 'search_files':
				return $this->files->search( $params['zone'] ?? 'plugins', $params['pattern'] ?? '*', (int) ( $params['limit'] ?? 100 ) );
			case 'install_plugin':
				return $this->plugins->install( $params['source'] ?? '' );
			case 'update_plugin':
				return $this->plugins->update( $params['file'] ?? '' );
			case 'activate_plugin':
				return $this->plugins->activate( $params['file'] ?? '' );
			case 'deactivate_plugin':
				return $this->plugins->deactivate( $params['file'] ?? '' );
			case 'create_page':
				$params['post_type'] = 'page';
				return $this->wordpress->create_post( $params );
			case 'create_product':
				return $this->wordpress->create_product( $params );
			case 'run_query':
				return $this->database->query( $params['sql'] ?? 'SELECT 1', false, false );
			case 'create_backup':
				return $this->backup->create( $params['type'] ?? 'database', $params );
			case 'restore_backup':
				return $this->backup->restore( (int) ( $params['backup_id'] ?? 0 ) );
			default:
				return new \WP_Error( 'unknown_tool', __( 'Unknown MCP tool.', 'dejoiy-ai-control-bridge' ), array( 'status' => 400 ) );
		}
	}
}
