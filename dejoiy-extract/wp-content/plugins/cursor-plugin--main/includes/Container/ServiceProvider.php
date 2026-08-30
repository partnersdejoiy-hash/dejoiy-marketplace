<?php
/**
 * Registers all plugin services in the container.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge\Container;

use Dejoiy\AiControlBridge\Admin\Menu;
use Dejoiy\AiControlBridge\Api\BackupController;
use Dejoiy\AiControlBridge\Api\DatabaseController;
use Dejoiy\AiControlBridge\Api\DeploymentController;
use Dejoiy\AiControlBridge\Api\DejoiyOsController;
use Dejoiy\AiControlBridge\Api\FilesController;
use Dejoiy\AiControlBridge\Api\McpController;
use Dejoiy\AiControlBridge\Api\PluginsController;
use Dejoiy\AiControlBridge\Api\Router;
use Dejoiy\AiControlBridge\Api\StructureController;
use Dejoiy\AiControlBridge\Api\ThemesController;
use Dejoiy\AiControlBridge\Api\WordPressController;
use Dejoiy\AiControlBridge\Auth\ApiKeyAuth;
use Dejoiy\AiControlBridge\Auth\JwtAuth;
use Dejoiy\AiControlBridge\Auth\PermissionChecker;
use Dejoiy\AiControlBridge\Auth\RestAuthMiddleware;
use Dejoiy\AiControlBridge\MCP\ToolRegistry;
use Dejoiy\AiControlBridge\Security\ActionClassifier;
use Dejoiy\AiControlBridge\Security\ApprovalQueue;
use Dejoiy\AiControlBridge\Security\AuditLogger;
use Dejoiy\AiControlBridge\Security\IpAllowlist;
use Dejoiy\AiControlBridge\Services\AgentService;
use Dejoiy\AiControlBridge\Services\BackupService;
use Dejoiy\AiControlBridge\Services\CacheService;
use Dejoiy\AiControlBridge\Services\DatabaseService;
use Dejoiy\AiControlBridge\Services\DeploymentService;
use Dejoiy\AiControlBridge\Services\FileSystemService;
use Dejoiy\AiControlBridge\Services\MigrationService;
use Dejoiy\AiControlBridge\Services\PluginManagerService;
use Dejoiy\AiControlBridge\Services\StructureService;
use Dejoiy\AiControlBridge\Services\ThemeManagerService;
use Dejoiy\AiControlBridge\Services\WordPressService;

/**
 * Service provider for dependency injection.
 */
class ServiceProvider {

	/**
	 * @param Container $container Container instance.
	 */
	public function register( Container $container ): void {
		// Security & Auth.
		$container->singleton( AuditLogger::class );
		$container->singleton( ActionClassifier::class );
		$container->singleton( ApprovalQueue::class );
		$container->singleton( IpAllowlist::class );
		$container->singleton( ApiKeyAuth::class );
		$container->singleton( JwtAuth::class );
		$container->singleton( PermissionChecker::class );
		$container->singleton( RestAuthMiddleware::class );

		// Services.
		$container->singleton( AgentService::class );
		$container->singleton( FileSystemService::class );
		$container->singleton( DatabaseService::class );
		$container->singleton( WordPressService::class );
		$container->singleton( PluginManagerService::class );
		$container->singleton( ThemeManagerService::class );
		$container->singleton( DeploymentService::class );
		$container->singleton( BackupService::class );
		$container->singleton( CacheService::class );
		$container->singleton( StructureService::class );
		$container->singleton( MigrationService::class );
		$container->singleton( ToolRegistry::class );

		// API Controllers.
		$container->singleton( Router::class );
		$container->singleton( FilesController::class );
		$container->singleton( DatabaseController::class );
		$container->singleton( WordPressController::class );
		$container->singleton( PluginsController::class );
		$container->singleton( ThemesController::class );
		$container->singleton( DeploymentController::class );
		$container->singleton( BackupController::class );
		$container->singleton( McpController::class );
		$container->singleton( StructureController::class );

		// Admin.
		$container->singleton( Menu::class );
	}
}
