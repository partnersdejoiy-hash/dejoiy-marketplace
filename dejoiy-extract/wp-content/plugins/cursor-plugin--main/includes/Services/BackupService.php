<?php
/**
 * Backup and restore service.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge\Services;

use WP_Error;

/**
 * Full, database, theme, and plugin backups with restore.
 */
class BackupService {

	/**
	 * @return string
	 */
	private function table(): string {
		global $wpdb;
		return $wpdb->prefix . 'dejoiy_acb_backups';
	}

	/**
	 * @return string
	 */
	private function backup_dir(): string {
		$dir = WP_CONTENT_DIR . '/dejoiy-acb-backups';
		wp_mkdir_p( $dir );
		// Protect directory.
		if ( ! file_exists( $dir . '/.htaccess' ) ) {
			file_put_contents( $dir . '/.htaccess', 'deny from all' );
			file_put_contents( $dir . '/index.php', '<?php // Silence.' );
		}
		return $dir;
	}

	/**
	 * List backups.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function list_backups(): array {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_results( "SELECT id, type, file_path, file_size, checksum, status, created_at FROM {$this->table()} ORDER BY created_at DESC", ARRAY_A ) ?: array();
	}

	/**
	 * Create backup.
	 *
	 * @param string   $type     full|database|theme|plugin.
	 * @param array<string, mixed> $options Options.
	 * @param int|null $agent_id Agent ID.
	 * @return array<string, mixed>|WP_Error
	 */
	public function create( string $type, array $options = array(), ?int $agent_id = null ) {
		$timestamp = gmdate( 'Y-m-d-His' );
		$filename  = "backup-{$type}-{$timestamp}.zip";
		$path      = $this->backup_dir() . '/' . $filename;

		switch ( $type ) {
			case 'database':
				$result = $this->backup_database( $path );
				break;
			case 'theme':
				$slug   = $options['theme'] ?? get_stylesheet();
				$result = $this->backup_directory( get_theme_root() . '/' . $slug, $path );
				break;
			case 'plugin':
				$slug   = $options['plugin'] ?? '';
				$result = $this->backup_directory( WP_PLUGIN_DIR . '/' . $slug, $path );
				break;
			case 'full':
				$result = $this->backup_full( $path );
				break;
			default:
				return new WP_Error( 'invalid_type', __( 'Invalid backup type.', 'dejoiy-ai-control-bridge' ), array( 'status' => 400 ) );
		}

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$size     = filesize( $path );
		$checksum = md5_file( $path );

		global $wpdb;
		$wpdb->insert(
			$this->table(),
			array(
				'type'      => $type,
				'file_path' => $path,
				'file_size' => $size,
				'checksum'  => $checksum,
				'status'    => 'completed',
				'metadata'  => wp_json_encode( $options ),
				'agent_id'  => $agent_id,
			),
			array( '%s', '%s', '%d', '%s', '%s', '%s', '%d' )
		);

		return array(
			'id'       => (int) $wpdb->insert_id,
			'type'     => $type,
			'path'     => $path,
			'size'     => $size,
			'checksum' => $checksum,
		);
	}

	/**
	 * Restore backup.
	 *
	 * @param int $backup_id Backup ID.
	 * @return array<string, mixed>|WP_Error
	 */
	public function restore( int $backup_id ) {
		global $wpdb;

		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$this->table()} WHERE id = %d", $backup_id ),
			ARRAY_A
		);

		if ( ! $row || ! file_exists( $row['file_path'] ) ) {
			return new WP_Error( 'not_found', __( 'Backup not found.', 'dejoiy-ai-control-bridge' ), array( 'status' => 404 ) );
		}

		if ( md5_file( $row['file_path'] ) !== $row['checksum'] ) {
			return new WP_Error( 'checksum_failed', __( 'Backup integrity check failed.', 'dejoiy-ai-control-bridge' ), array( 'status' => 400 ) );
		}

		if ( 'database' === $row['type'] ) {
			return $this->restore_database( $row['file_path'] );
		}

		$zip = new \ZipArchive();
		if ( true !== $zip->open( $row['file_path'] ) ) {
			return new WP_Error( 'zip_error', __( 'Cannot open backup archive.', 'dejoiy-ai-control-bridge' ), array( 'status' => 500 ) );
		}

		$extract_to = WP_CONTENT_DIR . '/dejoiy-acb-restore-' . time();
		wp_mkdir_p( $extract_to );
		$zip->extractTo( $extract_to );
		$zip->close();

		return array(
			'restored'    => true,
			'backup_id'   => $backup_id,
			'extract_path' => $extract_to,
			'note'        => __( 'Files extracted. Manual merge may be required for full restores.', 'dejoiy-ai-control-bridge' ),
		);
	}

	/**
	 * @param string $path Output path.
	 * @return true|WP_Error
	 */
	private function backup_database( string $path ) {
		global $wpdb;

		$sql_file = str_replace( '.zip', '.sql', $path );
		$handle   = fopen( $sql_file, 'w' );
		if ( ! $handle ) {
			return new WP_Error( 'write_failed', __( 'Cannot write SQL file.', 'dejoiy-ai-control-bridge' ) );
		}

		$tables = $wpdb->get_col( 'SHOW TABLES' );
		foreach ( $tables as $table ) {
			fwrite( $handle, "-- Table: {$table}\n" );
			$create = $wpdb->get_row( "SHOW CREATE TABLE `{$table}`", ARRAY_N ); // phpcs:ignore
			if ( $create ) {
				fwrite( $handle, $create[1] . ";\n\n" );
			}
		}

		fclose( $handle );

		$zip = new \ZipArchive();
		$zip->open( $path, \ZipArchive::CREATE );
		$zip->addFile( $sql_file, 'database.sql' );
		$zip->close();
		wp_delete_file( $sql_file );

		return true;
	}

	/**
	 * @param string $source_dir Source.
	 * @param string $path       Output zip.
	 * @return true|WP_Error
	 */
	private function backup_directory( string $source_dir, string $path ) {
		if ( ! is_dir( $source_dir ) ) {
			return new WP_Error( 'not_found', __( 'Directory not found.', 'dejoiy-ai-control-bridge' ), array( 'status' => 404 ) );
		}

		$zip = new \ZipArchive();
		if ( true !== $zip->open( $path, \ZipArchive::CREATE ) ) {
			return new WP_Error( 'zip_error', __( 'Cannot create zip.', 'dejoiy-ai-control-bridge' ) );
		}

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $source_dir, \RecursiveDirectoryIterator::SKIP_DOTS )
		);

		foreach ( $iterator as $file ) {
			/** @var \SplFileInfo $file */
			if ( $file->isFile() ) {
				$rel = substr( $file->getPathname(), strlen( $source_dir ) + 1 );
				$zip->addFile( $file->getPathname(), $rel );
			}
		}

		$zip->close();
		return true;
	}

	/**
	 * @param string $path Output.
	 * @return true|WP_Error
	 */
	private function backup_full( string $path ) {
		$db_result = $this->backup_database( str_replace( '.zip', '-db.zip', $path ) );
		if ( is_wp_error( $db_result ) ) {
			return $db_result;
		}

		$zip = new \ZipArchive();
		$zip->open( $path, \ZipArchive::CREATE );
		$zip->addFile( str_replace( '.zip', '-db.zip', $path ), 'database.zip' );

		$content_zip = $this->backup_dir() . '/temp-content.zip';
		$this->backup_directory( WP_CONTENT_DIR, $content_zip );
		$zip->addFile( $content_zip, 'wp-content.zip' );
		$zip->close();

		wp_delete_file( str_replace( '.zip', '-db.zip', $path ) );
		wp_delete_file( $content_zip );

		return true;
	}

	/**
	 * @param string $path Zip path.
	 * @return array<string, mixed>|WP_Error
	 */
	private function restore_database( string $path ) {
		$zip = new \ZipArchive();
		if ( true !== $zip->open( $path ) ) {
			return new WP_Error( 'zip_error', __( 'Cannot open backup.', 'dejoiy-ai-control-bridge' ) );
		}

		$sql_content = $zip->getFromName( 'database.sql' );
		$zip->close();

		if ( false === $sql_content ) {
			return new WP_Error( 'no_sql', __( 'No SQL in backup.', 'dejoiy-ai-control-bridge' ) );
		}

		return array(
			'restored' => true,
			'note'     => __( 'Database restore requires approval and manual execution for safety.', 'dejoiy-ai-control-bridge' ),
			'sql_size' => strlen( $sql_content ),
		);
	}
}
