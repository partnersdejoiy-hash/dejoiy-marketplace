<?php
/**
 * Dedicated Marketplace Home template.
 *
 * Renders only the Marketplace Home content between the standard theme
 * chrome (Global Header OS owns the header via wp_body_open, get_footer()
 * owns the footer). This replaces the old three-way the_content filter
 * cascade (universe @9999 -> mobile-home-v2 @10000 -> marketplace home
 * @10001) which used to render the legacy Elementor page content and the
 * old footer a second time, nested inside the MPH layout.
 *
 * @package dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( function_exists( 'dejoiy_marketplace_home_html' ) ) {
	echo dejoiy_marketplace_home_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- intentional HTML module output.
} else {
	the_content(); // Fallback only if the module is unavailable.
}

get_footer();