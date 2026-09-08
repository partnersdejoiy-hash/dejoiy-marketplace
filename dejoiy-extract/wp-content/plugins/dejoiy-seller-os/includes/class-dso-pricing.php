<?php
/**
 * DSO Pricing - Pricing Management
 */
if (!defined('ABSPATH')) exit;

class DSO_Pricing {

    public function render() {
        $user_id = get_current_user_id();
        $plugin = Dejoiy_Seller_OS::instance();
        $vendor_id = $plugin->get_vendor_id($user_id);
        $products = $this->get_pricing_data($vendor_id);

        // Handle bulk price update
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dso_bulk_price_update'])) {
            check_admin_referer('dso_pricing');
            $product_ids = $_POST['product_ids'] ?? [];
            $price_field = sanitize_text_field($_POST['price_field']);
            $adjustment_type = sanitize_text_field($_POST['adjustment_type']);
            $adjustment_value = floatval($_POST['adjustment_value']);

            foreach ($product_ids as $pid) {
                $product = wc_get_product(intval($pid));
                if (!$product) continue;

                $current = floatval($product->get_regular_price());
                if ($adjustment_type === 'fixed') {
                    $new_price = $adjustment_value;
                } elseif ($adjustment_type === 'increase') {
                    $new_price = $current + $adjustment_value;
                } elseif ($adjustment_type === 'decrease') {
                    $new_price = max(0, $current - $adjustment_value);
                } elseif ($adjustment_type === 'percent') {
                    $new_price = $current * (1 + $adjustment_value / 100);
                } else {
                    $new_price = $current;
                }

                $product->set_regular_price(round($new_price, 2));
                if ($price_field === 'sale_price') {
                    $product->set_sale_price(round($new_price, 2));
                }
                $product->save();
            }

            wp_redirect('?section=pricing&updated=1');
            exit;
        }

        ?>
        <div class="dso-page dso-pricing">
            <div class="dso-page-header">
                <div>
                    <h1>Pricing Management</h1>
                    <p>Manage product pricing, sales, and discounts</p>
                </div>
            </div>

            <!-- Quick Price Stats -->
            <div class="dso-mini-stats">
                <div class="dso-mini-stat">
                    <span class="dso-mini-stat-value"><?php echo count($products) ?></span>
                    <span class="dso-mini-stat-label">Products</span>
                </div>
                <div class="dso-mini-stat">
                    <span class="dso-mini-stat-value"><?php echo $this->count_on_sale($vendor_id) ?></span>
                    <span class="dso-mini-stat-label">On Sale</span>
                </div>
                <div class="dso-mini-stat">
                    <span class="dso-mini-stat-value"><?php echo wc_price($this->get_avg_price($vendor_id)) ?></span>
                    <span class="dso-mini-stat-label">Avg Price</span>
                </div>
            </div>

            <!-- Bulk Price Update -->
            <div class="dso-card">
                <div class="dso-card-header"><h3>Bulk Price Update</h3></div>
                <div class="dso-card-body">
                    <form method="post" class="dso-form" id="dso-bulk-price-form">
                        <?php wp_nonce_field('dso_pricing'); ?>
                        <div class="dso-form-row">
                            <div class="dso-form-group">
                                <label>Adjustment Type</label>
                                <select name="adjustment_type" class="dso-select">
                                    <option value="fixed">Set Fixed Price</option>
                                    <option value="increase">Increase by</option>
                                    <option value="decrease">Decrease by</option>
                                    <option value="percent">Adjust by %</option>
                                </select>
                            </div>
                            <div class="dso-form-group">
                                <label>Value</label>
                                <input type="number" name="adjustment_value" class="dso-input" step="0.01" min="0" required placeholder="0.00" />
                            </div>
                            <div class="dso-form-group">
                                <label>Apply To</label>
                                <select name="price_field" class="dso-select">
                                    <option value="regular_price">Regular Price</option>
                                    <option value="sale_price">Sale Price</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" name="dso_bulk_price_update" value="1" class="dso-btn dso-btn-primary" onclick="return confirm('Update prices for selected products?')">
                            Apply to Selected
                        </button>
                    </form>
                </div>
            </div>

            <!-- Products Pricing Table -->
            <div class="dso-card">
                <div class="dso-card-header">
                    <h3>Product Pricing</h3>
                    <div class="dso-search-box dso-search-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" placeholder="Search..." class="dso-input dso-input-sm" id="dso-pricing-search" />
                    </div>
                </div>
                <div class="dso-table-responsive">
                    <table class="dso-table dso-table-pricing">
                        <thead>
                            <tr>
                                <th class="dso-th-check"><input type="checkbox" id="dso-select-all-pricing" /></th>
                                <th>Product</th>
                                <th>Regular Price</th>
                                <th>Sale Price</th>
                                <th>Sale Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($products)): ?>
                                <tr class="dso-empty-row"><td colspan="6"><div class="dso-empty-inline"><p>No products to manage pricing for</p></div></td></tr>
                            <?php else: ?>
                                <?php foreach ($products as $p): ?>
                                    <tr>
                                        <td class="dso-td-check"><input type="checkbox" class="dso-pricing-check" name="product_ids[]" value="<?php echo $p['id'] ?>" /></td>
                                        <td>
                                            <div class="dso-product-cell">
                                                <div class="dso-product-thumb"><?php echo $p['image'] ?></div>
                                                <span class="dso-product-name"><?php echo esc_html($p['name']) ?></span>
                                            </div>
                                        </td>
                                        <td><?php echo wc_price($p['regular_price']) ?></td>
                                        <td><?php echo $p['sale_price'] > 0 ? wc_price($p['sale_price']) : '—' ?></td>
                                        <td><?php echo $p['on_sale'] ? '<span class="dso-badge dso-badge-green">On Sale</span>' : '<span class="dso-badge dso-badge-gray">Regular</span>' ?></td>
                                        <td><a href="?section=edit-product&id=<?php echo $p['id'] ?>" class="dso-action-btn">Edit</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }

    public function get_pricing_data($vendor_id) {
        global $wpdb;
        if (!$vendor_id) return [];

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = '_vendor_id' AND meta_value = %d GROUP BY post_id",
            $vendor_id
        ));

        $products = [];
        foreach ($results as $row) {
            $product = wc_get_product($row->post_id);
            if (!$product) continue;

            $image = get_the_post_thumbnail($row->post_id, [40, 40]);
            if (!$image) $image = '<div class="dso-product-placeholder-img"></div>';

            $products[] = [
                'id' => $product->get_id(),
                'name' => $product->get_name(),
                'regular_price' => floatval($product->get_regular_price()),
                'sale_price' => floatval($product->get_sale_price()),
                'on_sale' => $product->is_on_sale(),
                'image' => $image,
            ];
        }
        return $products;
    }

    private function count_on_sale($vendor_id) {
        global $wpdb;
        return intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT pm.post_id) FROM {$wpdb->prefix}postmeta pm
            INNER JOIN {$wpdb->prefix}postmeta sale ON pm.post_id = sale.post_id AND sale.meta_key = '_sale_price' AND sale.meta_value != ''
            WHERE pm.meta_key = '_vendor_id' AND pm.meta_value = %d",
            $vendor_id
        )));
    }

    private function get_avg_price($vendor_id) {
        global $wpdb;
        return floatval($wpdb->get_var($wpdb->prepare(
            "SELECT AVG(meta_value) FROM {$wpdb->prefix}postmeta pm
            INNER JOIN {$wpdb->prefix}postmeta vendor ON pm.post_id = vendor.post_id AND vendor.meta_key = '_vendor_id' AND vendor.meta_value = %d
            WHERE pm.meta_key = '_regular_price' AND pm.meta_value > 0",
            $vendor_id
        )));
    }
}
