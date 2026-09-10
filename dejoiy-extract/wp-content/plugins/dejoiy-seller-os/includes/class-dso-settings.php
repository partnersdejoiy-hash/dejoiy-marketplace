<?php
/**
 * DSO Settings
 */
if (!defined('ABSPATH')) exit;

class DSO_Settings {

    public function render() {
        $user_id = get_current_user_id();
        $plugin = Dejoiy_Seller_OS::instance();
        $vendor_id = $plugin->get_vendor_id($user_id);
        $store = DSO_Auth::get_vendor_store($user_id);

        // Handle password change
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dso_update_settings'])) {
            check_admin_referer('dso_settings');

            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (!empty($new_password)) {
                if (!wp_check_password($current_password, get_userdata($user_id)->user_pass)) {
                    $error = 'Current password is incorrect.';
                } elseif ($new_password !== $confirm_password) {
                    $error = 'New passwords do not match.';
                } elseif (strlen($new_password) < 8) {
                    $error = 'Password must be at least 8 characters.';
                } else {
                    wp_set_password($new_password, $user_id);
                    $success = 'Password updated successfully.';
                }
            }

            // Update notification preferences
            update_user_meta($user_id, 'dso_email_orders', isset($_POST['email_orders']) ? 1 : 0);
            update_user_meta($user_id, 'dso_email_reviews', isset($_POST['email_reviews']) ? 1 : 0);
            update_user_meta($user_id, 'dso_email_payments', isset($_POST['email_payments']) ? 1 : 0);
        }

        $email_orders = get_user_meta($user_id, 'dso_email_orders', true) !== '0';
        $email_reviews = get_user_meta($user_id, 'dso_email_reviews', true) !== '0';
        $email_payments = get_user_meta($user_id, 'dso_email_payments', true) !== '0';

        ?>
        <div class="dso-page dso-settings">
            <div class="dso-page-header">
                <div>
                    <h1>Settings</h1>
                    <p>Manage your account settings</p>
                </div>
            </div>

            <?php if (!empty($success)): ?>
                <div class="dso-alert dso-alert-success"><?php echo esc_html($success) ?></div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="dso-alert dso-alert-error"><?php echo esc_html($error) ?></div>
            <?php endif; ?>

            <form method="post" class="dso-form">
                <?php wp_nonce_field('dso_settings'); ?>

                <!-- Account -->
                <div class="dso-card">
                    <div class="dso-card-header"><h3>Account Security</h3></div>
                    <div class="dso-card-body">
                        <div class="dso-form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" class="dso-input" />
                        </div>
                        <div class="dso-form-row">
                            <div class="dso-form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password" class="dso-input" minlength="8" />
                            </div>
                            <div class="dso-form-group">
                                <label for="confirm_password">Confirm Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="dso-input" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notification Preferences -->
                <div class="dso-card">
                    <div class="dso-card-header"><h3>Email Notifications</h3></div>
                    <div class="dso-card-body">
                        <div class="dso-form-group">
                            <label class="dso-checkbox-label">
                                <input type="checkbox" name="email_orders" value="1" <?php echo $email_orders ? 'checked' : '' ?> />
                                Receive email for new orders
                            </label>
                        </div>
                        <div class="dso-form-group">
                            <label class="dso-checkbox-label">
                                <input type="checkbox" name="email_reviews" value="1" <?php echo $email_reviews ? 'checked' : '' ?> />
                                Receive email for new reviews
                            </label>
                        </div>
                        <div class="dso-form-group">
                            <label class="dso-checkbox-label">
                                <input type="checkbox" name="email_payments" value="1" <?php echo $email_payments ? 'checked' : '' ?> />
                                Receive email for payment updates
                            </label>
                        </div>
                    </div>
                </div>

                <button type="submit" name="dso_update_settings" value="1" class="dso-btn dso-btn-primary">
                    Save Settings
                </button>
            </form>
        </div>
        <?php
    }
}
