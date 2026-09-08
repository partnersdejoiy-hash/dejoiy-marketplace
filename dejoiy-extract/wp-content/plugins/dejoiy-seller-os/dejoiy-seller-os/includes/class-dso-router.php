<?php
/**
 * DSO Router - Handles section rendering
 */
if (!defined('ABSPATH')) exit;

class DSO_Router {

    private static $sections = [
        'dashboard'      => ['class' => 'DSO_Dashboard', 'title' => 'Dashboard', 'icon' => '📊'],
        'products'       => ['class' => 'DSO_Products', 'title' => 'Products', 'icon' => '📦'],
        'add-product'    => ['class' => 'DSO_Products', 'method' => 'add_product', 'title' => 'Add Product', 'icon' => '➕'],
        'edit-product'   => ['class' => 'DSO_Products', 'method' => 'edit_product', 'title' => 'Edit Product', 'icon' => '✏️'],
        'inventory'      => ['class' => 'DSO_Products', 'method' => 'inventory', 'title' => 'Inventory', 'icon' => '📋'],
        'pricing'        => ['class' => 'DSO_Pricing', 'title' => 'Pricing', 'icon' => '💲'],
        'orders'         => ['class' => 'DSO_Orders', 'title' => 'Orders', 'icon' => '🛒'],
        'order-detail'   => ['class' => 'DSO_Orders', 'method' => 'order_detail', 'title' => 'Order Detail', 'icon' => '📋'],
        'customers'      => ['class' => 'DSO_Customers', 'title' => 'Customers', 'icon' => '👥'],
        'advertising'    => ['class' => 'DSO_Advertising', 'title' => 'Advertising', 'icon' => '📣'],
        'growth'         => ['class' => 'DSO_Growth', 'title' => 'Growth', 'icon' => '🌱'],
        'reports'        => ['class' => 'DSO_Reports', 'title' => 'Reports', 'icon' => '📈'],
        'finance'        => ['class' => 'DSO_Finance', 'title' => 'Finance', 'icon' => '💰'],
        'withdrawals'    => ['class' => 'DSO_Finance', 'method' => 'withdrawals', 'title' => 'Withdrawals', 'icon' => '🏦'],
        'performance'    => ['class' => 'DSO_Performance', 'title' => 'Performance', 'icon' => '⚡'],
        'reviews'        => ['class' => 'DSO_Reviews', 'title' => 'Reviews', 'icon' => '⭐'],
        'marketing'      => ['class' => 'DSO_Marketing', 'title' => 'Marketing', 'icon' => '🏷️'],
        'shipping'       => ['class' => 'DSO_Shipping', 'title' => 'Shipping', 'icon' => '🚚'],
        'apps'           => ['class' => 'DSO_Apps', 'title' => 'Apps & Services', 'icon' => '🧩'],
        'b2b'            => ['class' => 'DSO_B2B', 'title' => 'B2B', 'icon' => '🏢'],
        'brands'         => ['class' => 'DSO_Brands', 'title' => 'Brands', 'icon' => '🏷️'],
        'learn'          => ['class' => 'DSO_Learn', 'title' => 'Learn', 'icon' => '🎓'],
        'notifications'  => ['class' => 'DSO_Notifications', 'title' => 'Notifications', 'icon' => '🔔'],
        'support'        => ['class' => 'DSO_Support', 'title' => 'Support', 'icon' => '🆘'],
        'store'          => ['class' => 'DSO_Store', 'title' => 'Store Settings', 'icon' => '🏪'],
        'settings'       => ['class' => 'DSO_Settings', 'title' => 'Settings', 'icon' => '⚙️'],
    ];

    public static function render($section) {
        $user_id = DSO_Auth::require_vendor();
        $store = DSO_Auth::get_vendor_store($user_id);
        $caps = DSO_Auth::get_capabilities($user_id);

        if (!isset(self::$sections[$section])) {
            $section = 'dashboard';
        }

        $config = self::$sections[$section];
        $class_name = $config['class'];
        $method = $config['method'] ?? 'render';

        $class = new $class_name();
        $page_title = $config['title'];

        $nav_items = self::get_nav_items($caps);

        ob_start();
        include DSO_PATH . 'templates/seller-os-shell.php';
        $content = ob_get_clean();
        echo $content;
    }

    public static function get_nav_items($caps = []) {
        $items = [
            ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>', 'url' => 'dashboard'],
        ];

        if (!empty($caps['manage_products'])) {
            $items[] = ['id' => 'products', 'label' => 'Catalogue', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 002 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0022 16z"/></svg>', 'url' => 'products', 'children' => [
                ['id' => 'products-all', 'label' => 'All Products', 'url' => 'products'],
                ['id' => 'products-add', 'label' => 'Add Product', 'url' => 'add-product'],
            ]];
            $items[] = ['id' => 'inventory', 'label' => 'Inventory', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 002 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0022 16z"/><path d="M3 12h4l2-3 4 6 2-3h4"/></svg>', 'url' => 'inventory'];
            $items[] = ['id' => 'pricing', 'label' => 'Pricing', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>', 'url' => 'pricing'];
        }

        if (!empty($caps['manage_orders'])) {
            $items[] = ['id' => 'orders', 'label' => 'Orders', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>', 'url' => 'orders'];
        }

        $items[] = ['id' => 'advertising', 'label' => 'Advertising', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>', 'url' => 'advertising'];

        $items[] = ['id' => 'growth', 'label' => 'Growth', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>', 'url' => 'growth'];

        if (!empty($caps['view_analytics'])) {
            $items[] = ['id' => 'reports', 'label' => 'Reports', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 20V10"/><path d="M12 20V4"/><path d="M6 20v-6"/></svg>', 'url' => 'reports'];
        }

        if (!empty($caps['manage_withdrawals'])) {
            $items[] = ['id' => 'finance', 'label' => 'Payments', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>', 'url' => 'finance', 'children' => [
                ['id' => 'finance-overview', 'label' => 'Earnings', 'url' => 'finance'],
                ['id' => 'finance-withdraw', 'label' => 'Withdrawals', 'url' => 'withdrawals'],
            ]];
        }

        $items[] = ['id' => 'performance', 'label' => 'Performance', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>', 'url' => 'performance'];

        $items[] = ['id' => 'reviews', 'label' => 'Reviews', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>', 'url' => 'reviews'];

        $items[] = ['id' => 'apps', 'label' => 'Apps & Services', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>', 'url' => 'apps'];

        $items[] = ['id' => 'b2b', 'label' => 'B2B', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>', 'url' => 'b2b'];

        $items[] = ['id' => 'brands', 'label' => 'Brands', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>', 'url' => 'brands'];

        $items[] = ['id' => 'learn', 'label' => 'Learn', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/><path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/></svg>', 'url' => 'learn'];

        $items[] = ['id' => 'notifications', 'label' => 'Notifications', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>', 'url' => 'notifications'];

        $items[] = ['id' => 'support', 'label' => 'Support', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>', 'url' => 'support'];

        $items[] = ['id' => 'store', 'label' => 'Store Settings', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.32 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>', 'url' => 'store'];

        return $items;
    }

    public static function get_current_section() {
        if (isset($_GET['seller-hub'])) {
            return sanitize_text_field($_GET['seller-hub']);
        }
        return 'dashboard';
    }

    public static function get_current_section_config($section) {
        if (isset(self::$sections[$section])) {
            return self::$sections[$section];
        }
        return null;
    }
}
