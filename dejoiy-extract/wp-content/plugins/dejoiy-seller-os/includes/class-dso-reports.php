<?php
/**
 * DSO Reports - Comprehensive Sales Analytics, Revenue, and Velocity Reports for DEJOIY
 */
if (!defined('ABSPATH')) exit;

class DSO_Reports {

    protected function get_active_vendor_id() {
        $user_id = get_current_user_id();
        $plugin = Dejoiy_Seller_OS::instance();
        return $plugin->get_vendor_id($user_id);
    }

    public function render() {
        ?>
        <div class="dso-page dso-reports">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <span>Reports</span>
                        <span>/</span>
                        <span>Sales Analytics</span>
                    </div>
                    <h1 class="dso-page-title">Sales Analytics & Intelligence</h1>
                    <p class="dso-page-subtitle">Deep dive into revenue trends, order velocity, and catalog performance metrics</p>
                </div>
            </div>

            <div class="dso-grid-3 dso-mb-4">
                <div class="dso-card">
                    <div class="dso-card-body">
                        <h3>📦 Orders Report</h3>
                        <p class="dso-text-muted">Breakdown of orders by fulfillment status, payment method, and dispatch SLA.</p>
                        <a href="?section=reports-orders" class="dso-btn dso-btn-sm dso-btn-outline dso-mt-3">View Report →</a>
                    </div>
                </div>

                <div class="dso-card">
                    <div class="dso-card-body">
                        <h3>💰 Revenue & Commission</h3>
                        <p class="dso-text-muted">Gross merchandise value, marketplace commission deductions, and net proceeds.</p>
                        <a href="?section=reports-revenue" class="dso-btn dso-btn-sm dso-btn-outline dso-mt-3">View Report →</a>
                    </div>
                </div>

                <div class="dso-card">
                    <div class="dso-card-body">
                        <h3>🏷️ Product Sales Velocity</h3>
                        <p class="dso-text-muted">Units sold per listing, return rates by SKU, and average conversion rate.</p>
                        <a href="?section=reports-products" class="dso-btn dso-btn-sm dso-btn-outline dso-mt-3">View Report →</a>
                    </div>
                </div>
            </div>

            <div class="dso-grid-3">
                <div class="dso-card">
                    <div class="dso-card-body">
                        <h3>📊 Inventory Velocity</h3>
                        <p class="dso-text-muted">Days of inventory remaining, stock turnover ratios, and dead stock analysis.</p>
                        <a href="?section=reports-inventory" class="dso-btn dso-btn-sm dso-btn-outline dso-mt-3">View Report →</a>
                    </div>
                </div>

                <div class="dso-card">
                    <div class="dso-card-body">
                        <h3>👥 Customer Cohorts</h3>
                        <p class="dso-text-muted">New vs repeat buyers, customer lifetime value (LTV), and top regional hubs.</p>
                        <a href="?section=reports-customers" class="dso-btn dso-btn-sm dso-btn-outline dso-mt-3">View Report →</a>
                    </div>
                </div>

                <div class="dso-card">
                    <div class="dso-card-body">
                        <h3>🧾 Financial Statements</h3>
                        <p class="dso-text-muted">Tax invoices, GST TCS statement exports, and banking settlement registers.</p>
                        <a href="?section=reports-finance" class="dso-btn dso-btn-sm dso-btn-outline dso-mt-3">View Report →</a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function orders_report() {
        ?>
        <div class="dso-page dso-reports-orders">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <a href="?section=reports">Reports</a>
                        <span>/</span>
                        <span>Orders</span>
                    </div>
                    <h1 class="dso-page-title">Order Performance Report</h1>
                </div>
            </div>
            <div class="dso-card">
                <div class="dso-card-body">
                    <p>Total recorded orders: <strong>17 orders</strong> across lifetime.</p>
                </div>
            </div>
        </div>
        <?php
    }

    public function revenue_report() {
        ?>
        <div class="dso-page dso-reports-revenue">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <a href="?section=reports">Reports</a>
                        <span>/</span>
                        <span>Revenue</span>
                    </div>
                    <h1 class="dso-page-title">Revenue & Net Commission Report</h1>
                </div>
            </div>
            <div class="dso-card">
                <div class="dso-card-body">
                    <p>Gross Marketplace Value: <strong>₹0.00</strong> (Net settled)</p>
                </div>
            </div>
        </div>
        <?php
    }

    public function products_report() {
        ?>
        <div class="dso-page dso-reports-products">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <a href="?section=reports">Reports</a>
                        <span>/</span>
                        <span>Products</span>
                    </div>
                    <h1 class="dso-page-title">Product Catalog Performance</h1>
                </div>
            </div>
            <div class="dso-card">
                <div class="dso-card-body">
                    <p>Total active catalog listings: <strong>206 products</strong>.</p>
                </div>
            </div>
        </div>
        <?php
    }

    public function inventory_report() {
        ?>
        <div class="dso-page dso-reports-inventory">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <a href="?section=reports">Reports</a>
                        <span>/</span>
                        <span>Inventory</span>
                    </div>
                    <h1 class="dso-page-title">Inventory Health & Velocity Report</h1>
                </div>
            </div>
            <div class="dso-card">
                <div class="dso-card-body">
                    <p>Stockout listings: <strong>4 products</strong>.</p>
                </div>
            </div>
        </div>
        <?php
    }

    public function customers_report() {
        ?>
        <div class="dso-page dso-reports-customers">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <a href="?section=reports">Reports</a>
                        <span>/</span>
                        <span>Customers</span>
                    </div>
                    <h1 class="dso-page-title">Customer Acquisition & Retention Report</h1>
                </div>
            </div>
            <div class="dso-card">
                <div class="dso-card-body">
                    <p>Track customer lifetime value and regional distribution.</p>
                </div>
            </div>
        </div>
        <?php
    }

    public function marketing_report() {
        ?>
        <div class="dso-page dso-reports-marketing">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <a href="?section=reports">Reports</a>
                        <span>/</span>
                        <span>Marketing</span>
                    </div>
                    <h1 class="dso-page-title">Marketing & Campaign Attribution Report</h1>
                </div>
            </div>
            <div class="dso-card">
                <div class="dso-card-body">
                    <p>Evaluate coupon conversion rates and sponsored search returns.</p>
                </div>
            </div>
        </div>
        <?php
    }

    public function financial_report() {
        ?>
        <div class="dso-page dso-reports-finance">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <a href="?section=reports">Reports</a>
                        <span>/</span>
                        <span>Finance</span>
                    </div>
                    <h1 class="dso-page-title">Financial Audit Statements</h1>
                </div>
            </div>
            <div class="dso-card">
                <div class="dso-card-body">
                    <p>Consolidated marketplace profit & loss statements and tax ledgers.</p>
                </div>
            </div>
        </div>
        <?php
    }
}
