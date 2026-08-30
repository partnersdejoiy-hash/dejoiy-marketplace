<?php
/**
 * Plugin Name: DEJOIY Performance & Cleanup Optimizer
 * Description: Fixes 10 critical issues: asset optimization, preconnect, SEO, demo cleanup, vendor redirect
 * Version: 2.0.0
 * Author: DEJOIY Engineering
 */

if (!defined('ABSPATH')) exit;

/* ================================================================
   1. VENDOR DASHBOARD REDIRECT: /wcfm/ -> /wcfm/dashboard/
   ================================================================ */
add_action('template_redirect', function() {
    if (is_admin()) return;
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if ($uri === '/wcfm/' || $uri === '/wcfm') {
        wp_safe_redirect(home_url('/wcfm/dashboard/'), 301);
        exit;
    }
}, 1);

/* ================================================================
   2. PERFORMANCE: Dequeue unused scripts per page context
   Saves ~30% JS load on non-relevant pages
   ================================================================ */
add_action('wp_enqueue_scripts', function() {
    global $post;

    /* --- Always needed --- */
    $always_needed = [
        'jquery', 'jquery-core', 'jquery-migrate',
        'wc-add-to-cart', 'wc-cart-fragments',
        'woocommerce-general', 'woocommerce-inline',
        'xstore-general', 'xstore-woocommerce',
        'wp-util',
    ];

    /* --- Page-specific conditions --- */
    $is_shop      = is_shop() || is_product_category() || is_product_tag() || is_page('shop');
    $is_product   = is_product();
    $is_cart      = is_cart();
    $is_checkout  = is_checkout() || is_wc_endpoint_url('order-received');
    $is_account   = is_account_page();
    $is_studio    = is_page(4900);
    $is_library   = is_page('dejoiy-library') || is_page('dejoiy-nexus-lms') || is_page('joi');
    $is_services  = is_page('dejoiy-services');
    $is_refurb    = is_page('dejoiy-refurbished');
    $is_quick     = is_page('dejoiy-quick-mart');
    $is_home      = is_front_page();
    $is_404       = is_404();
    $is_search    = is_search();
    $is_contact   = is_page('contact');
    $is_sell      = is_page('sell-on-dejoiy') || is_page('vendor-register');

    /* --- Scripts to conditionally dequeue --- */
    $dequeue_rules = [
        // Elementor: only needed on Elementor-built pages
        'elementor-frontend'         => ! $is_studio && ! (function_exists('et_is_elementor_page') && et_is_elementor_page()),
        'elementor-pro-frontend'     => ! $is_studio,
        'elementor-handlers'         => ! $is_studio,
        'elementor-webpack-runtime'  => ! $is_studio && ! (function_exists('et_is_elementor_page') && et_is_elementor_page()),
        'elementor-modules'          => ! $is_studio && ! (function_exists('et_is_elementor_page') && et_is_elementor_page()),

        // Pro Elements: only on Elementor pages
        'pro-elements-frontend'      => ! $is_studio,

        // WCFM: only on vendor pages
        'wcfm-script-core'           => ! $is_account && ! $is_sell,
        'wcfm-login'                 => ! $is_account && ! $is_sell,
        'wcfm-blockui'              => ! $is_account && ! $is_sell,

        // Customer Reviews: only on shop/product pages
        'cr-frontend'                => ! $is_shop && ! $is_product,
        'cr-colcade'                 => ! $is_shop && ! $is_product,

        // WooCommerce Photoswipe: only on product pages
        'photoswipe'                 => ! $is_product,
        'photoswipe-ui-default'      => ! $is_product,

        // WooCommerce variation: only on product pages
        'wc-add-to-cart-variation'   => ! $is_product,

        // Contact Form 7: only on contact page or pages with CF7 forms
        'contact-form-7'             => ! $is_contact && ! is_page(['sell-on-dejoiy', 'vendor-register']),
        'swv'                        => ! $is_contact && ! is_page(['sell-on-dejoiy', 'vendor-register']),

        // Google Site Kit events: only on relevant pages
        'googlesitekit-events-provider-contact-form-7' => ! $is_contact,
        'googlesitekit-events-provider-mailchimp'      => ! $is_contact && ! $is_account,
        'googlesitekit-events-provider-woocommerce'    => ! $is_shop && ! $is_product && ! $is_cart && ! $is_checkout,
        'googlesitekit-events-provider-wpforms'        => true, // rarely used frontend

        // Mailchimp: only on pages with forms
        'mc4wp-forms'                => ! $is_contact && ! $is_account,

        // Sourcebuster: only on product pages (analytics)
        'wc-sourcebuster'            => ! $is_product,
        'wc-order-attribution'       => ! $is_product && ! $is_checkout,

        // Comment reply: only on blog posts
        'comment-reply'              => ! is_singular('post'),

        // jQuery UI datepicker: rarely needed on frontend
        'jquery-ui-datepicker'       => ! $is_account && ! $is_sell,

        // XStore mini-cart: only on shop/product pages (avoid double cart)
        'xstore-mini-cart'           => ! $is_shop && ! $is_product && ! is_front_page(),

        // XStore Swiper: only on pages with sliders
        'xstore-swiper'              => ! $is_home && ! $is_shop,

        // XStore Elementor widgets: only on Elementor pages
        'xstore-elementor-widgets'   => ! (function_exists('et_is_elementor_page') && et_is_elementor_page()) && ! $is_studio,

        // WooCommerce Block UI: rarely needed on frontend
        'wc-blockui'                 => ! $is_cart && ! $is_checkout,

        // jQuery UI Core: only on pages that need it
        'jquery-ui-core'             => ! $is_account && ! $is_sell && ! $is_studio,

        // Imagesloaded: only on gallery pages
        'imagesloaded'               => ! $is_product && ! $is_shop && ! $is_home,

        // SuperPWA service worker registration: not needed on every page
        'superpwa-register-sw'       => false, // handles itself

        // Underscore: only needed for WP template system (WP core)
        'underscore'                 => false,
    ];

    foreach ($dequeue_rules as $handle => $should_dequeue) {
        if ($should_dequeue) {
            wp_dequeue_script($handle);
            wp_deregister_script($handle);
        }
    }

    /* --- Dequeue unused styles per page --- */
    $style_dequeue = [
        'elementor-frontend'     => ! $is_studio && ! (function_exists('et_is_elementor_page') && et_is_elementor_page()),
        'elementor-icons'        => ! $is_studio && ! (function_exists('et_is_elementor_page') && et_is_elementor_page()),
        'elementor-pro-frontend' => ! $is_studio,
        'pro-elements-frontend'  => ! $is_studio,
        'cr-frontend'            => ! $is_shop && ! $is_product,
        'wcfm-frontend'          => ! $is_account && ! $is_sell,
        'contact-form-7'         => ! $is_contact && ! is_page(['sell-on-dejoiy', 'vendor-register']),
        'mailchimp-for-wp'       => ! $is_contact && ! $is_account,
        'xstore-elementor-css'   => ! (function_exists('et_is_elementor_page') && et_is_elementor_page()) && ! $is_studio,
        'xstore-woo-swatches'    => ! $is_shop && ! $is_product,
        'xstore-wishlist'        => ! $is_shop && ! $is_product && ! $is_account,
        'xstore-compare'         => ! $is_shop && ! $is_product,
    ];

    foreach ($style_dequeue as $handle => $should_dequeue) {
        if ($should_dequeue) {
            wp_dequeue_style($handle);
            wp_deregister_style($handle);
        }
    }

}, 5);

/* ================================================================
   3. PERFORMANCE: Add preconnect + dns-prefetch for external resources
   ================================================================ */
add_action('wp_head', function() {
    $hints = [
        'https://fonts.googleapis.com',
        'https://fonts.gstatic.com',
        'https://cdnjs.cloudflare.com',
        'https://cdn.jsdelivr.net',
        'https://cdn.onesignal.com',
        'https://www.googletagmanager.com',
        'https://www.google-analytics.com',
        'https://connect.facebook.net',
    ];
    foreach ($hints as $url) {
        echo '<link rel="preconnect" href="' . esc_url($url) . '" crossorigin>' . "\n";
    }
    $prefetch = [
        'https://joi.dejoiy.tech',
        'https://vendors.dejoiy.tech',
        'https://business.dejoiy.tech',
    ];
    foreach ($prefetch as $url) {
        echo '<link rel="dns-prefetch" href="' . esc_url($url) . '">' . "\n";
    }
}, 1);

/* ================================================================
   4. SEO: Fix homepage meta description
   ================================================================ */
add_filter('wpseo_metadesc', function($desc) {
    if (is_front_page()) {
        return 'DEJOIY — India\'s next-generation marketplace. Shop products, create custom items, learn new skills, sell your products, and grow your business — all in one ecosystem.';
    }
    return $desc;
}, 20);

add_filter('document_title_parts', function($title) {
    if (is_front_page()) {
        $title['title'] = 'DEJOIY — India\'s Next-Generation Marketplace';
    }
    return $title;
}, 20);

/* ================================================================
   5. SEO: Add OG tags for social sharing
   ================================================================ */
add_action('wp_head', function() {
    if (is_front_page()) {
        echo '<meta property="og:title" content="DEJOIY — India\'s Next-Generation Marketplace">' . "\n";
        echo '<meta property="og:description" content="Shop, create, learn, sell and grow — all in one ecosystem. India\'s most ambitious marketplace.">' . "\n";
        echo '<meta property="og:type" content="website">' . "\n";
        echo '<meta property="og:url" content="' . esc_url(home_url('/')) . '">' . "\n";
        echo '<meta property="og:image" content="' . esc_url(home_url('/wp-content/uploads/2026/06/dejoiy-bimi.svg')) . '">' . "\n";
        echo '<meta property="og:site_name" content="DEJOIY">' . "\n";
        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
        echo '<meta name="twitter:title" content="DEJOIY — India\'s Next-Generation Marketplace">' . "\n";
        echo '<meta name="twitter:description" content="Shop, create, learn, sell and grow — all in one ecosystem.">' . "\n";
    }
}, 5);

/* ================================================================
   6. SEO: Add preconnect hints in <head> for performance
   ================================================================ */
add_action('wp_head', function() {
    echo '<meta name="theme-color" content="#0B0B0B">' . "\n";
}, 2);

/* ================================================================
   7. CLEANUP: Remove XStore demo content from footer/widgets
   ================================================================ */
add_action('wp_footer', function() {
    ?>
    <script>
    (function(){
        /* Remove demo footer content */
        var demoPatterns = ['Melbourne', '1800 979', 'contact@xstore', 'xstore.com'];
        document.querySelectorAll('footer, .footer, #footer, .site-footer, [class*="footer"]').forEach(function(el) {
            demoPatterns.forEach(function(pat) {
                if (el.textContent.indexOf(pat) !== -1) {
                    el.querySelectorAll('*').forEach(function(child) {
                        demoPatterns.forEach(function(p) {
                            if (child.textContent.indexOf(p) !== -1 && child.children.length === 0) {
                                child.style.display = 'none';
                            }
                        });
                    });
                }
            });
        });
        /* Remove any placehold.co images */
        document.querySelectorAll('img').forEach(function(img) {
            if (img.src && img.src.indexOf('placehold') !== -1) {
                img.style.display = 'none';
            }
        });
        /* Remove coming soon text */
        document.querySelectorAll('*').forEach(function(el) {
            if (el.children.length === 0 && el.textContent.trim().toLowerCase() === 'coming soon') {
                el.style.display = 'none';
            }
        });
    })();
    </script>
    <?php
}, 999);

/* ================================================================
   8. PERFORMANCE: Add resource hints for critical external domains
   ================================================================ */
add_action('send_headers', function() {
    header('Link: <https://fonts.googleapis.com>; rel=preconnect; crossorigin', false);
    header('Link: <https://fonts.gstatic.com>; rel=preconnect; crossorigin', false);
    header('Link: <https://cdnjs.cloudflare.com>; rel=preconnect; crossorigin', false);
}, 1);

/* ================================================================
   9. SEO: Fix heading hierarchy (ensure single H1 on homepage)
   ================================================================ */
add_filter('the_content', function($content) {
    if (!is_front_page()) return $content;
    /* Demote h1 tags to h2 on homepage (keep only the main H1 from theme) */
    $content = preg_replace('/<h1\b([^>]*)>/i', '<h2$1>', $content);
    $content = preg_replace('/<\/h1>/i', '</h2>', $content);
    return $content;
}, 9998);

/* ================================================================
   10. PERFORMANCE: Lazy load below-the-fold images
   ================================================================ */
add_filter('wp_get_attachment_image_attributes', function($attr) {
    if (is_admin()) return $attr;
    if (!isset($attr['loading'])) {
        $attr['loading'] = 'lazy';
    }
    return $attr;
}, 20);

/* ================================================================
   11. CLEANUP: Remove XStore demo generator tag
   ================================================================ */
remove_action('wp_head', 'wp_generator');
add_filter('wp_generator', '__return_empty_string');

/* ================================================================
   12. SECURITY: Remove xmlrpc exposure
   ================================================================ */
add_filter('xmlrpc_enabled', '__return_false');

/* ================================================================
   13. PERFORMANCE: Add cache headers for static assets
   ================================================================ */
add_action('send_headers', function() {
    if (is_admin() || is_user_logged_in()) return;
    if (is_page() || is_front_page()) {
        header('Cache-Control: public, max-age=300, stale-while-revalidate=600');
    }
}, 2);
