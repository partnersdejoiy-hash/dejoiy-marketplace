<?php
/**
 * DSO Router - Handles section routing, navigation hierarchy, and execution
 * Built for DEJOIY Seller Operating System
 */
if (!defined('ABSPATH')) exit;

class DSO_Router {

    private static $sections = [
        // Dashboard
        'dashboard'             => ['class' => 'DSO_Dashboard', 'title' => 'Dashboard', 'group' => 'main'],

        // Catalogue
        'products'              => ['class' => 'DSO_Products', 'title' => 'All Products', 'group' => 'catalogue'],
        'catalog-upload'        => ['class' => 'DSO_Products', 'method' => 'catalog_upload', 'title' => 'Bulk Catalog Upload', 'group' => 'catalogue'],
        'add-product'           => ['class' => 'DSO_Products', 'method' => 'add_product', 'title' => 'Add Product', 'group' => 'catalogue'],
        'edit-product'          => ['class' => 'DSO_Products', 'method' => 'edit_product', 'title' => 'Edit Product', 'group' => 'catalogue'],
        'categories'            => ['class' => 'DSO_Products', 'method' => 'categories', 'title' => 'Categories', 'group' => 'catalogue'],
        'collections'           => ['class' => 'DSO_Products', 'method' => 'collections', 'title' => 'Collections', 'group' => 'catalogue'],
        'attributes'            => ['class' => 'DSO_Products', 'method' => 'attributes', 'title' => 'Attributes', 'group' => 'catalogue'],
        'product-quality'       => ['class' => 'DSO_Products', 'method' => 'product_quality', 'title' => 'Listing Quality', 'group' => 'catalogue'],
        'reviews'               => ['class' => 'DSO_Reviews', 'title' => 'Product Reviews', 'group' => 'catalogue'],

        // Inventory
        'inventory'             => ['class' => 'DSO_Products', 'method' => 'inventory', 'title' => 'Inventory Overview', 'group' => 'inventory'],
        'inventory-stock'       => ['class' => 'DSO_Products', 'method' => 'inventory', 'title' => 'Stock Manager', 'group' => 'inventory'],
        'inventory-low'         => ['class' => 'DSO_Products', 'method' => 'inventory_low', 'title' => 'Low Stock Alerts', 'group' => 'inventory'],
        'inventory-out'         => ['class' => 'DSO_Products', 'method' => 'inventory_out', 'title' => 'Out of Stock', 'group' => 'inventory'],
        'inventory-history'     => ['class' => 'DSO_Products', 'method' => 'inventory_history', 'title' => 'Stock History', 'group' => 'inventory'],
        'inventory-bulk'        => ['class' => 'DSO_Products', 'method' => 'inventory_bulk', 'title' => 'Bulk Stock Updater', 'group' => 'inventory'],

        // Pricing
        'pricing'               => ['class' => 'DSO_Pricing', 'title' => 'Pricing Overview', 'group' => 'pricing'],
        'automate-pricing'      => ['class' => 'DSO_Pricing', 'method' => 'automate_pricing', 'title' => 'Automate Pricing', 'group' => 'pricing'],
        'promotions'            => ['class' => 'DSO_Pricing', 'method' => 'promotions', 'title' => 'Promotions', 'group' => 'pricing'],
        'deals'                 => ['class' => 'DSO_Pricing', 'method' => 'deals', 'title' => 'Deals & Flash Sales', 'group' => 'pricing'],
        'coupons'               => ['class' => 'DSO_Pricing', 'method' => 'coupons', 'title' => 'Coupons', 'group' => 'pricing'],
        'bulk-pricing'          => ['class' => 'DSO_Pricing', 'method' => 'bulk_pricing', 'title' => 'Bulk Pricing Rules', 'group' => 'pricing'],
        'b2b-pricing'           => ['class' => 'DSO_Pricing', 'method' => 'b2b_pricing', 'title' => 'B2B Wholesale Pricing', 'group' => 'pricing'],

        // Orders
        'orders'                => ['class' => 'DSO_Orders', 'title' => 'All Orders', 'group' => 'orders'],
        'orders-pending'        => ['class' => 'DSO_Orders', 'method' => 'orders_pending', 'title' => 'Pending Orders', 'group' => 'orders'],
        'orders-processing'     => ['class' => 'DSO_Orders', 'method' => 'orders_processing', 'title' => 'Processing Orders', 'group' => 'orders'],
        'orders-shipped'        => ['class' => 'DSO_Orders', 'method' => 'orders_shipped', 'title' => 'Shipped Orders', 'group' => 'orders'],
        'orders-delivered'      => ['class' => 'DSO_Orders', 'method' => 'orders_delivered', 'title' => 'Delivered Orders', 'group' => 'orders'],
        'orders-returns'        => ['class' => 'DSO_Orders', 'method' => 'orders_returns', 'title' => 'Returns', 'group' => 'orders'],
        'orders-refunds'        => ['class' => 'DSO_Orders', 'method' => 'orders_refunds', 'title' => 'Refund Requests', 'group' => 'orders'],
        'orders-cancelled'      => ['class' => 'DSO_Orders', 'method' => 'orders_cancelled', 'title' => 'Cancelled Orders', 'group' => 'orders'],
        'order-detail'          => ['class' => 'DSO_Orders', 'method' => 'order_detail', 'title' => 'Order Detail', 'group' => 'orders'],
        'print-invoice'         => ['class' => 'DSO_Orders', 'method' => 'print_invoice', 'title' => 'Tax Invoice', 'group' => 'orders'],

        // Fulfillment / Shipping
        'shipping'              => ['class' => 'DSO_Shipping', 'title' => 'Shipping & Delivery', 'group' => 'fulfillment'],
        'shipments'             => ['class' => 'DSO_Shipping', 'method' => 'shipments', 'title' => 'Shipments', 'group' => 'fulfillment'],
        'shipping-tracking'     => ['class' => 'DSO_Shipping', 'method' => 'tracking', 'title' => 'Tracking & Carriers', 'group' => 'fulfillment'],
        'shipping-delivery'     => ['class' => 'DSO_Shipping', 'method' => 'delivery', 'title' => 'Delivery Rates', 'group' => 'fulfillment'],
        'shipping-pickup'       => ['class' => 'DSO_Shipping', 'method' => 'pickup', 'title' => 'Pickup Points', 'group' => 'fulfillment'],
        'shipping-packaging'    => ['class' => 'DSO_Shipping', 'method' => 'packaging', 'title' => 'Packaging Specs', 'group' => 'fulfillment'],
        'print-label'           => ['class' => 'DSO_Shipping', 'method' => 'print_label', 'title' => 'Print Shipping Label', 'group' => 'fulfillment'],

        // Advertising
        'advertising'           => ['class' => 'DSO_Advertising', 'title' => 'Campaign Manager', 'group' => 'advertising'],
        'advertising-sponsored' => ['class' => 'DSO_Advertising', 'method' => 'sponsored', 'title' => 'Sponsored Products', 'group' => 'advertising'],
        'advertising-promotions'=> ['class' => 'DSO_Advertising', 'method' => 'promotions', 'title' => 'Ad Promotions', 'group' => 'advertising'],
        'advertising-performance'=> ['class' => 'DSO_Advertising', 'method' => 'performance', 'title' => 'RoAS & Performance', 'group' => 'advertising'],

        // Growth
        'growth'                => ['class' => 'DSO_Growth', 'title' => 'Growth Opportunities', 'group' => 'growth'],
        'growth-recommendations'=> ['class' => 'DSO_Growth', 'method' => 'recommendations', 'title' => 'AI Recommendations', 'group' => 'growth'],
        'growth-insights'       => ['class' => 'DSO_Growth', 'method' => 'insights', 'title' => 'Customer Insights', 'group' => 'growth'],
        'growth-opportunities'  => ['class' => 'DSO_Growth', 'method' => 'opportunities', 'title' => 'Product Opportunities', 'group' => 'growth'],

        // Reports & Analytics
        'reports'               => ['class' => 'DSO_Reports', 'title' => 'Sales Analytics', 'group' => 'reports'],
        'reports-orders'        => ['class' => 'DSO_Reports', 'method' => 'orders_report', 'title' => 'Order Reports', 'group' => 'reports'],
        'reports-revenue'       => ['class' => 'DSO_Reports', 'method' => 'revenue_report', 'title' => 'Revenue & Commission', 'group' => 'reports'],
        'reports-products'      => ['class' => 'DSO_Reports', 'method' => 'products_report', 'title' => 'Product Reports', 'group' => 'reports'],
        'reports-inventory'     => ['class' => 'DSO_Reports', 'method' => 'inventory_report', 'title' => 'Inventory Velocity', 'group' => 'reports'],
        'reports-customers'     => ['class' => 'DSO_Reports', 'method' => 'customers_report', 'title' => 'Customer Reports', 'group' => 'reports'],
        'reports-marketing'     => ['class' => 'DSO_Reports', 'method' => 'marketing_report', 'title' => 'Marketing Reports', 'group' => 'reports'],
        'reports-finance'       => ['class' => 'DSO_Reports', 'method' => 'financial_report', 'title' => 'Financial Statements', 'group' => 'reports'],
        'analytics'             => ['class' => 'DSO_Reports', 'title' => 'Analytics', 'group' => 'reports'],

        // Payments & Finance
        'finance'               => ['class' => 'DSO_Finance', 'title' => 'Balance Overview', 'group' => 'payments'],
        'finance-transactions'  => ['class' => 'DSO_Finance', 'method' => 'transactions', 'title' => 'Transactions & Ledger', 'group' => 'payments'],
        'finance-commissions'   => ['class' => 'DSO_Finance', 'method' => 'commissions', 'title' => 'Commissions Breakdown', 'group' => 'payments'],
        'withdrawals'           => ['class' => 'DSO_Finance', 'method' => 'withdrawals', 'title' => 'Withdrawal Requests', 'group' => 'payments'],
        'finance-payouts'       => ['class' => 'DSO_Finance', 'method' => 'payouts', 'title' => 'Payout History', 'group' => 'payments'],
        'finance-statements'    => ['class' => 'DSO_Finance', 'method' => 'statements', 'title' => 'Tax Invoices & Statements', 'group' => 'payments'],

        // Performance
        'performance'           => ['class' => 'DSO_Performance', 'title' => 'Store Health', 'group' => 'performance'],
        'performance-products'  => ['class' => 'DSO_Performance', 'method' => 'product_health', 'title' => 'Product Health', 'group' => 'performance'],
        'performance-seller'    => ['class' => 'DSO_Performance', 'method' => 'seller_performance', 'title' => 'Seller Performance Tier', 'group' => 'performance'],
        'performance-csat'      => ['class' => 'DSO_Performance', 'method' => 'customer_satisfaction', 'title' => 'Customer Satisfaction', 'group' => 'performance'],
        'performance-delivery'  => ['class' => 'DSO_Performance', 'method' => 'delivery_performance', 'title' => 'Delivery Performance', 'group' => 'performance'],

        // B2B Wholesale
        'b2b'                   => ['class' => 'DSO_B2B', 'title' => 'B2B Wholesale Hub', 'group' => 'b2b'],
        'b2b-orders'            => ['class' => 'DSO_B2B', 'method' => 'bulk_orders', 'title' => 'Bulk Orders', 'group' => 'b2b'],
        'b2b-customers'         => ['class' => 'DSO_B2B', 'method' => 'business_customers', 'title' => 'Business Customers', 'group' => 'b2b'],
        'b2b-offers'            => ['class' => 'DSO_B2B', 'method' => 'b2b_offers', 'title' => 'B2B RFQ & Offers', 'group' => 'b2b'],

        // Brands
        'brands'                => ['class' => 'DSO_Brands', 'title' => 'My Brands', 'group' => 'brands'],
        'brands-assets'         => ['class' => 'DSO_Brands', 'method' => 'brand_assets', 'title' => 'Brand Assets & Media', 'group' => 'brands'],
        'brands-protection'     => ['class' => 'DSO_Brands', 'method' => 'brand_protection', 'title' => 'Brand Protection & IP', 'group' => 'brands'],

        // Apps & Services
        'apps'                  => ['class' => 'DSO_Apps', 'title' => 'Seller Tools & Apps', 'group' => 'apps'],
        'apps-integrations'     => ['class' => 'DSO_Apps', 'method' => 'integrations', 'title' => 'Integrations (Shiprocket, Razorpay)', 'group' => 'apps'],
        'apps-automation'       => ['class' => 'DSO_Apps', 'method' => 'automation', 'title' => 'Automation Rules', 'group' => 'apps'],
        'apps-services'         => ['class' => 'DSO_Apps', 'method' => 'services', 'title' => 'Marketplace Services', 'group' => 'apps'],

        // Learn
        'learn'                 => ['class' => 'DSO_Learn', 'title' => 'Seller University', 'group' => 'learn'],
        'learn-kb'              => ['class' => 'DSO_Learn', 'method' => 'knowledgebase', 'title' => 'Knowledgebase', 'group' => 'learn'],
        'learn-guides'          => ['class' => 'DSO_Learn', 'method' => 'guides', 'title' => 'Listing & SEO Guides', 'group' => 'learn'],
        'learn-tutorials'       => ['class' => 'DSO_Learn', 'method' => 'tutorials', 'title' => 'Video Tutorials', 'group' => 'learn'],
        'learn-policies'        => ['class' => 'DSO_Learn', 'method' => 'policies', 'title' => 'Policies & Standards', 'group' => 'learn'],

        // Customers
        'customers'             => ['class' => 'DSO_Customers', 'title' => 'Customer List', 'group' => 'orders'],

        // Settings & Operations
        'store'                 => ['class' => 'DSO_Store', 'title' => 'Store Settings', 'group' => 'settings'],
        'settings'              => ['class' => 'DSO_Settings', 'title' => 'Account & Business Profile', 'group' => 'settings'],
        'settings-tax'          => ['class' => 'DSO_Settings', 'method' => 'tax', 'title' => 'GST & Tax Settings', 'group' => 'settings'],
        'settings-shipping'     => ['class' => 'DSO_Shipping', 'title' => 'Shipping Settings', 'group' => 'settings'],
        'notifications'         => ['class' => 'DSO_Notifications', 'title' => 'Notifications', 'group' => 'settings'],
        'support'               => ['class' => 'DSO_Support', 'title' => 'Support & Help Desk', 'group' => 'settings'],
    ];

    /**
     * Render the requested section inside the application shell
     */
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

        if (!class_exists($class_name)) {
            $class_name = 'DSO_Dashboard';
            $method = 'render';
        }

        $class = new $class_name();
        $page_title = $config['title'] ?? 'Seller Hub';
        $current_section = $section;
        $nav_items = self::get_nav_items($caps);
        $user_id = get_current_user_id();
        $store = DSO_Auth::get_vendor_store($user_id);
        $store_name = $store ? $store['name'] : 'Seller';
        $user_data = get_userdata($user_id);
        $display_name = $user_data ? $user_data->display_name : $store_name;
        $store_logo = $store && !empty($store['logo']) ? $store['logo'] : '';

        ob_start();
        include DSO_PATH . 'templates/seller-os-shell.php';
        $content = ob_get_clean();
        echo $content;
    }

    /**
     * Get structured navigation hierarchy with badges and icons
     */
    public static function get_nav_items($caps = []) {
        $items = [
            [
                'id' => 'dashboard',
                'label' => 'Dashboard',
                'group' => 'Core',
                'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>',
                'url' => 'dashboard',
            ],
            [
                'id' => 'catalogue',
                'label' => 'Catalogue',
                'group' => 'Catalog & Orders',
                'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 002 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0022 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>',
                'url' => 'products',
                'children' => [
                    ['id' => 'products', 'label' => 'All Products', 'url' => 'products'],
                    ['id' => 'catalog-upload', 'label' => 'Bulk CSV Upload', 'url' => 'catalog-upload'],
                    ['id' => 'add-product', 'label' => 'Add Product', 'url' => 'add-product'],
                    ['id' => 'categories', 'label' => 'Categories', 'url' => 'categories'],
                    ['id' => 'collections', 'label' => 'Collections', 'url' => 'collections'],
                    ['id' => 'brands', 'label' => 'Brands', 'url' => 'brands'],
                    ['id' => 'attributes', 'label' => 'Attributes', 'url' => 'attributes'],
                    ['id' => 'reviews', 'label' => 'Product Reviews', 'url' => 'reviews'],
                    ['id' => 'product-quality', 'label' => 'Listing Quality', 'url' => 'product-quality'],
                ],
            ],
            [
                'id' => 'inventory',
                'label' => 'Inventory',
                'group' => 'Catalog & Orders',
                'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>',
                'url' => 'inventory',
                'children' => [
                    ['id' => 'inventory', 'label' => 'Inventory Overview', 'url' => 'inventory'],
                    ['id' => 'inventory-stock', 'label' => 'Stock Manager', 'url' => 'inventory-stock'],
                    ['id' => 'inventory-low', 'label' => 'Low Stock Alerts', 'url' => 'inventory-low'],
                    ['id' => 'inventory-out', 'label' => 'Out of Stock', 'url' => 'inventory-out'],
                    ['id' => 'inventory-bulk', 'label' => 'Bulk Stock Updater', 'url' => 'inventory-bulk'],
                ],
            ],
            [
                'id' => 'pricing',
                'label' => 'Pricing',
                'group' => 'Catalog & Orders',
                'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>',
                'url' => 'pricing',
                'children' => [
                    ['id' => 'pricing', 'label' => 'Pricing Strategy', 'url' => 'pricing'],
                    ['id' => 'automate-pricing', 'label' => 'Automate Pricing', 'url' => 'automate-pricing'],
                    ['id' => 'promotions', 'label' => 'Promotions', 'url' => 'promotions'],
                    ['id' => 'deals', 'label' => 'Deals & Flash Sales', 'url' => 'deals'],
                    ['id' => 'coupons', 'label' => 'Coupons', 'url' => 'coupons'],
                    ['id' => 'bulk-pricing', 'label' => 'Bulk Pricing Rules', 'url' => 'bulk-pricing'],
                ],
            ],
            [
                'id' => 'orders',
                'label' => 'Orders',
                'group' => 'Catalog & Orders',
                'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>',
                'url' => 'orders',
                'children' => [
                    ['id' => 'orders', 'label' => 'All Orders', 'url' => 'orders'],
                    ['id' => 'orders-pending', 'label' => 'Pending Orders', 'url' => 'orders-pending'],
                    ['id' => 'orders-processing', 'label' => 'Processing', 'url' => 'orders-processing'],
                    ['id' => 'orders-shipped', 'label' => 'Shipped', 'url' => 'orders-shipped'],
                    ['id' => 'orders-delivered', 'label' => 'Delivered', 'url' => 'orders-delivered'],
                    ['id' => 'orders-returns', 'label' => 'Returns & Refunds', 'url' => 'orders-returns'],
                    ['id' => 'orders-cancelled', 'label' => 'Cancelled', 'url' => 'orders-cancelled'],
                ],
            ],
            [
                'id' => 'fulfillment',
                'label' => 'Fulfillment',
                'group' => 'Catalog & Orders',
                'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
                'url' => 'shipping',
                'children' => [
                    ['id' => 'shipping', 'label' => 'Shipping Overview', 'url' => 'shipping'],
                    ['id' => 'shipments', 'label' => 'Shipments', 'url' => 'shipments'],
                    ['id' => 'shipping-tracking', 'label' => 'Tracking & Carriers', 'url' => 'shipping-tracking'],
                    ['id' => 'shipping-packaging', 'label' => 'Packaging Specs', 'url' => 'shipping-packaging'],
                ],
            ],
            [
                'id' => 'advertising',
                'label' => 'Advertising',
                'group' => 'Growth & Finance',
                'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M19.07 4.93a10 10 0 010 14.14M15.54 8.46a5 5 0 010 7.07"/></svg>',
                'url' => 'advertising',
                'children' => [
                    ['id' => 'advertising', 'label' => 'Campaign Manager', 'url' => 'advertising'],
                    ['id' => 'advertising-sponsored', 'label' => 'Sponsored Products', 'url' => 'advertising-sponsored'],
                    ['id' => 'advertising-performance', 'label' => 'Ad Performance', 'url' => 'advertising-performance'],
                ],
            ],
            [
                'id' => 'growth',
                'label' => 'Growth & AI',
                'group' => 'Growth & Finance',
                'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>',
                'url' => 'growth',
                'children' => [
                    ['id' => 'growth', 'label' => 'Growth Opportunities', 'url' => 'growth'],
                    ['id' => 'growth-recommendations', 'label' => 'Recommendations Engine', 'url' => 'growth-recommendations'],
                    ['id' => 'growth-insights', 'label' => 'Customer Insights', 'url' => 'growth-insights'],
                    ['id' => 'growth-opportunities', 'label' => 'Product Opportunities', 'url' => 'growth-opportunities'],
                ],
            ],
            [
                'id' => 'reports',
                'label' => 'Reports',
                'group' => 'Growth & Finance',
                'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 20V10"/><path d="M12 20V4"/><path d="M6 20v-6"/></svg>',
                'url' => 'reports',
                'children' => [
                    ['id' => 'reports', 'label' => 'Sales Analytics', 'url' => 'reports'],
                    ['id' => 'reports-orders', 'label' => 'Order Reports', 'url' => 'reports-orders'],
                    ['id' => 'reports-revenue', 'label' => 'Revenue & Commissions', 'url' => 'reports-revenue'],
                    ['id' => 'reports-products', 'label' => 'Product Velocity', 'url' => 'reports-products'],
                    ['id' => 'reports-finance', 'label' => 'Financial Statements', 'url' => 'reports-finance'],
                ],
            ],
            [
                'id' => 'payments',
                'label' => 'Payments',
                'group' => 'Growth & Finance',
                'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',
                'url' => 'finance',
                'children' => [
                    ['id' => 'finance', 'label' => 'Balance Overview', 'url' => 'finance'],
                    ['id' => 'withdrawals', 'label' => 'Withdrawals & Payouts', 'url' => 'withdrawals'],
                    ['id' => 'finance-transactions', 'label' => 'Transactions Ledger', 'url' => 'finance-transactions'],
                    ['id' => 'finance-commissions', 'label' => 'Commissions', 'url' => 'finance-commissions'],
                    ['id' => 'finance-statements', 'label' => 'Tax Invoices', 'url' => 'finance-statements'],
                ],
            ],
            [
                'id' => 'performance',
                'label' => 'Performance',
                'group' => 'Growth & Finance',
                'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
                'url' => 'performance',
                'children' => [
                    ['id' => 'performance', 'label' => 'Store Health', 'url' => 'performance'],
                    ['id' => 'performance-seller', 'label' => 'Seller Tier Score', 'url' => 'performance-seller'],
                    ['id' => 'performance-csat', 'label' => 'Customer Feedback', 'url' => 'performance-csat'],
                ],
            ],
            [
                'id' => 'b2b',
                'label' => 'B2B Wholesale',
                'group' => 'B2B & Ecosystem',
                'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>',
                'url' => 'b2b',
                'children' => [
                    ['id' => 'b2b', 'label' => 'Business Pricing', 'url' => 'b2b'],
                    ['id' => 'b2b-orders', 'label' => 'Bulk Orders', 'url' => 'b2b-orders'],
                    ['id' => 'b2b-customers', 'label' => 'Business Accounts', 'url' => 'b2b-customers'],
                    ['id' => 'b2b-offers', 'label' => 'RFQ & Quotations', 'url' => 'b2b-offers'],
                ],
            ],
            [
                'id' => 'brands',
                'label' => 'Brands',
                'group' => 'B2B & Ecosystem',
                'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>',
                'url' => 'brands',
                'children' => [
                    ['id' => 'brands', 'label' => 'My Registered Brands', 'url' => 'brands'],
                    ['id' => 'brands-assets', 'label' => 'Brand Assets', 'url' => 'brands-assets'],
                    ['id' => 'brands-protection', 'label' => 'Brand Registry & IP', 'url' => 'brands-protection'],
                ],
            ],
            [
                'id' => 'apps',
                'label' => 'Apps & Services',
                'group' => 'B2B & Ecosystem',
                'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>',
                'url' => 'apps',
                'children' => [
                    ['id' => 'apps', 'label' => 'Seller Tools', 'url' => 'apps'],
                    ['id' => 'apps-integrations', 'label' => 'Logistics & Payments', 'url' => 'apps-integrations'],
                    ['id' => 'apps-automation', 'label' => 'Automations', 'url' => 'apps-automation'],
                ],
            ],
            [
                'id' => 'learn',
                'label' => 'Seller Learn',
                'group' => 'Support & Store',
                'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/><path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/></svg>',
                'url' => 'learn',
                'children' => [
                    ['id' => 'learn', 'label' => 'Seller University', 'url' => 'learn'],
                    ['id' => 'learn-kb', 'label' => 'Knowledgebase', 'url' => 'learn-kb'],
                    ['id' => 'learn-guides', 'label' => 'Listing Guides', 'url' => 'learn-guides'],
                    ['id' => 'learn-policies', 'label' => 'Marketplace Policies', 'url' => 'learn-policies'],
                ],
            ],
            [
                'id' => 'settings',
                'label' => 'Settings',
                'group' => 'Support & Store',
                'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.32 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>',
                'url' => 'store',
                'children' => [
                    ['id' => 'store', 'label' => 'Store Settings', 'url' => 'store'],
                    ['id' => 'settings', 'label' => 'Account Info', 'url' => 'settings'],
                    ['id' => 'settings-tax', 'label' => 'GST & Taxes', 'url' => 'settings-tax'],
                    ['id' => 'shipping', 'label' => 'Shipping Settings', 'url' => 'shipping'],
                    ['id' => 'notifications', 'label' => 'Notifications', 'url' => 'notifications'],
                    ['id' => 'support', 'label' => 'Support Desk', 'url' => 'support'],
                ],
            ],
        ];

        return $items;
    }

    /**
     * Get current active section from request parameter
     */
    public static function get_current_section() {
        if (!empty($_GET['section'])) {
            return sanitize_text_field($_GET['section']);
        }
        if (!empty($_GET['seller-hub'])) {
            return sanitize_text_field($_GET['seller-hub']);
        }
        return 'dashboard';
    }

    /**
     * Get configuration array for a section
     */
    public static function get_current_section_config($section) {
        if (isset(self::$sections[$section])) {
            return self::$sections[$section];
        }
        return null;
    }

    /**
     * Get all registered sections
     */
    public static function get_all_sections() {
        return self::$sections;
    }
}

