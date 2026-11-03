<?php
/**
 * DEJOIY brand kit (mu-plugin).
 *
 * Serves the :Dejoiy Marketplace lockup wherever the theme asks for the
 * Customizer logo, and the :D app icon wherever it asks for the site icon,
 * so every header/footer picks up the new logo without DB changes.
 * Files: wp-content/themes/dejoiy/assets/brand/
 */
if (!defined('ABSPATH')) exit;

function dejoiy_brand_url($file) {
    return content_url('themes/dejoiy/assets/brand/' . $file);
}

/** Customizer logo attachment -> marketplace lockup PNG (1850x240; PNG so it also works in emails). */
add_filter('wp_get_attachment_image_src', function ($image, $attachment_id) {
    $logo_id = (int) get_theme_mod('custom_logo');
    if ($logo_id > 0 && (int) $attachment_id === $logo_id) {
        return array(dejoiy_brand_url('dejoiy-marketplace.png'), 1850, 240, false);
    }
    return $image;
}, 20, 2);

/** No srcset for the logo (it would point at the old PNG renditions). */
add_filter('wp_calculate_image_srcset', function ($sources, $size_array, $image_src, $image_meta, $attachment_id) {
    $logo_id = (int) get_theme_mod('custom_logo');
    return ($logo_id > 0 && (int) $attachment_id === $logo_id) ? false : $sources;
}, 20, 5);

/** Site icon -> :D app icon at the closest size. */
add_filter('get_site_icon_url', function ($url, $size) {
    $size = (int) $size;
    if ($size <= 32) return dejoiy_brand_url('favicon-dejoiy-32.png');
    if ($size <= 180) return dejoiy_brand_url('icon-dejoiy-180.png');
    if ($size <= 192) return dejoiy_brand_url('icon-dejoiy-192.png');
    return dejoiy_brand_url('icon-dejoiy-512.png');
}, 20, 2);

add_filter('site_icon_meta_tags', function () {
    return array(
        '<link rel="icon" href="' . esc_url(dejoiy_brand_url('favicon-dejoiy.svg')) . '" type="image/svg+xml" />',
        '<link rel="icon" href="' . esc_url(dejoiy_brand_url('favicon-dejoiy-32.png')) . '" sizes="32x32" type="image/png" />',
        '<link rel="apple-touch-icon" href="' . esc_url(dejoiy_brand_url('icon-dejoiy-180.png')) . '" />',
        '<meta name="msapplication-TileImage" content="' . esc_url(dejoiy_brand_url('icon-dejoiy-512.png')) . '" />',
    );
}, 20);

/** SuperPWA manifest icons -> 192/512 app icons. */
add_filter('superpwa_manifest', function ($manifest) {
    if (is_array($manifest)) {
        $manifest['icons'] = array(
            array('src' => dejoiy_brand_url('icon-dejoiy-192.png'), 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'),
            array('src' => dejoiy_brand_url('icon-dejoiy-512.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'),
        );
    }
    return $manifest;
}, 20);
