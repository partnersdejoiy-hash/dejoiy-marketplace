<?php
/**
 * IP allowlist enforcement.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge\Security;

/**
 * Validates client IP against global and per-agent allowlists.
 */
class IpAllowlist {

	/**
	 * @param array<string, mixed> $agent Agent record.
	 * @return bool
	 */
	public function is_allowed( array $agent ): bool {
		$global_enabled = (bool) get_option( 'dejoiy_acb_ip_allowlist_enabled', false );
		$client_ip      = $this->client_ip();

		$lists = array();

		if ( $global_enabled ) {
			$global = get_option( 'dejoiy_acb_global_ip_allowlist', array() );
			if ( is_array( $global ) && ! empty( $global ) ) {
				$lists[] = $global;
			}
		}

		$agent_list = $agent['ip_allowlist'] ?? null;
		if ( is_string( $agent_list ) ) {
			$agent_list = json_decode( $agent_list, true );
		}
		if ( is_array( $agent_list ) && ! empty( $agent_list ) ) {
			$lists[] = $agent_list;
		}

		if ( empty( $lists ) ) {
			return true;
		}

		foreach ( $lists as $list ) {
			if ( $this->ip_in_list( $client_ip, $list ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param string              $ip   Client IP.
	 * @param array<int, string>  $list Allowlist.
	 * @return bool
	 */
	private function ip_in_list( string $ip, array $list ): bool {
		foreach ( $list as $entry ) {
			$entry = trim( $entry );
			if ( empty( $entry ) ) {
				continue;
			}
			if ( $ip === $entry ) {
				return true;
			}
			// CIDR support (basic).
			if ( false !== strpos( $entry, '/' ) && $this->ip_in_cidr( $ip, $entry ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param string $ip   IP address.
	 * @param string $cidr CIDR notation.
	 * @return bool
	 */
	private function ip_in_cidr( string $ip, string $cidr ): bool {
		list( $subnet, $mask ) = explode( '/', $cidr, 2 );
		$mask = (int) $mask;

		$ip_long     = ip2long( $ip );
		$subnet_long = ip2long( $subnet );

		if ( false === $ip_long || false === $subnet_long ) {
			return false;
		}

		$wildcard = pow( 2, ( 32 - $mask ) ) - 1;
		$netmask  = ~ $wildcard;

		return ( $ip_long & $netmask ) === ( $subnet_long & $netmask );
	}

	/**
	 * @return string
	 */
	private function client_ip(): string {
		return isset( $_SERVER['REMOTE_ADDR'] )
			? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) )
			: '0.0.0.0';
	}
}
