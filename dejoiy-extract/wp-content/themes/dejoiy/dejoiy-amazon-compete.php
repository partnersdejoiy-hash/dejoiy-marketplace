<?php
/**
 * DEJOIY Amazon-compete layer — honest catalog UX, aliases, PDP truth, headers.
 *
 * Does not invent sellers, reviews, or inventory. Safe to load on every request.
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Seller Hub writes WooCommerce `product` posts in this same WordPress install.
 * Those listings are what https://dejoiy.com shows. Host cookies differ
 * (sellerhub.dejoiy.com vs dejoiy.com) but the catalogue is one database.
 */

function dejoiy_amazon_compete_assets() {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return;
	}
	$dir = get_stylesheet_directory();
	$uri = get_stylesheet_directory_uri();
	$css = $dir . '/dejoiy-amazon-compete.css';
	if ( is_readable( $css ) ) {
		wp_enqueue_style(
			'dejoiy-amazon-compete',
			$uri . '/dejoiy-amazon-compete.css',
			array( 'dejoiy-global-header' ),
			(string) filemtime( $css )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'dejoiy_amazon_compete_assets', 10110 );

/**
 * Customer-app security headers (Seller Hub already sends these at nginx).
 * Nginx CORS * cannot be fully removed from PHP; this still sets HSTS/frame/nosniff.
 */
function dejoiy_amazon_compete_send_headers() {
	if ( headers_sent() ) {
		return;
	}
	$host = (string) ( $_SERVER['HTTP_HOST'] ?? '' );
	if ( false !== strpos( $host, 'sellerhub' ) ) {
		return;
	}
	header( 'Strict-Transport-Security: max-age=31536000; includeSubDomains', false );
	header( 'X-Frame-Options: SAMEORIGIN', false );
	header( 'X-Content-Type-Options: nosniff', false );
	header( 'Referrer-Policy: strict-origin-when-cross-origin', false );
}
add_action( 'send_headers', 'dejoiy_amazon_compete_send_headers', 2 );

/**
 * Amazon-style short URLs → real destinations (no rewrite flush required).
 */
function dejoiy_amazon_compete_aliases() {
	if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}
	$path = (string) parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH );
	$path = strtolower( trim( $path, '/' ) );
	if ( '' === $path ) {
		return;
	}

	if ( in_array( $path, array( 'audit-download', 'dejoiy-promo-deck' ), true ) && ! current_user_can( 'manage_options' ) ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}

	if ( 'search' === $path ) {
		$s    = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
		wp_safe_redirect( add_query_arg( array_filter( array( 's' => $s, 'post_type' => 'product' ) ), $shop ), 301 );
		exit;
	}

	$sell = function_exists( 'dejoiy_sell_url' ) ? dejoiy_sell_url() : home_url( '/vendor-register/' );

	$map = array(
		'wishlist'            => home_url( '/my-account/?et-wishlist-page' ),
		'favorites'           => home_url( '/my-account/?et-wishlist-page' ),
		'nexus'               => home_url( '/dejoiy-library/?dejoiy_library=1' ),
		'library'             => home_url( '/dejoiy-library/' ),
		'track-order'         => function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'orders' ) : home_url( '/my-account/orders/' ),
		'track'               => function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'orders' ) : home_url( '/my-account/orders/' ),
		'orders'              => function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'orders' ) : home_url( '/my-account/orders/' ),
		'become-a-seller'     => $sell,
		'sell-on-dejoiy'      => $sell,
		'seller-center'       => $sell,
		'vendor-membership'   => $sell,
		'start-selling'       => $sell,
		'seller'              => 'https://sellerhub.dejoiy.com/',
		'custom-studio'       => home_url( '/dejoiy-custom-studio/' ),
		'studio'              => home_url( '/dejoiy-custom-studio/' ),
		'contact'             => home_url( '/contact-us/' ),
	);

	if ( isset( $map[ $path ] ) ) {
		wp_safe_redirect( $map[ $path ], 301 );
		exit;
	}
}
add_action( 'template_redirect', 'dejoiy_amazon_compete_aliases', 1 );

/**
 * Hide duplicate Woo "Copy" listings from storefront loops (they remain in admin / Seller Hub).
 *
 * @param WP_Query $query Query.
 */
function dejoiy_amazon_compete_hide_copy_listings( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	$pt = $query->get( 'post_type' );
	$is_products = ( 'product' === $pt )
		|| $query->is_post_type_archive( 'product' )
		|| ( function_exists( 'is_shop' ) && is_shop() )
		|| ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() )
		|| $query->is_search();
	if ( ! $is_products ) {
		return;
	}

	$ids = dejoiy_amazon_compete_copy_product_ids();
	if ( empty( $ids ) ) {
		return;
	}
	$existing = $query->get( 'post__not_in' );
	if ( ! is_array( $existing ) ) {
		$existing = array();
	}
	$query->set( 'post__not_in', array_values( array_unique( array_merge( $existing, $ids ) ) ) );
}
add_action( 'pre_get_posts', 'dejoiy_amazon_compete_hide_copy_listings', 40 );

/**
 * @return array<int, int>
 */
function dejoiy_amazon_compete_copy_product_ids() {
	$cached = get_transient( 'dejoiy_copy_product_ids' );
	if ( is_array( $cached ) ) {
		return $cached;
	}
	$q = new WP_Query(
		array(
			'post_type'              => 'product',
			'post_status'            => 'publish',
			'posts_per_page'         => 80,
			's'                      => '(Copy)',
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);
	$ids = array();
	foreach ( (array) $q->posts as $id ) {
		$title = get_the_title( (int) $id );
		if ( false !== stripos( $title, '(copy)' ) ) {
			$ids[] = (int) $id;
		}
	}
	wp_reset_postdata();
	set_transient( 'dejoiy_copy_product_ids', $ids, 10 * MINUTE_IN_SECONDS );
	return $ids;
}

add_action(
	'save_post_product',
	function () {
		delete_transient( 'dejoiy_copy_product_ids' );
	}
);

/**
 * Real Product JSON-LD (Offer + optional AggregateRating).
 */
function dejoiy_amazon_compete_product_schema() {
	if ( ! function_exists( 'is_product' ) || ! is_product() || ! function_exists( 'wc_get_product' ) ) {
		return;
	}
	$product = wc_get_product( get_queried_object_id() );
	if ( ! $product ) {
		return;
	}
	$desc = $product->get_short_description();
	if ( '' === trim( wp_strip_all_tags( (string) $desc ) ) ) {
		$desc = $product->get_description();
	}
	$desc = wp_strip_all_tags( (string) $desc );
	if ( '' === $desc ) {
		$desc = $product->get_name() . ' on DEJOIY.';
	}

	$data = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Product',
		'name'        => $product->get_name(),
		'description' => wp_trim_words( $desc, 40, '…' ),
		'sku'         => $product->get_sku(),
		'url'         => get_permalink( $product->get_id() ),
		'image'       => wp_get_attachment_url( $product->get_image_id() ) ?: '',
		'brand'       => array(
			'@type' => 'Brand',
			'name'  => 'DEJOIY',
		),
		'offers'      => array(
			'@type'         => 'Offer',
			'priceCurrency' => get_woocommerce_currency(),
			'price'         => $product->get_price(),
			'availability'  => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
			'url'           => get_permalink( $product->get_id() ),
		),
	);

	$rating = (float) $product->get_average_rating();
	$count  = (int) $product->get_review_count();
	if ( $rating > 0 && $count > 0 ) {
		$data['aggregateRating'] = array(
			'@type'       => 'AggregateRating',
			'ratingValue' => $rating,
			'reviewCount' => $count,
		);
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $data ) . '</script>' . "\n";
}
add_action( 'wp_head', 'dejoiy_amazon_compete_product_schema', 32 );

add_filter( 'woocommerce_single_product_zoom_enabled', '__return_true', 20 );
add_filter( 'woocommerce_single_product_photoswipe_enabled', '__return_true', 20 );

/**
 * Checkout: visible UPI / COD / cards strip (does not change gateways).
 */
function dejoiy_amazon_compete_checkout_pay_strip() {
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
		return;
	}
	echo '<p class="djy-pay-strip" role="note">Pay with UPI, cards, net banking, or COD where available. Secure checkout on HTTPS.</p>';
}
add_action( 'woocommerce_checkout_before_customer_details', 'dejoiy_amazon_compete_checkout_pay_strip', 4 );

/**
 * Related products on desktop PDP (mobile already has a custom shelf).
 */
function dejoiy_amazon_compete_related_heading() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	if ( function_exists( 'dejoiy_mobile_product_is_product_focus' ) && dejoiy_mobile_product_is_product_focus() ) {
		return;
	}
	add_filter( 'woocommerce_output_related_products_args', 'dejoiy_amazon_compete_related_args', 20 );
}
add_action( 'wp', 'dejoiy_amazon_compete_related_heading', 30 );

/**
 * @param array<string, mixed> $args Args.
 * @return array<string, mixed>
 */
function dejoiy_amazon_compete_related_args( $args ) {
	$args['posts_per_page'] = 4;
	$args['columns']        = 4;
	return $args;
}

/**
 * Honest Open Graph description from the product, not a generic sentence.
 *
 * @param string $desc Description.
 * @return string
 */
function dejoiy_amazon_compete_og_desc( $desc ) {
	if ( ! function_exists( 'is_product' ) || ! is_product() || ! function_exists( 'wc_get_product' ) ) {
		return $desc;
	}
	$product = wc_get_product( get_queried_object_id() );
	if ( ! $product ) {
		return $desc;
	}
	$text = wp_strip_all_tags( (string) $product->get_short_description() );
	if ( '' === $text ) {
		$text = wp_strip_all_tags( (string) $product->get_description() );
	}
	if ( '' === $text ) {
		$price = $product->get_price();
		$text  = $product->get_name() . ( $price ? ' — ' . wp_strip_all_tags( wc_price( $price ) ) : '' );
	}
	return wp_trim_words( $text, 32, '…' );
}
add_filter( 'aioseo_description', 'dejoiy_amazon_compete_og_desc', 20 );
add_filter( 'rank_math/frontend/description', 'dejoiy_amazon_compete_og_desc', 20 );

/**
 * Do not render XStore Elementor sticky cart (bottom "Select options" bar).
 * PDP already has Add to cart and Buy now in the product summary.
 *
 * @param bool                 $should_render Whether to render.
 * @param \Elementor\Widget_Base $widget Widget.
 * @return bool
 */
function dejoiy_amazon_compete_skip_sticky_cart_widget( $should_render, $widget ) {
	if ( ! $should_render || ! is_object( $widget ) || ! method_exists( $widget, 'get_name' ) ) {
		return $should_render;
	}
	$name = $widget->get_name();
	if ( 'woocommerce-product-etheme_sticky_cart' === $name ) {
		return false;
	}
	if ( 'woocommerce-archive-etheme_dynamic_categories' === $name ) {
		return false;
	}
	return $should_render;
}
add_filter( 'elementor/frontend/widget/should_render', 'dejoiy_amazon_compete_skip_sticky_cart_widget', 10, 2 );

/**
 * Turn seller-entered product copy into readable sections on the PDP.
 * Works for any product: headings like Features/Care, bullet lines, size chips.
 *
 * @param string $html Raw HTML.
 * @return string
 */
function dejoiy_amazon_compete_format_description_html( $html ) {
	$html = (string) $html;
	if ( '' === trim( wp_strip_all_tags( $html ) ) ) {
		return $html;
	}
	if ( false !== strpos( $html, 'class="djy-desc"' ) || false !== strpos( $html, "class='djy-desc'" ) ) {
		return $html;
	}

	$plain = trim( wp_strip_all_tags( $html ) );
	$bold_bits = array();
	if ( preg_match_all( '#<(b|strong)\b[^>]*>(.*?)</\1>#is', $html, $bm ) ) {
		$bold_bits = $bm[2];
	}
	$bold_plain = trim( wp_strip_all_tags( implode( ' ', $bold_bits ) ) );
	$work       = $html;
	if ( strlen( $bold_plain ) >= (int) ( 0.65 * strlen( $plain ) ) ) {
		$work = preg_replace( '#</?(b|strong)\b[^>]*>#i', '', $work );
	}

	$work = preg_replace( '#<p[^>]*>\s*(?:<br\s*/?>|&nbsp;|\s)*\s*</p>#i', '', $work );
	$work = preg_replace( '#<(br|p)\b[^>]*>#i', "\n", $work );
	$work = preg_replace( '#</p>#i', "\n", $work );
	$work = str_replace( array( '&nbsp;', '&#160;' ), ' ', $work );
	$work = html_entity_decode( wp_strip_all_tags( $work ), ENT_QUOTES, 'UTF-8' );
	$work = preg_replace( "/[ \t]+\n/", "\n", $work );
	$lines = preg_split( '/\n+/', $work );
	$lines = array_values(
		array_filter(
			array_map(
				static function ( $line ) {
					$line = preg_replace( '/\s+/u', ' ', (string) $line );
					return trim( $line, " \t\n\r\0\x0B\xC2\xA0" );
				},
				$lines
			)
		)
	);
	if ( ! $lines ) {
		return $html;
	}

	$heading_re = '/^(about this item|product details?|features?|key features?|highlights?|specifications?|details?|materials?|fabric|care instructions?|how to use|what.?s included|what.?s in the box|package contents?|available sizes?|available colou?rs?|size(?:s)?|colour?s?|dimensions?|description)\s*:?\s*$/i';
	$list_re    = '/^(?:[•●○■▪►·\-–—*]|[0-9]+[.)])\s+/u';
	$chip_heads = array( 'available sizes', 'available size', 'available colors', 'available colours', 'available color', 'available colour', 'sizes', 'size', 'colors', 'colours' );

	$out  = array();
	$list = array();
	$last_heading = '';

	$flush_list = static function () use ( &$list, &$out ) {
		if ( ! $list ) {
			return;
		}
		$items = '';
		foreach ( $list as $item ) {
			$items .= '<li>' . esc_html( $item ) . '</li>';
		}
		$out[] = '<ul class="djy-desc__list">' . $items . '</ul>';
		$list  = array();
	};

	foreach ( $lines as $line ) {
		if ( preg_match( $heading_re, $line ) ) {
			$flush_list();
			$last_heading = strtolower( rtrim( $line, " :" ) );
			$out[]        = '<h3 class="djy-desc__h">' . esc_html( rtrim( $line, " :" ) ) . '</h3>';
			continue;
		}
		if ( preg_match( $list_re, $line ) ) {
			$list[] = preg_replace( $list_re, '', $line );
			continue;
		}
		$flush_list();
		$parts = preg_split( '/\s*,\s*/', $line );
		$chip_ok = count( $parts ) >= 2;
		if ( $chip_ok ) {
			foreach ( $parts as $part ) {
				if ( mb_strlen( $part ) > 22 ) {
					$chip_ok = false;
					break;
				}
			}
		}
		if ( $chip_ok && in_array( $last_heading, $chip_heads, true ) ) {
			$chips = '';
			foreach ( $parts as $part ) {
				$chips .= '<span class="djy-desc__chip">' . esc_html( $part ) . '</span>';
			}
			$out[] = '<div class="djy-desc__chips">' . $chips . '</div>';
			continue;
		}
		$out[] = '<p class="djy-desc__p">' . esc_html( $line ) . '</p>';
	}
	$flush_list();

	return '<div class="djy-desc">' . implode( '', $out ) . '</div>';
}

/**
 * @param string $content Content.
 * @return string
 */
function dejoiy_amazon_compete_filter_product_content( $content ) {
	if ( is_admin() || wp_doing_ajax() || ! function_exists( 'is_product' ) || ! is_product() ) {
		return $content;
	}
	return dejoiy_amazon_compete_format_description_html( $content );
}
add_filter( 'the_content', 'dejoiy_amazon_compete_filter_product_content', 16 );

/**
 * @param string $content Short description.
 * @return string
 */
function dejoiy_amazon_compete_filter_short_description( $content ) {
	if ( is_admin() || ! function_exists( 'is_product' ) || ! is_product() ) {
		return $content;
	}
	$plain = trim( wp_strip_all_tags( (string) $content ) );
	if ( '' === $plain ) {
		return $content;
	}
	return '<p class="djy-desc__p">' . esc_html( $plain ) . '</p>';
}
add_filter( 'woocommerce_short_description', 'dejoiy_amazon_compete_filter_short_description', 20 );

/**
 * Blog sidebar (All Category / Top News / Newsletter) stays on news + announcements only.
 *
 * @return bool
 */
function dejoiy_amazon_compete_is_updates_surface() {
	if ( is_admin() ) {
		return true;
	}
	if ( function_exists( 'is_singular' ) && is_singular( 'post' ) ) {
		return true;
	}
	if ( function_exists( 'is_home' ) && is_home() && ! is_front_page() ) {
		return true;
	}
	if ( function_exists( 'is_category' ) && is_category() ) {
		return true;
	}
	if ( function_exists( 'is_page' ) && ( is_page( 'news' ) || is_page( 'blog' ) ) ) {
		return true;
	}
	return false;
}

/**
 * @param array<string, array<int, string>> $sidebars Widgets.
 * @return array<string, array<int, string>>
 */
function dejoiy_amazon_compete_trim_blog_sidebar( $sidebars ) {
	if ( is_admin() || dejoiy_amazon_compete_is_updates_surface() || ! is_array( $sidebars ) ) {
		return $sidebars;
	}
	$sidebars['main-sidebar'] = array();
	return $sidebars;
}
add_filter( 'sidebars_widgets', 'dejoiy_amazon_compete_trim_blog_sidebar', 99 );

/**
 * Canonical seller onboarding URL (Seller Hub account, not WCFM).
 *
 * @return string
 */
function dejoiy_sell_url() {
	return home_url( '/vendor-register/' );
}

add_filter( 'woocommerce_redirect_single_search_result', '__return_false', 99 );

/**
 * Marketplace search always looks at products (Amazon-style catalog search).
 *
 * @param WP_Query $query Query.
 */
function dejoiy_amazon_compete_force_product_search( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	$s = $query->get( 's' );
	if ( ! is_string( $s ) || '' === trim( $s ) ) {
		return;
	}
	if ( $query->is_search() || $query->is_post_type_archive( 'product' ) || ( function_exists( 'is_shop' ) && is_shop() ) ) {
		$query->set( 'post_type', 'product' );
		$query->set( 'post_status', 'publish' );
	}
}
add_action( 'pre_get_posts', 'dejoiy_amazon_compete_force_product_search', 5 );

/**
 * Seed honest News / Announcements posts once.
 */
function dejoiy_amazon_compete_ensure_news_posts() {
	if ( get_option( 'dejoiy_news_seed_v2' ) ) {
		dejoiy_amazon_compete_clean_news_page_seo();
		return;
	}
	if ( ! function_exists( 'wp_insert_post' ) ) {
		return;
	}

	$cat_id = 0;
	$term   = get_term_by( 'slug', 'dejoiy-announcements', 'category' );
	if ( $term && ! is_wp_error( $term ) ) {
		$cat_id = (int) $term->term_id;
	} else {
		$created = wp_insert_term( 'Dejoiy Announcements', 'category', array( 'slug' => 'dejoiy-announcements' ) );
		if ( ! is_wp_error( $created ) ) {
			$cat_id = (int) $created['term_id'];
		}
	}

	$posts = array(
		array(
			'name'    => 'seller-hub-is-open',
			'title'   => 'Seller Hub is open — list products from one dashboard',
			'content' => '<p>DEJOIY sellers manage listings, orders, and payouts in Seller Hub at sellerhub.dejoiy.com — not WordPress admin, and not a separate WCFM vendor desk.</p><p>To open a store, use <a href="' . esc_url( dejoiy_sell_url() ) . '">Sell on DEJOIY</a>. After you submit, you land in Seller Hub signed in as a seller.</p><p>Existing sellers can sign in directly at <a href="https://sellerhub.dejoiy.com/">sellerhub.dejoiy.com</a>.</p>',
		),
		array(
			'name'    => 'how-shopping-works-on-dejoiy',
			'title'   => 'How shopping works on DEJOIY',
			'content' => '<p>Search from the header looks through live products on the shop. Results stay on a list so you can compare — we do not jump you to a single SKU when more than one match exists.</p><p>Add items to cart, then checkout over HTTPS. UPI, cards, net banking, and COD appear where the seller and gateway allow them. Track orders from My Account.</p><p>The catalog is still small and growing. If a search is empty, that means we do not have that product yet — not a broken page.</p>',
		),
		array(
			'name'    => 'dejoiy-updates-what-is-live',
			'title'   => 'What is live on DEJOIY today',
			'content' => '<p>The customer store at dejoiy.com and Seller Hub share one catalog. When a seller publishes a product in Seller Hub, shoppers can find it on DEJOIY.</p><p>Worlds such as Nexus, Custom Studio, QuickMart, Renew, and Hire are in the header as they launch. Shop is the place to buy what is in stock now.</p><p>Questions: <a href="' . esc_url( home_url( '/contact-us/' ) ) . '">Contact us</a> or support-care@dejoiy.com.</p>',
		),
	);

	$author = 1;
	foreach ( $posts as $item ) {
		if ( get_page_by_path( $item['name'], OBJECT, 'post' ) ) {
			continue;
		}
		$pid = wp_insert_post(
			array(
				'post_type'    => 'post',
				'post_status'  => 'publish',
				'post_name'    => $item['name'],
				'post_title'   => $item['title'],
				'post_content' => $item['content'],
				'post_author'  => $author,
				'post_excerpt' => wp_trim_words( wp_strip_all_tags( $item['content'] ), 28, '…' ),
			),
			true
		);
		if ( ! is_wp_error( $pid ) && $pid && $cat_id ) {
			wp_set_post_categories( (int) $pid, array( $cat_id ), false );
		}
	}

	update_option( 'dejoiy_news_seed_v2', 1, false );
	dejoiy_amazon_compete_clean_news_page_seo();
}

/**
 * Strip XStore demo SEO copy from the News page.
 */
function dejoiy_amazon_compete_clean_news_page_seo() {
	if ( get_option( 'dejoiy_news_page_seo_v1' ) ) {
		return;
	}
	$page = get_page_by_path( 'news' );
	if ( $page && ! is_wp_error( $page ) ) {
		wp_update_post(
			array(
				'ID'           => (int) $page->ID,
				'post_excerpt' => 'Official DEJOIY updates from the marketplace.',
				'post_content' => 'Official DEJOIY updates.',
			)
		);
		delete_post_meta( (int) $page->ID, 'rank_math_description' );
		delete_post_meta( (int) $page->ID, 'rank_math_title' );
		delete_post_meta( (int) $page->ID, '_yoast_wpseo_metadesc' );
	}
	update_option( 'dejoiy_news_page_seo_v1', 1, false );
}
add_action( 'init', 'dejoiy_amazon_compete_ensure_news_posts', 30 );

/**
 * @return string
 */
function dejoiy_amazon_compete_news_feed_html() {
	$q = new WP_Query(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => 12,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);

	$html  = '<div class="djy-news">';
	$html .= '<p class="djy-news__lede">Official DEJOIY updates. No demo posts, no placeholder magazines.</p>';
	if ( ! $q->have_posts() ) {
		$html .= '<p class="djy-news__empty">No updates published yet.</p></div>';
		return $html;
	}
	$html .= '<ol class="djy-news__list">';
	while ( $q->have_posts() ) {
		$q->the_post();
		$html .= '<li class="djy-news__item">';
		$html .= '<time class="djy-news__date" datetime="' . esc_attr( get_the_date( 'c' ) ) . '">' . esc_html( get_the_date() ) . '</time>';
		$html .= '<h2 class="djy-news__title"><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h2>';
		$html .= '<p class="djy-news__excerpt">' . esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt() . ' ' . get_the_content() ), 36, '…' ) ) . '</p>';
		$html .= '</li>';
	}
	wp_reset_postdata();
	$html .= '</ol></div>';
	return $html;
}

/**
 * Bypass Elementor demo markup on /news/.
 *
 * @param string $template Template path.
 * @return string
 */
function dejoiy_amazon_compete_news_template( $template ) {
	if ( function_exists( 'is_page' ) && is_page( 'news' ) ) {
		$custom = get_stylesheet_directory() . '/page-news.php';
		if ( is_readable( $custom ) ) {
			return $custom;
		}
	}
	return $template;
}
add_filter( 'template_include', 'dejoiy_amazon_compete_news_template', 99999 );
