<?php
/**
 * DEJOIY — Branding Shield & Admin CSS Cleaner
 *
 * Strips ALL WordPress, WooCommerce, WCFM, and admin-only plugin branding
 * from the frontend HTML output. No end-user should ever detect that the
 * site runs on WordPress/WooCommerce/WCFM.
 *
 * Covers:
 *  1. Block editor / AI plugin CSS removal (handles + inline)
 *  2. Meta generator tag removal
 *  3. Body class branding cleanup
 *  4. HTML comment branding cleanup
 *  5. sourceURL comment cleanup
 *  6. WCFM link/element hiding
 *  7. WooCommerce footer credit removal
 *  8. wp_login.php URL scrubbing from frontend JS
 *
 * @version 2.0.0
 * @author  DEJOIY Engineering
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ================================================================
   1. DEQUEUE ADMIN-ONLY STYLES
   ================================================================ */
add_action( 'wp_enqueue_scripts', function () {
    $admin_only_handles = [
        'ai-summarization-styles',
        'ai-summarization',
        'ai-content-translation',
        'ai-content-translation-styles',
        'wp-block-editor-content-styles',
        'wp-block-editor',
        'wp-edit-post-common',
        'wp-components',
        'wp-block-library',
        'wp-block-library-theme',
        'wp-editor-classic-asset-queue',
        'wp-editor',
        'global-styles',
        'classic-theme-styles',
        'wp-block-patterns',
        'wp-reusable-blocks',
        'wp-edit-blocks',
        'wp-format-library',
        'wp-query-pagination',
        'wp-block-theme',
        'make-do-blocks',
        'astra-notices',
    ];

    foreach ( $admin_only_handles as $handle ) {
        wp_dequeue_style( $handle );
        wp_deregister_style( $handle );
    }

    global $wp_styles;
    if ( is_a( $wp_styles, 'WP_Styles' ) ) {
        $admin_path_patterns = [
            'ai-summarization',
            'ai-content-translation',
            'block-editor',
            'wp-includes/css/dist/block-editor',
            'wp-includes/css/dist/components',
            'wp-includes/css/dist/edit-post',
            'wp-includes/css/dist/block-library',
            'wp-includes/css/dist/format-library',
        ];

        foreach ( $wp_styles->queue as $handle ) {
            $style = $wp_styles->registered[ $handle ] ?? null;
            if ( ! $style || empty( $style->src ) ) continue;
            foreach ( $admin_path_patterns as $pat ) {
                if ( strpos( $style->src, $pat ) !== false ) {
                    wp_dequeue_style( $handle );
                    wp_deregister_style( $handle );
                    break;
                }
            }
        }
    }
}, 9999 );

/* ================================================================
   2. REMOVE META GENERATOR TAGS
   ================================================================ */
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'wp_generator', '__return_empty_string' );
add_filter( 'the_generator', '__return_empty_string' );

/* ================================================================
   3. CLEAN BODY CLASSES (remove WordPress/WooCommerce/XStore branding)
   ================================================================ */
add_filter( 'body_class', function ( $classes ) {
    if ( is_admin() ) return $classes;

    $strip_patterns = [ 'woocommerce', 'wp-theme-', 'theme-xstore', 'wp-child-theme-', 'wcfm' ];

    $cleaned = [];
    foreach ( $classes as $class ) {
        $lower = strtolower( $class );
        $keep  = true;
        foreach ( $strip_patterns as $pat ) {
            if ( strpos( $lower, $pat ) !== false ) { $keep = false; break; }
        }
        if ( $keep ) $cleaned[] = $class;
    }
    return $cleaned;
}, 9999 );

/* ================================================================
   4. MASTER OUTPUT BUFFER — STRIP ALL REMAINING BRANDING FROM HTML
   ================================================================ */
add_action( 'template_redirect', function () {
    if ( is_admin() ) return;
    ob_start( function ( $html ) {
        /* --- Meta tags --- */
        $html = preg_replace( '#<meta\s+name=["\']generator["\'][^>]*>#i', '', $html );
        $html = preg_replace( '#<meta\s+name=["\']onesignal-plugin["\'][^>]*>#i', '', $html );

        /* --- Strip inline <style> blocks with admin CSS --- */
        $html = preg_replace( '#<style[^>]*>[^<]*ai-summarization[^<]*</style>#i', '', $html );
        $html = preg_replace( '#<style[^>]*>[^<]*ai-content-translation[^<]*</style>#i', '', $html );
        $html = preg_replace( '#<style[^>]*>[^<]*block-editor[^<]*</style>#i', '', $html );

        /* --- Strip body class branding (belt + suspenders with the filter above) --- */
        $html = preg_replace_callback(
            '#(<body[^>]*\sclass=")([^"]*)(")#i',
            function ( $m ) {
                $classes = explode( ' ', $m[2] );
                $strip   = [ 'woocommerce', 'wp-theme-', 'theme-xstore', 'wp-child-theme-', 'wcfm' ];
                $cleaned = array_filter( $classes, function ( $c ) use ( $strip ) {
                    $lower = strtolower( $c );
                    foreach ( $strip as $pat ) {
                        if ( strpos( $lower, $pat ) !== false ) return false;
                    }
                    return true;
                });
                return $m[1] . implode( ' ', $cleaned ) . $m[3];
            },
            $html
        );

        /* --- Strip WordPress/plugin HTML comments --- */
        $html = preg_replace( '#<!--\s*Manifest added by SuperPWA[^>]*-->#i', '', $html );

        /* --- Strip sourceURL=woocommerce comments from inline JS --- */
        $html = preg_replace( '#//#\s*sourceURL=woocommerce[^;]*;?#i', '', $html );
        $html = preg_replace( '#/\*#\s*sourceURL=woocommerce[^*]*\*/#i', '', $html );

        return $html;
    });
}, 1 );

/* ================================================================
   5. HIDE WCFM "BECOME A VENDOR" AND OTHER WCFM LINKS
   ================================================================ */
add_action( 'wp_footer', function () {
    if ( is_admin() ) return;
    ?>
    <script>
    (function(){
        var wcfmTexts = ['WCFM', 'WCFM Marketplace', 'Store Manager', 'WordPress', 'WooCommerce'];
        var allEls = document.querySelectorAll('a, span, div, p, li, button, h1, h2, h3, h4, h5, h6');
        allEls.forEach(function(el) {
            if (el.children.length > 0) return;
            var txt = (el.textContent || '').trim();
            wcfmTexts.forEach(function(brand) {
                if (txt === brand || txt.indexOf(brand) !== -1) {
                    el.style.display = 'none';
                    el.setAttribute('aria-hidden', 'true');
                }
            });
        });
        document.querySelectorAll('.wcfmmp_become_vendor_link, .wcfm-become-a-vendor, [class*="wcfmmp"], [class*="wcfm_"]').forEach(function(el) {
            el.style.display = 'none';
            el.setAttribute('aria-hidden', 'true');
        });
    })();
    </script>
    <?php
}, 9999 );

/* ================================================================
   6. SUPPRESS WOOCOMMERCE FOOTER CREDITS
   ================================================================ */
add_filter( 'wcorg_credit_text', '__return_empty_string' );

/* ================================================================
   7. HIDE WP-LOGIN URLs FROM FRONTEND JS VARS
   ================================================================ */
add_action( 'wp_head', function () {
    if ( is_admin() ) return;
    ?>
    <script>
    (function(){
        document.querySelectorAll('a[href*="wp-login"]').forEach(function(a) {
            a.href = a.href.replace(/wp-login\.php/g, 'my-account/');
        });
    })();
    </script>
    <?php
}, 9999 );
