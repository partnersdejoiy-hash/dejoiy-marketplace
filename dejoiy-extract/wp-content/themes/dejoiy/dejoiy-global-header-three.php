<?php
/**
 * DEJOIY Global Header — Three.js Animated Header
 * 
 * Universal header for ALL pages except dedicated universe pages:
 * - Nexus (dejoiy-library)
 * - Custom Studio (dejoiy-custom-studio)
 * - Refurbished (dejoiy-refurbished)
 * - Services (dejoiy-services)
 * - QuickMart (dejoiy-quick-mart)
 * 
 * Features:
 * - Real DEJOIY logo
 * - Three.js constellation/network animation
 * - Light glassmorphism (Amazon-style)
 * - Mobile app-like layout
 * - Responsive: Desktop / Tablet / Mobile
 * 
 * @version 2.0.0
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Check if current page is a dedicated universe page.
 */
function dejoiy_is_dedicated_universe_page() {
    $uri = strtolower( (string) wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) );
    $dedicated = array(
        'dejoiy-library',
        'dejoiy_library=1',
        'dejoiy-custom-studio',
        'dejoiy-refurbished',
        'dejoiy-services',
        'dejoiy-quick-mart',
    );
    foreach ( $dedicated as $needle ) {
        if ( false !== strpos( $uri, $needle ) ) return true;
    }
    if ( is_page() ) {
        $slug = (string) get_post_field( 'post_name', get_queried_object_id() );
        $slugs = array( 'dejoiy-library', 'dejoiy-custom-studio', 'dejoiy-refurbished', 'dejoiy-services', 'dejoiy-quick-mart' );
        if ( in_array( $slug, $slugs, true ) ) return true;
    }
    return false;
}

/**
 * Should the global header render?
 */
function dejoiy_global_header_three_should_render() {
    if ( is_admin() && ! wp_doing_ajax() ) return false;
    if ( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) return false;
    if ( dejoiy_is_dedicated_universe_page() ) return false;
    if ( defined( 'DEJOIY_GLOBAL_HEADER_DISABLED' ) && DEJOIY_GLOBAL_HEADER_DISABLED ) return false;
    return true;
}

/**
 * Logo URL
 */
function dejoiy_dgh_logo_url() {
    $logo_id = (int) get_theme_mod( 'custom_logo' );
    if ( $logo_id > 0 ) {
        $img = wp_get_attachment_image_url( $logo_id, 'medium' );
        if ( $img ) return $img;
    }
    return get_stylesheet_directory_uri() . '/assets/dejoiy-logo.png';
}

/**
 * Get WooCommerce cart count
 */
function dejoiy_dgh_cart_count() {
    if ( function_exists( 'WC' ) && WC()->cart ) {
        return WC()->cart->get_cart_contents_count();
    }
    return 0;
}

/**
 * Render the header HTML
 */
function dejoiy_global_header_three_render() {
    if ( false ) return;

    $logo_url = dejoiy_dgh_logo_url();
    $home_url = home_url( '/' );
    $shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
    $cart_url = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );
    $account_url = home_url( '/my-account/' );
    $orders_url = function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'orders' ) : $account_url;
    $cart_count = dejoiy_dgh_cart_count();
    $is_logged_in = is_user_logged_in();
    $greeting = $is_logged_in ? __( 'Hello', 'dejoiy' ) : __( 'Hello, Sign In', 'dejoiy' );

    // Check if this is the homepage
    $is_home = is_front_page() || is_home();
    ?>
    <!-- DEJOIY Global Header v2.0 — Three.js Animated -->
    <div class="dgh-wrap" id="dgh-wrap">
        <canvas id="dgh-canvas" aria-hidden="true"></canvas>
        <div class="dgh-glass" id="dgh-glass">

            <!-- Desktop/Tablet Header (≥769px) -->
            <div class="dgh-desktop">
                <!-- Top bar -->
                <div class="dgh-top">
                    <div class="dgh-container">
                        <a class="dgh-logo" href="<?php echo esc_url( $home_url ); ?>" aria-label="DEJOIY Home">
                            <img src="<?php echo esc_url( $logo_url ); ?>" alt="DEJOIY" class="dgh-logo__img" width="140" height="42" loading="eager" decoding="async">
                        </a>

                        <a class="dgh-deliver" href="<?php echo esc_url( $account_url ); ?>">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                            <span><small>Deliver to</small><strong>India</strong></span>
                        </a>

                        <form class="dgh-search" action="<?php echo esc_url( $shop_url ); ?>" method="get" role="search">
                            <label for="dgh-search-input" class="sr-only">Search DEJOIY</label>
                            <input id="dgh-search-input" class="dgh-search__input" type="search" name="s" placeholder="Search products, brands and more…" autocomplete="off">
                            <input type="hidden" name="post_type" value="product">
                            <button type="submit" class="dgh-search__btn" aria-label="Search">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            </button>
                        </form>

                        <nav class="dgh-actions" aria-label="Account actions">
                            <a href="<?php echo esc_url( $account_url ); ?>" class="dgh-action">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                <span><?php echo esc_html( $greeting ); ?></span>
                            </a>
                            <a href="<?php echo esc_url( $orders_url ); ?>" class="dgh-action">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><path d="M2 10h20"/></svg>
                                <span>Orders</span>
                            </a>
                            <a href="<?php echo esc_url( $cart_url ); ?>" class="dgh-action dgh-action--cart" aria-label="Cart">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                                <?php if ( $cart_count > 0 ) : ?>
                                    <span class="dgh-badge"><?php echo esc_html( $cart_count > 99 ? '99+' : (string) $cart_count ); ?></span>
                                <?php endif; ?>
                            </a>
                        </nav>
                    </div>
                </div>

                <!-- Nav bar -->
                <nav class="dgh-nav" aria-label="Marketplace navigation">
                    <div class="dgh-container">
                        <a href="<?php echo esc_url( $home_url ); ?>" class="dgh-nav__link<?php echo $is_home ? ' dgh-nav__link--active' : ''; ?>">Home</a>
                        <a href="<?php echo esc_url( $shop_url ); ?>" class="dgh-nav__link<?php echo ( function_exists( 'is_shop' ) && is_shop() ) ? ' dgh-nav__link--active' : ''; ?>">Shop</a>
                        <a href="<?php echo esc_url( home_url( '/dejoiy-library/?dejoiy_library=1' ) ); ?>" class="dgh-nav__link">Nexus</a>
                        <a href="<?php echo esc_url( home_url( '/dejoiy-custom-studio/' ) ); ?>" class="dgh-nav__link">Custom Studio</a>
                        <a href="<?php echo esc_url( home_url( '/dejoiy-refurbished/' ) ); ?>" class="dgh-nav__link">Refurbished</a>
                        <a href="<?php echo esc_url( home_url( '/dejoiy-services/' ) ); ?>" class="dgh-nav__link">Services</a>
                        <a href="<?php echo esc_url( home_url( '/sell-on-dejoiy/' ) ); ?>" class="dgh-nav__link">Sell on DEJOIY</a>
                        <a href="<?php echo esc_url( home_url( '/dejoiy-festival-sale/' ) ); ?>" class="dgh-nav__link dgh-nav__link--deals">🔥 Deals</a>
                        <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="dgh-nav__link">Support</a>
                        <a href="<?php echo esc_url( home_url( '/joi/' ) ); ?>" class="dgh-nav__link dgh-nav__link--joi">✨ Meet JOI</a>
                    </div>
                </nav>
            </div>

            <!-- Mobile Header (≤768px) — App-like -->
            <div class="dgh-mobile">
                <div class="dgh-mobile__top">
                    <a class="dgh-logo dgh-logo--mobile" href="<?php echo esc_url( $home_url ); ?>" aria-label="DEJOIY Home">
                        <img src="<?php echo esc_url( $logo_url ); ?>" alt="DEJOIY" class="dgh-logo__img" width="110" height="36" loading="eager" decoding="async">
                    </a>
                    <a href="<?php echo esc_url( $cart_url ); ?>" class="dgh-mobile__cart" aria-label="Cart">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                        <?php if ( $cart_count > 0 ) : ?>
                            <span class="dgh-badge"><?php echo esc_html( $cart_count > 99 ? '99+' : (string) $cart_count ); ?></span>
                        <?php endif; ?>
                    </a>
                </div>

                <form class="dgh-mobile__search" action="<?php echo esc_url( $shop_url ); ?>" method="get" role="search">
                    <label for="dgh-m-search" class="sr-only">Search DEJOIY</label>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#888" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input id="dgh-m-search" class="dgh-mobile__search-input" type="search" name="s" placeholder="Search DEJOIY…" autocomplete="off">
                    <input type="hidden" name="post_type" value="product">
                </form>

                <!-- Bottom navigation — app style -->
                <nav class="dgh-bottomnav" aria-label="Navigation">
                    <a href="<?php echo esc_url( $home_url ); ?>" class="dgh-bottomnav__item<?php echo $is_home ? ' is-active' : ''; ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        <span>Home</span>
                    </a>
                    <a href="<?php echo esc_url( $shop_url ); ?>" class="dgh-bottomnav__item">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" x2="21" y1="6" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                        <span>Shop</span>
                    </a>
                    <a href="<?php echo esc_url( home_url( '/dejoiy-festival-sale/' ) ); ?>" class="dgh-bottomnav__item dgh-bottomnav__item--deals">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" x2="7.01" y1="7" y2="7"/></svg>
                        <span>Deals</span>
                    </a>
                    <a href="<?php echo esc_url( $account_url ); ?>" class="dgh-bottomnav__item">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span>Account</span>
                    </a>
                    <a href="<?php echo esc_url( $cart_url ); ?>" class="dgh-bottomnav__item">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                        <?php if ( $cart_count > 0 ) : ?>
                            <span class="dgh-badge dgh-badge--nav"><?php echo esc_html( $cart_count > 99 ? '99+' : (string) $cart_count ); ?></span>
                        <?php endif; ?>
                        <span>Cart</span>
                    </a>
                </nav>
            </div>
        </div>
    </div>
    <?php
}
add_action( 'wp_body_open', 'dejoiy_global_header_three_render', 1 );

/**
 * Enqueue header assets
 */
function dejoiy_global_header_three_assets() {
    if ( false ) return;
    
    $uri = get_stylesheet_directory_uri();
    $dir = get_stylesheet_directory();
    
    // Three.js CDN
    wp_enqueue_script( 'dgh-three', 'https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js', array(), 'r128', true );
    
    // Header CSS
    $css = $dir . '/dejoiy-global-header-three.css';
    if ( is_readable( $css ) ) {
        wp_enqueue_style( 'dgh-style', $uri . '/dejoiy-global-header-three.css', array(), (string) filemtime( $css ) );
    }
    
    // Header JS
    $js = $dir . '/dejoiy-global-header-three.js';
    if ( is_readable( $js ) ) {
        wp_enqueue_script( 'dgh-script', $js, array( 'dgh-three' ), (string) filemtime( $js ), true );
    }
}
add_action( 'wp_enqueue_scripts', 'dejoiy_global_header_three_assets', 10070 );
