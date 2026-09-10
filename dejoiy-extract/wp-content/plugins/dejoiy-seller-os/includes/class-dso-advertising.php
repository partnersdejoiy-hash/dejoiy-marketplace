<?php
/**
 * DSO Advertising - Sponsored Products & Marketing Engine for DEJOIY
 */
if (!defined('ABSPATH')) exit;

class DSO_Advertising {

    public function render() {
        ?>
        <div class="dso-page dso-advertising">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <span>Advertising</span>
                        <span>/</span>
                        <span>Campaign Manager</span>
                    </div>
                    <h1 class="dso-page-title">Advertising Campaign Manager</h1>
                    <p class="dso-page-subtitle">Boost search rank, win top banner placements, and accelerate product discovery across DEJOIY</p>
                </div>
                <div class="dso-page-actions">
                    <a href="?section=advertising-performance" class="dso-btn dso-btn-outline">RoAS Performance</a>
                    <a href="?section=advertising-sponsored" class="dso-btn dso-btn-primary">+ Create Ad Campaign</a>
                </div>
            </div>

            <div class="dso-stats-row">
                <div class="dso-stat-card">
                    <span class="dso-stat-label">Active Campaigns</span>
                    <span class="dso-stat-val dso-text-primary">3</span>
                    <span class="dso-stat-sub">Sponsored listings live</span>
                </div>
                <div class="dso-stat-card">
                    <span class="dso-stat-label">Total Impressions</span>
                    <span class="dso-stat-val">48,250</span>
                    <span class="dso-stat-sub">Search appearances</span>
                </div>
                <div class="dso-stat-card">
                    <span class="dso-stat-label">Ad Clicks</span>
                    <span class="dso-stat-val">1,840</span>
                    <span class="dso-stat-sub">3.81% CTR</span>
                </div>
                <div class="dso-stat-card">
                    <span class="dso-stat-label">Return on Ad Spend (RoAS)</span>
                    <span class="dso-stat-val dso-text-success">4.8x</span>
                    <span class="dso-stat-sub">₹4.80 revenue per ₹1 spend</span>
                </div>
            </div>

            <!-- Active Campaigns Table -->
            <div class="dso-card">
                <div class="dso-card-header">
                    <h3 class="dso-card-title">Live Ad Campaigns</h3>
                </div>
                <div class="dso-card-body dso-p-0">
                    <div class="dso-table-responsive">
                        <table class="dso-table">
                            <thead>
                                <tr>
                                    <th>Campaign Name</th>
                                    <th>Type</th>
                                    <th>Daily Budget</th>
                                    <th>Spend</th>
                                    <th>Sales Generated</th>
                                    <th>RoAS</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Festive Apparel Discovery</strong></td>
                                    <td>Sponsored Search</td>
                                    <td>₹500.00 / day</td>
                                    <td>₹3,400.00</td>
                                    <td>₹18,200.00</td>
                                    <td><span class="dso-badge dso-badge-green">5.35x</span></td>
                                    <td><span class="dso-badge dso-badge-green">Active</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Ceramic Collection Spotlight</strong></td>
                                    <td>Category Banner</td>
                                    <td>₹300.00 / day</td>
                                    <td>₹1,800.00</td>
                                    <td>₹7,500.00</td>
                                    <td><span class="dso-badge dso-badge-green">4.16x</span></td>
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

    public function sponsored() {
        ?>
        <div class="dso-page dso-sponsored">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <a href="?section=advertising">Advertising</a>
                        <span>/</span>
                        <span>Sponsored Products</span>
                    </div>
                    <h1 class="dso-page-title">Sponsored Products Bid Manager</h1>
                    <p class="dso-page-subtitle">Target high-intent buyer keywords with cost-per-click (CPC) bidding</p>
                </div>
            </div>

            <div class="dso-card">
                <div class="dso-card-header">
                    <h3 class="dso-card-title">Keyword Bidding Matrix</h3>
                </div>
                <div class="dso-card-body dso-p-0">
                    <div class="dso-table-responsive">
                        <table class="dso-table">
                            <thead>
                                <tr>
                                    <th>Target Keyword</th>
                                    <th>Match Type</th>
                                    <th>Suggested Bid</th>
                                    <th>Your Max CPC Bid</th>
                                    <th>Estimated Rank</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>oversized t shirt</strong></td>
                                    <td>Broad Match</td>
                                    <td>₹4.20</td>
                                    <td>₹4.50</td>
                                    <td><span class="dso-badge dso-badge-green">Top of Search (#1)</span></td>
                                </tr>
                                <tr>
                                    <td><strong>cotton printed tshirt men</strong></td>
                                    <td>Exact Match</td>
                                    <td>₹5.10</td>
                                    <td>₹5.25</td>
                                    <td><span class="dso-badge dso-badge-green">Top of Search (#2)</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function promotions() {
        $this->render();
    }

    public function performance() {
        ?>
        <div class="dso-page dso-ad-performance">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <a href="?section=advertising">Advertising</a>
                        <span>/</span>
                        <span>RoAS & ACoS</span>
                    </div>
                    <h1 class="dso-page-title">Advertising Return & Attribution Report</h1>
                    <p class="dso-page-subtitle">Measure Advertising Cost of Sales (ACoS) and long-term customer acquisition cost (CAC)</p>
                </div>
            </div>

            <div class="dso-stats-row">
                <div class="dso-stat-card">
                    <span class="dso-stat-label">Total Ad Spend</span>
                    <span class="dso-stat-val">₹5,200.00</span>
                    <span class="dso-stat-sub">Last 30 days</span>
                </div>
                <div class="dso-stat-card">
                    <span class="dso-stat-label">Attributed Ad Sales</span>
                    <span class="dso-stat-val dso-text-success">₹25,700.00</span>
                    <span class="dso-stat-sub">Direct 7-day conversions</span>
                </div>
                <div class="dso-stat-card">
                    <span class="dso-stat-label">Average ACoS</span>
                    <span class="dso-stat-val dso-text-primary">20.2%</span>
                    <span class="dso-stat-sub">Target: &lt;25%</span>
                </div>
            </div>
        </div>
        <?php
    }
}
