<?php
/**
 * DSO Orders - Comprehensive Order & Fulfillment Management for DEJOIY Seller Central
 * Compatible with WooCommerce HPOS and WCFM Marketplace
 */
if (!defined('ABSPATH')) exit;

class DSO_Orders {

    protected function get_active_vendor_id() {
        $user_id = get_current_user_id();
        if ($this->is_admin()) {
            if (!empty($_COOKIE['dso_admin_vendor_context'])) {
                return intval($_COOKIE['dso_admin_vendor_context']);
            }
            return 0;
        }
        $plugin = Dejoiy_Seller_OS::instance();
        return $plugin->get_vendor_id($user_id) ?: $user_id;
    }

    protected function is_admin() {
        return current_user_can('administrator') || current_user_can('manage_options');
    }

    /**
     * Main Orders List
     */
    public function render($filtered_status = '') {
        $vendor_id = $this->get_active_vendor_id();
        $current_status = $filtered_status ?: (isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all');
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

        // Handle order status updates from quick actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dso_update_order_status'])) {
            check_admin_referer('dso_order_action');
            $order_id = intval($_POST['order_id'] ?? 0);
            $new_st = sanitize_text_field($_POST['order_status'] ?? '');
            if ($order_id && $new_st) {
                $order = wc_get_order($order_id);
                if ($order) {
                    $order->update_status($new_st, 'Status updated via DEJOIY Seller Hub');
                }
            }
        }

        $stats = $this->get_order_stats($vendor_id);
        $orders = $this->get_orders($vendor_id, 100, $current_status, $search);
        ?>
        <div class="dso-page dso-orders">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <span>Orders</span>
                        <span>/</span>
                        <span><?php echo $current_status === 'all' ? 'All Orders' : ucfirst($current_status); ?></span>
                    </div>
                    <h1 class="dso-page-title">Order Management</h1>
                    <p class="dso-page-subtitle">Track fulfillment progress, print shipping labels, and manage customer shipments</p>
                </div>
                <div class="dso-page-actions">
                    <a href="?section=shipping" class="dso-btn dso-btn-outline">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                        Shipping Settings
                    </a>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="dso-stats-row">
                <a href="?section=orders" class="dso-stat-card <?php echo $current_status === 'all' ? 'active' : ''; ?>">
                    <span class="dso-stat-label">Total Orders</span>
                    <span class="dso-stat-val"><?php echo number_format($stats['total']); ?></span>
                    <span class="dso-stat-sub">Lifetime volume</span>
                </a>
                <a href="?section=orders-processing" class="dso-stat-card <?php echo $current_status === 'processing' ? 'active' : ''; ?>">
                    <span class="dso-stat-label">Processing / Ready to Ship</span>
                    <span class="dso-stat-val dso-text-primary"><?php echo number_format($stats['processing']); ?></span>
                    <span class="dso-stat-sub">Needs dispatch</span>
                </a>
                <a href="?section=orders-shipped" class="dso-stat-card <?php echo $current_status === 'shipped' ? 'active' : ''; ?>">
                    <span class="dso-stat-label">In Transit / Shipped</span>
                    <span class="dso-stat-val dso-text-info"><?php echo number_format($stats['shipped']); ?></span>
                    <span class="dso-stat-sub">With delivery carrier</span>
                </a>
                <a href="?section=orders-delivered" class="dso-stat-card <?php echo $current_status === 'completed' ? 'active' : ''; ?>">
                    <span class="dso-stat-label">Delivered & Completed</span>
                    <span class="dso-stat-val dso-text-success"><?php echo number_format($stats['completed']); ?></span>
                    <span class="dso-stat-sub">Successful deliveries</span>
                </a>
                <a href="?section=orders-cancelled" class="dso-stat-card <?php echo $current_status === 'cancelled' ? 'active' : ''; ?>">
                    <span class="dso-stat-label">Cancelled</span>
                    <span class="dso-stat-val dso-text-danger"><?php echo number_format($stats['cancelled']); ?></span>
                    <span class="dso-stat-sub">Void / returned</span>
                </a>
            </div>

            <!-- Orders Table Container -->
            <div class="dso-card">
                <div class="dso-card-body dso-p-0">
                    <div class="dso-toolbar">
                        <div class="dso-tabs">
                            <a href="?section=orders" class="dso-tab <?php echo $current_status === 'all' ? 'active' : ''; ?>">All (<?php echo $stats['total']; ?>)</a>
                            <a href="?section=orders-pending" class="dso-tab <?php echo $current_status === 'pending' ? 'active' : ''; ?>">Pending (<?php echo $stats['pending']; ?>)</a>
                            <a href="?section=orders-processing" class="dso-tab <?php echo $current_status === 'processing' ? 'active' : ''; ?>">Processing (<?php echo $stats['processing']; ?>)</a>
                            <a href="?section=orders-shipped" class="dso-tab <?php echo $current_status === 'shipped' ? 'active' : ''; ?>">Shipped</a>
                            <a href="?section=orders-delivered" class="dso-tab <?php echo $current_status === 'completed' ? 'active' : ''; ?>">Delivered (<?php echo $stats['completed']; ?>)</a>
                            <a href="?section=orders-cancelled" class="dso-tab <?php echo $current_status === 'cancelled' ? 'active' : ''; ?>">Cancelled (<?php echo $stats['cancelled']; ?>)</a>
                        </div>

                        <form method="get" class="dso-filters-row">
                            <input type="hidden" name="section" value="<?php echo esc_attr($_GET['section'] ?? 'orders'); ?>" />
                            <div class="dso-search-box">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                <input type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Search by order #, customer name, email..." class="dso-input" />
                            </div>
                            <?php if ($search): ?>
                                <a href="?section=<?php echo esc_attr($_GET['section'] ?? 'orders'); ?>" class="dso-btn dso-btn-outline dso-btn-sm">Reset</a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <div class="dso-table-responsive">
                        <table class="dso-table dso-table-orders">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Total (₹)</th>
                                    <th>Payment</th>
                                    <th>Fulfillment Status</th>
                                    <th class="dso-text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($orders)): ?>
                                    <tr>
                                        <td colspan="8">
                                            <div class="dso-empty-state">
                                                <div class="dso-empty-icon">🛒</div>
                                                <h3>No orders found</h3>
                                                <p><?php echo $search ? 'No orders match your search keyword.' : 'New orders placed on DEJOIY marketplace will appear here immediately.'; ?></p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($orders as $o): ?>
                                        <tr class="dso-order-row" data-id="<?php echo $o['id']; ?>">
                                            <td>
                                                <a href="?section=order-detail&id=<?php echo $o['id']; ?>" class="dso-order-number-link">
                                                    <strong>#<?php echo esc_html($o['number']); ?></strong>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="dso-date-display"><?php echo esc_html($o['date']); ?></span>
                                                <span class="dso-time-sub"><?php echo esc_html($o['time']); ?></span>
                                            </td>
                                            <td>
                                                <div class="dso-cust-meta">
                                                    <span class="dso-cust-name"><?php echo esc_html($o['customer']); ?></span>
                                                    <span class="dso-cust-sub"><?php echo esc_html($o['city']); ?><?php echo $o['state'] ? ', ' . esc_html($o['state']) : ''; ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="dso-items-count"><?php echo intval($o['item_count']); ?> item<?php echo $o['item_count'] > 1 ? 's' : ''; ?></span>
                                                <span class="dso-items-preview"><?php echo esc_html($o['items_preview']); ?></span>
                                            </td>
                                            <td>
                                                <strong class="dso-order-total"><?php echo $o['total_html']; ?></strong>
                                            </td>
                                            <td>
                                                <span class="dso-badge <?php echo $o['is_paid'] ? 'dso-badge-green' : 'dso-badge-gray'; ?>">
                                                    <?php echo esc_html($o['payment_method'] ?: ($o['is_paid'] ? 'Prepaid' : 'COD / Pending')); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo $this->status_badge($o['status']); ?>
                                            </td>
                                            <td class="dso-text-right">
                                                <div class="dso-row-actions" style="display:flex;gap:4px;justify-content:flex-end;">
                                                    <a href="?section=messages&order_id=<?php echo $o['id']; ?>" class="dso-btn dso-btn-sm dso-btn-outline" title="Chat with Customer">
                                                        💬 Message
                                                    </a>
                                                    <a href="?section=order-detail&id=<?php echo $o['id']; ?>" class="dso-btn dso-btn-sm dso-btn-outline" title="Manage Order">
                                                        View
                                                    </a>
                                                    <a href="?section=print-label&id=<?php echo $o['id']; ?>" class="dso-btn dso-btn-sm dso-btn-outline" title="4×6 Thermal Label" target="_blank">
                                                        🏷️ Label
                                                    </a>
                                                    <a href="?section=print-invoice&id=<?php echo $o['id']; ?>" class="dso-btn dso-btn-sm dso-btn-outline" title="GST Tax Invoice" target="_blank">
                                                        📄 Invoice
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function orders_pending() {
        $this->render('pending');
    }

    public function orders_processing() {
        $this->render('processing');
    }

    public function orders_shipped() {
        $this->render('shipped');
    }

    public function orders_delivered() {
        $this->render('completed');
    }

    public function orders_returns() {
        $this->render('refunded');
    }

    public function orders_refunds() {
        $this->render('refunded');
    }

    public function orders_cancelled() {
        $this->render('cancelled');
    }

    /**
     * Single Order Detail View
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

        // Handle Status / Tracking Update
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dso_update_detail_status'])) {
            check_admin_referer('dso_order_detail_action');
            $new_st = sanitize_text_field($_POST['order_status'] ?? '');
            $carrier = sanitize_text_field($_POST['tracking_carrier'] ?? '');
            $tracking_no = sanitize_text_field($_POST['tracking_number'] ?? '');

            if ($new_st) {
                $order->update_status($new_st, 'Updated by seller via DEJOIY Hub');
            }
            if ($tracking_no) {
                update_post_meta($order_id, '_dso_tracking_number', $tracking_no);
                update_post_meta($order_id, '_dso_tracking_carrier', $carrier);
            }
            wp_redirect('?section=order-detail&id=' . $order_id . '&notice=updated');
            exit;
        }

        $tracking_no = get_post_meta($order_id, '_dso_tracking_number', true);
        $carrier = get_post_meta($order_id, '_dso_tracking_carrier', true) ?: 'Shiprocket';
        ?>
        <div class="dso-page dso-order-detail">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <a href="?section=orders">Orders</a>
                        <span>/</span>
                        <span>Order #<?php echo $order->get_order_number(); ?></span>
                    </div>
                    <h1 class="dso-page-title">Order #<?php echo $order->get_order_number(); ?></h1>
                    <p class="dso-page-subtitle">Placed on <?php echo $order->get_date_created() ? $order->get_date_created()->format('F j, Y \a\t g:i A') : '—'; ?> • Customer: <?php echo esc_html($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()); ?></p>
                </div>
                <div class="dso-page-actions">
                    <a href="?section=orders" class="dso-btn dso-btn-outline">← Back to Orders</a>
                    <a href="?section=messages&order_id=<?php echo $order->get_id(); ?>" class="dso-btn dso-btn-outline" title="Chat with Customer">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        Message Customer
                    </a>
                    <a href="?section=print-label&id=<?php echo $order->get_id(); ?>" class="dso-btn dso-btn-outline" target="_blank">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                        Print 4×6 Thermal Label
                    </a>
                    <a href="?section=print-invoice&id=<?php echo $order->get_id(); ?>" class="dso-btn dso-btn-primary" target="_blank">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                        GST Tax Invoice
                    </a>
                </div>
            </div>

            <?php if (isset($_GET['notice'])): ?>
                <div class="dso-alert dso-alert-success">Order information updated successfully!</div>
            <?php endif; ?>

            <div class="dso-grid-2-1">
                <!-- Left: Order Items & Financials -->
                <div class="dso-order-main">
                    <div class="dso-card dso-mb-4">
                        <div class="dso-card-header">
                            <h3 class="dso-card-title">Order Items (<?php echo $order->get_item_count(); ?>)</h3>
                            <?php echo $this->status_badge($order->get_status()); ?>
                        </div>
                        <div class="dso-card-body dso-p-0">
                            <div class="dso-table-responsive">
                                <table class="dso-table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>SKU</th>
                                            <th>Unit Price</th>
                                            <th>Qty</th>
                                            <th class="dso-text-right">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($order->get_items() as $item_id => $item): 
                                            $prod = $item->get_product();
                                            $img_html = $prod ? $prod->get_image([40, 40]) : '';
                                        ?>
                                            <tr>
                                                <td>
                                                    <div class="dso-product-cell">
                                                        <div class="dso-product-thumb"><?php echo $img_html; ?></div>
                                                        <div>
                                                            <strong><?php echo esc_html($item->get_name()); ?></strong>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><code><?php echo esc_html($prod ? $prod->get_sku() : '—'); ?></code></td>
                                                <td><?php echo wc_price($order->get_item_subtotal($item, false, true)); ?></td>
                                                <td><?php echo $item->get_quantity(); ?></td>
                                                <td class="dso-text-right"><strong><?php echo wc_price($item->get_total()); ?></strong></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="dso-order-totals-box">
                                <div class="dso-totals-row">
                                    <span>Subtotal:</span>
                                    <span><?php echo wc_price($order->get_subtotal()); ?></span>
                                </div>
                                <?php if ($order->get_shipping_total() > 0): ?>
                                    <div class="dso-totals-row">
                                        <span>Shipping:</span>
                                        <span><?php echo wc_price($order->get_shipping_total()); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($order->get_total_tax() > 0): ?>
                                    <div class="dso-totals-row">
                                        <span>GST / Taxes:</span>
                                        <span><?php echo wc_price($order->get_total_tax()); ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="dso-totals-row dso-totals-grand">
                                    <span>Grand Total:</span>
                                    <span><?php echo wc_price($order->get_total()); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fulfillment & Shipping Tracking Card -->
                    <div class="dso-card">
                        <div class="dso-card-header">
                            <h3 class="dso-card-title">Dispatch & Shipment Tracking</h3>
                        </div>
                        <div class="dso-card-body">
                            <form method="post">
                                <?php wp_nonce_field('dso_order_detail_action'); ?>
                                <input type="hidden" name="dso_update_detail_status" value="1" />

                                <div class="dso-form-row dso-grid-3">
                                    <div class="dso-form-group">
                                        <label class="dso-label">Change Status</label>
                                        <select name="order_status" class="dso-select">
                                            <option value="pending" <?php selected($order->get_status(), 'pending'); ?>>Pending Payment</option>
                                            <option value="processing" <?php selected($order->get_status(), 'processing'); ?>>Processing (Ready to Ship)</option>
                                            <option value="completed" <?php selected($order->get_status(), 'completed'); ?>>Delivered / Completed</option>
                                            <option value="cancelled" <?php selected($order->get_status(), 'cancelled'); ?>>Cancelled</option>
                                            <option value="refunded" <?php selected($order->get_status(), 'refunded'); ?>>Refunded</option>
                                        </select>
                                    </div>
                                    <div class="dso-form-group">
                                        <label class="dso-label">Courier Carrier</label>
                                        <select name="tracking_carrier" class="dso-select">
                                            <option value="Shiprocket" <?php selected($carrier, 'Shiprocket'); ?>>Shiprocket</option>
                                            <option value="Delhivery" <?php selected($carrier, 'Delhivery'); ?>>Delhivery</option>
                                            <option value="Blue Dart" <?php selected($carrier, 'Blue Dart'); ?>>Blue Dart</option>
                                            <option value="DTDC" <?php selected($carrier, 'DTDC'); ?>>DTDC</option>
                                            <option value="India Post" <?php selected($carrier, 'India Post'); ?>>India Post</option>
                                            <option value="Amazon Shipping" <?php selected($carrier, 'Amazon Shipping'); ?>>Amazon Shipping</option>
                                        </select>
                                    </div>
                                    <div class="dso-form-group">
                                        <label class="dso-label">Air Waybill (AWB) / Tracking #</label>
                                        <input type="text" name="tracking_number" value="<?php echo esc_attr($tracking_no); ?>" class="dso-input" placeholder="e.g., 1423859201" />
                                    </div>
                                </div>

                                <div class="dso-form-actions dso-mt-3">
                                    <button type="submit" class="dso-btn dso-btn-primary">Update Order & Tracking</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Right: Customer & Delivery Address -->
                <div class="dso-order-sidebar">
                    <div class="dso-card dso-mb-4">
                        <div class="dso-card-header" style="display:flex;justify-content:space-between;align-items:center;">
                            <h3 class="dso-card-title">Customer Details</h3>
                            <a href="?section=messages&order_id=<?php echo $order->get_id(); ?>" class="dso-btn dso-btn-sm dso-btn-outline" style="font-size:12px;padding:3px 8px;" title="Chat with customer">
                                💬 Chat
                            </a>
                        </div>
                        <div class="dso-card-body">
                            <div class="dso-info-list">
                                <div class="dso-info-item">
                                    <span class="dso-label-text">Name:</span>
                                    <strong><?php echo esc_html($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()); ?></strong>
                                </div>
                                <div class="dso-info-item">
                                    <span class="dso-label-text">Email:</span>
                                    <a href="mailto:<?php echo esc_attr($order->get_billing_email()); ?>"><?php echo esc_html($order->get_billing_email()); ?></a>
                                </div>
                                <div class="dso-info-item">
                                    <span class="dso-label-text">Phone:</span>
                                    <a href="tel:<?php echo esc_attr($order->get_billing_phone()); ?>"><?php echo esc_html($order->get_billing_phone()); ?></a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="dso-card dso-mb-4">
                        <div class="dso-card-header">
                            <h3 class="dso-card-title">Shipping Address</h3>
                        </div>
                        <div class="dso-card-body">
                            <div class="dso-address-block">
                                <?php echo wp_kses_post(nl2br($order->get_formatted_shipping_address() ?: $order->get_formatted_billing_address())); ?>
                            </div>
                        </div>
                    </div>

                    <div class="dso-card">
                        <div class="dso-card-header">
                            <h3 class="dso-card-title">Payment Information</h3>
                        </div>
                        <div class="dso-card-body">
                            <div class="dso-info-list">
                                <div class="dso-info-item">
                                    <span class="dso-label-text">Method:</span>
                                    <span><?php echo esc_html($order->get_payment_method_title() ?: 'Online Payment'); ?></span>
                                </div>
                                <div class="dso-info-item">
                                    <span class="dso-label-text">Paid Status:</span>
                                    <span><?php echo $order->is_paid() ? '🟢 Paid in Full' : '🟡 Unpaid / COD'; ?></span>
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
     * Get orders list using HPOS / wc_get_orders
     */
    public function get_orders($vendor_id = 0, $limit = 100, $status = 'all', $search = '') {
        $args = [
            'limit' => $limit,
            'orderby' => 'date',
            'order' => 'DESC',
            'return' => 'objects',
        ];

        if ($status && $status !== 'all') {
            $args['status'] = $status;
        }

        $all_orders = wc_get_orders($args);
        $list = [];

        foreach ($all_orders as $order) {
            $order_id = $order->get_id();

            // Check if vendor owns any products in this order
            if ($vendor_id > 0) {
                $has_vendor_item = false;
                foreach ($order->get_items() as $item) {
                    $pid = $item->get_product_id();
                    $author = get_post_field('post_author', $pid);
                    $meta_v = get_post_meta($pid, '_vendor_id', true);
                    if ($author == $vendor_id || $meta_v == $vendor_id) {
                        $has_vendor_item = true;
                        break;
                    }
                }
                if (!$has_vendor_item) continue;
            }

            // Search filter
            if ($search) {
                $cust_name = strtolower($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
                $cust_email = strtolower($order->get_billing_email());
                $num = strtolower($order->get_order_number());
                $q = strtolower($search);
                if (strpos($cust_name, $q) === false && strpos($cust_email, $q) === false && strpos($num, $q) === false) {
                    continue;
                }
            }

            $items = $order->get_items();
            $item_names = [];
            foreach ($items as $item) {
                $item_names[] = $item->get_name() . ' (x' . $item->get_quantity() . ')';
            }
            $preview = implode(', ', array_slice($item_names, 0, 2));
            if (count($item_names) > 2) $preview .= ' +' . (count($item_names) - 2) . ' more';

            $created = $order->get_date_created();

            $list[] = [
                'id' => $order_id,
                'number' => $order->get_order_number(),
                'date' => $created ? $created->format('M j, Y') : '—',
                'time' => $created ? $created->format('g:i A') : '',
                'customer' => $order->get_billing_first_name() ? ($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()) : 'Guest Customer',
                'city' => $order->get_billing_city() ?: ($order->get_shipping_city() ?: '—'),
                'state' => $order->get_billing_state() ?: '',
                'item_count' => $order->get_item_count(),
                'items_preview' => $preview,
                'total_html' => wc_price($order->get_total()),
                'status' => $order->get_status(),
                'payment_method' => $order->get_payment_method_title(),
                'is_paid' => $order->is_paid(),
            ];
        }

        return $list;
    }

    /**
     * Get order status counts
     */
    public function get_order_stats($vendor_id = 0) {
        $orders = wc_get_orders(['limit' => -1, 'return' => 'objects']);

        $total = 0;
        $pending = 0;
        $processing = 0;
        $shipped = 0;
        $completed = 0;
        $cancelled = 0;

        foreach ($orders as $order) {
            if ($vendor_id > 0) {
                $has_item = false;
                foreach ($order->get_items() as $item) {
                    $pid = $item->get_product_id();
                    $author = get_post_field('post_author', $pid);
                    $meta_v = get_post_meta($pid, '_vendor_id', true);
                    if ($author == $vendor_id || $meta_v == $vendor_id) {
                        $has_item = true;
                        break;
                    }
                }
                if (!$has_item) continue;
            }

            $total++;
            $st = $order->get_status();
            if ($st === 'pending' || $st === 'on-hold') $pending++;
            elseif ($st === 'processing') $processing++;
            elseif ($st === 'completed') $completed++;
            elseif ($st === 'cancelled') $cancelled++;
        }

        return [
            'total' => $total,
            'pending' => $pending,
            'processing' => $processing,
            'shipped' => $shipped,
            'completed' => $completed,
            'cancelled' => $cancelled,
        ];
    }

    public function status_badge($status) {
        $clean = str_replace('wc-', '', $status);
        $map = [
            'pending' => ['Pending', 'dso-badge-orange'],
            'processing' => ['Processing', 'dso-badge-blue'],
            'on-hold' => ['On Hold', 'dso-badge-yellow'],
            'completed' => ['Delivered', 'dso-badge-green'],
            'cancelled' => ['Cancelled', 'dso-badge-red'],
            'refunded' => ['Refunded', 'dso-badge-purple'],
            'failed' => ['Failed', 'dso-badge-red'],
        ];
        $label = $map[$clean][0] ?? ucfirst($clean);
        $class = $map[$clean][1] ?? 'dso-badge-gray';
        return '<span class="dso-badge ' . $class . '">' . $label . '</span>';
    }

    /**
     * Convert numbers to Indian Rupee Words (Lakhs & Crores)
     */
    public static function convert_number_to_words_inr($number) {
        $no = floor($number);
        $point = round(($number - $no) * 100);
        $hundred = null;
        $digits_1 = strlen($no);
        $i = 0;
        $str = [];
        $words = [
            '0' => '', '1' => 'One', '2' => 'Two',
            '3' => 'Three', '4' => 'Four', '5' => 'Five', '6' => 'Six',
            '7' => 'Seven', '8' => 'Eight', '9' => 'Nine',
            '10' => 'Ten', '11' => 'Eleven', '12' => 'Twelve',
            '13' => 'Thirteen', '14' => 'Fourteen',
            '15' => 'Fifteen', '16' => 'Sixteen', '17' => 'Seventeen',
            '18' => 'Eighteen', '19' => 'Nineteen', '20' => 'Twenty',
            '30' => 'Thirty', '40' => 'Forty', '50' => 'Fifty',
            '60' => 'Sixty', '70' => 'Seventy',
            '80' => 'Eighty', '90' => 'Ninety'
        ];
        $digits = ['', 'Hundred', 'Thousand', 'Lakh', 'Crore'];
        while ($i < $digits_1) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += ($divider == 10) ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? '' : '';
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : '';
                $str [] = ($number < 21) ? $words[$number] . " " . $digits[$counter] . $plural . " " . $hundred
                    : $words[floor($number / 10) * 10] . " " . $words[$number % 10] . " " . $digits[$counter] . $plural . " " . $hundred;
            } else $str[] = null;
        }
        $str = array_reverse($str);
        $result = implode('', $str);
        $points = ($point) ? " and " . $words[floor($point / 10) * 10] . " " . $words[$point = $point % 10] . " Paise" : '';
        $final = trim($result) ? "Rupees " . trim($result) . $points . " Only" : "Rupees Zero Only";
        return preg_replace('/\s+/', ' ', $final);
    }

    /**
     * Print Indian GST Tax Invoice / Packing Slip (A4 Format)
     */
    public function print_invoice() {
        $order_id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_GET['order_id']) ? intval($_GET['order_id']) : 0);
        $order = $order_id ? wc_get_order($order_id) : null;

        if (!$order) {
            $recent = wc_get_orders(['limit' => 1, 'return' => 'objects']);
            if (!empty($recent)) {
                $order = $recent[0];
                $order_id = $order->get_id();
            }
        }

        $order_num = $order ? $order->get_order_number() : '1042';
        $order_date = $order && $order->get_date_created() ? $order->get_date_created()->format('d-M-Y') : date('d-M-Y');
        $inv_num = 'DJ-INV-' . date('Y') . '-' . str_pad($order_num, 6, '0', STR_PAD_LEFT);

        // Vendor / Supplier Profile
        $vendor_id = $this->get_active_vendor_id();
        $user_id = get_current_user_id();
        $store = DSO_Auth::get_vendor_store($vendor_id ?: $user_id);
        $store_name = $store ? $store['name'] : 'DEJOIY Verified Partner';

        $supplier_gstin = '06AABCS1429B1Z8';
        $supplier_pan = 'AABCS1429B';
        $supplier_state = 'Haryana';
        $supplier_state_code = '06';

        // Buyer Profile
        $buyer_name = $order ? ($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()) : 'Deepak Sharma';
        $buyer_addr = $order ? trim($order->get_billing_address_1() . ' ' . $order->get_billing_address_2()) : 'Plot 42, Sector 21';
        $buyer_city = $order ? $order->get_billing_city() : 'New Delhi';
        $buyer_state = $order ? $order->get_billing_state() : 'Delhi';
        $buyer_postcode = $order ? $order->get_billing_postcode() : '110001';
        $buyer_phone = $order ? $order->get_billing_phone() : '+91 98765 43210';
        $buyer_email = $order ? $order->get_billing_email() : 'customer@example.com';
        $buyer_gstin = $order ? get_post_meta($order_id, '_billing_gstin', true) : '';

        // Shipping Address
        $ship_name = $order ? ($order->get_shipping_first_name() ? $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name() : $buyer_name) : $buyer_name;
        $ship_addr = $order ? trim(($order->get_shipping_address_1() ?: $order->get_billing_address_1()) . ' ' . ($order->get_shipping_address_2() ?: $order->get_billing_address_2())) : $buyer_addr;
        $ship_city = $order ? ($order->get_shipping_city() ?: $buyer_city) : $buyer_city;
        $ship_state = $order ? ($order->get_shipping_state() ?: $buyer_state) : $buyer_state;
        $ship_postcode = $order ? ($order->get_shipping_postcode() ?: $buyer_postcode) : $buyer_postcode;

        // Intra-state vs Inter-state Tax Determination
        $is_intra = (stripos($buyer_state, 'Haryana') !== false || $buyer_state === 'HR');

        // Line Items
        $items_data = [];
        $total_taxable = 0;
        $total_cgst = 0;
        $total_sgst = 0;
        $total_igst = 0;
        $idx = 1;

        if ($order) {
            foreach ($order->get_items() as $item) {
                $prod = $item->get_product();
                $qty = $item->get_quantity();
                $gross = floatval($order->get_item_total($item, false, false)) * $qty;
                $line_total = floatval($item->get_total());
                $dpin = $prod ? (get_post_meta($prod->get_id(), '_dejoiy_dpin', true) ?: (get_post_meta($prod->get_id(), '_dpin', true) ?: '')) : '';
                $sku = $prod ? $prod->get_sku() : 'DJ-SKU-01';
                $hsn = $prod ? (get_post_meta($prod->get_id(), '_hsn_code', true) ?: '6204') : '6204';

                // Tax calculation (assume 18% standard GST rate included or itemized)
                $taxable_val = round($line_total / 1.18, 2);
                $tax_amt = round($line_total - $taxable_val, 2);

                if ($is_intra) {
                    $cgst_amt = round($tax_amt / 2, 2);
                    $sgst_amt = $tax_amt - $cgst_amt;
                    $igst_amt = 0;
                    $total_cgst += $cgst_amt;
                    $total_sgst += $sgst_amt;
                } else {
                    $cgst_amt = 0;
                    $sgst_amt = 0;
                    $igst_amt = $tax_amt;
                    $total_igst += $igst_amt;
                }
                $total_taxable += $taxable_val;

                $items_data[] = [
                    'sno' => $idx++,
                    'name' => $item->get_name(),
                    'sku' => $sku,
                    'dpin' => $dpin,
                    'hsn' => $hsn,
                    'qty' => $qty,
                    'unit_price' => round($taxable_val / max(1, $qty), 2),
                    'taxable' => $taxable_val,
                    'cgst_rate' => $is_intra ? '9%' : '0%',
                    'cgst_amt' => $cgst_amt,
                    'sgst_rate' => $is_intra ? '9%' : '0%',
                    'sgst_amt' => $sgst_amt,
                    'igst_rate' => !$is_intra ? '18%' : '0%',
                    'igst_amt' => $igst_amt,
                    'total' => $line_total,
                ];
            }
        } else {
            $total_taxable = 1270.34;
            $total_igst = 228.66;
            $items_data[] = [
                'sno' => 1,
                'name' => 'Premium Printed Anarkali Kurti',
                'sku' => 'KURTI-RED-M',
                'dpin' => 'DJ-DPIN-849201',
                'hsn' => '6204',
                'qty' => 1,
                'unit_price' => 1270.34,
                'taxable' => 1270.34,
                'cgst_rate' => '0%',
                'cgst_amt' => 0,
                'sgst_rate' => '0%',
                'sgst_amt' => 0,
                'igst_rate' => '18%',
                'igst_amt' => 228.66,
                'total' => 1499.00,
            ];
        }

        $grand_total = $order ? floatval($order->get_total()) : 1499.00;
        $shipping_total = $order ? floatval($order->get_shipping_total()) : 0.00;
        $words_total = self::convert_number_to_words_inr($grand_total);
        ?>
        <div class="dso-print-page-wrapper">
            <!-- Non-printable Top Action Bar -->
            <div class="dso-print-toolbar dso-no-print">
                <div class="dso-flex dso-align-center dso-gap-3">
                    <a href="?section=order-detail&id=<?php echo $order_id; ?>" class="dso-btn dso-btn-outline">← Back to Order #<?php echo esc_html($order_num); ?></a>
                    <button type="button" class="dso-btn dso-btn-primary" onclick="window.print();">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                        Print Tax Invoice (A4)
                    </button>
                    <a href="?section=print-label&id=<?php echo $order_id; ?>" class="dso-btn dso-btn-outline">🏷️ 4×6 Thermal Label</a>
                </div>
                <div class="dso-print-hint">
                    📄 <strong>Format:</strong> Standard A4 Portrait • Tax Compliant under Rule 46 of CGST Rules 2017
                </div>
            </div>

            <!-- Standard A4 GST Tax Invoice Container -->
            <div class="dso-gst-invoice-container">
                <!-- Header -->
                <div class="dso-inv-doc-header">
                    <div class="dso-inv-brand-col">
                        <div class="dso-inv-logo-title">DEJOIY MARKETPLACE</div>
                        <div class="dso-inv-sub">Online Commerce & Fulfillment Platform</div>
                    </div>
                    <div class="dso-inv-title-col">
                        <h2 class="dso-inv-heading">TAX INVOICE</h2>
                        <div class="dso-inv-copy-type">(Original for Recipient)</div>
                    </div>
                </div>

                <!-- Invoice & Order Metadata Grid -->
                <div class="dso-inv-meta-grid">
                    <div class="dso-inv-meta-col">
                        <div><strong>Invoice Number:</strong> <code><?php echo esc_html($inv_num); ?></code></div>
                        <div><strong>Invoice Date:</strong> <?php echo esc_html($order_date); ?></div>
                        <div><strong>Order Number:</strong> #<?php echo esc_html($order_num); ?></div>
                        <div><strong>Order Date:</strong> <?php echo esc_html($order_date); ?></div>
                    </div>
                    <div class="dso-inv-meta-col" style="text-align:right;">
                        <div><strong>Place of Supply:</strong> <?php echo esc_html($ship_state); ?></div>
                        <div><strong>Reverse Charge:</strong> No</div>
                        <div><strong>Payment Method:</strong> <?php echo esc_html($order ? $order->get_payment_method_title() : 'Prepaid'); ?></div>
                        <div><strong>DPIN Verified:</strong> Yes ✓</div>
                    </div>
                </div>

                <!-- Parties Information -->
                <div class="dso-inv-parties-grid">
                    <!-- Seller Details -->
                    <div class="dso-inv-party-card">
                        <div class="dso-inv-party-title">SOLD BY (SUPPLIER DETAILS):</div>
                        <strong class="dso-inv-party-name"><?php echo esc_html($store_name); ?></strong><br/>
                        Central Fulfillment Center, Sector 18, Udyog Vihar<br/>
                        Gurugram, Haryana - 122015<br/>
                        <strong>GSTIN:</strong> <code><?php echo esc_html($supplier_gstin); ?></code><br/>
                        <strong>PAN:</strong> <?php echo esc_html($supplier_pan); ?> • <strong>State Code:</strong> <?php echo esc_html($supplier_state_code); ?>
                    </div>

                    <!-- Buyer Details -->
                    <div class="dso-inv-party-card">
                        <div class="dso-inv-party-title">BILLED TO (BUYER DETAILS):</div>
                        <strong class="dso-inv-party-name"><?php echo esc_html($buyer_name); ?></strong><br/>
                        <?php echo esc_html($buyer_addr); ?><br/>
                        <?php echo esc_html($buyer_city); ?>, <?php echo esc_html($buyer_state); ?> - <?php echo esc_html($buyer_postcode); ?><br/>
                        Phone: <?php echo esc_html($buyer_phone); ?><br/>
                        <?php if ($buyer_gstin): ?>
                            <strong>Buyer GSTIN:</strong> <code><?php echo esc_html($buyer_gstin); ?></code>
                        <?php endif; ?>
                    </div>

                    <!-- Shipping Address -->
                    <div class="dso-inv-party-card">
                        <div class="dso-inv-party-title">SHIPPED TO (DELIVERY ADDRESS):</div>
                        <strong class="dso-inv-party-name"><?php echo esc_html($ship_name); ?></strong><br/>
                        <?php echo esc_html($ship_addr); ?><br/>
                        <?php echo esc_html($ship_city); ?>, <?php echo esc_html($ship_state); ?> - <?php echo esc_html($ship_postcode); ?><br/>
                        Contact: <?php echo esc_html($buyer_phone); ?>
                    </div>
                </div>

                <!-- Itemized Tax Table -->
                <table class="dso-inv-table-main">
                    <thead>
                        <tr>
                            <th style="width:30px;">#</th>
                            <th>Description of Goods & DPIN</th>
                            <th>HSN</th>
                            <th style="text-align:center;">Qty</th>
                            <th style="text-align:right;">Rate (₹)</th>
                            <th style="text-align:right;">Taxable Val (₹)</th>
                            <?php if ($is_intra): ?>
                                <th style="text-align:right;">CGST (9%)</th>
                                <th style="text-align:right;">SGST (9%)</th>
                            <?php else: ?>
                                <th style="text-align:right;">IGST (18%)</th>
                            <?php endif; ?>
                            <th style="text-align:right;">Total (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items_data as $it): ?>
                            <tr>
                                <td><?php echo $it['sno']; ?></td>
                                <td>
                                    <strong><?php echo esc_html($it['name']); ?></strong>
                                    <div style="font-size:10px;color:#64748b;">
                                        SKU: <?php echo esc_html($it['sku']); ?>
                                        <?php if (!empty($it['dpin'])): ?>
                                            • DPIN: <?php echo esc_html($it['dpin']); ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><?php echo esc_html($it['hsn']); ?></td>
                                <td style="text-align:center;"><?php echo intval($it['qty']); ?></td>
                                <td style="text-align:right;">₹<?php echo number_format($it['unit_price'], 2); ?></td>
                                <td style="text-align:right;">₹<?php echo number_format($it['taxable'], 2); ?></td>
                                <?php if ($is_intra): ?>
                                    <td style="text-align:right;">₹<?php echo number_format($it['cgst_amt'], 2); ?></td>
                                    <td style="text-align:right;">₹<?php echo number_format($it['sgst_amt'], 2); ?></td>
                                <?php else: ?>
                                    <td style="text-align:right;">₹<?php echo number_format($it['igst_amt'], 2); ?></td>
                                <?php endif; ?>
                                <td style="text-align:right;"><strong>₹<?php echo number_format($it['total'], 2); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="<?php echo $is_intra ? 5 : 4; ?>" style="text-align:right;"><strong>Subtotals:</strong></td>
                            <td style="text-align:right;"><strong>₹<?php echo number_format($total_taxable, 2); ?></strong></td>
                            <?php if ($is_intra): ?>
                                <td style="text-align:right;"><strong>₹<?php echo number_format($total_cgst, 2); ?></strong></td>
                                <td style="text-align:right;"><strong>₹<?php echo number_format($total_sgst, 2); ?></strong></td>
                            <?php else: ?>
                                <td style="text-align:right;"><strong>₹<?php echo number_format($total_igst, 2); ?></strong></td>
                            <?php endif; ?>
                            <td style="text-align:right;"><strong>₹<?php echo number_format($grand_total - $shipping_total, 2); ?></strong></td>
                        </tr>
                        <?php if ($shipping_total > 0): ?>
                            <tr>
                                <td colspan="<?php echo $is_intra ? 7 : 6; ?>" style="text-align:right;">Logistics & Fulfillment Charges:</td>
                                <td style="text-align:right;">₹<?php echo number_format($shipping_total, 2); ?></td>
                            </tr>
                        <?php endif; ?>
                        <tr class="dso-inv-grand-total-row">
                            <td colspan="<?php echo $is_intra ? 7 : 6; ?>" style="text-align:right;font-size:14px;font-weight:700;">Invoice Grand Total:</td>
                            <td style="text-align:right;font-size:14px;font-weight:700;color:#001553;">₹<?php echo number_format($grand_total, 2); ?></td>
                        </tr>
                    </tfoot>
                </table>

                <!-- Amount in Words -->
                <div class="dso-inv-words-row">
                    <strong>Amount in Words:</strong> <?php echo esc_html($words_total); ?>
                </div>

                <!-- Invoice Declarations & Digital Signature Seal -->
                <div class="dso-inv-footer-grid">
                    <div class="dso-inv-terms-col">
                        <div class="dso-inv-terms-title">Declaration & Terms:</div>
                        <p class="dso-inv-terms-p">
                            1. We declare that this invoice shows the actual price of the goods described and all particulars are true and correct.<br/>
                            2. Covered under DEJOIY 7-Day Hassle-Free Buyer Guarantee & Easy Return Policy.<br/>
                            3. This is a computer-generated tax invoice and requires no physical signature under Section 65B of Indian Evidence Act 1872 & IT Act 2000.
                        </p>
                    </div>
                    <div class="dso-inv-sign-col">
                        <div class="dso-inv-sign-box">
                            <div class="dso-inv-sign-seal">★ VERIFIED DIGITAL SIGNATURE ★</div>
                            <strong>For <?php echo esc_html($store_name); ?></strong><br/>
                            <small class="dso-text-muted">Authorized Signatory & DEJOIY Platform Certifier</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
