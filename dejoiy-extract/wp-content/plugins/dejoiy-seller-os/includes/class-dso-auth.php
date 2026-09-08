<?php
/**
 * DSO Authentication Handler
 */
if (!defined('ABSPATH')) exit;

class DSO_Auth {

    /**
     * Check if user is authenticated and is a vendor
     */
    public static function require_vendor() {
        if (!is_user_logged_in()) {
            wp_redirect(wp_login_url(home_url('/seller-hub/')));
            exit;
        }

        $plugin = Dejoiy_Seller_OS::instance();
        if (!$plugin->is_vendor()) {
            wp_die(
                'Access Denied',
                'Unauthorized',
                ['response' => 403, 'back_link' => true]
            );
        }

        return get_current_user_id();
    }

    /**
     * Get current vendor's store data
     */
    public static function get_vendor_store($user_id = 0) {
        if (!$user_id) $user_id = get_current_user_id();
        $plugin = Dejoiy_Seller_OS::instance();
        $vendor_id = $plugin->get_vendor_id($user_id);

        if (!$vendor_id) return null;

        $store = get_post($vendor_id);
        if (!$store) return null;

        return [
            'id' => $vendor_id,
            'name' => get_the_title($vendor_id),
            'description' => get_the_excerpt($vendor_id),
            'url' => get_permalink($vendor_id),
            'logo' => get_the_post_thumbnail_url($vendor_id, 'thumbnail'),
            'banner' => get_the_post_thumbnail_url($vendor_id, 'full'),
            'user_id' => $user_id,
            'email' => get_the_author_meta('user_email', $user_id),
        ];
    }

    /**
     * Get vendor capabilities
     */
    public static function get_capabilities($user_id = 0) {
        if (!$user_id) $user_id = get_current_user_id();

        $defaults = [
            'manage_products' => true,
            'manage_orders' => true,
            'manage_customers' => true,
            'manage_withdrawals' => true,
            'manage_store' => true,
            'view_analytics' => true,
            'manage_coupons' => true,
            'manage_shipping' => true,
        ];

        // Check WCFM capabilities
        if (function_exists('wcfm_get_user_capabilities')) {
            $caps = wcfm_get_user_capabilities($user_id);
            if (!empty($caps)) {
                return array_merge($defaults, $caps);
            }
        }

        return $defaults;
    }

    /**
     * Verify REST API nonce
     */
    public static function verify_nonce($request) {
        $nonce = $request->get_header('X-WP-Nonce');
        if (!wp_verify_nonce($nonce, 'wp_rest')) {
            return new WP_Error('invalid_nonce', 'Security check failed', ['status' => 403]);
        }
        return true;
    }
}
