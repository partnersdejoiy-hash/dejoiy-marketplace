<?php
/**
 * JWT authentication.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge\Auth;

use Dejoiy\AiControlBridge\Services\AgentService;

/**
 * Issues and validates JWT tokens for AI agents.
 */
class JwtAuth {

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
	 * Issue JWT for agent.
	 *
	 * @param array<string, mixed> $agent Agent record.
	 * @return string JWT token.
	 */
	public function issue_token( array $agent ): string {
		$expiry = (int) get_option( 'dejoiy_acb_jwt_expiry', 3600 );
		$now    = time();

		$payload = array(
			'iss'      => get_site_url(),
			'sub'      => (string) $agent['id'],
			'agent'    => $agent['name'],
			'perms'    => $agent['permissions'],
			'iat'      => $now,
			'exp'      => $now + $expiry,
			'jti'      => wp_generate_password( 16, false ),
		);

		return $this->encode( $payload, $this->get_secret() );
	}

	/**
	 * Validate JWT from Authorization header.
	 *
	 * @return array<string, mixed>|null Decoded payload with agent.
	 */
	public function authenticate(): ?array {
		$token = $this->extract_bearer_token();
		if ( empty( $token ) || 0 === strpos( $token, 'dacb_' ) ) {
			return null;
		}

		$payload = $this->decode( $token, $this->get_secret() );
		if ( ! $payload || empty( $payload['sub'] ) ) {
			return null;
		}

		$agent = $this->agents->get_by_id( (int) $payload['sub'] );
		if ( ! $agent || 'active' !== $agent['status'] ) {
			return null;
		}

		$agent['jwt_payload'] = $payload;
		return $agent;
	}

	/**
	 * @return string|null
	 */
	private function extract_bearer_token(): ?string {
		$auth = isset( $_SERVER['HTTP_AUTHORIZATION'] )
			? sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZATION'] ) )
			: '';

		if ( preg_match( '/^Bearer\s+(.+)$/i', $auth, $matches ) ) {
			return $matches[1];
		}

		return null;
	}

	/**
	 * @return string
	 */
	private function get_secret(): string {
		$secret = get_option( 'dejoiy_acb_jwt_secret', '' );
		if ( empty( $secret ) ) {
			$secret = wp_generate_password( 64, true, true );
			update_option( 'dejoiy_acb_jwt_secret', $secret );
		}
		return $secret;
	}

	/**
	 * @param array<string, mixed> $payload Payload.
	 * @param string               $secret  Secret key.
	 * @return string
	 */
	private function encode( array $payload, string $secret ): string {
		$header  = $this->base64url( wp_json_encode( array( 'typ' => 'JWT', 'alg' => 'HS256' ) ) );
		$body    = $this->base64url( wp_json_encode( $payload ) );
		$sig     = $this->base64url( hash_hmac( 'sha256', "$header.$body", $secret, true ) );
		return "$header.$body.$sig";
	}

	/**
	 * @param string $token  JWT.
	 * @param string $secret Secret.
	 * @return array<string, mixed>|null
	 */
	private function decode( string $token, string $secret ): ?array {
		$parts = explode( '.', $token );
		if ( 3 !== count( $parts ) ) {
			return null;
		}

		list( $header, $body, $sig ) = $parts;
		$expected = $this->base64url( hash_hmac( 'sha256', "$header.$body", $secret, true ) );

		if ( ! hash_equals( $expected, $sig ) ) {
			return null;
		}

		$payload = json_decode( $this->base64url_decode( $body ), true );
		if ( ! is_array( $payload ) ) {
			return null;
		}

		if ( ! empty( $payload['exp'] ) && time() > (int) $payload['exp'] ) {
			return null;
		}

		return $payload;
	}

	/**
	 * @param string $data Data.
	 * @return string
	 */
	private function base64url( string $data ): string {
		return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
	}

	/**
	 * @param string $data Encoded data.
	 * @return string
	 */
	private function base64url_decode( string $data ): string {
		return base64_decode( strtr( $data, '-_', '+/' ) );
	}
}
