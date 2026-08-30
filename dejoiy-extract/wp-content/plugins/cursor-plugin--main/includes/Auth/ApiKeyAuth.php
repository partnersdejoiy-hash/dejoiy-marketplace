<?php
/**
 * API Key authentication.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge\Auth;

use Dejoiy\AiControlBridge\Services\AgentService;

/**
 * Validates API keys from X-Dejoiy-API-Key header.
 */
class ApiKeyAuth {

	/**
	 * @var AgentService
	 */
	private $agents;

	/**
	 * @param AgentService $agents Agent service.
	 */
	public function __construct( AgentService $agents ) {
		$this->agents = $agents;
	}

	/**
	 * Authenticate request by API key.
	 *
	 * @return array<string, mixed>|null Agent data or null.
	 */
	public function authenticate(): ?array {
		$key = $this->extract_api_key();
		if ( empty( $key ) ) {
			return null;
		}

		return $this->agents->validate_api_key( $key );
	}

	/**
	 * Extract API key from headers.
	 *
	 * @return string|null
	 */
	private function extract_api_key(): ?string {
		if ( isset( $_SERVER['HTTP_X_DEJOIY_API_KEY'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_DEJOIY_API_KEY'] ) );
		}

		$auth = isset( $_SERVER['HTTP_AUTHORIZATION'] )
			? sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZATION'] ) )
			: '';

		if ( preg_match( '/^Bearer\s+(.+)$/i', $auth, $matches ) ) {
			$token = $matches[1];
			// API keys start with dacb_ prefix.
			if ( 0 === strpos( $token, 'dacb_' ) ) {
				return $token;
			}
		}

		return null;
	}
}
