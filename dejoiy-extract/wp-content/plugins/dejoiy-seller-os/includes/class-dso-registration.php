<?php
/**
 * DSO Native Seller Onboarding & Registration Engine
 * Fully replaces [wcfm_vendor_registration] with modern DEJOIY onboarding
 */
if (!defined('ABSPATH')) exit;

class DSO_Registration {

    public static function init() {
        add_shortcode('wcfm_vendor_registration', [__CLASS__, 'render_registration_form']);
        add_shortcode('dejoiy_seller_registration', [__CLASS__, 'render_registration_form']);
        add_action('init', [__CLASS__, 'handle_registration_post']);

        add_filter('the_title', function($title, $id = 0) {
            if (!is_admin() && (is_page('vendor-register') || is_page('become-a-vendor'))) {
                return 'Sell on DEJOIY — Partner Onboarding';
            }
            return $title;
        }, 20, 2);

        add_filter('pre_get_document_title', function($title) {
            if (!is_admin() && (is_page('vendor-register') || is_page('become-a-vendor'))) {
                return 'Sell on DEJOIY — Merchant & Partner Onboarding';
            }
            return $title;
        }, 20);
    }

    public static function handle_registration_post() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['dso_action']) || $_POST['dso_action'] !== 'register_seller') {
            return;
        }

        if (!isset($_POST['dso_reg_nonce']) || !wp_verify_nonce($_POST['dso_reg_nonce'], 'dso_seller_register')) {
            wp_die('Security token expired. Please try again.', 'Registration Error', ['response' => 403]);
        }

        $store_name = sanitize_text_field($_POST['store_name'] ?? '');
        $full_name  = sanitize_text_field($_POST['full_name'] ?? '');
        $email      = sanitize_email($_POST['email'] ?? '');
        $phone      = sanitize_text_field($_POST['phone'] ?? '');
        $password   = $_POST['password'] ?? '';
        $gstin      = strtoupper(sanitize_text_field($_POST['gstin'] ?? ''));
        $city       = sanitize_text_field($_POST['city'] ?? '');
        $state      = sanitize_text_field($_POST['state'] ?? '');

        $errors = [];
        if (empty($store_name)) $errors[] = 'Store Name is required.';
        if (empty($email) || !is_email($email)) $errors[] = 'A valid email address is required.';
        if (empty($phone)) $errors[] = 'Phone number is required.';
        if (empty($password) || strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';

        if (email_exists($email)) {
            $errors[] = 'An account with this email address already exists. Please log in.';
        }

        // Generate username from email or store name
        $base_user = sanitize_user(explode('@', $email)[0]);
        $username = $base_user;
        $i = 1;
        while (username_exists($username)) {
            $username = $base_user . $i;
            $i++;
        }

        if (!empty($errors)) {
            set_transient('dso_reg_errors_' . md5($email), $errors, 60);
            wp_safe_redirect(add_query_arg(['reg_error' => 1], wp_get_referer() ?: home_url('/vendor-register/')));
            exit;
        }

        // Create user
        $user_id = wp_insert_user([
            'user_login'   => $username,
            'user_pass'    => $password,
            'user_email'   => $email,
            'display_name' => $store_name,
            'first_name'   => $full_name,
            'role'         => 'wcfm_vendor',
        ]);

        if (is_wp_error($user_id)) {
            set_transient('dso_reg_errors_' . md5($email), [$user_id->get_error_message()], 60);
            wp_safe_redirect(add_query_arg(['reg_error' => 1], wp_get_referer() ?: home_url('/vendor-register/')));
            exit;
        }

        // Assign secondary role if needed
        $user_obj = get_user_by('id', $user_id);
        if ($user_obj) {
            $user_obj->add_role('seller');
        }

        // Format Merchant Code
        $merchant_code = sprintf('DJY-SLR-%06d', $user_id);

        // Store Usermeta
        update_user_meta($user_id, 'store_name', $store_name);
        update_user_meta($user_id, 'wcfmmp_store_name', $store_name);
        update_user_meta($user_id, '_dejoiy_seller_id', $merchant_code);
        update_user_meta($user_id, 'phone', $phone);
        update_user_meta($user_id, 'dso_store_phone', $phone);
        update_user_meta($user_id, 'billing_phone', $phone);

        if (!empty($gstin)) {
            update_user_meta($user_id, '_dejoiy_seller_gst', ['gstin' => $gstin]);
            update_user_meta($user_id, 'dso_gstin', $gstin);
        }

        $wcfm_profile = [
            'store_name'       => $store_name,
            'phone'            => $phone,
            'address'          => ['city' => $city, 'state' => $state],
            'customer_support' => ['email' => $email, 'phone' => $phone],
        ];
        update_user_meta($user_id, 'wcfmmp_profile_settings', $wcfm_profile);

        // Check platform auto-approval
        $settings = DSO_Marketplace::get_marketplace_settings();
        $auto_approve = ($settings['auto_approve_vendors'] ?? 'yes') === 'yes';
        if ($auto_approve) {
            update_user_meta($user_id, 'dso_verified_seller', 'yes');
            update_user_meta($user_id, '_wcfm_email_verified', 'yes');
            update_user_meta($user_id, 'dso_store_suspended', 'no');
        } else {
            update_user_meta($user_id, 'dso_store_suspended', 'yes');
        }

        // Auto login
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id, true, is_ssl());

        // Redirect directly to seller hub onboarding
        wp_safe_redirect('https://sellerhub.dejoiy.com/?registered=1');
        exit;
    }

    public static function render_registration_form() {
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $plugin = Dejoiy_Seller_OS::instance();
            if ($plugin->is_vendor($user_id)) {
                return '<div style="max-width:600px;margin:40px auto;padding:32px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;text-align:center;">
                    <h3 style="font-size:20px;font-weight:700;color:#0f172a;margin-bottom:12px;">You are already registered as a DEJOIY Merchant!</h3>
                    <p style="color:#64748b;margin-bottom:24px;">Manage your listings, orders, and payouts directly inside Seller Central.</p>
                    <a href="https://sellerhub.dejoiy.com/" style="display:inline-block;background:#001553;color:#ffffff;padding:12px 24px;border-radius:8px;font-weight:700;text-decoration:none;">Open DEJOIY Seller Hub →</a>
                </div>';
            }
        }

        $errors = [];
        if (isset($_GET['reg_error'])) {
            // Find any transient errors
            $errors = ['Please check all fields and ensure your email is valid and unique.'];
        }

        ob_start();
        ?>
        <div class="dso-reg-container" style="max-width:640px;margin:32px auto;padding:36px;background:#ffffff;border:1px solid #e2e8f0;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,0.04);font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
            <div style="text-align:center;margin-bottom:28px;">
                <div style="display:inline-flex;align-items:center;justify-content:center;width:56px;height:56px;background:#eef2ff;color:#001553;border-radius:14px;font-size:26px;margin-bottom:12px;">🏪</div>
                <h2 style="font-size:24px;font-weight:800;color:#0f172a;margin:0 0 6px;">Open Your DEJOIY Store</h2>
                <p style="font-size:14px;color:#64748b;margin:0;">Join hundreds of verified merchants selling to thousands of buyers nationwide.</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:8px;margin-bottom:20px;font-size:14px;">
                    <?php foreach ($errors as $e): echo '<div>• ' . esc_html($e) . '</div>'; endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" style="display:flex;flex-direction:column;gap:18px;">
                <?php wp_nonce_field('dso_seller_register', 'dso_reg_nonce'); ?>
                <input type="hidden" name="dso_action" value="register_seller" />

                <div>
                    <label style="display:block;font-size:13px;font-weight:700;color:#334155;margin-bottom:6px;">Store / Brand Name *</label>
                    <input type="text" name="store_name" required placeholder="e.g. Royal Handcrafts" style="width:100%;box-sizing:border-box;padding:10px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:14px;" />
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div>
                        <label style="display:block;font-size:13px;font-weight:700;color:#334155;margin-bottom:6px;">Owner Full Name *</label>
                        <input type="text" name="full_name" required placeholder="e.g. Rahul Sharma" style="width:100%;box-sizing:border-box;padding:10px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:14px;" />
                    </div>
                    <div>
                        <label style="display:block;font-size:13px;font-weight:700;color:#334155;margin-bottom:6px;">Phone Number *</label>
                        <input type="tel" name="phone" required placeholder="e.g. +91 9876543210" style="width:100%;box-sizing:border-box;padding:10px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:14px;" />
                    </div>
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:700;color:#334155;margin-bottom:6px;">Business Email Address *</label>
                    <input type="email" name="email" required placeholder="rahul@example.com" style="width:100%;box-sizing:border-box;padding:10px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:14px;" />
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:700;color:#334155;margin-bottom:6px;">Account Password *</label>
                    <input type="password" name="password" required minlength="6" placeholder="Create a secure password (min 6 chars)" style="width:100%;box-sizing:border-box;padding:10px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:14px;" />
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div>
                        <label style="display:block;font-size:13px;font-weight:700;color:#334155;margin-bottom:6px;">City / Region</label>
                        <input type="text" name="city" placeholder="e.g. New Delhi" style="width:100%;box-sizing:border-box;padding:10px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:14px;" />
                    </div>
                    <div>
                        <label style="display:block;font-size:13px;font-weight:700;color:#334155;margin-bottom:6px;">GSTIN (Optional)</label>
                        <input type="text" name="gstin" placeholder="15-digit GSTIN (if registered)" maxlength="15" style="width:100%;box-sizing:border-box;padding:10px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:14px;text-transform:uppercase;" />
                    </div>
                </div>

                <div style="margin-top:8px;">
                    <button type="submit" style="width:100%;padding:14px;background:#001553;color:#ffffff;font-size:15px;font-weight:800;border:none;border-radius:10px;cursor:pointer;transition:background 0.2s ease;">
                        Submit & Launch Store Hub →
                    </button>
                </div>

                <div style="text-align:center;font-size:13px;color:#64748b;margin-top:8px;">
                    Already have a merchant account? <a href="https://sellerhub.dejoiy.com/" style="color:#001553;font-weight:700;text-decoration:none;">Log In to Seller Central</a>
                </div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
}
