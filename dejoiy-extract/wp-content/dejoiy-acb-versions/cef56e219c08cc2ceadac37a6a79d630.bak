<?php
/**
 * DEJOIY Homepage Intelligence — cross-ecosystem discovery sections.
 *
 * Prepends to front page content (Elementor-safe). Shortcode: [dejoiy_home_intelligence]
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Track viewed product for "Because You Viewed".
 *
 * @param int $product_id Product ID.
 */
function dejoiy_home_track_view( $product_id ) {
	$product_id = (int) $product_id;
	if ( $product_id < 1 || 'product' !== get_post_type( $product_id ) ) {
		return;
	}
	$key  = 'dejoiy_viewed';
	$list = isset( $_COOKIE[ $key ] ) ? json_decode( wp_unslash( $_COOKIE[ $key ] ), true ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	if ( ! is_array( $list ) ) {
		$list = array();
	}
	$list = array_values( array_diff( array_map( 'intval', $list ), array( $product_id ) ) );
	array_unshift( $list, $product_id );
	$list = array_slice( $list, 0, 12 );
	setcookie( $key, wp_json_encode( $list ), time() + MONTH_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
}

/**
 * @return array<int, int>
 */
function dejoiy_home_get_viewed_ids() {
	$key  = 'dejoiy_viewed';
	$list = isset( $_COOKIE[ $key ] ) ? json_decode( wp_unslash( $_COOKIE[ $key ] ), true ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	return is_array( $list ) ? array_map( 'intval', $list ) : array();
}

/**
 * Query products for a section.
 *
 * @param array<string, mixed> $args Query args.
 * @return WP_Query
 */
function dejoiy_home_query_products( $args = array() ) {
	$defaults = array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => 8,
		'orderby'        => 'date',
		'order'          => 'DESC',
	);
	return new WP_Query( array_merge( $defaults, $args ) );
}

/**
 * Render product card for intelligence grid.
 *
 * @param int $product_id Product ID.
 */
function dejoiy_home_render_card( $product_id ) {
	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		return;
	}
	$url   = function_exists( 'dejoiy_ecosystem_product_url' ) ? dejoiy_ecosystem_product_url( $product_id ) : get_permalink( $product_id );
	$cover = get_the_post_thumbnail_url( $product_id, 'woocommerce_thumbnail' );
	if ( ! $cover && function_exists( 'dejoiy_library_get_cover_url' ) ) {
		$cover = dejoiy_library_get_cover_url( $product_id, 'medium' );
	}
	$badge = function_exists( 'dejoiy_ecosystem_badge_label' ) ? dejoiy_ecosystem_badge_label( $product_id ) : '';
	$dpin  = function_exists( 'dejoiy_display_product_dpin' ) ? dejoiy_display_product_dpin( $product_id ) : '';
	?>
	<article class="dejoiy-os-card" data-product-id="<?php echo esc_attr( (string) $product_id ); ?>">
		<a href="<?php echo esc_url( $url ); ?>" class="dejoiy-os-card-link">
			<?php if ( $cover ) : ?>
				<img src="<?php echo esc_url( $cover ); ?>" alt="" class="dejoiy-os-card-img" loading="lazy" width="200" height="260" />
			<?php else : ?>
				<span class="dejoiy-os-card-placeholder" aria-hidden="true"></span>
			<?php endif; ?>
			<?php if ( $badge ) : ?>
				<span class="dejoiy-os-card-badge"><?php echo esc_html( $badge ); ?></span>
			<?php endif; ?>
			<h3 class="dejoiy-os-card-title"><?php echo esc_html( $product->get_name() ); ?></h3>
			<?php if ( $dpin ) : ?>
				<span class="dejoiy-os-card-dpin"><?php echo esc_html( $dpin ); ?></span>
			<?php endif; ?>
			<span class="dejoiy-os-card-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
		</a>
	</article>
	<?php
}

/**
 * Render one horizontal section.
 *
 * @param string               $title Section title.
 * @param array<string, mixed> $args  WP_Query args.
 */
function dejoiy_home_render_section( $title, $args ) {
	$q = dejoiy_home_query_products( $args );
	if ( ! $q->have_posts() ) {
		return;
	}
	?>
	<section class="dejoiy-os-sec" data-dejoiy-reveal>
		<div class="dejoiy-os-sec-in">
			<h2 class="dejoiy-os-sec-title"><?php echo esc_html( $title ); ?></h2>
			<div class="dejoiy-os-grid">
				<?php
				while ( $q->have_posts() ) {
					$q->the_post();
					dejoiy_home_render_card( get_the_ID() );
				}
				wp_reset_postdata();
				?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * @return string HTML.
 */
function dejoiy_home_intelligence_html() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return '';
	}

	ob_start();
	?>
	<div id="dejoiy-os-home" class="dejoiy-os-home" aria-label="<?php esc_attr_e( 'DEJOIY intelligent picks', 'dejoiy' ); ?>">
		<header class="dejoiy-os-hero">
			<p class="dejoiy-os-kicker"><?php esc_html_e( 'DEJOIY Operating System', 'dejoiy' ); ?></p>
			<h1 class="dejoiy-os-hero-title"><?php esc_html_e( 'Joy-driven commerce, one intelligent shelf', 'dejoiy' ); ?></h1>
			<p class="dejoiy-os-hero-desc"><?php esc_html_e( 'Marketplace, Nexus books, Refurbished, QuickMart, Services, and Custom Studio — curated for you.', 'dejoiy' ); ?></p>
		</header>
	<?php

	dejoiy_home_render_section(
		__( 'Trending for you', 'dejoiy' ),
		array(
			'meta_key' => 'total_sales',
			'orderby'  => 'meta_value_num',
			'order'    => 'DESC',
		)
	);

	$viewed = dejoiy_home_get_viewed_ids();
	if ( ! empty( $viewed ) ) {
		dejoiy_home_render_section(
			__( 'Because you viewed', 'dejoiy' ),
			array(
				'post__in'       => $viewed,
				'orderby'        => 'post__in',
				'posts_per_page' => 8,
			)
		);
	}

	dejoiy_home_render_section(
		__( 'Recently explored', 'dejoiy' ),
		array(
			'orderby' => 'modified',
			'order'   => 'DESC',
		)
	);

	dejoiy_home_render_section(
		__( 'Most purchased', 'dejoiy' ),
		array(
			'meta_key' => 'total_sales',
			'orderby'  => 'meta_value_num',
			'order'    => 'DESC',
			'offset'   => 4,
		)
	);

	$nexus_ids = get_posts(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 8,
			'fields'         => 'ids',
			'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'slug',
					'terms'    => array( 'dejoiy-library' ),
				),
			),
		)
	);
	if ( $nexus_ids ) {
		dejoiy_home_render_section(
			__( 'Nexus learning picks', 'dejoiy' ),
			array(
				'post__in'       => $nexus_ids,
				'orderby'        => 'post__in',
				'posts_per_page' => 8,
			)
		);
	}

	$refurb_ids = get_posts(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 8,
			'fields'         => 'ids',
			'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'slug',
					'terms'    => array( 'renewed-refurbished' ),
				),
			),
		)
	);
	if ( $refurb_ids ) {
		dejoiy_home_render_section(
			__( 'Refurbished best deals', 'dejoiy' ),
			array(
				'post__in'       => $refurb_ids,
				'orderby'        => 'rand',
				'posts_per_page' => 8,
			)
		);
	}

	dejoiy_home_render_section(
		__( 'QuickMart essentials', 'dejoiy' ),
		array(
			'tax_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'slug',
					'terms'    => array( 'quick-products' ),
				),
			),
		)
	);

	dejoiy_home_render_section(
		__( 'Custom Studio inspirations', 'dejoiy' ),
		array(
			'tax_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'slug',
					'terms'    => array( 'customized-products', 'custom-t-shirts' ),
				),
			),
		)
	);

	dejoiy_home_render_section(
		__( 'Digital highlights', 'dejoiy' ),
		array(
			'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'   => '_virtual',
					'value' => 'yes',
				),
			),
		)
	);

	?>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * @return string
 */
function dejoiy_home_intelligence_shortcode() {
	return dejoiy_home_intelligence_html();
}
add_shortcode( 'dejoiy_home_intelligence', 'dejoiy_home_intelligence_shortcode' );

/**
 * Legacy prepend removed — DEJOIY Universe home replaces the front page.
 * Shortcode [dejoiy_home_intelligence] remains for manual use only.
 */

/**
 * Track product views on single product.
 */
function dejoiy_home_track_single_product() {
	if ( is_product() ) {
		dejoiy_home_track_view( get_queried_object_id() );
	}
}
add_action( 'template_redirect', 'dejoiy_home_track_single_product', 20 );
