<?php
/**
 * PSR-4 style autoloader for Dejoiy\AiControlBridge namespace.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

spl_autoload_register(
	static function ( string $class ): void {
		$prefix = 'Dejoiy\\AiControlBridge\\';
		if ( strpos( $class, $prefix ) !== 0 ) {
			return;
		}

		$relative = substr( $class, strlen( $prefix ) );
		$relative = str_replace( '\\', '/', $relative );

		$paths = array(
			DEJOIY_ACB_PLUGIN_DIR . 'includes/' . $relative . '.php',
			DEJOIY_ACB_PLUGIN_DIR . 'includes/' . strtolower( preg_replace( '/([a-z])([A-Z])/', '$1-$2', $relative ) ) . '.php',
		);

		// Map nested namespaces to directory structure.
		$file = DEJOIY_ACB_PLUGIN_DIR . 'includes/' . $relative . '.php';
		if ( file_exists( $file ) ) {
			require_once $file;
			return;
		}

		// Convert Api\FilesController -> Api/FilesController.php
		$segments = explode( '/', $relative );
		$last     = array_pop( $segments );
		$dir      = implode( '/', $segments );
		$candidate = DEJOIY_ACB_PLUGIN_DIR . 'includes/' . ( $dir ? $dir . '/' : '' ) . $last . '.php';
		if ( file_exists( $candidate ) ) {
			require_once $candidate;
		}
	}
);
