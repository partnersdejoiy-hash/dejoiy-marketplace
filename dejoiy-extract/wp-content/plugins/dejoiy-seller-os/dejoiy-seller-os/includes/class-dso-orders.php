<?php
/**
 * DSO Orders - Order Management
 */
if (!defined('ABSPATH')) exit;

class DSO_Orders {

    public function render() {
        $user_id = get_current_user_id();
        $plugin = Dejoiy_Seller_OS::instance();
        $vendor_id = $plugin->get_vendor_id($user_id);
        $orders = $this->get_orders($vendor_id);
        $stats = $this->get_order_stats($vendor_id);

        ?>
        <div class="dso-page dso-orders">
            <div class="dso-page-header">
                <div>
                    <h1>Orders</h1>
                    <p>Manage and fulfill your customer orders</p>
                </div>
            </div>

            <!-- Order Stats -->
            <div class="dso-mini-stats">
                <div class="dso-mini-stat">
                    <span class="dso-mini-stat-value"><?php echo $stats['total'] ?></span>
                    <span class="dso-mini-stat-label">Total</span>
                </div>
                <div class="dso-mini-stat dso-mini-stat-highlight">
                    <span class="dso-mini-stat-value"><?php echo $stats['pending'] ?></span>
                    <span class="dso-mini-stat-label">Pending</span>
                </div>
                <div class="dso-mini-stat">
                    <span class="dso-mini-stat-value"><?php echo $stats['processing'] ?></span>
                    <span class="dso-mini-stat-label">Processing</span>
                </div>
                <div class="dso-mini-stat">
                    <span class="dso-mini-stat-value"><?php echo $stats['completed'] ?></span>
                    <span class="dso-mini-stat-label">Completed</span>
                </div>
                <div class="dso-mini-stat">
                    <span class="dso-mini-stat-value"><?php echo $stats['cancelled'] ?></span>
                    <span class="dso-mini-stat-label">Cancelled</span>
                </div>
            </div>

            <!-- Filters -->
            <div class="dso-card">
                <div class="dso-filters-bar">
                    <div class="dso-search-box">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" placeholder="Search orders..." id="dso-order-search" class="dso-input" />
                    </div>
                    <div class="dso-filter-group">
                        <select id="dso-order-status" class="dso-select">
                            <option value="">All Status</option>
                            <option value="wc-pending">Pending</option>
                            <option value="wc-processing">Processing</option>
                            <option value="wc-on-hold">On Hold</option>
                            <option value="wc-completed">Completed</option>
                            <option value="wc-cancelled">Cancelled</option>
                            <option value="wc-refunded">Refunded</option>
                        </select>
                    </div>
                </div>

                <div class="dso-table-responsive">
                    <table class="dso-table dso-table-orders">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($orders)): ?>
                                <tr class="dso-empty-row">
                                    <td colspan="8">
                                        <div class="dso-empty-state">
                                            <div class="dso-empty-icon">🛒</div>
                                            <h3>No orders yet</h3>
                                            <p>Once your first order arrives, it will appear here.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($orders as $order): ?>
                                    <tr class="dso-order-row" data-status="<?php echo esc_attr($order['status']) ?>">
                                        <td>
                                            <a href="?section=order-detail&id=<?php echo $order['id'] ?>" class="dso-order-link">
                                                #<?php echo esc_html($order['number']) ?>
                                            </a>
                                        </td>
                                        <td><?php echo esc_html($order['date']) ?></td>
                                        <td>
                                            <span class="dso-customer-name"><?php echo esc_html($order['customer']) ?></span>
                                            <?php if (!empty($order['email'])): ?>
                                                <span class="dso-customer-email"><?php echo esc_html($order['email']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo intval($order['item_count']) ?> item(s)</td>
                                        <td class="dso-order-total"><?php echo $order['total'] ?></td>
                                        <td><?php echo $order['payment_status'] ?></td>
                                        <td><?php echo $order['status_badge'] ?></td>
                                        <td>
                                            <div class="dso-actions">
                                                <a href="?section=order-detail&id=<?php echo $order['id'] ?>" class="dso-action-btn" title="View">👁️</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (!empty($orders)): ?>
                <div class="dso-pagination">
                    <span class="dso-pagination-info">Showing <?php echo count($orders) ?> order(s)</span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Order Detail page
     */
    public function order_detail() {
        $order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$order_id) {
            wp_redirect('?section=orders');
            exit;
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            wp_die('Order not found.');
        }

        // Handle actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dso_order_action'])) {
            check_admin_referer('dso_order_action');

            $action = sanitize_text_field($_POST['dso_order_action']);
            $tracking = sanitize_text_field($_POST['tracking_number'] ?? '');

            switch ($action) {
                case 'processing':
                    $order->update_status('processing');
                    break;
                case 'complete':
                    $order->update_status('completed');
                    break;
                case 'cancel':
                    $order->update_status('cancelled');
                    break;
                case 'add_note':
                    $note = sanitize_textarea_field($_POST['order_note'] ?? '');
                    if ($note) {
                        $order->add_order_note($note, 0, true);
                    }
                    break;
                case 'add_tracking':
                    if ($tracking && function_exists('wc_ast_update_tracking')) {
                        // Shiprocket tracking
                        update_post_meta($order_id, '_wc_ast_tracking_number', $tracking);
                    }
                    $order->add_order_note('Tracking number: ' . $tracking);
                    break;
            }

            wp_redirect('?section=order-detail&id=' . $order_id . '&updated=1');
            exit;
        }

        $items = $order->get_items();
        ?>
        <div class="dso-page dso-order-detail">
            <div class="dso-page-header">
                <div>
                    <h1>Order #<?php echo $order->get_order_number() ?></h1>
                    <p>Placed on <?php echo $order->get_date_created()->format('F j, Y \a\t g:i A') ?></p>
                </div>
                <div class="dso-page-actions">
                    <a href="?section=orders" class="dso-btn dso-btn-secondary">← Back to Orders</a>
                </div>
            </div>

            <!-- Order Status Bar -->
            <div class="dso-order-status-bar">
                <div class="dso-status-step <?php echo in_array($order->get_status(), ['pending', 'processing', 'completed']) ? 'dso-status-active' : '' ?>">
                    <span class="dso-status-dot"></span>
                    <span>Order Placed</span>
                </div>
                <div class="dso-status-connector"></div>
                <div class="dso-status-step <?php echo in_array($order->get_status(), ['processing', 'completed']) ? 'dso-status-active' : '' ?>">
                    <span class="dso-status-dot"></span>
                    <span>Processing</span>
                </div>
                <div class="dso-status-connector"></div>
                <div class="dso-status-step <?php echo $order->get_status() === 'completed' ? 'dso-status-active' : '' ?>">
                    <span class="dso-status-dot"></span>
                    <span>Completed</span>
                </div>
            </div>

            <div class="dso-grid-2-1">
                <!-- Main Content -->
                <div>
                    <!-- Order Items -->
                    <div class="dso-card">
                        <div class="dso-card-header"><h3>Order Items</h3></div>
                        <div class="dso-card-body">
                            <table class="dso-table dso-table-items">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Qty</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="dso-product-cell">
                                                    <?php
                                                    $product = $item->get_product();
                                                    $img = $product ? get_the_post_thumbnail($product->get_id(), [40, 40]) : '';
                                                    if ($img): ?>
                                                        <div class="dso-product-thumb"><?php echo $img ?></div>
                                                    <?php endif; ?>
                                                    <span class="dso-product-name"><?php echo esc_html($item->get_name()) ?></span>
                                                </div>
                                            </td>
                                            <td><?php echo wc_price($item->get_total() / $item->get_quantity()) ?></td>
                                            <td><?php echo $item->get_quantity() ?></td>
                                            <td><?php echo wc_price($item->get_total()) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <!-- Totals -->
                            <div class="dso-order-totals">
                                <div class="dso-total-row"><span>Subtotal</span><span><?php echo wc_price($order->get_subtotal()) ?></span></div>
                                <div class="dso-total-row"><span>Shipping</span><span><?php echo wc_price($order->get_shipping_total()) ?></span></div>
                                <?php if ($order->get_discount_total() > 0): ?>
                                    <div class="dso-total-row"><span>Discount</span><span>-<?php echo wc_price($order->get_discount_total()) ?></span></div>
                                <?php endif; ?>
                                <div class="dso-total-row"><span>Tax</span><span><?php echo wc_price($order->get_total_tax()) ?></span></div>
                                <div class="dso-total-row dso-total-grand"><span>Total</span><span><?php echo wc_price($order->get_total()) ?></span></div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Timeline -->
                    <div class="dso-card">
                        <div class="dso-card-header"><h3>Order Timeline</h3></div>
                        <div class="dso-card-body">
                            <div class="dso-timeline">
                                <?php
                                $notes = wc_get_order_notes(['order_id' => $order_id]);
                                if (empty($notes)): ?>
                                    <div class="dso-timeline-empty">No timeline events yet.</div>
                                <?php else: ?>
                                    <?php foreach ($notes as $note): ?>
                                        <div class="dso-timeline-item">
                                            <div class="dso-timeline-dot"></div>
                                            <div class="dso-timeline-content">
                                                <p><?php echo wp_kses_post(nl2br($note->content)) ?></p>
                                                <span class="dso-timeline-date"><?php echo $note->date_created->format('M j, Y g:i A') ?></span>
                                                <span class="dso-timeline-author"><?php echo $note->customer_note ? 'Customer Note' : 'Seller Note' ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div>
                    <!-- Order Actions -->
                    <div class="dso-card">
                        <div class="dso-card-header"><h3>Actions</h3></div>
                        <div class="dso-card-body">
                            <form method="post" class="dso-form">
                                <?php wp_nonce_field('dso_order_action'); ?>

                                <?php if ($order->get_status() === 'pending'): ?>
                                    <button type="submit" name="dso_order_action" value="processing" class="dso-btn dso-btn-primary dso-btn-full dso-btn-mb">
                                        Mark as Processing
                                    </button>
                                <?php endif; ?>

                                <?php if (in_array($order->get_status(), ['pending', 'processing', 'on-hold'])): ?>
                                    <button type="submit" name="dso_order_action" value="complete" class="dso-btn dso-btn-success dso-btn-full dso-btn-mb">
                                        Mark as Completed
                                    </button>
                                <?php endif; ?>

                                <?php if (!in_array($order->get_status(), ['completed', 'cancelled', 'refunded'])): ?>
                                    <button type="submit" name="dso_order_action" value="cancel" class="dso-btn dso-btn-danger dso-btn-full dso-btn-mb"
                                        onclick="return confirm('Are you sure you want to cancel this order?')">
                                        Cancel Order
                                    </button>
                                <?php endif; ?>

                                <hr class="dso-divider" />

                                <div class="dso-form-group">
                                    <label>Add Tracking Number</label>
                                    <input type="text" name="tracking_number" class="dso-input" placeholder="Enter tracking number" />
                                </div>
                                <button type="submit" name="dso_order_action" value="add_tracking" class="dso-btn dso-btn-secondary dso-btn-full dso-btn-mb">
                                    Add Tracking
                                </button>

                                <hr class="dso-divider" />

                                <div class="dso-form-group">
                                    <label>Add Note</label>
                                    <textarea name="order_note" class="dso-textarea" rows="3" placeholder="Private note..."></textarea>
                                </div>
                                <button type="submit" name="dso_order_action" value="add_note" class="dso-btn dso-btn-secondary dso-btn-full">
                                    Add Note
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Customer Info -->
                    <div class="dso-card">
                        <div class="dso-card-header"><h3>Customer</h3></div>
                        <div class="dso-card-body">
                            <div class="dso-info-list">
                                <div class="dso-info-item">
                                    <span class="dso-info-label">Name</span>
                                    <span><?php echo esc_html($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()) ?></span>
                                </div>
                                <div class="dso-info-item">
                                    <span class="dso-info-label">Email</span>
                                    <span><?php echo esc_html($order->get_billing_email()) ?></span>
                                </div>
                                <div class="dso-info-item">
                                    <span class="dso-info-label">Phone</span>
                                    <span><?php echo esc_html($order->get_billing_phone()) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Billing Address -->
                    <div class="dso-card">
                        <div class="dso-card-header"><h3>Billing Address</h3></div>
                        <div class="dso-card-body">
                            <div class="dso-address">
                                <?php echo wp_kses_post(nl2br($order->get_formatted_billing_address())) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Address -->
                    <?php if ($order->has_shipping_address()): ?>
                    <div class="dso-card">
                        <div class="dso-card-header"><h3>Shipping Address</h3></div>
                        <div class="dso-card-body">
                            <div class="dso-address">
                                <?php echo wp_kses_post(nl2br($order->get_formatted_shipping_address())) ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Payment Info -->
                    <div class="dso-card">
                        <div class="dso-card-header"><h3>Payment</h3></div>
                        <div class="dso-card-body">
                            <div class="dso-info-list">
                                <div class="dso-info-item">
                                    <span class="dso-info-label">Method</span>
                                    <span><?php echo esc_html($order->get_payment_method_title()) ?></span>
                                </div>
                                <div class="dso-info-item">
                                    <span class="dso-info-label">Status</span>
                                    <span><?php echo esc_html($order->get_status()) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Get orders list
     */
    public function get_orders($vendor_id, $limit = 50) {
        global $wpdb;

        if (!$vendor_id) return [];

        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT order_id, order_status, item_total, customer_id, created
            FROM {$wpdb->prefix}wcfm_marketplace_orders
            WHERE vendor_id = %d
            ORDER BY created DESC
            LIMIT %d",
            $vendor_id, $limit
        ));

        $orders = [];
        foreach ($rows as $row) {
            $order = wc_get_order($row->order_id);
            if (!$order) continue;

            $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
            $customer_email = $order->get_billing_email();

            $orders[] = [
                'id' => $row->order_id,
                'number' => $order->get_order_number(),
                'date' => $row->created ? date('M j, Y', strtotime($row->created)) : '—',
                'customer' => $customer_name ?: 'Guest',
                'email' => $customer_email ?: '—',
                'item_count' => $order->get_item_count(),
                'total' => wc_price($row->item_total),
                'status' => $row->order_status,
                'status_badge' => $this->status_badge($row->order_status),
                'payment_status' => $order->is_paid() ? '<span class="dso-badge dso-badge-green">Paid</span>' : '<span class="dso-badge dso-badge-orange">Unpaid</span>',
            ];
        }

        return $orders;
    }

    /**
     * Get order stats
     */
    private function get_order_stats($vendor_id) {
        global $wpdb;

        $total = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}wcfm_marketplace_orders WHERE vendor_id = %d",
            $vendor_id
        ));

        $pending = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}wcfm_marketplace_orders WHERE vendor_id = %d AND order_status = 'wc-pending'",
            $vendor_id
        ));

        $processing = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}wcfm_marketplace_orders WHERE vendor_id = %d AND order_status = 'wc-processing'",
            $vendor_id
        ));

        $completed = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}wcfm_marketplace_orders WHERE vendor_id = %d AND order_status = 'wc-completed'",
            $vendor_id
        ));

        $cancelled = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}wcfm_marketplace_orders WHERE vendor_id = %d AND order_status = 'wc-cancelled'",
            $vendor_id
        ));

        return [
            'total' => intval($total),
            'pending' => intval($pending),
            'processing' => intval($processing),
            'completed' => intval($completed),
            'cancelled' => intval($cancelled),
        ];
    }

    private function status_badge($status) {
        $map = [
            'wc-pending' => ['Pending', 'dso-badge-orange'],
            'wc-processing' => ['Processing', 'dso-badge-blue'],
            'wc-on-hold' => ['On Hold', 'dso-badge-yellow'],
            'wc-completed' => ['Completed', 'dso-badge-green'],
            'wc-cancelled' => ['Cancelled', 'dso-badge-red'],
            'wc-refunded' => ['Refunded', 'dso-badge-purple'],
            'wc-failed' => ['Failed', 'dso-badge-red'],
        ];
        $label = $map[$status][0] ?? ucfirst(str_replace('wc-', '', $status));
        $class = $map[$status][1] ?? 'dso-badge-gray';
        return '<span class="dso-badge ' . $class . '">' . $label . '</span>';
    }
}
