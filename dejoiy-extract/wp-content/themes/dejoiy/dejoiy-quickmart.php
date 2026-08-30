<?php
/**
 * DEJOIY QuickMart — Blinkit / Zepto style quick commerce (isolated module).
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_stylesheet_directory() . '/library-quickmart-cart-isolation.php';

// Do not load QuickMart UI / WC hooks on the main DEJOIY marketplace cart.
if ( function_exists( 'dejoiy_quickmart_is_marketplace_cart_request' ) && dejoiy_quickmart_is_marketplace_cart_request() ) {
	return;
}

$dejoiy_qm_ui = get_stylesheet_directory() . '/dejoiy-quickmart-ui.php';
if ( is_readable( $dejoiy_qm_ui ) ) {
	require_once $dejoiy_qm_ui;
}
$dejoiy_qm_launch = get_stylesheet_directory() . '/dejoiy-quick-launch.php';
if ( is_readable( $dejoiy_qm_launch ) ) {
	require_once $dejoiy_qm_launch;
}
$dejoiy_qm_mock = get_stylesheet_directory() . '/dejoiy-quickmart-mock.php';
if ( is_readable( $dejoiy_qm_mock ) ) {
	require_once $dejoiy_qm_mock;
}
$dejoiy_qm_flow = get_stylesheet_directory() . '/dejoiy-quickmart-flow.php';
if ( is_readable( $dejoiy_qm_flow ) && defined( 'DEJOIY_QUICKMART_FLOW_ENABLED' ) && DEJOIY_QUICKMART_FLOW_ENABLED ) {
	require_once $dejoiy_qm_flow;
}

/**
 * QuickMart page (slug, ID, or URI).
 *
 * @return bool
 */
function dejoiy_quickmart_is_quickmart_page() {
	if ( is_page( 'dejoiy-quick-mart' ) || is_page( 4717 ) ) {
		return true;
	}
	$uri = strtolower( (string) wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) );
	return false !== strpos( $uri, 'dejoiy-quick-mart' );
}

/**
 * @return bool
 */
function dejoiy_quickmart_is_app_page() {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return false;
	}
	if ( dejoiy_quickmart_is_quickmart_page() ) {
		return true;
	}
	$uri = strtolower( (string) wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) );
	if ( false !== strpos( $uri, 'dejoiy-quick-mart' ) ) {
		return true;
	}
	if ( function_exists( 'is_product' ) && is_product() ) {
		$pid = get_queried_object_id();
		return $pid && dejoiy_quickmart_is_product( $pid );
	}
	if ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() ) {
		$term = get_queried_object();
		if ( $term && isset( $term->slug ) && 'quick-products' === $term->slug ) {
			return true;
		}
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['dejoiy_ecosystem'] ) && 'quickmart' === sanitize_key( wp_unslash( $_GET['dejoiy_ecosystem'] ) ) ) {
		return true;
	}
	return false;
}

/**
 * @return array<int, string>
 */
function dejoiy_quickmart_serviceable_pincodes() {
	$pins = get_option(
		'dejoiy_quickmart_pincodes',
		array( '110001', '110016', '110017', '110019', '110020', '110024', '110025', '400001', '400050', '560001', '560034', '500001', '600001', '700001', '411001' )
	);
	if ( ! is_array( $pins ) ) {
		$pins = array();
	}
	return array_values(
		array_filter(
			array_map(
				static function ( $p ) {
					return preg_match( '/^\d{6}$/', (string) $p ) ? (string) $p : '';
				},
				$pins
			)
		)
	);
}

/**
 * @param string $pincode Pincode.
 * @return bool
 */
function dejoiy_quickmart_pincode_is_serviceable( $pincode ) {
	$pincode = preg_replace( '/\D/', '', (string) $pincode );
	if ( 6 !== strlen( $pincode ) ) {
		return false;
	}
	return in_array( $pincode, dejoiy_quickmart_serviceable_pincodes(), true );
}

/**
 * @return string
 */
function dejoiy_quickmart_get_pincode() {
	if ( function_exists( 'WC' ) && WC()->session ) {
		$pin = (string) WC()->session->get( 'dejoiy_quickmart_pincode' );
		if ( $pin ) {
			return $pin;
		}
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_COOKIE['dejoiy_qm_pincode'] ) ) {
		return preg_replace( '/\D/', '', (string) wp_unslash( $_COOKIE['dejoiy_qm_pincode'] ) );
	}
	return '';
}

/**
 * @param string $pincode Pincode.
 * @param string $label   Display label.
 */
function dejoiy_quickmart_save_pincode( $pincode, $label = '' ) {
	$pincode = preg_replace( '/\D/', '', (string) $pincode );
	if ( 6 !== strlen( $pincode ) ) {
		return;
	}
	if ( function_exists( 'WC' ) && WC()->session ) {
		WC()->session->set( 'dejoiy_quickmart_pincode', $pincode );
		WC()->session->set( 'dejoiy_quickmart_location', $label ? $label : dejoiy_quickmart_pincode_label( $pincode ) );
	}
	if ( ! headers_sent() ) {
		setcookie( 'dejoiy_qm_pincode', $pincode, time() + MONTH_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
	}
}

/**
 * @param string $pincode Pincode.
 * @return string
 */
function dejoiy_quickmart_pincode_label( $pincode ) {
	$map = array(
		'110001' => 'Connaught Place, New Delhi',
		'110016' => 'Hauz Khas, New Delhi',
		'400001' => 'Fort, Mumbai',
		'560001' => 'MG Road, Bengaluru',
		'500001' => 'Abids, Hyderabad',
		'600001' => 'Parrys, Chennai',
		'700001' => 'BBD Bagh, Kolkata',
	);
	return isset( $map[ $pincode ] ) ? $map[ $pincode ] : sprintf( /* translators: %s pincode */ __( 'Pincode %s', 'dejoiy' ), $pincode );
}

/**
 * @return string
 */
function dejoiy_quickmart_get_location_label() {
	if ( function_exists( 'WC' ) && WC()->session ) {
		$loc = (string) WC()->session->get( 'dejoiy_quickmart_location' );
		if ( $loc ) {
			return $loc;
		}
	}
	$pin = dejoiy_quickmart_get_pincode();
	return $pin ? dejoiy_quickmart_pincode_label( $pin ) : __( 'Set delivery location', 'dejoiy' );
}

/**
 * AJAX: save pincode.
 */
function dejoiy_quickmart_ajax_save_pincode() {
	check_ajax_referer( 'dejoiy_quickmart', 'nonce' );
	$pin = isset( $_POST['pincode'] ) ? preg_replace( '/\D/', '', (string) wp_unslash( $_POST['pincode'] ) ) : '';
	if ( ! dejoiy_quickmart_pincode_is_serviceable( $pin ) ) {
		wp_send_json_error( array( 'message' => __( 'QuickMart is not available in this area yet. Try another pincode.', 'dejoiy' ) ) );
	}
	$label = dejoiy_quickmart_pincode_label( $pin );
	dejoiy_quickmart_save_pincode( $pin, $label );
	wp_send_json_success(
		array(
			'pincode' => $pin,
			'label'   => $label,
			'eta'     => dejoiy_quickmart_eta_label(),
		)
	);
}
add_action( 'wp_ajax_dejoiy_quickmart_pincode', 'dejoiy_quickmart_ajax_save_pincode' );
add_action( 'wp_ajax_nopriv_dejoiy_quickmart_pincode', 'dejoiy_quickmart_ajax_save_pincode' );

/**
 * @return string
 */
function dejoiy_quickmart_eta_label() {
	$pin = dejoiy_quickmart_get_pincode();
	if ( $pin && dejoiy_quickmart_pincode_is_serviceable( $pin ) ) {
		$hash = abs( crc32( $pin ) ) % 6;
		$min  = 12 + $hash;
		$max  = $min + 8;
		return sprintf( '%d–%d mins', $min, $max );
	}
	return __( '10–20 mins', 'dejoiy' );
}

/**
 * Single-line ETA for header (keeps en-dash).
 *
 * @return string
 */
function dejoiy_quickmart_eta_display() {
	return dejoiy_quickmart_eta_label();
}

/**
 * QuickMart orders page URL (stays in quick flow).
 *
 * @return string
 */
function dejoiy_quickmart_orders_url() {
	return add_query_arg( 'qm_view', 'orders', dejoiy_quickmart_base_url() );
}

/**
 * @param array $args Query args.
 * @return WP_Query
 */
function dejoiy_quickmart_query_products( $args = array() ) {
	$defaults = array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => 12,
		'no_found_rows'  => true,
	);
	$args = wp_parse_args( $args, $defaults );
	if ( empty( $args['tax_query'] ) ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => array( 'quick-products' ),
			),
		);
	}
	return new WP_Query( $args );
}

/**
 * @param WC_Product|null $product Product.
 * @return string
 */
function dejoiy_quickmart_render_product_card( $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return '';
	}
	$id    = $product->get_id();
	$url   = function_exists( 'dejoiy_ecosystem_product_url' ) ? dejoiy_ecosystem_product_url( $id, 'quickmart' ) : get_permalink( $id );
	$img   = $product->get_image( 'woocommerce_thumbnail', array( 'class' => 'qm-card__img', 'loading' => 'lazy' ) );
	$price = $product->get_price_html();
	$reg   = $product->get_regular_price();
	$sale  = $product->get_sale_price();
	$badge = '';
	if ( $sale && $reg && (float) $reg > (float) $sale ) {
		$pct = round( ( ( (float) $reg - (float) $sale ) / (float) $reg ) * 100 );
		$badge = '<span class="qm-card__off">' . esc_html( (string) $pct ) . '% off</span>';
	}
	$weight = $product->get_weight() ? $product->get_weight() . ' ' . get_option( 'woocommerce_weight_unit', 'kg' ) : '';

	ob_start();
	?>
	<article class="qm-card qm-card--fk" data-product-id="<?php echo esc_attr( (string) $id ); ?>">
		<a class="qm-card__link" href="<?php echo esc_url( $url ); ?>">
			<span class="qm-card__eta"><?php echo esc_html( dejoiy_quickmart_eta_label() ); ?></span>
			<?php echo $badge; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<div class="qm-card__media"><?php echo $img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			<h3 class="qm-card__title"><?php echo esc_html( $product->get_name() ); ?></h3>
			<?php if ( $weight ) : ?>
				<p class="qm-card__weight"><?php echo esc_html( $weight ); ?></p>
			<?php endif; ?>
			<div class="qm-card__price"><?php echo $price; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
		</a>
		<?php if ( $product->is_purchasable() && $product->is_in_stock() ) : ?>
			<a href="<?php echo esc_url( add_query_arg( DEJOIY_QUICKMART_FLOW, '1', $product->add_to_cart_url() ) ); ?>" class="qm-card__add qm-card__add--fk add_to_cart_button ajax_add_to_cart" data-product_id="<?php echo esc_attr( (string) $id ); ?>" data-quantity="1" aria-label="<?php esc_attr_e( 'Add to cart', 'dejoiy' ); ?>">+</a>
		<?php else : ?>
			<span class="qm-card__add qm-card__add--disabled"><?php esc_html_e( 'Sold out', 'dejoiy' ); ?></span>
		<?php endif; ?>
	</article>
	<?php
	return (string) ob_get_clean();
}

/**
 * @return array<int, array<string, string>>
 */
function dejoiy_quickmart_categories() {
	$shop = home_url( '/dejoiy-quick-mart/' );
	return array(
		array( 'slug' => 'fruits', 'label' => __( 'Fruits', 'dejoiy' ), 'icon' => '🍎', 'color' => '#FF6B6B' ),
		array( 'slug' => 'vegetables', 'label' => __( 'Vegetables', 'dejoiy' ), 'icon' => '🥬', 'color' => '#22C55E' ),
		array( 'slug' => 'dairy', 'label' => __( 'Dairy', 'dejoiy' ), 'icon' => '🥛', 'color' => '#38BDF8' ),
		array( 'slug' => 'snacks', 'label' => __( 'Snacks', 'dejoiy' ), 'icon' => '🍿', 'color' => '#FACC15' ),
		array( 'slug' => 'beverages', 'label' => __( 'Beverages', 'dejoiy' ), 'icon' => '🥤', 'color' => '#A78BFA' ),
		array( 'slug' => 'bakery', 'label' => __( 'Bakery', 'dejoiy' ), 'icon' => '🥐', 'color' => '#FB923C' ),
		array( 'slug' => 'personal-care', 'label' => __( 'Personal Care', 'dejoiy' ), 'icon' => '🧴', 'color' => '#EC4899' ),
		array( 'slug' => 'home-essentials', 'label' => __( 'Home Essentials', 'dejoiy' ), 'icon' => '🏠', 'color' => '#64748B' ),
		array( 'slug' => 'electronics', 'label' => __( 'Electronics', 'dejoiy' ), 'icon' => '📱', 'color' => '#0F172A' ),
	);
}

/**
 * @return string
 */
function dejoiy_quickmart_home_html() {
	$pin     = dejoiy_quickmart_get_pincode();
	$loc     = dejoiy_quickmart_get_location_label();
	$eta     = dejoiy_quickmart_eta_label();
	$shop    = add_query_arg( array( 'post_type' => 'product', 'product_cat' => 'quick-products' ), home_url( '/' ) );
	$cats    = dejoiy_quickmart_categories();
	$trending = dejoiy_quickmart_query_products( array( 'posts_per_page' => 10, 'orderby' => 'date' ) );
	$deals   = dejoiy_quickmart_query_products( array( 'posts_per_page' => 8, 'meta_key' => '_sale_price', 'meta_compare' => 'EXISTS' ) );

	ob_start();
	?>
	<div id="dejoiy-quickmart-app" class="dejoiy-quickmart-app">
		<section class="qm-hero">
			<div class="qm-hero__inner">
				<p class="qm-hero__eta"><?php echo esc_html( sprintf( /* translators: %s ETA */ __( 'Delivering in %s', 'dejoiy' ), $eta ) ); ?></p>
				<h1 class="qm-hero__title"><?php esc_html_e( 'Groceries & essentials at your door', 'dejoiy' ); ?></h1>
				<?php if ( $pin ) : ?>
					<p class="qm-hero__loc">📍 <?php echo esc_html( $loc ); ?></p>
				<?php else : ?>
					<button type="button" class="qm-hero__cta" data-qm-open-pin><?php esc_html_e( 'Enter delivery pincode', 'dejoiy' ); ?></button>
				<?php endif; ?>
			</div>
		</section>

		<section class="qm-cats" aria-label="<?php esc_attr_e( 'Categories', 'dejoiy' ); ?>">
			<div class="qm-cats__grid">
				<?php foreach ( $cats as $cat ) : ?>
					<a class="qm-cats__card" href="<?php echo esc_url( add_query_arg( 'qm_cat', $cat['slug'], home_url( '/dejoiy-quick-mart/' ) ) ); ?>" style="--qm-cat:<?php echo esc_attr( $cat['color'] ); ?>">
						<span class="qm-cats__icon" aria-hidden="true"><?php echo esc_html( $cat['icon'] ); ?></span>
						<span class="qm-cats__label"><?php echo esc_html( $cat['label'] ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="qm-shelf">
			<div class="qm-shelf__head">
				<h2><?php esc_html_e( 'Trending now', 'dejoiy' ); ?></h2>
				<a href="<?php echo esc_url( $shop ); ?>"><?php esc_html_e( 'See all', 'dejoiy' ); ?></a>
			</div>
			<div class="qm-shelf__track">
				<?php
				while ( $trending->have_posts() ) {
					$trending->the_post();
					$p = wc_get_product( get_the_ID() );
					if ( $p ) {
						echo dejoiy_quickmart_render_product_card( $p ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
				}
				wp_reset_postdata();
				?>
			</div>
		</section>

		<section class="qm-flash">
			<div class="qm-shelf__head">
				<h2><?php esc_html_e( 'Flash deals', 'dejoiy' ); ?></h2>
				<span class="qm-flash__timer" data-qm-flash-timer>02:59:59</span>
			</div>
			<div class="qm-shelf__track">
				<?php
				if ( $deals->have_posts() ) {
					while ( $deals->have_posts() ) {
						$deals->the_post();
						$p = wc_get_product( get_the_ID() );
						if ( $p ) {
							echo dejoiy_quickmart_render_product_card( $p ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						}
					}
					wp_reset_postdata();
				} else {
					while ( $trending->have_posts() ) {
						$trending->the_post();
						$p = wc_get_product( get_the_ID() );
						if ( $p ) {
							echo dejoiy_quickmart_render_product_card( $p ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						}
					}
					wp_reset_postdata();
				}
				?>
			</div>
		</section>

		<section class="qm-shelf">
			<div class="qm-shelf__head">
				<h2><?php esc_html_e( 'Daily essentials', 'dejoiy' ); ?></h2>
			</div>
			<div class="qm-shelf__grid">
				<?php
				$daily = dejoiy_quickmart_query_products( array( 'posts_per_page' => 8, 'offset' => 2 ) );
				while ( $daily->have_posts() ) {
					$daily->the_post();
					$p = wc_get_product( get_the_ID() );
					if ( $p ) {
						echo dejoiy_quickmart_render_product_card( $p ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
				}
				wp_reset_postdata();
				?>
			</div>
		</section>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Replace QuickMart page body.
 *
 * @param string $content Content.
 * @return string
 */
function dejoiy_quickmart_replace_page_content( $content ) {
	if ( ! dejoiy_quickmart_is_quickmart_page() ) {
		return $content;
	}
	if ( is_admin() ) {
		return $content;
	}
	static $done = false;
	if ( $done ) {
		return '';
	}
	$done = true;
	if ( function_exists( 'dejoiy_quickmart_is_launch_home' ) && dejoiy_quickmart_is_launch_home() ) {
		return function_exists( 'dejoiy_quick_launch_page_html' ) ? dejoiy_quick_launch_page_html() : $content;
	}
	if ( function_exists( 'dejoiy_quickmart_is_checkout_view' ) && dejoiy_quickmart_is_checkout_view() ) {
		return function_exists( 'dejoiy_quickmart_checkout_page_html' )
			? dejoiy_quickmart_checkout_page_html()
			: dejoiy_quickmart_home_html();
	}
	if ( function_exists( 'dejoiy_quickmart_is_cart_view' ) && dejoiy_quickmart_is_cart_view() ) {
		return function_exists( 'dejoiy_quickmart_cart_page_html' )
			? dejoiy_quickmart_cart_page_html()
			: dejoiy_quickmart_home_html();
	}
	if ( function_exists( 'dejoiy_quickmart_is_orders_view' ) && dejoiy_quickmart_is_orders_view() ) {
		return function_exists( 'dejoiy_quickmart_orders_page_html' )
			? dejoiy_quickmart_orders_page_html()
			: dejoiy_quickmart_home_html();
	}
	if ( function_exists( 'dejoiy_quickmart_is_search_view' ) && dejoiy_quickmart_is_search_view() ) {
		return function_exists( 'dejoiy_quickmart_search_page_html' )
			? dejoiy_quickmart_search_page_html()
			: dejoiy_quickmart_home_html();
	}
	return function_exists( 'dejoiy_quickmart_blinkit_home_html' )
		? dejoiy_quickmart_blinkit_home_html()
		: dejoiy_quickmart_home_html();
}
add_filter( 'the_content', 'dejoiy_quickmart_replace_page_content', 9999 );
add_filter( 'elementor/frontend/the_content', 'dejoiy_quickmart_replace_page_content', 9999 );

/**
 * Print QuickMart chrome (header, pin modal, cart drawer, footer).
 */
/** Legacy wrapper — Blinkit chrome in dejoiy-quickmart-ui.php */
function dejoiy_quickmart_print_chrome() {
	if ( function_exists( 'dejoiy_quickmart_print_blinkit_chrome' ) ) {
		dejoiy_quickmart_print_blinkit_chrome();
	}
}

function dejoiy_quickmart_print_footer() {
	if ( function_exists( 'dejoiy_quickmart_is_launch_home' ) && dejoiy_quickmart_is_launch_home() ) {
		return;
	}
	if ( ! dejoiy_quickmart_is_app_page() ) {
		return;
	}
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;

	$home = home_url( '/dejoiy-quick-mart/' );
	$cats = dejoiy_quickmart_categories();

	?>
	<footer class="qm-footer" role="contentinfo">
		<div class="qm-footer__grid">
			<div>
				<strong>QuickMart</strong>
				<p><?php esc_html_e( 'Quick commerce by DEJOIY', 'dejoiy' ); ?></p>
			</div>
			<div>
				<strong><?php esc_html_e( 'Categories', 'dejoiy' ); ?></strong>
				<ul>
					<?php foreach ( array_slice( $cats, 0, 5 ) as $cat ) : ?>
						<li><a href="<?php echo esc_url( add_query_arg( 'qm_cat', $cat['slug'], $home ) ); ?>"><?php echo esc_html( $cat['label'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<div>
				<strong><?php esc_html_e( 'Support', 'dejoiy' ); ?></strong>
				<ul>
					<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact', 'dejoiy' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/faq/' ) ); ?>"><?php esc_html_e( 'FAQs', 'dejoiy' ); ?></a></li>
				</ul>
			</div>
		</div>
		<p class="qm-footer__copy">&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> DEJOIY QuickMart</p>
	</footer>
	<?php
}
function dejoiy_quickmart_maybe_print_blinkit_chrome() {
	if ( function_exists( 'dejoiy_quickmart_is_launch_home' ) && dejoiy_quickmart_is_launch_home() ) {
		return;
	}
	if ( function_exists( 'dejoiy_quickmart_print_blinkit_chrome' ) ) {
		dejoiy_quickmart_print_blinkit_chrome();
	}
}
add_action( 'wp_body_open', 'dejoiy_quickmart_maybe_print_blinkit_chrome', 3 );
add_action( 'etheme_after_body_open', 'dejoiy_quickmart_maybe_print_blinkit_chrome', 3 );
add_action( 'wp_footer', 'dejoiy_quickmart_print_footer', 5 );

/**
 * @param array<int, string> $classes Body classes.
 * @return array<int, string>
 */
function dejoiy_quickmart_body_class( $classes ) {
	if ( function_exists( 'dejoiy_quickmart_is_launch_home' ) && dejoiy_quickmart_is_launch_home() ) {
		return $classes;
	}
	if ( dejoiy_quickmart_is_app_page() ) {
		$classes[] = 'dejoiy-quickmart-app';
		if ( function_exists( 'dejoiy_quickmart_is_search_view' ) && dejoiy_quickmart_is_search_view() ) {
			$classes[] = 'dejoiy-quickmart-search-view';
		}
		$classes[] = 'dejoiy-mobile-os-off';
	}
	if ( dejoiy_quickmart_is_cart_context() ) {
		$classes[] = 'dejoiy-quickmart-checkout-flow';
	}
	return $classes;
}
add_filter( 'body_class', 'dejoiy_quickmart_body_class', 24 );

/**
 * Enqueue QuickMart assets.
 */
function dejoiy_quickmart_assets() {
	if ( function_exists( 'dejoiy_quickmart_is_launch_home' ) && dejoiy_quickmart_is_launch_home() ) {
		return;
	}
	if ( ! dejoiy_quickmart_is_app_page() && ! dejoiy_quickmart_is_cart_context() ) {
		return;
	}
	$uri = get_stylesheet_directory_uri();
	$dir = get_stylesheet_directory();
	$css = $dir . '/dejoiy-quickmart.css';
	$js  = $dir . '/dejoiy-quickmart.js';
	if ( is_readable( $css ) ) {
		wp_enqueue_style( 'dejoiy-quickmart', $uri . '/dejoiy-quickmart.css', array(), (string) filemtime( $css ) );
	}
	$blinkit = $dir . '/dejoiy-quickmart-blinkit.css';
	if ( is_readable( $blinkit ) ) {
		wp_enqueue_style(
			'dejoiy-quickmart-blinkit',
			$uri . '/dejoiy-quickmart-blinkit.css',
			array( 'dejoiy-quickmart' ),
			(string) filemtime( $blinkit )
		);
	}
	if ( is_readable( $js ) ) {
		wp_enqueue_script( 'dejoiy-quickmart', $uri . '/dejoiy-quickmart.js', array( 'jquery' ), (string) filemtime( $js ), true );
		wp_localize_script(
			'dejoiy-quickmart',
			'dejoiyQuickmart',
			array(
				'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
				'nonce'     => wp_create_nonce( 'dejoiy_quickmart' ),
				'cartUrl'   => dejoiy_quickmart_get_cart_url(),
				'checkoutUrl' => dejoiy_quickmart_get_checkout_url(),
				'homeUrl'   => home_url( '/dejoiy-quick-mart/' ),
				'searchUrl' => function_exists( 'dejoiy_quickmart_search_url' ) ? dejoiy_quickmart_search_url() : home_url( '/dejoiy-quick-mart/?qm_view=search' ),
				'isSearchView' => ( function_exists( 'dejoiy_quickmart_is_search_view' ) && dejoiy_quickmart_is_search_view() ) ? 1 : 0,
				'pincode'   => dejoiy_quickmart_get_pincode(),
				'cartCount' => dejoiy_quickmart_get_cart_count(),
				'hasPin'    => dejoiy_quickmart_get_pincode() ? 1 : 0,
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'dejoiy_quickmart_assets', 10070 );

/**
 * Add flow flag to add-to-cart redirect on QuickMart.
 *
 * @param string $url URL.
 * @return string
 */
function dejoiy_quickmart_add_to_cart_redirect( $url ) {
	if ( dejoiy_quickmart_is_app_page() || dejoiy_quickmart_is_add_request()
		|| ( function_exists( 'dejoiy_quickmart_is_product_surface' ) && dejoiy_quickmart_is_product_surface() ) ) {
		return dejoiy_quickmart_get_cart_url();
	}
	return $url;
}
add_filter( 'woocommerce_add_to_cart_redirect', 'dejoiy_quickmart_add_to_cart_redirect', 15 );


/**
 * @param string     $url     Add to cart URL.
 * @param WC_Product $product Product.
 * @return string
 */
function dejoiy_quickmart_product_add_to_cart_url( $url, $product ) {
	if ( ( dejoiy_quickmart_is_app_page()
		|| ( function_exists( 'dejoiy_quickmart_is_product_surface' ) && dejoiy_quickmart_is_product_surface() ) )
		&& $product && dejoiy_quickmart_is_product( $product->get_id() ) ) {
		return add_query_arg( DEJOIY_QUICKMART_FLOW, '1', $url );
	}
	return $url;
}
add_filter( 'woocommerce_product_add_to_cart_url', 'dejoiy_quickmart_product_add_to_cart_url', 15, 2 );

/**
 * @param array<string, string> $fragments Fragments.
 * @return array<string, string>
 */
function dejoiy_quickmart_cart_fragments( $fragments ) {
	$fragments['qm_cart_count'] = (string) dejoiy_quickmart_get_cart_count();
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'dejoiy_quickmart_cart_fragments' );
