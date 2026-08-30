<?php
/**
 * Main plugin bootstrap.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge;

use Dejoiy\AiControlBridge\Admin\Menu;
use Dejoiy\AiControlBridge\Api\Router;
use Dejoiy\AiControlBridge\Container\Container as PluginContainer;
use Dejoiy\AiControlBridge\Container\ServiceProvider;
use Dejoiy\AiControlBridge\Cron\ApprovalProcessor;
use Dejoiy\AiControlBridge\Security\AuditLogger;

/**
 * Plugin singleton.
 */
final class Plugin {

	/**
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * @var PluginContainer
	 */
	private $container;

	/**
	 * @return Plugin
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->container = new PluginContainer();
		$this->register_services();
	}

	/**
	 * @return PluginContainer
	 */
	public function container(): PluginContainer {
		return $this->container;
	}

	/**
	 * Boot plugin hooks.
	 */
	public function boot(): void {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
		add_action( 'dejoiy_acb_process_approvals', array( ApprovalProcessor::class, 'process_expired' ) );

		if ( ! wp_next_scheduled( 'dejoiy_acb_process_approvals' ) ) {
			wp_schedule_event( time(), 'hourly', 'dejoiy_acb_process_approvals' );
		}
	}

	/**
	 * Initialize admin and i18n.
	 */
	public function init(): void {
		load_plugin_textdomain(
			'dejoiy-ai-control-bridge',
			false,
			dirname( DEJOIY_ACB_PLUGIN_BASENAME ) . '/languages'
		);

		if ( is_admin() ) {
			$this->container->get( Menu::class )->register();
		}
	}

	/**
	 * Register REST API routes.
	 */
	public function register_rest_routes(): void {
		$this->container->get( Router::class )->register();
	}

	/**
	 * Register all services in the container.
	 */
	private function register_services(): void {
		$providers = array(
			ServiceProvider::class,
		);

		foreach ( $providers as $provider ) {
			( new $provider() )->register( $this->container );
		}
	}
}
