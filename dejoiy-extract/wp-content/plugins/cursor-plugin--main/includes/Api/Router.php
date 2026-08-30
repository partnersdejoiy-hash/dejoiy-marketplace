<?php
/**
 * REST API route registration.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge\Api;

/**
 * Registers all DEJOIY AI Control Bridge REST routes.
 */
class Router {

	/**
	 * @var FilesController
	 */
	private $files;

	/**
	 * @var DatabaseController
	 */
	private $database;

	/**
	 * @var WordPressController
	 */
	private $wordpress;

	/**
	 * @var PluginsController
	 */
	private $plugins;

	/**
	 * @var ThemesController
	 */
	private $themes;

	/**
	 * @var DeploymentController
	 */
	private $deployment;

	/**
	 * @var BackupController
	 */
	private $backup;

	/**
	 * @var McpController
	 */
	private $mcp;

	/**
	 * @var StructureController
	 */
	private $structure;

	/**
	 * @var DejoiyOsController
	 */
	private $dejoiy_os;

	/**
	 * @param FilesController      $files      Files.
	 * @param DatabaseController   $database   Database.
	 * @param WordPressController  $wordpress  WordPress.
	 * @param PluginsController    $plugins    Plugins.
	 * @param ThemesController     $themes     Themes.
	 * @param DeploymentController $deployment Deployment.
	 * @param BackupController     $backup     Backup.
	 * @param McpController        $mcp        MCP.
	 * @param StructureController  $structure  Structure.
	 * @param DejoiyOsController   $dejoiy_os  Public DEJOIY OS API.
	 */
	public function __construct(
		FilesController $files,
		DatabaseController $database,
		WordPressController $wordpress,
		PluginsController $plugins,
		ThemesController $themes,
		DeploymentController $deployment,
		BackupController $backup,
		McpController $mcp,
		StructureController $structure,
		DejoiyOsController $dejoiy_os
	) {
		$this->files      = $files;
		$this->database   = $database;
		$this->wordpress  = $wordpress;
		$this->plugins    = $plugins;
		$this->themes     = $themes;
		$this->deployment = $deployment;
		$this->backup     = $backup;
		$this->mcp        = $mcp;
		$this->structure  = $structure;
		$this->dejoiy_os  = $dejoiy_os;
	}

	/**
	 * Register routes.
	 */
	public function register(): void {
		$this->dejoiy_os->register_routes();
		$this->files->register_routes();
		$this->database->register_routes();
		$this->wordpress->register_routes();
		$this->plugins->register_routes();
		$this->themes->register_routes();
		$this->deployment->register_routes();
		$this->backup->register_routes();
		$this->mcp->register_routes();
		$this->structure->register_routes();
	}
}
