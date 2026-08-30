<?php
/**
 * Plugin activation handler.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge;

/**
 * Creates database tables and default options on activation.
 */
class Activator {

	/**
	 * Activate plugin.
	 */
	public static function activate(): void {
		self::create_tables();
		self::set_default_options();
		flush_rewrite_rules();
	}

	/**
	 * Create custom database tables.
	 */
	private static function create_tables(): void {
		global $wpdb;

		$charset = $wpdb->get_charset_collate();
		$prefix  = $wpdb->prefix . 'dejoiy_acb_';

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$sql_agents = "CREATE TABLE {$prefix}agents (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			name varchar(191) NOT NULL,
			api_key_hash varchar(255) NOT NULL,
			api_key_prefix varchar(16) NOT NULL,
			jwt_secret varchar(255) DEFAULT NULL,
			permissions longtext NOT NULL,
			ip_allowlist longtext DEFAULT NULL,
			status varchar(20) NOT NULL DEFAULT 'active',
			last_seen datetime DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY api_key_prefix (api_key_prefix),
			KEY status (status)
		) $charset;";

		$sql_audit = "CREATE TABLE {$prefix}audit_logs (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			agent_id bigint(20) unsigned DEFAULT NULL,
			agent_name varchar(191) DEFAULT NULL,
			action varchar(100) NOT NULL,
			resource_type varchar(50) DEFAULT NULL,
			resource_id varchar(191) DEFAULT NULL,
			request_method varchar(10) DEFAULT NULL,
			request_path varchar(500) DEFAULT NULL,
			request_payload longtext DEFAULT NULL,
			response_status int(11) DEFAULT NULL,
			result longtext DEFAULT NULL,
			ip_address varchar(45) DEFAULT NULL,
			approval_status varchar(20) DEFAULT 'not_required',
			approval_id bigint(20) unsigned DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY agent_id (agent_id),
			KEY action (action),
			KEY created_at (created_at),
			KEY approval_status (approval_status)
		) $charset;";

		$sql_approvals = "CREATE TABLE {$prefix}approvals (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			agent_id bigint(20) unsigned NOT NULL,
			action varchar(100) NOT NULL,
			payload longtext NOT NULL,
			status varchar(20) NOT NULL DEFAULT 'pending',
			requested_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			resolved_at datetime DEFAULT NULL,
			resolved_by bigint(20) unsigned DEFAULT NULL,
			resolution_note text DEFAULT NULL,
			expires_at datetime DEFAULT NULL,
			PRIMARY KEY (id),
			KEY status (status),
			KEY agent_id (agent_id),
			KEY expires_at (expires_at)
		) $charset;";

		$sql_deployments = "CREATE TABLE {$prefix}deployments (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			agent_id bigint(20) unsigned DEFAULT NULL,
			version varchar(50) NOT NULL,
			commit_hash varchar(64) DEFAULT NULL,
			branch varchar(191) DEFAULT NULL,
			status varchar(20) NOT NULL DEFAULT 'pending',
			log longtext DEFAULT NULL,
			rollback_of bigint(20) unsigned DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			completed_at datetime DEFAULT NULL,
			PRIMARY KEY (id),
			KEY status (status),
			KEY created_at (created_at)
		) $charset;";

		$sql_backups = "CREATE TABLE {$prefix}backups (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			type varchar(30) NOT NULL,
			file_path varchar(500) NOT NULL,
			file_size bigint(20) unsigned DEFAULT 0,
			checksum varchar(64) DEFAULT NULL,
			status varchar(20) NOT NULL DEFAULT 'completed',
			metadata longtext DEFAULT NULL,
			created_by bigint(20) unsigned DEFAULT NULL,
			agent_id bigint(20) unsigned DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY type (type),
			KEY created_at (created_at)
		) $charset;";

		$sql_versions = "CREATE TABLE {$prefix}file_versions (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			file_path varchar(500) NOT NULL,
			version int(11) NOT NULL DEFAULT 1,
			content_hash varchar(64) NOT NULL,
			backup_path varchar(500) NOT NULL,
			agent_id bigint(20) unsigned DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY file_path (file_path(191)),
			KEY created_at (created_at)
		) $charset;";

		dbDelta( $sql_agents );
		dbDelta( $sql_audit );
		dbDelta( $sql_approvals );
		dbDelta( $sql_deployments );
		dbDelta( $sql_backups );
		dbDelta( $sql_versions );

		update_option( 'dejoiy_acb_db_version', DEJOIY_ACB_VERSION );
	}

	/**
	 * Set default plugin options.
	 */
	private static function set_default_options(): void {
		$defaults = array(
			'dejoiy_acb_jwt_secret'           => wp_generate_password( 64, true, true ),
			'dejoiy_acb_jwt_expiry'             => 3600,
			'dejoiy_acb_require_approval'       => true,
			'dejoiy_acb_ip_allowlist_enabled'   => false,
			'dejoiy_acb_global_ip_allowlist'    => array(),
			'dejoiy_acb_db_write_enabled'       => false,
			'dejoiy_acb_rate_limit'             => 120,
			'dejoiy_acb_allowed_paths'          => array( 'wp-content', 'themes', 'plugins', 'uploads', 'mu-plugins' ),
			'dejoiy_acb_git_enabled'            => false,
			'dejoiy_acb_git_repo_path'          => '',
			'dejoiy_acb_backup_retention_days'  => 30,
		);

		foreach ( $defaults as $key => $value ) {
			if ( false === get_option( $key ) ) {
				add_option( $key, $value );
			}
		}
	}
}
