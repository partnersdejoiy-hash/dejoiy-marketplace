<?php
/**
 * WordPress content and settings management.
 *
 * @package Dejoiy\AiControlBridge
 */

declare(strict_types=1);

namespace Dejoiy\AiControlBridge\Services;

use WP_Error;
use WP_Post;
use WP_User;

/**
 * Manage posts, pages, products, menus, users, and options.
 */
class WordPressService {

	/**
	 * Create a post or page.
	 *
	 * @param array<string, mixed> $args Post args.
	 * @return array<string, mixed>|WP_Error
	 */
	public function create_post( array $args ) {
		$defaults = array(
			'post_title'   => '',
			'post_content' => '',
			'post_status'  => 'draft',
			'post_type'    => 'post',
			'post_author'  => get_current_user_id() ?: 1,
		);

		$data = wp_parse_args( $args, $defaults );
		$id   = wp_insert_post( $data, true );

		if ( is_wp_error( $id ) ) {
			return $id;
		}

		return $this->format_post( get_post( $id ) );
	}

	/**
	 * Create WooCommerce product if available.
	 *
	 * @param array<string, mixed> $args Product args.
	 * @return array<string, mixed>|WP_Error
	 */
	public function create_product( array $args ) {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return new WP_Error( 'woocommerce_missing', __( 'WooCommerce is not active.', 'dejoiy-ai-control-bridge' ), array( 'status' => 400 ) );
		}

		$product = new \WC_Product_Simple();
		$product->set_name( $args['name'] ?? 'New Product' );
		$product->set_status( $args['status'] ?? 'draft' );

		if ( isset( $args['regular_price'] ) ) {
			$product->set_regular_price( (string) $args['regular_price'] );
		}
		if ( isset( $args['description'] ) ) {
			$product->set_description( $args['description'] );
		}
		if ( isset( $args['short_description'] ) ) {
			$product->set_short_description( $args['short_description'] );
		}

		$id = $product->save();

		return array(
			'id'     => $id,
			'name'   => $product->get_name(),
			'status' => $product->get_status(),
			'type'   => 'product',
		);
	}

	/**
	 * Create navigation menu.
	 *
	 * @param string $name Menu name.
	 * @return array<string, mixed>|WP_Error
	 */
	public function create_menu( string $name ) {
		$menu_id = wp_create_nav_menu( $name );
		if ( is_wp_error( $menu_id ) ) {
			return $menu_id;
		}

		return array( 'id' => $menu_id, 'name' => $name );
	}

	/**
	 * Create user.
	 *
	 * @param array<string, mixed> $args User args.
	 * @return array<string, mixed>|WP_Error
	 */
	public function create_user( array $args ) {
		$defaults = array(
			'user_login' => '',
			'user_email' => '',
			'user_pass'  => wp_generate_password( 16 ),
			'role'       => 'subscriber',
		);

		$data = wp_parse_args( $args, $defaults );
		$id   = wp_insert_user( $data );

		if ( is_wp_error( $id ) ) {
			return $id;
		}

		$user = get_user_by( 'id', $id );
		return $this->format_user( $user );
	}

	/**
	 * Register custom post type.
	 *
	 * @param array<string, mixed> $args CPT args.
	 * @return array<string, mixed>
	 */
	public function register_post_type( array $args ): array {
		$slug = sanitize_key( $args['slug'] ?? 'custom_type' );
		register_post_type( $slug, $args );
		return array( 'slug' => $slug, 'registered' => true );
	}

	/**
	 * Get WordPress option(s).
	 *
	 * @param string|null $key Option key.
	 * @return mixed
	 */
	public function get_options( ?string $key = null ) {
		$blocked = array( 'auth_key', 'secure_auth_key', 'logged_in_key', 'nonce_key', 'dejoiy_acb_jwt_secret' );

		if ( $key ) {
			if ( in_array( $key, $blocked, true ) ) {
				return new WP_Error( 'blocked', __( 'Option access denied.', 'dejoiy-ai-control-bridge' ), array( 'status' => 403 ) );
			}
			return get_option( $key );
		}

		global $wpdb;
		$options = $wpdb->get_results(
			"SELECT option_name, option_value FROM {$wpdb->options} WHERE autoload IN ('yes', 'on', 'auto') LIMIT 500",
			ARRAY_A
		);

		$result = array();
		foreach ( $options as $opt ) {
			if ( in_array( $opt['option_name'], $blocked, true ) ) {
				continue;
			}
			$result[ $opt['option_name'] ] = maybe_unserialize( $opt['option_value'] );
		}

		return $result;
	}

	/**
	 * Update option.
	 *
	 * @param string $key   Key.
	 * @param mixed  $value Value.
	 * @return bool|WP_Error
	 */
	public function update_option( string $key, $value ) {
		$blocked = array( 'auth_key', 'secure_auth_key', 'logged_in_key', 'nonce_key', 'dejoiy_acb_jwt_secret' );
		if ( in_array( $key, $blocked, true ) ) {
			return new WP_Error( 'blocked', __( 'Option update denied.', 'dejoiy-ai-control-bridge' ), array( 'status' => 403 ) );
		}

		return update_option( $key, $value );
	}

	/**
	 * Get WooCommerce settings.
	 *
	 * @return array<string, mixed>
	 */
	public function get_woocommerce_config(): array {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return array( 'active' => false );
		}

		return array(
			'active'       => true,
			'version'      => WC()->version,
			'currency'     => get_woocommerce_currency(),
			'store_address' => array(
				'address_1' => get_option( 'woocommerce_store_address' ),
				'city'      => get_option( 'woocommerce_store_city' ),
				'country'   => get_option( 'woocommerce_default_country' ),
			),
			'payment_gateways' => array_keys( WC()->payment_gateways()->payment_gateways() ),
		);
	}

	/**
	 * Get WCFM configuration if available.
	 *
	 * @return array<string, mixed>
	 */
	public function get_wcfm_config(): array {
		if ( ! defined( 'WCFM_VERSION' ) && ! class_exists( 'WCFM' ) ) {
			return array( 'active' => false );
		}

		return array(
			'active'  => true,
			'version' => defined( 'WCFM_VERSION' ) ? WCFM_VERSION : 'unknown',
			'options' => array(
				'marketplace' => get_option( 'wcfm_marketplace_options', array() ),
				'membership'  => get_option( 'wcfm_membership_options', array() ),
			),
		);
	}

	/**
	 * @param WP_Post|null $post Post.
	 * @return array<string, mixed>|null
	 */
	private function format_post( ?WP_Post $post ): ?array {
		if ( ! $post ) {
			return null;
		}
		return array(
			'id'      => $post->ID,
			'title'   => $post->post_title,
			'status'  => $post->post_status,
			'type'    => $post->post_type,
			'link'    => get_permalink( $post ),
		);
	}

	/**
	 * @param WP_User|false $user User.
	 * @return array<string, mixed>|null
	 */
	private function format_user( $user ): ?array {
		if ( ! $user ) {
			return null;
		}
		return array(
			'id'    => $user->ID,
			'login' => $user->user_login,
			'email' => $user->user_email,
			'role'  => $user->roles[0] ?? '',
		);
	}
}
