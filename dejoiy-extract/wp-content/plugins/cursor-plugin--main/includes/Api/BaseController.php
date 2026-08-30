<?php
/**
 * Base REST API controller.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge\Api;

use Dejoiy\AiControlBridge\Auth\RestAuthMiddleware;
use Dejoiy\AiControlBridge\Security\ActionClassifier;
use Dejoiy\AiControlBridge\Security\ApprovalQueue;
use Dejoiy\AiControlBridge\Security\AuditLogger;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Shared controller functionality.
 */
abstract class BaseController {

	/**
	 * @var AuditLogger
	 */
	protected $audit;

	/**
	 * @var ActionClassifier
	 */
	protected $classifier;

	/**
	 * @var ApprovalQueue
	 */
	protected $approvals;

	/**
	 * @param AuditLogger      $audit      Audit logger.
	 * @param ActionClassifier $classifier Action classifier.
	 * @param ApprovalQueue    $approvals  Approval queue.
	 */
	public function __construct(
		AuditLogger $audit,
		ActionClassifier $classifier,
		ApprovalQueue $approvals
	) {
		$this->audit      = $audit;
		$this->classifier = $classifier;
		$this->approvals  = $approvals;
	}

	/**
	 * @param mixed              $result   Result.
	 * @param WP_REST_Request    $request  Request.
	 * @param string             $action   Action slug.
	 * @return WP_REST_Response|\WP_Error
	 */
	protected function respond( $result, WP_REST_Request $request, string $action = 'general' ) {
		$agent = RestAuthMiddleware::current_agent();
		$status = is_wp_error( $result ) ? $result->get_error_data()['status'] ?? 400 : 200;

		$this->audit->log(
			array(
				'agent_id'        => $agent['id'] ?? null,
				'agent_name'      => $agent['name'] ?? null,
				'action'          => $action,
				'request_method'  => $request->get_method(),
				'request_path'    => $request->get_route(),
				'request_payload' => $request->get_json_params() ?: $request->get_params(),
				'response_status' => $status,
				'result'          => is_wp_error( $result ) ? array( 'error' => $result->get_error_message() ) : array( 'success' => true ),
				'approval_status' => 'not_required',
			)
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return new WP_REST_Response( $result, 200 );
	}

	/**
	 * Handle approval-gated actions.
	 *
	 * @param WP_REST_Request        $request Request.
	 * @param string                 $action  Action slug.
	 * @param callable               $execute Callback to execute.
	 * @return WP_REST_Response|\WP_Error
	 */
	protected function maybe_approve( WP_REST_Request $request, string $action, callable $execute ) {
		$agent = RestAuthMiddleware::current_agent();

		if ( $this->classifier->requires_approval( $action ) && empty( $request->get_param( 'approval_id' ) ) ) {
			$approval_id = $this->approvals->queue(
				(int) $agent['id'],
				$action,
				array(
					'route'  => $request->get_route(),
					'method' => $request->get_method(),
					'params' => $request->get_json_params() ?: $request->get_params(),
				)
			);

			$this->audit->log(
				array(
					'agent_id'        => $agent['id'],
					'agent_name'      => $agent['name'],
					'action'          => $action,
					'request_path'    => $request->get_route(),
					'approval_status' => 'pending',
					'approval_id'     => $approval_id,
					'response_status' => 202,
				)
			);

			return new WP_REST_Response(
				array(
					'approval_required' => true,
					'approval_id'       => $approval_id,
					'message'           => __( 'Action queued for admin approval.', 'dejoiy-ai-control-bridge' ),
				),
				202
			);
		}

		$result = $execute();
		return $this->respond( $result, $request, $action );
	}

	/**
	 * @return array<string, mixed>|null
	 */
	protected function agent(): ?array {
		return RestAuthMiddleware::current_agent();
	}
}
