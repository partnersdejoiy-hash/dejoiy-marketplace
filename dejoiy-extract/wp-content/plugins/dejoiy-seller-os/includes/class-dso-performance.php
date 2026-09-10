<?php
/**
 * DSO Performance - Marketplace Service Levels, Tiers & Scorecards for DEJOIY
 */
if (!defined('ABSPATH')) exit;

class DSO_Performance {

    public function render() {
        ?>
        <div class="dso-page dso-performance">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <span>Performance</span>
                        <span>/</span>
                        <span>Store Health</span>
                    </div>
                    <h1 class="dso-page-title">Store Performance Scorecard</h1>
                    <p class="dso-page-subtitle">Track operational service levels, customer satisfaction scores, and seller badge tiers</p>
                </div>
            </div>

            <div class="dso-stats-row">
                <div class="dso-stat-card">
                    <span class="dso-stat-label">Current Seller Tier</span>
                    <span class="dso-stat-val dso-text-primary">Tier 1 (Platinum)</span>
                    <span class="dso-stat-sub">Top marketplace standing</span>
                </div>
                <div class="dso-stat-card">
                    <span class="dso-stat-label">Order Defect Rate (ODR)</span>
                    <span class="dso-stat-val dso-text-success">0.0%</span>
                    <span class="dso-stat-sub">Target: &lt; 1%</span>
                </div>
                <div class="dso-stat-card">
                    <span class="dso-stat-label">Late Dispatch Rate</span>
                    <span class="dso-stat-val dso-text-success">0.0%</span>
                    <span class="dso-stat-sub">Target: &lt; 2%</span>
                </div>
                <div class="dso-stat-card">
                    <span class="dso-stat-label">Customer CSAT</span>
                    <span class="dso-stat-val dso-text-success">98.5%</span>
                    <span class="dso-stat-sub">Positive sentiment</span>
                </div>
            </div>

            <div class="dso-grid-2 dso-mb-4">
                <div class="dso-card">
                    <div class="dso-card-header"><h3 class="dso-card-title">Fulfillment SLA Standards</h3></div>
                    <div class="dso-card-body">
                        <ul class="dso-clean-list">
                            <li>✓ Same-day label generation: <strong>99.2% compliance</strong></li>
                            <li>✓ On-time courier handover: <strong>98.8% compliance</strong></li>
                            <li>✓ Tracking numbers uploaded within 12h: <strong>100%</strong></li>
                        </ul>
                    </div>
                </div>

                <div class="dso-card">
                    <div class="dso-card-header"><h3 class="dso-card-title">Customer Feedback Score</h3></div>
                    <div class="dso-card-body">
                        <ul class="dso-clean-list">
                            <li>✓ 5-Star Reviews: <strong>94%</strong></li>
                            <li>✓ Return Requests: <strong>0%</strong></li>
                            <li>✓ Customer Support Tickets: <strong>0 unresolved</strong></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function product_health() {
        ?>
        <div class="dso-page dso-perf-products">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <a href="?section=performance">Performance</a>
                        <span>/</span>
                        <span>Product Health</span>
                    </div>
                    <h1 class="dso-page-title">Catalog Health & Defect Rate</h1>
                </div>
            </div>
            <div class="dso-card">
                <div class="dso-card-body">
                    <p>All listings meet quality standards. Zero product defect complaints received.</p>
                </div>
            </div>
        </div>
        <?php
    }

    public function seller_performance() {
        $this->render();
    }

    public function customer_satisfaction() {
        ?>
        <div class="dso-page dso-perf-csat">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <a href="?section=performance">Performance</a>
                        <span>/</span>
                        <span>CSAT</span>
                    </div>
                    <h1 class="dso-page-title">Customer Satisfaction (CSAT)</h1>
                </div>
            </div>
            <div class="dso-card">
                <div class="dso-card-body">
                    <p>Customer satisfaction score is <strong>98.5%</strong> across all buyer feedback.</p>
                </div>
            </div>
        </div>
        <?php
    }

    public function delivery_performance() {
        ?>
        <div class="dso-page dso-perf-delivery">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <a href="?section=performance">Performance</a>
                        <span>/</span>
                        <span>Delivery SLA</span>
                    </div>
                    <h1 class="dso-page-title">Delivery SLA Performance</h1>
                </div>
            </div>
            <div class="dso-card">
                <div class="dso-card-body">
                    <p>Carrier on-time delivery rate is <strong>98%</strong> nationwide.</p>
                </div>
            </div>
        </div>
        <?php
    }
}
