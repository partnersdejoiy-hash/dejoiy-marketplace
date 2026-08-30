<?php
/**
 * Deployment engine with Git integration.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge\Services;

use WP_Error;

/**
 * Git-based deployments with version history and rollback.
 */
class DeploymentService {

	/**
	 * @return string
	 */
	private function table(): string {
		global $wpdb;
		return $wpdb->prefix . 'dejoiy_acb_deployments';
	}

	/**
	 * Get deployment history.
	 *
	 * @param int $limit Limit.
	 * @return array<int, array<string, mixed>>
	 */
	public function get_history( int $limit = 50 ): array {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$this->table()} ORDER BY created_at DESC LIMIT %d", $limit ),
			ARRAY_A
		) ?: array();
	}

	/**
	 * Create deployment record and execute if Git enabled.
	 *
	 * @param array<string, mixed> $args Deployment args.
	 * @param int|null             $agent_id Agent ID.
	 * @return array<string, mixed>|WP_Error
	 */
	public function deploy( array $args, ?int $agent_id = null ) {
		global $wpdb;

		$version = $args['version'] ?? gmdate( 'Y-m-d-His' );
		$branch  = $args['branch'] ?? 'main';
		$message = $args['message'] ?? 'AI deployment';

		$wpdb->insert(
			$this->table(),
			array(
				'agent_id' => $agent_id,
				'version'  => sanitize_text_field( $version ),
				'branch'   => sanitize_text_field( $branch ),
				'status'   => 'running',
				'log'      => '',
			),
			array( '%d', '%s', '%s', '%s', '%s' )
		);

		$deployment_id = (int) $wpdb->insert_id;
		$log           = array();

		if ( get_option( 'dejoiy_acb_git_enabled', false ) ) {
			$result = $this->git_deploy( $branch, $message, $log );
			if ( is_wp_error( $result ) ) {
				$this->update_status( $deployment_id, 'failed', implode( "\n", $log ) );
				return $result;
			}

			$commit = $result['commit'] ?? '';
			$wpdb->update(
				$this->table(),
				array( 'commit_hash' => $commit ),
				array( 'id' => $deployment_id ),
				array( '%s' ),
				array( '%d' )
			);
		} else {
			$log[] = 'Git integration disabled. Deployment recorded only.';
		}

		$this->update_status( $deployment_id, 'completed', implode( "\n", $log ) );

		return array(
			'id'      => $deployment_id,
			'version' => $version,
			'status'  => 'completed',
			'log'     => $log,
		);
	}

	/**
	 * Rollback to previous deployment.
	 *
	 * @param int $deployment_id Deployment to rollback to.
	 * @return array<string, mixed>|WP_Error
	 */
	public function rollback( int $deployment_id ) {
		global $wpdb;

		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$this->table()} WHERE id = %d", $deployment_id ),
			ARRAY_A
		);

		if ( ! $row ) {
			return new WP_Error( 'not_found', __( 'Deployment not found.', 'dejoiy-ai-control-bridge' ), array( 'status' => 404 ) );
		}

		if ( ! get_option( 'dejoiy_acb_git_enabled', false ) || empty( $row['commit_hash'] ) ) {
			return new WP_Error( 'rollback_unavailable', __( 'Git rollback not available.', 'dejoiy-ai-control-bridge' ), array( 'status' => 400 ) );
		}

		$log = array();
		$repo = get_option( 'dejoiy_acb_git_repo_path', ABSPATH );
		$cmd  = sprintf( 'cd %s && git checkout %s 2>&1', escapeshellarg( $repo ), escapeshellarg( $row['commit_hash'] ) );
		exec( $cmd, $output, $code ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_exec

		$log[] = implode( "\n", $output );

		if ( 0 !== $code ) {
			return new WP_Error( 'rollback_failed', __( 'Git rollback failed.', 'dejoiy-ai-control-bridge' ), array( 'status' => 500 ) );
		}

		return $this->deploy(
			array(
				'version' => 'rollback-' . $deployment_id,
				'message' => 'Rollback to deployment #' . $deployment_id,
			),
			$row['agent_id'] ? (int) $row['agent_id'] : null
		);
	}

	/**
	 * Get Git status.
	 *
	 * @return array<string, mixed>
	 */
	public function git_status(): array {
		if ( ! get_option( 'dejoiy_acb_git_enabled', false ) ) {
			return array( 'enabled' => false );
		}

		$repo = get_option( 'dejoiy_acb_git_repo_path', ABSPATH );
		$branch = trim( shell_exec( 'cd ' . escapeshellarg( $repo ) . ' && git rev-parse --abbrev-ref HEAD 2>/dev/null' ) ?: '' );
		$commit = trim( shell_exec( 'cd ' . escapeshellarg( $repo ) . ' && git rev-parse HEAD 2>/dev/null' ) ?: '' );

		return array(
			'enabled' => true,
			'repo'    => $repo,
			'branch'  => $branch,
			'commit'  => $commit,
		);
	}

	/**
	 * @param string $branch  Branch.
	 * @param string $message Message.
	 * @param array<int, string> $log Log lines.
	 * @return array<string, mixed>|WP_Error
	 */
	private function git_deploy( string $branch, string $message, array &$log ) {
		$repo = get_option( 'dejoiy_acb_git_repo_path', ABSPATH );

		$commands = array(
			'git fetch origin',
			"git checkout {$branch}",
			'git pull origin ' . $branch,
		);

		foreach ( $commands as $cmd ) {
			$full = 'cd ' . escapeshellarg( $repo ) . ' && ' . $cmd . ' 2>&1';
			exec( $full, $output, $code ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_exec
			$log[] = implode( "\n", $output );
			if ( 0 !== $code ) {
				return new WP_Error( 'git_failed', __( 'Git command failed: ', 'dejoiy-ai-control-bridge' ) . $cmd, array( 'status' => 500 ) );
			}
		}

		$commit = trim( shell_exec( 'cd ' . escapeshellarg( $repo ) . ' && git rev-parse HEAD 2>/dev/null' ) ?: '' );

		return array( 'commit' => $commit );
	}

	/**
	 * @param int    $id     ID.
	 * @param string $status Status.
	 * @param string $log    Log.
	 */
	private function update_status( int $id, string $status, string $log ): void {
		global $wpdb;
		$wpdb->update(
			$this->table(),
			array(
				'status'       => $status,
				'log'          => $log,
				'completed_at' => current_time( 'mysql', true ),
			),
			array( 'id' => $id ),
			array( '%s', '%s', '%s' ),
			array( '%d' )
		);
	}
}
