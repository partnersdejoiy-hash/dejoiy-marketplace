<?php
/**
 * DSO Store Settings
 */
if (!defined('ABSPATH')) exit;

class DSO_Store {

    public function render() {
        $user_id = get_current_user_id();
        $store = DSO_Auth::get_vendor_store($user_id);

        // Handle update
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dso_update_store'])) {
            check_admin_referer('dso_update_store');
            $plugin = Dejoiy_Seller_OS::instance();
            $vendor_id = $plugin->get_vendor_id($user_id);

            if ($vendor_id) {
                wp_update_post([
                    'ID' => $vendor_id,
                    'post_title' => sanitize_text_field($_POST['store_name']),
                    'post_content' => wp_kses_post($_POST['store_description']),
                ]);

                update_post_meta($vendor_id, '_store_email', sanitize_email($_POST['store_email']));
                update_post_meta($vendor_id, '_store_phone', sanitize_text_field($_POST['store_phone']));
                update_post_meta($vendor_id, '_store_address', sanitize_textarea_field($_POST['store_address']));
                update_post_meta($vendor_id, '_store_city', sanitize_text_field($_POST['store_city']));
                update_post_meta($vendor_id, '_store_state', sanitize_text_field($_POST['store_state']));
                update_post_meta($vendor_id, '_store_zip', sanitize_text_field($_POST['store_zip']));
                update_post_meta($vendor_id, '_store_country', sanitize_text_field($_POST['store_country']));
                update_post_meta($vendor_id, '_store_facebook', esc_url_raw($_POST['store_facebook']));
                update_post_meta($vendor_id, '_store_twitter', esc_url_raw($_POST['store_twitter']));
                update_post_meta($vendor_id, '_store_instagram', esc_url_raw($_POST['store_instagram']));
                update_post_meta($vendor_id, '_store_youtube', esc_url_raw($_POST['store_youtube']));
                update_post_meta($vendor_id, '_store_website', esc_url_raw($_POST['store_website']));

                // Featured image
                if (!empty($_POST['store_logo_id'])) {
                    set_post_thumbnail($vendor_id, intval($_POST['store_logo_id']));
                }

                wp_redirect('?section=store&updated=1');
                exit;
            }
        }

        ?>
        <div class="dso-page dso-store-settings">
            <div class="dso-page-header">
                <div>
                    <h1>Store Settings</h1>
                    <p>Manage your store profile and appearance</p>
                </div>
            </div>

            <form method="post" class="dso-form" enctype="multipart/form-data">
                <?php wp_nonce_field('dso_update_store'); ?>

                <div class="dso-form-layout">
                    <div class="dso-form-main">
                        <!-- Store Info -->
                        <div class="dso-card">
                            <div class="dso-card-header"><h3>Store Information</h3></div>
                            <div class="dso-card-body">
                                <div class="dso-form-group">
                                    <label for="store_name">Store Name *</label>
                                    <input type="text" id="store_name" name="store_name" class="dso-input" required value="<?php echo esc_attr($store['name'] ?? '') ?>" />
                                </div>
                                <div class="dso-form-group">
                                    <label for="store_description">Store Description</label>
                                    <textarea id="store_description" name="store_description" class="dso-textarea" rows="5"><?php echo esc_textarea($store['description'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Contact -->
                        <div class="dso-card">
                            <div class="dso-card-header"><h3>Contact Information</h3></div>
                            <div class="dso-card-body">
                                <div class="dso-form-row">
                                    <div class="dso-form-group">
                                        <label for="store_email">Email</label>
                                        <input type="email" id="store_email" name="store_email" class="dso-input" value="<?php echo esc_attr(get_post_meta($store['id'] ?? 0, '_store_email', true) ?: $store['email'] ?? '') ?>" />
                                    </div>
                                    <div class="dso-form-group">
                                        <label for="store_phone">Phone</label>
                                        <input type="tel" id="store_phone" name="store_phone" class="dso-input" value="<?php echo esc_attr(get_post_meta($store['id'] ?? 0, '_store_phone', true)) ?>" />
                                    </div>
                                </div>
                                <div class="dso-form-group">
                                    <label for="store_address">Address</label>
                                    <textarea id="store_address" name="store_address" class="dso-textarea" rows="2"><?php echo esc_textarea(get_post_meta($store['id'] ?? 0, '_store_address', true)) ?></textarea>
                                </div>
                                <div class="dso-form-row">
                                    <div class="dso-form-group">
                                        <label for="store_city">City</label>
                                        <input type="text" id="store_city" name="store_city" class="dso-input" value="<?php echo esc_attr(get_post_meta($store['id'] ?? 0, '_store_city', true)) ?>" />
                                    </div>
                                    <div class="dso-form-group">
                                        <label for="store_state">State</label>
                                        <input type="text" id="store_state" name="store_state" class="dso-input" value="<?php echo esc_attr(get_post_meta($store['id'] ?? 0, '_store_state', true)) ?>" />
                                    </div>
                                    <div class="dso-form-group">
                                        <label for="store_zip">ZIP Code</label>
                                        <input type="text" id="store_zip" name="store_zip" class="dso-input" value="<?php echo esc_attr(get_post_meta($store['id'] ?? 0, '_store_zip', true)) ?>" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Social Links -->
                        <div class="dso-card">
                            <div class="dso-card-header"><h3>Social Links</h3></div>
                            <div class="dso-card-body">
                                <div class="dso-form-group">
                                    <label for="store_facebook">Facebook</label>
                                    <input type="url" id="store_facebook" name="store_facebook" class="dso-input" placeholder="https://facebook.com/..." value="<?php echo esc_attr(get_post_meta($store['id'] ?? 0, '_store_facebook', true)) ?>" />
                                </div>
                                <div class="dso-form-group">
                                    <label for="store_instagram">Instagram</label>
                                    <input type="url" id="store_instagram" name="store_instagram" class="dso-input" placeholder="https://instagram.com/..." value="<?php echo esc_attr(get_post_meta($store['id'] ?? 0, '_store_instagram', true)) ?>" />
                                </div>
                                <div class="dso-form-group">
                                    <label for="store_twitter">Twitter / X</label>
                                    <input type="url" id="store_twitter" name="store_twitter" class="dso-input" placeholder="https://twitter.com/..." value="<?php echo esc_attr(get_post_meta($store['id'] ?? 0, '_store_twitter', true)) ?>" />
                                </div>
                                <div class="dso-form-group">
                                    <label for="store_youtube">YouTube</label>
                                    <input type="url" id="store_youtube" name="store_youtube" class="dso-input" placeholder="https://youtube.com/..." value="<?php echo esc_attr(get_post_meta($store['id'] ?? 0, '_store_youtube', true)) ?>" />
                                </div>
                                <div class="dso-form-group">
                                    <label for="store_website">Website</label>
                                    <input type="url" id="store_website" name="store_website" class="dso-input" placeholder="https://..." value="<?php echo esc_attr(get_post_meta($store['id'] ?? 0, '_store_website', true)) ?>" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="dso-form-sidebar">
                        <div class="dso-card">
                            <div class="dso-card-header"><h3>Store Logo</h3></div>
                            <div class="dso-card-body">
                                <div class="dso-image-upload" id="dso-logo-upload">
                                    <?php if (!empty($store['logo'])): ?>
                                        <img src="<?php echo esc_url($store['logo']) ?>" alt="Store Logo" class="dso-current-image" />
                                    <?php else: ?>
                                        <div class="dso-image-placeholder">
                                            <p>Upload Logo</p>
                                        </div>
                                    <?php endif; ?>
                                    <input type="hidden" name="store_logo_id" id="store_logo_id" />
                                </div>
                            </div>
                        </div>

                        <div class="dso-card">
                            <div class="dso-card-body">
                                <button type="submit" name="dso_update_store" value="1" class="dso-btn dso-btn-primary dso-btn-full">
                                    Save Changes
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <?php
    }
}
