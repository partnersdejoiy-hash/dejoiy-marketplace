<?php
/**
 * DSO Shipping
 */
if (!defined('ABSPATH')) exit;

class DSO_Shipping {

    public function render() {
        $user_id = get_current_user_id();
        $plugin = Dejoiy_Seller_OS::instance();
        $vendor_id = $plugin->get_vendor_id($user_id);

        // Handle shipping settings update
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dso_update_shipping'])) {
            check_admin_referer('dso_shipping');

            update_post_meta($vendor_id, '_shipping_enabled', isset($_POST['shipping_enabled']) ? 'yes' : 'no');
            update_post_meta($vendor_id, '_free_shipping_min', sanitize_text_field($_POST['free_shipping_min']));
            update_post_meta($vendor_id, '_shipping_processing_time', sanitize_text_field($_POST['processing_time']));

            wp_redirect('?section=shipping&updated=1');
            exit;
        }

        $shipping_enabled = get_post_meta($vendor_id, '_shipping_enabled', true);
        $free_shipping_min = get_post_meta($vendor_id, '_free_shipping_min', true);
        $processing_time = get_post_meta($vendor_id, '_shipping_processing_time', true);

        ?>
        <div class="dso-page dso-shipping">
            <div class="dso-page-header">
                <div>
                    <h1>Shipping</h1>
                    <p>Configure your shipping settings</p>
                </div>
            </div>

            <form method="post" class="dso-form">
                <?php wp_nonce_field('dso_shipping'); ?>

                <div class="dso-card">
                    <div class="dso-card-header"><h3>Shipping Configuration</h3></div>
                    <div class="dso-card-body">
                        <div class="dso-form-group">
                            <label class="dso-checkbox-label">
                                <input type="checkbox" name="shipping_enabled" value="1" <?php echo $shipping_enabled === 'yes' ? 'checked' : '' ?> />
                                Enable shipping for your products
                            </label>
                        </div>
                        <div class="dso-form-row">
                            <div class="dso-form-group">
                                <label for="free_shipping_min">Minimum Order for Free Shipping (₹)</label>
                                <input type="number" id="free_shipping_min" name="free_shipping_min" class="dso-input" step="0.01" min="0" value="<?php echo esc_attr($free_shipping_min) ?>" placeholder="0 for no free shipping" />
                            </div>
                            <div class="dso-form-group">
                                <label for="processing_time">Processing Time</label>
                                <select id="processing_time" name="processing_time" class="dso-select">
                                    <option value="1" <?php echo $processing_time === '1' ? 'selected' : '' ?>>1 business day</option>
                                    <option value="2" <?php echo $processing_time === '2' ? 'selected' : '' ?>>2 business days</option>
                                    <option value="3" <?php echo $processing_time === '3' ? 'selected' : '' ?>>3 business days</option>
                                    <option value="5" <?php echo $processing_time === '5' ? 'selected' : '' ?>>5 business days</option>
                                    <option value="7" <?php echo $processing_time === '7' ? 'selected' : '' ?>>7 business days</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" name="dso_update_shipping" value="1" class="dso-btn dso-btn-primary">
                    Save Shipping Settings
                </button>
            </form>

            <!-- Shiprocket Integration Status -->
            <div class="dso-card">
                <div class="dso-card-header"><h3>Shiprocket Integration</h3></div>
                <div class="dso-card-body">
                    <?php if (class_exists('ShipRocket_WooCommerce')): ?>
                        <div class="dso-alert dso-alert-success">
                            <strong>✓ Shiprocket is active</strong> — Automated shipping and tracking is enabled for your orders.
                        </div>
                    <?php else: ?>
                        <div class="dso-alert dso-alert-info">
                            Shiprocket shipping integration is managed by the store administrator.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
}
