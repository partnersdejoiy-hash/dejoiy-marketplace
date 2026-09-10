<?php
/**
 * DSO Growth - Strategic Growth, AI Recommendations & Category Insights for DEJOIY
 */
if (!defined('ABSPATH')) exit;

class DSO_Growth {

    protected function get_active_vendor_id() {
        $user_id = get_current_user_id();
        $plugin = Dejoiy_Seller_OS::instance();
        return $plugin->get_vendor_id($user_id);
    }

    public function render() {
        ?>
        <div class="dso-page dso-growth">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <span>Growth</span>
                        <span>/</span>
                        <span>Opportunities</span>
                    </div>
                    <h1 class="dso-page-title">DEJOIY Growth Center</h1>
                    <p class="dso-page-subtitle">Algorithmic recommendations, demand gaps, and high-margin product opportunities</p>
                </div>
            </div>

            <div class="dso-grid-3 dso-mb-4">
                <div class="dso-card">
                    <div class="dso-card-body">
                        <div class="dso-app-icon" style="font-size: 32px;">💡</div>
                        <h3>AI Recommendations</h3>
                        <p class="dso-text-muted">Data-driven listing tweaks to immediately improve search rank and conversions.</p>
                        <a href="?section=growth-recommendations" class="dso-btn dso-btn-sm dso-btn-primary dso-mt-3">View 5 Tips →</a>
                    </div>
                </div>

                <div class="dso-card">
                    <div class="dso-card-body">
                        <div class="dso-app-icon" style="font-size: 32px;">👥</div>
                        <h3>Shopper Demographic Insights</h3>
                        <p class="dso-text-muted">Understand top buying regions, metro vs non-metro splits, and repeat buyer rates.</p>
                        <a href="?section=growth-insights" class="dso-btn dso-btn-sm dso-btn-outline dso-mt-3">View Demographics →</a>
                    </div>
                </div>

                <div class="dso-card">
                    <div class="dso-card-body">
                        <div class="dso-app-icon" style="font-size: 32px;">📈</div>
                        <h3>Product Demand Gaps</h3>
                        <p class="dso-text-muted">Unmet customer search terms on DEJOIY with high search volume and low seller competition.</p>
                        <a href="?section=growth-opportunities" class="dso-btn dso-btn-sm dso-btn-outline dso-mt-3">Explore Gaps →</a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function recommendations() {
        ?>
        <div class="dso-page dso-recommendations">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <a href="?section=growth">Growth</a>
                        <span>/</span>
                        <span>AI Recommendations</span>
                    </div>
                    <h1 class="dso-page-title">Marketplace AI Listing Recommendations</h1>
                    <p class="dso-page-subtitle">Automated recommendations based on top-performing market competitors</p>
                </div>
            </div>

            <div class="dso-card">
                <div class="dso-card-body">
                    <div class="dso-recommendation dso-p-3 dso-mb-3" style="border: 1px solid var(--dj-border); border-radius: 8px;">
                        <h4>🎯 Add Multi-angle Gallery Images to Top 10 Listings</h4>
                        <p class="dso-text-muted">Listings with 3+ images have a 44% higher checkout conversion rate on DEJOIY.</p>
                        <a href="?section=product-quality" class="dso-btn dso-btn-sm dso-btn-primary dso-mt-2">Audit Image Scores →</a>
                    </div>

                    <div class="dso-recommendation dso-p-3 dso-mb-3" style="border: 1px solid var(--dj-border); border-radius: 8px;">
                        <h4>⚡ Enroll Top Items in Buy 2 Get 5% Off Tier</h4>
                        <p class="dso-text-muted">Apparel buyers frequently purchase matching colors together when offered volume discounts.</p>
                        <a href="?section=bulk-pricing" class="dso-btn dso-btn-sm dso-btn-outline dso-mt-2">Enable Volume Tier →</a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function insights() {
        ?>
        <div class="dso-page dso-growth-insights">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <a href="?section=growth">Growth</a>
                        <span>/</span>
                        <span>Insights</span>
                    </div>
                    <h1 class="dso-page-title">Customer Regional Demographics</h1>
                    <p class="dso-page-subtitle">Where your buyers are located across India</p>
                </div>
            </div>

            <div class="dso-grid-2">
                <div class="dso-card">
                    <div class="dso-card-header"><h3 class="dso-card-title">Top Delivery States</h3></div>
                    <div class="dso-card-body">
                        <ul class="dso-clean-list">
                            <li>1. Maharashtra (34% of volume)</li>
                            <li>2. Delhi NCR (26% of volume)</li>
                            <li>3. Karnataka (18% of volume)</li>
                            <li>4. Gujarat (12% of volume)</li>
                        </ul>
                    </div>
                </div>

                <div class="dso-card">
                    <div class="dso-card-header"><h3 class="dso-card-title">Payment Preferences</h3></div>
                    <div class="dso-card-body">
                        <ul class="dso-clean-list">
                            <li>UPI / Instant Transfer: 68%</li>
                            <li>Credit / Debit Cards: 21%</li>
                            <li>Cash on Delivery: 11%</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function opportunities() {
        ?>
        <div class="dso-page dso-growth-opps">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <a href="?section=growth">Growth</a>
                        <span>/</span>
                        <span>Opportunities</span>
                    </div>
                    <h1 class="dso-page-title">Marketplace Catalog Demand Gaps</h1>
                    <p class="dso-page-subtitle">Products customers are searching for but DEJOIY currently has low seller coverage for</p>
                </div>
            </div>

            <div class="dso-card">
                <div class="dso-card-body dso-p-0">
                    <div class="dso-table-responsive">
                        <table class="dso-table">
                            <thead>
                                <tr>
                                    <th>Trending Search Term</th>
                                    <th>Department</th>
                                    <th>Search Volume Index</th>
                                    <th>Seller Competition</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>french terry heavyweight hoodie</strong></td>
                                    <td>Fashion & Apparel</td>
                                    <td><span class="dso-badge dso-badge-green">High (8.9k / mo)</span></td>
                                    <td><span class="dso-badge dso-badge-gray">Low</span></td>
                                    <td><a href="?section=add-product" class="dso-btn dso-btn-sm dso-btn-primary">Add Listing</a></td>
                                </tr>
                                <tr>
                                    <td><strong>handcrafted ceramic planter with drainage</strong></td>
                                    <td>Home & Kitchen</td>
                                    <td><span class="dso-badge dso-badge-green">High (6.4k / mo)</span></td>
                                    <td><span class="dso-badge dso-badge-gray">Low</span></td>
                                    <td><a href="?section=add-product" class="dso-btn dso-btn-sm dso-btn-primary">Add Listing</a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
