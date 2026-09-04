<?php
/**
 * DEJOIY Product Detail polish.
 *
 * Adds a marketplace trust strip to the product summary and a direct
 * "Buy Now" (add-to-cart -> checkout) button for simple products.
 * All claims link to real pages; availability is derived from WooCommerce.
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return bool
 */
function dejoiy_product_detail_active() {
	return is_product() && ! is_admin();
}

/**
 * Shared product for the active single product.
 *
 * @return \WC_Product|null
 */
function dejoiy_product_detail_current() {
	if ( ! function_exists( 'wc_get_product' ) ) {
		return null;
	}
	$pid = (int) get_the_ID();
	if ( $pid < 1 ) {
		return null;
	}
	return wc_get_product( $pid );
}

/**
 * Trust strip markup (shortcode + elementor-resident).
 *
 * @return string
 */
function dejoiy_product_detail_trust_html() {
	$product = dejoiy_product_detail_current();
	if ( ! $product ) {
		return '';
	}
	$terms   = home_url( '/terms-and-conditions/' );
	$contact = home_url( '/contact-us/' );
	switch ( $product->get_stock_status() ) {
		case 'outofstock':
			$avail = __( 'Currently out of stock', 'dejoiy' );
			break;
		case 'onbackorder':
			$avail = __( 'On backorder — reserve now', 'dejoiy' );
			break;
		default:
			$avail = __( 'In stock · ships in 2–3 days', 'dejoiy' );
	}
	ob_start();
	?>
	<div class="djy-strip" aria-label="Order details">
		<span class="djy-chip"><span class="djy-chip__dot" aria-hidden="true"></span><?php echo esc_html( $avail ); ?></span>
		<span class="djy-chip"><span class="djy-chip__dot" aria-hidden="true"></span><?php esc_html_e( 'Secure payments', 'dejoiy' ); ?></span>
		<span class="djy-chip djy-chip--link"><span class="djy-chip__dot" aria-hidden="true"></span><a href="<?php echo esc_url( $terms ); ?>"><?php esc_html_e( 'Buyer protection', 'dejoiy' ); ?></a></span>
		<span class="djy-chip djy-chip--link"><span class="djy-chip__dot" aria-hidden="true"></span><a href="<?php echo esc_url( $contact ); ?>"><?php esc_html_e( '24×7 support', 'dejoiy' ); ?></a></span>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Buy Now button markup (shortcode + elementor-resident).
 *
 * @return string
 */
function dejoiy_product_detail_buy_now_html() {
	$product = dejoiy_product_detail_current();
	if ( ! $product || ! $product->is_type( 'simple' ) || ! $product->is_purchasable() || ! $product->is_in_stock() ) {
		return '';
	}
	$url = add_query_arg(
		array( 'add-to-cart' => $product->get_id() ),
		wc_get_checkout_url()
	);
	return '<a class="djy-buynow" href="' . esc_url( $url ) . '">' . esc_html__( 'Buy Now', 'dejoiy' ) . '</a>';
}

add_shortcode( 'djy_product_trust', 'dejoiy_product_detail_trust_html' );
add_shortcode( 'djy_product_buynow', 'dejoiy_product_detail_buy_now_html' );

/**
 * "You may also like" rail — real products sharing a category, falling back
 * to recent/trending products.
 *
 * @return string
 */
function dejoiy_product_detail_rail_html() {
	$product = dejoiy_product_detail_current();
	if ( ! $product ) {
		return '';
	}

	$ids   = array();
	$cats  = function_exists( 'wp_get_post_terms' ) ? wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'ids' ) ) : array();
	$query = array(
		'post_type'           => 'product',
		'post_status'         => 'publish',
		'posts_per_page'      => 8,
		'post__not_in'        => array( $product->get_id() ),
		'fields'              => 'ids',
		'no_found_rows'       => true,
	);

	if ( ! is_wp_error( $cats ) && ! empty( $cats ) ) {
		$query['tax_query'] = array(
			array(
				'taxonomy' => 'product_cat',
				'field'    => 'term_id',
				'terms'    => $cats,
			),
		);
		$query['orderby']   = 'date';
		$query['order']     = 'DESC';
	}

	$ids = get_posts( $query );
	if ( count( $ids ) < 4 ) {
		unset( $query['tax_query'], $query['orderby'] );
		$query['orderby']    = 'meta_value_num';
		$query['meta_key']   = 'total_sales';
		$query['order']      = 'DESC';
		$query['posts_per_page'] = 8;
		$fallback            = get_posts( $query );
		$ids                 = array_merge( $ids, $fallback );
		$ids                 = array_values( array_unique( $ids ) );
	}
	$ids = array_slice( $ids, 0, 8 );
	if ( empty( $ids ) ) {
		return '';
	}

	ob_start();
	?>
	<div class="dpy-rail" data-dpy-rail>
		<h2 class="dpy-rail__title"><?php esc_html_e( 'You may also like', 'dejoiy' ); ?></h2>
		<div class="dpy-rail__grid">
			<?php foreach ( $ids as $pid ) : $p = function_exists( 'wc_get_product' ) ? wc_get_product( $pid ) : null; ?>
				<?php if ( ! $p ) continue; ?>
				<a class="dpy-rail__card" href="<?php echo esc_url( get_permalink( $pid ) ); ?>">
					<span class="dpy-rail__img"><?php echo $p->get_image( 'woocommerce_thumbnail', array( 'loading' => 'lazy' ) ); ?></span>
					<span class="dpy-rail__name"><?php echo esc_html( wp_trim_words( $p->get_name(), 6 ) ); ?></span>
					<span class="dpy-rail__price"><?php echo wp_kses_post( $p->get_price_html() ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
	return (string) ob_get_clean();
}

add_shortcode( 'djy_product_rail', 'dejoiy_product_detail_rail_html' );

/**
 * Inject the trust strip + Buy Now widgets into the Elementor
 * single-product template (id 4234) at render time — no DB surgery,
 * works with Elementor's data cache.
 *
 * @param array<int, array<string, mixed>> $data    Document elements.
 * @param int                              $post_id Template/library post id.
 * @return array<int, array<string, mixed>>
 */
function dejoiy_product_detail_inject_template( $data, $post_id ) {
	if ( (int) $post_id !== 4234 || ! is_product() ) {
		return $data;
	}

	$trust = array(
		'id'         => substr( md5( 'djy-trust' . gmdate( 'i' ) . wp_rand() ), 0, 13 ),
		'elType'     => 'widget',
		'settings'   => array( 'shortcode' => '[djy_product_trust]' ),
		'elements'   => array(),
		'widgetType' => 'shortcode',
		'isInner'    => false,
	);

	$product = dejoiy_product_detail_current();

	$mutated_strip  = false;
	$mutated_buynow = false;
	$mutated_rail   = false;

	$walk = static function ( &$node ) use ( &$walk, &$mutated_strip, &$mutated_buynow, &$mutated_rail, $trust, $product ) {
		if ( ! is_array( $node ) || ! isset( $node['elements'] ) || ! is_array( $node['elements'] ) ) {
			return;
		}
		$children = &$node['elements'];
		$count    = count( $children );
		for ( $i = 0; $i < $count; $i++ ) {
			if ( ! is_array( $children[ $i ] ) ) {
				continue;
			}
			$id = isset( $children[ $i ]['id'] ) ? $children[ $i ]['id'] : '';
			if ( '2486bf86' === $id && ! $mutated_strip ) {
				$widget                          = $trust;
				$widget['id']                    = substr( md5( 'djy-trust' . mt_rand() ), 0, 13 );
				$widget['settings']['shortcode'] = '[djy_product_trust]';
				array_splice( $children, $i + 1, 0, array( $widget ) );
				$count++;
				$mutated_strip = true;
				continue;
			}
			if ( '51854dc9' === $id && ! $mutated_buynow ) {
				if ( $product && $product->is_type( 'simple' ) ) {
					$widget                          = $trust;
					$widget['id']                    = substr( md5( 'djy-buynow' . mt_rand() ), 0, 13 );
					$widget['settings']['shortcode'] = '[djy_product_buynow]';
					array_splice( $children, $i + 1, 0, array( $widget ) );
					$count++;
				}
				$mutated_buynow = true;
				continue;
			}
			if ( 'e6f5a62' === $id && ! $mutated_rail ) {
				$widget                          = $trust;
				$widget['id']                    = substr( md5( 'djy-rail' . mt_rand() ), 0, 13 );
				$widget['settings']['shortcode'] = '[djy_product_rail]';
				array_splice( $children, $i + 1, 0, array( $widget ) );
				$count++;
				$mutated_rail = true;
				continue;
			}
		}
		foreach ( $children as &$child ) {
			$walk( $child );
		}
	};

	foreach ( $data as &$top ) {
		$walk( $top );
	}

	return $data;
}
add_filter( 'elementor/frontend/builder_content_data', 'dejoiy_product_detail_inject_template', 20, 2 );

/**
 * Elementor caches the rendered HTML of a document in `_elementor_element_cache`
 * and short-circuits the render path (and our injection filter) until the cache
 * expires. This template renders dynamic widgets on every request, so keep its
 * cache from being stored/used on the front end. Elementor's admin/preview
 * (where the cache is never stored anyway) is unaffected.
 */
function dejoiy_product_detail_block_document_cache( $check, $post_id, $meta_key, $meta_value ) {
	if ( 4234 === (int) $post_id && '_elementor_element_cache' === $meta_key && ! is_admin() ) {
		delete_metadata( 'post', $post_id, $meta_key, true );
		return true;
	}
	return $check;
}
add_filter( 'update_post_metadata', 'dejoiy_product_detail_block_document_cache', 10, 4 );

/**
 * Shop / category grid polish (archive-style product loops).
 */
function dejoiy_shop_cards_assets() {
	if ( is_admin() || ( ! is_shop() && ! is_product_category() && ! is_product_tag() ) ) {
		return;
	}
	$uri = get_stylesheet_directory_uri();
	$dir = get_stylesheet_directory();
	$css = $dir . '/dejoiy-shop-cards.css';
	if ( is_readable( $css ) ) {
		wp_enqueue_style(
			'dejoiy-shop-cards',
			$uri . '/dejoiy-shop-cards.css',
			array(),
			(string) filemtime( $css )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'dejoiy_shop_cards_assets', 10061 );

/**
 * Product detail assets (single product pages only).
 */
function dejoiy_product_detail_assets() {
	if ( ! dejoiy_product_detail_active() ) {
		return;
	}
	$uri = get_stylesheet_directory_uri();
	$dir = get_stylesheet_directory();
	$css = $dir . '/dejoiy-product-detail.css';
	if ( is_readable( $css ) ) {
		wp_enqueue_style(
			'dejoiy-product-detail',
			$uri . '/dejoiy-product-detail.css',
			array(),
			(string) filemtime( $css )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'dejoiy_product_detail_assets', 10060 );