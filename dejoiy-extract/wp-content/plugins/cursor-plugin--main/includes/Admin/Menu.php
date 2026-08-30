<?php
/**
 * AI Control Center admin menu.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge\Admin;

use Dejoiy\AiControlBridge\Auth\JwtAuth;
use Dejoiy\AiControlBridge\Auth\PermissionChecker;
use Dejoiy\AiControlBridge\Security\ApprovalQueue;
use Dejoiy\AiControlBridge\Security\AuditLogger;
use Dejoiy\AiControlBridge\Services\AgentService;
use Dejoiy\AiControlBridge\Services\BackupService;
use Dejoiy\AiControlBridge\Services\DeploymentService;

/**
 * Registers AI Control Center admin area.
 */
class Menu {

	/**
	 * @var AgentService
	 */
	private $agents;

	/**
	 * @var AuditLogger
	 */
	private $audit;

	/**
	 * @var ApprovalQueue
	 */
	private $approvals;

	/**
	 * @var BackupService
	 */
	private $backup;

	/**
	 * @var DeploymentService
	 */
	private $deployment;

	/**
	 * @var JwtAuth
	 */
	private $jwt;

	/**
	 * @param AgentService      $agents     Agents.
	 * @param AuditLogger       $audit      Audit.
	 * @param ApprovalQueue     $approvals  Approvals.
	 * @param BackupService     $backup     Backup.
	 * @param DeploymentService $deployment Deployment.
	 * @param JwtAuth           $jwt        JWT.
	 */
	public function __construct(
		AgentService $agents,
		AuditLogger $audit,
		ApprovalQueue $approvals,
		BackupService $backup,
		DeploymentService $deployment,
		JwtAuth $jwt
	) {
		$this->agents     = $agents;
		$this->audit      = $audit;
		$this->approvals  = $approvals;
		$this->backup     = $backup;
		$this->deployment = $deployment;
		$this->jwt        = $jwt;
	}

	/**
	 * Register admin hooks.
	 */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_post_dejoiy_acb_create_agent', array( $this, 'handle_create_agent' ) );
		add_action( 'admin_post_dejoiy_acb_revoke_agent', array( $this, 'handle_revoke_agent' ) );
		add_action( 'admin_post_dejoiy_acb_approve_action', array( $this, 'handle_approve' ) );
		add_action( 'admin_post_dejoiy_acb_reject_action', array( $this, 'handle_reject' ) );
		add_action( 'admin_post_dejoiy_acb_save_security', array( $this, 'handle_save_security' ) );
	}

	/**
	 * Add admin menu pages.
	 */
	public function add_menu(): void {
		add_menu_page(
			__( 'AI Control Center', 'dejoiy-ai-control-bridge' ),
			__( 'AI Control Center', 'dejoiy-ai-control-bridge' ),
			'manage_options',
			'dejoiy-ai-control-center',
			array( $this, 'render_dashboard' ),
			'dashicons-cloud',
			3
		);

		$submenus = array(
			'dejoiy-ai-control-center'           => array( __( 'Dashboard', 'dejoiy-ai-control-bridge' ), array( $this, 'render_dashboard' ) ),
			'dejoiy-acb-agents'                  => array( __( 'Connected Agents', 'dejoiy-ai-control-bridge' ), array( $this, 'render_agents' ) ),
			'dejoiy-acb-activity'                => array( __( 'Activity Feed', 'dejoiy-ai-control-bridge' ), array( $this, 'render_activity' ) ),
			'dejoiy-acb-files'                   => array( __( 'File Manager', 'dejoiy-ai-control-bridge' ), array( $this, 'render_files' ) ),
			'dejoiy-acb-database'                => array( __( 'Database Manager', 'dejoiy-ai-control-bridge' ), array( $this, 'render_database' ) ),
			'dejoiy-acb-deployment'              => array( __( 'Deployment Center', 'dejoiy-ai-control-bridge' ), array( $this, 'render_deployment' ) ),
			'dejoiy-acb-backup'                  => array( __( 'Backup Center', 'dejoiy-ai-control-bridge' ), array( $this, 'render_backup' ) ),
			'dejoiy-acb-audit'                   => array( __( 'Audit Logs', 'dejoiy-ai-control-bridge' ), array( $this, 'render_audit' ) ),
			'dejoiy-acb-security'                => array( __( 'Security Settings', 'dejoiy-ai-control-bridge' ), array( $this, 'render_security' ) ),
		);

		foreach ( $submenus as $slug => $item ) {
			add_submenu_page(
				'dejoiy-ai-control-center',
				$item[0],
				$item[0],
				'manage_options',
				$slug,
				$item[1]
			);
		}
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook Hook suffix.
	 */
	public function enqueue_assets( string $hook ): void {
		if ( false === strpos( $hook, 'dejoiy' ) ) {
			return;
		}

		wp_enqueue_style(
			'dejoiy-acb-admin',
			DEJOIY_ACB_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			DEJOIY_ACB_VERSION
		);

		wp_enqueue_script(
			'dejoiy-acb-admin',
			DEJOIY_ACB_PLUGIN_URL . 'assets/js/admin.js',
			array(),
			DEJOIY_ACB_VERSION,
			true
		);

		wp_localize_script(
			'dejoiy-acb-admin',
			'dejoiyAcb',
			array(
				'restUrl'   => rest_url( DEJOIY_ACB_REST_NAMESPACE ),
				'nonce'     => wp_create_nonce( 'wp_rest' ),
				'manifest'  => rest_url( DEJOIY_ACB_REST_NAMESPACE . '/mcp/manifest' ),
			)
		);
	}

	/**
	 * Render dashboard overview.
	 */
	public function render_dashboard(): void {
		$agents   = $this->agents->list_all();
		$pending  = $this->approvals->get_pending();
		$activity = $this->audit->get_recent( 10 );
		$this->render_wrapper( 'dashboard', compact( 'agents', 'pending', 'activity' ) );
	}

	/**
	 * Render connected agents page.
	 */
	public function render_agents(): void {
		$agents = $this->agents->list_all();
		$permissions = PermissionChecker::ALL_PERMISSIONS;
		$this->render_wrapper( 'agents', compact( 'agents', 'permissions' ) );
	}

	/**
	 * Render activity feed.
	 */
	public function render_activity(): void {
		$activity = $this->audit->get_recent( 100 );
		$pending  = $this->approvals->get_pending();
		$this->render_wrapper( 'activity', compact( 'activity', 'pending' ) );
	}

	/**
	 * Render file manager info.
	 */
	public function render_files(): void {
		$this->render_wrapper( 'files', array(
			'zones' => array( 'wp-content', 'themes', 'plugins', 'uploads', 'mu-plugins' ),
			'api'   => rest_url( DEJOIY_ACB_REST_NAMESPACE . '/files/tree' ),
		) );
	}

	/**
	 * Render database manager info.
	 */
	public function render_database(): void {
		$this->render_wrapper( 'database', array(
			'api_tables' => rest_url( DEJOIY_ACB_REST_NAMESPACE . '/database/tables' ),
			'api_schema' => rest_url( DEJOIY_ACB_REST_NAMESPACE . '/database/schema' ),
		) );
	}

	/**
	 * Render deployment center.
	 */
	public function render_deployment(): void {
		$history = $this->deployment->get_history( 20 );
		$git     = $this->deployment->git_status();
		$this->render_wrapper( 'deployment', compact( 'history', 'git' ) );
	}

	/**
	 * Render backup center.
	 */
	public function render_backup(): void {
		$backups = $this->backup->list_backups();
		$this->render_wrapper( 'backup', compact( 'backups' ) );
	}

	/**
	 * Render audit logs.
	 */
	public function render_audit(): void {
		$logs = $this->audit->get_recent( 200 );
		$this->render_wrapper( 'audit', compact( 'logs' ) );
	}

	/**
	 * Render security settings.
	 */
	public function render_security(): void {
		$settings = array(
			'require_approval'     => get_option( 'dejoiy_acb_require_approval', true ),
			'ip_allowlist_enabled' => get_option( 'dejoiy_acb_ip_allowlist_enabled', false ),
			'global_ip_allowlist'  => get_option( 'dejoiy_acb_global_ip_allowlist', array() ),
			'db_write_enabled'     => get_option( 'dejoiy_acb_db_write_enabled', false ),
			'git_enabled'          => get_option( 'dejoiy_acb_git_enabled', false ),
			'git_repo_path'        => get_option( 'dejoiy_acb_git_repo_path', '' ),
			'jwt_expiry'           => get_option( 'dejoiy_acb_jwt_expiry', 3600 ),
		);
		$this->render_wrapper( 'security', compact( 'settings' ) );
	}

	/**
	 * @param string               $view View name.
	 * @param array<string, mixed> $data Data.
	 */
	private function render_wrapper( string $view, array $data = array() ): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'dejoiy-ai-control-bridge' ) );
		}

		extract( $data, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		$template = DEJOIY_ACB_PLUGIN_DIR . 'includes/Admin/views/' . $view . '.php';
		echo '<div class="wrap dejoiy-acb-wrap">';
		echo '<h1>' . esc_html__( 'AI Control Center', 'dejoiy-ai-control-bridge' ) . '</h1>';
		if ( file_exists( $template ) ) {
			include $template;
		} else {
			echo '<p>' . esc_html( sprintf( 'View: %s', $view ) ) . '</p>';
		}
		echo '</div>';
	}

	/**
	 * Handle agent creation form.
	 */
	public function handle_create_agent(): void {
		$this->verify_admin_post( 'dejoiy_acb_create_agent' );

		$name = sanitize_text_field( wp_unslash( $_POST['agent_name'] ?? '' ) );
		$perms = array( sanitize_text_field( wp_unslash( $_POST['permission_set'] ?? 'read_only' ) ) );

		$agent = $this->agents->create( $name, $perms );
		set_transient( 'dejoiy_acb_new_api_key_' . get_current_user_id(), $agent['api_key'] ?? '', 300 );

		wp_safe_redirect( admin_url( 'admin.php?page=dejoiy-acb-agents&created=1' ) );
		exit;
	}

	/**
	 * Handle agent revocation.
	 */
	public function handle_revoke_agent(): void {
		$this->verify_admin_post( 'dejoiy_acb_revoke_agent' );
		$id = (int) ( $_POST['agent_id'] ?? 0 );
		$this->agents->revoke( $id );
		wp_safe_redirect( admin_url( 'admin.php?page=dejoiy-acb-agents&revoked=1' ) );
		exit;
	}

	/**
	 * Handle approval.
	 */
	public function handle_approve(): void {
		$this->verify_admin_post( 'dejoiy_acb_approve' );
		$id = (int) ( $_POST['approval_id'] ?? 0 );
		$this->approvals->approve( $id, get_current_user_id() );
		wp_safe_redirect( admin_url( 'admin.php?page=dejoiy-acb-activity&approved=1' ) );
		exit;
	}

	/**
	 * Handle rejection.
	 */
	public function handle_reject(): void {
		$this->verify_admin_post( 'dejoiy_acb_reject' );
		$id = (int) ( $_POST['approval_id'] ?? 0 );
		$this->approvals->reject( $id, get_current_user_id(), sanitize_text_field( wp_unslash( $_POST['note'] ?? '' ) ) );
		wp_safe_redirect( admin_url( 'admin.php?page=dejoiy-acb-activity&rejected=1' ) );
		exit;
	}

	/**
	 * Save security settings.
	 */
	public function handle_save_security(): void {
		$this->verify_admin_post( 'dejoiy_acb_security' );

		update_option( 'dejoiy_acb_require_approval', ! empty( $_POST['require_approval'] ) );
		update_option( 'dejoiy_acb_ip_allowlist_enabled', ! empty( $_POST['ip_allowlist_enabled'] ) );
		update_option( 'dejoiy_acb_db_write_enabled', ! empty( $_POST['db_write_enabled'] ) );
		update_option( 'dejoiy_acb_git_enabled', ! empty( $_POST['git_enabled'] ) );
		update_option( 'dejoiy_acb_git_repo_path', sanitize_text_field( wp_unslash( $_POST['git_repo_path'] ?? '' ) ) );
		update_option( 'dejoiy_acb_jwt_expiry', max( 300, (int) ( $_POST['jwt_expiry'] ?? 3600 ) ) );

		$ips = sanitize_textarea_field( wp_unslash( $_POST['global_ip_allowlist'] ?? '' ) );
		update_option( 'dejoiy_acb_global_ip_allowlist', array_filter( array_map( 'trim', explode( "\n", $ips ) ) ) );

		wp_safe_redirect( admin_url( 'admin.php?page=dejoiy-acb-security&saved=1' ) );
		exit;
	}

	/**
	 * @param string $action Action name.
	 */
	private function verify_admin_post( string $action ): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'dejoiy-ai-control-bridge' ) );
		}
		check_admin_referer( $action );
	}
}
