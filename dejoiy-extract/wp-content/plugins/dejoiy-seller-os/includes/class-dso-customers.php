<?php
/**
 * DSO Customers
 */
if (!defined('ABSPATH')) exit;

class DSO_Customers {

    public function render() {
        $user_id = get_current_user_id();
        $plugin = Dejoiy_Seller_OS::instance();
        $vendor_id = $plugin->get_vendor_id($user_id);
        $customers = $this->get_customers($vendor_id);

        ?>
        <div class="dso-page dso-customers">
            <div class="dso-page-header">
                <div>
                    <h1>Customers</h1>
                    <p>View customers who purchased from your store</p>
                </div>
            </div>

            <div class="dso-card">
                <div class="dso-filters-bar">
                    <div class="dso-search-box">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" placeholder="Search customers..." id="dso-customer-search" class="dso-input" />
                    </div>
                </div>

                <div class="dso-table-responsive">
                    <table class="dso-table">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Orders</th>
                                <th>Total Spent</th>
                                <th>Avg. Order</th>
                                <th>Last Order</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($customers)): ?>
                                <tr class="dso-empty-row">
                                    <td colspan="5">
                                        <div class="dso-empty-state">
                                            <div class="dso-empty-icon">👥</div>
                                            <h3>No customers yet</h3>
                                            <p>Customer data will appear here once you receive orders.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($customers as $c): ?>
                                    <tr>
                                        <td>
                                            <div class="dso-customer-cell">
                                                <div class="dso-avatar"><?php echo strtoupper(substr($c['name'], 0, 1)) ?></div>
                                                <div>
                                                    <span class="dso-customer-name"><?php echo esc_html($c['name']) ?></span>
                                                    <span class="dso-customer-email"><?php echo esc_html($c['email']) ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo $c['order_count'] ?></td>
                                        <td><?php echo wc_price($c['total_spent']) ?></td>
                                        <td><?php echo wc_price($c['avg_order']) ?></td>
                                        <td><?php echo esc_html($c['last_order_date']) ?></td>
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

    public function get_customers($vendor_id) {
        global $wpdb;
        if (!$vendor_id) return [];

        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT customer_name, customer_email, customer_id,
                    COUNT(*) as order_count,
                    SUM(order_total) as total_spent,
                    AVG(order_total) as avg_order,
                    MAX(order_date) as last_order
            FROM {$wpdb->prefix}wcfm_marketplace_orders
            WHERE vendor_id = %d AND order_status IN ('wc-completed', 'wc-processing')
            GROUP BY customer_email
            ORDER BY total_spent DESC",
            $vendor_id
        ));

        $customers = [];
        foreach ($rows as $row) {
            $customers[] = [
                'name' => $row->customer_name ?: 'Guest',
                'email' => $row->customer_email ?: '—',
                'order_count' => intval($row->order_count),
                'total_spent' => floatval($row->total_spent),
                'avg_order' => round(floatval($row->avg_order), 2),
                'last_order_date' => $row->last_order ? date('M j, Y', strtotime($row->last_order)) : '—',
            ];
        }

        return $customers;
    }
}
