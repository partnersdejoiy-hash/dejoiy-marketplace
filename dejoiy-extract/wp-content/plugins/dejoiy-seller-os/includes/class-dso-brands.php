<?php
if (!defined('ABSPATH')) exit;
class DSO_Brands {
    public function render() {
        $user_id = get_current_user_id();
        $plugin = Dejoiy_Seller_OS::instance();
        $vendor_id = $plugin->get_vendor_id($user_id);
        $brands = $this->get_brands($vendor_id);
        ?>
        <div class="dso-page dso-brands">
            <div class="dso-page-header"><div><h1>Brands</h1><p>Manage your brand presence on DEJOIY</p></div></div>
            <div class="dso-card">
                <div class="dso-card-header"><h3>Your Brands</h3></div>
                <div class="dso-card-body">
                    <?php if (empty($brands)): ?>
                        <div class="dso-empty-state">
                            <div class="dso-empty-icon">🏷️</div>
                            <h3>No brands yet</h3>
                            <p>Assign brands to your products to build brand identity on DEJOIY.</p>
                        </div>
                    <?php else: ?>
                        <div class="dso-brands-grid">
                            <?php foreach ($brands as $brand): ?>
                                <div class="dso-brand-card">
                                    <h4><?php echo esc_html($brand['name']) ?></h4>
                                    <span class="dso-text-muted"><?php echo $brand['products'] ?> products</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
    private function get_brands($vendor_id) {
        global $wpdb;
        if (!$vendor_id) return [];
        $brands = get_terms(['taxonomy' => 'product_brand', 'hide_empty' => false]);
        if (is_wp_error($brands)) return [];
        $result = [];
        foreach ($brands as $brand) {
            $count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(DISTINCT pm.post_id) FROM {$wpdb->prefix}postmeta pm
                INNER JOIN {$wpdb->prefix}postmeta vendor ON pm.post_id = vendor.post_id AND vendor.meta_key = '_vendor_id' AND vendor.meta_value = %d
                INNER JOIN {$wpdb->term_relationships} tr ON pm.post_id = tr.object_id
                WHERE tr.term_taxonomy_id = %d",
                $vendor_id, $brand->term_id
            ));
            if ($count > 0) {
                $result[] = ['name' => $brand->name, 'products' => intval($count)];
            }
        }
        return $result;
    }
}
