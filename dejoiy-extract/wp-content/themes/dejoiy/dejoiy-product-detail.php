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
	global $product;
	if ( is_a( $product, 'WC_Product' ) ) {
		return $product;
	}
	if ( ! function_exists( 'wc_get_product' ) ) {
		return null;
	}
	$pid = (int) get_the_ID();
	if ( $pid < 1 ) {
		$pid = (int) get_queried_object_id();
	}
	if ( $pid < 1 ) {
		return null;
	}
	return wc_get_product( $pid );
}

/**
 * Calculate dynamic delivery promise based on 4:00 PM IST dispatch cut-off
 */
function dejoiy_get_delivery_promise() {
	try {
		$tz = new DateTimeZone('Asia/Kolkata');
		$now = new DateTime('now', $tz);
		$cutoff = clone $now;
		$cutoff->setTime(16, 0, 0);

		$days = 3;
		if ($now > $cutoff) {
			$days++;
			$next_cutoff = clone $cutoff;
			$next_cutoff->modify('+1 day');
			$diff = $now->diff($next_cutoff);
		} else {
			$diff = $now->diff($cutoff);
		}

		$delivery = clone $now;
		$delivery->modify("+{$days} days");
		if ((int) $delivery->format('N') === 7) {
			$delivery->modify('+1 day');
		}

		$h = (int) $diff->h;
		$m = (int) $diff->i;
		$s = (int) $diff->s;

		return array(
			'date_formatted' => $delivery->format('l, M j'),
			'hours_left'     => $h,
			'mins_left'      => $m,
			'total_seconds'  => ($h * 3600) + ($m * 60) + $s,
		);
	} catch (\Exception $e) {
		return array(
			'date_formatted' => '3–4 business days',
			'hours_left'     => 4,
			'mins_left'      => 0,
			'total_seconds'  => 14400,
		);
	}
}

/**
 * Safely retrieve seller info for the current product
 */
function dejoiy_get_pdp_seller_info($product) {
	if (!$product) {
		return array('name' => 'DEJOIY Marketplace', 'url' => home_url('/shop/'));
	}
	$product_id = $product->get_id();
	$vendor_id = 0;
	$store_name = '';
	$store_url = '';

	if (class_exists('DSO_Messenger')) {
		$info = DSO_Messenger::get_item_vendor_info($product_id);
		$vendor_id = isset($info['vendor_id']) ? (int) $info['vendor_id'] : 0;
		$store_name = isset($info['store_name']) ? (string) $info['store_name'] : '';
	}
	if (empty($store_name) && function_exists('wcfm_get_vendor_id_by_post')) {
		$vendor_id = (int) wcfm_get_vendor_id_by_post($product_id);
		if ($vendor_id && function_exists('wcfm_get_vendor_store_name')) {
			$store_name = wcfm_get_vendor_store_name($vendor_id);
		}
	}
	if (empty($store_name)) {
		$post = get_post($product_id);
		if ($post && $post->post_author) {
			$author = get_userdata($post->post_author);
			if ($author) {
				$store_name = $author->display_name ?: $author->user_login;
			}
		}
	}
	if (empty($store_name)) {
		$store_name = 'DEJOIY Verified Merchant';
	}
	if ($vendor_id) {
		if (function_exists('wcfmmp_get_store_url')) {
			$store_url = wcfmmp_get_store_url($vendor_id);
		}
		if (empty($store_url)) {
			$u = get_userdata($vendor_id);
			if ($u) {
				$store_url = home_url('/store/' . $u->user_nicename . '/');
			}
		}
	}
	if (empty($store_url)) {
		$store_url = home_url('/shop/');
	}
	return array(
		'name' => $store_name,
		'url'  => $store_url,
	);
}

/**
 * Trust strip + Amazon-style Buy Box Card markup
 *
 * @return string
 */
function dejoiy_product_detail_trust_html() {
	static $rendered_pids = array();
	$product = dejoiy_product_detail_current();
	if ( ! $product ) {
		return '';
	}
	$pid = $product->get_id();
	if ( isset( $rendered_pids[ $pid ] ) ) {
		return '';
	}
	$rendered_pids[ $pid ] = true;

	$promise = dejoiy_get_delivery_promise();
	$seller  = dejoiy_get_pdp_seller_info($product);
	$in_stock = $product->is_in_stock();

	ob_start();
	?>
	<div class="djy-amazon-buybox-card" style="margin:16px 0;background:#ffffff;border:1.5px solid #e2e8f0;border-radius:14px;padding:16px 18px;box-shadow:0 4px 16px rgba(0,0,0,0.03);font-family:'Inter',sans-serif;">
		<!-- Dynamic Delivery Promise -->
		<div style="display:flex;align-items:flex-start;gap:12px;padding-bottom:14px;border-bottom:1px solid #f1f5f9;">
			<div style="width:34px;height:34px;border-radius:50%;background:#ecfdf5;color:#059669;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;">🚚</div>
			<div>
				<div style="font-size:14px;font-weight:700;color:#0f172a;line-height:1.3;">
					<?php if ($in_stock) : ?>
						FREE Delivery <span style="color:#059669;"><?php echo esc_html($promise['date_formatted']); ?></span>
					<?php else : ?>
						<span style="color:#dc2626;">Currently Out of Stock</span>
					<?php endif; ?>
				</div>
				<?php if ($in_stock) : ?>
					<div style="font-size:12px;color:#64748b;margin-top:3px;">
						Order within <span id="djy-cutoff-clock" style="font-weight:700;color:#b45309;background:#fef3c7;padding:1px 6px;border-radius:6px;"><?php echo esc_html($promise['hours_left']); ?> hrs <?php echo esc_html($promise['mins_left']); ?> mins</span>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- Seller & Fulfilled Attributes -->
		<div style="display:grid;grid-template-columns:auto 1fr;gap:8px 16px;margin-top:14px;font-size:12.5px;">
			<div style="color:#64748b;">Ships from</div>
			<div style="color:#0f172a;font-weight:600;">DEJOIY Express Fulfilled</div>

			<div style="color:#64748b;">Sold by</div>
			<div>
				<a href="<?php echo esc_url($seller['url']); ?>" style="color:#001553;font-weight:700;text-decoration:none;border-bottom:1px dashed #94a3b8;"><?php echo esc_html($seller['name']); ?></a>
				<span style="display:inline-block;margin-left:6px;background:#e0f2fe;color:#0369a1;font-size:10px;font-weight:700;padding:1px 6px;border-radius:6px;">Verified Seller</span>
			</div>

			<div style="color:#64748b;">Returns</div>
			<div style="color:#0f172a;font-weight:600;">7 Days Replacement / Return</div>

			<div style="color:#64748b;">Payment</div>
			<div style="color:#0f172a;font-weight:600;">Cash on Delivery / UPI Available</div>
		</div>

		<!-- Trust Badges Strip -->
		<div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-top:14px;padding-top:12px;border-top:1px solid #f1f5f9;flex-wrap:wrap;">
			<span style="font-size:11.5px;color:#475569;display:inline-flex;align-items:center;gap:4px;">
				<span style="color:#10b981;">✓</span> 100% Genuine
			</span>
			<span style="font-size:11.5px;color:#475569;display:inline-flex;align-items:center;gap:4px;">
				<span style="color:#3b82f6;">🔒</span> Secure Payments
			</span>
			<span style="font-size:11.5px;color:#475569;display:inline-flex;align-items:center;gap:4px;">
				<span style="color:#8b5cf6;">🛡️</span> Buyer Protection
			</span>
		</div>
	</div>
	<?php if ($in_stock && $promise['total_seconds'] > 0) : ?>
	<script>
	(function() {
		var secs = <?php echo (int) $promise['total_seconds']; ?>;
		var el = document.getElementById('djy-cutoff-clock');
		if (!el || secs <= 0) return;
		var timer = setInterval(function() {
			if (secs <= 0) { clearInterval(timer); return; }
			secs--;
			var h = Math.floor(secs / 3600);
			var m = Math.floor((secs % 3600) / 60);
			var s = secs % 60;
			el.textContent = h + ' hrs ' + m + ' mins ' + (s < 10 ? '0' : '') + s + 's';
		}, 1000);
	})();
	</script>
	<?php endif; ?>
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
  * Render trust strip and Amazon buy box via WooCommerce standard product hooks
  */
function dejoiy_product_detail_render_hooked() {
	if ( ! is_product() ) {
		return;
	}
	echo dejoiy_product_detail_trust_html();
}
add_action( 'woocommerce_single_product_summary', 'dejoiy_product_detail_render_hooked', 35 );
add_action( 'woocommerce_after_add_to_cart_button', 'dejoiy_product_detail_render_hooked', 15 );
add_action( 'woocommerce_after_add_to_cart_form', 'dejoiy_product_detail_render_hooked', 15 );
add_action( 'woocommerce_share', 'dejoiy_product_detail_render_hooked', 15 );

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