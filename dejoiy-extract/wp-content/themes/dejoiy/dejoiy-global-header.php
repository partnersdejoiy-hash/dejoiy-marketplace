<?php
/**
 * DEJOIY Global Header OS — Unified canonical header for all pages.
 *
 * Replaces: desktop marketplace header, mobile OS header, header OS v4, site chrome.
 * Works on: Homepage, Shop, Categories, Product, Cart, Checkout, Account, Nexus, Studio, QuickMart, Renew, Hire.
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'DEJOIY_GH_VERSION' ) ) {
	define( 'DEJOIY_GH_VERSION', '2.0.0' );
}

/* ---------------------------------------------------------------
   ENABLE / DISABLE
   --------------------------------------------------------------- */

function dejoiy_global_header_enabled() {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return false;
	}
	if ( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) {
		return false;
	}
	// Disable on dedicated ecosystem pages that have their own chrome
	if ( function_exists( 'dejoiy_mobile_os_is_dedicated_page' ) && dejoiy_mobile_os_is_dedicated_page() ) {
		return false;
	}
	// Allow filter
	return (bool) apply_filters( 'dejoiy_global_header_enabled', true );
}

/* ---------------------------------------------------------------
   ICONS — Lucide-style inline SVGs (consistent stroke family)
   --------------------------------------------------------------- */

function dejoiy_gh_icon( $name ) {
	$icons = array(
		'search'     => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>',
		'user'       => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
		'heart'      => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>',
		'cart'       => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>',
		'menu'       => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>',
		'chevron'    => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>',
		'chevron-r'  => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>',
		'x'          => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>',
		'package'    => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>',
		'shield'     => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/></svg>',
		'globe'      => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>',
		'map-pin'    => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>',
		'store'      => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"/><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"/><path d="M2 7h20"/></svg>',
		'truck'      => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>',
		'bolt'       => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg>',
		'book'       => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>',
		'palette'    => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/></svg>',
		'recycle'    => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 19H4.815a1.83 1.83 0 0 1-1.57-.881 1.785 1.785 0 0 1-.004-1.784L7.196 9.5"/><path d="M11 19h8.203a1.83 1.83 0 0 0 1.556-.89 1.784 1.784 0 0 0 0-1.775l-1.226-2.12"/><path d="m14 16-3 3 3 3"/><path d="M8.293 13.596 7.196 9.5 3.1 10.598"/><path d="m9.344 5.811 1.093-1.892A1.83 1.83 0 0 1 11.985 3a1.784 1.784 0 0 1 1.546.888l3.943 6.843"/><path d="m13.378 9.633 4.096 1.098 1.097-4.096"/></svg>',
		'briefcase'  => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/><rect width="20" height="14" x="2" y="6" rx="2"/></svg>',
		'joi'        => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a4 4 0 0 0-4 4c0 2 1 3 1 5h6c0-2 1-3 1-5a4 4 0 0 0-4-4z"/><path d="M10 14h4"/><path d="M10 18h4"/><path d="M11 21h2"/></svg>',
		'arrow-right'=> '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>',
	);
	return isset( $icons[ $name ] ) ? $icons[ $name ] : '';
}

/* ---------------------------------------------------------------
   NAVIGATION DATA
   --------------------------------------------------------------- */

function dejoiy_gh_nav_items() {
	$shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
	return array(
		array(
			'id'    => 'shop',
			'label' => __( 'Shop', 'dejoiy' ),
			'url'   => $shop,
			'icon'  => 'store',
		),
		array(
			'id'    => 'nexus',
			'label' => __( 'Nexus', 'dejoiy' ),
			'url'   => home_url( '/dejoiy-library/?dejoiy_library=1' ),
			'icon'  => 'book',
		),
		array(
			'id'    => 'studio',
			'label' => __( 'Custom Studio', 'dejoiy' ),
			'url'   => home_url( '/dejoiy-custom-studio/' ),
			'icon'  => 'palette',
		),
		array(
			'id'    => 'quickmart',
			'label' => __( 'QuickMart', 'dejoiy' ),
			'url'   => home_url( '/dejoiy-quick-mart/' ),
			'icon'  => 'bolt',
		),
		array(
			'id'    => 'renew',
			'label' => __( 'Renew', 'dejoiy' ),
			'url'   => home_url( '/dejoiy-refurbished/' ),
			'icon'  => 'recycle',
		),
		array(
			'id'    => 'hire',
			'label' => __( 'Hire', 'dejoiy' ),
			'url'   => home_url( '/dejoiy-services/' ),
			'icon'  => 'briefcase',
		),
	);
}

function dejoiy_gh_world_colors() {
	return array(
		'shop'     => '#2563EB',
		'nexus'    => '#7C3AED',
		'studio'   => '#EC4899',
		'quickmart'=> '#16A34A',
		'renew'    => '#0D9488',
		'hire'     => '#EA580C',
	);
}

function dejoiy_gh_account_menu() {
	$account = home_url( '/my-account/' );
	$items   = array(
		array( 'key' => 'account', 'label' => __( 'My Account', 'dejoiy' ), 'url' => $account ),
		array( 'key' => 'orders',  'label' => __( 'My Orders', 'dejoiy' ),  'url' => function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'orders' ) : $account ),
		array( 'key' => 'wishlist','label' => __( 'Wishlist', 'dejoiy' ),    'url' => home_url( '/my-account/?et-wishlist-page' ) ),
		array( 'key' => 'settings','label' => __( 'Settings', 'dejoiy' ),    'url' => function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'edit-account' ) : $account ),
	);
	if ( is_user_logged_in() ) {
		$items[] = array( 'key' => 'logout', 'label' => __( 'Sign Out', 'dejoiy' ), 'url' => wp_logout_url( home_url( '/' ) ) );
	} else {
		$items[] = array( 'key' => 'login', 'label' => __( 'Sign In', 'dejoiy' ), 'url' => $account );
	}
	return $items;
}

function dejoiy_gh_customer_name() {
	if ( ! is_user_logged_in() ) {
		return '';
	}
	$user = wp_get_current_user();
	$name = trim( (string) $user->first_name );
	if ( '' === $name ) {
		$name = trim( (string) $user->display_name );
	}
	return $name;
}

function dejoiy_gh_cart_count() {
	if ( ! function_exists( 'WC' ) ) {
		return 0;
	}
	if ( is_null( WC()->cart ) && function_exists( 'wc_load_cart' ) ) {
		wc_load_cart();
	}
	if ( ! WC()->cart ) {
		return 0;
	}
	$qty = 0;
	foreach ( WC()->cart->get_cart() as $key => $item ) {
		$visible = apply_filters( 'woocommerce_cart_item_visible', true, $item, $key );
		if ( ! $visible ) {
			continue;
		}
		$qty += isset( $item['quantity'] ) ? (int) $item['quantity'] : 0;
	}
	return max( 0, $qty );
}

function dejoiy_gh_delivery_label() {
	if ( function_exists( 'WC' ) && WC()->customer ) {
		$city  = trim( (string) WC()->customer->get_shipping_city() );
		$state = trim( (string) WC()->customer->get_shipping_state() );
		if ( $city ) {
			return $state ? $city . ', ' . $state : $city;
		}
	}
	return __( 'Set location', 'dejoiy' );
}

/* ---------------------------------------------------------------
   CATEGORIES for mega-menu
   --------------------------------------------------------------- */

function dejoiy_gh_main_categories() {
	static $cached = null;
	if ( null !== $cached ) {
		return $cached;
	}
	$cached = array();
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return $cached;
	}
	$terms = get_terms( array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => false,
		'parent'     => 0,
		'orderby'    => 'name',
		'order'      => 'ASC',
		'number'     => 24,
	) );
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return $cached;
	}
	$skip = array( 'uncategorized', 'uncategorised' );
	foreach ( $terms as $term ) {
		if ( in_array( $term->slug, $skip, true ) ) {
			continue;
		}
		$link = get_term_link( $term );
		if ( is_wp_error( $link ) ) {
			continue;
		}
		$cached[] = array(
			'slug'  => $term->slug,
			'label' => $term->name,
			'url'   => $link,
			'count' => (int) $term->count,
		);
	}
	return $cached;
}

/* ---------------------------------------------------------------
   ACTIVE STATE detection
   --------------------------------------------------------------- */

function dejoiy_gh_is_active( $id ) {
	$uri = strtolower( (string) wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) );
	switch ( $id ) {
		case 'shop':
			return ( function_exists( 'is_shop' ) && is_shop() )
				|| ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() )
				|| ( function_exists( 'is_product' ) && is_product() );
		case 'nexus':
			return false !== strpos( $uri, 'dejoiy-library' ) || false !== strpos( $uri, 'dejoiy_library' );
		case 'studio':
			return false !== strpos( $uri, 'dejoiy-custom-studio' );
		case 'quickmart':
			return false !== strpos( $uri, 'dejoiy-quick-mart' );
		case 'renew':
			return false !== strpos( $uri, 'dejoiy-refurbished' );
		case 'hire':
			return false !== strpos( $uri, 'dejoiy-services' );
	}
	return false;
}

/* ---------------------------------------------------------------
   ENQUEUE ASSETS
   --------------------------------------------------------------- */

function dejoiy_global_header_enqueue() {
	if ( ! dejoiy_global_header_enabled() ) {
		return;
	}
	$dir = get_stylesheet_directory();
	$uri = get_stylesheet_directory_uri();
	$css = $dir . '/dejoiy-global-header.css';
	$js  = $dir . '/dejoiy-global-header.js';
	$ver = DEJOIY_GH_VERSION;
	if ( is_readable( $css ) ) {
		wp_enqueue_style( 'dejoiy-global-header', $uri . '/dejoiy-global-header.css', array(), $ver . '.' . (string) filemtime( $css ) );
	}
	$mobile_css = $dir . '/dejoiy-mobile-redesign.css';
	if ( is_readable( $mobile_css ) ) {
		wp_enqueue_style( 'dejoiy-mobile-redesign', $uri . '/dejoiy-mobile-redesign.css', array( 'dejoiy-global-header' ), $ver . '.' . (string) filemtime( $mobile_css ) );
	}
	$app_layout = $dir . '/dejoiy-mobile-app-layout.css';
	if ( is_readable( $app_layout ) ) {
		wp_enqueue_style( 'dejoiy-mobile-app-layout', $uri . '/dejoiy-mobile-app-layout.css', array( 'dejoiy-mobile-redesign' ), $ver . '.' . (string) filemtime( $app_layout ) );
	}
	if ( is_readable( $js ) ) {
		wp_enqueue_script( 'dejoiy-global-header', $uri . '/dejoiy-global-header.js', array(), $ver . '.' . (string) filemtime( $js ), true );
		wp_localize_script( 'dejoiy-global-header', 'dejoiyGH', array(
			'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
			'nonce'        => wp_create_nonce( 'dejoiy_header_os_v4' ),
			'isLoggedIn'   => is_user_logged_in() ? 1 : 0,
			'customerName' => dejoiy_gh_customer_name(),
			'cartCount'    => dejoiy_gh_cart_count(),
			'cartUrl'      => function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' ),
			'checkoutUrl'  => function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/checkout/' ),
			'accountUrl'   => home_url( '/my-account/' ),
			'ordersUrl'    => function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'orders' ) : home_url( '/my-account/' ),
			'deliveryUrl'  => function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'edit-address' ) : home_url( '/my-account/' ),
			'deliveryLabel'=> dejoiy_gh_delivery_label(),
			'shopUrl'      => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ),
			'nav'          => dejoiy_gh_nav_items(),
			'worldColors'  => dejoiy_gh_world_colors(),
			'accountMenu'  => dejoiy_gh_account_menu(),
			'categories'   => array_slice( dejoiy_gh_main_categories(), 0, 16 ),
			'i18n'         => array(
				'searchPlaceholder' => __( 'Search products, brands, and more…', 'dejoiy' ),
				'noResults'         => __( 'No results found', 'dejoiy' ),
				'browseAll'         => __( 'Browse All', 'dejoiy' ),
				'viewAll'           => __( 'View all', 'dejoiy' ),
				'close'             => __( 'Close', 'dejoiy' ),
				'searching'         => __( 'Searching…', 'dejoiy' ),
			),
		) );
	}
}
add_action( 'wp_enqueue_scripts', 'dejoiy_global_header_enqueue', 10100 );

/* ---------------------------------------------------------------
   BODY CLASS
   --------------------------------------------------------------- */

function dejoiy_gh_body_class( $classes ) {
	if ( ! dejoiy_global_header_enabled() ) {
		return $classes;
	}
	$classes[] = 'dejoiy-gh';
	return $classes;
}
add_filter( 'body_class', 'dejoiy_gh_body_class', 22 );

/* ---------------------------------------------------------------
   PRINT HEADER — Desktop (≥1025px)
   --------------------------------------------------------------- */

function dejoiy_gh_desktop_header_html() {
	$home      = home_url( '/' );
	$shop      = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
	$cart      = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );
	$account   = home_url( '/my-account/' );
	$orders    = function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'orders' ) : $account;
	$delivery  = function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'edit-address' ) : $account;
	$cart_ct   = dejoiy_gh_cart_count();
	$nav       = dejoiy_gh_nav_items();
	$greet     = is_user_logged_in()
		? ( dejoiy_gh_customer_name() ? sprintf( __( 'Hello, %s', 'dejoiy' ), dejoiy_gh_customer_name() ) : __( 'Hello', 'dejoiy' ) )
		: __( 'Hello, Sign In', 'dejoiy' );
	$logo_id   = (int) get_theme_mod( 'custom_logo' );
	$logo_html = '';
	if ( $logo_id > 0 ) {
		$img = wp_get_attachment_image( $logo_id, array( 200, 72 ), false, array( 'class' => 'gh-logo__img', 'alt' => get_bloginfo( 'name', 'display' ), 'loading' => 'eager', 'decoding' => 'async' ) );
		if ( $img ) {
			$logo_html = $img;
		}
	}
	if ( ! $logo_html ) {
		$logo_html = '<span class="gh-logo__mark">D</span><span class="gh-logo__word">DEJOIY</span>';
	}
	ob_start();
	?>
	<header id="dejoiy-global-header" class="gh" role="banner" data-gh-header>
		<!-- Desktop: ≥1025px -->
		<div class="gh-desktop" aria-hidden="false">
			<!-- Utility bar -->
			<div class="gh-util" role="navigation" aria-label="<?php esc_attr_e( 'Utility', 'dejoiy' ); ?>">
				<div class="gh-util__in">
					<div class="gh-util__left">
						<a class="gh-util__link" href="<?php echo esc_url( $delivery ); ?>"><?php echo dejoiy_gh_icon( 'map-pin' ); ?> <span><?php echo esc_html( dejoiy_gh_delivery_label() ); ?></span></a>
					</div>
					<div class="gh-util__right">
						<a class="gh-util__link" href="<?php echo esc_url( home_url( '/sell-on-dejoiy/' ) ); ?>"><?php esc_html_e( 'Sell on DEJOIY', 'dejoiy' ); ?></a>
						<a class="gh-util__link" href="<?php echo esc_url( home_url( '/deals/' ) ); ?>"><?php esc_html_e( 'Deals', 'dejoiy' ); ?></a>
						<a class="gh-util__link" href="<?php echo esc_url( $orders ); ?>"><?php esc_html_e( 'Track Order', 'dejoiy' ); ?></a>
						<a class="gh-util__link" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Support', 'dejoiy' ); ?></a>
					</div>
				</div>
			</div>
			<!-- Main header -->
			<div class="gh-main" data-gh-main>
				<div class="gh-main__in">
					<!-- Logo -->
					<a class="gh-logo" href="<?php echo esc_url( $home ); ?>" aria-label="<?php esc_attr_e( 'DEJOIY Home', 'dejoiy' ); ?>">
						<?php echo $logo_html; // phpcs:ignore ?>
					</a>
					<!-- Search -->
					<form class="gh-search" action="<?php echo esc_url( $shop ); ?>" method="get" role="search" data-gh-search>
						<input type="hidden" name="post_type" value="product" />
						<label class="screen-reader-text" for="gh-search-input"><?php esc_html_e( 'Search DEJOIY', 'dejoiy' ); ?></label>
						<div class="gh-search__wrap">
							<?php echo dejoiy_gh_icon( 'search' ); // phpcs:ignore ?>
							<input id="gh-search-input" class="gh-search__input" type="search" name="s" placeholder="<?php esc_attr_e( 'Search products, brands, and more…', 'dejoiy' ); ?>" autocomplete="off" data-gh-search-input />
						</div>
						<button type="submit" class="gh-search__btn" aria-label="<?php esc_attr_e( 'Search', 'dejoiy' ); ?>">
							<?php echo dejoiy_gh_icon( 'search' ); // phpcs:ignore ?>
							<span><?php esc_html_e( 'Search', 'dejoiy' ); ?></span>
						</button>
						<!-- Search panel -->
						<div class="gh-search__panel" id="gh-search-panel" hidden>
							<div class="gh-search__panel-head">
								<span class="gh-search__panel-label"><?php esc_html_e( 'JOI Search', 'dejoiy' ); ?></span>
								<button type="button" class="gh-search__close" data-gh-search-close aria-label="<?php esc_attr_e( 'Close search', 'dejoiy' ); ?>">
									<?php echo dejoiy_gh_icon( 'x' ); // phpcs:ignore ?>
								</button>
							</div>
							<div class="gh-search__results" id="gh-search-results" hidden></div>
						</div>
					</form>
					<!-- Actions -->
					<div class="gh-actions">
						<!-- JOI -->
						<a class="gh-action gh-action--joi" href="<?php echo esc_url( home_url( '/joi/' ) ); ?>" aria-label="<?php esc_attr_e( 'Ask JOI', 'dejoiy' ); ?>" data-gh-joi>
							<?php echo dejoiy_gh_icon( 'joi' ); // phpcs:ignore ?>
							<span class="gh-action__label">JOI</span>
							<span class="gh-action__glow" aria-hidden="true"></span>
						</a>
						<!-- Account -->
						<div class="gh-action gh-action--account" data-gh-dropdown-wrap>
							<button type="button" class="gh-action__btn" data-gh-account-toggle aria-expanded="false" aria-haspopup="true">
								<?php echo dejoiy_gh_icon( 'user' ); // phpcs:ignore ?>
								<span class="gh-action__text">
									<span class="gh-action__kicker"><?php echo esc_html( $greet ); ?></span>
									<span class="gh-action__label"><?php esc_html_e( 'Account', 'dejoiy' ); ?></span>
								</span>
							</button>
							<div class="gh-dropdown" data-gh-account-menu hidden>
								<?php foreach ( dejoiy_gh_account_menu() as $item ) : ?>
									<a class="gh-dropdown__item<?php echo 'logout' === $item['key'] ? ' gh-dropdown__item--danger' : ''; ?>" href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a>
								<?php endforeach; ?>
							</div>
						</div>
						<!-- Wishlist -->
						<a class="gh-action" href="<?php echo esc_url( home_url( '/my-account/?et-wishlist-page' ) ); ?>" aria-label="<?php esc_attr_e( 'Wishlist', 'dejoiy' ); ?>">
							<?php echo dejoiy_gh_icon( 'heart' ); // phpcs:ignore ?>
							<span class="gh-action__label"><?php esc_html_e( 'Wishlist', 'dejoiy' ); ?></span>
						</a>
						<!-- Cart -->
						<a class="gh-action gh-action--cart" href="<?php echo esc_url( $cart ); ?>" aria-label="<?php esc_attr_e( 'Cart', 'dejoiy' ); ?>">
							<span class="gh-action__icon-wrap">
								<?php echo dejoiy_gh_icon( 'cart' ); // phpcs:ignore ?>
								<?php if ( $cart_ct > 0 ) : ?>
									<span class="gh-badge" data-gh-cart-badge><?php echo esc_html( $cart_ct > 99 ? '99+' : (string) $cart_ct ); ?></span>
								<?php else : ?>
									<span class="gh-badge" data-gh-cart-badge hidden>0</span>
								<?php endif; ?>
							</span>
							<span class="gh-action__label"><?php esc_html_e( 'Cart', 'dejoiy' ); ?></span>
						</a>
					</div>
				</div>
			</div>
			<!-- Navigation bar -->
			<nav class="gh-nav" aria-label="<?php esc_attr_e( 'Marketplace', 'dejoiy' ); ?>">
				<div class="gh-nav__in">
					<!-- Browse All -->
					<div class="gh-browse" data-gh-dropdown-wrap>
						<button type="button" class="gh-browse__btn" data-gh-browse-toggle aria-expanded="false" aria-haspopup="true">
							<?php echo dejoiy_gh_icon( 'menu' ); // phpcs:ignore ?>
							<span><?php esc_html_e( 'Browse All', 'dejoiy' ); ?></span>
							<?php echo dejoiy_gh_icon( 'chevron' ); // phpcs:ignore ?>
						</button>
						<!-- Mega Menu -->
						<div class="gh-mega" id="gh-mega-menu" data-gh-mega hidden>
							<div class="gh-mega__grid">
								<!-- Worlds column -->
								<div class="gh-mega__worlds">
									<h3 class="gh-mega__heading"><?php esc_html_e( 'DEJOIY Worlds', 'dejoiy' ); ?></h3>
									<?php foreach ( dejoiy_gh_nav_items() as $item ) : ?>
										<a class="gh-mega__world" href="<?php echo esc_url( $item['url'] ); ?>" style="--gh-world:<?php echo esc_attr( dejoiy_gh_world_colors()[ $item['id'] ] ?? '#2563EB' ); ?>">
											<span class="gh-mega__world-icon"><?php echo dejoiy_gh_icon( $item['icon'] ); // phpcs:ignore ?></span>
											<span class="gh-mega__world-label"><?php echo esc_html( $item['label'] ); ?></span>
											<span class="gh-mega__world-arrow"><?php echo dejoiy_gh_icon( 'chevron-r' ); // phpcs:ignore ?></span>
										</a>
									<?php endforeach; ?>
								</div>
								<!-- Categories column -->
								<div class="gh-mega__cats">
									<h3 class="gh-mega__heading"><?php esc_html_e( 'Shop by Category', 'dejoiy' ); ?></h3>
									<div class="gh-mega__cat-grid">
										<?php foreach ( dejoiy_gh_main_categories() as $cat ) : ?>
											<a class="gh-mega__cat" href="<?php echo esc_url( $cat['url'] ); ?>"><?php echo esc_html( $cat['label'] ); ?></a>
										<?php endforeach; ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!-- Nav links -->
					<div class="gh-nav__links">
						<?php foreach ( $nav as $item ) :
							$active = dejoiy_gh_is_active( $item['id'] );
						?>
							<a class="gh-nav__link<?php echo $active ? ' is-active' : ''; ?>" href="<?php echo esc_url( $item['url'] ); ?>"<?php echo $active ? ' aria-current="page"' : ''; ?>>
								<?php echo dejoiy_gh_icon( $item['icon'] ); // phpcs:ignore ?>
								<span><?php echo esc_html( $item['label'] ); ?></span>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			</nav>
		</div>
		<!-- Mobile: ≤1024px -->
		<div class="gh-mobile" aria-hidden="true">
			<!-- Top row: logo + actions -->
			<div class="gh-m-top">
				<button type="button" class="gh-m-browse" data-gh-m-browse aria-label="<?php esc_attr_e( 'Browse', 'dejoiy' ); ?>">
					<?php echo dejoiy_gh_icon( 'menu' ); // phpcs:ignore ?>
				</button>
				<a class="gh-logo gh-logo--sm" href="<?php echo esc_url( $home ); ?>" aria-label="<?php esc_attr_e( 'DEJOIY Home', 'dejoiy' ); ?>">
					<?php echo $logo_html; // phpcs:ignore ?>
				</a>
				<div class="gh-m-actions">
					<a class="gh-m-action" href="<?php echo esc_url( $account ); ?>" aria-label="<?php esc_attr_e( 'Account', 'dejoiy' ); ?>">
						<?php echo dejoiy_gh_icon( 'user' ); // phpcs:ignore ?>
					</a>
					<a class="gh-m-action gh-m-action--cart" href="<?php echo esc_url( $cart ); ?>" aria-label="<?php esc_attr_e( 'Cart', 'dejoiy' ); ?>">
						<?php echo dejoiy_gh_icon( 'cart' ); // phpcs:ignore ?>
						<?php if ( $cart_ct > 0 ) : ?>
							<span class="gh-badge gh-badge--sm" data-gh-m-cart-badge><?php echo esc_html( $cart_ct > 99 ? '99+' : (string) $cart_ct ); ?></span>
						<?php endif; ?>
					</a>
				</div>
			</div>
			<!-- Search row -->
			<div class="gh-m-search">
				<label class="gh-m-search__wrap" for="gh-m-search-input">
					<?php echo dejoiy_gh_icon( 'search' ); // phpcs:ignore ?>
					<input id="gh-m-search-input" class="gh-m-search__input" type="search" name="s" placeholder="<?php esc_attr_e( 'Search DEJOIY…', 'dejoiy' ); ?>" autocomplete="off" data-gh-m-search-input />
					<input type="hidden" name="post_type" value="product" />
				</label>
			</div>
			<!-- World chips -->
			<div class="gh-m-chips" data-gh-m-chips>
				<?php foreach ( dejoiy_gh_nav_items() as $item ) :
					$active = dejoiy_gh_is_active( $item['id'] );
				?>
					<a class="gh-m-chip<?php echo $active ? ' is-active' : ''; ?>" href="<?php echo esc_url( $item['url'] ); ?>" style="--gh-world:<?php echo esc_attr( dejoiy_gh_world_colors()[ $item['id'] ] ?? '#2563EB' ); ?>">
						<?php echo dejoiy_gh_icon( $item['icon'] ); // phpcs:ignore ?>
						<span><?php echo esc_html( $item['label'] ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
		<!-- Mobile Bottom Nav -->
		<nav class="gh-m-bottom" aria-label="<?php esc_attr_e( 'Primary', 'dejoiy' ); ?>">
			<a href="<?php echo esc_url( $home ); ?>" class="gh-m-bottom__item<?php echo is_front_page() ? ' is-active' : ''; ?>">
				<span class="gh-m-bottom__icon"><?php echo dejoiy_gh_icon( 'search' ); // phpcs:ignore ?></span>
				<small><?php esc_html_e( 'Home', 'dejoiy' ); ?></small>
			</a>
			<a href="<?php echo esc_url( $shop ); ?>" class="gh-m-bottom__item<?php echo ( function_exists( 'is_shop' ) && is_shop() ) ? ' is-active' : ''; ?>">
				<span class="gh-m-bottom__icon"><?php echo dejoiy_gh_icon( 'store' ); // phpcs:ignore ?></span>
				<small><?php esc_html_e( 'Shop', 'dejoiy' ); ?></small>
			</a>
			<a href="<?php echo esc_url( home_url( '/dejoiy-library/?dejoiy_library=1' ) ); ?>" class="gh-m-bottom__item<?php echo dejoiy_gh_is_active( 'nexus' ) ? ' is-active' : ''; ?>">
				<span class="gh-m-bottom__icon"><?php echo dejoiy_gh_icon( 'book' ); // phpcs:ignore ?></span>
				<small><?php esc_html_e( 'Nexus', 'dejoiy' ); ?></small>
			</a>
			<a href="<?php echo esc_url( home_url( '/my-account/?et-wishlist-page' ) ); ?>" class="gh-m-bottom__item">
				<span class="gh-m-bottom__icon"><?php echo dejoiy_gh_icon( 'heart' ); // phpcs:ignore ?></span>
				<small><?php esc_html_e( 'Wishlist', 'dejoiy' ); ?></small>
			</a>
			<a href="<?php echo esc_url( $account ); ?>" class="gh-m-bottom__item<?php echo ( function_exists( 'is_account_page' ) && is_account_page() ) ? ' is-active' : ''; ?>">
				<span class="gh-m-bottom__icon"><?php echo dejoiy_gh_icon( 'user' ); // phpcs:ignore ?></span>
				<small><?php esc_html_e( 'Account', 'dejoiy' ); ?></small>
			</a>
		</nav>
		<!-- Mobile Drawer -->
		<div class="gh-m-drawer" id="gh-m-drawer" hidden aria-hidden="true">
			<div class="gh-m-drawer__backdrop" data-gh-m-close></div>
			<div class="gh-m-drawer__panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Navigation', 'dejoiy' ); ?>">
				<div class="gh-m-drawer__head">
					<span class="gh-m-drawer__title"><?php esc_html_e( 'Explore DEJOIY', 'dejoiy' ); ?></span>
					<button type="button" class="gh-m-drawer__close" data-gh-m-close aria-label="<?php esc_attr_e( 'Close', 'dejoiy' ); ?>">
						<?php echo dejoiy_gh_icon( 'x' ); // phpcs:ignore ?>
					</button>
				</div>
				<div class="gh-m-drawer__body">
					<div class="gh-m-drawer__section">
						<h3 class="gh-m-drawer__heading"><?php esc_html_e( 'Worlds', 'dejoiy' ); ?></h3>
						<?php foreach ( dejoiy_gh_nav_items() as $item ) : ?>
							<a class="gh-m-drawer__link" href="<?php echo esc_url( $item['url'] ); ?>" style="--gh-world:<?php echo esc_attr( dejoiy_gh_world_colors()[ $item['id'] ] ?? '#2563EB' ); ?>">
								<span class="gh-m-drawer__link-icon"><?php echo dejoiy_gh_icon( $item['icon'] ); // phpcs:ignore ?></span>
								<span class="gh-m-drawer__link-label"><?php echo esc_html( $item['label'] ); ?></span>
								<span class="gh-m-drawer__link-arrow"><?php echo dejoiy_gh_icon( 'chevron-r' ); // phpcs:ignore ?></span>
							</a>
						<?php endforeach; ?>
					</div>
					<div class="gh-m-drawer__section">
						<h3 class="gh-m-drawer__heading"><?php esc_html_e( 'Categories', 'dejoiy' ); ?></h3>
						<?php foreach ( array_slice( dejoiy_gh_main_categories(), 0, 12 ) as $cat ) : ?>
							<a class="gh-m-drawer__cat" href="<?php echo esc_url( $cat['url'] ); ?>"><?php echo esc_html( $cat['label'] ); ?></a>
						<?php endforeach; ?>
					</div>
					<div class="gh-m-drawer__section">
						<?php foreach ( dejoiy_gh_account_menu() as $item ) : ?>
							<a class="gh-m-drawer__link" href="<?php echo esc_url( $item['url'] ); ?>">
								<span class="gh-m-drawer__link-label"><?php echo esc_html( $item['label'] ); ?></span>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
		<!-- Mobile Search Full-screen -->
		<div class="gh-m-search-sheet" id="gh-m-search-sheet" hidden aria-hidden="true">
			<div class="gh-m-search-sheet__panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Search', 'dejoiy' ); ?>">
				<header class="gh-m-search-sheet__head">
					<button type="button" class="gh-m-search-sheet__back" data-gh-m-search-close aria-label="<?php esc_attr_e( 'Close search', 'dejoiy' ); ?>">
						<?php echo dejoiy_gh_icon( 'arrow-right' ); // phpcs:ignore ?>
					</button>
					<label class="gh-m-search-sheet__input-wrap" for="gh-m-sheet-input">
						<?php echo dejoiy_gh_icon( 'search' ); // phpcs:ignore ?>
						<input id="gh-m-sheet-input" class="gh-m-search-sheet__input" type="search" name="s" placeholder="<?php esc_attr_e( 'Search DEJOIY…', 'dejoiy' ); ?>" autocomplete="off" data-gh-m-sheet-input />
						<input type="hidden" name="post_type" value="product" />
					</label>
				</header>
				<div class="gh-m-search-sheet__body">
					<div class="gh-m-search-sheet__results" id="gh-m-sheet-results" data-gh-m-sheet-results></div>
				</div>
			</div>
		</div>
	</header>
	<?php
	return (string) ob_get_clean();
}

/* ---------------------------------------------------------------
   PRINT HEADER on body open
   --------------------------------------------------------------- */

function dejoiy_gh_print_header() {
	if ( ! dejoiy_global_header_enabled() ) {
		return;
	}
	// Skip checkout focus
	if ( function_exists( 'is_checkout' ) && is_checkout() && ! is_wc_endpoint_url( 'order-received' ) ) {
		return;
	}
	// Skip dedicated ecosystem pages
	if ( function_exists( 'dejoiy_mobile_os_is_dedicated_page' ) && dejoiy_mobile_os_is_dedicated_page() ) {
		return;
	}
	// Skip product pages (they have their own chrome)
	if ( function_exists( 'is_product' ) && is_product() ) {
		return;
	}
	// Skip cart page (cart experience has its own)
	if ( function_exists( 'is_cart' ) && is_cart() ) {
		return;
	}
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;
	echo dejoiy_gh_desktop_header_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
add_action( 'wp_body_open', 'dejoiy_gh_print_header', 1 );
add_action( 'etheme_after_body_open', 'dejoiy_gh_print_header', 1 );

/* ---------------------------------------------------------------
   HIDE LEGACY CHROME
   --------------------------------------------------------------- */

function dejoiy_gh_hide_legacy() {
	if ( ! dejoiy_global_header_enabled() ) {
		return;
	}
	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		return;
	}
	if ( function_exists( 'dejoiy_mobile_os_is_dedicated_page' ) && dejoiy_mobile_os_is_dedicated_page() ) {
		return;
	}
	if ( function_exists( 'is_product' ) && is_product() ) {
		return;
	}
	if ( function_exists( 'is_cart' ) && is_cart() ) {
		return;
	}
	echo '<style id="dejoiy-gh-guard">';
	// Hide desktop marketplace header (replaced by our unified header)
	echo 'body.dejoiy-gh #dejoiy-marketplace-header,.dmh{display:none!important;visibility:hidden!important;pointer-events:none!important;}';
	// Hide mobile OS header (replaced by our unified header)
	echo 'body.dejoiy-gh #dejoiy-mobile-os-chrome,body.dejoiy-gh #dejoiy-mobile-os-header{display:none!important;visibility:hidden!important;}';
	// Hide mobile OS bottom nav (replaced by our unified bottom nav)
	echo 'body.dejoiy-gh #dejoiy-mobile-os-bottom{display:none!important;}';
	// Hide mobile OS search sheet
	echo 'body.dejoiy-gh #dm-joi-search-sheet{display:none!important;}';
	// Hide Header OS V4 Elementor header
	echo 'body.dejoiy-gh .elementor-location-header,body.dejoiy-gh header[data-elementor-id="4228"]{display:none!important;visibility:hidden!important;}';
	// Hide legacy theme header
	echo 'body.dejoiy-gh .header-wrapper,body.dejoiy-gh .mobile-header-wrapper,body.dejoiy-gh .etheme-elementor-header-sticky{display:none!important;}';
	// Ensure our header is visible
	echo 'body.dejoiy-gh #dejoiy-global-header{display:block!important;visibility:visible!important;}';
	// Desktop only: show desktop, hide mobile
	echo '@media(min-width:1025px){body.dejoiy-gh .gh-desktop{display:block!important;}body.dejoiy-gh .gh-mobile,body.dejoiy-gh .gh-m-bottom{display:none!important;}}';
	// Mobile only: show mobile, hide desktop
	echo '@media(max-width:1024px){body.dejoiy-gh .gh-desktop{display:none!important;}body.dejoiy-gh .gh-mobile{display:block!important;}body.dejoiy-gh .gh-m-bottom{display:flex!important;}}';
	echo '</style>';
}
add_action( 'wp_head', 'dejoiy_gh_hide_legacy', 2 );

/* ---------------------------------------------------------------
   CART FRAGMENTS — keep badge in sync
   --------------------------------------------------------------- */

function dejoiy_gh_cart_fragments( $fragments ) {
	if ( ! dejoiy_global_header_enabled() ) {
		return $fragments;
	}
	$count = dejoiy_gh_cart_count();
	$label = $count > 99 ? '99+' : (string) $count;
	$show  = $count > 0;
	// Desktop badge
	$fragments['[data-gh-cart-badge]'] = '<span class="gh-badge" data-gh-cart-badge' . ( $show ? '' : ' hidden' ) . '>' . esc_html( $label ) . '</span>';
	// Mobile badge
	$fragments['[data-gh-m-cart-badge]'] = '<span class="gh-badge gh-badge--sm" data-gh-m-cart-badge' . ( $show ? '' : ' hidden' ) . '>' . esc_html( $label ) . '</span>';
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'dejoiy_gh_cart_fragments', 30 );

/* ---------------------------------------------------------------
   AJAX: search
   --------------------------------------------------------------- */

function dejoiy_gh_ajax_search() {
	check_ajax_referer( 'dejoiy_header_os_v4', 'nonce' );
	$term = isset( $_REQUEST['q'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['q'] ) ) : '';
	if ( strlen( $term ) < 2 ) {
		wp_send_json( array( 'results' => array() ) );
	}
	$out   = array();
	$query = new WP_Query( array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		's'              => $term,
		'posts_per_page' => 8,
		'no_found_rows'  => true,
	) );
	foreach ( $query->posts as $post ) {
		$pid   = $post->ID;
		$eco   = function_exists( 'dejoiy_get_product_ecosystem' ) ? dejoiy_get_product_ecosystem( $pid ) : 'marketplace';
		$thumb = get_the_post_thumbnail_url( $pid, 'thumbnail' );
		$price = '';
		if ( function_exists( 'wc_get_product' ) ) {
			$product = wc_get_product( $pid );
			if ( $product ) {
				$price = wp_strip_all_tags( $product->get_price_html() );
			}
		}
		$url = function_exists( 'dejoiy_ecosystem_product_url' ) ? dejoiy_ecosystem_product_url( $pid ) : get_permalink( $pid );
		$out[] = array(
			'type'  => 'product',
			'eco'   => $eco,
			'title' => html_entity_decode( get_the_title( $pid ), ENT_QUOTES, 'UTF-8' ),
			'url'   => $url,
			'thumb' => $thumb ? $thumb : '',
			'price' => $price,
		);
	}
	wp_reset_postdata();
	wp_send_json( array( 'results' => $out ) );
}
add_action( 'wp_ajax_dejoiy_gh_search', 'dejoiy_gh_ajax_search' );
add_action( 'wp_ajax_nopriv_dejoiy_gh_search', 'dejoiy_gh_ajax_search' );

/* ---------------------------------------------------------------
   DISABLE old header systems when global header is active
   --------------------------------------------------------------- */

function dejoiy_gh_disable_old_headers() {
	if ( ! dejoiy_global_header_enabled() ) {
		return;
	}
	// Remove desktop marketplace header
	remove_action( 'wp_body_open', 'dejoiy_desktop_marketplace_ensure_header', 1 );
	remove_action( 'etheme_after_body_open', 'dejoiy_desktop_marketplace_ensure_header', 1 );
	remove_action( 'wp_head', 'dejoiy_desktop_marketplace_viewport_guard', 2 );
	// Remove mobile OS header
	remove_action( 'wp_body_open', 'dejoiy_mobile_os_print_header', 2 );
	remove_action( 'etheme_after_body_open', 'dejoiy_mobile_os_print_header', 2 );
	remove_action( 'etheme_before_page_wrapper', 'dejoiy_mobile_os_print_header', 1 );
	// Remove mobile OS footer chrome
	remove_action( 'wp_footer', 'dejoiy_mobile_os_print_footer_chrome', 5 );
	// Remove site chrome
	remove_action( 'wp_body_open', 'dejoiy_site_chrome_render_header', 3 );
	remove_action( 'etheme_after_body_open', 'dejoiy_site_chrome_render_header', 3 );
	remove_action( 'wp_footer', 'dejoiy_site_chrome_render_footer', 1 );
}
add_action( 'after_setup_theme', 'dejoiy_gh_disable_old_headers', 5 );

/* ---------------------------------------------------------------
   LOGO redirect on logout
   --------------------------------------------------------------- */

function dejoiy_gh_logout_redirect( $redirect_to, $requested, $user ) {
	unset( $user );
	if ( $requested ) {
		return $requested;
	}
	return home_url( '/' );
}
add_filter( 'logout_redirect', 'dejoiy_gh_logout_redirect', 20, 3 );
