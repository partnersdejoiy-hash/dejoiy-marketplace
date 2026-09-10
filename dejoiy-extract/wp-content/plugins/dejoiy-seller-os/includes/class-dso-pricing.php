<?php
/**
 * DSO Pricing - Comprehensive Pricing, Deals, Coupons, and Promotions for DEJOIY Seller Central
 */
if (!defined('ABSPATH')) exit;

class DSO_Pricing {

    protected function get_active_vendor_id() {
        $user_id = get_current_user_id();
        $plugin = Dejoiy_Seller_OS::instance();
        return $plugin->get_vendor_id($user_id);
    }

    protected function is_admin() {
        return current_user_can('manage_woocommerce') || current_user_can('administrator');
    }

    public function render() {
        $vendor_id = $this->get_active_vendor_id();

        // Handle bulk price update
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dso_bulk_price_update'])) {
            check_admin_referer('dso_pricing_nonce');
            $product_ids = $_POST['product_ids'] ?? [];
            $price_field = sanitize_text_field($_POST['price_field'] ?? 'regular_price');
            $adj_type = sanitize_text_field($_POST['adjustment_type'] ?? 'fixed');
            $adj_val = floatval($_POST['adjustment_value'] ?? 0);

            foreach ($product_ids as $pid) {
                $product = wc_get_product(intval($pid));
                if (!$product) continue;

                $current = floatval($product->get_regular_price());
                if ($adj_type === 'fixed') {
                    $new_price = $adj_val;
                } elseif ($adj_type === 'increase') {
                    $new_price = $current + $adj_val;
                } elseif ($adj_type === 'decrease') {
                    $new_price = max(0, $current - $adj_val);
                } elseif ($adj_type === 'percent') {
                    $new_price = $current * (1 + $adj_val / 100);
                } else {
                    $new_price = $current;
                }

                $new_price = round($new_price, 2);
                if ($price_field === 'sale_price') {
                    $product->set_sale_price($new_price);
                } else {
                    $product->set_regular_price($new_price);
                    $product->set_price($product->get_sale_price() ?: $new_price);
                }
                $product->save();
            }

            wp_redirect('?section=pricing&notice=updated');
            exit;
        }

        $p_handler = new DSO_Products();
        $products = $p_handler->get_products($vendor_id, 100);

        $on_sale_count = 0;
        $total_val = 0;
        $count = count($products);

        foreach ($products as $p) {
            if (!empty($p['sale_price'])) $on_sale_count++;
            $total_val += floatval($p['regular_price']);
        }
        $avg_price = $count > 0 ? round($total_val / $count, 2) : 0;
        ?>
        <div class="dso-page dso-pricing">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <span>Pricing</span>
                        <span>/</span>
                        <span>Overview</span>
                    </div>
                    <h1 class="dso-page-title">Pricing & Promotion Engine</h1>
                    <p class="dso-page-subtitle">Configure competitive price points, promotional discounts, and automated margin rules</p>
                </div>
                <div class="dso-page-actions">
                    <a href="?section=deals" class="dso-btn dso-btn-outline">Create Flash Deal</a>
                    <a href="?section=coupons" class="dso-btn dso-btn-primary">+ Create Coupon</a>
                </div>
            </div>

            <?php if (isset($_GET['notice'])): ?>
                <div class="dso-alert dso-alert-success">Prices updated successfully across selected catalog listings.</div>
            <?php endif; ?>

            <!-- Metrics -->
            <div class="dso-stats-row">
                <div class="dso-stat-card">
                    <span class="dso-stat-label">Active Listings</span>
                    <span class="dso-stat-val"><?php echo $count; ?></span>
                    <span class="dso-stat-sub">In price matrix</span>
                </div>
                <div class="dso-stat-card">
                    <span class="dso-stat-label">Listings on Sale</span>
                    <span class="dso-stat-val dso-text-success"><?php echo $on_sale_count; ?></span>
                    <span class="dso-stat-sub"><?php echo $count > 0 ? round(($on_sale_count / $count) * 100) : 0; ?>% promotional penetration</span>
                </div>
                <div class="dso-stat-card">
                    <span class="dso-stat-label">Average Catalog Price</span>
                    <span class="dso-stat-val"><?php echo wc_price($avg_price); ?></span>
                    <span class="dso-stat-sub">Mean selling price</span>
                </div>
            </div>

            <!-- Bulk Pricing Form -->
            <div class="dso-card dso-mb-4">
                <div class="dso-card-header">
                    <h3 class="dso-card-title">Bulk Price Adjustment Tool</h3>
                </div>
                <div class="dso-card-body">
                    <form method="post" id="dso-pricing-bulk-form">
                        <?php wp_nonce_field('dso_pricing_nonce'); ?>
                        <input type="hidden" name="dso_bulk_price_update" value="1" />

                        <div class="dso-form-row dso-grid-3">
                            <div class="dso-form-group">
                                <label class="dso-label">Adjustment Formula</label>
                                <select name="adjustment_type" class="dso-select">
                                    <option value="percent">Percentage Discount (-X%)</option>
                                    <option value="fixed">Set Exact Fixed Price (₹)</option>
                                    <option value="decrease">Reduce Price by Fixed (₹)</option>
                                    <option value="increase">Increase Price by Fixed (₹)</option>
                                </select>
                            </div>
                            <div class="dso-form-group">
                                <label class="dso-label">Value (₹ or %)</label>
                                <input type="number" step="0.01" name="adjustment_value" class="dso-input" required placeholder="e.g., 10" />
                            </div>
                            <div class="dso-form-group">
                                <label class="dso-label">Apply Target Field</label>
                                <select name="price_field" class="dso-select">
                                    <option value="sale_price">Special Promotional Sale Price</option>
                                    <option value="regular_price">Regular Base Price</option>
                                </select>
                            </div>
                        </div>

                        <!-- Catalog Table for Selection -->
                        <div class="dso-table-responsive dso-mt-4">
                            <table class="dso-table">
                                <thead>
                                    <tr>
                                        <th class="dso-th-check"><input type="checkbox" id="dso-select-all" /></th>
                                        <th>Product</th>
                                        <th>SKU</th>
                                        <th>MRP (₹)</th>
                                        <th>Regular Price (₹)</th>
                                        <th>Sale Price (₹)</th>
                                        <th>Effective Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $p): ?>
                                        <tr>
                                            <td class="dso-td-check">
                                                <input type="checkbox" name="product_ids[]" value="<?php echo $p['id']; ?>" class="dso-product-check" />
                                            </td>
                                            <td>
                                                <div class="dso-product-cell">
                                                    <div class="dso-product-thumb"><?php echo $p['image_html']; ?></div>
                                                    <span class="dso-product-title"><?php echo esc_html($p['name']); ?></span>
                                                </div>
                                            </td>
                                            <td><code><?php echo esc_html($p['sku'] ?: '—'); ?></code></td>
                                            <td><?php echo $p['mrp'] ? '₹' . number_format($p['mrp'], 2) : '—'; ?></td>
                                            <td><strong>₹<?php echo number_format(floatval($p['regular_price']), 2); ?></strong></td>
                                            <td><?php echo !empty($p['sale_price']) ? '<span class="dso-text-success">₹' . number_format(floatval($p['sale_price']), 2) . '</span>' : '—'; ?></td>
                                            <td>
                                                <span class="dso-badge <?php echo !empty($p['sale_price']) ? 'dso-badge-green' : 'dso-badge-gray'; ?>">
                                                    <?php echo !empty($p['sale_price']) ? 'Discount Active' : 'Regular Rate'; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="dso-card-footer dso-mt-4">
                            <button type="submit" class="dso-btn dso-btn-primary">Apply Price Changes to Selected Items</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Promotions Manager
     */
    public function promotions() {
        ?>
        <div class="dso-page dso-promotions">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <a href="?section=pricing">Pricing</a>
                        <span>/</span>
                        <span>Promotions</span>
                    </div>
                    <h1 class="dso-page-title">Marketplace Campaigns & Promotions</h1>
                    <p class="dso-page-subtitle">Enroll products into DEJOIY festive banners, mega sales, and weekend specials</p>
                </div>
            </div>

            <div class="dso-grid-3">
                <div class="dso-card">
                    <div class="dso-card-header">
                        <h3 class="dso-card-title">DEJOIY Mega Sale</h3>
                        <span class="dso-badge dso-badge-green">Upcoming</span>
                    </div>
                    <div class="dso-card-body">
                        <p class="dso-text-muted">Massive marketplace-wide festival sale with homepage hero banner placement and push notifications.</p>
                        <ul class="dso-clean-list dso-mt-3">
                            <li>📅 Starts in 4 days</li>
                            <li>🏷️ Minimum discount required: 15% off MRP</li>
                            <li>🚀 Free express shipping badge</li>
                        </ul>
                        <a href="?section=pricing" class="dso-btn dso-btn-sm dso-btn-primary dso-btn-full dso-mt-3">Nominate Products →</a>
                    </div>
                </div>

                <div class="dso-card">
                    <div class="dso-card-header">
                        <h3 class="dso-card-title">Weekend Flash Surge</h3>
                        <span class="dso-badge dso-badge-purple">Weekly</span>
                    </div>
                    <div class="dso-card-body">
                        <p class="dso-text-muted">48-hour limited surge promotion targeting top-converting categories with custom countdown timers.</p>
                        <ul class="dso-clean-list dso-mt-3">
                            <li>📅 Every Saturday & Sunday</li>
                            <li>🏷️ Minimum discount: 10%</li>
                            <li>🎯 Spotlight on mobile app feed</li>
                        </ul>
                        <a href="?section=deals" class="dso-btn dso-btn-sm dso-btn-outline dso-btn-full dso-mt-3">Schedule Flash Deal →</a>
                    </div>
                </div>

                <div class="dso-card">
                    <div class="dso-card-header">
                        <h3 class="dso-card-title">Buy More, Save More</h3>
                        <span class="dso-badge dso-badge-blue">Always On</span>
                    </div>
                    <div class="dso-card-body">
                        <p class="dso-text-muted">Encourage multi-unit basket orders with automated quantity tier discounts.</p>
                        <ul class="dso-clean-list dso-mt-3">
                            <li>📦 Buy 2: Get 5% extra off</li>
                            <li>📦 Buy 3+: Get 10% extra off</li>
                            <li>📈 Boosts average order value (AOV)</li>
                        </ul>
                        <a href="?section=bulk-pricing" class="dso-btn dso-btn-sm dso-btn-outline dso-btn-full dso-mt-3">Configure Tiers →</a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Flash Deals Scheduler
     */
    public function deals() {
        ?>
        <div class="dso-page dso-deals">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <a href="?section=pricing">Pricing</a>
                        <span>/</span>
                        <span>Deals</span>
                    </div>
                    <h1 class="dso-page-title">Flash Deals & Lightning Discounts</h1>
                    <p class="dso-page-subtitle">Schedule time-bound flash sales with urgency countdown timers on product storefront pages</p>
                </div>
                <div class="dso-page-actions">
                    <a href="?section=pricing" class="dso-btn dso-btn-primary">+ Add New Flash Deal</a>
                </div>
            </div>

            <div class="dso-card">
                <div class="dso-card-header">
                    <h3 class="dso-card-title">Active & Scheduled Deals</h3>
                </div>
                <div class="dso-card-body dso-p-0">
                    <div class="dso-table-responsive">
                        <table class="dso-table">
                            <thead>
                                <tr>
                                    <th>Campaign Name</th>
                                    <th>Discount</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Super Saver Deal</strong></td>
                                    <td><span class="dso-badge dso-badge-green">20% OFF</span></td>
                                    <td><?php echo date('M j, Y'); ?></td>
                                    <td><?php echo date('M j, Y', strtotime('+3 days')); ?></td>
                                    <td><span class="dso-badge dso-badge-primary">Live Now</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Coupons Manager
     */
    public function coupons() {
        $vendor_id = $this->get_active_vendor_id();

        // Handle Coupon Creation
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dso_create_coupon'])) {
            check_admin_referer('dso_coupon_nonce');
            $code = strtoupper(sanitize_text_field($_POST['coupon_code'] ?? ''));
            $amount = floatval($_POST['coupon_amount'] ?? 0);
            $type = sanitize_text_field($_POST['discount_type'] ?? 'percent');
            $min_spend = floatval($_POST['min_spend'] ?? 0);

            if ($code && $amount > 0) {
                $coupon = new WC_Coupon();
                $coupon->set_code($code);
                $coupon->set_discount_type($type);
                $coupon->set_amount($amount);
                $coupon->set_individual_use(true);
                if ($min_spend > 0) $coupon->set_minimum_amount($min_spend);
                $coupon->save();

                if ($vendor_id) {
                    update_post_meta($coupon->get_id(), '_vendor_id', $vendor_id);
                }
                wp_redirect('?section=coupons&notice=created');
                exit;
            }
        }

        // Fetch coupons
        $coupons = get_posts(['post_type' => 'shop_coupon', 'posts_per_page' => 20]);
        ?>
        <div class="dso-page dso-coupons">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <a href="?section=pricing">Pricing</a>
                        <span>/</span>
                        <span>Coupons</span>
                    </div>
                    <h1 class="dso-page-title">Store Coupons & Vouchers</h1>
                    <p class="dso-page-subtitle">Create exclusive discount coupon codes for loyal shoppers and marketing campaigns</p>
                </div>
            </div>

            <?php if (isset($_GET['notice'])): ?>
                <div class="dso-alert dso-alert-success">Coupon created and activated successfully!</div>
            <?php endif; ?>

            <div class="dso-grid-2">
                <!-- Create Form -->
                <div class="dso-card">
                    <div class="dso-card-header">
                        <h3 class="dso-card-title">Create New Store Coupon</h3>
                    </div>
                    <div class="dso-card-body">
                        <form method="post">
                            <?php wp_nonce_field('dso_coupon_nonce'); ?>
                            <input type="hidden" name="dso_create_coupon" value="1" />

                            <div class="dso-form-group">
                                <label class="dso-label">Coupon Code <span class="dso-req">*</span></label>
                                <input type="text" name="coupon_code" class="dso-input" required placeholder="e.g., DEJOIY10 or WELCOME50" style="text-transform:uppercase; font-weight:700;" />
                            </div>

                            <div class="dso-form-row dso-grid-2">
                                <div class="dso-form-group">
                                    <label class="dso-label">Discount Type</label>
                                    <select name="discount_type" class="dso-select">
                                        <option value="percent">Percentage Discount (%)</option>
                                        <option value="fixed_cart">Fixed Basket Discount (₹)</option>
                                    </select>
                                </div>
                                <div class="dso-form-group">
                                    <label class="dso-label">Discount Amount <span class="dso-req">*</span></label>
                                    <input type="number" step="0.01" min="1" name="coupon_amount" class="dso-input" required placeholder="e.g., 10" />
                                </div>
                            </div>

                            <div class="dso-form-group">
                                <label class="dso-label">Minimum Purchase Amount (₹)</label>
                                <input type="number" step="1" min="0" name="min_spend" class="dso-input" placeholder="e.g., 499 (Optional)" />
                            </div>

                            <button type="submit" class="dso-btn dso-btn-primary dso-mt-3">Publish Coupon</button>
                        </form>
                    </div>
                </div>

                <!-- Existing Coupons List -->
                <div class="dso-card">
                    <div class="dso-card-header">
                        <h3 class="dso-card-title">Existing Active Coupons</h3>
                    </div>
                    <div class="dso-card-body dso-p-0">
                        <div class="dso-table-responsive">
                            <table class="dso-table">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Discount</th>
                                        <th>Usage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($coupons)): ?>
                                        <tr><td colspan="3" class="dso-p-4 dso-text-center">No active coupons found. Create your first coupon on the left.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($coupons as $c): 
                                            $c_obj = new WC_Coupon($c->ID);
                                        ?>
                                            <tr>
                                                <td><span class="dso-badge dso-badge-primary"><strong><?php echo esc_html($c_obj->get_code()); ?></strong></span></td>
                                                <td>
                                                    <?php echo $c_obj->get_discount_type() === 'percent' ? $c_obj->get_amount() . '%' : wc_price($c_obj->get_amount()); ?> OFF
                                                </td>
                                                <td><?php echo intval($c_obj->get_usage_count()); ?> used</td>
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
        <?php
    }

    /**
     * Quantity Tier Discounts
     */
    public function bulk_pricing() {
        ?>
        <div class="dso-page dso-bulk-pricing">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <a href="?section=pricing">Pricing</a>
                        <span>/</span>
                        <span>Bulk Pricing</span>
                    </div>
                    <h1 class="dso-page-title">Volume & Tiered Pricing Rules</h1>
                    <p class="dso-page-subtitle">Configure tiered volume discounts for retail buyers ordering multiple units</p>
                </div>
            </div>

            <div class="dso-card">
                <div class="dso-card-header">
                    <h3 class="dso-card-title">Active Tier Rules</h3>
                </div>
                <div class="dso-card-body dso-p-0">
                    <div class="dso-table-responsive">
                        <table class="dso-table">
                            <thead>
                                <tr>
                                    <th>Tier Name</th>
                                    <th>Min Quantity</th>
                                    <th>Discount Applied</th>
                                    <th>Applies To</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Tier 1 — Duo Pack</strong></td>
                                    <td>2 units</td>
                                    <td><span class="dso-badge dso-badge-green">5% OFF</span></td>
                                    <td>All Catalog Listings</td>
                                    <td><span class="dso-badge dso-badge-green">Active</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Tier 2 — Family / Multi Pack</strong></td>
                                    <td>5 units</td>
                                    <td><span class="dso-badge dso-badge-green">10% OFF</span></td>
                                    <td>All Catalog Listings</td>
                                    <td><span class="dso-badge dso-badge-green">Active</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Tier 3 — Bulk Case</strong></td>
                                    <td>10+ units</td>
                                    <td><span class="dso-badge dso-badge-green">15% OFF</span></td>
                                    <td>All Catalog Listings</td>
                                    <td><span class="dso-badge dso-badge-green">Active</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * B2B Wholesale Pricing
     */
    public function b2b_pricing() {
        ?>
        <div class="dso-page dso-b2b-pricing">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <a href="?section=pricing">Pricing</a>
                        <span>/</span>
                        <span>B2B Wholesale</span>
                    </div>
                    <h1 class="dso-page-title">B2B Wholesale & Business Pricing</h1>
                    <p class="dso-page-subtitle">Provide GST registered corporate and trade buyers with custom wholesale rates and GST invoices</p>
                </div>
                <div class="dso-page-actions">
                    <a href="?section=b2b" class="dso-btn dso-btn-primary">Go to B2B Hub →</a>
                </div>
            </div>

            <div class="dso-card">
                <div class="dso-card-body">
                    <div class="dso-info-box dso-mb-4">
                        <h4>🏢 Corporate Wholesale Program on DEJOIY</h4>
                        <p>Verified business customers with valid GSTINs receive dedicated bulk rate cards on DEJOIY Marketplace. You earn steady large-volume recurring sales with instant invoice generation.</p>
                    </div>
                    <div class="dso-grid-3">
                        <div class="dso-card">
                            <div class="dso-card-body">
                                <h4>Tier A: MOQ 25</h4>
                                <p class="dso-text-muted">20% margin below retail</p>
                                <span class="dso-badge dso-badge-green">Enabled</span>
                            </div>
                        </div>
                        <div class="dso-card">
                            <div class="dso-card-body">
                                <h4>Tier B: MOQ 50</h4>
                                <p class="dso-text-muted">28% margin below retail</p>
                                <span class="dso-badge dso-badge-green">Enabled</span>
                            </div>
                        </div>
                        <div class="dso-card">
                            <div class="dso-card-body">
                                <h4>Tier C: MOQ 100+</h4>
                                <p class="dso-text-muted">Custom Quote / RFQ</p>
                                <span class="dso-badge dso-badge-purple">RFQ Enabled</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
