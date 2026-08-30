<?php
/**
 * Uninstall DEJOIY AI Control Bridge.
 *
 * @package Dejoiy\AiControlBridge
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$tables = array(
	$wpdb->prefix . 'dejoiy_acb_agents',
	$wpdb->prefix . 'dejoiy_acb_audit_logs',
	$wpdb->prefix . 'dejoiy_acb_approvals',
	$wpdb->prefix . 'dejoiy_acb_deployments',
	$wpdb->prefix . 'dejoiy_acb_backups',
	$wpdb->prefix . 'dejoiy_acb_file_versions',
);

foreach ( $tables as $table ) {
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
}

$options = array(
	'dejoiy_acb_db_version',
	'dejoiy_acb_jwt_secret',
	'dejoiy_acb_jwt_expiry',
	'dejoiy_acb_require_approval',
	'dejoiy_acb_ip_allowlist_enabled',
	'dejoiy_acb_global_ip_allowlist',
	'dejoiy_acb_db_write_enabled',
	'dejoiy_acb_rate_limit',
	'dejoiy_acb_allowed_paths',
	'dejoiy_acb_git_enabled',
	'dejoiy_acb_git_repo_path',
	'dejoiy_acb_backup_retention_days',
	'dejoiy_acb_migrations_ran',
);

foreach ( $options as $option ) {
	delete_option( $option );
}

wp_clear_scheduled_hook( 'dejoiy_acb_process_approvals' );
