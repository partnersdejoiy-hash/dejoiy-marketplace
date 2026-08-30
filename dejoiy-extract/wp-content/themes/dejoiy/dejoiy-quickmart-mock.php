<?php
/**
 * QuickMart mock / fallback product imagery (external CDN — picsum, stable seeds).
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build a stable picsum URL for a category cell.
 *
 * @param string $slug Category slug.
 * @param int    $idx  Cell index 0–3.
 * @return string
 */
function dejoiy_quickmart_mock_image_url( $slug, $idx ) {
	$slug = sanitize_key( (string) $slug );
	$idx  = max( 0, min( 3, (int) $idx ) );
	return sprintf( 'https://picsum.photos/seed/dejoiy-qm-%s-%d/240/240', $slug, $idx );
}

/**
 * @param string $slug Category slug.
 * @return array<int, string>
 */
function dejoiy_quickmart_mock_images( $slug ) {
	$urls = array();
	for ( $i = 0; $i < 4; $i++ ) {
		$urls[] = dejoiy_quickmart_mock_image_url( $slug, $i );
	}
	return $urls;
}

/**
 * @param string $url    Image URL.
 * @param string $class  Class.
 * @param string $alt    Alt text.
 * @return string
 */
function dejoiy_quickmart_mock_img_html( $url, $class = 'qm-bestseller__thumb', $alt = '' ) {
	$alt = $alt ? $alt : __( 'Product', 'dejoiy' );
	return sprintf(
		'<img src="%s" alt="%s" class="%s" loading="lazy" width="120" height="120" decoding="async" referrerpolicy="no-referrer" />',
		esc_url( $url ),
		esc_attr( $alt ),
		esc_attr( $class )
	);
}
