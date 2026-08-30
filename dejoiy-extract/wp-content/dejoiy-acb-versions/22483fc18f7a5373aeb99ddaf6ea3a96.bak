<?php
/**
 * DEJOIY Nexus — dedicated header.
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( function_exists( 'dejoiy_library_ensure_cart_loaded' ) ) {
	dejoiy_library_ensure_cart_loaded();
}

$dlu_home     = function_exists( 'dejoiy_library_get_landing_url' ) ? dejoiy_library_get_landing_url() : home_url( '/dejoiy-library/' );
$dlu_cart     = function_exists( 'dejoiy_library_get_cart_url' ) ? dejoiy_library_get_cart_url() : home_url( '/cart/?dejoiy_library=1' );
$dlu_my       = function_exists( 'dejoiy_library_my_universe_url' ) ? dejoiy_library_my_universe_url() : $dlu_home;
$dlu_flow     = defined( 'DEJOIY_LIBRARY_FLOW' ) ? DEJOIY_LIBRARY_FLOW : 'dejoiy_library';
$dlu_home_q   = add_query_arg( $dlu_flow, '1', $dlu_home );
$dlu_logo     = function_exists( 'dejoiy_library_get_logo_url' ) ? dejoiy_library_get_logo_url( 192 ) : home_url( '/favicon.ico' );
$dlu_account  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();
$dlu_shelf    = array(
	'count' => 0,
	'items' => array(),
);
$dlu_checkout = $dlu_cart;

$dlu_hdr_compact = false;
if ( function_exists( 'dejoiy_library_is_nexus_flow_request' ) && dejoiy_library_is_nexus_flow_request() ) {
	if ( function_exists( 'dejoiy_library_is_wc_checkout_page_raw' ) && dejoiy_library_is_wc_checkout_page_raw() ) {
		$dlu_hdr_compact = true;
	}
	if ( function_exists( 'dejoiy_library_is_wc_cart_page_raw' ) && dejoiy_library_is_wc_cart_page_raw() ) {
		$dlu_hdr_compact = true;
	}
}

if ( function_exists( 'dejoiy_library_ensure_cart_loaded' ) ) {
	dejoiy_library_ensure_cart_loaded();
}
if ( function_exists( 'dejoiy_library_get_nexus_cart_panel_data' ) ) {
	$dlu_shelf    = dejoiy_library_get_nexus_cart_panel_data();
	$dlu_checkout = function_exists( 'dejoiy_library_get_checkout_url' ) ? dejoiy_library_get_checkout_url() : $dlu_cart;
} elseif ( function_exists( 'dejoiy_library_get_cart_shelf_data' ) ) {
	$dlu_shelf    = dejoiy_library_get_cart_shelf_data();
	$dlu_checkout = function_exists( 'dejoiy_library_get_checkout_url' ) ? dejoiy_library_get_checkout_url() : $dlu_cart;
}
?>
<header class="dlu-hdr<?php echo $dlu_hdr_compact ? ' dlu-hdr--compact' : ' dlu-hdr--mobile-slim'; ?>" id="dlu-hdr" role="banner">
	<div class="dlu-hdr-in">
		<div class="dlu-hdr-bar">
			<a class="dlu-brand" href="<?php echo esc_url( $dlu_home ); ?>" aria-label="DEJOIY Nexus home">
				<img class="dlu-brand-logo" src="<?php echo esc_url( $dlu_logo ); ?>" alt="" width="40" height="40" decoding="async" />
				<span class="dlu-brand-text">DEJOIY <em>Nexus</em></span>
			</a>

			<div class="dlu-hdr-bar-actions">
				<a href="<?php echo esc_url( $dlu_cart ); ?>" class="dlu-cart-btn dlu-cart-btn--bar" aria-label="<?php esc_attr_e( 'Nexus shelf', 'dejoiy' ); ?>">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
				</a>
				<button type="button" class="dlu-burger" id="dlu-burger" aria-label="Open menu" aria-expanded="false" aria-controls="dlu-hdr-drawer">
					<span></span><span></span><span></span>
				</button>
			</div>
		</div>

		<div class="dlu-search-wrap" id="dlu-hdr-search-wrap">
			<form class="dlu-hdr-search" action="<?php echo esc_url( $dlu_home_q ); ?>" method="get" role="search" id="dlu-hdr-search">
				<input type="hidden" name="<?php echo esc_attr( $dlu_flow ); ?>" value="1" />
				<input type="search" name="dlu_q" id="dlu-hdr-search-in" placeholder="Search Nexus books…" aria-label="Search DEJOIY Nexus" autocomplete="off" enterkeyhint="search" value="<?php echo isset( $_GET['dlu_q'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['dlu_q'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>" />
				<button type="submit" class="dlu-search-submit" aria-label="Search">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
				</button>
			</form>
			<div id="dlu-hdr-search-live" class="dlu-search-live" hidden aria-live="polite"></div>
		</div>

		<nav class="dlu-nav" aria-label="Nexus navigation">
			<a href="<?php echo esc_url( $dlu_home ); ?>#dlu-discover">Discover</a>
			<a href="<?php echo esc_url( $dlu_home ); ?>#dlu-languages">Languages</a>
			<a href="<?php echo esc_url( $dlu_home ); ?>#dlu-categories">Categories</a>
			<a href="<?php echo esc_url( $dlu_home ); ?>#dlu-collections">Collections</a>
			<a href="<?php echo esc_url( $dlu_home ); ?>#dlu-bestsellers">Best Sellers</a>
			<a href="<?php echo esc_url( $dlu_home ); ?>#dlu-free">Free Books</a>
			<a href="<?php echo esc_url( $dlu_home ); ?>#dlu-joi">AI Picks</a>
			<a href="<?php echo esc_url( $dlu_my ); ?>">My Nexus</a>
		</nav>

		<div class="dlu-hdr-tools">
			<button type="button" class="dlu-icon-btn dlu-icon-btn--hide-sm" aria-label="Notifications" title="Notifications">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
			</button>
			<button type="button" class="dlu-icon-btn dlu-icon-btn--hide-sm" id="dlu-wishlist-toggle" aria-label="Nexus wishlist" title="Nexus wishlist" aria-expanded="false" aria-controls="dlu-wishlist-panel">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
			</button>
			<button type="button" class="dlu-cart-btn dlu-cart-btn--tools" id="dlu-cart-toggle" aria-label="<?php esc_attr_e( 'Open Nexus shelf', 'dejoiy' ); ?>">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
			</button>
			<a href="<?php echo esc_url( $dlu_account ); ?>" class="dlu-icon-btn" aria-label="Profile">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
			</a>
			<a href="<?php echo esc_url( $dlu_home ); ?>#dlu-pass" class="dlu-btn-pass">Nexus Pass</a>
		</div>
	</div>
</header>

<nav id="dlu-hdr-drawer" class="dlu-hdr-drawer" hidden aria-label="Mobile menu">
	<div class="dlu-drawer-head">
		<strong>DEJOIY Nexus</strong>
		<button type="button" class="dlu-drawer-close" id="dlu-drawer-close" aria-label="Close menu">&times;</button>
	</div>
	<a href="<?php echo esc_url( $dlu_home_q ); ?>#dlu-discover">Discover</a>
	<a href="<?php echo esc_url( $dlu_home_q ); ?>#dlu-languages">Languages</a>
	<a href="<?php echo esc_url( $dlu_home_q ); ?>#dlu-categories">Categories</a>
	<a href="<?php echo esc_url( $dlu_home_q ); ?>#dlu-collections">Collections</a>
	<a href="<?php echo esc_url( $dlu_home_q ); ?>#dlu-bestsellers">Best Sellers</a>
	<a href="<?php echo esc_url( $dlu_home_q ); ?>#dlu-joi">AI Picks</a>
	<a href="<?php echo esc_url( $dlu_my ); ?>">My Nexus</a>
	<button type="button" class="dlu-drawer-wishlist" id="dlu-wishlist-toggle-mobile">Nexus Wishlist</button>
	<a href="<?php echo esc_url( $dlu_cart ); ?>"><?php esc_html_e( 'Nexus shelf', 'dejoiy' ); ?></a>
	<a href="<?php echo esc_url( $dlu_home_q ); ?>#dlu-pass" class="dlu-drawer-pass">Nexus Pass</a>
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="dlu-drawer-market">Marketplace Home</a>
</nav>
<div class="dlu-drawer-backdrop" id="dlu-drawer-backdrop" hidden></div>

<aside class="dlu-cart-panel" id="dlu-cart-panel" hidden aria-label="Nexus cart">
	<div class="dlu-cart-panel-head">
		<h2><?php esc_html_e( 'Nexus shelf', 'dejoiy' ); ?></h2>
		<button type="button" class="dlu-cart-close" id="dlu-cart-close" aria-label="Close">&times;</button>
	</div>
	<div class="dlu-cart-panel-body" id="dlu-cart-body">
		<?php
		if ( function_exists( 'dejoiy_library_render_cart_panel_inner' ) ) {
			dejoiy_library_render_cart_panel_inner( $dlu_shelf );
		} else {
			echo '<p class="dlu-cart-empty">' . esc_html__( 'Your shelf is empty. Discover a book to begin your collection.', 'dejoiy' ) . '</p>';
		}
		?>
	</div>
	<div class="dlu-cart-panel-foot">
		<?php
		$dlu_pending = isset( $dlu_shelf['pending_count'] ) ? (int) $dlu_shelf['pending_count'] : 0;
		if ( $dlu_pending > 0 ) :
			?>
			<a href="<?php echo esc_url( $dlu_checkout ); ?>" class="dlu-btn-primary dlu-cart-checkout-btn"><?php esc_html_e( 'Complete Your Collection', 'dejoiy' ); ?></a>
			<a href="<?php echo esc_url( $dlu_cart ); ?>" class="dlu-btn-secondary"><?php esc_html_e( 'View Nexus shelf', 'dejoiy' ); ?></a>
		<?php else : ?>
			<a href="<?php echo esc_url( dejoiy_library_get_landing_url() ); ?>#dlu-discover" class="dlu-btn-primary"><?php esc_html_e( 'Discover books', 'dejoiy' ); ?></a>
		<?php endif; ?>
	</div>
</aside>
<div class="dlu-cart-backdrop" id="dlu-cart-backdrop" hidden></div>

<aside class="dlu-wishlist-panel" id="dlu-wishlist-panel" hidden aria-label="Nexus wishlist">
	<div class="dlu-cart-panel-head">
		<h2>Nexus Wishlist</h2>
		<button type="button" class="dlu-cart-close" id="dlu-wishlist-close" aria-label="Close">&times;</button>
	</div>
	<div class="dlu-cart-panel-body" id="dlu-wishlist-body">
		<p class="dlu-cart-empty">Save books with ♡ on this page — your wishlist stays in DEJOIY Nexus only.</p>
	</div>
</aside>
<div class="dlu-wishlist-backdrop" id="dlu-wishlist-backdrop" hidden></div>
