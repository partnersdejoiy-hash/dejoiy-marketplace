<?php
/**
 * File system access service.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge\Services;

use WP_Error;

/**
 * Secure file operations within allowed WordPress directories.
 */
class FileSystemService {

	/**
	 * Allowed zone identifiers mapped to absolute paths.
	 *
	 * @return array<string, string>
	 */
	public function get_zones(): array {
		$wp_content = WP_CONTENT_DIR;

		return array(
			'wp-content'  => $wp_content,
			'themes'      => $wp_content . '/themes',
			'plugins'     => $wp_content . '/plugins',
			'uploads'     => wp_upload_dir()['basedir'],
			'mu-plugins'  => $wp_content . '/mu-plugins',
		);
	}

	/**
	 * Resolve zone + relative path to absolute path.
	 *
	 * @param string $zone     Zone name.
	 * @param string $relative Relative path.
	 * @return string|WP_Error
	 */
	public function resolve_path( string $zone, string $relative = '' ) {
		$zones = $this->get_zones();
		$allowed = get_option( 'dejoiy_acb_allowed_paths', array_keys( $zones ) );

		if ( ! in_array( $zone, (array) $allowed, true ) || ! isset( $zones[ $zone ] ) ) {
			return new WP_Error( 'invalid_zone', __( 'File zone not allowed.', 'dejoiy-ai-control-bridge' ), array( 'status' => 403 ) );
		}

		$base = realpath( $zones[ $zone ] );
		if ( false === $base ) {
			wp_mkdir_p( $zones[ $zone ] );
			$base = realpath( $zones[ $zone ] );
		}

		$relative = ltrim( str_replace( array( '..', "\0" ), '', $relative ), '/\\' );
		$full     = $base . ( $relative ? DIRECTORY_SEPARATOR . $relative : '' );
		$resolved = realpath( dirname( $full ) );

		// For new files, dirname may not exist yet.
		if ( false === $resolved ) {
			$resolved = $base;
			$full     = $base . ( $relative ? DIRECTORY_SEPARATOR . $relative : '' );
		} else {
			$full = $resolved . DIRECTORY_SEPARATOR . basename( $full );
		}

		if ( 0 !== strpos( $full, $base ) ) {
			return new WP_Error( 'path_traversal', __( 'Path traversal detected.', 'dejoiy-ai-control-bridge' ), array( 'status' => 403 ) );
		}

		return $full;
	}

	/**
	 * Get directory tree.
	 *
	 * @param string $zone     Zone.
	 * @param string $relative Path.
	 * @param int    $depth    Max depth.
	 * @return array<string, mixed>|WP_Error
	 */
	public function tree( string $zone, string $relative = '', int $depth = 3 ) {
		$path = $this->resolve_path( $zone, $relative );
		if ( is_wp_error( $path ) ) {
			return $path;
		}

		if ( ! is_dir( $path ) ) {
			return new WP_Error( 'not_directory', __( 'Path is not a directory.', 'dejoiy-ai-control-bridge' ), array( 'status' => 404 ) );
		}

		return array(
			'zone'  => $zone,
			'path'  => $relative,
			'tree'  => $this->build_tree( $path, $depth ),
		);
	}

	/**
	 * @param string $path  Absolute path.
	 * @param int    $depth Depth.
	 * @return array<int, array<string, mixed>>
	 */
	private function build_tree( string $path, int $depth ): array {
		if ( $depth <= 0 || ! is_readable( $path ) ) {
			return array();
		}

		$items = array();
		$entries = @scandir( $path );
		if ( ! $entries ) {
			return array();
		}

		foreach ( $entries as $entry ) {
			if ( in_array( $entry, array( '.', '..' ), true ) ) {
				continue;
			}

			$full = $path . DIRECTORY_SEPARATOR . $entry;
			$is_dir = is_dir( $full );

			$item = array(
				'name'  => $entry,
				'type'  => $is_dir ? 'directory' : 'file',
				'size'  => $is_dir ? null : (int) @filesize( $full ),
				'mtime' => (int) @filemtime( $full ),
			);

			if ( $is_dir ) {
				$item['children'] = $this->build_tree( $full, $depth - 1 );
			}

			$items[] = $item;
		}

		return $items;
	}

	/**
	 * Read file contents.
	 *
	 * @param string $zone     Zone.
	 * @param string $relative Path.
	 * @return array<string, mixed>|WP_Error
	 */
	public function read( string $zone, string $relative ) {
		$path = $this->resolve_path( $zone, $relative );
		if ( is_wp_error( $path ) ) {
			return $path;
		}

		if ( ! is_file( $path ) || ! is_readable( $path ) ) {
			return new WP_Error( 'not_found', __( 'File not found.', 'dejoiy-ai-control-bridge' ), array( 'status' => 404 ) );
		}

		$max_size = 5 * 1024 * 1024; // 5MB.
		if ( filesize( $path ) > $max_size ) {
			return new WP_Error( 'file_too_large', __( 'File exceeds maximum read size.', 'dejoiy-ai-control-bridge' ), array( 'status' => 413 ) );
		}

		return array(
			'zone'     => $zone,
			'path'     => $relative,
			'content'  => file_get_contents( $path ),
			'size'     => filesize( $path ),
			'mtime'    => filemtime( $path ),
			'encoding' => 'utf-8',
		);
	}

	/**
	 * Write file contents.
	 *
	 * @param string $zone     Zone.
	 * @param string $relative Path.
	 * @param string $content  Content.
	 * @return array<string, mixed>|WP_Error
	 */
	public function write( string $zone, string $relative, string $content ) {
		$path = $this->resolve_path( $zone, $relative );
		if ( is_wp_error( $path ) ) {
			return $path;
		}

		$this->backup_version( $path );

		$dir = dirname( $path );
		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
		}

		$written = file_put_contents( $path, $content );
		if ( false === $written ) {
			return new WP_Error( 'write_failed', __( 'Failed to write file.', 'dejoiy-ai-control-bridge' ), array( 'status' => 500 ) );
		}

		return array(
			'zone'    => $zone,
			'path'    => $relative,
			'size'    => $written,
			'success' => true,
		);
	}

	/**
	 * Create file or directory.
	 *
	 * @param string $zone     Zone.
	 * @param string $relative Path.
	 * @param string $type     file|directory.
	 * @param string $content  Content for files.
	 * @return array<string, mixed>|WP_Error
	 */
	public function create( string $zone, string $relative, string $type = 'file', string $content = '' ) {
		$path = $this->resolve_path( $zone, $relative );
		if ( is_wp_error( $path ) ) {
			return $path;
		}

		if ( file_exists( $path ) ) {
			return new WP_Error( 'exists', __( 'Path already exists.', 'dejoiy-ai-control-bridge' ), array( 'status' => 409 ) );
		}

		if ( 'directory' === $type ) {
			wp_mkdir_p( $path );
		} else {
			$dir = dirname( $path );
			wp_mkdir_p( $dir );
			file_put_contents( $path, $content );
		}

		return array( 'zone' => $zone, 'path' => $relative, 'type' => $type, 'success' => true );
	}

	/**
	 * Delete file or directory.
	 *
	 * @param string $zone     Zone.
	 * @param string $relative Path.
	 * @return array<string, mixed>|WP_Error
	 */
	public function delete( string $zone, string $relative ) {
		$path = $this->resolve_path( $zone, $relative );
		if ( is_wp_error( $path ) ) {
			return $path;
		}

		if ( ! file_exists( $path ) ) {
			return new WP_Error( 'not_found', __( 'Path not found.', 'dejoiy-ai-control-bridge' ), array( 'status' => 404 ) );
		}

		$this->backup_version( $path );

		if ( is_dir( $path ) ) {
			$this->recursive_delete( $path );
		} else {
			wp_delete_file( $path );
		}

		return array( 'zone' => $zone, 'path' => $relative, 'deleted' => true );
	}

	/**
	 * Rename file or directory.
	 *
	 * @param string $zone Zone.
	 * @param string $from Source path.
	 * @param string $to   Destination path.
	 * @return array<string, mixed>|WP_Error
	 */
	public function rename( string $zone, string $from, string $to ) {
		$from_path = $this->resolve_path( $zone, $from );
		$to_path   = $this->resolve_path( $zone, $to );

		if ( is_wp_error( $from_path ) ) {
			return $from_path;
		}
		if ( is_wp_error( $to_path ) ) {
			return $to_path;
		}

		if ( ! file_exists( $from_path ) ) {
			return new WP_Error( 'not_found', __( 'Source not found.', 'dejoiy-ai-control-bridge' ), array( 'status' => 404 ) );
		}

		wp_mkdir_p( dirname( $to_path ) );
		if ( ! rename( $from_path, $to_path ) ) {
			return new WP_Error( 'rename_failed', __( 'Rename failed.', 'dejoiy-ai-control-bridge' ), array( 'status' => 500 ) );
		}

		return array( 'zone' => $zone, 'from' => $from, 'to' => $to, 'success' => true );
	}

	/**
	 * Search files by pattern.
	 *
	 * @param string $zone    Zone.
	 * @param string $pattern Glob pattern.
	 * @param int    $limit   Max results.
	 * @return array<int, array<string, mixed>>
	 */
	public function search( string $zone, string $pattern, int $limit = 100 ): array {
		$zones = $this->get_zones();
		if ( ! isset( $zones[ $zone ] ) ) {
			return array();
		}

		$base = $zones[ $zone ];
		$files = array();
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $base, \RecursiveDirectoryIterator::SKIP_DOTS )
		);

		$count = 0;
		foreach ( $iterator as $file ) {
			if ( $count >= $limit ) {
				break;
			}
			/** @var \SplFileInfo $file */
			$name = $file->getFilename();
			if ( fnmatch( $pattern, $name ) || fnmatch( $pattern, $file->getPathname() ) ) {
				$rel = ltrim( str_replace( $base, '', $file->getPathname() ), '/\\' );
				$files[] = array(
					'path' => $rel,
					'type' => $file->isDir() ? 'directory' : 'file',
					'size' => $file->isFile() ? $file->getSize() : null,
				);
				++$count;
			}
		}

		return $files;
	}

	/**
	 * Store file version for rollback.
	 *
	 * @param string $path Absolute path.
	 */
	private function backup_version( string $path ): void {
		if ( ! file_exists( $path ) || ! is_file( $path ) ) {
			return;
		}

		global $wpdb;
		$versions_dir = WP_CONTENT_DIR . '/dejoiy-acb-versions';
		wp_mkdir_p( $versions_dir );

		$hash   = md5( $path . microtime( true ) );
		$backup = $versions_dir . '/' . $hash . '.bak';
		copy( $path, $backup );

		$table = $wpdb->prefix . 'dejoiy_acb_file_versions';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			return;
		}

		$wpdb->insert(
			$table,
			array(
				'file_path'    => $path,
				'content_hash' => md5_file( $path ),
				'backup_path'  => $backup,
				'version'      => (int) $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COALESCE(MAX(version), 0) + 1 FROM {$wpdb->prefix}dejoiy_acb_file_versions WHERE file_path = %s",
						$path
					)
				),
			),
			array( '%s', '%s', '%s', '%d' )
		);
	}

	/**
	 * @param string $dir Directory.
	 */
	private function recursive_delete( string $dir ): void {
		$items = scandir( $dir );
		if ( ! $items ) {
			return;
		}
		foreach ( $items as $item ) {
			if ( in_array( $item, array( '.', '..' ), true ) ) {
				continue;
			}
			$path = $dir . DIRECTORY_SEPARATOR . $item;
			if ( is_dir( $path ) ) {
				$this->recursive_delete( $path );
			} else {
				wp_delete_file( $path );
			}
		}
		rmdir( $dir );
	}
}
