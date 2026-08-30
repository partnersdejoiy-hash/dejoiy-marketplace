<?php
/**
 * Plugin Name: DEJOIY Homepage V3
 * Version: 3.5.0
 */
if (!defined('ABSPATH')) exit;

/* Load V3 via template_redirect — before template hierarchy loads buggy theme files */
add_action('template_redirect', function() {
    if (!is_front_page()) return;
    
    /* Load V3 combined file with full error suppression */
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
