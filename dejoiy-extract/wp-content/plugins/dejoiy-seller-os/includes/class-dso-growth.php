<?php
/**
 * DSO Growth - Business Insights
 */
if (!defined('ABSPATH')) exit;

class DSO_Growth {

    public function render() {
        $user_id = get_current_user_id();
        $plugin = Dejoiy_Seller_OS::instance();
        $vendor_id = $plugin->get_vendor_id($user_id);
        $data = $this->get_growth_data($vendor_id);

        ?>
        <div class="dso-page dso-growth">
            <div class="dso-page-header">
                <div>
                    <h1>Growth Center</h1>
                    <p>Actionable insights to grow your DEJOIY business</p>
                </div>
            </div>

            <!-- Growth Score -->
            <div class="dso-card dso-card-health">
                <div class="dso-card-header">
                    <h3>Business Growth Score</h3>
                    <span class="dso-health-score <?php echo $data['score'] >= 70 ? 'dso-health-good' : ($data['score'] >= 40 ? 'dso-health-warn' : 'dso-health-bad') ?>">
                        <?php echo $data['score'] ?>%
                    </span>
                </div>
                <div class="dso-growth-insights">
                    <?php foreach ($data['insights'] as $insight): ?>
                        <div class="dso-growth-insight-item">
                            <div class="dso-growth-insight-icon"><?php echo $insight['icon'] ?></div>
                            <div class="dso-growth-insight-content">
                                <h4><?php echo esc_html($insight['title']) ?></h4>
                                <p><?php echo esc_html($insight['description']) ?></p>
                                <?php if (!empty($insight['action_url'])): ?>
                                    <a href="<?php echo esc_url($insight['action_url']) ?>" class="dso-btn dso-btn-sm dso-btn-secondary">Take Action →</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Top Products & Slow Movers -->
            <div class="dso-grid-2">
                <div class="dso-card">
                    <div class="dso-card-header"><h3>Best Selling Products</h3></div>
                    <div class="dso-card-body">
                        <?php if (empty($data['best_sellers'])): ?>
                            <div class="dso-empty-inline"><p>No sales data yet</p></div>
                        <?php else: ?>
                            <?php foreach ($data['best_sellers'] as $bs): ?>
                                <div class="dso-growth-product-item">
                                    <div class="dso-product-cell">
                                        <div class="dso-product-thumb"><?php echo $bs['image'] ?></div>
                                        <div>
                                            <span class="dso-product-name"><?php echo esc_html($bs['name']) ?></span>
                                            <span class="dso-text-muted"><?php echo $bs['orders'] ?> orders</span>
                                        </div>
                                    </div>
                                    <strong><?php echo wc_price($bs['revenue']) ?></strong>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="dso-card">
                    <div class="dso-card-header"><h3>Low Stock Opportunities</h3></div>
                    <div class="dso-card-body">
                        <?php if (empty($data['low_stock'])): ?>
                            <div class="dso-empty-inline"><p>All products well stocked</p></div>
                        <?php else: ?>
                            <?php foreach ($data['low_stock'] as $ls): ?>
                                <div class="dso-growth-product-item">
                                    <div class="dso-product-cell">
                                        <div class="dso-product-thumb"><?php echo $ls['image'] ?></div>
                                        <div>
                                            <span class="dso-product-name"><?php echo esc_html($ls['name']) ?></span>
                                            <span class="dso-text-muted">Only <?php echo $ls['stock'] ?> left</span>
                                        </div>
                                    </div>
                                    <a href="?section=inventory" class="dso-btn dso-btn-sm dso-btn-primary">Restock</a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recommendations -->
            <div class="dso-card">
                <div class="dso-card-header"><h3>Growth Recommendations</h3></div>
                <div class="dso-card-body">
                    <?php if (empty($data['recommendations'])): ?>
                        <div class="dso-empty-inline"><p>Keep selling to unlock growth insights!</p></div>
                    <?php else: ?>
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
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    private function get_growth_data($vendor_id) {
        global $wpdb;
        $score = 50;
        $insights = [];
        $best_sellers = [];
        $low_stock = [];
        $recommendations = [];

        if ($vendor_id) {
            // Best sellers
            $rows = $wpdb->get_results($wpdb->prepare(
                "SELECT product_id, SUM(quantity) as orders, SUM(order_total) as revenue
                FROM {$wpdb->prefix}wcfm_marketplace_orders
                WHERE vendor_id = %d AND order_status IN ('wc-completed', 'wc-processing')
                GROUP BY product_id ORDER BY revenue DESC LIMIT 5",
                $vendor_id
            ));
            foreach ($rows as $r) {
                $product = wc_get_product($r->product_id);
                if (!$product) continue;
                $img = get_the_post_thumbnail($r->product_id, [40, 40]);
                $best_sellers[] = [
                    'name' => $product->get_name(),
                    'orders' => intval($r->orders),
                    'revenue' => floatval($r->revenue),
                    'image' => $img ?: '<div class="dso-product-placeholder-img"></div>',
                ];
            }

            // Low stock
            $low_rows = $wpdb->get_results($wpdb->prepare(
                "SELECT pm.post_id, stock.meta_value as stock_qty
                FROM {$wpdb->prefix}postmeta pm
                INNER JOIN {$wpdb->prefix}postmeta stock ON pm.post_id = stock.post_id AND stock.meta_key = '_stock'
                INNER JOIN {$wpdb->prefix}postmeta manage ON pm.post_id = manage.post_id AND manage.meta_key = '_manage_stock' AND manage.meta_value = 'yes'
                WHERE pm.meta_key = '_vendor_id' AND pm.meta_value = %d AND stock.meta_value > 0 AND stock.meta_value <= %d
                ORDER BY stock.meta_value ASC LIMIT 5",
                $vendor_id, get_option('woocommerce_notify_low_stock_amount', 2)
            ));
            foreach ($low_rows as $lr) {
                $product = wc_get_product($lr->post_id);
                if (!$product) continue;
                $img = get_the_post_thumbnail($lr->post_id, [40, 40]);
                $low_stock[] = [
                    'name' => $product->get_name(),
                    'stock' => intval($lr->stock_qty),
                    'image' => $img ?: '<div class="dso-product-placeholder-img"></div>',
                ];
            }

            // Score
            $total_products = $this->count_products($vendor_id);
            $total_orders = $this->count_orders($vendor_id);
            $completed = $this->count_completed($vendor_id);
            $rating = $this->get_rating($vendor_id);

            if ($total_products > 0) $score += 10;
            if ($total_products > 5) $score += 10;
            if ($total_orders > 0) $score += 10;
            if ($completed > 0) $score += 10;
            if ($rating >= 4) $score += 10;
            $score = min(100, $score);

            // Recommendations
            if ($total_products < 5) {
                $recommendations[] = ['icon' => '📦', 'message' => 'Add more products to increase your visibility. Aim for at least 10 products.', 'action_url' => '?section=add-product'];
            }
            if ($total_orders === 0) {
                $recommendations[] = ['icon' => '🎯', 'message' => 'Complete your store profile and add more products to start getting orders.', 'action_url' => '?section=store'];
            }
            if ($rating > 0 && $rating < 4) {
                $recommendations[] = ['icon' => '⭐', 'message' => 'Your rating is below 4 stars. Focus on quality and customer service.', 'action_url' => '?section=reviews'];
            }
            if (!empty($low_stock)) {
                $recommendations[] = ['icon' => '⚠️', 'message' => count($low_stock) . ' product(s) are low on stock. Restock to avoid missed sales.', 'action_url' => '?section=inventory'];
            }
        }

        return compact('score', 'insights', 'best_sellers', 'low_stock', 'recommendations');
    }

    private function count_products($vid) {
        global $wpdb;
        return intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT post_id) FROM {$wpdb->prefix}postmeta WHERE meta_key = '_vendor_id' AND meta_value = %d", $vid
        )));
    }
    private function count_orders($vid) {
        global $wpdb;
        return intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}wcfm_marketplace_orders WHERE vendor_id = %d", $vid
        )));
    }
    private function count_completed($vid) {
        global $wpdb;
        return intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}wcfm_marketplace_orders WHERE vendor_id = %d AND order_status = 'wc-completed'", $vid
        )));
    }
    private function get_rating($vid) {
        global $wpdb;
        $r = $wpdb->get_var($wpdb->prepare(
            "SELECT AVG(meta_value) FROM {$wpdb->prefix}wcfm_marketplace_review_rating_meta WHERE vendor_id = %d", $vid
        ));
        return $r ? round(floatval($r), 1) : 0;
    }
}
