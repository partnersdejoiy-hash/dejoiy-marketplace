<?php
/**
 * DEJOIY Motion Adapters
 * 
 * Premium motion system powered by ThreeUI Community concepts.
 * Adapts ThreeUI visual technology into DEJOIY-branded components.
 * 
 * ThreeUI Community: https://github.com/MengTo/threeui (MIT License)
 * DEJOIY wrapper layer — not raw ThreeUI exposure.
 * 
 * @package Dejoiy_Marketplace
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Dejoiy_Motion_Adapters {

    private static $instance = null;
    private static $version = '1.0.0';

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 999 );
        add_action( 'wp_footer', array( $this, 'render_motion_containers' ) );
    }

    /**
     * Enqueue motion CSS and JS
     */
    public function enqueue_assets() {
        $theme_uri = get_template_directory_uri();

        // Motion CSS
        wp_enqueue_style(
            'dejoiy-motion-adapters',
            $theme_uri . '/dejoiy-motion/dejoiy-motion-adapters.css',
            array(),
            self::$version
        );

        // Motion JS (lightweight, no React dependency)
        wp_enqueue_script(
            'dejoiy-motion-adapters',
            $theme_uri . '/dejoiy-motion/dejoiy-motion-adapters.js',
            array(),
            self::$version,
            true
        );

        // Pass config to JS
        wp_localize_script( 'dejoiy-motion-adapters', 'dejoiyMotion', array(
            'isMobile'  => wp_is_mobile(),
            'reducedMotion' => false, // Will be detected via JS
            'debug'     => false,
        ) );
    }

    /**
     * Render motion container elements in footer
     * These are lightweight placeholders that JS initializes
     */
    public function render_motion_containers() {
        ?>
        <!-- DEJOIY Motion System — initialized by dejoiy-motion-adapters.js -->
        <div id="dejoiy-motion-root" class="dm-root" aria-hidden="true"></div>
        <?php
    }

    /**
     * Helper: Check if page should use motion
     */
    public static function should_use_motion() {
        // Skip on checkout, account, cart pages for performance
        if ( is_checkout() || is_account_page() || is_cart() ) {
            return false;
        }
        return true;
    }
}

// Initialize
Dejoiy_Motion_Adapters::instance();
