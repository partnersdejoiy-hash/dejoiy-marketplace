<?php
/**
 * DSO Support
 */
if (!defined('ABSPATH')) exit;

class DSO_Support {

    public function render() {
        $user_id = get_current_user_id();
        $plugin = Dejoiy_Seller_OS::instance();
        $vendor_id = $plugin->get_vendor_id($user_id);

        // Handle ticket submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dso_submit_ticket'])) {
            check_admin_referer('dso_support');
            global $wpdb;

            $wpdb->insert($wpdb->prefix . 'dso_support_tickets', [
                'vendor_id' => $vendor_id,
                'subject' => sanitize_text_field($_POST['ticket_subject']),
                'category' => sanitize_text_field($_POST['ticket_category']),
                'message' => wp_kses_post($_POST['ticket_message']),
            ]);

            wp_redirect('?section=support&submitted=1');
            exit;
        }

        $tickets = $this->get_tickets($vendor_id);

        ?>
        <div class="dso-page dso-support">
            <div class="dso-page-header">
                <div>
                    <h1>Support</h1>
                    <p>Get help with your seller account</p>
                </div>
            </div>

            <div class="dso-grid-2">
                <!-- Submit Ticket -->
                <div class="dso-card">
                    <div class="dso-card-header"><h3>Submit a Ticket</h3></div>
                    <div class="dso-card-body">
                        <form method="post" class="dso-form">
                            <?php wp_nonce_field('dso_support'); ?>
                            <div class="dso-form-group">
                                <label for="ticket_subject">Subject *</label>
                                <input type="text" id="ticket_subject" name="ticket_subject" class="dso-input" required placeholder="Brief description of your issue" />
                            </div>
                            <div class="dso-form-group">
                                <label for="ticket_category">Category</label>
                                <select id="ticket_category" name="ticket_category" class="dso-select">
                                    <option value="general">General</option>
                                    <option value="orders">Order Help</option>
                                    <option value="payments">Payment Help</option>
                                    <option value="products">Product Help</option>
                                    <option value="account">Account Help</option>
                                    <option value="technical">Technical Help</option>
                                </select>
                            </div>
                            <div class="dso-form-group">
                                <label for="ticket_message">Message *</label>
                                <textarea id="ticket_message" name="ticket_message" class="dso-textarea" rows="5" required placeholder="Describe your issue in detail..."></textarea>
                            </div>
                            <button type="submit" name="dso_submit_ticket" value="1" class="dso-btn dso-btn-primary dso-btn-full">
                                Submit Ticket
                            </button>
                        </form>
                    </div>
                </div>

                <!-- FAQ -->
                <div class="dso-card">
                    <div class="dso-card-header"><h3>Frequently Asked Questions</h3></div>
                    <div class="dso-card-body">
                        <div class="dso-faq-list">
                            <div class="dso-faq-item">
                                <h4>How do I add a product?</h4>
                                <p>Go to Products → Add Product. Fill in the details and click "Add Product".</p>
                            </div>
                            <div class="dso-faq-item">
                                <h4>How do I withdraw my earnings?</h4>
                                <p>Go to Finance → Withdrawals and request a withdrawal. Your request will be processed within 3-5 business days.</p>
                            </div>
                            <div class="dso-faq-item">
                                <h4>How do I update my store profile?</h4>
                                <p>Go to Store Settings to update your store name, description, logo, and contact information.</p>
                            </div>
                            <div class="dso-faq-item">
                                <h4>When do I get paid?</h4>
                                <p>Earnings are available for withdrawal after the order is marked as completed and the return window has passed.</p>
                            </div>
                            <div class="dso-faq-item">
                                <h4>How do I handle returns?</h4>
                                <p>Returns are managed through the Orders section. You'll receive a notification when a return is requested.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- My Tickets -->
            <div class="dso-card">
                <div class="dso-card-header"><h3>My Tickets</h3></div>
                <div class="dso-table-responsive">
                    <table class="dso-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Subject</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tickets)): ?>
                                <tr class="dso-empty-row"><td colspan="5"><div class="dso-empty-inline"><p>No tickets yet</p></div></td></tr>
                            <?php else: ?>
                                <?php foreach ($tickets as $t): ?>
                                    <tr>
                                        <td><?php echo $t['id'] ?></td>
                                        <td><?php echo esc_html($t['subject']) ?></td>
                                        <td><?php echo esc_html(ucfirst($t['category'])) ?></td>
                                        <td><?php echo $t['status_badge'] ?></td>
                                        <td><?php echo esc_html($t['date']) ?></td>
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

    public function get_tickets($vendor_id) {
        global $wpdb;
        if (!$vendor_id) return [];

        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dso_support_tickets
            WHERE vendor_id = %d ORDER BY created_at DESC",
            $vendor_id
        ));

        $tickets = [];
        foreach ($rows as $row) {
            $status_map = [
                'open' => ['Open', 'dso-badge-green'],
                'pending' => ['Pending', 'dso-badge-orange'],
                'resolved' => ['Resolved', 'dso-badge-blue'],
                'closed' => ['Closed', 'dso-badge-gray'],
            ];
            $s = $status_map[$row->status] ?? ['Unknown', 'dso-badge-gray'];

            $tickets[] = [
                'id' => $row->id,
                'subject' => $row->subject,
                'category' => $row->category,
                'status' => $row->status,
                'status_badge' => '<span class="dso-badge ' . $s[1] . '">' . $s[0] . '</span>',
                'date' => $row->created_at ? date('M j, Y', strtotime($row->created_at)) : '—',
            ];
        }
        return $tickets;
    }
}
