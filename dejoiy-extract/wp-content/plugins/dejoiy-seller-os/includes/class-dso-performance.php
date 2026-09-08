<?php
/**
 * DSO Performance - Store Performance Metrics
 */
if (!defined('ABSPATH')) exit;

class DSO_Performance {

    public function render() {
        $user_id = get_current_user_id();
        $plugin = Dejoiy_Seller_OS::instance();
        $vendor_id = $plugin->get_vendor_id($user_id);
        $data = $this->get_performance_data($vendor_id);

        ?>
        <div class="dso-page dso-performance">
            <div class="dso-page-header">
                <div>
                    <h1>Store Performance</h1>
                    <p>Track your store's key performance indicators</p>
                </div>
            </div>

            <!-- Performance KPIs -->
            <div class="dso-kpi-grid dso-kpi-grid-4">
                <div class="dso-kpi-card dso-kpi-green">
                    <div class="dso-kpi-content">
                        <span class="dso-kpi-label">Revenue</span>
                        <span class="dso-kpi-value"><?php echo wc_price($data['revenue']) ?></span>
                        <span class="dso-kpi-sub"><?php echo $data['period'] ?></span>
                    </div>
                </div>
                <div class="dso-kpi-card dso-kpi-blue">
                    <div class="dso-kpi-content">
                        <span class="dso-kpi-label">Orders</span>
                        <span class="dso-kpi-value"><?php echo $data['orders'] ?></span>
                    </div>
                </div>
                <div class="dso-kpi-card dso-kpi-purple">
                    <div class="dso-kpi-content">
                        <span class="dso-kpi-label">Avg Order Value</span>
                        <span class="dso-kpi-value"><?php echo wc_price($data['aov']) ?></span>
                    </div>
                </div>
                <div class="dso-kpi-card dso-kpi-teal">
                    <div class="dso-kpi-content">
                        <span class="dso-kpi-label">Store Rating</span>
                        <span class="dso-kpi-value"><?php echo $data['rating'] > 0 ? $data['rating'].'/5' : 'N/A' ?></span>
                        <span class="dso-kpi-sub"><?php echo $data['review_count'] ?> reviews</span>
                    </div>
                </div>
            </div>

            <!-- Performance Chart -->
            <div class="dso-card dso-card-chart">
                <div class="dso-card-header"><h3>Revenue & Orders Trend</h3></div>
                <div class="dso-chart-container">
                    <canvas id="dso-perf-chart"></canvas>
                </div>
            </div>

            <!-- Performance Metrics -->
            <div class="dso-grid-2">
                <div class="dso-card">
                    <div class="dso-card-header"><h3>Fulfillment Metrics</h3></div>
                    <div class="dso-card-body">
                        <div class="dso-info-list">
                            <div class="dso-info-item"><span class="dso-info-label">Completion Rate</span><span><?php echo $data['completion_rate'] ?>%</span></div>
                            <div class="dso-info-item"><span class="dso-info-label">Avg Processing Time</span><span><?php echo $data['avg_processing'] ?></span></div>
                            <div class="dso-info-item"><span class="dso-info-label">Cancellation Rate</span><span><?php echo $data['cancel_rate'] ?>%</span></div>
                        </div>
                    </div>
                </div>
                <div class="dso-card">
                    <div class="dso-card-header"><h3>Customer Metrics</h3></div>
                    <div class="dso-card-body">
                        <div class="dso-info-list">
                            <div class="dso-info-item"><span class="dso-info-label">Total Customers</span><span><?php echo $data['customers'] ?></span></div>
                            <div class="dso-info-item"><span class="dso-info-label">Repeat Customers</span><span><?php echo $data['repeat_customers'] ?></span></div>
                            <div class="dso-info-item"><span class="dso-info-label">Refund Rate</span><span><?php echo $data['refund_rate'] ?>%</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    private function get_performance_data($vendor_id) {
        global $wpdb;
        $data = [
            'revenue' => 0, 'orders' => 0, 'aov' => 0, 'rating' => 0,
            'review_count' => 0, 'completion_rate' => 0, 'avg_processing' => '—',
            'cancel_rate' => 0, 'customers' => 0, 'repeat_customers' => 0,
            'refund_rate' => 0, 'period' => 'All time', 'chart_data' => ['labels' => [], 'revenue' => [], 'orders' => []],
        ];

        if (!$vendor_id) return $data;

        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT COALESCE(SUM(order_total), 0) as revenue, COUNT(*) as orders
            FROM {$wpdb->prefix}wcfm_marketplace_orders
            WHERE vendor_id = %d AND order_status IN ('wc-completed', 'wc-processing')",
            $vendor_id
        ));
        $data['revenue'] = floatval($row->revenue ?? 0);
        $data['orders'] = intval($row->orders ?? 0);
        $data['aov'] = $data['orders'] > 0 ? $data['revenue'] / $data['orders'] : 0;

        // Rating
        $rating = $wpdb->get_var($wpdb->prepare(
            "SELECT AVG(meta_value) FROM {$wpdb->prefix}wcfm_marketplace_review_rating_meta WHERE vendor_id = %d", $vendor_id
        ));
        $data['rating'] = $rating ? round(floatval($rating), 1) : 0;
        $data['review_count'] = intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}wcfm_marketplace_reviews WHERE vendor_id = %d AND approved = 1", $vendor_id
        )));

        // Completion rate
        $completed = intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}wcfm_marketplace_orders WHERE vendor_id = %d AND order_status = 'wc-completed'", $vendor_id
        )));
        $data['completion_rate'] = $data['orders'] > 0 ? round(($completed / $data['orders']) * 100) : 0;

        // Cancel rate
        $cancelled = intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}wcfm_marketplace_orders WHERE vendor_id = %d AND order_status = 'wc-cancelled'", $vendor_id
        )));
        $data['cancel_rate'] = $data['orders'] > 0 ? round(($cancelled / $data['orders']) * 100) : 0;

        // Customers
        $data['customers'] = intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT customer_email) FROM {$wpdb->prefix}wcfm_marketplace_orders WHERE vendor_id = %d AND customer_email != ''", $vendor_id
        )));

        // Chart
        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $data['chart_data']['labels'][] = date('M j', strtotime($date));
            $cr = $wpdb->get_row($wpdb->prepare(
                "SELECT COALESCE(SUM(order_total), 0) as sales, COUNT(*) as orders
                FROM {$wpdb->prefix}wcfm_marketplace_orders
                WHERE vendor_id = %d AND order_status IN ('wc-completed', 'wc-processing')
                AND order_date >= %s AND order_date <= %s",
                $vendor_id, $date.' 00:00:00', $date.' 23:59:59'
            ));
            $data['chart_data']['revenue'][] = floatval($cr->sales ?? 0);
            $data['chart_data']['orders'][] = intval($cr->orders ?? 0);
        }

        return $data;
    }
}
