<?php
/**
 * DSO Reports - Business Reports
 */
if (!defined('ABSPATH')) exit;

class DSO_Reports {

    public function render() {
        $user_id = get_current_user_id();
        $plugin = Dejoiy_Seller_OS::instance();
        $vendor_id = $plugin->get_vendor_id($user_id);
        $period = isset($_GET['period']) ? sanitize_text_field($_GET['period']) : '30d';
        $data = $this->get_report_data($vendor_id, $period);

        ?>
        <div class="dso-page dso-reports">
            <div class="dso-page-header">
                <div>
                    <h1>Reports</h1>
                    <p>Detailed business analytics and reports</p>
                </div>
                <div class="dso-page-actions">
                    <div class="dso-chart-filters">
                        <a href="?section=reports&period=7d" class="dso-filter-btn <?php echo $period === '7d' ? 'active' : '' ?>">7D</a>
                        <a href="?section=reports&period=30d" class="dso-filter-btn <?php echo $period === '30d' ? 'active' : '' ?>">30D</a>
                        <a href="?section=reports&period=90d" class="dso-filter-btn <?php echo $period === '90d' ? 'active' : '' ?>">90D</a>
                        <a href="?section=reports&period=1y" class="dso-filter-btn <?php echo $period === '1y' ? 'active' : '' ?>">1Y</a>
                    </div>
                </div>
            </div>

            <!-- Report Tabs -->
            <div class="dso-tabs">
                <button class="dso-tab active" data-tab="sales">Sales</button>
                <button class="dso-tab" data-tab="orders">Orders</button>
                <button class="dso-tab" data-tab="products">Products</button>
                <button class="dso-tab" data-tab="customers">Customers</button>
                <button class="dso-tab" data-tab="inventory">Inventory</button>
            </div>

            <!-- Sales Report -->
            <div class="dso-tab-content active" id="tab-sales">
                <div class="dso-kpi-grid dso-kpi-grid-4">
                    <div class="dso-kpi-card dso-kpi-green">
                        <div class="dso-kpi-content">
                            <span class="dso-kpi-label">Total Revenue</span>
                            <span class="dso-kpi-value"><?php echo wc_price($data['total_revenue']) ?></span>
                        </div>
                    </div>
                    <div class="dso-kpi-card dso-kpi-blue">
                        <div class="dso-kpi-content">
                            <span class="dso-kpi-label">Total Orders</span>
                            <span class="dso-kpi-value"><?php echo $data['total_orders'] ?></span>
                        </div>
                    </div>
                    <div class="dso-kpi-card dso-kpi-purple">
                        <div class="dso-kpi-content">
                            <span class="dso-kpi-label">Avg Order Value</span>
                            <span class="dso-kpi-value"><?php echo wc_price($data['avg_order']) ?></span>
                        </div>
                    </div>
                    <div class="dso-kpi-card dso-kpi-teal">
                        <div class="dso-kpi-content">
                            <span class="dso-kpi-label">Items Sold</span>
                            <span class="dso-kpi-value"><?php echo $data['items_sold'] ?></span>
                        </div>
                    </div>
                </div>

                <div class="dso-card dso-card-chart">
                    <div class="dso-card-header"><h3>Revenue Trend</h3></div>
                    <div class="dso-chart-container">
                        <canvas id="dso-report-revenue-chart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Orders Report -->
            <div class="dso-tab-content" id="tab-orders" style="display:none;">
                <div class="dso-card">
                    <div class="dso-card-header"><h3>Order Status Breakdown</h3></div>
                    <div class="dso-card-body">
                        <div class="dso-report-breakdown">
                            <div class="dso-breakdown-item"><span class="dso-badge dso-badge-orange">Pending</span><strong><?php echo $data['pending'] ?></strong></div>
                            <div class="dso-breakdown-item"><span class="dso-badge dso-badge-blue">Processing</span><strong><?php echo $data['processing'] ?></strong></div>
                            <div class="dso-breakdown-item"><span class="dso-badge dso-badge-green">Completed</span><strong><?php echo $data['completed'] ?></strong></div>
                            <div class="dso-breakdown-item"><span class="dso-badge dso-badge-red">Cancelled</span><strong><?php echo $data['cancelled'] ?></strong></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Report -->
            <div class="dso-tab-content" id="tab-products" style="display:none;">
                <div class="dso-card">
                    <div class="dso-card-header"><h3>Top Products by Revenue</h3></div>
                    <div class="dso-table-responsive">
                        <table class="dso-table">
                            <thead><tr><th>#</th><th>Product</th><th>Orders</th><th>Revenue</th></tr></thead>
                            <tbody>
                                <?php if (empty($data['top_products'])): ?>
                                    <tr class="dso-empty-row"><td colspan="4"><div class="dso-empty-inline"><p>No data yet</p></div></td></tr>
                                <?php else: ?>
                                    <?php foreach ($data['top_products'] as $i => $tp): ?>
                                        <tr><td><?php echo $i+1 ?></td><td><?php echo esc_html($tp['name']) ?></td><td><?php echo $tp['orders'] ?></td><td><?php echo wc_price($tp['revenue']) ?></td></tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Customers Report -->
            <div class="dso-tab-content" id="tab-customers" style="display:none;">
                <div class="dso-card">
                    <div class="dso-card-header"><h3>Top Customers</h3></div>
                    <div class="dso-card-body">
                        <?php if (empty($data['top_customers'])): ?>
                            <div class="dso-empty-inline"><p>No customer data yet</p></div>
                        <?php else: ?>
                            <?php foreach ($data['top_customers'] as $tc): ?>
                                <div class="dso-growth-product-item">
                                    <div>
                                        <strong><?php echo esc_html($tc['name']) ?></strong>
                                        <span class="dso-text-muted"><?php echo $tc['orders'] ?> orders</span>
                                    </div>
                                    <strong><?php echo wc_price($tc['spent']) ?></strong>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Inventory Report -->
            <div class="dso-tab-content" id="tab-inventory" style="display:none;">
                <div class="dso-kpi-grid dso-kpi-grid-3">
                    <div class="dso-kpi-card dso-kpi-green"><div class="dso-kpi-content"><span class="dso-kpi-label">In Stock</span><span class="dso-kpi-value"><?php echo $data['in_stock'] ?></span></div></div>
                    <div class="dso-kpi-card dso-kpi-orange"><div class="dso-kpi-content"><span class="dso-kpi-label">Low Stock</span><span class="dso-kpi-value"><?php echo $data['low_stock'] ?></span></div></div>
                    <div class="dso-kpi-card dso-kpi-red"><div class="dso-kpi-content"><span class="dso-kpi-label">Out of Stock</span><span class="dso-kpi-value"><?php echo $data['out_stock'] ?></span></div></div>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.dso-tab').forEach(function(tab) {
                tab.addEventListener('click', function() {
                    document.querySelectorAll('.dso-tab').forEach(function(t) { t.classList.remove('active'); });
                    document.querySelectorAll('.dso-tab-content').forEach(function(c) { c.style.display = 'none'; });
                    tab.classList.add('active');
                    var target = document.getElementById('tab-' + tab.dataset.tab);
                    if (target) target.style.display = 'block';
                });
            });
            if (typeof DSO !== 'undefined' && DSO.initReports) DSO.initReports(<?php echo wp_json_encode($data['chart_data']) ?>);
        });
        </script>
        <?php
    }

    private function get_report_data($vendor_id, $period) {
        global $wpdb;
        $days = 30;
        switch ($period) {
            case '7d': $days = 7; break;
            case '90d': $days = 90; break;
            case '1y': $days = 365; break;
        }

        $start_date = date('Y-m-d 00:00:00', strtotime("-{$days} days"));
        $end_date = date('Y-m-d 23:59:59');

        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT COALESCE(SUM(item_total), 0) as revenue, COUNT(*) as orders, COALESCE(SUM(quantity), 0) as items
            FROM {$wpdb->prefix}wcfm_marketplace_orders
            WHERE vendor_id = %d AND order_status IN ('wc-completed', 'wc-processing')
            AND created >= %s AND created <= %s",
            $vendor_id, $start_date, $end_date
        ));

        $total_revenue = floatval($row->revenue ?? 0);
        $total_orders = intval($row->orders ?? 0);
        $items_sold = intval($row->items ?? 0);
        $avg_order = $total_orders > 0 ? $total_revenue / $total_orders : 0;

        // Status counts
        $pending = intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}wcfm_marketplace_orders WHERE vendor_id = %d AND order_status = 'wc-pending' AND created >= %s", $vendor_id, $start_date
        )));
        $processing = intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}wcfm_marketplace_orders WHERE vendor_id = %d AND order_status = 'wc-processing' AND created >= %s", $vendor_id, $start_date
        )));
        $completed = intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}wcfm_marketplace_orders WHERE vendor_id = %d AND order_status = 'wc-completed' AND created >= %s", $vendor_id, $start_date
        )));
        $cancelled = intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}wcfm_marketplace_orders WHERE vendor_id = %d AND order_status = 'wc-cancelled' AND created >= %s", $vendor_id, $start_date
        )));

        // Top products
        $top_products = [];
        $tp_rows = $wpdb->get_results($wpdb->prepare(
            "SELECT product_id, SUM(quantity) as orders, SUM(item_total) as revenue
            FROM {$wpdb->prefix}wcfm_marketplace_orders
            WHERE vendor_id = %d AND order_status IN ('wc-completed', 'wc-processing') AND created >= %s
            GROUP BY product_id ORDER BY revenue DESC LIMIT 10",
            $vendor_id, $start_date
        ));
        foreach ($tp_rows as $tp) {
            $product = wc_get_product($tp->product_id);
            $top_products[] = ['name' => $product ? $product->get_name() : '#'.$tp->product_id, 'orders' => intval($tp->orders), 'revenue' => floatval($tp->revenue)];
        }

        // Top customers
        $top_customers = [];
        $tc_rows = $wpdb->get_results($wpdb->prepare(
            "SELECT customer_id, COUNT(*) as orders, SUM(item_total) as spent
            FROM {$wpdb->prefix}wcfm_marketplace_orders
            WHERE vendor_id = %d AND order_status IN ('wc-completed', 'wc-processing') AND created >= %s AND customer_id > 0
            GROUP BY customer_id ORDER BY spent DESC LIMIT 10",
            $vendor_id, $start_date
        ));
        foreach ($tc_rows as $tc) {
            $user_data = get_userdata($tc->customer_id);
            $customer_name = $user_data ? $user_data->display_name : 'Guest';
            $top_customers[] = ['name' => $customer_name, 'orders' => intval($tc->orders), 'spent' => floatval($tc->spent)];
        }

        // Inventory
        $in_stock = intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT pm.post_id) FROM {$wpdb->prefix}postmeta pm
            INNER JOIN {$wpdb->prefix}postmeta stock ON pm.post_id = stock.post_id AND stock.meta_key = '_stock_status' AND stock.meta_value = 'instock'
            WHERE pm.meta_key = '_vendor_id' AND pm.meta_value = %d", $vendor_id
        )));
        $low_stock = intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT pm.post_id) FROM {$wpdb->prefix}postmeta pm
            INNER JOIN {$wpdb->prefix}postmeta stock ON pm.post_id = stock.post_id AND stock.meta_key = '_stock'
            INNER JOIN {$wpdb->prefix}postmeta manage ON pm.post_id = manage.post_id AND manage.meta_key = '_manage_stock' AND manage.meta_value = 'yes'
            WHERE pm.meta_key = '_vendor_id' AND pm.meta_value = %d AND stock.meta_value > 0 AND stock.meta_value <= %d",
            $vendor_id, get_option('woocommerce_notify_low_stock_amount', 2)
        )));
        $out_stock = intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT pm.post_id) FROM {$wpdb->prefix}postmeta pm
            INNER JOIN {$wpdb->prefix}postmeta stock ON pm.post_id = stock.post_id AND stock.meta_key = '_stock_status' AND stock.meta_value = 'outofstock'
            WHERE pm.meta_key = '_vendor_id' AND pm.meta_value = %d", $vendor_id
        )));

        // Chart data
        $chart_data = ['labels' => [], 'revenue' => []];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $chart_data['labels'][] = date('M j', strtotime($date));
            $cr = $wpdb->get_var($wpdb->prepare(
                "SELECT COALESCE(SUM(item_total), 0) FROM {$wpdb->prefix}wcfm_marketplace_orders
                WHERE vendor_id = %d AND order_status IN ('wc-completed', 'wc-processing')
                AND created >= %s AND created <= %s",
                $vendor_id, $date.' 00:00:00', $date.' 23:59:59'
            ));
            $chart_data['revenue'][] = floatval($cr);
        }

        return compact('total_revenue', 'total_orders', 'avg_order', 'items_sold', 'pending', 'processing', 'completed', 'cancelled', 'top_products', 'top_customers', 'in_stock', 'low_stock', 'out_stock', 'chart_data');
    }
}
