<?php
/**
 * DSO Finance - Earnings & Withdrawals
 */
if (!defined('ABSPATH')) exit;

class DSO_Finance {

    public function render() {
        $user_id = get_current_user_id();
        $plugin = Dejoiy_Seller_OS::instance();
        $vendor_id = $plugin->get_vendor_id($user_id);
        $data = $this->get_finance_data($vendor_id);

        ?>
        <div class="dso-page dso-finance">
            <div class="dso-page-header">
                <div>
                    <h1>Finance</h1>
                    <p>Track your earnings, commissions, and payouts</p>
                </div>
            </div>

            <!-- Finance KPIs -->
            <div class="dso-kpi-grid dso-kpi-grid-4">
                <div class="dso-kpi-card dso-kpi-green">
                    <div class="dso-kpi-content">
                        <span class="dso-kpi-label">Total Earnings</span>
                        <span class="dso-kpi-value"><?php echo wc_price($data['total_earnings']) ?></span>
                    </div>
                </div>
                <div class="dso-kpi-card dso-kpi-teal">
                    <div class="dso-kpi-content">
                        <span class="dso-kpi-label">Available Balance</span>
                        <span class="dso-kpi-value"><?php echo wc_price($data['available']) ?></span>
                    </div>
                </div>
                <div class="dso-kpi-card dso-kpi-orange">
                    <div class="dso-kpi-content">
                        <span class="dso-kpi-label">Pending Balance</span>
                        <span class="dso-kpi-value"><?php echo wc_price($data['pending']) ?></span>
                    </div>
                </div>
                <div class="dso-kpi-card dso-kpi-purple">
                    <div class="dso-kpi-content">
                        <span class="dso-kpi-label">Total Withdrawn</span>
                        <span class="dso-kpi-value"><?php echo wc_price($data['withdrawn']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Earnings Chart -->
            <div class="dso-card dso-card-chart">
                <div class="dso-card-header"><h3>Earnings Over Time</h3></div>
                <div class="dso-chart-container">
                    <canvas id="dso-earnings-chart"></canvas>
                </div>
            </div>

            <!-- Ledger -->
            <div class="dso-card">
                <div class="dso-card-header">
                    <h3>Transaction Ledger</h3>
                    <a href="?section=withdrawals" class="dso-link">Withdraw Funds →</a>
                </div>
                <div class="dso-table-responsive">
                    <table class="dso-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Credit</th>
                                <th>Debit</th>
                                <th>Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($data['ledger'])): ?>
                                <tr class="dso-empty-row"><td colspan="6"><div class="dso-empty-inline"><p>No transactions yet</p></div></td></tr>
                            <?php else: ?>
                                <?php foreach ($data['ledger'] as $entry): ?>
                                    <tr>
                                        <td><?php echo esc_html($entry['date']) ?></td>
                                        <td><?php echo $entry['type_badge'] ?></td>
                                        <td><?php echo esc_html($entry['description']) ?></td>
                                        <td class="dso-text-green"><?php echo $entry['credit'] > 0 ? wc_price($entry['credit']) : '—' ?></td>
                                        <td class="dso-text-red"><?php echo $entry['debit'] > 0 ? wc_price($entry['debit']) : '—' ?></td>
                                        <td><strong><?php echo wc_price($entry['balance']) ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            DSO.initFinance(<?php echo wp_json_encode($data['chart_data']) ?>);
        });
        </script>
        <?php
    }

    /**
     * Withdrawals page
     */
    public function withdrawals() {
        $user_id = get_current_user_id();
        $plugin = Dejoiy_Seller_OS::instance();
        $vendor_id = $plugin->get_vendor_id($user_id);
        $data = $this->get_finance_data($vendor_id);

        // Handle withdrawal request
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dso_request_withdrawal'])) {
            check_admin_referer('dso_withdrawal');

            $amount = floatval($_POST['withdrawal_amount'] ?? 0);
            if ($amount > 0 && $amount <= $data['available']) {
                global $wpdb;
                $wpdb->insert($wpdb->prefix . 'wcfm_marketplace_withdraw_request', [
                    'vendor_id' => $vendor_id,
                    'withdraw_amount' => $amount,
                    'withdraw_status' => 0,
                    'created' => current_time('mysql'),
                ]);

                wp_redirect('?section=withdrawals&requested=1');
                exit;
            }
        }

        ?>
        <div class="dso-page dso-withdrawals">
            <div class="dso-page-header">
                <div>
                    <h1>Withdrawals</h1>
                    <p>Request and track your payout withdrawals</p>
                </div>
            </div>

            <div class="dso-grid-2">
                <!-- Request Withdrawal -->
                <div class="dso-card">
                    <div class="dso-card-header"><h3>Request Withdrawal</h3></div>
                    <div class="dso-card-body">
                        <div class="dso-balance-display">
                            <span class="dso-balance-label">Available to Withdraw</span>
                            <span class="dso-balance-amount"><?php echo wc_price($data['available']) ?></span>
                        </div>

                        <?php if ($data['available'] > 0): ?>
                            <form method="post" class="dso-form">
                                <?php wp_nonce_field('dso_withdrawal'); ?>
                                <div class="dso-form-group">
                                    <label for="withdrawal_amount">Amount (₹)</label>
                                    <input type="number" id="withdrawal_amount" name="withdrawal_amount" class="dso-input" step="0.01" min="1" max="<?php echo $data['available'] ?>" required placeholder="Enter amount" />
                                </div>
                                <button type="submit" name="dso_request_withdrawal" value="1" class="dso-btn dso-btn-primary dso-btn-full">
                                    Request Withdrawal
                                </button>
                            </form>
                        <?php else: ?>
                            <p class="dso-text-muted">No funds available for withdrawal yet.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Withdrawal History -->
                <div class="dso-card">
                    <div class="dso-card-header"><h3>Withdrawal History</h3></div>
                    <div class="dso-card-body">
                        <?php if (empty($data['withdrawals'])): ?>
                            <div class="dso-empty-inline">
                                <p>No withdrawal history</p>
                            </div>
                        <?php else: ?>
                            <div class="dso-withdrawal-list">
                                <?php foreach ($data['withdrawals'] as $w): ?>
                                    <div class="dso-withdrawal-item">
                                        <div class="dso-wi-info">
                                            <strong><?php echo wc_price($w['amount']) ?></strong>
                                            <span><?php echo esc_html($w['date']) ?></span>
                                        </div>
                                        <div class="dso-wi-status">
                                            <?php echo $w['status_badge'] ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function get_finance_data($vendor_id) {
        global $wpdb;

        $total_earnings = 0;
        $withdrawn = 0;
        $available = 0;
        $pending = 0;
        $ledger = [];
        $chart_data = ['labels' => [], 'earnings' => []];
        $withdrawals = [];

        if ($vendor_id) {
            // Total earnings
            $row = $wpdb->get_row($wpdb->prepare(
                "SELECT COALESCE(SUM(item_total), 0) as total
                FROM {$wpdb->prefix}wcfm_marketplace_orders
                WHERE vendor_id = %d AND order_status IN ('wc-completed', 'wc-processing')",
                $vendor_id
            ));
            $total_earnings = floatval($row->total ?? 0);

            // Ledger
            $ledger_rows = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}wcfm_marketplace_vendor_ledger
                WHERE vendor_id = %d ORDER BY id DESC LIMIT 20",
                $vendor_id
            ));

            foreach ($ledger_rows as $lr) {
                $type = $lr->credit > 0 ? 'credit' : 'debit';
                $ledger[] = [
                    'date' => date('M j, Y', strtotime($lr->created)),
                    'type' => $type,
                    'type_badge' => '<span class="dso-badge dso-badge-' . ($type === 'credit' ? 'green' : 'red') . '">' . ucfirst($type) . '</span>',
                    'description' => $lr->reference ?? 'Transaction',
                    'credit' => floatval($lr->credit),
                    'debit' => floatval($lr->debit),
                    'balance' => floatval($lr->credit - $lr->debit),
                ];
            }

            $available = !empty($ledger) ? max(0, floatval($ledger[0]['balance'])) : 0;

            // Withdrawals
            $withdraw_rows = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}wcfm_marketplace_withdraw_request
                WHERE vendor_id = %d ORDER BY id DESC",
                $vendor_id
            ));

            foreach ($withdraw_rows as $wr) {
                $status_map = [
                    0 => ['Pending', 'dso-badge-orange'],
                    1 => ['Processing', 'dso-badge-blue'],
                    2 => ['Completed', 'dso-badge-green'],
                    3 => ['Cancelled', 'dso-badge-red'],
                ];
                $s = $status_map[$wr->withdraw_status] ?? ['Unknown', 'dso-badge-gray'];

                $withdrawals[] = [
                    'amount' => floatval($wr->withdraw_amount),
                    'date' => date('M j, Y', strtotime($wr->created)),
                    'status' => $wr->withdraw_status,
                    'status_badge' => '<span class="dso-badge ' . $s[1] . '">' . $s[0] . '</span>',
                ];

                if ($wr->withdraw_status < 2) {
                    $pending += floatval($wr->withdraw_amount);
                } else {
                    $withdrawn += floatval($wr->withdraw_amount);
                }
            }

            // Chart data
            for ($i = 29; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-{$i} days"));
                $chart_data['labels'][] = date('M j', strtotime($date));

                $cr = $wpdb->get_var($wpdb->prepare(
                    "SELECT COALESCE(SUM(item_total), 0)
                    FROM {$wpdb->prefix}wcfm_marketplace_orders
                    WHERE vendor_id = %d AND order_status IN ('wc-completed', 'wc-processing')
                    AND created >= %s AND created <= %s",
                    $vendor_id, $date . ' 00:00:00', $date . ' 23:59:59'
                ));

                $chart_data['earnings'][] = floatval($cr);
            }
        }

        return compact('total_earnings', 'available', 'pending', 'withdrawn', 'ledger', 'chart_data', 'withdrawals');
    }
}
