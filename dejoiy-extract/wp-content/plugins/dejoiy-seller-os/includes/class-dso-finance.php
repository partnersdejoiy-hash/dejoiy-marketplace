<?php
/**
 * DSO Finance - Comprehensive Treasury, Payouts, Ledgers, and GST Statements for DEJOIY
 */
if (!defined('ABSPATH')) exit;

class DSO_Finance {

    protected function get_active_vendor_id() {
        $user_id = get_current_user_id();
        $plugin = Dejoiy_Seller_OS::instance();
        return $plugin->get_vendor_id($user_id);
    }

    public function render() {
        ?>
        <div class="dso-page dso-finance">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <span>Payments</span>
                        <span>/</span>
                        <span>Overview</span>
                    </div>
                    <h1 class="dso-page-title">Finance & Payouts Treasury</h1>
                    <p class="dso-page-subtitle">Track settled marketplace earnings, commission deductions, GST TCS credits, and weekly bank payouts</p>
                </div>
                <div class="dso-page-actions">
                    <a href="?section=finance-statements" class="dso-btn dso-btn-outline">Tax Statements</a>
                    <a href="?section=withdrawals" class="dso-btn dso-btn-primary">Request Early Payout</a>
                </div>
            </div>

            <div class="dso-stats-row">
                <div class="dso-stat-card">
                    <span class="dso-stat-label">Available for Withdrawal</span>
                    <span class="dso-stat-val dso-text-success">₹0.00</span>
                    <span class="dso-stat-sub">Ready to payout</span>
                </div>
                <div class="dso-stat-card">
                    <span class="dso-stat-label">Pending In Return Window</span>
                    <span class="dso-stat-val dso-text-warning">₹0.00</span>
                    <span class="dso-stat-sub">7-day buyer return buffer</span>
                </div>
                <div class="dso-stat-card">
                    <span class="dso-stat-label">Total Lifetime Paid Out</span>
                    <span class="dso-stat-val">₹0.00</span>
                    <span class="dso-stat-sub">Direct NEFT/RTGS transfers</span>
                </div>
            </div>

            <div class="dso-card dso-mb-4">
                <div class="dso-card-header">
                    <h3 class="dso-card-title">Settlement Schedule & Bank Account</h3>
                </div>
                <div class="dso-card-body">
                    <div class="dso-grid-2">
                        <div class="dso-info-box">
                            <h4>🏦 Registered Payout Bank Account</h4>
                            <p class="dso-text-muted">Direct Deposit: <strong>HDFC Bank ••••••4091</strong><br/>IFSC: HDFC0001234 • Account Holder: DEJOIY Verified Merchant</p>
                            <span class="dso-badge dso-badge-green">Penny Drop Verified ✓</span>
                        </div>
                        <div class="dso-info-box">
                            <h4>📅 Automated Weekly Payout Cycle</h4>
                            <p class="dso-text-muted">Settlements are processed automatically every <strong>Wednesday</strong> directly into your verified bank account via RBI IMPS/NEFT rail.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Ledger Activity -->
            <div class="dso-card">
                <div class="dso-card-header">
                    <h3 class="dso-card-title">Recent Transactions & Credits</h3>
                    <a href="?section=finance-transactions" class="dso-link-action">View Full Ledger →</a>
                </div>
                <div class="dso-card-body dso-p-0">
                    <div class="dso-table-responsive">
                        <table class="dso-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Reference #</th>
                                    <th>Description</th>
                                    <th>Gross Amount</th>
                                    <th>Fee & GST</th>
                                    <th>Net Payout Credit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6" class="dso-p-4 dso-text-center">No transactions recorded yet. Delivered orders will generate financial credits here.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function transactions() {
        ?>
        <div class="dso-page dso-finance-transactions">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <a href="?section=finance">Finance</a>
                        <span>/</span>
                        <span>Ledger</span>
                    </div>
                    <h1 class="dso-page-title">Transaction Ledger</h1>
                    <p class="dso-page-subtitle">Itemized audit of all order credits, marketplace commissions, logistics fees, and payout debits</p>
                </div>
            </div>

            <div class="dso-card">
                <div class="dso-card-body dso-p-0">
                    <div class="dso-table-responsive">
                        <table class="dso-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Order / Reference</th>
                                    <th>Amount</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td colspan="5" class="dso-p-4 dso-text-center">No ledger entries recorded yet.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function commissions() {
        ?>
        <div class="dso-page dso-commissions">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <a href="?section=finance">Finance</a>
                        <span>/</span>
                        <span>Commissions</span>
                    </div>
                    <h1 class="dso-page-title">Commission Breakdown</h1>
                    <p class="dso-page-subtitle">Detailed item-by-item breakdown of DEJOIY marketplace referral fees</p>
                </div>
            </div>

            <div class="dso-card">
                <div class="dso-card-body">
                    <p class="dso-text-muted">Your store is on the Standard Seller Tier with 8% referral fee on Apparel, 10% on Home & Kitchen, and 6% on Electronics.</p>
                </div>
            </div>
        </div>
        <?php
    }

    public function withdrawals() {
        ?>
        <div class="dso-page dso-withdrawals">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <a href="?section=finance">Finance</a>
                        <span>/</span>
                        <span>Withdrawals</span>
                    </div>
                    <h1 class="dso-page-title">Withdrawal Requests</h1>
                    <p class="dso-page-subtitle">Request manual on-demand disbursements of available balances</p>
                </div>
            </div>

            <div class="dso-card">
                <div class="dso-card-body">
                    <div class="dso-form-group" style="max-width: 400px;">
                        <label class="dso-label">Available Balance</label>
                        <h3>₹0.00</h3>
                        <p class="dso-text-muted">Minimum withdrawal threshold is ₹500.00. Automatic settlements process every Wednesday.</p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function payouts() {
        ?>
        <div class="dso-page dso-payouts">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <a href="?section=finance">Finance</a>
                        <span>/</span>
                        <span>Payout History</span>
                    </div>
                    <h1 class="dso-page-title">Settlement Payout History</h1>
                    <p class="dso-page-subtitle">Bank transfer UTR numbers and disbursement reconciliation records</p>
                </div>
            </div>

            <div class="dso-card">
                <div class="dso-card-body dso-p-0">
                    <div class="dso-table-responsive">
                        <table class="dso-table">
                            <thead>
                                <tr>
                                    <th>Payout Date</th>
                                    <th>Settlement ID</th>
                                    <th>Bank UTR Reference</th>
                                    <th>Amount Disbursed</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td colspan="5" class="dso-p-4 dso-text-center">No past disbursements recorded.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function statements() {
        ?>
        <div class="dso-page dso-statements">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <a href="?section=finance">Finance</a>
                        <span>/</span>
                        <span>Tax Statements</span>
                    </div>
                    <h1 class="dso-page-title">GST Statements & Tax Invoices</h1>
                    <p class="dso-page-subtitle">Download monthly GST TCS certificates (Form GSTR-8) and marketplace commission tax invoices</p>
                </div>
            </div>

            <div class="dso-card">
                <div class="dso-card-body dso-p-0">
                    <div class="dso-table-responsive">
                        <table class="dso-table">
                            <thead>
                                <tr>
                                    <th>Billing Period</th>
                                    <th>Document Type</th>
                                    <th>GSTIN Disclosed</th>
                                    <th>Download</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>May 2026</strong></td>
                                    <td>Monthly Marketplace Commission Invoice</td>
                                    <td>07AABCT9876C1Z2</td>
                                    <td><button class="dso-btn dso-btn-sm dso-btn-outline">Download PDF</button></td>
                                </tr>
                                <tr>
                                    <td><strong>April 2026</strong></td>
                                    <td>GST TCS Credit Certificate (1% TCS)</td>
                                    <td>07AABCT9876C1Z2</td>
                                    <td><button class="dso-btn dso-btn-sm dso-btn-outline">Download PDF</button></td>
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
