<?php
/**
 * DSO Analytics
 */
if (!defined('ABSPATH')) exit;

class DSO_Analytics {

    public function render() {
        $user_id = get_current_user_id();
        $plugin = Dejoiy_Seller_OS::instance();
        $vendor_id = $plugin->get_vendor_id($user_id);
        $data = $this->get_analytics_data($vendor_id);

        ?>
        <div class="dso-page dso-analytics">
            <div class="dso-page-header">
                <div>
                    <h1>Analytics</h1>
                    <p>Insights into your store performance</p>
                </div>
                <div class="dso-page-actions">
                    <div class="dso-chart-filters">
                        <button class="dso-filter-btn active" data-period="7d">7D</button>
                        <button class="dso-filter-btn" data-period="30d">30D</button>
                        <button class="dso-filter-btn" data-period="90d">90D</button>
                        <button class="dso-filter-btn" data-period="1y">1Y</button>
                    </div>
                </div>
            </div>

            <!-- KPI Row -->
            <div class="dso-kpi-grid dso-kpi-grid-4">
                <div class="dso-kpi-card dso-kpi-blue">
                    <div class="dso-kpi-content">
                        <span class="dso-kpi-label">Total Revenue</span>
                        <span class="dso-kpi-value"><?php echo wc_price($data['total_revenue']) ?></span>
                    </div>
                </div>
                <div class="dso-kpi-card dso-kpi-green">
                    <div class="dso-kpi-content">
                        <span class="dso-kpi-label">Total Orders</span>
                        <span class="dso-kpi-value"><?php echo $data['total_orders'] ?></span>
                    </div>
                </div>
                <div class="dso-kpi-card dso-kpi-purple">
                    <div class="dso-kpi-content">
                        <span class="dso-kpi-label">Avg. Order Value</span>
                        <span class="dso-kpi-value"><?php echo wc_price($data['avg_order_value']) ?></span>
                    </div>
                </div>
                <div class="dso-kpi-card dso-kpi-teal">
                    <div class="dso-kpi-content">
                        <span class="dso-kpi-label">Products Sold</span>
                        <span class="dso-kpi-value"><?php echo $data['products_sold'] ?></span>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="dso-grid-2">
                <div class="dso-card dso-card-chart">
                    <div class="dso-card-header"><h3>Revenue Trend</h3></div>
                    <div class="dso-chart-container">
                        <canvas id="dso-revenue-chart"></canvas>
                    </div>
                </div>
                <div class="dso-card dso-card-chart">
                    <div class="dso-card-header"><h3>Orders Trend</h3></div>
                    <div class="dso-chart-container">
                        <canvas id="dso-orders-chart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Products by Revenue -->
            <div class="dso-card">
                <div class="dso-card-header"><h3>Top Products by Revenue</h3></div>
                <div class="dso-table-responsive">
                    <table class="dso-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th>Orders</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($data['top_products'])): ?>
                                <tr class="dso-empty-row"><td colspan="4"><div class="dso-empty-inline"><p>No product data yet</p></div></td></tr>
                            <?php else: ?>
                                <?php foreach ($data['top_products'] as $i => $p): ?>
                                    <tr>
                                        <td><?php echo $i + 1 ?></td>
                                        <td><?php echo esc_html($p['name']) ?></td>
                                        <td><?php echo $p['orders'] ?></td>
                                        <td><?php echo wc_price($p['revenue']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            DSO.initAnalytics(<?php echo wp_json_encode($data['chart_data']) ?>);
        });
        </script>
        <?php
    }

    public function get_analytics_data($vendor_id) {
        global $wpdb;

        $total_revenue = 0;
        $total_orders = 0;
        $products_sold = 0;
        $top_products = [];
        $chart_data = ['labels' => [], 'revenue' => [], 'orders' => []];

        if ($vendor_id) {
            $row = $wpdb->get_row($wpdb->prepare(
                "SELECT COALESCE(SUM(order_total), 0) as revenue, COUNT(*) as orders, COALESCE(SUM(quantity), 0) as sold
                FROM {$wpdb->prefix}wcfm_marketplace_orders
                WHERE vendor_id = %d AND order_status IN ('wc-completed', 'wc-processing')",
                $vendor_id
            ));

            $total_revenue = floatval($row->revenue ?? 0);
            $total_orders = intval($row->orders ?? 0);
            $products_sold = intval($row->sold ?? 0);

            // Top products
            $top_products = $wpdb->get_results($wpdb->prepare(
                "SELECT product_id, SUM(quantity) as orders, SUM(order_total) as revenue
                FROM {$wpdb->prefix}wcfm_marketplace_orders
                WHERE vendor_id = %d AND order_status IN ('wc-completed', 'wc-processing')
                GROUP BY product_id ORDER BY revenue DESC LIMIT 10",
                $vendor_id
            ));

            foreach ($top_products as &$tp) {
                $product = wc_get_product($tp->product_id);
                $tp->name = $product ? $product->get_name() : 'Product #' . $tp->product_id;
            }

            // Chart data - last 30 days
            for ($i = 29; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-{$i} days"));
                $chart_data['labels'][] = date('M j', strtotime($date));

                $cr = $wpdb->get_row($wpdb->prepare(
                    "SELECT COALESCE(SUM(order_total), 0) as sales, COUNT(*) as orders
                    FROM {$wpdb->prefix}wcfm_marketplace_orders
                    WHERE vendor_id = %d AND order_status IN ('wc-completed', 'wc-processing')
                    AND order_date >= %s AND order_date <= %s",
                    $vendor_id, $date . ' 00:00:00', $date . ' 23:59:59'
                ));

                $chart_data['revenue'][] = floatval($cr->sales ?? 0);
                $chart_data['orders'][] = intval($cr->orders ?? 0);
            }
        }

        $avg_order_value = $total_orders > 0 ? $total_revenue / $total_orders : 0;

        return [
            'total_revenue' => $total_revenue,
            'total_orders' => $total_orders,
            'avg_order_value' => $avg_order_value,
            'products_sold' => $products_sold,
            'top_products' => $top_products,
            'chart_data' => $chart_data,
        ];
    }
}
