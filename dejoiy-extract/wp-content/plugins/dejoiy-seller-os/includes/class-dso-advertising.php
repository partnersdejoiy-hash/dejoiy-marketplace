<?php
/**
 * DSO Advertising - Campaign Management
 */
if (!defined('ABSPATH')) exit;

class DSO_Advertising {

    public function render() {
        $user_id = get_current_user_id();
        $plugin = Dejoiy_Seller_OS::instance();
        $vendor_id = $plugin->get_vendor_id($user_id);
        $campaigns = $this->get_campaigns($vendor_id);

        ?>
        <div class="dso-page dso-advertising">
            <div class="dso-page-header">
                <div>
                    <h1>Advertising</h1>
                    <p>Promote your products and reach more customers</p>
                </div>
            </div>

            <!-- Ad Performance Overview -->
            <div class="dso-kpi-grid dso-kpi-grid-4">
                <div class="dso-kpi-card dso-kpi-blue">
                    <div class="dso-kpi-content">
                        <span class="dso-kpi-label">Active Campaigns</span>
                        <span class="dso-kpi-value"><?php echo $this->count_active($vendor_id) ?></span>
                    </div>
                </div>
                <div class="dso-kpi-card dso-kpi-green">
                    <div class="dso-kpi-content">
                        <span class="dso-kpi-label">Total Impressions</span>
                        <span class="dso-kpi-value"><?php echo number_format($this->get_total_impressions($vendor_id)) ?></span>
                    </div>
                </div>
                <div class="dso-kpi-card dso-kpi-purple">
                    <div class="dso-kpi-content">
                        <span class="dso-kpi-label">Total Clicks</span>
                        <span class="dso-kpi-value"><?php echo number_format($this->get_total_clicks($vendor_id)) ?></span>
                    </div>
                </div>
                <div class="dso-kpi-card dso-kpi-teal">
                    <div class="dso-kpi-content">
                        <span class="dso-kpi-label">Ad Spend</span>
                        <span class="dso-kpi-value"><?php echo wc_price($this->get_total_spend($vendor_id)) ?></span>
                    </div>
                </div>
            </div>

            <!-- Create Campaign -->
            <div class="dso-card">
                <div class="dso-card-header">
                    <h3>Advertising Campaigns</h3>
                </div>
                <div class="dso-card-body">
                    <?php if (empty($campaigns)): ?>
                        <div class="dso-empty-state">
                            <div class="dso-empty-icon">📣</div>
                            <h3>No campaigns yet</h3>
                            <p>Start promoting your products to reach more customers on DEJOIY.</p>
                            <div class="dso-empty-actions">
                                <div class="dso-ad-info-grid">
                                    <div class="dso-ad-info-card">
                                        <div class="dso-ad-info-icon">🎯</div>
                                        <h4>Product Promotion</h4>
                                        <p>Feature your products in search results and category pages</p>
                                    </div>
                                    <div class="dso-ad-info-card">
                                        <div class="dso-ad-info-icon">🏷️</div>
                                        <h4>Sponsored Listings</h4>
                                        <p>Get premium placement in DEJOIY search results</p>
                                    </div>
                                    <div class="dso-ad-info-card">
                                        <div class="dso-ad-info-icon">📊</div>
                                        <h4>Performance Ads</h4>
                                        <p>Pay only for results — clicks and conversions</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="dso-table-responsive">
                            <table class="dso-table">
                                <thead>
                                    <tr>
                                        <th>Campaign</th>
                                        <th>Type</th>
                                        <th>Budget</th>
                                        <th>Spent</th>
                                        <th>Impressions</th>
                                        <th>Clicks</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($campaigns as $c): ?>
                                        <tr>
                                            <td><strong><?php echo esc_html($c['name']) ?></strong></td>
                                            <td><?php echo esc_html($c['type']) ?></td>
                                            <td><?php echo wc_price($c['budget']) ?></td>
                                            <td><?php echo wc_price($c['spent']) ?></td>
                                            <td><?php echo number_format($c['impressions']) ?></td>
                                            <td><?php echo number_format($c['clicks']) ?></td>
                                            <td><?php echo $c['status_badge'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Promoted Products -->
            <div class="dso-card">
                <div class="dso-card-header"><h3>Promoted Products</h3></div>
                <div class="dso-card-body">
                    <div class="dso-empty-inline">
                        <p>Product promotion features will be available soon. Contact support to learn about advertising options.</p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    private function get_campaigns($vendor_id) {
        return [];
    }

    private function count_active($vendor_id) { return 0; }
    private function get_total_impressions($vendor_id) { return 0; }
    private function get_total_clicks($vendor_id) { return 0; }
    private function get_total_spend($vendor_id) { return 0; }
}
