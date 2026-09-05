<?php
/**
 * DEJOIY Policy Pages — card-based presentation for legal pages.
 *
 * Wraps plain Gutenberg policy copy (h2 sections + paragraphs) into a
 * premium card grid so legal pages feel structured, not like a wall of text.
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Policy page IDs that use the card presentation.
 *
 * @return int[]
 */
function dejoiy_policy_pages_ids() {
	return array( 3, 5453 ); // privacy-policy, returns-and-refunds.
}

/**
 * Is the current request a policy page?
 *
 * @return bool
 */
function dejoiy_is_policy_page() {
	if ( ! is_page() || is_admin() ) {
		return false;
	}
	$id = (int) get_queried_object_id();
	return in_array( $id, dejoiy_policy_pages_ids(), true );
}

/**
 * Turn a flat sequence of <h2> + <p> blocks into card sections.
 *
 * @param string $content Filtered page content (Gutenberg/wptexturize/wpautop HTML).
 * @return string
 */
function dejoiy_policy_cardify( $content ) {
	$pos = strpos( $content, '<h2' );
	if ( $pos === false ) {
		return $content;
	}

	$lead = trim( substr( $content, 0, $pos ) );
	$tail = substr( $content, $pos );

	$parts = preg_split( '/(<h2\b[^>]*>.+?<\/h2>)/is', $tail, -1, PREG_SPLIT_DELIM_CAPTURE );
	if ( ! is_array( $parts ) || count( $parts ) < 3 ) {
		return $content;
	}

	$cards = '';
	$idx   = 0;
	$count = count( $parts );
	for ( $i = 1; $i < $count; $i += 2 ) {
		$idx++;
		$heading = $parts[ $i ];
		$body    = isset( $parts[ $i + 1 ] ) ? $parts[ $i + 1 ] : '';

		$body = trim( preg_replace( '/<!--\s*\/?wp:[^>]*-->/', '', $body ) );
		if ( '' === $body ) {
			continue;
		}

		$cards .= '<section class="dpol-card">'
			. '<span class="dpol-num" aria-hidden="true">' . str_pad( (string) $idx, 2, '0', STR_PAD_LEFT ) . '</span>'
			. '<div class="dpol-card-body">'
			. $heading
			. $body
			. '</div>'
			. '</section>';
	}

	if ( '' === $cards ) {
		return $content;
	}

	$lead_html = '';
	if ( '' !== $lead ) {
		$lead      = trim( preg_replace( '/<!--\s*\/?wp:[^>]*-->/', '', $lead ) );
		$lead_html = '<div class="dpol-lead">' . $lead . '</div>';
	}

	return $lead_html . '<div class="dpol-cards">' . $cards . '</div>';
}

/**
 * @param string $content Existing content.
 * @return string
 */
function dejoiy_policy_cardify_content( $content ) {
	if ( is_admin() || ! in_the_loop() || ! is_main_query() || ! dejoiy_is_policy_page() ) {
		return $content;
	}
	return dejoiy_policy_cardify( (string) $content );
}
add_filter( 'the_content', 'dejoiy_policy_cardify_content', 20 );

/**
 * Enqueue card presentation assets on policy pages only.
 */
function dejoiy_policy_pages_assets() {
	if ( ! dejoiy_is_policy_page() ) {
		return;
	}
	$dir = get_stylesheet_directory();
	$uri = get_stylesheet_directory_uri();
	$css = $dir . '/dejoiy-policy-pages.css';
	if ( is_readable( $css ) ) {
		wp_enqueue_style(
			'dejoiy-policy-pages',
			$uri . '/dejoiy-policy-pages.css',
			array(),
			(string) filemtime( $css )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'dejoiy_policy_pages_assets', 100 );
