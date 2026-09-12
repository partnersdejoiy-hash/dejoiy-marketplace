<?php
/**
 * Child sidebar — blog widgets only on News / Announcements surfaces.
 *
 * @package Dejoiy
 */

defined( 'ABSPATH' ) || exit;

if ( function_exists( 'dejoiy_amazon_compete_is_updates_surface' ) && ! dejoiy_amazon_compete_is_updates_surface() ) {
	return;
}

$sidebar = get_query_var( 'et_sidebar', 'left' );
if ( ! $sidebar || in_array( $sidebar, array( 'without', 'no_sidebar' ), true ) ) {
	return;
}

require get_template_directory() . '/sidebar.php';
