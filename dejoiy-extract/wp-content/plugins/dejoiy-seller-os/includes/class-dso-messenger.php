<?php
/**
 * DSO Messenger - Real-Time Buyer-Seller Messaging Center
 * Connects buyers on dejoiy.com with sellers on sellerhub.dejoiy.com
 * Supports product inquiries, order tracking queries, canned replies, and real-time polling.
 */
if (!defined('ABSPATH')) exit;

class DSO_Messenger {

    private static $instance = null;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        // Register REST routes
        add_action('rest_api_init', [$this, 'register_rest_routes']);

        // Frontend hooks for dejoiy.com
        add_action('wp_footer', [$this, 'render_buyer_floating_widget'], 99);
        add_action('woocommerce_single_product_summary', [$this, 'render_product_chat_button'], 35);
        add_action('woocommerce_after_add_to_cart_button', [$this, 'render_product_chat_button_cart'], 10);
        add_action('woocommerce_product_meta_end', [$this, 'render_product_chat_button'], 10);
        add_action('woocommerce_share', [$this, 'render_product_chat_button'], 10);
        add_filter('woocommerce_my_account_my_orders_actions', [$this, 'add_order_message_action'], 20, 2);

        // My Account Messages Tab
        add_action('init', [$this, 'register_account_endpoints']);
        add_filter('woocommerce_account_menu_items', [$this, 'add_account_messages_menu_item'], 40);
        add_action('woocommerce_account_messages_endpoint', [$this, 'render_account_messages_endpoint']);
    }

    /**
     * Ensure database tables exist
     */
    public static function check_tables() {
        global $wpdb;
        $table1 = $wpdb->prefix . 'dso_conversations';
        $table2 = $wpdb->prefix . 'dso_messages';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table1'") !== $table1) {
            $c = $wpdb->get_charset_collate();
            $sql1 = "CREATE TABLE IF NOT EXISTS $table1 (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                thread_token VARCHAR(64) NOT NULL UNIQUE,
                buyer_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
                buyer_name VARCHAR(120) NOT NULL DEFAULT 'Customer',
                buyer_email VARCHAR(120) NOT NULL DEFAULT '',
                buyer_phone VARCHAR(30) NOT NULL DEFAULT '',
                vendor_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
                vendor_name VARCHAR(150) NOT NULL DEFAULT 'Seller',
                product_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
                product_name VARCHAR(255) NOT NULL DEFAULT '',
                product_image VARCHAR(500) NOT NULL DEFAULT '',
                product_price VARCHAR(50) NOT NULL DEFAULT '',
                order_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
                order_number VARCHAR(100) NOT NULL DEFAULT '',
                order_total VARCHAR(50) NOT NULL DEFAULT '',
                subject VARCHAR(255) NOT NULL DEFAULT 'General Inquiry',
                status VARCHAR(20) NOT NULL DEFAULT 'open',
                last_message TEXT DEFAULT NULL,
                last_message_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                last_sender VARCHAR(20) NOT NULL DEFAULT 'buyer',
                unread_seller INT UNSIGNED NOT NULL DEFAULT 0,
                unread_buyer INT UNSIGNED NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY buyer_id (buyer_id),
                KEY vendor_id (vendor_id),
                KEY product_id (product_id),
                KEY order_id (order_id),
                KEY status (status),
                KEY last_message_at (last_message_at)
            ) $c;";

            $sql2 = "CREATE TABLE IF NOT EXISTS $table2 (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                conversation_id BIGINT(20) UNSIGNED NOT NULL,
                sender_type VARCHAR(20) NOT NULL DEFAULT 'buyer',
                sender_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
                sender_name VARCHAR(120) NOT NULL DEFAULT '',
                message TEXT NOT NULL,
                attachments TEXT DEFAULT NULL,
                is_read TINYINT(1) NOT NULL DEFAULT 0,
                read_at DATETIME DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY conversation_id (conversation_id),
                KEY is_read (is_read),
                KEY created_at (created_at)
            ) $c;";

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql1);
            dbDelta($sql2);
        }
    }

    /**
     * Register REST API Routes
     */
    public function register_rest_routes() {
        $ns = 'dejoiy/v1/messenger';

        // 1. Get Threads
        register_rest_route($ns, '/threads', [
            'methods' => 'GET',
            'callback' => [$this, 'rest_get_threads'],
            'permission_callback' => '__return_true',
        ]);

        // 2. Get Messages for a Thread
        register_rest_route($ns, '/messages', [
            'methods' => 'GET',
            'callback' => [$this, 'rest_get_messages'],
            'permission_callback' => '__return_true',
        ]);

        // 3. Send Message
        register_rest_route($ns, '/send', [
            'methods' => 'POST',
            'callback' => [$this, 'rest_send_message'],
            'permission_callback' => '__return_true',
        ]);

        // 4. Start Conversation
        register_rest_route($ns, '/start', [
            'methods' => 'POST',
            'callback' => [$this, 'rest_start_conversation'],
            'permission_callback' => '__return_true',
        ]);

        // 5. Mark Read
        register_rest_route($ns, '/mark-read', [
            'methods' => 'POST',
            'callback' => [$this, 'rest_mark_read'],
            'permission_callback' => '__return_true',
        ]);

        // 6. Update Status (open / resolved)
        register_rest_route($ns, '/status', [
            'methods' => 'POST',
            'callback' => [$this, 'rest_update_status'],
            'permission_callback' => '__return_true',
        ]);

        // 7. Unread Count Badge
        register_rest_route($ns, '/unread', [
            'methods' => 'GET',
            'callback' => [$this, 'rest_get_unread_count'],
            'permission_callback' => '__return_true',
        ]);
    }

    /**
     * Helper: Get current vendor ID
     */
    protected function get_vendor_id() {
        $user_id = get_current_user_id();
        if (!$user_id) return 0;
        $plugin = Dejoiy_Seller_OS::instance();
        return $plugin->get_vendor_id($user_id) ?: $user_id;
    }

    /**
     * REST: Get threads list
     */
    public function rest_get_threads($request) {
        global $wpdb;
        $user_id = get_current_user_id();
        $role = sanitize_text_field($request->get_param('role') ?: '');
        $status = sanitize_text_field($request->get_param('status') ?: 'all');
        $search = sanitize_text_field($request->get_param('q') ?: '');
        $token = sanitize_text_field($request->get_param('token') ?: '');

        $where = ["1=1"];
        $params = [];

        // Detect if seller or buyer
        $is_seller = ($role === 'seller') || ($user_id && $this->get_vendor_id() == $user_id && !empty($_COOKIE['dso_vendor_logged']));
        if (isset($_GET['is_seller']) && $_GET['is_seller'] == '1') {
            $is_seller = true;
        }

        if ($is_seller) {
            $vendor_id = $this->get_vendor_id();
            if (!$vendor_id && $request->get_param('vendor_id')) {
                $vendor_id = intval($request->get_param('vendor_id'));
            }
            if ($vendor_id) {
                $where[] = "vendor_id = %d";
                $params[] = $vendor_id;
            }
        } else {
            // Buyer mode
            if ($user_id) {
                $where[] = "(buyer_id = %d OR thread_token = %s)";
                $params[] = $user_id;
                $params[] = $token;
            } elseif ($token) {
                $where[] = "thread_token = %s";
                $params[] = $token;
            } else {
                return rest_ensure_response(['threads' => [], 'unread' => 0]);
            }
        }

        if ($status && $status !== 'all') {
            $where[] = "status = %s";
            $params[] = $status;
        }

        if (!empty($search)) {
            $like = '%' . $wpdb->esc_like($search) . '%';
            $where[] = "(buyer_name LIKE %s OR product_name LIKE %s OR order_number LIKE %s OR subject LIKE %s OR last_message LIKE %s)";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql_where = implode(' AND ', $where);
        $query = "SELECT * FROM {$wpdb->prefix}dso_conversations WHERE $sql_where ORDER BY last_message_at DESC LIMIT 50";
        if (!empty($params)) {
            $query = $wpdb->prepare($query, $params);
        }

        $threads = $wpdb->get_results($query, ARRAY_A) ?: [];

        // Format dates & timeago
        $total_unread = 0;
        foreach ($threads as &$t) {
            $t['last_message_display'] = esc_html($t['last_message'] ?: 'No messages yet');
            $t['time_ago'] = human_time_diff(strtotime($t['last_message_at']), current_time('timestamp')) . ' ago';
            $t['buyer_initials'] = strtoupper(substr($t['buyer_name'] ?: 'C', 0, 2));
            if ($is_seller) {
                $total_unread += intval($t['unread_seller']);
            } else {
                $total_unread += intval($t['unread_buyer']);
            }
        }

        return rest_ensure_response([
            'success' => true,
            'threads' => $threads,
            'total_unread' => $total_unread,
        ]);
    }

    /**
     * REST: Get messages for a thread (supports incremental polling via `since_id`)
     */
    public function rest_get_messages($request) {
        global $wpdb;
        $conv_id = intval($request->get_param('conversation_id'));
        $token = sanitize_text_field($request->get_param('thread_token') ?: '');
        $since_id = intval($request->get_param('since_id') ?: 0);

        if (!$conv_id && !$token) {
            return new WP_Error('missing_params', 'Conversation ID or token required', ['status' => 400]);
        }

        // Fetch thread
        if ($conv_id) {
            $thread = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}dso_conversations WHERE id = %d", $conv_id), ARRAY_A);
        } else {
            $thread = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}dso_conversations WHERE thread_token = %s", $token), ARRAY_A);
            if ($thread) $conv_id = $thread['id'];
        }

        if (!$thread) {
            return new WP_Error('not_found', 'Conversation not found', ['status' => 404]);
        }

        // Fetch messages
        $sql = "SELECT * FROM {$wpdb->prefix}dso_messages WHERE conversation_id = %d";
        $params = [$conv_id];
        if ($since_id > 0) {
            $sql .= " AND id > %d";
            $params[] = $since_id;
        }
        $sql .= " ORDER BY id ASC";

        $messages = $wpdb->get_results($wpdb->prepare($sql, $params), ARRAY_A) ?: [];

        foreach ($messages as &$m) {
            $m['formatted_time'] = date_i18n('g:i A', strtotime($m['created_at']));
            $m['formatted_date'] = date_i18n('M j, Y', strtotime($m['created_at']));
            $m['message_html'] = nl2br(make_clickable(esc_html($m['message'])));
        }

        return rest_ensure_response([
            'success' => true,
            'thread' => $thread,
            'messages' => $messages,
        ]);
    }

    /**
     * REST: Send a message
     */
    public function rest_send_message($request) {
        global $wpdb;
        $conv_id = intval($request->get_param('conversation_id'));
        $token = sanitize_text_field($request->get_param('thread_token') ?: '');
        $message_text = trim(wp_strip_all_tags($request->get_param('message') ?: ''));
        $sender_type = sanitize_text_field($request->get_param('sender_type') ?: '');

        if (empty($message_text)) {
            return new WP_Error('empty_message', 'Message cannot be empty', ['status' => 400]);
        }

        // Find thread
        if ($conv_id) {
            $thread = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}dso_conversations WHERE id = %d", $conv_id), ARRAY_A);
        } else {
            $thread = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}dso_conversations WHERE thread_token = %s", $token), ARRAY_A);
            if ($thread) $conv_id = $thread['id'];
        }

        if (!$thread) {
            return new WP_Error('not_found', 'Conversation not found', ['status' => 404]);
        }

        $user_id = get_current_user_id();

        // Determine sender type if not explicitly supplied
        if (empty($sender_type)) {
            if ($user_id && $user_id == $thread['vendor_id']) {
                $sender_type = 'seller';
            } else {
                $sender_type = 'buyer';
            }
        }

        if ($sender_type === 'seller') {
            $sender_name = $thread['vendor_name'] ?: 'Seller';
            $unread_buyer_inc = 1;
            $unread_seller_inc = 0;
        } else {
            $sender_name = $thread['buyer_name'] ?: ($user_id ? (get_userdata($user_id)->display_name ?: 'Buyer') : 'Customer');
            $unread_buyer_inc = 0;
            $unread_seller_inc = 1;
        }

        $now = current_time('mysql');

        // Insert message
        $wpdb->insert($wpdb->prefix . 'dso_messages', [
            'conversation_id' => $conv_id,
            'sender_type' => $sender_type,
            'sender_id' => $user_id,
            'sender_name' => $sender_name,
            'message' => $message_text,
            'is_read' => 0,
            'created_at' => $now,
        ]);

        $msg_id = $wpdb->insert_id;

        // Update conversation summary
        $update_data = [
            'last_message' => mb_substr($message_text, 0, 150),
            'last_message_at' => $now,
            'last_sender' => $sender_type,
            'status' => 'open',
            'updated_at' => $now,
        ];

        if ($sender_type === 'seller') {
            $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->prefix}dso_conversations 
                 SET last_message = %s, last_message_at = %s, last_sender = 'seller', unread_buyer = unread_buyer + 1, unread_seller = 0, status = 'open', updated_at = %s 
                 WHERE id = %d",
                mb_substr($message_text, 0, 150), $now, $now, $conv_id
            ));
        } else {
            $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->prefix}dso_conversations 
                 SET last_message = %s, last_message_at = %s, last_sender = 'buyer', unread_seller = unread_seller + 1, unread_buyer = 0, status = 'open', updated_at = %s 
                 WHERE id = %d",
                mb_substr($message_text, 0, 150), $now, $now, $conv_id
            ));

            // Create notification for seller
            $wpdb->insert($wpdb->prefix . 'dso_notifications', [
                'vendor_id' => $thread['vendor_id'],
                'type' => 'message',
                'title' => '💬 New message from ' . $sender_name,
                'message' => mb_substr($message_text, 0, 100),
                'is_read' => 0,
                'action_url' => '?section=messages&id=' . $conv_id,
                'created_at' => $now,
            ]);
        }

        $new_msg = [
            'id' => $msg_id,
            'conversation_id' => $conv_id,
            'sender_type' => $sender_type,
            'sender_id' => $user_id,
            'sender_name' => $sender_name,
            'message' => $message_text,
            'message_html' => nl2br(make_clickable(esc_html($message_text))),
            'formatted_time' => date_i18n('g:i A', strtotime($now)),
            'is_read' => 0,
            'created_at' => $now,
        ];

        return rest_ensure_response([
            'success' => true,
            'message' => $new_msg,
        ]);
    }

    /**
     * REST: Start or open conversation
     */
    public function rest_start_conversation($request) {
        global $wpdb;
        $vendor_id = intval($request->get_param('vendor_id') ?: 2);
        $product_id = intval($request->get_param('product_id') ?: 0);
        $order_id = intval($request->get_param('order_id') ?: 0);
        $buyer_name = sanitize_text_field($request->get_param('buyer_name') ?: '');
        $buyer_email = sanitize_email($request->get_param('buyer_email') ?: '');
        $buyer_phone = sanitize_text_field($request->get_param('buyer_phone') ?: '');
        $subject = sanitize_text_field($request->get_param('subject') ?: 'Product Inquiry');
        $initial_msg = trim(wp_strip_all_tags($request->get_param('message') ?: ''));

        $user_id = get_current_user_id();
        if ($user_id) {
            $user = get_userdata($user_id);
            if (empty($buyer_name)) $buyer_name = $user->display_name;
            if (empty($buyer_email)) $buyer_email = $user->user_email;
        }

        if (empty($buyer_name)) $buyer_name = 'Customer';

        // Retrieve vendor name
        $vendor_user = get_userdata($vendor_id);
        $vendor_name = $vendor_user ? $vendor_user->display_name : 'DEJOIY Partner';
        if (class_exists('DSO_Auth')) {
            $store = DSO_Auth::get_vendor_store($vendor_id);
            if ($store && !empty($store['name'])) {
                $vendor_name = $store['name'];
            }
        }

        // Product details if applicable
        $product_name = '';
        $product_image = '';
        $product_price = '';
        if ($product_id) {
            $product = wc_get_product($product_id);
            if ($product) {
                $product_name = $product->get_name();
                $img_id = $product->get_image_id();
                $product_image = $img_id ? wp_get_attachment_image_url($img_id, 'thumbnail') : '';
                $product_price = '₹' . number_format(floatval($product->get_price()), 2);
                if (empty($subject) || $subject === 'General Inquiry') {
                    $subject = 'Inquiry: ' . $product_name;
                }
            }
        }

        // Order details if applicable
        $order_number = '';
        $order_total = '';
        if ($order_id) {
            $order = wc_get_order($order_id);
            if ($order) {
                $order_number = $order->get_order_number();
                $order_total = '₹' . number_format(floatval($order->get_total()), 2);
                $subject = 'Order #' . $order_number . ' Inquiry';
            }
        }

        // Check if an existing open thread exists for same buyer & vendor & (product or order)
        $existing = null;
        if ($user_id) {
            $existing_query = "SELECT * FROM {$wpdb->prefix}dso_conversations WHERE buyer_id = %d AND vendor_id = %d";
            $ex_params = [$user_id, $vendor_id];
            if ($order_id) {
                $existing_query .= " AND order_id = %d";
                $ex_params[] = $order_id;
            } elseif ($product_id) {
                $existing_query .= " AND product_id = %d";
                $ex_params[] = $product_id;
            }
            $existing_query .= " AND status != 'archived' ORDER BY last_message_at DESC LIMIT 1";
            $existing = $wpdb->get_row($wpdb->prepare($existing_query, $ex_params), ARRAY_A);
        }

        $now = current_time('mysql');

        if ($existing) {
            $conv_id = $existing['id'];
            $token = $existing['thread_token'];

            // If initial message provided, append it
            if (!empty($initial_msg)) {
                $wpdb->insert($wpdb->prefix . 'dso_messages', [
                    'conversation_id' => $conv_id,
                    'sender_type' => 'buyer',
                    'sender_id' => $user_id,
                    'sender_name' => $buyer_name,
                    'message' => $initial_msg,
                    'is_read' => 0,
                    'created_at' => $now,
                ]);

                $wpdb->query($wpdb->prepare(
                    "UPDATE {$wpdb->prefix}dso_conversations 
                     SET last_message = %s, last_message_at = %s, last_sender = 'buyer', unread_seller = unread_seller + 1, status = 'open', updated_at = %s 
                     WHERE id = %d",
                    mb_substr($initial_msg, 0, 150), $now, $now, $conv_id
                ));
            }

            return rest_ensure_response([
                'success' => true,
                'conversation_id' => $conv_id,
                'thread_token' => $token,
                'thread' => $existing,
                'is_new' => false,
            ]);
        }

        // Create new thread
        $token = bin2hex(random_bytes(16));

        $wpdb->insert($wpdb->prefix . 'dso_conversations', [
            'thread_token' => $token,
            'buyer_id' => $user_id,
            'buyer_name' => $buyer_name,
            'buyer_email' => $buyer_email,
            'buyer_phone' => $buyer_phone,
            'vendor_id' => $vendor_id,
            'vendor_name' => $vendor_name,
            'product_id' => $product_id,
            'product_name' => $product_name,
            'product_image' => $product_image,
            'product_price' => $product_price,
            'order_id' => $order_id,
            'order_number' => $order_number,
            'order_total' => $order_total,
            'subject' => $subject,
            'status' => 'open',
            'last_message' => $initial_msg ?: 'Conversation started',
            'last_message_at' => $now,
            'last_sender' => 'buyer',
            'unread_seller' => !empty($initial_msg) ? 1 : 0,
            'unread_buyer' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $conv_id = $wpdb->insert_id;

        if (!empty($initial_msg)) {
            $wpdb->insert($wpdb->prefix . 'dso_messages', [
                'conversation_id' => $conv_id,
                'sender_type' => 'buyer',
                'sender_id' => $user_id,
                'sender_name' => $buyer_name,
                'message' => $initial_msg,
                'is_read' => 0,
                'created_at' => $now,
            ]);

            // Notify seller
            $wpdb->insert($wpdb->prefix . 'dso_notifications', [
                'vendor_id' => $vendor_id,
                'type' => 'message',
                'title' => '💬 New inquiry from ' . $buyer_name,
                'message' => mb_substr($initial_msg, 0, 100),
                'is_read' => 0,
                'action_url' => '?section=messages&id=' . $conv_id,
                'created_at' => $now,
            ]);
        }

        $thread = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}dso_conversations WHERE id = %d", $conv_id), ARRAY_A);

        return rest_ensure_response([
            'success' => true,
            'conversation_id' => $conv_id,
            'thread_token' => $token,
            'thread' => $thread,
            'is_new' => true,
        ]);
    }

    /**
     * REST: Mark thread as read
     */
    public function rest_mark_read($request) {
        global $wpdb;
        $conv_id = intval($request->get_param('conversation_id'));
        $role = sanitize_text_field($request->get_param('role') ?: 'seller');

        if (!$conv_id) {
            return new WP_Error('missing_params', 'Conversation ID required', ['status' => 400]);
        }

        $now = current_time('mysql');

        if ($role === 'seller') {
            $wpdb->update($wpdb->prefix . 'dso_conversations', ['unread_seller' => 0, 'updated_at' => $now], ['id' => $conv_id]);
            $wpdb->update($wpdb->prefix . 'dso_messages', ['is_read' => 1, 'read_at' => $now], ['conversation_id' => $conv_id, 'sender_type' => 'buyer']);
        } else {
            $wpdb->update($wpdb->prefix . 'dso_conversations', ['unread_buyer' => 0, 'updated_at' => $now], ['id' => $conv_id]);
            $wpdb->update($wpdb->prefix . 'dso_messages', ['is_read' => 1, 'read_at' => $now], ['conversation_id' => $conv_id, 'sender_type' => 'seller']);
        }

        return rest_ensure_response(['success' => true]);
    }

    /**
     * REST: Update thread status (open / resolved)
     */
    public function rest_update_status($request) {
        global $wpdb;
        $conv_id = intval($request->get_param('conversation_id'));
        $status = sanitize_text_field($request->get_param('status') ?: 'open');

        if (!in_array($status, ['open', 'resolved', 'archived'])) {
            $status = 'open';
        }

        $wpdb->update($wpdb->prefix . 'dso_conversations', ['status' => $status, 'updated_at' => current_time('mysql')], ['id' => $conv_id]);

        return rest_ensure_response(['success' => true, 'status' => $status]);
    }

    /**
     * REST: Get unread count badge
     */
    public function rest_get_unread_count($request) {
        global $wpdb;
        $user_id = get_current_user_id();
        $role = sanitize_text_field($request->get_param('role') ?: 'seller');
        $token = sanitize_text_field($request->get_param('token') ?: '');

        if ($role === 'seller') {
            $vendor_id = $this->get_vendor_id();
            $count = $wpdb->get_var($wpdb->prepare("SELECT SUM(unread_seller) FROM {$wpdb->prefix}dso_conversations WHERE vendor_id = %d", $vendor_id));
        } else {
            if ($user_id) {
                $count = $wpdb->get_var($wpdb->prepare("SELECT SUM(unread_buyer) FROM {$wpdb->prefix}dso_conversations WHERE buyer_id = %d", $user_id));
            } elseif ($token) {
                $count = $wpdb->get_var($wpdb->prepare("SELECT unread_buyer FROM {$wpdb->prefix}dso_conversations WHERE thread_token = %s", $token));
            } else {
                $count = 0;
            }
        }

        return rest_ensure_response(['success' => true, 'unread' => intval($count ?: 0)]);
    }

    /**
     * RENDER: Seller Central Messages Center (https://sellerhub.dejoiy.com/?section=messages)
     */
    public function render_seller_view() {
        self::check_tables();
        $vendor_id = $this->get_vendor_id();
        $selected_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

        if (!$selected_id && $order_id) {
            global $wpdb;
            $table_conv = $wpdb->prefix . 'dso_conversations';
            $found_id = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$table_conv} WHERE order_id = %d ORDER BY id DESC LIMIT 1",
                $order_id
            ));
            if ($found_id) {
                $selected_id = intval($found_id);
            } else {
                $order = wc_get_order($order_id);
                if ($order) {
                    $buyer_id = $order->get_customer_id() ?: 0;
                    $buyer_name = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()) ?: 'Customer';
                    $buyer_email = $order->get_billing_email();
                    $buyer_phone = $order->get_billing_phone();
                    $order_num = $order->get_order_number();
                    $order_tot = '₹' . number_format(floatval($order->get_total()), 2);

                    $vendor_user = get_user_by('id', $vendor_id);
                    $vendor_name = $vendor_user ? $vendor_user->display_name : 'DEJOIY Partner';
                    if (function_exists('wcfm_get_vendor_store_info')) {
                        $store = wcfm_get_vendor_store_info($vendor_id);
                        if (!empty($store['name'])) {
                            $vendor_name = $store['name'];
                        }
                    }

                    $token = wp_generate_password(32, false);
                    $now = current_time('mysql');

                    $first_product_id = 0;
                    $first_product_name = '';
                    $items = $order->get_items();
                    if (!empty($items)) {
                        $first_item = reset($items);
                        $first_product_id = $first_item->get_product_id();
                        $first_product_name = $first_item->get_name();
                    }

                    $wpdb->insert($table_conv, [
                        'thread_token' => $token,
                        'buyer_id' => $buyer_id,
                        'buyer_name' => $buyer_name,
                        'buyer_email' => $buyer_email,
                        'buyer_phone' => $buyer_phone,
                        'vendor_id' => $vendor_id ?: 1,
                        'vendor_name' => $vendor_name,
                        'product_id' => $first_product_id,
                        'product_name' => $first_product_name,
                        'order_id' => $order_id,
                        'order_number' => $order_num,
                        'order_total' => $order_tot,
                        'subject' => 'Order #' . $order_num,
                        'status' => 'open',
                        'last_message' => 'Direct thread started for Order #' . $order_num,
                        'last_message_at' => $now,
                        'last_sender' => 'seller',
                        'unread_seller' => 0,
                        'unread_buyer' => 0,
                        'created_at' => $now,
                        'updated_at' => $now
                    ]);
                    $selected_id = $wpdb->insert_id;

                    $wpdb->insert($wpdb->prefix . 'dso_messages', [
                        'conversation_id' => $selected_id,
                        'sender_type' => 'system',
                        'sender_id' => 0,
                        'sender_name' => 'DEJOIY System',
                        'message' => 'Direct thread opened for Order #' . $order_num . '. You can now communicate directly with ' . esc_html($buyer_name) . '.',
                        'created_at' => $now
                    ]);
                }
            }
        }
        ?>
        <div class="dso-page dso-messenger-page" id="dso-seller-messenger">
            <div class="dso-page-header">
                <div>
                    <div class="dso-breadcrumb">
                        <a href="?section=dashboard">Dashboard</a>
                        <span>/</span>
                        <span>Buyer-Seller Messages</span>
                    </div>
                    <h1 class="dso-page-title">Customer Messaging Center</h1>
                    <p class="dso-page-subtitle">Real-time direct communication with marketplace customers, order queries, and product support.</p>
                </div>
                <div class="dso-page-actions">
                    <button type="button" class="dso-btn dso-btn-outline" id="dso-refresh-threads-btn" title="Refresh messages">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg>
                        Refresh
                    </button>
                </div>
            </div>

            <!-- Main Messenger Layout -->
            <div class="dso-messenger-container">
                <!-- Left Pane: Threads List -->
                <div class="dso-msg-sidebar" id="dso-msg-sidebar">
                    <div class="dso-msg-search-wrap">
                        <div class="dso-msg-search-box">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <input type="text" id="dso-thread-search-input" placeholder="Search customer, order #..." />
                        </div>
                        <div class="dso-msg-filter-pills">
                            <button type="button" class="dso-msg-pill active" data-filter="all">All</button>
                            <button type="button" class="dso-msg-pill" data-filter="unread">Unread <span class="dso-pill-badge" id="dso-unread-filter-badge" style="display:none;">0</span></button>
                            <button type="button" class="dso-msg-pill" data-filter="orders">Orders</button>
                            <button type="button" class="dso-msg-pill" data-filter="resolved">Resolved</button>
                        </div>
                    </div>

                    <div class="dso-msg-threads-list" id="dso-msg-threads-list">
                        <div class="dso-msg-loading">
                            <div class="dso-spinner"></div> Loading customer messages...
                        </div>
                    </div>
                </div>

                <!-- Right Pane: Active Chat -->
                <div class="dso-msg-chat-pane" id="dso-msg-chat-pane">
                    <!-- Blank State when no thread is selected -->
                    <div class="dso-msg-empty-state" id="dso-msg-empty-state">
                        <div class="dso-empty-bubble-icon">💬</div>
                        <h3>Select a conversation</h3>
                        <p>Choose a customer message from the left to view order context and reply in real time.</p>
                    </div>

                    <!-- Active Chat View -->
                    <div class="dso-msg-active-chat" id="dso-msg-active-chat" style="display:none;">
                        <!-- Chat Header -->
                        <div class="dso-chat-header">
                            <div class="dso-chat-header-left">
                                <button type="button" class="dso-chat-back-btn" id="dso-chat-back-btn" aria-label="Back to messages">
                                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                                </button>
                                <div class="dso-chat-avatar" id="dso-chat-avatar">DS</div>
                                <div>
                                    <div class="dso-chat-buyer-title">
                                        <span id="dso-chat-buyer-name">Deepak Sharma</span>
                                        <span class="dso-badge dso-badge-green" id="dso-chat-status-badge">● Open</span>
                                    </div>
                                    <div class="dso-chat-subtext" id="dso-chat-subtext">Verified Buyer • deepak.dejoi@gmail.com</div>
                                </div>
                            </div>
                            <div class="dso-chat-header-actions">
                                <div id="dso-chat-context-pills" style="display:flex;gap:6px;align-items:center;"></div>
                                <button type="button" class="dso-btn dso-btn-sm dso-btn-outline" id="dso-resolve-btn" title="Toggle resolved status">
                                    ✓ Resolve
                                </button>
                            </div>
                        </div>

                        <!-- Context Bar (Order / Product Summary Banner) -->
                        <div class="dso-chat-context-bar" id="dso-chat-context-bar" style="display:none;"></div>

                        <!-- Messages Stream -->
                        <div class="dso-chat-messages" id="dso-chat-messages">
                            <!-- Messages bubbles populated dynamically -->
                        </div>

                        <!-- Canned Quick Replies -->
                        <div class="dso-canned-replies">
                            <span class="dso-canned-title">⚡ Quick Replies:</span>
                            <button type="button" class="dso-canned-chip" data-reply="Hello! Your order is being processed and will be dispatched via express courier shortly.">📦 Order Processing</button>
                            <button type="button" class="dso-canned-chip" data-reply="Hi! Your order has been dispatched. You can track it using the tracking link in your account.">🚚 Shipped & Tracking</button>
                            <button type="button" class="dso-canned-chip" data-reply="Yes, this product is in stock, genuine, and comes with DEJOIY Buyer Protection guarantee.">✅ In Stock & Genuine</button>
                            <button type="button" class="dso-canned-chip" data-reply="We provide a 7-day hassle-free replacement under our standard seller policy.">🔄 Returns & Warranty</button>
                        </div>

                        <!-- Composer -->
                        <div class="dso-chat-composer">
                            <textarea id="dso-msg-input" placeholder="Type your reply to customer... (Press Enter to send)" rows="1"></textarea>
                            <button type="button" id="dso-msg-send-btn" class="dso-btn dso-btn-primary" aria-label="Send Message">
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                                <span>Send</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        (function() {
            function initSellerMessenger() {
            var activeConvId = <?php echo $selected_id ? $selected_id : 0; ?>;
            var activeFilter = 'all';
            var searchQuery = '';
            var lastMessageId = 0;
            var pollInterval = null;
            var restBase = '/wp-json/dejoiy/v1/messenger/';
            var currentThread = null;

            // Audio notification synthesized via Web Audio
            function playChime(isIncoming) {
                try {
                    var ctx = new (window.AudioContext || window.webkitAudioContext)();
                    var osc = ctx.createOscillator();
                    var gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    var now = ctx.currentTime;
                    if (isIncoming) {
                        osc.frequency.setValueAtTime(587.33, now);
                        osc.frequency.exponentialRampToValueAtTime(880, now + 0.12);
                        gain.gain.setValueAtTime(0.08, now);
                        gain.gain.exponentialRampToValueAtTime(0.001, now + 0.25);
                        osc.start(now);
                        osc.stop(now + 0.25);
                    } else {
                        osc.frequency.setValueAtTime(440, now);
                        osc.frequency.exponentialRampToValueAtTime(659.25, now + 0.08);
                        gain.gain.setValueAtTime(0.06, now);
                        gain.gain.exponentialRampToValueAtTime(0.001, now + 0.18);
                        osc.start(now);
                        osc.stop(now + 0.18);
                    }
                } catch(e){}
            }

            var currentVendorId = <?php echo (int) $vendor_id; ?>;
            function loadThreads() {
                var url = restBase + 'threads?role=seller&vendor_id=' + currentVendorId + '&status=' + activeFilter + '&q=' + encodeURIComponent(searchQuery);
                fetch(url, { credentials: 'same-origin' })
                    .then(function(r){ return r.json(); })
                    .then(function(data){
                        if (!data.success) return;
                        renderThreadsList(data.threads);
                        updateUnreadBadges(data.total_unread);
                        if (activeConvId && !currentThread) {
                            selectThread(activeConvId);
                        }
                    })
                    .catch(function(e){ console.error(e); });
            }

            function updateUnreadBadges(count) {
                var badge = document.getElementById('dso-unread-filter-badge');
                if (badge) {
                    if (count > 0) {
                        badge.textContent = count;
                        badge.style.display = 'inline-block';
                    } else {
                        badge.style.display = 'none';
                    }
                }
                var navBadge = document.querySelector('.dso-nav-badge-messages');
                if (navBadge) {
                    navBadge.textContent = count > 0 ? count : '';
                    navBadge.style.display = count > 0 ? 'inline-block' : 'none';
                }
            }

            function renderThreadsList(threads) {
                var container = document.getElementById('dso-msg-threads-list');
                if (!threads || threads.length === 0) {
                    container.innerHTML = '<div class="dso-msg-empty-list"><div style="font-size:32px;margin-bottom:8px;">📬</div><strong>No conversations found</strong><p style="font-size:12px;color:#64748b;margin-top:4px;">Customer messages will appear here.</p></div>';
                    return;
                }

                var html = '';
                threads.forEach(function(t) {
                    var isSelected = (t.id == activeConvId);
                    var unreadCount = parseInt(t.unread_seller) || 0;
                    var statusClass = t.status === 'resolved' ? 'dso-th-resolved' : '';
                    var badgeTag = '';
                    if (t.order_number) {
                        badgeTag = '<span class="dso-th-tag dso-th-tag-order">#' + escapeHtml(t.order_number) + '</span>';
                    } else if (t.product_name) {
                        badgeTag = '<span class="dso-th-tag dso-th-tag-prod">📦 ' + escapeHtml(t.product_name.substring(0, 20)) + '</span>';
                    }

                    html += '<div class="dso-msg-thread-item ' + (isSelected ? 'active ' : '') + statusClass + '" data-id="' + t.id + '">' +
                                '<div class="dso-th-avatar">' + (t.buyer_initials || 'C') + '</div>' +
                                '<div class="dso-th-body">' +
                                    '<div class="dso-th-row">' +
                                        '<strong class="dso-th-name">' + escapeHtml(t.buyer_name) + '</strong>' +
                                        '<span class="dso-th-time">' + escapeHtml(t.time_ago) + '</span>' +
                                    '</div>' +
                                    '<div class="dso-th-row" style="margin-top:2px;">' +
                                        badgeTag +
                                    '</div>' +
                                    '<div class="dso-th-preview">' + escapeHtml(t.last_message_display) + '</div>' +
                                '</div>' +
                                (unreadCount > 0 ? '<div class="dso-th-unread-badge">' + unreadCount + '</div>' : '') +
                            '</div>';
                });

                container.innerHTML = html;

                // Click handler
                container.querySelectorAll('.dso-msg-thread-item').forEach(function(el) {
                    el.addEventListener('click', function() {
                        var id = parseInt(this.getAttribute('data-id'));
                        selectThread(id);
                    });
                });
            }

            function selectThread(convId) {
                activeConvId = convId;
                lastMessageId = 0;

                // Highlight in list
                document.querySelectorAll('.dso-msg-thread-item').forEach(function(el) {
                    el.classList.toggle('active', el.getAttribute('data-id') == convId);
                });

                // Show active chat pane on mobile
                var container = document.querySelector('.dso-messenger-container');
                if (container) container.classList.add('dso-chat-open');

                document.getElementById('dso-msg-empty-state').style.display = 'none';
                document.getElementById('dso-msg-active-chat').style.display = 'flex';

                loadThreadMessages(convId, true);
                markThreadRead(convId);

                // Start real-time poller
                if (pollInterval) clearInterval(pollInterval);
                pollInterval = setInterval(function() {
                    pollNewMessages(activeConvId);
                }, 2500);
            }

            function markThreadRead(convId) {
                fetch(restBase + 'mark-read', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ conversation_id: convId, role: 'seller' }),
                    credentials: 'same-origin'
                }).then(function(){
                    loadThreads();
                });
            }

            function loadThreadMessages(convId, isFullLoad) {
                var url = restBase + 'messages?conversation_id=' + convId + (isFullLoad ? '' : ('&since_id=' + lastMessageId));
                fetch(url, { credentials: 'same-origin' })
                    .then(function(r){ return r.json(); })
                    .then(function(data){
                        if (!data.success) return;
                        currentThread = data.thread;
                        updateChatHeader(data.thread);
                        updateContextBar(data.thread);

                        var chatBox = document.getElementById('dso-chat-messages');
                        if (isFullLoad) {
                            chatBox.innerHTML = '';
                        }

                        if (data.messages && data.messages.length > 0) {
                            data.messages.forEach(function(m) {
                                appendMessageBubble(m);
                                if (m.id > lastMessageId) lastMessageId = m.id;
                            });
                            scrollChatToBottom();
                        }
                    });
            }

            function pollNewMessages(convId) {
                if (!convId || convId !== activeConvId) return;
                var url = restBase + 'messages?conversation_id=' + convId + '&since_id=' + lastMessageId;
                fetch(url, { credentials: 'same-origin' })
                    .then(function(r){ return r.json(); })
                    .then(function(data){
                        if (!data.success || !data.messages || data.messages.length === 0) return;
                        var hasIncoming = false;
                        data.messages.forEach(function(m) {
                            appendMessageBubble(m);
                            if (m.id > lastMessageId) lastMessageId = m.id;
                            if (m.sender_type === 'buyer') hasIncoming = true;
                        });
                        scrollChatToBottom();
                        if (hasIncoming) {
                            playChime(true);
                            markThreadRead(convId);
                        }
                    });
            }

            function updateChatHeader(thread) {
                document.getElementById('dso-chat-avatar').textContent = (thread.buyer_name || 'C').substring(0, 2).toUpperCase();
                document.getElementById('dso-chat-buyer-name').textContent = thread.buyer_name;
                document.getElementById('dso-chat-subtext').textContent = (thread.buyer_email || 'Verified Customer') + (thread.buyer_phone ? ' • ' + thread.buyer_phone : '');

                var statusBadge = document.getElementById('dso-chat-status-badge');
                if (thread.status === 'resolved') {
                    statusBadge.className = 'dso-badge dso-badge-gray';
                    statusBadge.textContent = '✓ Resolved';
                    document.getElementById('dso-resolve-btn').textContent = 'Reopen Case';
                } else {
                    statusBadge.className = 'dso-badge dso-badge-green';
                    statusBadge.textContent = '● Open';
                    document.getElementById('dso-resolve-btn').textContent = '✓ Mark Resolved';
                }

                var pillsBox = document.getElementById('dso-chat-context-pills');
                pillsBox.innerHTML = '';
                if (thread.order_id && thread.order_number) {
                    pillsBox.innerHTML += '<a href="?section=order-detail&id=' + thread.order_id + '" class="dso-btn dso-btn-sm dso-btn-outline" target="_blank">📋 Order #' + escapeHtml(thread.order_number) + ' ↗</a>';
                }
                if (thread.product_id) {
                    pillsBox.innerHTML += '<a href="?section=edit-product&id=' + thread.product_id + '" class="dso-btn dso-btn-sm dso-btn-outline" target="_blank">📦 Product #' + thread.product_id + ' ↗</a>';
                }
            }

            function updateContextBar(thread) {
                var bar = document.getElementById('dso-chat-context-bar');
                if (thread.product_name || thread.order_number) {
                    var html = '<div class="dso-bar-flex">';
                    if (thread.product_image) {
                        html += '<img src="' + escapeHtml(thread.product_image) + '" class="dso-bar-thumb" alt="" />';
                    }
                    html += '<div class="dso-bar-info">';
                    if (thread.product_name) {
                        html += '<strong>' + escapeHtml(thread.product_name) + '</strong>' + (thread.product_price ? ' • <span class="dso-text-primary">' + escapeHtml(thread.product_price) + '</span>' : '');
                    }
                    if (thread.order_number) {
                        html += '<div style="font-size:11.5px;color:#64748b;">Linked Order: <strong>#' + escapeHtml(thread.order_number) + '</strong>' + (thread.order_total ? ' (' + escapeHtml(thread.order_total) + ')' : '') + '</div>';
                    }
                    html += '</div></div>';
                    bar.innerHTML = html;
                    bar.style.display = 'block';
                } else {
                    bar.style.display = 'none';
                }
            }

            function appendMessageBubble(m) {
                var isSeller = (m.sender_type === 'seller');
                var chatBox = document.getElementById('dso-chat-messages');

                var bubble = document.createElement('div');
                bubble.className = 'dso-bubble-row ' + (isSeller ? 'dso-bubble-out' : 'dso-bubble-in');
                bubble.innerHTML = '<div class="dso-bubble">' +
                                       '<div class="dso-bubble-sender">' + (isSeller ? 'You (Seller)' : escapeHtml(m.sender_name)) + '</div>' +
                                       '<div class="dso-bubble-text">' + m.message_html + '</div>' +
                                       '<div class="dso-bubble-meta">' +
                                           '<span>' + m.formatted_time + '</span>' +
                                           (isSeller ? '<span class="dso-bubble-ticks">✓✓</span>' : '') +
                                       '</div>' +
                                   '</div>';
                chatBox.appendChild(bubble);
            }

            function scrollChatToBottom() {
                var chatBox = document.getElementById('dso-chat-messages');
                chatBox.scrollTop = chatBox.scrollHeight;
            }

            function sendMessage() {
                var input = document.getElementById('dso-msg-input');
                var text = input.value.trim();
                if (!text || !activeConvId) return;

                input.value = '';
                input.style.height = 'auto';

                fetch(restBase + 'send', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        conversation_id: activeConvId,
                        message: text,
                        sender_type: 'seller'
                    }),
                    credentials: 'same-origin'
                })
                .then(function(r){ return r.json(); })
                .then(function(data){
                    if (data.success && data.message) {
                        appendMessageBubble(data.message);
                        if (data.message.id > lastMessageId) lastMessageId = data.message.id;
                        scrollChatToBottom();
                        playChime(false);
                        loadThreads();
                    }
                });
            }

            // Composer Events
            var sendBtn = document.getElementById('dso-msg-send-btn');
            var msgInput = document.getElementById('dso-msg-input');
            if (sendBtn) sendBtn.addEventListener('click', sendMessage);
            if (msgInput) {
                msgInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        sendMessage();
                    }
                });
                msgInput.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
                });
            }

            // Canned Replies click
            document.querySelectorAll('.dso-canned-chip').forEach(function(chip) {
                chip.addEventListener('click', function() {
                    var reply = this.getAttribute('data-reply');
                    if (msgInput) {
                        msgInput.value = reply;
                        msgInput.focus();
                    }
                });
            });

            // Status Filter Tabs
            document.querySelectorAll('.dso-msg-pill').forEach(function(pill) {
                pill.addEventListener('click', function() {
                    document.querySelectorAll('.dso-msg-pill').forEach(function(p){ p.classList.remove('active'); });
                    this.classList.add('active');
                    activeFilter = this.getAttribute('data-filter');
                    loadThreads();
                });
            });

            // Live Search
            var searchInp = document.getElementById('dso-thread-search-input');
            var searchDebounce = null;
            if (searchInp) {
                searchInp.addEventListener('input', function() {
                    var v = this.value;
                    clearTimeout(searchDebounce);
                    searchDebounce = setTimeout(function() {
                        searchQuery = v;
                        loadThreads();
                    }, 250);
                });
            }

            // Refresh Button
            var refreshBtn = document.getElementById('dso-refresh-threads-btn');
            if (refreshBtn) {
                refreshBtn.addEventListener('click', function() {
                    loadThreads();
                    if (activeConvId) loadThreadMessages(activeConvId, true);
                });
            }

            // Mobile Back Button
            var backBtn = document.getElementById('dso-chat-back-btn');
            if (backBtn) {
                backBtn.addEventListener('click', function() {
                    var container = document.querySelector('.dso-messenger-container');
                    if (container) container.classList.remove('dso-chat-open');
                });
            }

            // Toggle Resolve
            var resolveBtn = document.getElementById('dso-resolve-btn');
            if (resolveBtn) {
                resolveBtn.addEventListener('click', function() {
                    if (!activeConvId || !currentThread) return;
                    var newSt = (currentThread.status === 'resolved') ? 'open' : 'resolved';
                    fetch(restBase + 'status', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ conversation_id: activeConvId, status: newSt }),
                        credentials: 'same-origin'
                    }).then(function(){
                        loadThreads();
                        if (activeConvId) loadThreadMessages(activeConvId, true);
                    });
                });
            }

            function escapeHtml(str) {
                if (!str) return '';
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            // Initial load
            loadThreads();
        }

        // Run immediately as elements are already in the DOM
        initSellerMessenger();
        })();
        </script>
        <?php
    }

    /**
     * RENDER: Floating Buyer-Seller Widget on dejoiy.com
     */
    public function render_buyer_floating_widget() {
        // Only run on buyer-facing pages (not on seller hub standalone)
        if (strpos($_SERVER['HTTP_HOST'] ?? '', 'sellerhub') !== false) {
            return;
        }

        self::check_tables();

        $user_id = get_current_user_id();
        $user_name = '';
        $user_email = '';
        if ($user_id) {
            $user = get_userdata($user_id);
            $user_name = $user ? $user->display_name : '';
            $user_email = $user ? $user->user_email : '';
        }

        $current_prod_id = 0;
        $current_prod_title = '';
        $current_prod_price = '';
        $current_prod_thumb = '';
        $current_vendor_id = 2; // Default verified merchant deepak ki dukaan

        if (is_product()) {
            global $post;
            if ($post) {
                $current_prod_id = $post->ID;
                $current_prod_title = get_the_title($post->ID);
                $p = wc_get_product($post->ID);
                if ($p) {
                    $current_prod_price = '₹' . number_format(floatval($p->get_price()), 2);
                    $img_id = $p->get_image_id();
                    $current_prod_thumb = $img_id ? wp_get_attachment_image_url($img_id, 'thumbnail') : '';
                }
                $v = get_post_field('post_author', $post->ID);
                if ($v) $current_vendor_id = $v;
            }
        }
        ?>
        <style>
        /* DEJOIY Buyer Messenger Floating Widget */
        #dejoiy-buyer-widget-root {
          position: fixed;
          bottom: 24px;
          right: 24px;
          z-index: 999999;
          font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        #djy-chat-launcher {
          display: flex;
          align-items: center;
          gap: 10px;
          padding: 12px 20px;
          background: linear-gradient(135deg, #001553 0%, #0066ff 100%);
          color: #ffffff;
          border: none;
          border-radius: 30px;
          cursor: pointer;
          font-size: 14px;
          font-weight: 700;
          box-shadow: 0 8px 25px rgba(0, 102, 255, 0.35);
          transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
          position: relative;
        }
        #djy-chat-launcher:hover {
          transform: translateY(-2px);
          box-shadow: 0 12px 30px rgba(0, 102, 255, 0.45);
        }
        .djy-launcher-pulse {
          position: absolute;
          top: 0; left: 0; right: 0; bottom: 0;
          border-radius: 30px;
          box-shadow: 0 0 0 0 rgba(0, 102, 255, 0.6);
          animation: djyPulse 2s infinite;
        }
        @keyframes djyPulse {
          0% { box-shadow: 0 0 0 0 rgba(0, 102, 255, 0.6); }
          70% { box-shadow: 0 0 0 12px rgba(0, 102, 255, 0); }
          100% { box-shadow: 0 0 0 0 rgba(0, 102, 255, 0); }
        }
        .djy-launcher-unread {
          background: #d9006c;
          color: #fff;
          font-size: 11px;
          font-weight: 800;
          padding: 2px 7px;
          border-radius: 10px;
          margin-left: 4px;
        }
        #djy-chat-modal {
          width: 380px;
          height: 540px;
          max-width: calc(100vw - 32px);
          max-height: calc(100vh - 100px);
          background: #ffffff;
          border-radius: 18px;
          border: 1px solid #e2e8f0;
          box-shadow: 0 20px 50px rgba(0, 12, 44, 0.25);
          display: flex;
          flex-direction: column;
          overflow: hidden;
          animation: djySlideUp 0.25s ease;
        }
        @keyframes djySlideUp {
          from { opacity: 0; transform: translateY(16px); }
          to { opacity: 1; transform: translateY(0); }
        }
        .djy-cm-header {
          background: linear-gradient(135deg, #000c2c 0%, #001553 100%);
          color: #ffffff;
          padding: 14px 16px;
          display: flex;
          justify-content: space-between;
          align-items: center;
        }
        .djy-cm-brand {
          display: flex;
          align-items: center;
          gap: 10px;
        }
        .djy-cm-avatar {
          width: 36px;
          height: 36px;
          border-radius: 50%;
          background: rgba(255, 255, 255, 0.12);
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 18px;
        }
        .djy-cm-title {
          font-size: 14px;
          font-weight: 800;
          color: #ffffff;
        }
        .djy-cm-online {
          font-size: 11px;
          color: #94a3b8;
          display: flex;
          align-items: center;
          gap: 5px;
          margin-top: 1px;
        }
        .djy-cm-dot {
          width: 7px;
          height: 7px;
          border-radius: 50%;
          background: #10b981;
        }
        .djy-cm-close-btn {
          background: rgba(255, 255, 255, 0.1);
          border: none;
          color: #ffffff;
          width: 28px;
          height: 28px;
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          cursor: pointer;
          font-size: 14px;
        }
        .djy-cm-prod-banner {
          display: flex;
          align-items: center;
          gap: 10px;
          background: #f8fafc;
          padding: 8px 14px;
          border-bottom: 1px solid #e2e8f0;
        }
        .djy-cm-prod-img {
          width: 36px;
          height: 36px;
          border-radius: 6px;
          object-fit: cover;
          border: 1px solid #e2e8f0;
        }
        .djy-cm-prod-meta {
          flex: 1;
          min-width: 0;
        }
        .djy-cm-prod-name {
          font-size: 12.5px;
          font-weight: 700;
          color: #0f172a;
          white-space: nowrap;
          overflow: hidden;
          text-overflow: ellipsis;
        }
        .djy-cm-prod-price {
          font-size: 11px;
          color: #0066ff;
          font-weight: 600;
        }
        .djy-cm-messages {
          flex: 1;
          overflow-y: auto;
          padding: 14px;
          display: flex;
          flex-direction: column;
          gap: 10px;
          background: #fafafa;
        }
        .djy-cm-welcome {
          margin: auto;
          text-align: center;
          padding: 24px 14px;
        }
        .djy-msg-row {
          display: flex;
          flex-direction: column;
          max-width: 80%;
        }
        .djy-msg-in { align-self: flex-start; }
        .djy-msg-out { align-self: flex-end; }
        .djy-msg-bubble {
          border-radius: 12px;
          padding: 8px 12px;
          font-size: 13px;
          line-height: 1.4;
          word-break: break-word;
        }
        .djy-msg-in .djy-msg-bubble {
          background: #ffffff;
          border: 1px solid #e2e8f0;
          color: #0f172a;
          border-bottom-left-radius: 3px;
        }
        .djy-msg-out .djy-msg-bubble {
          background: #0066ff;
          color: #ffffff;
          border-bottom-right-radius: 3px;
        }
        .djy-msg-sender {
          font-size: 10px;
          font-weight: 700;
          color: #0066ff;
          margin-bottom: 3px;
        }
        .djy-msg-meta {
          font-size: 10px;
          opacity: 0.7;
          text-align: right;
          margin-top: 3px;
        }
        .djy-cm-quick-prompts {
          display: flex;
          gap: 6px;
          overflow-x: auto;
          padding: 6px 12px;
          background: #f8fafc;
          border-top: 1px solid #e2e8f0;
          scrollbar-width: none;
        }
        .djy-cm-quick-prompts::-webkit-scrollbar { display: none; }
        .djy-prompt-chip {
          padding: 4px 10px;
          border-radius: 12px;
          background: #ffffff;
          border: 1px solid #cbd5e1;
          font-size: 11px;
          font-weight: 600;
          color: #334155;
          white-space: nowrap;
          cursor: pointer;
        }
        .djy-prompt-chip:hover {
          background: #eff6ff;
          border-color: #0066ff;
          color: #0066ff;
        }
        .djy-cm-guest-bar {
          display: flex;
          gap: 6px;
          padding: 6px 12px;
          background: #f1f5f9;
          border-top: 1px solid #e2e8f0;
        }
        .djy-cm-guest-bar input {
          flex: 1;
          padding: 6px 10px !important;
          font-size: 11.5px !important;
          border-radius: 6px !important;
          border: 1px solid #cbd5e1 !important;
          background: #ffffff !important;
        }
        .djy-cm-composer {
          padding: 10px 12px;
          background: #ffffff;
          border-top: 1px solid #e2e8f0;
          display: flex;
          align-items: center;
          gap: 8px;
        }
        .djy-cm-composer input {
          flex: 1;
          border: 1.5px solid #cbd5e1 !important;
          border-radius: 20px !important;
          padding: 8px 14px !important;
          font-size: 13.5px !important;
          color: #0f172a !important;
          outline: none !important;
          background: #ffffff !important;
        }
        .djy-cm-composer input:focus { border-color: #0066ff !important; }
        .djy-cm-composer button {
          width: 38px;
          height: 38px;
          border-radius: 50%;
          background: #0066ff;
          color: #ffffff;
          border: none;
          cursor: pointer;
          display: flex;
          align-items: center;
          justify-content: center;
          flex-shrink: 0;
          transition: transform 0.15s ease;
        }
        .djy-cm-composer button:hover { transform: scale(1.05); }
        .djy-product-chat-strip { margin: 14px 0; }
        .djy-btn-chat-seller {
          display: inline-flex;
          align-items: center;
          gap: 8px;
          padding: 10px 18px;
          border-radius: 10px;
          background: rgba(0, 102, 255, 0.08);
          border: 1.5px solid rgba(0, 102, 255, 0.35);
          color: #0066ff;
          font-size: 13.5px;
          font-weight: 700;
          cursor: pointer;
          transition: all 0.2s ease;
        }
        .djy-btn-chat-seller:hover {
          background: #0066ff;
          color: #ffffff;
          border-color: #0066ff;
          box-shadow: 0 4px 12px rgba(0, 102, 255, 0.25);
        }
        @media (max-width: 768px) {
          #djy-chat-modal {
            width: 100vw;
            height: 85vh;
            max-width: 100vw;
            max-height: 85vh;
            position: fixed;
            bottom: 0;
            right: 0;
            left: 0;
            border-radius: 20px 20px 0 0;
          }
        }
        </style>
        <!-- DEJOIY Buyer-Seller Real-Time Messenger Widget -->
        <div id="dejoiy-buyer-widget-root">
            <!-- Floating Launch Button -->
            <button type="button" id="djy-chat-launcher" aria-label="Message Seller">
                <div class="djy-launcher-pulse"></div>
                <div class="djy-launcher-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </div>
                <span class="djy-launcher-label"><?php echo is_product() ? 'Ask Seller' : 'Message Seller'; ?></span>
                <span class="djy-launcher-unread" id="djy-launcher-unread" style="display:none;">0</span>
            </button>

            <!-- Floating Chat Window Card -->
            <div id="djy-chat-modal" style="display:none;">
                <!-- Header -->
                <div class="djy-cm-header">
                    <div class="djy-cm-brand">
                        <div class="djy-cm-avatar">🏪</div>
                        <div>
                            <div class="djy-cm-title">DEJOIY Seller Messenger</div>
                            <div class="djy-cm-online"><span class="djy-cm-dot"></span> Online • Fast seller response</div>
                        </div>
                    </div>
                    <div class="djy-cm-actions">
                        <button type="button" class="djy-cm-close-btn" id="djy-cm-close-btn" title="Close chat">✕</button>
                    </div>
                </div>

                <!-- Product Context Banner (if on product page) -->
                <?php if ($current_prod_id): ?>
                <div class="djy-cm-prod-banner">
                    <?php if ($current_prod_thumb): ?>
                        <img src="<?php echo esc_url($current_prod_thumb); ?>" class="djy-cm-prod-img" alt="" />
                    <?php endif; ?>
                    <div class="djy-cm-prod-meta">
                        <div class="djy-cm-prod-name"><?php echo esc_html($current_prod_title); ?></div>
                        <div class="djy-cm-prod-price"><?php echo esc_html($current_prod_price); ?> • Verified Merchant</div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Messages Stream -->
                <div class="djy-cm-messages" id="djy-cm-messages">
                    <div class="djy-cm-welcome">
                        <div style="font-size:28px;margin-bottom:6px;">👋</div>
                        <strong>Have a question for the seller?</strong>
                        <p style="font-size:12px;color:#64748b;margin:4px 0 0;">Ask about sizing, delivery estimation, custom requests, or stock status.</p>
                    </div>
                </div>

                <!-- Quick Inquiry Prompts -->
                <div class="djy-cm-quick-prompts" id="djy-cm-quick-prompts">
                    <button type="button" class="djy-prompt-chip" data-text="Is this item currently in stock and ready to ship?">⚡ Is this in stock?</button>
                    <button type="button" class="djy-prompt-chip" data-text="What is the expected delivery time to my pincode?">🚚 Delivery time?</button>
                    <button type="button" class="djy-prompt-chip" data-text="Is cash on delivery (COD) available for this product?">💵 COD available?</button>
                </div>

                <!-- Guest identity fields if not logged in -->
                <?php if (!$user_id): ?>
                <div class="djy-cm-guest-bar" id="djy-cm-guest-bar">
                    <input type="text" id="djy-guest-name" placeholder="Your Name" />
                    <input type="email" id="djy-guest-email" placeholder="Your Email (for replies)" />
                </div>
                <?php endif; ?>

                <!-- Composer -->
                <div class="djy-cm-composer">
                    <input type="text" id="djy-cm-input" placeholder="Type message to seller..." autocomplete="off" />
                    <button type="button" id="djy-cm-send-btn" aria-label="Send message">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <script>
        (function() {
            var restBase = '/wp-json/dejoiy/v1/messenger/';
            var currentProdId = <?php echo (int) $current_prod_id; ?>;
            var currentVendorId = <?php echo (int) $current_vendor_id; ?>;
            var currentOrderId = 0;
            var isUserLoggedIn = <?php echo $user_id ? 'true' : 'false'; ?>;
            var userName = '<?php echo esc_js($user_name); ?>';
            var userEmail = '<?php echo esc_js($user_email); ?>';

            var activeToken = localStorage.getItem('dejoiy_buyer_thread_token_' + currentVendorId + '_' + currentProdId) || localStorage.getItem('dejoiy_buyer_active_token') || '';
            var activeConvId = localStorage.getItem('dejoiy_buyer_active_conv_id') || 0;
            var lastMsgId = 0;
            var pollTimer = null;

            var launcherBtn = document.getElementById('djy-chat-launcher');
            var chatModal = document.getElementById('djy-chat-modal');
            var closeBtn = document.getElementById('djy-cm-close-btn');
            var sendBtn = document.getElementById('djy-cm-send-btn');
            var textInput = document.getElementById('djy-cm-input');
            var messagesBox = document.getElementById('djy-cm-messages');

            function toggleModal(open) {
                if (open) {
                    chatModal.style.display = 'flex';
                    launcherBtn.style.display = 'none';
                    if (textInput) textInput.focus();
                    if (activeToken && activeConvId) {
                        loadMessages(true);
                    }
                    if (pollTimer) clearInterval(pollTimer);
                    pollTimer = setInterval(pollMessages, 2500);
                } else {
                    chatModal.style.display = 'none';
                    launcherBtn.style.display = 'flex';
                    if (pollTimer) clearInterval(pollTimer);
                }
            }

            if (launcherBtn) launcherBtn.addEventListener('click', function(){ toggleModal(true); });
            if (closeBtn) closeBtn.addEventListener('click', function(){ toggleModal(false); });

            // Expose globally for "Ask Seller" buttons
            window.dejoiyOpenSellerChat = function(prodId, vendorId, orderId) {
                if (prodId) currentProdId = prodId;
                if (vendorId) currentVendorId = vendorId;
                if (orderId) currentOrderId = orderId;
                toggleModal(true);
            };

            // Order action listener
            document.addEventListener('click', function(e) {
                var btn = e.target.closest('a[href*="#chat-order-"]');
                if (btn) {
                    e.preventDefault();
                    var href = btn.getAttribute('href');
                    var match = href.match(/#chat-order-(\d+)/);
                    if (match && match[1]) {
                        window.dejoiyOpenSellerChat(0, 0, parseInt(match[1]));
                    }
                }
            });

            function loadMessages(isInitial) {
                var url = restBase + 'messages?thread_token=' + encodeURIComponent(activeToken) + '&conversation_id=' + activeConvId;
                fetch(url, { credentials: 'same-origin' })
                    .then(function(r){ return r.json(); })
                    .then(function(data){
                        if (!data.success || !data.messages) return;
                        if (isInitial) messagesBox.innerHTML = '';
                        data.messages.forEach(function(m) {
                            appendBubble(m);
                            if (m.id > lastMsgId) lastMsgId = m.id;
                        });
                        messagesBox.scrollTop = messagesBox.scrollHeight;
                        markRead();
                    });
            }

            function pollMessages() {
                if (!activeToken && !activeConvId) return;
                var url = restBase + 'messages?thread_token=' + encodeURIComponent(activeToken) + '&conversation_id=' + activeConvId + '&since_id=' + lastMsgId;
                fetch(url, { credentials: 'same-origin' })
                    .then(function(r){ return r.json(); })
                    .then(function(data){
                        if (!data.success || !data.messages || data.messages.length === 0) return;
                        data.messages.forEach(function(m) {
                            appendBubble(m);
                            if (m.id > lastMsgId) lastMsgId = m.id;
                        });
                        messagesBox.scrollTop = messagesBox.scrollHeight;
                        markRead();
                    });
            }

            function markRead() {
                if (!activeConvId) return;
                fetch(restBase + 'mark-read', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ conversation_id: activeConvId, role: 'buyer' }),
                    credentials: 'same-origin'
                });
            }

            function appendBubble(m) {
                var isMe = (m.sender_type === 'buyer');
                var div = document.createElement('div');
                div.className = 'djy-msg-row ' + (isMe ? 'djy-msg-out' : 'djy-msg-in');
                div.innerHTML = '<div class="djy-msg-bubble">' +
                                    (isMe ? '' : '<div class="djy-msg-sender">' + escapeHtml(m.sender_name) + ' (Verified Seller)</div>') +
                                    '<div class="djy-msg-text">' + m.message_html + '</div>' +
                                    '<div class="djy-msg-meta">' + m.formatted_time + '</div>' +
                                '</div>';
                messagesBox.appendChild(div);
            }

            function sendBuyerMessage(customText) {
                var text = customText || (textInput ? textInput.value.trim() : '');
                if (!text) return;

                if (textInput) textInput.value = '';

                var gName = userName;
                var gEmail = userEmail;
                if (!isUserLoggedIn) {
                    var nEl = document.getElementById('djy-guest-name');
                    var eEl = document.getElementById('djy-guest-email');
                    if (nEl && nEl.value.trim()) gName = nEl.value.trim();
                    if (eEl && eEl.value.trim()) gEmail = eEl.value.trim();
                }

                // If no active thread yet, start one
                if (!activeConvId && !activeToken) {
                    fetch(restBase + 'start', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            vendor_id: currentVendorId,
                            product_id: currentProdId,
                            order_id: currentOrderId,
                            buyer_name: gName,
                            buyer_email: gEmail,
                            message: text
                        }),
                        credentials: 'same-origin'
                    })
                    .then(function(r){ return r.json(); })
                    .then(function(data){
                        if (data.success && data.conversation_id) {
                            activeConvId = data.conversation_id;
                            activeToken = data.thread_token;
                            localStorage.setItem('dejoiy_buyer_active_conv_id', activeConvId);
                            localStorage.setItem('dejoiy_buyer_active_token', activeToken);
                            localStorage.setItem('dejoiy_buyer_thread_token_' + currentVendorId + '_' + currentProdId, activeToken);

                            // Hide welcome
                            var w = document.querySelector('.djy-cm-welcome');
                            if (w) w.style.display = 'none';

                            loadMessages(true);
                        }
                    });
                } else {
                    // Send message into existing thread
                    fetch(restBase + 'send', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            conversation_id: activeConvId,
                            thread_token: activeToken,
                            message: text,
                            sender_type: 'buyer'
                        }),
                        credentials: 'same-origin'
                    })
                    .then(function(r){ return r.json(); })
                    .then(function(data){
                        if (data.success && data.message) {
                            appendBubble(data.message);
                            if (data.message.id > lastMsgId) lastMsgId = data.message.id;
                            messagesBox.scrollTop = messagesBox.scrollHeight;
                        }
                    });
                }
            }

            if (sendBtn) sendBtn.addEventListener('click', function(){ sendBuyerMessage(); });
            if (textInput) {
                textInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        sendBuyerMessage();
                    }
                });
            }

            // Quick prompts chips
            document.querySelectorAll('.djy-prompt-chip').forEach(function(chip) {
                chip.addEventListener('click', function() {
                    var promptText = this.getAttribute('data-text');
                    sendBuyerMessage(promptText);
                    document.getElementById('djy-cm-quick-prompts').style.display = 'none';
                });
            });

            function escapeHtml(str) {
                if (!str) return '';
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }
        })();
        </script>
        <?php
    }

    /**
     * RENDER: Product Single Page "Chat with Seller" Button
     */
    public function render_product_chat_button() {
        static $rendered = false;
        if ($rendered) return;

        global $product;
        if (!$product) {
            $product = wc_get_product(get_the_ID());
        }
        if (!$product) return;

        $rendered = true;
        $prod_id = $product->get_id();
        $author_id = get_post_field('post_author', $prod_id) ?: 2;
        $store_name = 'Verified Seller';
        if (class_exists('DSO_Auth')) {
            $store = DSO_Auth::get_vendor_store($author_id);
            if ($store && !empty($store['name'])) $store_name = $store['name'];
        }
        ?>
        <div class="djy-product-chat-strip" style="margin:16px 0;">
            <button type="button" class="djy-btn-chat-seller" onclick="if(window.dejoiyOpenSellerChat){ window.dejoiyOpenSellerChat(<?php echo $prod_id; ?>, <?php echo $author_id; ?>); }">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:6px;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <span>Chat with Seller (<?php echo esc_html($store_name); ?>)</span>
            </button>
        </div>
        <?php
    }

    public function render_product_chat_button_cart() {
        $this->render_product_chat_button();
    }

    /**
     * HOOK: Add "Message Seller" action to customer orders list
     */
    public function add_order_message_action($actions, $order) {
        if (!$order) return $actions;
        $order_id = $order->get_id();
        $vendor_id = 2;
        foreach ($order->get_items() as $item) {
            $pid = $item->get_product_id();
            $author = get_post_field('post_author', $pid);
            if ($author) {
                $vendor_id = $author;
                break;
            }
        }
        $actions['message_seller'] = [
            'url' => '#chat-order-' . $order_id,
            'name' => '💬 Message Seller',
            'action' => 'message-seller',
        ];
        return $actions;
    }

    /**
     * WooCommerce My Account Endpoint Registration
     */
    public function register_account_endpoints() {
        add_rewrite_endpoint('messages', EP_ROOT | EP_PAGES);
    }

    public function add_account_messages_menu_item($items) {
        $new_items = [];
        foreach ($items as $key => $val) {
            $new_items[$key] = $val;
            if ($key === 'orders') {
                $new_items['messages'] = '💬 Seller Messages';
            }
        }
        return $new_items;
    }

    public function render_account_messages_endpoint() {
        $user_id = get_current_user_id();
        ?>
        <div class="djy-account-messages-view">
            <h2>💬 Your Messages with DEJOIY Sellers</h2>
            <p>Direct communication with verified marketplace sellers regarding your orders and product inquiries.</p>
            <div id="djy-account-threads-list" style="margin-top:20px;">
                <p>Loading your message history...</p>
            </div>
            <script>
            (function() {
                var restBase = '/wp-json/dejoiy/v1/messenger/';
                fetch(restBase + 'threads?role=buyer', { credentials: 'same-origin' })
                    .then(function(r){ return r.json(); })
                    .then(function(data){
                        var box = document.getElementById('djy-account-threads-list');
                        if (!data.success || !data.threads || data.threads.length === 0) {
                            box.innerHTML = '<div style="padding:30px;text-align:center;background:#f8fafc;border-radius:12px;border:1px solid #e2e8f0;">' +
                                                '<div style="font-size:32px;margin-bottom:8px;">📬</div>' +
                                                '<strong>No messages found</strong>' +
                                                '<p style="font-size:13px;color:#64748b;margin-top:4px;">When you contact a seller on any product page, your conversation will appear here.</p>' +
                                            '</div>';
                            return;
                        }

                        var html = '<div style="display:flex;flex-direction:column;gap:12px;">';
                        data.threads.forEach(function(t) {
                            html += '<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px;display:flex;justify-content:space-between;align-items:center;">' +
                                        '<div>' +
                                            '<div style="font-weight:700;color:#0f172a;font-size:15px;">' + t.vendor_name + '</div>' +
                                            '<div style="font-size:13px;color:#64748b;margin-top:2px;">' + (t.product_name ? '📦 ' + t.product_name : (t.order_number ? '📋 Order #' + t.order_number : t.subject)) + '</div>' +
                                            '<div style="font-size:12px;color:#94a3b8;margin-top:4px;">' + t.last_message_display + ' • ' + t.time_ago + '</div>' +
                                        '</div>' +
                                        '<button type="button" class="button" onclick="if(window.dejoiyOpenSellerChat){ window.dejoiyOpenSellerChat(' + t.product_id + ', ' + t.vendor_id + '); }">Open Chat 💬</button>' +
                                    '</div>';
                        });
                        html += '</div>';
                        box.innerHTML = html;
                    });
            })();
            </script>
        </div>
        <?php
    }
}

// Initialize messenger
DSO_Messenger::instance();
