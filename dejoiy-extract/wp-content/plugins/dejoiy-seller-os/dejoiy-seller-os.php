<?php
/**
 * Plugin Name: DEJOIY Seller OS
 * Description: World-class seller dashboard — Amazon-level experience powered by WCFM + WooCommerce
 * Version: 1.0.0
 * Author: DEJOIY
 * Text Domain: dejoiy-seller-os
 * Requires PHP: 7.4
 * Requires at least: 5.8
 */

if (!defined('ABSPATH')) exit;

define('DSO_VERSION', '1.0.0');
define('DSO_PATH', plugin_dir_path(__FILE__));
define('DSO_URL', plugin_dir_url(__FILE__));
define('DSO_BASE', 'seller-hub');

// ─── Core bootstrap ───
require_once DSO_PATH . 'includes/class-dso-auth.php';
require_once DSO_PATH . 'includes/class-dso-router.php';
require_once DSO_PATH . 'includes/class-dso-dashboard.php';
require_once DSO_PATH . 'includes/class-dso-products.php';
require_once DSO_PATH . 'includes/class-dso-orders.php';
require_once DSO_PATH . 'includes/class-dso-customers.php';
require_once DSO_PATH . 'includes/class-dso-analytics.php';
require_once DSO_PATH . 'includes/class-dso-finance.php';
require_once DSO_PATH . 'includes/class-dso-reviews.php';
require_once DSO_PATH . 'includes/class-dso-store.php';
require_once DSO_PATH . 'includes/class-dso-notifications.php';
require_once DSO_PATH . 'includes/class-dso-support.php';
require_once DSO_PATH . 'includes/class-dso-settings.php';
require_once DSO_PATH . 'includes/class-dso-marketing.php';
require_once DSO_PATH . 'includes/class-dso-shipping.php';
require_once DSO_PATH . 'includes/class-dso-pricing.php';
require_once DSO_PATH . 'includes/class-dso-advertising.php';
require_once DSO_PATH . 'includes/class-dso-growth.php';
require_once DSO_PATH . 'includes/class-dso-reports.php';
require_once DSO_PATH . 'includes/class-dso-performance.php';
require_once DSO_PATH . 'includes/class-dso-apps.php';
require_once DSO_PATH . 'includes/class-dso-b2b.php';
require_once DSO_PATH . 'includes/class-dso-brands.php';
require_once DSO_PATH . 'includes/class-dso-learn.php';
require_once DSO_PATH . 'api/rest-api.php';

/**
 * Main Seller OS class
 */
class Dejoiy_Seller_OS {

    private static $instance = null;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Register hooks
        add_action('init', [$this, 'init']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('rest_api_init', [$this, 'register_rest_routes']);

        // Register custom rewrite rules
        add_action('init', [$this, 'register_rewrite_rules']);
        add_action('template_redirect', [$this, 'handle_template_redirect']);

        // Register activation/deactivation
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);

        // WooCommerce endpoint integration
        add_filter('woocommerce_account_menu_items', [$this, 'add_seller_hub_menu_item']);
        add_action('woocommerce_account_seller-hub_endpoint', [$this, 'render_seller_hub']);
    }

    public function init() {
        // Add endpoint
        add_rewrite_endpoint('seller-hub', EP_ROOT | EP_PAGES);
    }

    public function register_rewrite_rules() {
        add_rewrite_rule('^' . DSO_BASE . '/(.+?)/?$', 'index.php? seller-hub=$matches[1]', 'top');
        add_rewrite_rule('^' . DSO_BASE . '/?$', 'index.php? seller-hub=dashboard', 'top');
    }

    /**
     * Add seller-hub to WooCommerce My Account menu
     */
    public function add_seller_hub_menu_item($items) {
        // Check if user is a vendor
        if ($this->is_vendor()) {
            $new_items = [];
            foreach ($items as $key => $value) {
                $new_items[$key] = $value;
                if ($key === 'dashboard') {
                    $new_items['seller-hub'] = '🏪 Seller Hub';
                }
            }
            return $new_items;
        }
        return $items;
    }

    /**
     * Render the seller hub inside WooCommerce My Account
     */
    public function render_seller_hub() {
        $section = isset($_GET['seller-hub']) ? sanitize_text_field($_GET['seller-hub']) : 'dashboard';
        DSO_Router::render($section);
    }

    /**
     * Check if current user is a vendor
     */
    public function is_vendor($user_id = 0) {
        if (!$user_id) $user_id = get_current_user_id();
        if (!$user_id) return false;

        // Check WCFM vendor capability
        if (function_exists('wcfm_is_vendor')) {
            return wcfm_is_vendor($user_id);
        }

        // Fallback: check user role
        $user = get_userdata($user_id);
        if (!$user) return false;

        $vendor_roles = ['wcfm_vendor', 'vendor', 'store_manager'];
        foreach ($vendor_roles as $role) {
            if (in_array($role, $user->roles)) return true;
        }

        return false;
    }

    /**
     * Get vendor ID for current user
     */
    public function get_vendor_id($user_id = 0) {
        if (!$user_id) $user_id = get_current_user_id();

        if (function_exists('wcfm_get_vendor_id_by_user')) {
            return wcfm_get_vendor_id_by_user($user_id);
        }

        // Fallback: check vendor meta
        $vendor_id = get_user_meta($user_id, 'wcfm_vendor_id', true);
        if ($vendor_id) return intval($vendor_id);

        return 0;
    }

    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        DSO_REST_API::register_routes();
    }

    /**
     * Handle template redirect for direct URL access
     */
    public function handle_template_redirect() {
        // Check if accessing seller-hub directly
        if (isset($_GET['seller-hub']) || preg_match('#/seller-hub/#', $_SERVER['REQUEST_URI'] ?? '')) {
            if (!$this->is_vendor()) {
                wp_redirect(wp_login_url(home_url('/seller-hub/')));
                exit;
            }
        }
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_assets() {
        global $post;

        // Only load on seller hub pages
        $is_seller_page = false;
        if (is_account_page() && isset($_GET['seller-hub'])) {
            $is_seller_page = true;
        }
        if ($post && has_shortcode($post->post_content, 'seller_hub')) {
            $is_seller_page = true;
        }

        if (!$is_seller_page) return;

        // Chart.js
        wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', [], '4.4.0', true);

        // Seller OS CSS
        wp_enqueue_style('dso-main', DSO_URL . 'assets/css/seller-os.css', [], DSO_VERSION);

        // Seller OS JS
        wp_enqueue_script('dso-main', DSO_URL . 'assets/js/seller-os.js', ['jquery', 'chartjs'], DSO_VERSION, true);

        // Pass data to JS
        wp_localize_script('dso-main', 'dsoData', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'restUrl' => rest_url('dejoiy-seller-os/v1/'),
            'nonce' => wp_create_nonce('dso_nonce'),
            'vendorId' => $this->get_vendor_id(),
            'userId' => get_current_user_id(),
            'baseUrl' => home_url('/' . DSO_BASE . '/'),
            'currency' => get_woocommerce_currency_symbol(),
        ]);
    }

    /**
     * Plugin activation
     */
    public function activate() {
        global $wpdb;

        // Create notifications table
        $charset_collate = $wpdb->get_charset_collate();
        $table = $wpdb->prefix . 'dso_notifications';

        $sql = "CREATE TABLE IF NOT EXISTS $table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            vendor_id BIGINT(20) UNSIGNED NOT NULL,
            type VARCHAR(50) NOT NULL DEFAULT 'info',
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            action_url VARCHAR(500) DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY vendor_id (vendor_id),
            KEY is_read (is_read)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        // Create support tickets table
        $table2 = $wpdb->prefix . 'dso_support_tickets';
        $sql2 = "CREATE TABLE IF NOT EXISTS $table2 (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            vendor_id BIGINT(20) UNSIGNED NOT NULL,
            subject VARCHAR(255) NOT NULL,
            category VARCHAR(50) NOT NULL DEFAULT 'general',
            message TEXT NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'open',
            priority VARCHAR(20) NOT NULL DEFAULT 'normal',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY vendor_id (vendor_id),
            KEY status (status)
        ) $charset_collate;";

        dbDelta($sql2);

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
}

// Initialize
Dejoiy_Seller_OS::instance();
