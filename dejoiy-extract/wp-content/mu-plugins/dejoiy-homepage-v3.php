<?php
/**
 * Plugin Name: DEJOIY Homepage V3
 * Version: 3.7.0
 */
if (!defined('ABSPATH')) exit;

/* Load V3 via template_redirect — before template hierarchy loads buggy theme files */
add_action('template_redirect', function() {
    if (!is_front_page()) return;

    /* Marketplace Home (NexGen) owns the front page when active — let the
       normal template pipeline render it via the_content filters. V3 resumes
       automatically when the marketplace home is disabled. */
    if (function_exists('dejoiy_marketplace_home_active') && dejoiy_marketplace_home_active()) {
        return;
    }

    /* Load V3 combined file with full error suppression.
       The Three.js global header is required here — only the V3 fallback
       homepage uses it, so it must not mount its header on any other page. */
    $gh_three = get_stylesheet_directory() . '/dejoiy-global-header-three.php';
    if (is_readable($gh_three) && !function_exists('dejoiy_global_header_three_render')) {
        require_once $gh_three;
    }

    $path = get_stylesheet_directory() . '/dejoiy-homepage-v3-combined.php';
    if (!file_exists($path)) return;
    
    $old = error_reporting(0);
    ob_start();
    @include $path;
    ob_end_clean();
    error_reporting($old);
    
    if (!function_exists('dejoiy_v3_render')) return;
    
    /* Take over the response */
    status_header(200);
    nocache_headers();
    
    echo dejoiy_v3_render();
    exit;
}, 1);
