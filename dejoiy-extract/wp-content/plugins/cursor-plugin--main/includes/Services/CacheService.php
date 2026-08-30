<?php
/**
 * Cache clearing service.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge\Services;

/**
 * Clears WordPress and popular caching plugin caches.
 */
class CacheService {

	/**
	 * Clear all detectable caches.
	 *
	 * @return array<string, mixed>
	 */
	public function clear_all(): array {
		$cleared = array();

		wp_cache_flush();
		$cleared[] = 'object_cache';

		if ( function_exists( 'wp_cache_clear_cache' ) ) {
			wp_cache_clear_cache();
			$cleared[] = 'wp_super_cache';
		}

		if ( function_exists( 'w3tc_flush_all' ) ) {
			w3tc_flush_all();
			$cleared[] = 'w3_total_cache';
		}

		if ( function_exists( 'rocket_clean_domain' ) ) {
			rocket_clean_domain();
			$cleared[] = 'wp_rocket';
		}

		if ( function_exists( 'litespeed_purge_all' ) ) {
			do_action( 'litespeed_purge_all' );
			$cleared[] = 'litespeed';
		}

		if ( class_exists( '\Elementor\Plugin' ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
			$cleared[] = 'elementor';
		}

		flush_rewrite_rules( false );
		$cleared[] = 'rewrite_rules';

		return array(
			'cleared' => $cleared,
			'success' => true,
		);
	}
}
