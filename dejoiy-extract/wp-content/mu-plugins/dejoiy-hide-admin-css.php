<?php
/**
 * DEJOIY — Hide Admin/Editor CSS from Frontend
 * Prevents block editor, AI summarization, AI translation, and other admin-only
 * plugin stylesheets from leaking to non-logged-in visitors on dejoiy.com.
 *
 * @version 1.0.0
 * @author  DEJOIY Engineering
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Aggressively strip any stylesheet whose handle or source path
 * contains patterns that belong exclusively to the WordPress block editor
 * or admin-only AI plugins.
 */
add_action( 'wp_enqueue_scripts', function () {
    // 1) Dequeue styles by known handles
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
        'global-styles',                // sometimes leaks from FSE plugins
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

    // 2) Walk all enqueued styles and remove any whose src matches
    //    known admin-only plugin path patterns.
    global $wp_styles;
    if ( ! is_a( $wp_styles, 'WP_Styles' ) ) return;

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
}, 9999 );

/**
 * Strip the actual inline <style> blocks that leak block-editor CSS
 * from the final HTML output. Uses output buffering on
 * 'template_redirect' so the buffer wraps ALL wp_head output.
 */
add_action( 'template_redirect', function () {
    if ( is_admin() ) return;
    ob_start( function ( $html ) {
        // Remove inline <style> blocks containing ai-summarization CSS
        $html = preg_replace(
            '#<style[^>]*>[^<]*ai-summarization[^<]*</style>#i',
            '', $html
        );
        // Remove inline <style> blocks containing ai-content-translation CSS
        $html = preg_replace(
            '#<style[^>]*>[^<]*ai-content-translation[^<]*</style>#i',
            '', $html
        );
        // Remove inline <style> blocks containing block-editor selectors
        $html = preg_replace(
            '#<style[^>]*>[^<]*block-editor[^<]*</style>#i',
            '', $html
        );
        return $html;
    });
}, 1 );
