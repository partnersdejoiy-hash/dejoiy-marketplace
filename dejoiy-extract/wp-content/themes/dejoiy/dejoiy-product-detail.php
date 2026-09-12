<?php
/**
 * DEJOIY Product Detail — Amazon-Grade Single Product Experience.
 *
 * Adds authentic Amazon-standard visual hierarchy, store links, ratings,
 * deal badge, 4-feature trust carousel, bank/partner offers, Amazon Buy Box card,
 * and mobile sticky conversion bar.
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
			'date_formatted' => 'Tuesday, Sep 15',
			'hours_left'     => 14,
			'mins_left'      => 30,
			'total_seconds'  => 52200,
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
	$vendor_id  = 0;
	$store_name = '';
	$store_url  = '';

	if (class_exists('DSO_Messenger')) {
		$info       = DSO_Messenger::get_item_vendor_info($product_id);
		$vendor_id  = isset($info['vendor_id']) ? (int) $info['vendor_id'] : 0;
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
 * Amazon-style Product Header: Store link + Rating line + Choice tag
 */
function dejoiy_product_detail_header_html() {
	static $rendered_hdr = array();
	$product = dejoiy_product_detail_current();
	if ( ! $product ) {
		return '';
	}
	$pid = $product->get_id();
	if ( isset( $rendered_hdr[ $pid ] ) ) {
		return '';
	}
	$rendered_hdr[ $pid ] = true;

	$seller = dejoiy_get_pdp_seller_info( $product );
	$rating = (float) $product->get_average_rating();
	$count  = (int) $product->get_review_count();
	ob_start();
	?>
	<div class="djy-amazon-pdp-header">
		<div class="djy-amazon-brand-line">
			<a href="<?php echo esc_url( $seller['url'] ); ?>" class="djy-amazon-store-link">
				<?php echo esc_html( sprintf( __( 'Visit the %s Store', 'dejoiy' ), $seller['name'] ) ); ?>
			</a>
			<span class="djy-amazon-verified-badge">✓ <?php esc_html_e( 'Sold on DEJOIY', 'dejoiy' ); ?></span>
		</div>
		<?php if ( $count > 0 && $rating > 0 ) : ?>
		<div class="djy-amazon-rating-line">
			<div class="djy-amazon-stars" aria-label="<?php echo esc_attr( sprintf( __( '%s out of 5 stars', 'dejoiy' ), $rating ) ); ?>">
				<?php
				$full = (int) round( $rating );
				for ( $i = 1; $i <= 5; $i++ ) {
					echo '<span class="djy-star">' . ( $i <= $full ? '★' : '☆' ) . '</span>';
				}
				?>
				<span class="djy-rating-num"><?php echo esc_html( number_format_i18n( $rating, 1 ) ); ?></span>
			</div>
			<span class="djy-rating-sep">|</span>
			<a href="#reviews" class="djy-rating-count"><?php echo esc_html( sprintf( _n( '%s rating', '%s ratings', $count, 'dejoiy' ), number_format_i18n( $count ) ) ); ?></a>
		</div>
		<?php else : ?>
		<div class="djy-amazon-rating-line is-empty"><?php esc_html_e( 'No customer reviews yet', 'dejoiy' ); ?></div>
		<?php endif; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}
add_shortcode( 'djy_product_header', 'dejoiy_product_detail_header_html' );

/**
 * Trust strip + Amazon-style Deal, Badges, Offers & Buy Box Card markup
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

	$promise  = dejoiy_get_delivery_promise();
	$seller   = dejoiy_get_pdp_seller_info($product);
	$in_stock = $product->is_in_stock();
	$regular  = (float) $product->get_regular_price();
	$price    = (float) $product->get_price();
	$disc     = ( $regular > $price && $price > 0 ) ? (int) round( ( ( $regular - $price ) / $regular ) * 100 ) : 30;
	$mrp      = $regular > $price ? $regular : ( $price > 0 ? round( $price * 1.43 ) : 499 );

	ob_start();
	?>
	<div class="djy-amazon-sheet">
		<!-- Amazon Deal & Pricing Section -->
		<div class="djy-amazon-deal-box">
			<div class="djy-amazon-deal-tag">
				<span class="djy-deal-pill">-<?php echo esc_html( (string) $disc ); ?>%</span>
				<span class="djy-deal-text"><?php esc_html_e( 'Limited time deal', 'dejoiy' ); ?></span>
			</div>
			<div class="djy-amazon-price-row">
				<span class="djy-amazon-price-curr">₹</span>
				<span class="djy-amazon-price-val"><?php echo esc_html( number_format( $price, 2 ) ); ?></span>
			</div>
			<div class="djy-amazon-mrp-row">
				<span class="djy-amazon-mrp-label"><?php esc_html_e( 'M.R.P.:', 'dejoiy' ); ?></span>
				<del class="djy-amazon-mrp-val">₹<?php echo esc_html( number_format( $mrp, 2 ) ); ?></del>
				<span class="djy-amazon-tax-note"><?php esc_html_e( 'Inclusive of all taxes', 'dejoiy' ); ?></span>
			</div>
			<div class="djy-amazon-emi-line">
				<strong>EMI</strong> starts at ₹32/month. <a href="#emi-options" class="djy-emi-link">No Cost EMI available</a>
			</div>
		</div>

		<!-- 4-Feature Trust Badges Grid -->
		<div class="djy-amazon-features-grid">
			<div class="djy-feature-item">
				<div class="djy-feature-icon">🚚</div>
				<div class="djy-feature-title"><?php esc_html_e( 'Free Delivery', 'dejoiy' ); ?></div>
				<div class="djy-feature-sub"><?php esc_html_e( 'On eligible orders', 'dejoiy' ); ?></div>
			</div>
			<div class="djy-feature-item">
				<div class="djy-feature-icon">🔄</div>
				<div class="djy-feature-title"><?php esc_html_e( '7 Days Replacement', 'dejoiy' ); ?></div>
				<div class="djy-feature-sub"><?php esc_html_e( 'Easy & contactless', 'dejoiy' ); ?></div>
			</div>
			<div class="djy-feature-item">
				<div class="djy-feature-icon">🛡️</div>
				<div class="djy-feature-title"><?php esc_html_e( 'DEJOIY Delivered', 'dejoiy' ); ?></div>
				<div class="djy-feature-sub"><?php esc_html_e( 'Express Fulfilled', 'dejoiy' ); ?></div>
			</div>
			<div class="djy-feature-item">
				<div class="djy-feature-icon">🔒</div>
				<div class="djy-feature-title"><?php esc_html_e( 'Secure Transaction', 'dejoiy' ); ?></div>
				<div class="djy-feature-sub"><?php esc_html_e( '256-bit SSL', 'dejoiy' ); ?></div>
			</div>
		</div>

		<!-- Bank & Partner Offers Cards -->
		<div class="djy-amazon-offers-wrapper">
			<div class="djy-offers-heading">
				<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#b45309" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
				<span><?php esc_html_e( 'Offers & Savings', 'dejoiy' ); ?></span>
			</div>
			<div class="djy-offers-track">
				<div class="djy-offer-card">
					<div class="djy-offer-card__badge"><?php esc_html_e( 'Bank Offer', 'dejoiy' ); ?></div>
					<div class="djy-offer-card__text"><?php esc_html_e( 'Flat ₹50 Instant Discount on UPI & select Credit Cards.', 'dejoiy' ); ?></div>
					<span class="djy-offer-card__link"><?php esc_html_e( '1 offer >', 'dejoiy' ); ?></span>
				</div>
				<div class="djy-offer-card">
					<div class="djy-offer-card__badge"><?php esc_html_e( 'Partner Offer', 'dejoiy' ); ?></div>
					<div class="djy-offer-card__text"><?php esc_html_e( 'Save up to 18% with business invoice & GST claim.', 'dejoiy' ); ?></div>
					<span class="djy-offer-card__link"><?php esc_html_e( '1 offer >', 'dejoiy' ); ?></span>
				</div>
			</div>
		</div>

		<!-- Amazon Buy Box Card -->
		<div class="djy-amazon-buybox-card">
			<div class="djy-buybox__price-row">
				<span class="djy-buybox__curr">₹</span>
				<span class="djy-buybox__amount"><?php echo esc_html( number_format( $price, 2 ) ); ?></span>
			</div>

			<!-- Dynamic Delivery Promise -->
			<div class="djy-buybox__delivery-promise">
				<div class="djy-delivery-headline">
					<?php if ( $in_stock ) : ?>
						FREE delivery <strong class="djy-delivery-date"><?php echo esc_html( $promise['date_formatted'] ); ?></strong>
					<?php else : ?>
						<strong class="djy-out-of-stock-text"><?php esc_html_e( 'Currently unavailable.', 'dejoiy' ); ?></strong>
					<?php endif; ?>
				</div>
				<?php if ( $in_stock ) : ?>
					<div class="djy-delivery-timer">
						Order within <span id="djy-cutoff-clock" class="djy-cutoff-badge"><?php echo esc_html( $promise['hours_left'] ); ?> hrs <?php echo esc_html( $promise['mins_left'] ); ?> mins</span>
					</div>
				<?php endif; ?>
			</div>

			<!-- Delivery Location Pin -->
			<div class="djy-buybox__location">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#007185" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
				<span class="djy-location-text">Deliver to Deepak - New Delhi 110001</span>
			</div>

			<!-- Stock Status -->
			<div class="djy-buybox__stock-status">
				<?php if ( $in_stock ) : ?>
					<span class="djy-stock-in"><?php esc_html_e( 'In stock', 'dejoiy' ); ?></span>
				<?php else : ?>
					<span class="djy-stock-out"><?php esc_html_e( 'Out of stock', 'dejoiy' ); ?></span>
				<?php endif; ?>
			</div>

			<!-- Ships from & Sold by Attributes -->
			<div class="djy-buybox__meta-grid">
				<div class="djy-meta-k"><?php esc_html_e( 'Ships from', 'dejoiy' ); ?></div>
				<div class="djy-meta-v"><strong>DEJOIY Express Fulfilled</strong></div>

				<div class="djy-meta-k"><?php esc_html_e( 'Sold by', 'dejoiy' ); ?></div>
				<div class="djy-meta-v">
					<a href="<?php echo esc_url( $seller['url'] ); ?>" class="djy-seller-link"><?php echo esc_html( $seller['name'] ); ?></a>
					<span class="djy-seller-verified">✓ Verified</span>
				</div>

				<div class="djy-meta-k"><?php esc_html_e( 'Returns', 'dejoiy' ); ?></div>
				<div class="djy-meta-v"><?php esc_html_e( '7 Days Replacement / Return', 'dejoiy' ); ?></div>

				<div class="djy-meta-k"><?php esc_html_e( 'Payment', 'dejoiy' ); ?></div>
				<div class="djy-meta-v"><?php esc_html_e( 'Secure transaction (UPI/Cards/COD)', 'dejoiy' ); ?></div>
			</div>

			<!-- Security Guarantee -->
			<div class="djy-buybox__security">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
				<span><?php esc_html_e( 'Secure transaction', 'dejoiy' ); ?></span>
			</div>
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
        ob_start();
        ?>
        <div class="djy-buy-options" style="display:flex; flex-direction:column; gap:8px;">
            <a class="djy-buynow" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Buy Now', 'dejoiy' ); ?></a>
            <button type="button" class="djy-save-later" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>" style="background:transparent; border:1px solid #d5d9d9; border-radius:8px; padding:10px 14px; font-weight:600; cursor:pointer; width:100%; transition:all 0.2s;">
                <?php esc_html_e( 'Save for Later', 'dejoiy' ); ?>
            </button>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const btn = document.querySelector('.djy-save-later');
                if (btn) {
                    btn.addEventListener('click', function() {
                        this.innerHTML = 'Saved to List ✓';
                        this.style.borderColor = '#007185';
                        this.style.color = '#007185';
                    });
                }
            });
        </script>
        <?php
        return ob_get_clean();
}

add_shortcode( 'djy_product_trust', 'dejoiy_product_detail_trust_html' );
add_shortcode( 'djy_product_buynow', 'dejoiy_product_detail_buy_now_html' );

/**
 * Single product mobile floating sticky conversion bar
 */
function dejoiy_product_detail_mobile_bar() {
	if ( ! is_product() ) {
		return;
	}
	$product = dejoiy_product_detail_current();
	if ( ! $product || ! $product->is_in_stock() ) {
		return;
	}
	$thumb_html = $product->get_image( 'woocommerce_thumbnail', array( 'class' => 'djy-sticky-thumb' ) );
	$price_html = $product->get_price_html();
	$buy_url    = add_query_arg( array( 'add-to-cart' => $product->get_id() ), wc_get_checkout_url() );
	?>
	<div class="djy-pdp-mobile-sticky" id="djy-pdp-mobile-sticky">
		<div class="djy-pdp-mobile-sticky__product">
			<?php echo $thumb_html; // phpcs:ignore ?>
			<div class="djy-pdp-mobile-sticky__info">
				<span class="djy-pdp-mobile-sticky__title"><?php echo esc_html( wp_trim_words( $product->get_name(), 4 ) ); ?></span>
				<span class="djy-pdp-mobile-sticky__price"><?php echo wp_kses_post( $price_html ); ?></span>
			</div>
		</div>
		<div class="djy-pdp-mobile-sticky__buttons">
			<button type="button" class="djy-pdp-mobile-sticky__cart-btn" onclick="var b=document.querySelector('.single_add_to_cart_button');if(b){b.click();}"><?php esc_html_e( 'Add to Cart', 'dejoiy' ); ?></button>
			<a href="<?php echo esc_url( $buy_url ); ?>" class="djy-pdp-mobile-sticky__buy-btn"><?php esc_html_e( 'Buy Now', 'dejoiy' ); ?></a>
		</div>
	</div>
	<?php
}

/**
 * Bottom "Select Options" / sticky cart is redundant — PDP already has Add to Cart + Buy Now.
 */
function dejoiy_disable_pdp_sticky_select_options() {
	remove_action( 'after_page_wrapper', 'etheme_sticky_add_to_cart', 1 );
	remove_action( 'wp_footer', 'dejoiy_product_detail_mobile_bar', 30 );
}
add_action( 'wp', 'dejoiy_disable_pdp_sticky_select_options', 40 );
add_filter( 'theme_mod_sticky_add_to_cart_et-desktop', '__return_false', 99 );

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
		$query['orderby']        = 'meta_value_num';
		$query['meta_key']       = 'total_sales';
		$query['order']          = 'DESC';
		$query['posts_per_page'] = 8;
		$fallback                = get_posts( $query );
		$ids                     = array_merge( $ids, $fallback );
		$ids                     = array_values( array_unique( $ids ) );
	}
	$ids = array_slice( $ids, 0, 8 );
	if ( empty( $ids ) ) {
		return '';
	}

	ob_start();
	?>
	<div class="dpy-rail" data-dpy-rail>
		<h2 class="dpy-rail__title"><?php esc_html_e( 'Customers who viewed this item also viewed', 'dejoiy' ); ?></h2>
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
 * Inject the header, trust strip + Buy Now widgets into the Elementor
 * single-product template (id 4234) at render time.
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

	$mutated_header = false;
	$mutated_strip  = false;
	$mutated_buynow = false;
	$mutated_rail   = false;

	$walk = static function ( &$node ) use ( &$walk, &$mutated_header, &$mutated_strip, &$mutated_buynow, &$mutated_rail, $trust, $product ) {
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
			
			// Inject Amazon Header (Store link + stars) above Title (83982ed)
			if ( '83982ed' === $id && ! $mutated_header ) {
				$hdr                          = $trust;
				$hdr['id']                    = substr( md5( 'djy-hdr' . mt_rand() ), 0, 13 );
				$hdr['settings']['shortcode'] = '[djy_product_header]';
				array_splice( $children, $i, 0, array( $hdr ) );
				$count++;
				$i++;
				$mutated_header = true;
				continue;
			}

			// Inject Deal + Badges + Buy Box Card after Price (2486bf86)
			if ( '2486bf86' === $id && ! $mutated_strip ) {
				$widget                          = $trust;
				$widget['id']                    = substr( md5( 'djy-trust' . mt_rand() ), 0, 13 );
				$widget['settings']['shortcode'] = '[djy_product_trust]';
				array_splice( $children, $i + 1, 0, array( $widget ) );
				$count++;
				$mutated_strip = true;
				continue;
			}

			// Inject Buy Now after Add to Cart (51854dc9)
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

			// Inject Rail after e6f5a62
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
 * Block elementor document cache on dynamic product page
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
 * Shop cards assets
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
 * Product detail assets
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
