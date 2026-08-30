<?php
/**
 * Cron handler for approval queue maintenance.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge\Cron;

use Dejoiy\AiControlBridge\Security\ApprovalQueue;

/**
 * Processes expired approval requests.
 */
class ApprovalProcessor {

	/**
	 * Expire old pending approvals.
	 */
	public static function process_expired(): void {
		$queue = new ApprovalQueue( new \Dejoiy\AiControlBridge\Security\AuditLogger() );
		$queue->expire_old();
	}
}
