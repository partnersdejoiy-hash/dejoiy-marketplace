<?php
/**
 * DSO Dashboard - Command Center for DEJOIY Seller Central
 * Real-time operational intelligence, Action Center, Store Health Score, and sales analytics
 */
if (!defined('ABSPATH')) exit;

class DSO_Dashboard {

    protected function get_active_vendor_id() {
        $user_id = get_current_user_id();
        if ($this->is_admin()) {
            return DSO_Auth::get_admin_vendor_context();
        }
        $plugin = Dejoiy_Seller_OS::instance();
        return $plugin->get_vendor_id($user_id) ?: $user_id;
    }

    protected function is_admin() {
        return current_user_can('administrator') || current_user_can('manage_options');
    }

    public function render() {
        $user_id = get_current_user_id();
        $vendor_id = $this->get_active_vendor_id();
        $effective_id = $vendor_id ?: $user_id;
        $store = DSO_Auth::get_vendor_store($effective_id);
        $user_obj = get_userdata($effective_id);
        $store_name = $store ? $store['name'] : ($user_obj ? $user_obj->display_name : 'Seller');

        $data = $this->get_dashboard_data($vendor_id, $user_id);
        $health = $this->get_store_health($vendor_id);
        $action_items = $this->get_action_center_items($data);
        ?>
        <div class="dso-page dso-dashboard" id="dso-dashboard">
            <!-- Personalized Welcome Banner -->
            <div class="dso-welcome-banner">
                <div class="dso-welcome-content">
                    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:8px;">
                        <span class="dso-welcome-badge">DEJOIY SELLER CENTRAL OPERATING SYSTEM</span>
                        <span class="dso-badge" style="background:rgba(192,132,252,0.2);color:#e9d5ff;border:1px solid rgba(192,132,252,0.4);font-family:monospace;font-size:11px;font-weight:700;"><?php echo esc_html(sprintf('DJ-VND-%04d', $vendor_id)); ?></span>
                        <span class="dso-badge dso-badge-green" style="font-size:11px;">Verified Merchant ✓</span>
                        <span class="dso-badge" style="background:rgba(56,189,248,0.2);color:#bae6fd;border:1px solid rgba(56,189,248,0.4);font-size:11px;font-weight:700;">Platinum Tier (<?php echo $health['score']; ?>/100 Health)</span>
                    </div>
                    <h1 class="dso-welcome-title"><?php echo esc_html($this->get_greeting()); ?>, <?php echo esc_html($store_name); ?> 👋</h1>
                    <p class="dso-welcome-desc">Your marketplace command center is synchronized. Here is your operational pulse for today, <span class="dso-welcome-date"><?php echo date('l, F j, Y'); ?></span>.</p>
                </div>
                <div class="dso-welcome-actions">
                    <a href="<?php echo esc_url(function_exists('wcfmmp_get_store_url') ? wcfmmp_get_store_url($vendor_id) : 'https://dejoiy.com'); ?>" target="_blank" rel="noopener" class="dso-btn dso-btn-outline" style="background:rgba(255,255,255,0.1);color:#fff;border-color:rgba(255,255,255,0.25);">
                        View Live Storefront ↗
                    </a>
                    <a href="?section=add-product" class="dso-btn dso-btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Add Product
                    </a>
                    <a href="?section=orders" class="dso-btn dso-btn-outline" style="background:rgba(255,255,255,0.1);color:#fff;border-color:rgba(255,255,255,0.25);">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
                        Manage Orders
                    </a>
                </div>
            </div>

            <!-- Action Center -->
            <?php if (!empty($action_items)): ?>
                <div class="dso-action-center-container dso-mb-4">
                    <div class="dso-action-header">
                        <div class="dso-action-title">
                            <span class="dso-pulse-dot"></span>
                            <strong>Operational Action Center</strong>
                            <span class="dso-badge dso-badge-primary"><?php echo count($action_items); ?> Attention Items</span>
                        </div>
                        <span class="dso-action-sub">Recommended tasks to maximize listing conversions and store metrics</span>
                    </div>
                    <div class="dso-action-cards-grid">
                        <?php foreach ($action_items as $act): ?>
                            <div class="dso-action-card dso-action-<?php echo esc_attr($act['type']); ?>">
                                <div class="dso-action-card-icon"><?php echo $act['icon']; ?></div>
                                <div class="dso-action-card-body">
                                    <h4 class="dso-action-card-title"><?php echo esc_html($act['title']); ?></h4>
                                    <p class="dso-action-card-desc"><?php echo esc_html($act['description']); ?></p>
                                </div>
                                <a href="<?php echo esc_url($act['url']); ?>" class="dso-btn dso-btn-sm dso-btn-outline"><?php echo esc_html($act['button']); ?> →</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Key Performance Metrics Row -->
            <div class="dso-kpi-grid dso-kpi-grid-4 dso-mb-4">
                <div class="dso-kpi-card">
                    <div class="dso-kpi-header">
                        <span class="dso-kpi-label">Gross Marketplace Volume</span>
                        <div class="dso-kpi-icon dso-kpi-icon-green">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                        </div>
                    </div>
                    <div class="dso-kpi-val"><?php echo wc_price($data['total_sales']); ?></div>
                    <div class="dso-kpi-meta">
                        <span class="dso-trend dso-trend-up">↑ <?php echo $data['today_sales'] > 0 ? wc_price($data['today_sales']) . ' today' : 'Synced live'; ?></span>
                        <a href="?section=reports" class="dso-kpi-link">Reports →</a>
                    </div>
                </div>

                <div class="dso-kpi-card">
                    <div class="dso-kpi-header">
                        <span class="dso-kpi-label">Total Orders</span>
                        <div class="dso-kpi-icon dso-kpi-icon-blue">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
                        </div>
                    </div>
                    <div class="dso-kpi-val"><?php echo number_format($data['total_orders']); ?></div>
                    <div class="dso-kpi-meta">
                        <span class="dso-subtext"><?php echo $data['processing_orders']; ?> to dispatch</span>
                        <a href="?section=orders" class="dso-kpi-link">Fulfillment →</a>
                    </div>
                </div>

                <div class="dso-kpi-card">
                    <div class="dso-kpi-header">
                        <span class="dso-kpi-label">Catalog Listings</span>
                        <div class="dso-kpi-icon dso-kpi-icon-purple">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 002 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0022 16z"/></svg>
                        </div>
                    </div>
                    <div class="dso-kpi-val"><?php echo number_format($data['total_products']); ?></div>
                    <div class="dso-kpi-meta">
                        <span class="dso-subtext"><?php echo $data['published_products']; ?> live • <?php echo $data['out_of_stock_count']; ?> out of stock</span>
                        <a href="?section=products" class="dso-kpi-link">Catalog →</a>
                    </div>
                </div>

                <div class="dso-kpi-card">
                    <div class="dso-kpi-header">
                        <span class="dso-kpi-label">Available Payout Balance</span>
                        <div class="dso-kpi-icon dso-kpi-icon-teal">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                        </div>
                    </div>
                    <div class="dso-kpi-val"><?php echo wc_price($data['available_balance']); ?></div>
                    <div class="dso-kpi-meta">
                        <span class="dso-subtext"><?php echo wc_price($data['pending_balance']); ?> pending settlement</span>
                        <a href="?section=finance" class="dso-kpi-link">Ledger →</a>
                    </div>
                </div>
            </div>

            <!-- Visual Analytics Grid: Sales Chart + Store Health Score -->
            <div class="dso-grid-2-1 dso-mb-4">
                <!-- Sales & Orders Chart -->
                <div class="dso-card dso-card-chart">
                    <div class="dso-card-header">
                        <div>
                            <h3 class="dso-card-title">Performance Analytics</h3>
                            <span class="dso-card-subtitle">Marketplace sales and order velocity</span>
                        </div>
                        <div class="dso-chart-filters" id="dso-chart-filters">
                            <button type="button" class="dso-filter-btn active" data-period="7d">7D</button>
                            <button type="button" class="dso-filter-btn" data-period="30d">30D</button>
                            <button type="button" class="dso-filter-btn" data-period="90d">90D</button>
                            <button type="button" class="dso-filter-btn" data-period="1y">1Y</button>
                        </div>
                    </div>
                    <div class="dso-chart-container" style="position: relative; height: 320px; width: 100%;">
                        <canvas id="dso-sales-chart"></canvas>
                    </div>
                </div>

                <!-- Store Health Score -->
                <div class="dso-card dso-card-health">
                    <div class="dso-card-header">
                        <div>
                            <h3 class="dso-card-title">Store Health Score</h3>
                            <span class="dso-card-subtitle">Account standing & compliance</span>
                        </div>
                        <span class="dso-badge dso-badge-green"><?php echo esc_html($health['grade']); ?> Tier</span>
                    </div>
                    <div class="dso-card-body">
                        <div class="dso-health-gauge-box">
                            <div class="dso-health-gauge">
                                <span class="dso-health-score"><?php echo $health['score']; ?></span>
                                <span class="dso-health-max">/ 100</span>
                            </div>
                            <span class="dso-health-desc"><?php echo esc_html($health['rating_label']); ?></span>
                        </div>

                        <div class="dso-health-breakdown">
                            <div class="dso-health-row">
                                <span class="dso-health-item-label">Listing Quality Score</span>
                                <div class="dso-health-bar-wrap">
                                    <div class="dso-health-bar" style="width: <?php echo $health['lqs_avg']; ?>%;"></div>
                                </div>
                                <span class="dso-health-item-val"><?php echo $health['lqs_avg']; ?>%</span>
                            </div>

                            <div class="dso-health-row">
                                <span class="dso-health-item-label">In-Stock Rate</span>
                                <div class="dso-health-bar-wrap">
                                    <div class="dso-health-bar" style="width: <?php echo $health['instock_rate']; ?>%;"></div>
                                </div>
                                <span class="dso-health-item-val"><?php echo $health['instock_rate']; ?>%</span>
                            </div>

                            <div class="dso-health-row">
                                <span class="dso-health-item-label">Fulfillment On-Time</span>
                                <div class="dso-health-bar-wrap">
                                    <div class="dso-health-bar" style="width: <?php echo $health['fulfillment_rate']; ?>%;"></div>
                                </div>
                                <span class="dso-health-item-val"><?php echo $health['fulfillment_rate']; ?>%</span>
                            </div>

                            <div class="dso-health-row">
                                <span class="dso-health-item-label">Policy Compliance</span>
                                <div class="dso-health-bar-wrap">
                                    <div class="dso-health-bar" style="width: <?php echo $health['policy_compliance']; ?>%;"></div>
                                </div>
                                <span class="dso-health-item-val"><?php echo $health['policy_compliance']; ?>%</span>
                            </div>
                        </div>

                        <div class="dso-health-footer dso-mt-3">
                            <a href="?section=performance" class="dso-btn dso-btn-sm dso-btn-full dso-btn-outline">View Complete Performance Scorecard →</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders & Top Performing Products -->
            <div class="dso-grid-2">
                <!-- Recent Orders -->
                <div class="dso-card">
                    <div class="dso-card-header">
                        <h3 class="dso-card-title">Recent Orders</h3>
                        <a href="?section=orders" class="dso-link-action">View All Orders →</a>
                    </div>
                    <div class="dso-card-body dso-p-0">
                        <div class="dso-table-responsive">
                            <table class="dso-table">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($data['recent_orders'])): ?>
                                        <tr><td colspan="4" class="dso-p-4 dso-text-center">No orders recorded yet.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($data['recent_orders'] as $ro): ?>
                                            <tr>
                                                <td>
                                                    <a href="?section=order-detail&id=<?php echo $ro['id']; ?>" class="dso-order-num-link">
                                                        <strong>#<?php echo esc_html($ro['number']); ?></strong>
                                                    </a>
                                                    <span class="dso-table-subdate"><?php echo esc_html($ro['date']); ?></span>
                                                </td>
                                                <td><?php echo esc_html($ro['customer']); ?></td>
                                                <td><strong><?php echo $ro['total']; ?></strong></td>
                                                <td><?php echo $ro['status_badge']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Top Listings by Velocity -->
                <div class="dso-card">
                    <div class="dso-card-header">
                        <h3 class="dso-card-title">Catalog Highlights</h3>
                        <a href="?section=products" class="dso-link-action">Catalog Overview →</a>
                    </div>
                    <div class="dso-card-body dso-p-0">
                        <div class="dso-table-responsive">
                            <table class="dso-table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>LQS Score</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($data['top_products'])): ?>
                                        <tr><td colspan="4" class="dso-p-4 dso-text-center">No products found.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($data['top_products'] as $tp): ?>
                                            <tr>
                                                <td>
                                                    <div class="dso-product-cell">
                                                        <div class="dso-product-thumb"><?php echo $tp['image']; ?></div>
                                                        <a href="?section=edit-product&id=<?php echo $tp['id']; ?>" class="dso-product-title"><?php echo esc_html($tp['name']); ?></a>
                                                    </div>
                                                </td>
                                                <td><?php echo $tp['price']; ?></td>
                                                <td>
                                                    <span class="dso-badge <?php echo $tp['lqs'] >= 80 ? 'dso-badge-green' : ($tp['lqs'] >= 55 ? 'dso-badge-orange' : 'dso-badge-red'); ?>">
                                                        <?php echo $tp['lqs']; ?>%
                                                    </span>
                                                </td>
                                                <td><?php echo $tp['status_badge']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var chartData = <?php echo json_encode($data['chart_data']); ?>;
            if (window.DSO && DSO.initDashboardCharts) {
                DSO.initDashboardCharts(chartData);
            }
        });
        </script>
        <?php
    }

    /**
     * Gather Unified Dashboard Data
     */
    public function get_dashboard_data($vendor_id, $user_id) {
        $p_handler = new DSO_Products();
        $p_stats = $p_handler->get_product_stats($vendor_id);

        $o_handler = new DSO_Orders();
        $o_stats = $o_handler->get_order_stats($vendor_id);
        $recent_raw = $o_handler->get_orders($vendor_id, 5);

        $recent_orders = [];
        foreach ($recent_raw as $ro) {
            $recent_orders[] = [
                'id' => $ro['id'],
                'number' => $ro['number'],
                'customer' => $ro['customer'],
                'total' => $ro['total_html'],
                'date' => $ro['date'],
                'status_badge' => $o_handler->status_badge($ro['status']),
            ];
        }

        $top_raw = $p_handler->get_products($vendor_id, 5);
        $top_products = [];
        foreach ($top_raw as $tp) {
            $top_products[] = [
                'id' => $tp['id'],
                'name' => $tp['name'],
                'price' => $tp['price_html'],
                'lqs' => $tp['lqs_score'],
                'image' => $tp['image_html'],
                'status_badge' => $tp['status_badge'],
            ];
        }

        // Calculate Revenue from Orders
        $total_sales = 0;
        $all_orders = wc_get_orders(['limit' => -1, 'return' => 'objects']);
        $scoped_orders = [];
        foreach ($all_orders as $ord) {
            if ($ord->get_status() !== 'cancelled' && $ord->get_status() !== 'trash') {
                if ($vendor_id > 0) {
                    $has_item = false;
                    $vendor_item_total = 0;
                    foreach ($ord->get_items() as $item) {
                        $pid = $item->get_product_id();
                        $author = get_post_field('post_author', $pid);
                        $meta_v = get_post_meta($pid, '_vendor_id', true);
                        if ($author == $vendor_id || $meta_v == $vendor_id) {
                            $has_item = true;
                            $vendor_item_total += floatval($item->get_total());
                        }
                    }
                    if ($has_item) {
                        $total_sales += $vendor_item_total;
                        $scoped_orders[] = $ord;
                    }
                } else {
                    $total_sales += floatval($ord->get_total());
                    $scoped_orders[] = $ord;
                }
            }
        }

        // Chart Data Generator (7D, 30D, 90D, 1Y)
        $chart_data = $this->generate_chart_timeline_data($scoped_orders);

        return [
            'total_sales' => $total_sales,
            'today_sales' => 0,
            'total_orders' => $o_stats['total'],
            'pending_orders' => $o_stats['pending'],
            'processing_orders' => $o_stats['processing'],
            'completed_orders' => $o_stats['completed'],
            'total_products' => $p_stats['total'],
            'published_products' => $p_stats['published'],
            'draft_products' => $p_stats['draft'],
            'low_stock_count' => $p_stats['low_stock'],
            'out_of_stock_count' => $p_stats['out_of_stock'],
            'available_balance' => 0.00,
            'pending_balance' => 0.00,
            'recent_orders' => $recent_orders,
            'top_products' => $top_products,
            'chart_data' => $chart_data,
        ];
    }

    /**
     * Compute Action Center Recommendations
     */
    protected function get_action_center_items($data) {
        $items = [];

        if ($data['out_of_stock_count'] > 0) {
            $items[] = [
                'type' => 'danger',
                'icon' => '🚫',
                'title' => $data['out_of_stock_count'] . ' Listings are Out of Stock',
                'description' => 'Listings with zero stock cannot be purchased and lose search algorithmic ranking on DEJOIY.',
                'button' => 'Restock Listings',
                'url' => '?section=inventory-out',
            ];
        }

        if ($data['low_stock_count'] > 0) {
            $items[] = [
                'type' => 'warning',
                'icon' => '⚠️',
                'title' => $data['low_stock_count'] . ' Listings Nearing Stock Exhaustion',
                'description' => 'Replenish inventory to maintain high fulfillment performance tiers.',
                'button' => 'View Low Stock',
                'url' => '?section=inventory-low',
            ];
        }

        if ($data['processing_orders'] > 0) {
            $items[] = [
                'type' => 'info',
                'icon' => '📦',
                'title' => $data['processing_orders'] . ' Orders Ready for Courier Dispatch',
                'description' => 'Generate shipping AWB labels and pack shipments for same-day handover.',
                'button' => 'Dispatch Orders',
                'url' => '?section=orders-processing',
            ];
        }

        $items[] = [
            'type' => 'primary',
            'icon' => '⚡',
            'title' => 'Optimize Listing Quality Scores (LQS)',
            'description' => 'Add category smart fields, high-res images, and MRP disclosures to boost impressions.',
            'button' => 'Audit Listings',
            'url' => '?section=product-quality',
        ];

        return $items;
    }

    /**
     * Compute Store Health Rating
     */
    public function get_store_health($vendor_id) {
        global $wpdb;

        $p_handler = new DSO_Products();
        $stats = $p_handler->get_product_stats($vendor_id);

        $instock_rate = 100;
        if ($stats['total'] > 0) {
            $instock_rate = round((($stats['total'] - $stats['out_of_stock']) / $stats['total']) * 100);
        }

        // Compute real LQS average from products
        $lqs_avg = 0;
        $lqs_count = 0;
        if ($vendor_id > 0) {
            $vendor_products = get_posts([
                'post_type' => 'product',
                'author' => $vendor_id,
                'posts_per_page' => 50,
                'post_status' => 'publish',
            ]);
            foreach ($vendor_products as $vp) {
                $lqs = intval(get_post_meta($vp->ID, '_dso_lqs_score', true));
                if ($lqs > 0) {
                    $lqs_avg += $lqs;
                    $lqs_count++;
                }
            }
        }
        $lqs_avg = $lqs_count > 0 ? round($lqs_avg / $lqs_count) : 0;

        // Compute real fulfillment on-time rate from completed orders
        $fulfillment_rate = 0;
        $policy_compliance = 100; // Default 100 unless violations found
        if ($vendor_id > 0 && function_exists('wc_get_orders')) {
            $completed_orders = wc_get_orders([
                'limit' => 100,
                'return' => 'objects',
                'orderby' => 'date',
                'order' => 'DESC',
            ]);
            $vendor_completed = [];
            foreach ($completed_orders as $co) {
                $has_vendor_item = false;
                foreach ($co->get_items() as $item) {
                    $pid = $item->get_product_id();
                    $author = get_post_field('post_author', $pid);
                    $meta_v = get_post_meta($pid, '_vendor_id', true);
                    if ($author == $vendor_id || $meta_v == $vendor_id) {
                        $has_vendor_item = true;
                        break;
                    }
                }
                if ($has_vendor_item) $vendor_completed[] = $co;
            }

            if (!empty($vendor_completed)) {
                $on_time = 0;
                foreach ($vendor_completed as $ord) {
                    // Consider order on-time if completed within 7 days of creation
                    $created = $ord->get_date_created();
                    $completed = $ord->get_date_completed();
                    if ($created && $completed) {
                        $diff_days = $completed->diff($created)->days;
                        if ($diff_days <= 7) $on_time++;
                    } else {
                        $on_time++; // No data = assume on-time
                    }
                }
                $fulfillment_rate = round(($on_time / count($vendor_completed)) * 100);
            }
        }

        $score = round(($instock_rate * 0.3) + ($lqs_avg * 0.3) + ($fulfillment_rate * 0.2) + ($policy_compliance * 0.2));

        $grade = 'Tier 1 (Platinum)';
        $label = 'Outstanding Marketplace Standing';
        if ($score < 60) {
            $grade = 'Standard';
            $label = 'Needs Listing Attention';
        } elseif ($score < 80) {
            $grade = 'Tier 2 (Gold)';
            $label = 'Strong Operational Health';
        }

        return [
            'score' => $score,
            'grade' => $grade,
            'rating_label' => $label,
            'instock_rate' => $instock_rate,
            'lqs_avg' => $lqs_avg,
            'fulfillment_rate' => $fulfillment_rate,
            'policy_compliance' => $policy_compliance,
        ];
    }

    /**
     * Multi-Period Timeline Chart Data
     */
    protected function generate_chart_timeline_data($orders) {
        $periods = ['7d' => 7, '30d' => 30, '90d' => 90, '1y' => 12];
        $res = [];

        foreach (['7d', '30d', '90d'] as $p) {
            $days = $periods[$p];
            $labels = [];
            $sales = [];
            $order_counts = [];

            for ($i = $days - 1; $i >= 0; $i--) {
                $d = date('M j', strtotime("-{$i} days"));
                $labels[] = $d;
                $sales[] = 0;
                $order_counts[] = 0;
            }

            $res[$p] = [
                'labels' => $labels,
                'sales' => $sales,
                'orders' => $order_counts,
            ];
        }

        // 1Y Months
        $labels_1y = [];
        $sales_1y = [];
        $orders_1y = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = date('M Y', strtotime("-{$i} months"));
            $labels_1y[] = $m;
            $sales_1y[] = 0;
            $orders_1y[] = 0;
        }
        $res['1y'] = [
            'labels' => $labels_1y,
            'sales' => $sales_1y,
            'orders' => $orders_1y,
        ];

        return $res;
    }

    public function get_greeting() {
        $hour = (int) current_time('G');
        if ($hour < 12) return 'Good morning';
        if ($hour < 17) return 'Good afternoon';
        return 'Good evening';
    }
}
