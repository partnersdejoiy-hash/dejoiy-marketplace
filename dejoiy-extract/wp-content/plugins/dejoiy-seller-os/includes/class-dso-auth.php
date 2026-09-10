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
        if (!$user_id) return null;

        $user = get_userdata($user_id);
        if (!$user) return null;

        $store_name = $user->display_name;
        $store_desc = '';
        $store_logo = '';
        $store_banner = '';
        $store_phone = '';
        $store_address = '';
        $store_url = home_url('/store/' . $user->user_nicename . '/');

        // Check WCFM Marketplace store
        if (function_exists('wcfmmp_get_store')) {
            $wcfm_store = wcfmmp_get_store($user_id);
            if ($wcfm_store) {
                $shop_name = $wcfm_store->get_shop_name();
                if (!empty($shop_name)) $store_name = $shop_name;
                $store_desc = $wcfm_store->get_shop_description() ?: '';
                $store_logo = $wcfm_store->get_avatar() ?: '';
                $store_banner = $wcfm_store->get_banner() ?: '';
                $store_phone = $wcfm_store->get_phone() ?: '';
                $link = $wcfm_store->get_link();
                if (!empty($link)) $store_url = $link;
            }
        }

        // Check post store if exists
        $post_store = get_post($user_id);
        if ($post_store && $post_store->post_type === 'wcfm_store') {
            $store_name = get_the_title($user_id);
            $store_desc = get_the_excerpt($user_id);
            $store_logo = get_the_post_thumbnail_url($user_id, 'thumbnail');
            $store_banner = get_the_post_thumbnail_url($user_id, 'full');
        }

        return [
            'id' => $user_id,
            'name' => $store_name,
            'description' => $store_desc,
            'url' => $store_url,
            'logo' => $store_logo,
            'banner' => $store_banner,
            'user_id' => $user_id,
            'email' => $user->user_email,
            'phone' => $store_phone,
            'address' => $store_address,
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
