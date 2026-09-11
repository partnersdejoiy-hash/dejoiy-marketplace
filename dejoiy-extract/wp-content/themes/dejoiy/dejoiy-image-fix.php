<?php
/**
 * DEJOIY Image Fix
 *
 * 1) Disables XStore lazy-load placeholder (real src moved to data-src) so
 *    product photos actually render on shop, category, cart, single product
 *    gallery, and quick view pages.
 * 2) Renders colourful "canva" placeholder cards for products that have no
 *    image at all, using the product name initial + a hashed gradient.
 *
 * Loaded from dejoiy/functions.php (theme child).
 */

defined( 'ABSPATH' ) || exit;

// -----------------------------------------------------------------------------
// 1) Neutralize XStore lazy-loading at the PHP level.
//    - removes the wp_get_attachment_image_attributes hook that rewrites
//      <img src> -> 1x1 placeholder + data-src
//    - removes the XStore_LazyLoad buffer that rewrites gallery/post images
// -----------------------------------------------------------------------------
add_action( 'init', function () {
	remove_filter( 'wp_get_attachment_image_attributes', 'etheme_lazy_attachment_attrs', 10, 3 );
	if ( class_exists( 'XStore_LazyLoad' ) ) {
		remove_action( 'wp_head', array( 'XStore_LazyLoad', 'setup' ), 99 );
	}
}, 20 );

// -----------------------------------------------------------------------------
// 2) Colorful "canva" placeholder card (SVG data URI) for products without an
//    image. Gradient chosen from a palette by hashing the product name; shows
//    the first letter + DEJOIY monogram.
// -----------------------------------------------------------------------------
if ( ! function_exists( 'dejoiy_canva_placeholder_src' ) ) {
	function dejoiy_canva_placeholder_src( $seed = '' ) {
		$seed = trim( (string) $seed );
		$seed = $seed === '' ? 'DEJOIY' : $seed;

		$hash = md5( strtolower( $seed ) );
		$grad = array(
			array( '#7c3aed', '#db2777' ),
			array( '#2563eb', '#06b6d4' ),
			array( '#059669', '#22d3ee' ),
			array( '#ea580c', '#f472b6' ),
			array( '#4338ca', '#a855f7' ),
			array( '#0f766e', '#84cc16' ),
		);
		$g = $grad[ hexdec( substr( $hash, 0, 2 ) ) % count( $grad ) ];

		$initial = strtoupper( function_exists( 'mb_substr' ) ? mb_substr( $seed, 0, 1 ) : substr( $seed, 0, 1 ) );
		if ( $initial === '' ) {
			$initial = 'D';
		}

		$svg = '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 400 400">'
			. '<defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1">'
			. '<stop offset="0" stop-color="' . $g[0] . '"/><stop offset="1" stop-color="' . $g[1] . '"/></linearGradient>'
			. '<radialGradient id="r" cx="0.5" cy="0.28" r="0.85">'
			. '<stop offset="0" stop-color="#ffffff" stop-opacity="0.24"/><stop offset="1" stop-color="#ffffff" stop-opacity="0"/></radialGradient></defs>'
			. '<rect width="400" height="400" fill="url(#g)"/>'
			. '<rect width="400" height="400" fill="url(#r)"/>'
			. '<circle cx="322" cy="74" r="34" fill="#ffffff" fill-opacity="0.14"/>'
			. '<circle cx="58" cy="336" r="22" fill="#ffffff" fill-opacity="0.1"/>'
			. '<text x="200" y="238" font-family="Arial, Helvetica, sans-serif" font-size="150" font-weight="800" text-anchor="middle" dominant-baseline="central" fill="#ffffff">' . esc_html( $initial ) . '</text>'
			. '<rect x="30" y="330" width="96" height="28" rx="14" fill="#ffffff" fill-opacity="0.92"/>'
			. '<text x="78" y="348" font-family="Arial, Helvetica, sans-serif" font-size="14" font-weight="800" text-anchor="middle" fill="#0f172a">DEJOIY</text>'
			. '</svg>';

		return 'data:image/svg+xml;charset=utf-8,' . rawurlencode( $svg );
	}
}

// Generic fallback src (used by plain wc_placeholder_img_src() calls).
add_filter( 'woocommerce_placeholder_img_src', function ( $src ) {
	return dejoiy_canva_placeholder_src( 'DEJOIY' );
}, 20 );

// Per-product card when the product has no featured image.
add_filter( 'woocommerce_product_get_image', function ( $image, $product, $size, $attr ) {
	if ( ! $product instanceof WC_Product || $product->get_image_id() ) {
		return $image;
	}

	$title = $product->get_title();
	$src   = dejoiy_canva_placeholder_src( $title ? $title : 'DEJOIY' );

	$class = 'wp-post-image dejoiy-canva-card woocommerce-placeholder';
	if ( is_array( $attr ) && ! empty( $attr['class'] ) ) {
		$class .= ' ' . $attr['class'];
	}

	return sprintf(
		'<img src="%s" alt="%s" class="%s" loading="eager" decoding="async">',
		esc_url( $src ),
		esc_attr( $title ? $title : 'DEJOIY' ),
		esc_attr( $class )
	);
}, 20, 4 );

// -----------------------------------------------------------------------------
// 3) Assets — client-side backstop that swaps any remaining lazy images and
//    converts leftover placeholders / broken images to the canva card.
// -----------------------------------------------------------------------------
add_action( 'wp_enqueue_scripts', function () {
	$dir = get_stylesheet_directory();
	$uri = get_stylesheet_directory_uri();

	$css = $dir . '/dejoiy-image-fix.css';
	if ( is_readable( $css ) ) {
		wp_enqueue_style( 'dejoiy-image-fix', $uri . '/dejoiy-image-fix.css', array(), (string) filemtime( $css ) );
	}

	$js = $dir . '/dejoiy-image-fix.js';
	if ( is_readable( $js ) ) {
		wp_enqueue_script( 'dejoiy-image-fix', $uri . '/dejoiy-image-fix.js', array(), (string) filemtime( $js ), true );
	}
} );