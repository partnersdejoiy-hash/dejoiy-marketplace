<?php
/**
 * DEJOIY XStore / 8theme white-label (display only).
 *
 * Masks vendor branding in admin + customer UI. Does NOT rename theme/plugin
 * files, folders, hooks, or asset URLs — that would break WooCommerce.
 *
 * Disable: define( 'DEJOIY_XSTORE_MASK_DISABLED', true );
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'DEJOIY_XSTORE_MASK_DISABLED' ) && DEJOIY_XSTORE_MASK_DISABLED ) {
	return;
}

/**
 * @param string $text Text.
 * @return string
 */
function dejoiy_xstore_mask_text( $text ) {
	if ( ! is_string( $text ) || '' === $text ) {
		return $text;
	}
	$from = array( 'XStore', 'xstore', 'XSTORE', '8theme', '8Theme', 'EightTheme', 'Eight Theme' );
	$to   = array( 'DEJOIY', 'DEJOIY', 'DEJOIY', 'DEJOIY', 'DEJOIY', 'DEJOIY', 'DEJOIY' );
	return str_replace( $from, $to, $text );
}

/**
 * Plugin list labels in wp-admin (paths unchanged).
 *
 * @param array<string, array<string, mixed>> $plugins Plugins.
 * @return array<string, array<string, mixed>>
 */
function dejoiy_xstore_mask_all_plugins( $plugins ) {
	if ( ! is_array( $plugins ) ) {
		return $plugins;
	}
	foreach ( $plugins as $file => &$data ) {
		if ( ! is_array( $data ) ) {
			continue;
		}
		if ( false !== strpos( $file, 'et-core' ) || false !== strpos( $file, 'xstore' ) ) {
			if ( isset( $data['Name'] ) ) {
				$data['Name'] = dejoiy_xstore_mask_text( (string) $data['Name'] );
			}
			if ( isset( $data['Description'] ) ) {
				$data['Description'] = dejoiy_xstore_mask_text( (string) $data['Description'] );
			}
			if ( isset( $data['Author'] ) ) {
				$data['Author'] = dejoiy_xstore_mask_text( (string) $data['Author'] );
			}
		}
	}
	return $plugins;
}
add_filter( 'all_plugins', 'dejoiy_xstore_mask_all_plugins', 50 );

/**
 * Theme names in Appearance screen (folder slug stays xstore).
 *
 * @param array<int, array<string, mixed>> $themes JS themes.
 * @return array<int, array<string, mixed>>
 */
function dejoiy_xstore_mask_themes_for_js( $themes ) {
	if ( ! is_array( $themes ) ) {
		return $themes;
	}
	foreach ( $themes as &$theme ) {
		if ( ! is_array( $theme ) ) {
			continue;
		}
		if ( isset( $theme['name'] ) ) {
			$theme['name'] = dejoiy_xstore_mask_text( (string) $theme['name'] );
		}
		if ( isset( $theme['description'] ) ) {
			$theme['description'] = dejoiy_xstore_mask_text( (string) $theme['description'] );
		}
		if ( isset( $theme['author'] ) ) {
			$theme['author'] = dejoiy_xstore_mask_text( (string) $theme['author'] );
		}
	}
	return $themes;
}
add_filter( 'wp_prepare_themes_for_js', 'dejoiy_xstore_mask_themes_for_js', 50 );

/**
 * Login screen branding.
 *
 * @return string
 */
function dejoiy_xstore_mask_login_header() {
	return 'DEJOIY';
}
add_filter( 'login_headertext', 'dejoiy_xstore_mask_login_header' );

/**
 * @return string
 */
function dejoiy_xstore_mask_login_url() {
	return home_url( '/' );
}
add_filter( 'login_headerurl', 'dejoiy_xstore_mask_login_url' );

/**
 * Hide 8theme promo links in admin chrome (display only).
 */
function dejoiy_xstore_mask_admin_css() {
	if ( ! is_admin() ) {
		return;
	}
	echo '<style id="dejoiy-xstore-mask-admin">';
	echo 'a[href*="8theme.com"],a[href*="xstore.8theme.com"]{display:none!important;}';
	echo '.etheme-panel-logo img[alt*="8theme"],.etheme-panel-logo img[alt*="XStore"]{content:url("https://dejoiy.com/wp-content/uploads/2026/05/DEJOIY-OFFICIAL-LOGO.png");max-width:120px;height:auto;}';
	echo '</style>';
}
add_action( 'admin_head', 'dejoiy_xstore_mask_admin_css', 99 );

/**
 * Document title / admin bar — never expose vendor name on frontend.
 *
 * @param array<string, string> $parts Title parts.
 * @return array<string, string>
 */
function dejoiy_xstore_mask_document_title( $parts ) {
	if ( is_admin() ) {
		return $parts;
	}
	foreach ( $parts as $key => $part ) {
		$parts[ $key ] = dejoiy_xstore_mask_text( (string) $part );
	}
	return $parts;
}
add_filter( 'document_title_parts', 'dejoiy_xstore_mask_document_title', 50 );
