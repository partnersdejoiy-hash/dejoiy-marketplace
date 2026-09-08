<?php
/**
 * DSO Dashboard - Command Center
 * Pulls real data from WooCommerce + WCFM
 */
if (!defined('ABSPATH')) exit;

class DSO_Dashboard {

    public function render() {
        $user_id = get_current_user_id();
        $store = DSO_Auth::get_vendor_store($user_id);
        $data = $this->get_dashboard_data($user_id);
        $store_health = $this->get_store_health($user_id);
        $greeting = $this->get_greeting();
        $vendor_name = $store ? $store['name'] : get_userdata($user_id)->display_name;

        ?>
        <div class="dso-page dso-dashboard" id="dso-dashboard">
            <!-- Personalized Greeting -->
            <div class="dso-greeting">
                <div class="dso-greeting-text">
                    <h1><?php echo esc_html($greeting) ?>, <?php echo esc_html($vendor_name) ?> 👋</h1>
                    <p>Here's what's happening with your store today. <span class="dso-date"><?php echo date('l, F j, Y') ?></span></p>
                </div>
                <div class="dso-greeting-actions">
                    <a href="?section=add-product" class="dso-btn dso-btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Add Product
                    </a>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="dso-kpi-grid">
                <?php $this->render_kpi_card([
                    'label' => 'Today\'s Sales',
                    'value' => wc_price($data['today_sales']),
                    'trend' => $data['sales_trend'],
                    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>',
                    'color' => 'green',
                    'url' => '?section=analytics',
                ]); ?>
                <?php $this->render_kpi_card([
                    'label' => 'Orders',
                    'value' => $data['total_orders'],
                    'trend' => $data['orders_trend'],
                    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>',
                    'color' => 'blue',
                    'url' => '?section=orders',
                ]); ?>
                <?php $this->render_kpi_card([
                    'label' => 'Products',
                    'value' => $data['total_products'],
                    'subtitle' => $data['low_stock_count'] . ' low stock',
                    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 002 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0022 16z"/></svg>',
                    'color' => 'purple',
                    'url' => '?section=products',
                ]); ?>
                <?php $this->render_kpi_card([
                    'label' => 'Pending Orders',
                    'value' => $data['pending_orders'],
                    'subtitle' => $data['processing_orders'] . ' processing',
                    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
                    'color' => $data['pending_orders'] > 0 ? 'orange' : 'green',
                    'url' => '?section=orders',
                ]); ?>
                <?php $this->render_kpi_card([
                    'label' => 'Balance',
                    'value' => wc_price($data['available_balance']),
                    'subtitle' => wc_price($data['pending_balance']) . ' pending',
                    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',
                    'color' => 'teal',
                    'url' => '?section=finance',
                ]); ?>
                <?php $this->render_kpi_card([
                    'label' => 'Store Rating',
                    'value' => $data['store_rating'] > 0 ? $data['store_rating'] . '/5' : 'N/A',
                    'subtitle' => $data['total_reviews'] . ' reviews',
                    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
                    'color' => 'yellow',
                    'url' => '?section=reviews',
                ]); ?>
            </div>

            <!-- Sales Chart + Business Snapshot -->
            <div class="dso-grid-2-1">
                <!-- Sales Chart -->
                <div class="dso-card dso-card-chart">
                    <div class="dso-card-header">
                        <h3>Sales Overview</h3>
                        <div class="dso-chart-filters">
                            <button class="dso-filter-btn active" data-period="7d">7D</button>
                            <button class="dso-filter-btn" data-period="30d">30D</button>
                            <button class="dso-filter-btn" data-period="90d">90D</button>
                            <button class="dso-filter-btn" data-period="1y">1Y</button>
                        </div>
                    </div>
                    <div class="dso-chart-container">
                        <canvas id="dso-sales-chart"></canvas>
                    </div>
                </div>

                <!-- Business Snapshot -->
                <div class="dso-card dso-card-snapshot">
                    <div class="dso-card-header">
                        <h3>Business Snapshot</h3>
                    </div>
                    <div class="dso-snapshot-list">
                        <?php $this->render_snapshot_item('Pending Orders', $data['pending_orders'], '?section=orders', 'orange'); ?>
                        <?php $this->render_snapshot_item('Low Stock', $data['low_stock_count'], '?section=inventory', 'yellow'); ?>
                        <?php $this->render_snapshot_item('Out of Stock', $data['out_of_stock_count'], '?section=inventory', 'red'); ?>
                        <?php $this->render_snapshot_item('Refund Requests', $data['refund_requests'], '?section=orders', 'purple'); ?>
                        <?php $this->render_snapshot_item('Pending Reviews', $data['pending_reviews'], '?section=reviews', 'blue'); ?>
                        <?php $this->render_snapshot_item('Unread Messages', $data['unread_messages'], '?section=notifications', 'teal'); ?>
                    </div>
                </div>
            </div>

            <!-- Recent Orders + Top Products -->
            <div class="dso-grid-2">
                <!-- Recent Orders -->
                <div class="dso-card">
                    <div class="dso-card-header">
                        <h3>Recent Orders</h3>
                        <a href="?section=orders" class="dso-link">View All →</a>
                    </div>
                    <div class="dso-table-responsive">
                        <table class="dso-table">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($data['recent_orders'])): ?>
                                    <tr class="dso-empty-row">
                                        <td colspan="4">
                                            <div class="dso-empty-inline">
                                                <p>No orders yet</p>
                                                <span>Once your first order comes in, it will appear here.</span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($data['recent_orders'] as $order): ?>
                                        <tr>
                                            <td>
                                                <a href="?section=order-detail&id=<?php echo $order['id'] ?>">
                                                    #<?php echo $order['number'] ?>
                                                </a>
                                            </td>
                                            <td><?php echo esc_html($order['customer']) ?></td>
                                            <td><?php echo $order['total'] ?></td>
                                            <td><?php echo $order['status_badge'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Top Products -->
                <div class="dso-card">
                    <div class="dso-card-header">
                        <h3>Top Products</h3>
                        <a href="?section=products" class="dso-link">View All →</a>
                    </div>
                    <div class="dso-top-products">
                        <?php if (empty($data['top_products'])): ?>
                            <div class="dso-empty-inline">
                                <p>No product data yet</p>
                                <span>Add your first product to start tracking sales.</span>
                            </div>
                        <?php else: ?>
                            <?php foreach ($data['top_products'] as $product): ?>
                                <div class="dso-top-product-item">
                                    <div class="dso-top-product-img">
                                        <?php echo $product['image'] ?>
                                    </div>
                                    <div class="dso-top-product-info">
                                        <h4><?php echo esc_html($product['name']) ?></h4>
                                        <span><?php echo $product['sales'] ?> sold</span>
                                    </div>
                                    <div class="dso-top-product-revenue">
                                        <?php echo $product['revenue'] ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Store Health -->
            <?php if (!empty($store_health)): ?>
            <div class="dso-card dso-card-health">
                <div class="dso-card-header">
                    <h3>Store Health</h3>
                    <span class="dso-health-score <?php echo $store_health['score'] >= 80 ? 'dso-health-good' : ($store_health['score'] >= 50 ? 'dso-health-warn' : 'dso-health-bad') ?>">
                        <?php echo $store_health['score'] ?>%
                    </span>
                </div>
                <div class="dso-health-grid">
                    <?php foreach ($store_health['items'] as $item): ?>
                        <div class="dso-health-item">
                            <div class="dso-health-item-header">
                                <span><?php echo esc_html($item['label']) ?></span>
                                <span class="dso-health-item-value"><?php echo $item['value'] ?></span>
                            </div>
                            <div class="dso-health-bar">
                                <div class="dso-health-bar-fill" style="width: <?php echo $item['percent'] ?>%; background: <?php echo $item['color'] ?>"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Smart Recommendations -->
            <?php if (!empty($data['recommendations'])): ?>
            <div class="dso-card dso-card-recommendations">
                <div class="dso-card-header">
                    <h3>💡 Recommendations</h3>
                </div>
                <div class="dso-recommendations-list">
                    <?php foreach ($data['recommendations'] as $rec): ?>
                        <div class="dso-recommendation">
                            <span class="dso-rec-icon"><?php echo $rec['icon'] ?></span>
                            <div class="dso-rec-text">
                                <p><?php echo esc_html($rec['message']) ?></p>
                                <?php if (!empty($rec['action_url'])): ?>
                                    <a href="<?php echo esc_url($rec['action_url']) ?>" class="dso-rec-link">Take Action →</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            DSO.initDashboard(<?php echo wp_json_encode($data['chart_data']) ?>);
        });
        </script>
        <?php
    }

    /**
     * Get all dashboard data from real WooCommerce/WCFM data
     */
    public function get_dashboard_data($user_id) {
        $plugin = Dejoiy_Seller_OS::instance();
        $vendor_id = $plugin->get_vendor_id($user_id);

        $today = current_time('mysql', false);
        $today_start = date('Y-m-d 00:00:00');
        $today_end = date('Y-m-d 23:59:59');
        $yesterday_start = date('Y-m-d 00:00:00', strtotime('-1 day'));
        $yesterday_end = date('Y-m-d 23:59:59', strtotime('-1 day'));

        // Today's sales
        $today_sales = $this->get_vendor_sales($vendor_id, $today_start, $today_end);
        $yesterday_sales = $this->get_vendor_sales($vendor_id, $yesterday_start, $yesterday_end);
        $sales_trend = $yesterday_sales > 0 ? round((($today_sales - $yesterday_sales) / $yesterday_sales) * 100, 1) : 0;

        // Orders
        $total_orders = $this->get_vendor_order_count($vendor_id);
        $pending_orders = $this->get_vendor_order_count($vendor_id, ['wc-pending', 'wc-on-hold']);
        $processing_orders = $this->get_vendor_order_count($vendor_id, ['wc-processing']);
        $yesterday_orders = $this->get_vendor_order_count($vendor_id, null, $yesterday_start, $yesterday_end);
        $orders_trend = $yesterday_orders > 0 ? round((($total_orders - $yesterday_orders) / max($yesterday_orders, 1)) * 100, 1) : 0;

        // Products
        $total_products = $this->get_vendor_product_count($vendor_id, ['publish']);
        $low_stock_count = $this->get_vendor_low_stock_count($vendor_id);
        $out_of_stock_count = $this->get_vendor_out_of_stock_count($vendor_id);

        // Finance
        $balance = $this->get_vendor_balance($vendor_id);
        $available_balance = $balance['available'];
        $pending_balance = $balance['pending'];

        // Reviews
        $store_rating = $this->get_vendor_rating($vendor_id);
        $total_reviews = $this->get_vendor_review_count($vendor_id);
        $pending_reviews = $this->get_vendor_pending_review_count($vendor_id);

        // Refunds
        $refund_requests = $this->get_vendor_refund_count($vendor_id);

        // Messages
        $unread_messages = $this->get_vendor_unread_messages($user_id);

        // Recent orders
        $recent_orders = $this->get_recent_orders($vendor_id, 5);

        // Top products
        $top_products = $this->get_top_products($vendor_id, 5);

        // Chart data (last 7 days)
        $chart_data = $this->get_chart_data($vendor_id, 7);

        // Recommendations
        $recommendations = $this->get_recommendations($vendor_id, $low_stock_count, $pending_orders, $out_of_stock_count, $total_products);

        return compact(
            'today_sales', 'sales_trend', 'total_orders', 'orders_trend',
            'pending_orders', 'processing_orders', 'total_products',
            'low_stock_count', 'out_of_stock_count', 'available_balance',
            'pending_balance', 'store_rating', 'total_reviews', 'pending_reviews',
            'refund_requests', 'unread_messages', 'recent_orders', 'top_products',
            'chart_data', 'recommendations'
        );
    }

    /**
     * Get vendor sales for a date range
     */
    private function get_vendor_sales($vendor_id, $start, $end = null) {
        global $wpdb;

        if (!$end) $end = current_time('mysql', false);

        // Use WCFM marketplace orders
        $sales = $wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(SUM(order_total), 0)
            FROM {$wpdb->prefix}wcfm_marketplace_orders
            WHERE vendor_id = %d
            AND order_status IN ('wc-completed', 'wc-processing')
            AND order_date >= %s AND order_date <= %s",
            $vendor_id, $start, $end
        ));

        return floatval($sales);
    }

    /**
     * Get vendor order count
     */
    private function get_vendor_order_count($vendor_id, $statuses = null, $start = null, $end = null) {
        global $wpdb;

        $where = "WHERE vendor_id = %d";
        $params = [$vendor_id];

        if ($statuses) {
            $placeholders = implode(',', array_fill(0, count($statuses), '%s'));
            $where .= " AND order_status IN ($placeholders)";
            $params = array_merge($params, $statuses);
        } else {
            $where .= " AND order_status NOT IN ('wc-cancelled', 'wc-trash')";
        }

        if ($start) {
            $where .= " AND order_date >= %s";
            $params[] = $start;
        }
        if ($end) {
            $where .= " AND order_date <= %s";
            $params[] = $end;
        }

        return intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}wcfm_marketplace_orders $where",
            ...$params
        )));
    }

    /**
     * Get vendor product count
     */
    private function get_vendor_product_count($vendor_id, $statuses = ['publish']) {
        global $wpdb;

        if (!$vendor_id) return 0;

        $placeholders = implode(',', array_fill(0, count($statuses), '%s'));
        $params = array_merge([$vendor_id], $statuses);

        return intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT post_id) FROM {$wpdb->prefix}postmeta
            WHERE meta_key = '_vendor_id' AND meta_value = %d
            AND post_id IN (
                SELECT ID FROM {$wpdb->prefix}posts
                WHERE post_type = 'product' AND post_status IN ($placeholders)
            )",
            ...$params
        )));
    }

    /**
     * Get low stock count
     */
    private function get_vendor_low_stock_count($vendor_id) {
        global $wpdb;

        if (!$vendor_id) return 0;

        $low_stock_threshold = get_option('woocommerce_notify_low_stock_amount', 2);

        return intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT pm.post_id)
            FROM {$wpdb->prefix}postmeta pm
            INNER JOIN {$wpdb->prefix}postmeta stock ON pm.post_id = stock.post_id AND stock.meta_key = '_stock'
            INNER JOIN {$wpdb->prefix}postmeta manage ON pm.post_id = manage.post_id AND manage.meta_key = '_manage_stock' AND manage.meta_value = 'yes'
            WHERE pm.meta_key = '_vendor_id' AND pm.meta_value = %d
            AND stock.meta_value <= %d AND stock.meta_value > 0",
            $vendor_id, $low_stock_threshold
        )));
    }

    /**
     * Get out of stock count
     */
    private function get_vendor_out_of_stock_count($vendor_id) {
        global $wpdb;

        if (!$vendor_id) return 0;

        return intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT pm.post_id)
            FROM {$wpdb->prefix}postmeta pm
            INNER JOIN {$wpdb->prefix}postmeta stock ON pm.post_id = stock.post_id AND stock.meta_key = '_stock_status'
            WHERE pm.meta_key = '_vendor_id' AND pm.meta_value = %d
            AND stock.meta_value = 'outofstock'",
            $vendor_id
        )));
    }

    /**
     * Get vendor balance from WCFM ledger
     */
    private function get_vendor_balance($vendor_id) {
        global $wpdb;

        $available = 0;
        $pending = 0;

        if ($vendor_id) {
            $last_balance = $wpdb->get_var($wpdb->prepare(
                "SELECT credit - debit FROM {$wpdb->prefix}wcfm_marketplace_vendor_ledger
                WHERE vendor_id = %d ORDER BY id DESC LIMIT 1",
                $vendor_id
            ));
            $available = floatval($last_balance);

            // Get pending from withdraw requests
            $pending = floatval($wpdb->get_var($wpdb->prepare(
                "SELECT COALESCE(SUM(amount), 0) FROM {$wpdb->prefix}wcfm_marketplace_withdraw_request
                WHERE vendor_id = %d AND status IN (0, 1)",
                $vendor_id
            )));
        }

        return ['available' => max($available, 0), 'pending' => $pending];
    }

    /**
     * Get vendor store rating
     */
    private function get_vendor_rating($vendor_id) {
        global $wpdb;

        if (!$vendor_id) return 0;

        $rating = $wpdb->get_var($wpdb->prepare(
            "SELECT AVG(meta_value) FROM {$wpdb->prefix}wcfm_marketplace_review_rating_meta
            WHERE vendor_id = %d",
            $vendor_id
        ));

        return $rating ? round(floatval($rating), 1) : 0;
    }

    /**
     * Get vendor review count
     */
    private function get_vendor_review_count($vendor_id) {
        global $wpdb;

        if (!$vendor_id) return 0;

        return intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}wcfm_marketplace_reviews
            WHERE vendor_id = %d AND approved = 1",
            $vendor_id
        )));
    }

    /**
     * Get pending review count
     */
    private function get_vendor_pending_review_count($vendor_id) {
        global $wpdb;

        if (!$vendor_id) return 0;

        return intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}wcfm_marketplace_reviews
            WHERE vendor_id = %d AND approved = 0",
            $vendor_id
        )));
    }

    /**
     * Get refund request count
     */
    private function get_vendor_refund_count($vendor_id) {
        global $wpdb;

        if (!$vendor_id) return 0;

        return intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}wcfm_marketplace_refund_request
            WHERE vendor_id = %d AND status IN (0, 1)",
            $vendor_id
        )));
    }

    /**
     * Get unread messages count
     */
    private function get_vendor_unread_messages($user_id) {
        global $wpdb;

        return intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}wcfm_messages
            WHERE receiver = %d AND is_read = 0",
            $user_id
        )));
    }

    /**
     * Get recent orders for display
     */
    private function get_recent_orders($vendor_id, $limit = 5) {
        global $wpdb;

        if (!$vendor_id) return [];

        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT order_id, order_status, order_total, customer_name, order_date
            FROM {$wpdb->prefix}wcfm_marketplace_orders
            WHERE vendor_id = %d
            ORDER BY order_date DESC LIMIT %d",
            $vendor_id, $limit
        ));

        $orders = [];
        foreach ($rows as $row) {
            $order = wc_get_order($row->order_id);
            $orders[] = [
                'id' => $row->order_id,
                'number' => $order ? $order->get_order_number() : $row->order_id,
                'customer' => $row->customer_name ?: 'Guest',
                'total' => wc_price($row->order_total),
                'status_badge' => $this->get_status_badge($row->order_status),
                'date' => $row->order_date,
            ];
        }

        return $orders;
    }

    /**
     * Get top products
     */
    private function get_top_products($vendor_id, $limit = 5) {
        global $wpdb;

        if (!$vendor_id) return [];

        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT product_id, SUM(quantity) as total_sold, SUM(order_total) as total_revenue
            FROM {$wpdb->prefix}wcfm_marketplace_orders
            WHERE vendor_id = %d AND order_status IN ('wc-completed', 'wc-processing')
            GROUP BY product_id
            ORDER BY total_sold DESC
            LIMIT %d",
            $vendor_id, $limit
        ));

        $products = [];
        foreach ($rows as $row) {
            $product = wc_get_product($row->product_id);
            if (!$product) continue;

            $image = get_the_post_thumbnail($row->product_id, [40, 40]);
            if (!$image) {
                $image = '<div class="dso-product-placeholder-img"></div>';
            }

            $products[] = [
                'name' => $product->get_name(),
                'sales' => intval($row->total_sold),
                'revenue' => wc_price($row->total_revenue),
                'image' => $image,
            ];
        }

        return $products;
    }

    /**
     * Get chart data for sales visualization
     */
    private function get_chart_data($vendor_id, $days = 7) {
        global $wpdb;

        $labels = [];
        $sales_data = [];
        $orders_data = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $labels[] = date('M j', strtotime($date));

            $start = $date . ' 00:00:00';
            $end = $date . ' 23:59:59';

            $row = $wpdb->get_row($wpdb->prepare(
                "SELECT COALESCE(SUM(order_total), 0) as sales, COUNT(*) as orders
                FROM {$wpdb->prefix}wcfm_marketplace_orders
                WHERE vendor_id = %d
                AND order_status IN ('wc-completed', 'wc-processing')
                AND order_date >= %s AND order_date <= %s",
                $vendor_id, $start, $end
            ));

            $sales_data[] = floatval($row->sales ?? 0);
            $orders_data[] = intval($row->orders ?? 0);
        }

        return [
            'labels' => $labels,
            'sales' => $sales_data,
            'orders' => $orders_data,
        ];
    }

    /**
     * Get smart recommendations based on real data
     */
    private function get_recommendations($vendor_id, $low_stock, $pending_orders, $out_of_stock, $total_products) {
        $recs = [];

        if ($low_stock > 0) {
            $recs[] = [
                'icon' => '⚠️',
                'message' => "{$low_stock} product(s) are running low on stock. Restock soon to avoid missed sales.",
                'action_url' => '?section=inventory',
            ];
        }

        if ($out_of_stock > 0) {
            $recs[] = [
                'icon' => '🚫',
                'message' => "{$out_of_stock} product(s) are out of stock. Update inventory or hide them.",
                'action_url' => '?section=inventory',
            ];
        }

        if ($pending_orders > 0) {
            $recs[] = [
                'icon' => '📦',
                'message' => "{$pending_orders} order(s) need your attention. Process them to keep customers happy.",
                'action_url' => '?section=orders',
            ];
        }

        if ($total_products === 0) {
            $recs[] = [
                'icon' => '🎯',
                'message' => "You haven't added any products yet. Add your first product to start selling!",
                'action_url' => '?section=add-product',
            ];
        }

        if ($vendor_id) {
            $store = get_post($vendor_id);
            if ($store && empty($store->post_content)) {
                $recs[] = [
                    'icon' => '📝',
                    'message' => "Complete your store description to attract more customers.",
                    'action_url' => '?section=store',
                ];
            }
        }

        return $recs;
    }

    /**
     * Get store health metrics
     */
    public function get_store_health($user_id) {
        $plugin = Dejoiy_Seller_OS::instance();
        $vendor_id = $plugin->get_vendor_id($user_id);
        $store = DSO_Auth::get_vendor_store($user_id);

        if (!$vendor_id) return null;

        $items = [];
        $total_score = 0;

        // Profile completion
        $profile_score = 0;
        if (!empty($store['name'])) $profile_score += 25;
        if (!empty($store['description'])) $profile_score += 25;
        if (!empty($store['logo'])) $profile_score += 25;
        if (!empty($store['email'])) $profile_score += 25;

        $items[] = [
            'label' => 'Profile',
            'value' => $profile_score . '%',
            'percent' => $profile_score,
            'color' => $profile_score >= 75 ? '#10b981' : ($profile_score >= 50 ? '#f59e0b' : '#ef4444'),
        ];
        $total_score += $profile_score;

        // Products
        $product_count = $this->get_vendor_product_count($vendor_id, ['publish']);
        $product_score = min(100, $product_count * 10);
        $items[] = [
            'label' => 'Products',
            'value' => $product_count . ' listed',
            'percent' => $product_score,
            'color' => $product_score >= 70 ? '#10b981' : ($product_score >= 30 ? '#f59e0b' : '#ef4444'),
        ];
        $total_score += $product_score;

        // Fulfillment rate
        $completed = $this->get_vendor_order_count($vendor_id, ['wc-completed']);
        $total = $this->get_vendor_order_count($vendor_id);
        $fulfillment = $total > 0 ? round(($completed / $total) * 100) : 100;
        $items[] = [
            'label' => 'Fulfillment',
            'value' => $fulfillment . '%',
            'percent' => $fulfillment,
            'color' => $fulfillment >= 90 ? '#10b981' : ($fulfillment >= 70 ? '#f59e0b' : '#ef4444'),
        ];
        $total_score += $fulfillment;

        // Reviews
        $rating = $this->get_vendor_rating($vendor_id);
        $review_score = $rating > 0 ? ($rating / 5) * 100 : 50;
        $items[] = [
            'label' => 'Reviews',
            'value' => $rating > 0 ? $rating . '/5' : 'No reviews',
            'percent' => $review_score,
            'color' => $review_score >= 80 ? '#10b981' : ($review_score >= 60 ? '#f59e0b' : '#ef4444'),
        ];
        $total_score += $review_score;

        // Inventory health
        $low_stock = $this->get_vendor_low_stock_count($vendor_id);
        $out_stock = $this->get_vendor_out_of_stock_count($vendor_id);
        $inventory_score = $out_stock === 0 && $low_stock === 0 ? 100 : max(0, 100 - ($out_stock * 20) - ($low_stock * 10));
        $items[] = [
            'label' => 'Inventory',
            'value' => $out_stock . ' out, ' . $low_stock . ' low',
            'percent' => $inventory_score,
            'color' => $inventory_score >= 80 ? '#10b981' : ($inventory_score >= 50 ? '#f59e0b' : '#ef4444'),
        ];
        $total_score += $inventory_score;

        $avg_score = round($total_score / count($items));

        return [
            'score' => $avg_score,
            'items' => $items,
        ];
    }

    /**
     * Get time-based greeting
     */
    private function get_greeting() {
        $hour = (int) current_time('G');
        if ($hour < 12) return 'Good morning';
        if ($hour < 17) return 'Good afternoon';
        return 'Good evening';
    }

    /**
     * Get status badge HTML
     */
    private function get_status_badge($status) {
        $map = [
            'wc-pending' => ['Pending', 'dso-badge-orange'],
            'wc-processing' => ['Processing', 'dso-badge-blue'],
            'wc-on-hold' => ['On Hold', 'dso-badge-yellow'],
            'wc-completed' => ['Completed', 'dso-badge-green'],
            'wc-cancelled' => ['Cancelled', 'dso-badge-red'],
            'wc-refunded' => ['Refunded', 'dso-badge-purple'],
            'wc-failed' => ['Failed', 'dso-badge-red'],
        ];

        $label = $map[$status][0] ?? ucfirst(str_replace('wc-', '', $status));
        $class = $map[$status][1] ?? 'dso-badge-gray';

        return '<span class="dso-badge ' . $class . '">' . esc_html($label) . '</span>';
    }

    /**
     * Render a single KPI card
     */
    private function render_kpi_card($args) {
        $trend_html = '';
        if (isset($args['trend']) && $args['trend'] != 0) {
            $direction = $args['trend'] > 0 ? 'up' : 'down';
            $trend_html = '<span class="dso-kpi-trend dso-trend-' . $direction . '">';
            $trend_html .= ($args['trend'] > 0 ? '↑' : '↓') . ' ' . abs($args['trend']) . '%';
            $trend_html .= '</span>';
        }

        $subtitle_html = isset($args['subtitle']) ? '<span class="dso-kpi-subtitle">' . esc_html($args['subtitle']) . '</span>' : '';
        ?>
        <a href="<?php echo esc_url($args['url'] ?? '#') ?>" class="dso-kpi-card dso-kpi-<?php echo esc_attr($args['color'] ?? 'blue') ?>">
            <div class="dso-kpi-icon"><?php echo $args['icon'] ?></div>
            <div class="dso-kpi-content">
                <span class="dso-kpi-label"><?php echo esc_html($args['label']) ?></span>
                <span class="dso-kpi-value"><?php echo $args['value'] ?></span>
                <div class="dso-kpi-meta">
                    <?php echo $trend_html ?>
                    <?php echo $subtitle_html ?>
                </div>
            </div>
        </a>
        <?php
    }

    /**
     * Render a snapshot item
     */
    private function render_snapshot_item($label, $count, $url, $color) {
        ?>
        <a href="<?php echo esc_url($url) ?>" class="dso-snapshot-item">
            <span class="dso-snapshot-dot dso-dot-<?php echo esc_attr($color) ?>"></span>
            <span class="dso-snapshot-label"><?php echo esc_html($label) ?></span>
            <span class="dso-snapshot-count"><?php echo intval($count) ?></span>
        </a>
        <?php
    }
}
