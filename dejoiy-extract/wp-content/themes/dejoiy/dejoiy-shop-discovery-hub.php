<?php
/**
 * DEJOIY Shop — Unified Discovery Hub (/shop only).
 *
 * Additive layer on WooCommerce shop archive. Does not replace templates or checkout.
 * Disable: define( 'DEJOIY_SHOP_DISCOVERY_DISABLED', true );
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var string */
define( 'DEJOIY_SHOP_DISCOVERY_VERSION', '1.1.1' );

/**
 * @return bool
 */
function dejoiy_shop_discovery_hub_enabled() {
	if ( defined( 'DEJOIY_SHOP_DISCOVERY_DISABLED' ) && DEJOIY_SHOP_DISCOVERY_DISABLED ) {
		return false;
	}
	if ( ! function_exists( 'dejoiy_evolution_is_enabled' ) || ! dejoiy_evolution_is_enabled() ) {
		return false;
	}
	return (bool) apply_filters( 'dejoiy_shop_discovery_hub_enabled', true );
}

/**
 * Unified marketplace catalog (used by library-shop-exclusion).
 *
 * @return bool
 */
function dejoiy_shop_is_unified_marketplace_catalog() {
	if ( ! did_action( 'wp' ) ) {
		return false;
	}
	if ( function_exists( 'is_shop' ) && is_shop() ) {
		return true;
	}
	return false;
}

/**
 * Full discovery hub UI (banner, legend, filters) — shop page only.
 *
 * @return bool
 */
function dejoiy_shop_discovery_hub_is_shop_page() {
	if ( ! dejoiy_shop_discovery_hub_enabled() || ! did_action( 'wp' ) ) {
		return false;
	}
	return function_exists( 'is_shop' ) && is_shop() && ! is_search();
}

/**
 * Product card enhancements on shop.
 *
 * @return bool
 */
function dejoiy_shop_discovery_card_enhancements_active() {
	return dejoiy_shop_discovery_hub_is_shop_page();
}

/**
 * @return string
 */
function dejoiy_shop_discovery_shop_url() {
	return function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
}

/**
 * Ecosystem definitions for legend + filters.
 *
 * @return array<string, array<string, string>>
 */
function dejoiy_shop_discovery_ecosystems() {
	$ecos = array(
		'marketplace' => array(
			'key'         => 'marketplace',
			'icon'        => '◆',
			'label'       => __( 'Marketplace', 'dejoiy' ),
			'description' => __( 'Physical and digital marketplace products', 'dejoiy' ),
			'filter'      => __( 'Marketplace', 'dejoiy' ),
		),
		'studio'      => array(
			'key'         => 'studio',
			'icon'        => '✿',
			'label'       => __( 'Studio', 'dejoiy' ),
			'description' => __( 'Custom-made and personalized products', 'dejoiy' ),
			'filter'      => __( 'Studio', 'dejoiy' ),
		),
		'nexus'       => array(
			'key'         => 'nexus',
			'icon'        => '✦',
			'label'       => __( 'Nexus', 'dejoiy' ),
			'description' => __( 'Books, learning resources and educational content', 'dejoiy' ),
			'filter'      => __( 'Nexus', 'dejoiy' ),
		),
		'services'    => array(
			'key'         => 'services',
			'icon'        => '◎',
			'label'       => __( 'Services', 'dejoiy' ),
			'description' => __( 'Professional and business services', 'dejoiy' ),
			'filter'      => __( 'Services', 'dejoiy' ),
		),
		'quick'       => array(
			'key'         => 'quick',
			'icon'        => '⚡',
			'label'       => __( 'Quick', 'dejoiy' ),
			'description' => __( 'Fast delivery essentials', 'dejoiy' ),
			'filter'      => __( 'Quick', 'dejoiy' ),
			'coming_soon' => '1',
		),
	);
	return apply_filters( 'dejoiy_shop_discovery_ecosystems', $ecos );
}

/**
 * Active ecosystem filter from query string.
 *
 * @return string all|marketplace|studio|nexus|services|quick
 */
function dejoiy_shop_discovery_active_eco_filter() {
	$raw = isset( $_GET['dejoiy_eco'] ) ? sanitize_key( wp_unslash( $_GET['dejoiy_eco'] ) ) : 'all'; // phpcs:ignore
	$valid = array( 'all', 'marketplace', 'studio', 'nexus', 'services', 'quick' );
	return in_array( $raw, $valid, true ) ? $raw : 'all';
}

/**
 * Map filter key to stored ecosystem meta value.
 *
 * @param string $filter_key Filter key.
 * @return string
 */
function dejoiy_shop_discovery_eco_meta_value( $filter_key ) {
	$map = array(
		'quick' => 'quickmart',
	);
	return isset( $map[ $filter_key ] ) ? $map[ $filter_key ] : $filter_key;
}

/**
 * Category slugs for an ecosystem.
 *
 * @param string $eco_meta Ecosystem meta value.
 * @return array<int, string>
 */
function dejoiy_shop_discovery_eco_category_slugs( $eco_meta ) {
	if ( ! function_exists( 'dejoiy_ecosystem_category_map' ) ) {
		return array();
	}
	$slugs = array();
	foreach ( dejoiy_ecosystem_category_map() as $slug => $eco ) {
		if ( $eco === $eco_meta ) {
			$slugs[] = $slug;
		}
	}
	return $slugs;
}

/**
 * Short badge HTML for a product card.
 *
 * @param int $product_id Product ID.
 * @return string
 */
function dejoiy_shop_discovery_product_badge_html( $product_id ) {
	if ( ! function_exists( 'dejoiy_get_product_ecosystem' ) ) {
		return '';
	}
	$eco  = dejoiy_get_product_ecosystem( $product_id );
	$defs = dejoiy_shop_discovery_ecosystems();
	$map  = array(
		'marketplace' => 'marketplace',
		'studio'      => 'studio',
		'nexus'       => 'nexus',
		'services'    => 'services',
		'quickmart'   => 'quick',
		'refurbished' => 'quick',
		'business'    => 'marketplace',
	);
	$key = isset( $map[ $eco ] ) ? $map[ $eco ] : 'marketplace';
	if ( ! isset( $defs[ $key ] ) ) {
		return '';
	}
	$d = $defs[ $key ];
	return sprintf(
		'<span class="dsh-eco-badge dsh-eco-badge--%1$s" data-eco="%1$s"><span class="dsh-eco-badge__icon" aria-hidden="true">%2$s</span><span class="dsh-eco-badge__text">%3$s</span></span>',
		esc_attr( $key ),
		esc_html( $d['icon'] ),
		esc_html( $d['label'] )
	);
}

/**
 * Filter shop main query by ecosystem.
 *
 * @param WP_Query $query Query.
 */
function dejoiy_shop_discovery_filter_shop_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! dejoiy_shop_discovery_hub_is_shop_page() ) {
		return;
	}
	$eco = dejoiy_shop_discovery_active_eco_filter();
	if ( 'all' === $eco || 'quick' === $eco ) {
		return;
	}

	$meta_eco = dejoiy_shop_discovery_eco_meta_value( $eco );
	$slugs    = dejoiy_shop_discovery_eco_category_slugs( $meta_eco );

	if ( 'marketplace' === $eco ) {
		$query->set(
			'meta_query',
			array(
				'relation' => 'OR',
				array(
					'key'     => '_dejoiy_ecosystem',
					'value'   => 'marketplace',
					'compare' => '=',
				),
				array(
					'key'     => '_dejoiy_ecosystem',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => '_dejoiy_ecosystem',
					'value'   => '',
					'compare' => '=',
				),
			)
		);
		return;
	}

	if ( ! empty( $slugs ) ) {
		$query->set(
			'tax_query',
			array(
				array(
					'taxonomy'         => 'product_cat',
					'field'            => 'slug',
					'terms'            => $slugs,
					'operator'         => 'IN',
					'include_children' => true,
				),
			)
		);
		return;
	}

	$query->set(
		'meta_query',
		array(
			array(
				'key'     => '_dejoiy_ecosystem',
				'value'   => $meta_eco,
				'compare' => '=',
			),
		)
	);
}
add_action( 'pre_get_posts', 'dejoiy_shop_discovery_filter_shop_query', 32 );

/**
 * Product loop links → dedicated ecosystem experiences.
 *
 * @param string     $link    Link.
 * @param WC_Product $product Product.
 * @return string
 */
function dejoiy_shop_discovery_loop_product_link( $link, $product ) {
	if ( ! dejoiy_shop_discovery_card_enhancements_active() || ! $product ) {
		return $link;
	}
	if ( function_exists( 'dejoiy_ecosystem_product_url' ) ) {
		$url = dejoiy_ecosystem_product_url( $product->get_id() );
		if ( $url ) {
			return $url;
		}
	}
	return $link;
}
add_filter( 'woocommerce_loop_product_link', 'dejoiy_shop_discovery_loop_product_link', 20, 2 );

/**
 * Ecosystem badge on product image area.
 */
function dejoiy_shop_discovery_loop_eco_badge() {
	global $product;
	if ( ! $product || ! dejoiy_shop_discovery_card_enhancements_active() ) {
		return;
	}
	$html = dejoiy_shop_discovery_product_badge_html( $product->get_id() );
	if ( $html ) {
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
add_action( 'woocommerce_before_shop_loop_item_title', 'dejoiy_shop_discovery_loop_eco_badge', 6 );

/**
 * DPIN + seller below title/price block.
 */
function dejoiy_shop_discovery_loop_card_meta() {
	global $product;
	if ( ! $product || ! dejoiy_shop_discovery_card_enhancements_active() ) {
		return;
	}
	$product_id = $product->get_id();
	$dpin       = function_exists( 'dejoiy_display_product_dpin' ) ? dejoiy_display_product_dpin( $product_id ) : '';
	$seller     = function_exists( 'dejoiy_universe_seller_label' ) ? dejoiy_universe_seller_label( $product_id ) : __( 'Verified seller', 'dejoiy' );
	?>
	<div class="dsh-card__meta">
		<?php if ( $dpin ) : ?>
			<span class="dsh-card__dpin" title="<?php esc_attr_e( 'DEJOIY Product ID', 'dejoiy' ); ?>"><?php echo esc_html( $dpin ); ?></span>
		<?php endif; ?>
		<span class="dsh-card__seller">
			<span class="dsh-card__seller-dot" aria-hidden="true"></span>
			<?php echo esc_html( $seller ); ?>
		</span>
	</div>
	<?php
}
add_action( 'woocommerce_after_shop_loop_item_title', 'dejoiy_shop_discovery_loop_card_meta', 12 );

/**
 * Open card wrapper.
 */
function dejoiy_shop_discovery_loop_open_wrapper() {
	if ( ! dejoiy_shop_discovery_card_enhancements_active() ) {
		return;
	}
	echo '<div class="dsh-product-card">';
}
add_action( 'woocommerce_before_shop_loop_item', 'dejoiy_shop_discovery_loop_open_wrapper', 2 );

/**
 * Close card wrapper.
 */
function dejoiy_shop_discovery_loop_close_wrapper() {
	if ( ! dejoiy_shop_discovery_card_enhancements_active() ) {
		return;
	}
	echo '</div>';
}
add_action( 'woocommerce_after_shop_loop_item', 'dejoiy_shop_discovery_loop_close_wrapper', 98 );

/**
 * Active DEJOIY shop category filter (?filter_cat=slug).
 *
 * @return string Empty when showing all.
 */
function dejoiy_shop_discovery_active_filter_cat() {
	if ( empty( $_GET['filter_cat'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return '';
	}
	$raw   = sanitize_text_field( wp_unslash( (string) $_GET['filter_cat'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$parts = array_filter( array_map( 'sanitize_title', explode( ',', $raw ) ) );
	return ! empty( $parts ) ? (string) $parts[0] : '';
}

/**
 * Shop URL with optional DEJOIY category filter (preserves ecosystem filter).
 *
 * @param string $cat_slug Category slug or empty for all.
 * @return string
 */
function dejoiy_shop_discovery_category_url( $cat_slug = '' ) {
	$args = array();
	if ( '' !== $cat_slug ) {
		$args['filter_cat'] = $cat_slug;
	}
	$eco = dejoiy_shop_discovery_active_eco_filter();
	if ( 'all' !== $eco ) {
		$args['dejoiy_eco'] = $eco;
	}
	return add_query_arg( $args, dejoiy_shop_discovery_shop_url() );
}

/**
 * Product categories for horizontal shop rail.
 *
 * @return array<int, array<string, string>>
 */
function dejoiy_shop_discovery_shop_categories() {
	$items = array();
	$skip  = array( 'uncategorized', 'uncategorised' );

	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return $items;
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'orderby'    => 'name',
			'order'      => 'ASC',
			'number'     => 0,
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return $items;
	}

	foreach ( $terms as $term ) {
		if ( in_array( $term->slug, $skip, true ) ) {
			continue;
		}
		$icon_fn = function_exists( 'dejoiy_mobile_os_category_icon' ) ? 'dejoiy_mobile_os_category_icon' : null;
		$icon    = $icon_fn ? call_user_func( $icon_fn, $term->slug, $term->name ) : '◆';
		$thumb   = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
		$items[] = array(
			'slug'  => $term->slug,
			'label' => $term->name,
			'url'   => dejoiy_shop_discovery_category_url( $term->slug ),
			'icon'  => $icon,
			'img'   => $thumb ? (string) wp_get_attachment_image_url( $thumb, 'woocommerce_thumbnail' ) : '',
		);
	}

	return apply_filters( 'dejoiy_shop_discovery_shop_categories', $items );
}

/**
 * Render shop hub: category scroll + slot for loop controls.
 */
function dejoiy_shop_discovery_render_hub() {
	static $rendered = false;
	if ( $rendered || ! dejoiy_shop_discovery_hub_is_shop_page() ) {
		return;
	}
	$rendered = true;

	?>
	<div class="dsh-hub" id="dejoiy-shop-discovery-hub">
		<div class="dsh-loop-controls" id="dsh-loop-controls-slot" aria-label="<?php esc_attr_e( 'Product display options', 'dejoiy' ); ?>"></div>
	</div>
	<?php
}
add_action( 'woocommerce_before_shop_loop', 'dejoiy_shop_discovery_render_hub', 1 );
add_action( 'woocommerce_before_main_content', 'dejoiy_shop_discovery_render_hub', 6 );

/**
 * Wrap native shop loop for grid styling.
 */
function dejoiy_shop_discovery_open_products_wrap() {
	if ( ! dejoiy_shop_discovery_hub_is_shop_page() ) {
		return;
	}
	echo '<div class="dsh-catalog">';
}
add_action( 'woocommerce_before_shop_loop', 'dejoiy_shop_discovery_open_products_wrap', 5 );

/**
 * Close products wrap.
 */
function dejoiy_shop_discovery_close_products_wrap() {
	if ( ! dejoiy_shop_discovery_hub_is_shop_page() ) {
		return;
	}
	echo '</div>';
}
add_action( 'woocommerce_after_shop_loop', 'dejoiy_shop_discovery_close_products_wrap', 99 );

/**
 * Body class + shell scope.
 *
 * @param array<int, string> $classes Classes.
 * @return array<int, string>
 */
function dejoiy_shop_discovery_body_class( $classes ) {
	if ( dejoiy_shop_discovery_hub_is_shop_page() ) {
		$classes[] = 'dejoiy-shop-discovery-hub';
		$classes[] = 'dejoiy-os-shell';
	}
	return $classes;
}
add_filter( 'body_class', 'dejoiy_shop_discovery_body_class' );

/**
 * Enqueue hub assets.
 */
function dejoiy_shop_discovery_enqueue_assets() {
	if ( ! dejoiy_shop_discovery_hub_is_shop_page() ) {
		return;
	}
	$dir = get_stylesheet_directory();
	$uri = get_stylesheet_directory_uri();
	$css = $dir . '/dejoiy-shop-discovery-hub.css';
	$js  = $dir . '/dejoiy-shop-discovery-hub.js';
	$deps = array();
	if ( wp_style_is( 'dejoiy-os-design-system', 'registered' ) ) {
		$deps[] = 'dejoiy-os-design-system';
	}
	if ( file_exists( $css ) ) {
		wp_enqueue_style(
			'dejoiy-shop-discovery-hub',
			$uri . '/dejoiy-shop-discovery-hub.css',
			$deps,
			(string) filemtime( $css )
		);
	}
	if ( file_exists( $js ) ) {
		wp_enqueue_script(
			'dejoiy-shop-discovery-hub',
			$uri . '/dejoiy-shop-discovery-hub.js',
			array(),
			(string) filemtime( $js ),
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'dejoiy_shop_discovery_enqueue_assets', 1010 );

/**
 * Remove Social Links widget from shop filter sidebars.
 *
 * @param array<string, array<int, string>> $sidebars_widgets Widgets map.
 * @return array<string, array<int, string>>
 */
function dejoiy_shop_discovery_strip_social_widgets( $sidebars_widgets ) {
	if ( ! dejoiy_shop_discovery_hub_is_shop_page() || ! is_array( $sidebars_widgets ) ) {
		return $sidebars_widgets;
	}

	foreach ( $sidebars_widgets as $sidebar => $widgets ) {
		if ( ! is_array( $widgets ) ) {
			continue;
		}
		foreach ( $widgets as $idx => $widget_id ) {
			if ( ! is_string( $widget_id ) ) {
				continue;
			}
			if (
				false !== strpos( $widget_id, 'etheme_widget_socials' )
				|| false !== strpos( $widget_id, 'etheme-socials' )
			) {
				unset( $sidebars_widgets[ $sidebar ][ $idx ] );
			}
		}
		$sidebars_widgets[ $sidebar ] = array_values( $sidebars_widgets[ $sidebar ] );
	}

	return $sidebars_widgets;
}
add_filter( 'sidebars_widgets', 'dejoiy_shop_discovery_strip_social_widgets', 99 );
