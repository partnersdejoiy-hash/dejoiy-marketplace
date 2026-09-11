<?php
/**
 * DSO Notifications
 */
if (!defined('ABSPATH')) exit;

class DSO_Notifications {

    /**
     * Create a notification record for a seller
     *
     * @param int    $vendor_id
     * @param string $type
     * @param string $title
     * @param string $message
     * @param string $action_url
     * @return int|bool
     */
    public function create($vendor_id, $type = 'info', $title = '', $message = '', $action_url = '') {
        global $wpdb;
        $table = $wpdb->prefix . 'dso_notifications';
        return $wpdb->insert(
            $table,
            [
                'vendor_id'  => intval($vendor_id),
                'type'       => sanitize_text_field($type),
                'title'      => sanitize_text_field($title),
                'message'    => sanitize_textarea_field($message),
                'action_url' => esc_url_raw($action_url),
                'is_read'    => 0,
                'created_at' => current_time('mysql'),
            ],
            ['%d', '%s', '%s', '%s', '%s', '%d', '%s']
        );
    }

    /**
     * Static helper to create notification
     */
    public static function create_notification($vendor_id, $type = 'info', $title = '', $message = '', $action_url = '') {
        $instance = new self();
        return $instance->create($vendor_id, $type, $title, $message, $action_url);
    }

    public function render() {
        $user_id = get_current_user_id();
        $plugin = Dejoiy_Seller_OS::instance();
        $vendor_id = $plugin->get_vendor_id($user_id);
        $notifications = $this->get_notifications($vendor_id);
        $unread_count = $this->get_unread_count($vendor_id);

        // Mark as read
        if (isset($_GET['mark_read'])) {
            global $wpdb;
            $wpdb->update(
                $wpdb->prefix . 'dso_notifications',
                ['is_read' => 1],
                ['vendor_id' => $vendor_id],
                ['%d'],
                ['%d']
            );
            wp_redirect('?section=notifications');
            exit;
        }

        ?>
        <div class="dso-page dso-notifications">
            <div class="dso-page-header">
                <div>
                    <h1>Notifications</h1>
                    <p><?php echo $unread_count ?> unread notification(s)</p>
                </div>
                <div class="dso-page-actions">
                    <?php if ($unread_count > 0): ?>
                        <a href="?section=notifications&mark_read=1" class="dso-btn dso-btn-secondary">Mark All as Read</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="dso-card">
                <div class="dso-notifications-list">
                    <?php if (empty($notifications)): ?>
                        <div class="dso-empty-state">
                            <div class="dso-empty-icon">🔔</div>
                            <h3>All caught up!</h3>
                            <p>No notifications at this time.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($notifications as $n): ?>
                            <div class="dso-notification-item <?php echo !$n['is_read'] ? 'dso-unread' : '' ?>">
                                <div class="dso-notif-icon"><?php echo $this->get_type_icon($n['type']) ?></div>
                                <div class="dso-notif-content">
                                    <h4><?php echo esc_html($n['title']) ?></h4>
                                    <p><?php echo esc_html($n['message']) ?></p>
                                    <span class="dso-notif-time"><?php echo esc_html($n['date']) ?></span>
                                </div>
                                <?php if (!empty($n['action_url'])): ?>
                                    <a href="<?php echo esc_url($n['action_url']) ?>" class="dso-btn dso-btn-sm dso-btn-secondary">View</a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    public function get_notifications($vendor_id) {
        global $wpdb;
        if (!$vendor_id) return [];

        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}dso_notifications
            WHERE vendor_id = %d ORDER BY created_at DESC LIMIT 50",
            $vendor_id
        ));

        $notifs = [];
        foreach ($rows as $row) {
            $notifs[] = [
                'id' => $row->id,
                'type' => $row->type,
                'title' => $row->title,
                'message' => $row->message,
                'is_read' => $row->is_read,
                'action_url' => $row->action_url,
                'date' => $row->created_at ? date('M j, Y g:i A', strtotime($row->created_at)) : '—',
            ];
        }
        return $notifs;
    }

    public function get_unread_count($vendor_id) {
        global $wpdb;
        if (!$vendor_id) return 0;
        return intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}dso_notifications WHERE vendor_id = %d AND is_read = 0",
            $vendor_id
        )));
    }

    private function get_type_icon($type) {
        $icons = [
            'order' => '📦',
            'product' => '🛍️',
            'review' => '⭐',
            'payment' => '💰',
            'shipping' => '🚚',
            'alert' => '⚠️',
            'info' => 'ℹ️',
        ];
        return $icons[$type] ?? '🔔';
    }
}
