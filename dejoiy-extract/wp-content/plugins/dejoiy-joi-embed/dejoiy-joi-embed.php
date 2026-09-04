<?php
/**
 * Plugin Name: DEJOIY Joi Embed
 * Description: Left-side Joi chat popup — small round launcher, close button, order lookup.
 * Version: 1.8.2
 * Author: DEJOIY
 */

if (!defined('ABSPATH')) {
    exit;
}

define('DEJOIY_JOI_URL', 'https://joi.dejoiy.tech');

/**
 * Widget availability gate — the script host (joi.dejoiy.tech) is not always
 * up. Keep a single source of truth so a dead host never breaks page loads:
 * enabled only when the option is set to '1' or filtered on. Register REST
 * routes still work regardless (they power the /joi/ page).
 */
function dejoiy_joi_widget_enabled(): bool {
    if (defined('DEJOIY_JOI_EMBED_SECRET') && DEJOIY_JOI_EMBED_SECRET === 'DISABLED') {
        return false;
    }
    return '1' === get_option('dejoiy_joi_widget_enabled', '0') && (bool) apply_filters('dejoiy_joi_widget_enabled', true);
}

/**
 * Shared secret with Joi server (wp-config.php or auto-provisioned wp_option).
 */
function dejoiy_joi_get_embed_secret(): string {
    if (defined('DEJOIY_JOI_EMBED_SECRET') && DEJOIY_JOI_EMBED_SECRET !== '') {
        return (string) DEJOIY_JOI_EMBED_SECRET;
    }
    $opt = get_option('dejoiy_joi_embed_secret', '');
    return is_string($opt) ? $opt : '';
}

/**
 * WooCommerce customer ID for the logged-in user (falls back to WP user ID).
 */
function dejoiy_joi_wc_customer_id(int $user_id): string {
    if ($user_id <= 0) {
        return '';
    }
    if (function_exists('wc_get_customer_id_by_user_id')) {
        $wc_id = wc_get_customer_id_by_user_id($user_id);
        if ($wc_id) {
            return (string) $wc_id;
        }
    }
    return (string) $user_id;
}

/**
 * HMAC embed token — set the same secret as DEJOIY_EMBED_SECRET in /etc/joi.env on Joi server.
 * In wp-config.php: define('DEJOIY_JOI_EMBED_SECRET', 'your-long-random-secret');
 */
function dejoiy_joi_embed_token(string $email, string $customer_id, string $name = ''): string {
    $secret = dejoiy_joi_get_embed_secret();
    if ($secret === '') {
        return '';
    }
    if ($email === '' || $customer_id === '') {
        return '';
    }

    $payload = wp_json_encode([
        'e' => $email,
        'id' => $customer_id,
        'n' => $name,
        'exp' => time() + DAY_IN_SECONDS,
    ]);
    if ($payload === false) {
        return '';
    }

    $payload_b64 = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
    $sig = hash_hmac('sha256', $payload_b64, $secret);

    return $payload_b64 . '.' . $sig;
}

/**
 * Fresh login context (not cacheable) — WordPress page caches often strip data-* on the widget script.
 */
function dejoiy_joi_inline_user_config(): void {
    if (!is_user_logged_in()) {
        return;
    }

    $user = wp_get_current_user();
    $user_name = $user->display_name ?: $user->user_login;
    $user_email = $user->user_email ?: '';
    $customer_id = dejoiy_joi_wc_customer_id((int) $user->ID);
    $token = dejoiy_joi_embed_token($user_email, $customer_id, $user_name);

    $is_operator = current_user_can('manage_options');

    $config = [
        'name' => $user_name,
        'email' => $user_email,
        'wcCustomerId' => $customer_id,
        'siteUrl' => home_url(),
        'embedToken' => $token,
        'showSettings' => $is_operator,
    ];

    echo '<script>window.__DEJOIY_JOI_USER__=' . wp_json_encode($config) . ';</script>' . "\n";
}
if (dejoiy_joi_widget_enabled()) {
    add_action('wp_footer', 'dejoiy_joi_inline_user_config', 98);
}

function dejoiy_joi_embed_script(): void {
    $user_name = '';
    $user_email = '';
    $customer_id = '';
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $user_name = $user->display_name ?: $user->user_login;
        $user_email = $user->user_email ?: '';
        $customer_id = dejoiy_joi_wc_customer_id((int) $user->ID);
    }

    $site_url = home_url();
    ?>
    <script
      src="<?php echo esc_url(DEJOIY_JOI_URL . '/joi-widget.js'); ?>"
      data-joi-url="<?php echo esc_attr(DEJOIY_JOI_URL); ?>"
      data-position="left"
      data-draggable="true"
      data-tagline="Having your joy?"
      data-site-url="<?php echo esc_attr($site_url); ?>"
      data-user-name="<?php echo esc_attr($user_name); ?>"
      data-user-email="<?php echo esc_attr($user_email); ?>"
      data-wc-customer-id="<?php echo esc_attr($customer_id); ?>"
      defer
    ></script>
    <?php
}
if (dejoiy_joi_widget_enabled()) {
    add_action('wp_footer', 'dejoiy_joi_embed_script', 99);
}

/**
 * Create read-only WooCommerce REST API keys for Joi order lookup (once).
 */
function dejoiy_joi_ensure_wc_api_keys(): ?array {
    if (!class_exists('WooCommerce')) {
        return null;
    }

    $saved = get_option('dejoiy_joi_wc_api_keys');
    if (is_array($saved) && !empty($saved['consumer_key']) && !empty($saved['consumer_secret'])) {
        return $saved;
    }

    if (!function_exists('wc_rand_hash')) {
        return null;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'woocommerce_api_keys';
    $description = 'Joi Order Lookup';

    $existing_id = (int) $wpdb->get_var(
        $wpdb->prepare("SELECT key_id FROM {$table} WHERE description = %s LIMIT 1", $description)
    );

    $consumer_key = 'ck_' . wc_rand_hash();
    $consumer_secret = 'cs_' . wc_rand_hash();

    $data = [
        'user_id' => 1,
        'description' => $description,
        'permissions' => 'read',
        'consumer_key' => function_exists('wc_api_hash') ? wc_api_hash($consumer_key) : hash_hmac('sha256', $consumer_key, 'wc'),
        'consumer_secret' => $consumer_secret,
        'truncated_key' => substr($consumer_key, -7),
    ];

    if ($existing_id > 0) {
        $wpdb->update($table, $data, ['key_id' => $existing_id], ['%d', '%s', '%s', '%s', '%s', '%s'], ['%d']);
    } else {
        $wpdb->insert($table, $data, ['%d', '%s', '%s', '%s', '%s', '%s']);
    }

    $keys = [
        'consumer_key' => $consumer_key,
        'consumer_secret' => $consumer_secret,
        'store_url' => home_url(),
    ];
    update_option('dejoiy_joi_wc_api_keys', $keys, false);

    return $keys;
}

/**
 * Push WooCommerce keys to Joi server (signed with DEJOIY_JOI_EMBED_SECRET).
 */
function dejoiy_joi_push_store_config(): void {
    $secret = dejoiy_joi_get_embed_secret();
    if ($secret === '') {
        return;
    }

    $keys = dejoiy_joi_ensure_wc_api_keys();
    if (!$keys) {
        return;
    }

    $body = wp_json_encode([
        'storeUrl' => $keys['store_url'] ?? home_url(),
        'consumerKey' => $keys['consumer_key'],
        'consumerSecret' => $keys['consumer_secret'],
    ]);
    if ($body === false) {
        return;
    }

    $sig = hash_hmac('sha256', $body, $secret);

    wp_remote_post(DEJOIY_JOI_URL . '/api/store/configure', [
        'timeout' => 30,
        'headers' => [
            'Content-Type' => 'application/json',
            'X-Joi-Setup-Sig' => $sig,
        ],
        'body' => $body,
    ]);
}

/**
 * Sync public pages to Joi (call on publish or via WP-Cron).
 * Requires Joi API reachable at DEJOIY_JOI_URL.
 */
function dejoiy_joi_sync_knowledge(): void {
    dejoiy_joi_push_store_config();
    $pages = [];
    $post_types = ['page', 'post'];
    if (post_type_exists('product')) {
        $post_types[] = 'product';
    }
    $posts = get_posts([
        'post_type' => $post_types,
        'post_status' => 'publish',
        'numberposts' => 50,
    ]);

    foreach ($posts as $post) {
        $content = wp_strip_all_tags($post->post_content);
        if (strlen($content) > 2500) {
            $content = substr($content, 0, 2500);
        }
        if (strlen($content) < 40) {
            continue;
        }
        $pages[] = [
            'title' => $post->post_title,
            'url' => get_permalink($post),
            'content' => $content,
        ];
    }

    if (empty($pages)) {
        return;
    }

    wp_remote_post(DEJOIY_JOI_URL . '/api/learn/content', [
        'timeout' => 60,
        'headers' => ['Content-Type' => 'application/json'],
        'body' => wp_json_encode([
            'pages' => $pages,
            'mergeIntoDejoiy' => true,
        ]),
    ]);
    wp_remote_post(DEJOIY_JOI_URL . '/api/learn/sync/dejoiy', [
        'timeout' => 120,
        'headers' => ['Content-Type' => 'application/json'],
        'body' => wp_json_encode(['maxPages' => 48]),
    ]);
    wp_remote_post(DEJOIY_JOI_URL . '/api/learn/sync/marketplace', [
        'timeout' => 120,
        'headers' => ['Content-Type' => 'application/json'],
        'body' => wp_json_encode(['siteUrl' => home_url()]),
    ]);
    wp_remote_post(DEJOIY_JOI_URL . '/api/learn/discover', [
        'timeout' => 180,
        'headers' => ['Content-Type' => 'application/json'],
        'body' => wp_json_encode([
            'siteUrl' => home_url(),
            'maxPages' => 48,
            'buildEmbeddings' => true,
        ]),
    ]);
}
add_action('save_post', function ($post_id) {
    if (wp_is_post_revision($post_id)) {
        return;
    }
    $post = get_post($post_id);
    if (!$post || $post->post_status !== 'publish') {
        return;
    }
    if (!in_array($post->post_type, ['page', 'post'], true)) {
        return;
    }
    dejoiy_joi_sync_knowledge();
}, 20, 1);

register_activation_hook(__FILE__, function () {
    dejoiy_joi_push_store_config();
    dejoiy_joi_sync_knowledge();
});

/**
 * Joi server can trigger WooCommerce key creation + push (signed with DEJOIY_JOI_EMBED_SECRET).
 */
add_action('rest_api_init', function () {
    register_rest_route('dejoiy-joi/v1', '/sync-secret', [
        'methods' => 'POST',
        'permission_callback' => '__return_true',
        'callback' => function ($request) {
            $existing = dejoiy_joi_get_embed_secret();
            if ($existing !== '') {
                return ['ok' => true, 'alreadySet' => true];
            }

            $body = $request->get_body();
            $params = json_decode($body, true);
            if (!is_array($params) || empty($params['secret']) || !is_string($params['secret'])) {
                return new WP_Error('dejoiy_joi_bad_body', 'secret required in JSON body', ['status' => 400]);
            }

            $secret = trim($params['secret']);
            if (strlen($secret) < 32) {
                return new WP_Error('dejoiy_joi_weak_secret', 'secret too short', ['status' => 400]);
            }

            $sig = $request->get_header('x-joi-setup-sig');
            $expected = hash_hmac('sha256', $body, $secret);
            if (!$sig || !hash_equals($expected, $sig)) {
                return new WP_Error('dejoiy_joi_bad_sig', 'Invalid setup signature', ['status' => 401]);
            }

            update_option('dejoiy_joi_embed_secret', $secret, false);
            dejoiy_joi_push_store_config();

            return ['ok' => true, 'provisioned' => true];
        },
    ]);

    register_rest_route('dejoiy-joi/v1', '/session', [
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function () {
            if (!is_user_logged_in()) {
                return ['loggedIn' => false];
            }
            $user = wp_get_current_user();
            $customer_id = dejoiy_joi_wc_customer_id((int) $user->ID);
            $token = dejoiy_joi_embed_token($user->user_email, $customer_id, $user->display_name);
            return [
                'loggedIn' => true,
                'name' => $user->display_name ?: $user->user_login,
                'email' => $user->user_email,
                'wcCustomerId' => $customer_id,
                'embedToken' => $token,
                'siteUrl' => home_url(),
            ];
        },
    ]);

    register_rest_route('dejoiy-joi/v1', '/orders/lookup', [
        'methods' => 'POST',
        'permission_callback' => '__return_true',
        'callback' => function ($request) {
            $embed_secret = dejoiy_joi_get_embed_secret();
            if ($embed_secret === '') {
                return new WP_Error('dejoiy_joi_no_secret', 'Embed secret not configured on WordPress', ['status' => 503]);
            }

            $body = $request->get_body();
            $sig = $request->get_header('x-joi-setup-sig');
            $expected = hash_hmac('sha256', $body, $embed_secret);
            if (!$sig || !hash_equals($expected, $sig)) {
                return new WP_Error('dejoiy_joi_bad_sig', 'Invalid setup signature', ['status' => 401]);
            }

            $params = json_decode($body, true);
            if (!is_array($params)) {
                return new WP_Error('dejoiy_joi_bad_body', 'Invalid JSON body', ['status' => 400]);
            }

            $email = isset($params['email']) ? sanitize_email((string) $params['email']) : '';
            $customer_id = isset($params['customerId']) ? absint($params['customerId']) : 0;
            $order_number = isset($params['orderNumber']) ? sanitize_text_field((string) $params['orderNumber']) : '';
            $limit = isset($params['limit']) ? max(1, min(20, absint($params['limit']))) : 8;

            if ($email === '' && $customer_id <= 0) {
                return new WP_Error('dejoiy_joi_no_customer', 'email or customerId required', ['status' => 400]);
            }

            if (!function_exists('wc_get_orders')) {
                return new WP_Error('dejoiy_joi_no_wc', 'WooCommerce is not active', ['status' => 503]);
            }

            $query = [
                'limit' => $limit,
                'orderby' => 'date',
                'order' => 'DESC',
                'return' => 'objects',
            ];

            if ($customer_id > 0) {
                $query['customer_id'] = $customer_id;
            } elseif ($email !== '') {
                $query['billing_email'] = $email;
            }

            $orders = wc_get_orders($query);
            $status_labels = [
                'pending' => 'Pending payment',
                'processing' => 'Processing',
                'on-hold' => 'On hold',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
                'refunded' => 'Refunded',
                'failed' => 'Failed',
            ];

            $out = [];
            foreach ($orders as $order) {
                if (!$order instanceof WC_Order) {
                    continue;
                }
                if ($email !== '' && strtolower($order->get_billing_email()) !== strtolower($email)) {
                    continue;
                }
                if ($order_number !== '' && $order->get_order_number() !== $order_number && (string) $order->get_id() !== $order_number) {
                    continue;
                }

                $items = [];
                foreach ($order->get_items() as $item) {
                    $items[] = [
                        'name' => $item->get_name(),
                        'quantity' => $item->get_quantity(),
                        'total' => (string) $item->get_total(),
                    ];
                }

                $tracking = '';
                foreach ($order->get_meta_data() as $meta) {
                    if (stripos($meta->key, 'tracking') !== false) {
                        $tracking = (string) $meta->value;
                        break;
                    }
                }

                $status = $order->get_status();
                $eta = '';
                if ($status === 'processing') {
                    $eta = 'Usually ships within 1–3 business days';
                } elseif ($status === 'completed') {
                    $eta = 'Delivered or ready for pickup';
                }
                if ($tracking !== '') {
                    $eta = 'Tracking: ' . $tracking;
                }

                $out[] = [
                    'id' => $order->get_id(),
                    'number' => $order->get_order_number(),
                    'status' => $status,
                    'statusLabel' => $status_labels[$status] ?? $status,
                    'date' => $order->get_date_created() ? $order->get_date_created()->date('Y-m-d') : '',
                    'total' => $order->get_total(),
                    'currency' => $order->get_currency(),
                    'itemCount' => $order->get_item_count(),
                    'items' => $items,
                    'tracking' => $tracking ?: null,
                    'etaHint' => $eta ?: null,
                ];
            }

            return ['orders' => $out];
        },
    ]);

    register_rest_route('dejoiy-joi/v1', '/bootstrap-store', [
        'methods' => 'POST',
        'permission_callback' => '__return_true',
        'callback' => function ($request) {
            $embed_secret = dejoiy_joi_get_embed_secret();
            if ($embed_secret === '') {
                return new WP_Error('dejoiy_joi_no_secret', 'Embed secret not configured on WordPress', ['status' => 503]);
            }

            $body = $request->get_body();
            $sig = $request->get_header('x-joi-setup-sig');
            $expected = hash_hmac('sha256', $body, $embed_secret);
            if (!$sig || !hash_equals($expected, $sig)) {
                return new WP_Error('dejoiy_joi_bad_sig', 'Invalid setup signature', ['status' => 401]);
            }

            dejoiy_joi_push_store_config();
            $keys = get_option('dejoiy_joi_wc_api_keys');

            return [
                'ok' => true,
                'storeUrl' => home_url(),
                'hasKeys' => is_array($keys) && !empty($keys['consumer_key']),
            ];
        },
    ]);
});
