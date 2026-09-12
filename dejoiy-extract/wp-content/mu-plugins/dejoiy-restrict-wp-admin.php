<?php
/**
 * Plugin Name: DEJOIY Security Shield — Restrict WP Admin & Frontend Editor
 * Description: Restricts WordPress admin bar, Gutenberg block editor assets, and wp-admin access strictly to WordPress Administrators inside wp-admin. Normal users, customers, and vendors never see any editor, toolbar, or WordPress admin UI on the frontend https://dejoiy.com.
 * Version: 2.1.0
 * Author: DEJOIY Marketplace Engineering
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 1. STRICT FRONTEND ADMIN BAR RESTRICTION
 * The user requirement: "wo sirf wordpress admin tk restricted rahe no where else"
 * (Restricted exclusively to WordPress admin dashboard, nowhere else).
 * On the frontend (https://dejoiy.com), NEVER display the admin bar to normal users or customers.
 */
add_filter( 'show_admin_bar', function( $show ) {
	// On frontend, always return false. Admin toolbar only exists inside /wp-admin/.
	if ( ! is_admin() ) {
		return false;
	}
	return current_user_can( 'administrator' );
}, PHP_INT_MAX );

/**
 * 2. REMOVE FRONTEND POST & COMMENT EDIT LINKS
 */
add_filter( 'edit_post_link', '__return_empty_string', PHP_INT_MAX );
add_filter( 'edit_comment_link', '__return_empty_string', PHP_INT_MAX );

/**
 * 3. RESTRICT /wp-admin/ ACCESS
 * Non-administrators (customers, subscribers, vendors) are strictly blocked from /wp-admin/.
 * They are redirected to /my-account/ or Seller Hub.
 */
add_action( 'admin_init', function() {
	// Allow background AJAX and REST requests
	if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}

	// If not logged in, standard login redirect will take over
	if ( ! is_user_logged_in() ) {
		return;
	}

	// If logged-in user is NOT an administrator, kick them out of wp-admin
	if ( ! current_user_can( 'administrator' ) ) {
		if ( function_exists( 'wcfm_is_vendor' ) && wcfm_is_vendor() ) {
			wp_safe_redirect( 'https://sellerhub.dejoiy.com/' );
		} else {
			$account_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/my-account/' );
			wp_safe_redirect( ! empty( $account_url ) ? $account_url : home_url( '/' ) );
		}
		exit;
	}
}, 1 );

/**
 * 4. DEFAULT USERS TO NO FRONTEND ADMIN BAR ON REGISTRATION
 */
add_action( 'user_register', function( $user_id ) {
	update_user_meta( $user_id, 'show_admin_bar_front', 'false' );
}, 10, 1 );

/**
 * 5. DEQUEUE ADMIN BAR & EDITOR ASSETS ON FRONTEND
 */
add_action( 'wp_enqueue_scripts', function() {
	if ( is_admin() ) {
		return;
	}

	// Dequeue admin bar
	wp_dequeue_style( 'admin-bar' );
	wp_deregister_style( 'admin-bar' );
	wp_dequeue_script( 'admin-bar' );
	wp_deregister_script( 'admin-bar' );

	// Dequeue block editor / Gutenberg assets on frontend
	$editor_styles = [
		'wp-block-library',
		'wp-block-library-theme',
		'wp-block-editor',
		'wp-edit-blocks',
		'wp-editor',
		'wp-components',
		'wp-edit-post-common',
		'ai-summarization-styles',
		'ai-content-translation-styles',
	];
	foreach ( $editor_styles as $handle ) {
		wp_dequeue_style( $handle );
	}
}, 99999 );

/**
 * 6. REMOVE ADMIN BAR RENDER ACTION ON FRONTEND
 */
add_action( 'init', function() {
	if ( ! is_admin() ) {
		remove_action( 'wp_footer', 'wp_admin_bar_render', 1000 );
	}
}, 1 );

/**
 * 7. CSS SHIELD TO GUARANTEE ZERO VISUAL LEAKAGE
 */
add_action( 'wp_head', function() {
	if ( is_admin() ) {
		return;
	}
	?>
	<style id="dejoiy-admin-shield">
	#wpadminbar,
	.edit-link,
	.post-edit-link,
	#wp-toolbar,
	.elementor-edit-link,
	.wcfm-page-heading-link {
		display: none !important;
		visibility: hidden !important;
		height: 0 !important;
		min-height: 0 !important;
		max-height: 0 !important;
		overflow: hidden !important;
		opacity: 0 !important;
		pointer-events: none !important;
	}
	html, body {
		margin-top: 0px !important;
		padding-top: 0px !important;
	}
	html body.admin-bar {
		margin-top: 0px !important;
	}
	</style>
	<?php
}, 99999 );
