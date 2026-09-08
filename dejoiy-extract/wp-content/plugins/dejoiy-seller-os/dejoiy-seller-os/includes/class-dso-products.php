<?php
/**
 * DSO Products - Product Management
 */
if (!defined('ABSPATH')) exit;

class DSO_Products {

    public function render() {
        $user_id = get_current_user_id();
        $plugin = Dejoiy_Seller_OS::instance();
        $vendor_id = $plugin->get_vendor_id($user_id);
        $products = $this->get_products($vendor_id);
        $stats = $this->get_product_stats($vendor_id);
        ?>
        <div class="dso-page dso-products">
            <div class="dso-page-header">
                <div>
                    <h1>Products</h1>
                    <p>Manage your product catalog</p>
                </div>
                <div class="dso-page-actions">
                    <a href="?section=add-product" class="dso-btn dso-btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Add Product
                    </a>
                </div>
            </div>

            <!-- Product Stats -->
            <div class="dso-mini-stats">
                <div class="dso-mini-stat">
                    <span class="dso-mini-stat-value"><?php echo $stats['total'] ?></span>
                    <span class="dso-mini-stat-label">Total</span>
                </div>
                <div class="dso-mini-stat">
                    <span class="dso-mini-stat-value"><?php echo $stats['published'] ?></span>
                    <span class="dso-mini-stat-label">Published</span>
                </div>
                <div class="dso-mini-stat">
                    <span class="dso-mini-stat-value"><?php echo $stats['draft'] ?></span>
                    <span class="dso-mini-stat-label">Drafts</span>
                </div>
                <div class="dso-mini-stat">
                    <span class="dso-mini-stat-value"><?php echo $stats['low_stock'] ?></span>
                    <span class="dso-mini-stat-label">Low Stock</span>
                </div>
                <div class="dso-mini-stat">
                    <span class="dso-mini-stat-value"><?php echo $stats['out_of_stock'] ?></span>
                    <span class="dso-mini-stat-label">Out of Stock</span>
                </div>
            </div>

            <!-- Filters -->
            <div class="dso-card">
                <div class="dso-filters-bar">
                    <div class="dso-search-box">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" placeholder="Search products..." id="dso-product-search" class="dso-input" />
                    </div>
                    <div class="dso-filter-group">
                        <select id="dso-product-status" class="dso-select">
                            <option value="">All Status</option>
                            <option value="publish">Published</option>
                            <option value="draft">Draft</option>
                            <option value="pending">Pending</option>
                        </select>
                        <select id="dso-product-stock" class="dso-select">
                            <option value="">All Stock</option>
                            <option value="instock">In Stock</option>
                            <option value="lowstock">Low Stock</option>
                            <option value="outofstock">Out of Stock</option>
                        </select>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="dso-table-responsive">
                    <table class="dso-table dso-table-products">
                        <thead>
                            <tr>
                                <th class="dso-th-check"><input type="checkbox" id="dso-select-all" /></th>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Sales</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($products)): ?>
                                <tr class="dso-empty-row">
                                    <td colspan="8">
                                        <div class="dso-empty-state">
                                            <div class="dso-empty-icon">📦</div>
                                            <h3>No products yet</h3>
                                            <p>Add your first product to start selling on DEJOIY.</p>
                                            <a href="?section=add-product" class="dso-btn dso-btn-primary">Add Product</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($products as $product): ?>
                                    <tr class="dso-product-row" data-status="<?php echo esc_attr($product['status']) ?>" data-stock="<?php echo esc_attr($product['stock_status']) ?>">
                                        <td class="dso-td-check"><input type="checkbox" class="dso-product-check" value="<?php echo $product['id'] ?>" /></td>
                                        <td class="dso-td-product">
                                            <div class="dso-product-cell">
                                                <div class="dso-product-thumb"><?php echo $product['image'] ?></div>
                                                <div class="dso-product-info">
                                                    <a href="?section=edit-product&id=<?php echo $product['id'] ?>" class="dso-product-name"><?php echo esc_html($product['name']) ?></a>
                                                    <span class="dso-product-category"><?php echo esc_html($product['category']) ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="dso-sku"><?php echo esc_html($product['sku'] ?: '—') ?></span></td>
                                        <td><?php echo $product['price_html'] ?></td>
                                        <td>
                                            <?php if ($product['manage_stock']): ?>
                                                <span class="dso-stock-qty <?php echo $product['stock_status'] === 'outofstock' ? 'dso-stock-out' : ($product['is_low_stock'] ? 'dso-stock-low' : '') ?>">
                                                    <?php echo $product['stock'] ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="dso-stock-status"><?php echo $product['stock_label'] ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo intval($product['total_sales']) ?></td>
                                        <td><?php echo $product['status_badge'] ?></td>
                                        <td>
                                            <div class="dso-actions">
                                                <a href="?section=edit-product&id=<?php echo $product['id'] ?>" class="dso-action-btn" title="Edit">✏️</a>
                                                <a href="<?php echo get_permalink($product['id']) ?>" class="dso-action-btn" target="_blank" title="View">👁️</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (!empty($products)): ?>
                <div class="dso-pagination">
                    <span class="dso-pagination-info">Showing <?php echo count($products) ?> products</span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            DSO.initProducts();
        });
        </script>
        <?php
    }

    /**
     * Add Product page
     */
    public function add_product() {
        $user_id = get_current_user_id();
        $plugin = Dejoiy_Seller_OS::instance();
        $vendor_id = $plugin->get_vendor_id($user_id);
        $categories = $this->get_product_categories();

        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dso_add_product'])) {
            check_admin_referer('dso_add_product');

            $product_id = wp_insert_post([
                'post_title' => sanitize_text_field($_POST['product_name']),
                'post_content' => wp_kses_post($_POST['description']),
                'post_excerpt' => sanitize_textarea_field($_POST['short_description']),
                'post_status' => sanitize_text_field($_POST['status']),
                'post_type' => 'product',
            ]);

            if ($product_id && !is_wp_error($product_id)) {
                // Set vendor
                update_post_meta($product_id, '_vendor_id', $vendor_id);

                // Pricing
                update_post_meta($product_id, '_regular_price', sanitize_text_field($_POST['regular_price']));
                if (!empty($_POST['sale_price'])) {
                    update_post_meta($product_id, '_sale_price', sanitize_text_field($_POST['sale_price']));
                }

                // SKU
                update_post_meta($product_id, '_sku', sanitize_text_field($_POST['sku']));

                // Stock
                if (!empty($_POST['manage_stock'])) {
                    update_post_meta($product_id, '_manage_stock', 'yes');
                    update_post_meta($product_id, '_stock', intval($_POST['stock_quantity']));
                    update_post_meta($product_id, '_stock_status', intval($_POST['stock_quantity']) > 0 ? 'instock' : 'outofstock');
                } else {
                    update_post_meta($product_id, '_manage_stock', 'no');
                    update_post_meta($product_id, '_stock_status', sanitize_text_field($_POST['stock_status'] ?? 'instock'));
                }

                // Shipping
                if (!empty($_POST['weight'])) update_post_meta($product_id, '_weight', sanitize_text_field($_POST['weight']));
                if (!empty($_POST['length'])) update_post_meta($product_id, '_length', sanitize_text_field($_POST['length']));
                if (!empty($_POST['width'])) update_post_meta($product_id, '_width', sanitize_text_field($_POST['width']));
                if (!empty($_POST['height'])) update_post_meta($product_id, '_height', sanitize_text_field($_POST['height']));

                // Categories
                if (!empty($_POST['product_categories'])) {
                    wp_set_object_terms($product_id, array_map('intval', $_POST['product_categories']), 'product_cat');
                }

                // Featured image
                if (!empty($_POST['featured_image_id'])) {
                    set_post_thumbnail($product_id, intval($_POST['featured_image_id']));
                }

                // Redirect to products list
                wp_redirect('?section=products&success=1');
                exit;
            }
        }

        ?>
        <div class="dso-page dso-add-product">
            <div class="dso-page-header">
                <div>
                    <h1>Add New Product</h1>
                    <p>Create a new product listing</p>
                </div>
                <div class="dso-page-actions">
                    <a href="?section=products" class="dso-btn dso-btn-secondary">← Back to Products</a>
                </div>
            </div>

            <form method="post" class="dso-form" enctype="multipart/form-data">
                <?php wp_nonce_field('dso_add_product'); ?>

                <div class="dso-form-layout">
                    <!-- Main Content -->
                    <div class="dso-form-main">
                        <!-- Basic Information -->
                        <div class="dso-card">
                            <div class="dso-card-header"><h3>Basic Information</h3></div>
                            <div class="dso-card-body">
                                <div class="dso-form-group">
                                    <label for="product_name">Product Name *</label>
                                    <input type="text" id="product_name" name="product_name" class="dso-input" required placeholder="Enter product name" />
                                </div>
                                <div class="dso-form-group">
                                    <label for="description">Description</label>
                                    <textarea id="description" name="description" class="dso-textarea" rows="8" placeholder="Detailed product description..."></textarea>
                                </div>
                                <div class="dso-form-group">
                                    <label for="short_description">Short Description</label>
                                    <textarea id="short_description" name="short_description" class="dso-textarea" rows="3" placeholder="Brief product summary..."></textarea>
                                </div>
                                <div class="dso-form-group">
                                    <label for="product_categories">Category</label>
                                    <select id="product_categories" name="product_categories[]" class="dso-select" multiple>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat->term_id ?>"><?php echo esc_html($cat->name) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Pricing -->
                        <div class="dso-card">
                            <div class="dso-card-header"><h3>Pricing</h3></div>
                            <div class="dso-card-body">
                                <div class="dso-form-row">
                                    <div class="dso-form-group">
                                        <label for="regular_price">Regular Price (₹) *</label>
                                        <input type="number" id="regular_price" name="regular_price" class="dso-input" step="0.01" min="0" required placeholder="0.00" />
                                    </div>
                                    <div class="dso-form-group">
                                        <label for="sale_price">Sale Price (₹)</label>
                                        <input type="number" id="sale_price" name="sale_price" class="dso-input" step="0.01" min="0" placeholder="0.00" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Inventory -->
                        <div class="dso-card">
                            <div class="dso-card-header"><h3>Inventory</h3></div>
                            <div class="dso-card-body">
                                <div class="dso-form-row">
                                    <div class="dso-form-group">
                                        <label for="sku">SKU</label>
                                        <input type="text" id="sku" name="sku" class="dso-input" placeholder="Stock Keeping Unit" />
                                    </div>
                                    <div class="dso-form-group">
                                        <label class="dso-checkbox-label">
                                            <input type="checkbox" name="manage_stock" value="1" id="manage_stock" />
                                            Manage stock quantity
                                        </label>
                                    </div>
                                </div>
                                <div class="dso-form-row" id="stock-fields" style="display:none;">
                                    <div class="dso-form-group">
                                        <label for="stock_quantity">Stock Quantity</label>
                                        <input type="number" id="stock_quantity" name="stock_quantity" class="dso-input" min="0" placeholder="0" />
                                    </div>
                                </div>
                                <div class="dso-form-group" id="stock-status-field">
                                    <label for="stock_status">Stock Status</label>
                                    <select id="stock_status" name="stock_status" class="dso-select">
                                        <option value="instock">In Stock</option>
                                        <option value="outofstock">Out of Stock</option>
                                        <option value="onbackorder">On Backorder</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Shipping -->
                        <div class="dso-card">
                            <div class="dso-card-header"><h3>Shipping</h3></div>
                            <div class="dso-card-body">
                                <div class="dso-form-group">
                                    <label for="weight">Weight (kg)</label>
                                    <input type="number" id="weight" name="weight" class="dso-input" step="0.01" min="0" placeholder="0.00" />
                                </div>
                                <div class="dso-form-row">
                                    <div class="dso-form-group">
                                        <label for="length">Length (cm)</label>
                                        <input type="number" id="length" name="length" class="dso-input" step="0.01" min="0" placeholder="0.00" />
                                    </div>
                                    <div class="dso-form-group">
                                        <label for="width">Width (cm)</label>
                                        <input type="number" id="width" name="width" class="dso-input" step="0.01" min="0" placeholder="0.00" />
                                    </div>
                                    <div class="dso-form-group">
                                        <label for="height">Height (cm)</label>
                                        <input type="number" id="height" name="height" class="dso-input" step="0.01" min="0" placeholder="0.00" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="dso-form-sidebar">
                        <!-- Publish -->
                        <div class="dso-card">
                            <div class="dso-card-header"><h3>Publish</h3></div>
                            <div class="dso-card-body">
                                <div class="dso-form-group">
                                    <label for="status">Status</label>
                                    <select id="status" name="status" class="dso-select">
                                        <option value="publish">Published</option>
                                        <option value="draft">Draft</option>
                                        <option value="pending">Pending Review</option>
                                    </select>
                                </div>
                                <button type="submit" name="dso_add_product" value="1" class="dso-btn dso-btn-primary dso-btn-full">Add Product</button>
                            </div>
                        </div>

                        <!-- Featured Image -->
                        <div class="dso-card">
                            <div class="dso-card-header"><h3>Featured Image</h3></div>
                            <div class="dso-card-body">
                                <div class="dso-image-upload" id="dso-image-upload">
                                    <div class="dso-image-placeholder">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="32" height="32"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                        <p>Click to upload image</p>
                                    </div>
                                    <input type="hidden" name="featured_image_id" id="featured_image_id" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            DSO.initAddProduct();
        });
        </script>
        <?php
    }

    /**
     * Edit Product page
     */
    public function edit_product() {
        $product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$product_id) {
            wp_redirect('?section=products');
            exit;
        }

        $product = wc_get_product($product_id);
        if (!$product) {
            wp_die('Product not found.');
        }

        // Handle update
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dso_edit_product'])) {
            check_admin_referer('dso_edit_product');

            wp_update_post([
                'ID' => $product_id,
                'post_title' => sanitize_text_field($_POST['product_name']),
                'post_content' => wp_kses_post($_POST['description']),
                'post_excerpt' => sanitize_textarea_field($_POST['short_description']),
                'post_status' => sanitize_text_field($_POST['status']),
            ]);

            update_post_meta($product_id, '_regular_price', sanitize_text_field($_POST['regular_price']));
            update_post_meta($product_id, '_price', sanitize_text_field($_POST['regular_price']));
            if (!empty($_POST['sale_price'])) {
                update_post_meta($product_id, '_sale_price', sanitize_text_field($_POST['sale_price']));
            } else {
                delete_post_meta($product_id, '_sale_price');
            }

            update_post_meta($product_id, '_sku', sanitize_text_field($_POST['sku']));

            if (!empty($_POST['manage_stock'])) {
                update_post_meta($product_id, '_manage_stock', 'yes');
                update_post_meta($product_id, '_stock', intval($_POST['stock_quantity']));
            } else {
                update_post_meta($product_id, '_manage_stock', 'no');
                update_post_meta($product_id, '_stock_status', sanitize_text_field($_POST['stock_status'] ?? 'instock'));
            }

            if (!empty($_POST['weight'])) update_post_meta($product_id, '_weight', sanitize_text_field($_POST['weight']));
            if (!empty($_POST['length'])) update_post_meta($product_id, '_length', sanitize_text_field($_POST['length']));
            if (!empty($_POST['width'])) update_post_meta($product_id, '_width', sanitize_text_field($_POST['width']));
            if (!empty($_POST['height'])) update_post_meta($product_id, '_height', sanitize_text_field($_POST['height']));

            if (!empty($_POST['product_categories'])) {
                wp_set_object_terms($product_id, array_map('intval', $_POST['product_categories']), 'product_cat');
            }

            wp_redirect('?section=products&updated=1');
            exit;
        }

        $categories = $this->get_product_categories();
        $current_cats = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'ids']);

        ?>
        <div class="dso-page dso-edit-product">
            <div class="dso-page-header">
                <div>
                    <h1>Edit: <?php echo esc_html($product->get_name()) ?></h1>
                </div>
                <div class="dso-page-actions">
                    <a href="<?php echo get_permalink($product_id) ?>" target="_blank" class="dso-btn dso-btn-secondary">View Product</a>
                    <a href="?section=products" class="dso-btn dso-btn-secondary">← Back</a>
                </div>
            </div>

            <form method="post" class="dso-form">
                <?php wp_nonce_field('dso_edit_product'); ?>

                <div class="dso-form-layout">
                    <div class="dso-form-main">
                        <div class="dso-card">
                            <div class="dso-card-header"><h3>Basic Information</h3></div>
                            <div class="dso-card-body">
                                <div class="dso-form-group">
                                    <label for="product_name">Product Name *</label>
                                    <input type="text" id="product_name" name="product_name" class="dso-input" required value="<?php echo esc_attr($product->get_name()) ?>" />
                                </div>
                                <div class="dso-form-group">
                                    <label for="description">Description</label>
                                    <textarea id="description" name="description" class="dso-textarea" rows="8"><?php echo wp_kses_post($product->get_description()) ?></textarea>
                                </div>
                                <div class="dso-form-group">
                                    <label for="short_description">Short Description</label>
                                    <textarea id="short_description" name="short_description" class="dso-textarea" rows="3"><?php echo esc_textarea($product->get_short_description()) ?></textarea>
                                </div>
                                <div class="dso-form-group">
                                    <label for="product_categories">Category</label>
                                    <select id="product_categories" name="product_categories[]" class="dso-select" multiple>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat->term_id ?>" <?php echo in_array($cat->term_id, $current_cats) ? 'selected' : '' ?>><?php echo esc_html($cat->name) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="dso-card">
                            <div class="dso-card-header"><h3>Pricing</h3></div>
                            <div class="dso-card-body">
                                <div class="dso-form-row">
                                    <div class="dso-form-group">
                                        <label for="regular_price">Regular Price (₹) *</label>
                                        <input type="number" id="regular_price" name="regular_price" class="dso-input" step="0.01" min="0" required value="<?php echo esc_attr($product->get_regular_price()) ?>" />
                                    </div>
                                    <div class="dso-form-group">
                                        <label for="sale_price">Sale Price (₹)</label>
                                        <input type="number" id="sale_price" name="sale_price" class="dso-input" step="0.01" min="0" value="<?php echo esc_attr($product->get_sale_price()) ?>" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="dso-card">
                            <div class="dso-card-header"><h3>Inventory</h3></div>
                            <div class="dso-card-body">
                                <div class="dso-form-row">
                                    <div class="dso-form-group">
                                        <label for="sku">SKU</label>
                                        <input type="text" id="sku" name="sku" class="dso-input" value="<?php echo esc_attr($product->get_sku()) ?>" />
                                    </div>
                                    <div class="dso-form-group">
                                        <label class="dso-checkbox-label">
                                            <input type="checkbox" name="manage_stock" value="1" id="manage_stock" <?php echo $product->get_manage_stock() ? 'checked' : '' ?> />
                                            Manage stock quantity
                                        </label>
                                    </div>
                                </div>
                                <div class="dso-form-row" id="stock-fields" <?php echo !$product->get_manage_stock() ? 'style="display:none;"' : '' ?>>
                                    <div class="dso-form-group">
                                        <label for="stock_quantity">Stock Quantity</label>
                                        <input type="number" id="stock_quantity" name="stock_quantity" class="dso-input" min="0" value="<?php echo esc_attr($product->get_stock_quantity()) ?>" />
                                    </div>
                                </div>
                                <div class="dso-form-group" id="stock-status-field" <?php echo $product->get_manage_stock() ? 'style="display:none;"' : '' ?>>
                                    <label for="stock_status">Stock Status</label>
                                    <select id="stock_status" name="stock_status" class="dso-select">
                                        <option value="instock" <?php echo $product->get_stock_status() === 'instock' ? 'selected' : '' ?>>In Stock</option>
                                        <option value="outofstock" <?php echo $product->get_stock_status() === 'outofstock' ? 'selected' : '' ?>>Out of Stock</option>
                                        <option value="onbackorder" <?php echo $product->get_stock_status() === 'onbackorder' ? 'selected' : '' ?>>On Backorder</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="dso-card">
                            <div class="dso-card-header"><h3>Shipping</h3></div>
                            <div class="dso-card-body">
                                <div class="dso-form-group">
                                    <label for="weight">Weight (kg)</label>
                                    <input type="number" id="weight" name="weight" class="dso-input" step="0.01" min="0" value="<?php echo esc_attr($product->get_weight()) ?>" />
                                </div>
                                <div class="dso-form-row">
                                    <div class="dso-form-group">
                                        <label for="length">Length (cm)</label>
                                        <input type="number" id="length" name="length" class="dso-input" step="0.01" min="0" value="<?php echo esc_attr($product->get_length()) ?>" />
                                    </div>
                                    <div class="dso-form-group">
                                        <label for="width">Width (cm)</label>
                                        <input type="number" id="width" name="width" class="dso-input" step="0.01" min="0" value="<?php echo esc_attr($product->get_width()) ?>" />
                                    </div>
                                    <div class="dso-form-group">
                                        <label for="height">Height (cm)</label>
                                        <input type="number" id="height" name="height" class="dso-input" step="0.01" min="0" value="<?php echo esc_attr($product->get_height()) ?>" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="dso-form-sidebar">
                        <div class="dso-card">
                            <div class="dso-card-header"><h3>Publish</h3></div>
                            <div class="dso-card-body">
                                <div class="dso-form-group">
                                    <label for="status">Status</label>
                                    <select id="status" name="status" class="dso-select">
                                        <option value="publish" <?php echo $product->get_status() === 'publish' ? 'selected' : '' ?>>Published</option>
                                        <option value="draft" <?php echo $product->get_status() === 'draft' ? 'selected' : '' ?>>Draft</option>
                                        <option value="pending" <?php echo $product->get_status() === 'pending' ? 'selected' : '' ?>>Pending Review</option>
                                    </select>
                                </div>
                                <button type="submit" name="dso_edit_product" value="1" class="dso-btn dso-btn-primary dso-btn-full">Update Product</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            DSO.initEditProduct();
        });
        </script>
        <?php
    }

    /**
     * Inventory page
     */
    public function inventory() {
        $user_id = get_current_user_id();
        $plugin = Dejoiy_Seller_OS::instance();
        $vendor_id = $plugin->get_vendor_id($user_id);
        $products = $this->get_inventory_data($vendor_id);

        ?>
        <div class="dso-page dso-inventory">
            <div class="dso-page-header">
                <div>
                    <h1>Inventory Management</h1>
                    <p>Track and manage your product stock levels</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="dso-card">
                <div class="dso-filters-bar">
                    <div class="dso-filter-group">
                        <select id="dso-inv-filter" class="dso-select">
                            <option value="">All Items</option>
                            <option value="lowstock">Low Stock</option>
                            <option value="outofstock">Out of Stock</option>
                            <option value="instock">In Stock</option>
                        </select>
                    </div>
                </div>

                <div class="dso-table-responsive">
                    <table class="dso-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Stock Qty</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($products)): ?>
                                <tr class="dso-empty-row">
                                    <td colspan="5">
                                        <div class="dso-empty-inline">
                                            <p>No inventory data</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($products as $p): ?>
                                    <tr data-stock="<?php echo esc_attr($p['stock_status']) ?>">
                                        <td class="dso-td-product">
                                            <div class="dso-product-cell">
                                                <div class="dso-product-thumb"><?php echo $p['image'] ?></div>
                                                <span class="dso-product-name"><?php echo esc_html($p['name']) ?></span>
                                            </div>
                                        </td>
                                        <td><?php echo esc_html($p['sku'] ?: '—') ?></td>
                                        <td>
                                            <input type="number" class="dso-input dso-input-sm dso-stock-input" data-id="<?php echo $p['id'] ?>" value="<?php echo $p['stock'] ?>" min="0" />
                                        </td>
                                        <td><?php echo $p['stock_badge'] ?></td>
                                        <td>
                                            <button class="dso-btn dso-btn-sm dso-btn-primary dso-update-stock" data-id="<?php echo $p['id'] ?>">Update</button>
                                        </td>
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

    /**
     * Get products list
     */
    public function get_products($vendor_id, $limit = 50, $offset = 0) {
        global $wpdb;

        if (!$vendor_id) return [];

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->prefix}postmeta
            WHERE meta_key = '_vendor_id' AND meta_value = %d
            GROUP BY post_id
            ORDER BY post_id DESC
            LIMIT %d OFFSET %d",
            $vendor_id, $limit, $offset
        ));

        $products = [];
        foreach ($results as $row) {
            $product = wc_get_product($row->post_id);
            if (!$product) continue;

            $stock = $product->get_stock_quantity();
            $stock_status = $product->get_stock_status();
            $low_threshold = get_option('woocommerce_notify_low_stock_amount', 2);

            $image = get_the_post_thumbnail($row->post_id, [50, 50]);
            if (!$image) {
                $image = '<div class="dso-product-placeholder-img"></div>';
            }

            $products[] = [
                'id' => $product->get_id(),
                'name' => $product->get_name(),
                'sku' => $product->get_sku(),
                'price_html' => $product->get_price_html(),
                'stock' => $product->get_manage_stock() ? $stock : '—',
                'stock_status' => $stock_status,
                'manage_stock' => $product->get_manage_stock(),
                'is_low_stock' => $product->get_manage_stock() && $stock <= $low_threshold && $stock > 0,
                'stock_label' => $stock_status === 'instock' ? 'In Stock' : ($stock_status === 'outofstock' ? 'Out of Stock' : 'Backorder'),
                'total_sales' => $product->get_total_sales(),
                'status' => $product->get_status(),
                'status_badge' => $this->status_badge($product->get_status()),
                'image' => $image,
                'category' => $this->get_product_category_name($product->get_id()),
                'date' => $product->get_date_created() ? $product->get_date_created()->format('M j, Y') : '—',
            ];
        }

        return $products;
    }

    /**
     * Get inventory data
     */
    public function get_inventory_data($vendor_id) {
        global $wpdb;

        if (!$vendor_id) return [];

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->prefix}postmeta
            WHERE meta_key = '_vendor_id' AND meta_value = %d
            GROUP BY post_id",
            $vendor_id
        ));

        $items = [];
        foreach ($results as $row) {
            $product = wc_get_product($row->post_id);
            if (!$product) continue;

            $stock = $product->get_stock_quantity();
            $stock_status = $product->get_stock_status();
            $low_threshold = get_option('woocommerce_notify_low_stock_amount', 2);
            $is_low = $product->get_manage_stock() && $stock <= $low_threshold && $stock > 0;

            $image = get_the_post_thumbnail($row->post_id, [40, 40]);
            if (!$image) {
                $image = '<div class="dso-product-placeholder-img"></div>';
            }

            $badge = 'dso-badge-green';
            $label = 'In Stock';
            if ($stock_status === 'outofstock') {
                $badge = 'dso-badge-red';
                $label = 'Out of Stock';
            } elseif ($is_low) {
                $badge = 'dso-badge-orange';
                $label = 'Low Stock';
            }

            $items[] = [
                'id' => $product->get_id(),
                'name' => $product->get_name(),
                'sku' => $product->get_sku(),
                'stock' => $product->get_manage_stock() ? $stock : '—',
                'stock_status' => $stock_status,
                'stock_badge' => '<span class="dso-badge ' . $badge . '">' . $label . '</span>',
                'image' => $image,
            ];
        }

        return $items;
    }

    /**
     * Get product stats
     */
    private function get_product_stats($vendor_id) {
        global $wpdb;

        $all = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT post_id) FROM {$wpdb->prefix}postmeta WHERE meta_key = '_vendor_id' AND meta_value = %d",
            $vendor_id
        ));

        $published = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT pm.post_id) FROM {$wpdb->prefix}postmeta pm
            INNER JOIN {$wpdb->prefix}posts p ON pm.post_id = p.ID
            WHERE pm.meta_key = '_vendor_id' AND pm.meta_value = %d AND p.post_type = 'product' AND p.post_status = 'publish'",
            $vendor_id
        ));

        $draft = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT pm.post_id) FROM {$wpdb->prefix}postmeta pm
            INNER JOIN {$wpdb->prefix}posts p ON pm.post_id = p.ID
            WHERE pm.meta_key = '_vendor_id' AND pm.meta_value = %d AND p.post_type = 'product' AND p.post_status = 'draft'",
            $vendor_id
        ));

        $low = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT pm.post_id) FROM {$wpdb->prefix}postmeta pm
            INNER JOIN {$wpdb->prefix}postmeta stock ON pm.post_id = stock.post_id AND stock.meta_key = '_stock'
            INNER JOIN {$wpdb->prefix}postmeta manage ON pm.post_id = manage.post_id AND manage.meta_key = '_manage_stock' AND manage.meta_value = 'yes'
            WHERE pm.meta_key = '_vendor_id' AND pm.meta_value = %d AND stock.meta_value <= %d AND stock.meta_value > 0",
            $vendor_id, get_option('woocommerce_notify_low_stock_amount', 2)
        ));

        $out = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT pm.post_id) FROM {$wpdb->prefix}postmeta pm
            INNER JOIN {$wpdb->prefix}postmeta stock ON pm.post_id = stock.post_id AND stock.meta_key = '_stock_status'
            WHERE pm.meta_key = '_vendor_id' AND pm.meta_value = %d AND stock.meta_value = 'outofstock'",
            $vendor_id
        ));

        return [
            'total' => intval($all),
            'published' => intval($published),
            'draft' => intval($draft),
            'low_stock' => intval($low),
            'out_of_stock' => intval($out),
        ];
    }

    private function get_product_categories() {
        return get_terms([
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
            'parent' => 0,
        ]);
    }

    private function get_product_category_name($product_id) {
        $terms = get_the_terms($product_id, 'product_cat');
        if ($terms && !is_wp_error($terms)) {
            return $terms[0]->name;
        }
        return 'Uncategorized';
    }

    private function status_badge($status) {
        $map = [
            'publish' => ['Published', 'dso-badge-green'],
            'draft' => ['Draft', 'dso-badge-gray'],
            'pending' => ['Pending', 'dso-badge-yellow'],
            'private' => ['Private', 'dso-badge-blue'],
        ];
        $label = $map[$status][0] ?? ucfirst($status);
        $class = $map[$status][1] ?? 'dso-badge-gray';
        return '<span class="dso-badge ' . $class . '">' . $label . '</span>';
    }
}
