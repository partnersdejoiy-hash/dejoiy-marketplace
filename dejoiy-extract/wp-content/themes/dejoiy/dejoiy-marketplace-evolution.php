<?php
/**
 * DEJOIY Marketplace Evolution — Phase 1 bootstrap.
 *
 * Production-safe, additive modules. Disable via wp-config:
 * define( 'DEJOIY_EVOLUTION_DISABLED', true );
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'DEJOIY_EVOLUTION_DISABLED' ) && DEJOIY_EVOLUTION_DISABLED ) {
	return;
}

if ( ! defined( 'DEJOIY_EVOLUTION_VERSION' ) ) {
	define( 'DEJOIY_EVOLUTION_VERSION', '1.0.0' );
}

/**
 * @return bool
 */
function dejoiy_evolution_is_enabled() {
	if ( defined( 'DEJOIY_EVOLUTION_DISABLED' ) && DEJOIY_EVOLUTION_DISABLED ) {
		return false;
	}
	return '1' === get_option( 'dejoiy_evolution_enabled', '1' );
}

/**
 * Load evolution modules once.
 */
function dejoiy_evolution_boot() {
	if ( ! dejoiy_evolution_is_enabled() ) {
		return;
	}

	static $loaded = false;
	if ( $loaded ) {
		return;
	}
	$loaded = true;

	$dir = get_stylesheet_directory();
	$mods  = array(
		'dejoiy-dpin.php',
		'dejoiy-marketplace-urls.php',
		'dejoiy-order-display-v2.php',
		'dejoiy-os-branding.php',
		'dejoiy-os-design-system.php',
		'dejoiy-product-ecosystem.php',
		'dejoiy-shop-discovery-hub.php',
		'dejoiy-checkout-experience.php',
		'dejoiy-cart-experience.php',
		'dejoiy-contact-experience.php',
		'dejoiy-about-experience.php',
		'dejoiy-services.php',
		'dejoiy-refurbished.php',
		'dejoiy-sellerhub-bridge.php',
		'dejoiy-quickmart.php',
		'dejoiy-home-intelligence.php',
		'dejoiy-universe-home.php',
		'dejoiy-marketplace-home.php',
		'dejoiy-site-chrome.php',
		'dejoiy-joi-intelligence.php',
		'dejoiy-desktop-marketplace-header.php',
		'dejoiy-mobile-os.php',
		'dejoiy-mobile-home-v2.php',
		'dejoiy-mobile-product.php',
	);

	foreach ( $mods as $file ) {
		if ( 'dejoiy-quickmart.php' === $file && defined( 'DEJOIY_QUICKMART_DISABLED' ) && DEJOIY_QUICKMART_DISABLED ) {
			continue;
		}
		if ( 'dejoiy-services.php' === $file && defined( 'DEJOIY_SERVICES_DISABLED' ) && DEJOIY_SERVICES_DISABLED ) {
			continue;
		}
		if ( 'dejoiy-refurbished.php' === $file && defined( 'DEJOIY_REFURBISHED_DISABLED' ) && DEJOIY_REFURBISHED_DISABLED ) {
			continue;
		}
		if ( 'dejoiy-sellerhub-bridge.php' === $file && defined( 'DEJOIY_SELLERHUB_DISABLED' ) && DEJOIY_SELLERHUB_DISABLED ) {
			continue;
		}
		$path = $dir . '/' . $file;
		if ( ! is_readable( $path ) ) {
			continue;
		}
		require_once $path;
	}

	add_action(
		'wp_enqueue_scripts',
		static function () {
			if ( is_admin() ) {
				return;
			}
			$uri = get_stylesheet_directory_uri();
			$dir = get_stylesheet_directory();
			$css = $dir . '/dejoiy-marketplace-os.css';
			$js  = $dir . '/dejoiy-marketplace-os.js';
			if ( file_exists( $css ) ) {
				wp_enqueue_style(
					'dejoiy-marketplace-os',
					$uri . '/dejoiy-marketplace-os.css',
					array(),
					(string) filemtime( $css )
				);
			}
			if ( file_exists( $js ) ) {
				wp_enqueue_script(
					'dejoiy-marketplace-os',
					$uri . '/dejoiy-marketplace-os.js',
					array(),
					(string) filemtime( $js ),
					true
				);
			}
		},
		1005
	);
}

add_action( 'after_setup_theme', 'dejoiy_evolution_boot', 20 );
