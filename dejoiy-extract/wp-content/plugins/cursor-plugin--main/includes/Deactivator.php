<?php
/**
 * Plugin deactivation handler.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge;

/**
 * Cleanup on deactivation (non-destructive).
 */
class Deactivator {

	/**
	 * Deactivate plugin.
	 */
	public static function deactivate(): void {
		wp_clear_scheduled_hook( 'dejoiy_acb_process_approvals' );
		flush_rewrite_rules();
	}
}
