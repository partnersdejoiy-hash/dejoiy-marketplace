<?php
/**
 * DSO Orders - Comprehensive Order & Fulfillment Management for DEJOIY Seller Central
 * Compatible with WooCommerce HPOS and WCFM Marketplace
 */
if (!defined('ABSPATH')) exit;

class DSO_Orders {

    protected function get_active_vendor_id() {
        $user_id = get_current_user_id();
        $plugin = Dejoiy_Seller_OS::instance();
        return $plugin->get_vendor_id($user_id);
    }

    protected function is_admin() {
        return current_user_can('manage_woocommerce') || current_user_can('administrator');
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
                                                <div class="dso-row-actions">
                                                    <a href="?section=order-detail&id=<?php echo $o['id']; ?>" class="dso-btn dso-btn-sm dso-btn-outline" title="Manage Order">
                                                        View Order
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
                    <button type="button" class="dso-btn dso-btn-outline" onclick="window.print();">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                        Print Packing Slip
                    </button>
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
                        <div class="dso-card-header">
                            <h3 class="dso-card-title">Customer Details</h3>
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
            if (!$this->is_admin() && $vendor_id) {
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
            if (!$this->is_admin() && $vendor_id) {
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
}
