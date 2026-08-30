<?php
/**
 * DEJOIY Homepage V3 — Amazon-Level Super Marketplace
 * Main file: combines all parts + WooCommerce product sections
 *
 * @package Dejoiy
 */
if (!defined('ABSPATH')) exit;

/* Load helper functions from existing template */
require_once get_stylesheet_directory() . '/dejoiy-homepage-v3-part1.php';
require_once get_stylesheet_directory() . '/dejoiy-homepage-v3-part2.php';
require_once get_stylesheet_directory() . '/dejoiy-homepage-v3-part3.php';
require_once get_stylesheet_directory() . '/dejoiy-homepage-v3-part4.php';

/**
 * Render a horizontal product shelf (cards)
 */
function dejoiy_v3_shelf($args, $world = 'market', $limit = 8) {
    $defaults = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'no_found_rows' => true,
    );
    $q = new WP_Query(array_merge($defaults, $args));
    if (!$q->have_posts()) { wp_reset_postdata(); return; }
    echo '<div class="djv3-scroll-row">';
    while ($q->have_posts()) { $q->the_post();
        $pid = get_the_ID();
        $product = wc_get_product($pid);
        if (!$product) continue;
        $url = function_exists('dejoiy_ecosystem_product_url') ? dejoiy_ecosystem_product_url($pid) : get_permalink($pid);
        $img = get_the_post_thumbnail_url($pid, 'woocommerce_thumbnail');
        if (!$img) {
            $gallery = $product->get_gallery_image_ids();
            if (!empty($gallery)) $img = wp_get_attachment_image_url($gallery[0], 'woocommerce_thumbnail');
        }
        $name = $product->get_name();
        $price_html = wp_strip_all_tags($product->get_price_html());
        $on_sale = $product->is_on_sale();
        $rating = $product->get_average_rating();
        $seller = '';
        $author_id = (int) get_post_field('post_author', $pid);
        if ($author_id > 0 && function_exists('wcfm_get_vendor_store_name')) {
            $seller = wcfm_get_vendor_store_name($author_id);
        }
        ?>
        <article class="djv3-card djv3-card--relative" style="min-width:220px;max-width:260px;width:220px;">
            <button type="button" class="djv3-card__fav" aria-label="Save to favorites">♡</button>
            <a href="<?php echo esc_url($url); ?>">
                <img class="djv3-card__img" src="<?php echo esc_url($img ?: 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22280%22 height=%22350%22%3E%3Crect fill=%22%23F5F7FF%22 width=%22280%22 height=%22350%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%239AA3B5%22 font-family=%22sans-serif%22 font-size=%2214%22%3ENo Image%3C/text%3E%3C/svg%3E'); ?>" alt="<?php echo esc_attr($name); ?>" loading="lazy" decoding="async" width="280" height="350">
                <?php if ($on_sale) : ?>
                    <span class="djv3-deal-card__discount">SALE</span>
                <?php endif; ?>
                <div class="djv3-card__body">
                    <h3 class="djv3-card__title"><?php echo esc_html($name); ?></h3>
                    <div><?php echo wp_kses_post($product->get_price_html()); ?></div>
                    <?php if ($rating > 0) : ?>
                        <div class="djv3-card__meta">★ <?php echo esc_html($rating); ?></div>
                    <?php endif; ?>
                    <?php if ($seller) : ?>
                        <div class="djv3-card__meta"><?php echo esc_html($seller); ?></div>
                    <?php endif; ?>
                </div>
            </a>
        </article>
        <?php
    }
    wp_reset_postdata();
    echo '</div>';
}

/**
 * Render the complete V3 homepage
 */
function dejoiy_v3_render() {
    if (!class_exists('WooCommerce')) return;

    ob_start();

    // Inject CSS
    echo '<style>' . dejoiy_v3_design_system_css() . dejoiy_v3_section_css() . '</style>';

    // Wrapper
    echo '<div class="djv3">';

    // Header
    dejoiy_v3_render_header();

    // Hero
    dejoiy_v3_render_hero();

    // Categories
    dejoiy_v3_render_categories();

    // JOI AI
    dejoiy_v3_render_joi();

    // DEJOIY Worlds
    dejoiy_v3_render_worlds();

    // Deals Section
    echo '<section class="djv3-section djv3-deals djv3-reveal"><div class="djv3-container">';
    echo '<div class="djv3-section__header"><div><h2 class="djv3-section__title">🏷️ Joy Deals</h2><p class="djv3-section__subtitle">Best prices, curated for you</p></div>';
    echo '<a href="' . esc_url(home_url('/dejoiy-festival-sale/')) . '" class="djv3-section__action">View All Deals →</a></div>';
    dejoiy_v3_shelf(array(
        'posts_per_page' => 8,
        'orderby' => 'date',
        'order' => 'DESC',
        'meta_query' => array(array(
            'key' => '_sale_price',
            'compare' => 'EXISTS',
        )),
    ), 'deals', 8);
    echo '</div></section>';

    // Nexus Section
    $nexus_posts = dejoiy_universe_get_products(array(
        'posts_per_page' => 8,
        'tax_query' => array(array(
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => array('dejoiy-library', 'e-books', 'courses'),
        )),
    ));
    if (!empty($nexus_posts)) {
        echo '<section class="djv3-world-section djv3-world-section--nexus djv3-reveal"><div class="djv3-container">';
        echo '<div class="djv3-world-section__header"><div><h2 class="djv3-section__title"><span class="djv3-world-section__icon">📚</span> DEJOIY Nexus</h2><p class="djv3-section__subtitle">Read. Learn. Grow. — Books, eBooks & courses.</p></div>';
        echo '<a href="' . esc_url(home_url('/dejoiy-library/?dejoiy_library=1')) . '" class="djv3-section__action">Enter Nexus →</a></div>';
        echo '<div class="djv3-scroll-row">';
        foreach ($nexus_posts as $p) {
            $product = wc_get_product($p->ID);
            if (!$product) continue;
            $url = function_exists('dejoiy_ecosystem_product_url') ? dejoiy_ecosystem_product_url($p->ID) : get_permalink($p->ID);
            $img = get_the_post_thumbnail_url($p->ID, 'woocommerce_thumbnail');
            if (!$img) {
                $gallery = $product->get_gallery_image_ids();
                if (!empty($gallery)) $img = wp_get_attachment_image_url($gallery[0], 'woocommerce_thumbnail');
            }
            ?>
            <article class="djv3-card djv3-card--relative" style="min-width:200px;max-width:240px;width:200px;">
                <a href="<?php echo esc_url($url); ?>">
                    <img class="djv3-card__img" src="<?php echo esc_url($img ?: 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22260%22%3E%3Crect fill=%22%23F5F7FF%22 width=%22200%22 height=%22260%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%239AA3B5%22 font-family=%22sans-serif%22 font-size=%2213%22%3E📚%3C/text%3E%3C/svg%3E'); ?>" alt="<?php echo esc_attr($product->get_name()); ?>" loading="lazy" width="200" height="260">
                    <div class="djv3-card__body">
                        <span class="djv3-badge djv3-badge--eco" style="margin-bottom:6px;">Nexus</span>
                        <h3 class="djv3-card__title"><?php echo esc_html($product->get_name()); ?></h3>
                        <div><?php echo wp_kses_post($product->get_price_html()); ?></div>
                    </div>
                </a>
            </article>
            <?php
        }
        echo '</div></div></section>';
    }

    // Custom Studio Section
    $studio_posts = dejoiy_universe_get_products(array(
        'posts_per_page' => 6,
        'tax_query' => array(array(
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => array('customized-products', 'custom-t-shirts'),
        )),
    ));
    echo '<section class="djv3-world-section djv3-world-section--studio djv3-reveal"><div class="djv3-container">';
    echo '<div class="djv3-world-section__header"><div><h2 class="djv3-section__title"><span class="djv3-world-section__icon">🎨</span> Create Something That\'s Yours</h2><p class="djv3-section__subtitle">Custom T-Shirts, Mugs, Caps & more — designed by you.</p></div>';
    echo '<a href="' . esc_url(home_url('/dejoiy-custom-studio/')) . '" class="djv3-section__action">Open Custom Studio →</a></div>';
    if (!empty($studio_posts)) {
        dejoiy_v3_shelf(array('post__in' => array_map(function($p) { return $p->ID; }, $studio_posts), 'orderby' => 'post__in'), 'studio', 6);
    } else {
        echo '<p style="color:var(--djv3-muted);text-align:center;padding:40px 0;">Custom Studio products coming soon. <a href="' . esc_url(home_url('/dejoiy-custom-studio/')) . '" style="color:var(--djv3-blue);font-weight:600;">Open Studio →</a></p>';
    }
    echo '</div></section>';

    // Services / Hire Section
    $service_posts = dejoiy_universe_get_products(array(
        'posts_per_page' => 6,
        'tax_query' => array(array(
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => array('services', 'digital-marketing', 'graphic-design', 'content-writing'),
        )),
    ));
    echo '<section class="djv3-world-section djv3-world-section--services djv3-reveal"><div class="djv3-container">';
    echo '<div class="djv3-world-section__header"><div><h2 class="djv3-section__title"><span class="djv3-world-section__icon">🤝</span> Need a Professional?</h2><p class="djv3-section__subtitle">Hire trusted experts for your business & personal needs.</p></div>';
    echo '<a href="' . esc_url(home_url('/dejoiy-services/')) . '" class="djv3-section__action">Explore Services →</a></div>';
    if (!empty($service_posts)) {
        echo '<div class="djv3-scroll-row">';
        foreach ($service_posts as $p) {
            $product = wc_get_product($p->ID);
            if (!$product) continue;
            $url = function_exists('dejoiy_ecosystem_product_url') ? dejoiy_ecosystem_product_url($p->ID) : get_permalink($p->ID);
            ?>
            <article class="djv3-service-card" style="min-width:260px;max-width:300px;width:280px;">
                <a href="<?php echo esc_url($url); ?>">
                    <div class="djv3-service-card__cat">Service</div>
                    <h3 class="djv3-service-card__title"><?php echo esc_html($product->get_name()); ?></h3>
                    <div class="djv3-service-card__footer">
                        <span class="djv3-service-card__price"><?php echo wp_strip_all_tags($product->get_price_html()); ?></span>
                        <?php if ($product->get_average_rating() > 0) : ?>
                            <span class="djv3-service-card__rating">★ <?php echo esc_html($product->get_average_rating()); ?></span>
                        <?php endif; ?>
                    </div>
                </a>
            </article>
            <?php
        }
        echo '</div>';
    } else {
        echo '<p style="color:var(--djv3-muted);text-align:center;padding:40px 0;">Professional services launching soon. <a href="' . esc_url(home_url('/sell-on-dejoiy/')) . '" style="color:var(--djv3-blue);font-weight:600;">Become a Service Provider →</a></p>';
    }
    echo '</div></section>';

    // Renew / Refurbished Section
    $renew_posts = dejoiy_universe_get_products(array(
        'posts_per_page' => 6,
        'tax_query' => array(array(
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => array('renewed-refurbished', 'refurbished'),
        )),
    ));
    echo '<section class="djv3-world-section djv3-world-section--renew djv3-reveal"><div class="djv3-container">';
    echo '<div class="djv3-world-section__header"><div><h2 class="djv3-section__title"><span class="djv3-world-section__icon">♻️</span> Renew — Save More, Choose Smarter</h2><p class="djv3-section__subtitle">Certified refurbished tech at smarter prices.</p></div>';
    echo '<a href="' . esc_url(home_url('/dejoiy-refurbished/')) . '" class="djv3-section__action">Shop Renew →</a></div>';
    if (!empty($renew_posts)) {
        dejoiy_v3_shelf(array('post__in' => array_map(function($p) { return $p->ID; }, $renew_posts), 'orderby' => 'post__in'), 'renew', 6);
    } else {
        echo '<p style="color:var(--djv3-muted);text-align:center;padding:40px 0;">Refurbished products coming soon.</p>';
    }
    echo '</div></section>';

    // Trending / Recommended Section
    $trending = dejoiy_universe_get_products(array(
        'posts_per_page' => 8,
        'meta_key' => 'total_sales',
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
    ));
    if (!empty($trending)) {
        echo '<section class="djv3-section djv3-reveal"><div class="djv3-container">';
        echo '<div class="djv3-section__header"><div><h2 class="djv3-section__title">🔥 Trending on DEJOIY</h2><p class="djv3-section__subtitle">What people are loving right now</p></div>';
        echo '<a href="' . esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/')) . '" class="djv3-section__action">View All →</a></div>';
        dejoiy_v3_shelf(array('post__in' => array_map(function($p) { return $p->ID; }, $trending), 'orderby' => 'post__in'), 'trending', 8);
        echo '</div></section>';
    }

    // Trust Strip
    dejoiy_v3_render_trust();

    // Seller CTA
    dejoiy_v3_render_seller_cta();

    // Footer
    dejoiy_v3_render_footer();

    // Mobile Bottom Nav
    dejoiy_v3_render_bottom_nav();

    echo '</div>'; /* .djv3 */

    // JS
    dejoiy_v3_render_js();

    return ob_get_clean();
}
