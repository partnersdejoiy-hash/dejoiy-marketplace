<?php
/**
 * Dejoiy Mobile Navigation Bar
 */

namespace AngieSnippets\DejoiyMobileNavigationBar_a7f40fe2;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DEJOIY_MOBILE_NAV_ASSETS_VERSION_a7f40fe2', '1.0.0' );

add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style(
		'dejoiy-mobile-nav-a7f40fe2',
		angie_cs_get_snippet_asset_url( __FILE__, 'style.css' ),
		[],
		DEJOIY_MOBILE_NAV_ASSETS_VERSION_a7f40fe2
	);
	wp_enqueue_script(
		'dejoiy-mobile-nav-a7f40fe2',
		angie_cs_get_snippet_asset_url( __FILE__, 'script.js' ),
		[],
		DEJOIY_MOBILE_NAV_ASSETS_VERSION_a7f40fe2,
		true
	);
});

add_action( 'wp_body_open', function() {
	?>
	<div class="dejoiy-nav-wrapper-a7f40fe2" id="djNav-a7f40fe2">
		<div class="dejoiy-nav-scroll-a7f40fe2" id="djScroll-a7f40fe2">

			<div class="dejoiy-nav-card-a7f40fe2" data-nav="shop" data-href="https://dejoiy.tech/shop/">
				<div class="dejoiy-nav-icon-a7f40fe2">
					<svg viewBox="0 0 24 24" fill="none" stroke="#2F6BFF" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
						<path class="dj-bag-a7f40fe2" d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
						<line x1="3" y1="6" x2="21" y2="6"/>
						<path d="M16 10a4 4 0 01-8 0"/>
						<path class="dj-smile-a7f40fe2" d="M9.5 14.5Q12 17 14.5 14.5" stroke-width="1.5" opacity="0"/>
					</svg>
				</div>
				<span class="dejoiy-nav-label-a7f40fe2">Shop</span>
			</div>

			<div class="dejoiy-nav-card-a7f40fe2" data-nav="library" data-href="https://dejoiy.tech/dejoiy-library/">
				<div class="dejoiy-nav-icon-a7f40fe2">
					<svg viewBox="0 0 24 24" fill="none" stroke="#7C3AED" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
						<path class="dj-book-cover-a7f40fe2" d="M4 19.5A2.5 2.5 0 016.5 17H20"/>
						<path class="dj-book-body-a7f40fe2" d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/>
						<line class="dj-book-page-a7f40fe2" x1="12" y1="6" x2="12" y2="14" stroke-width="1" opacity="0"/>
					</svg>
				</div>
				<span class="dejoiy-nav-label-a7f40fe2">Library</span>
			</div>

			<div class="dejoiy-nav-card-a7f40fe2" data-nav="studio" data-href="https://dejoiy.tech/dejoiy-custom-studio/">
				<div class="dejoiy-nav-icon-a7f40fe2">
					<svg viewBox="0 0 24 24" fill="none" stroke="#FF4FD8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
						<path d="M12 2l2.09 6.26L20.18 9l-5 4.09L16.82 20 12 16.54 7.18 20l1.64-6.91L3.82 9l6.09-.74z"/>
						<circle class="dj-sparkle1-a7f40fe2" cx="4" cy="4" r="1" fill="#FF4FD8" stroke="none" opacity="0"/>
						<circle class="dj-sparkle2-a7f40fe2" cx="20" cy="5" r="0.8" fill="#FF4FD8" stroke="none" opacity="0"/>
						<circle class="dj-sparkle3-a7f40fe2" cx="19" cy="19" r="0.6" fill="#FF4FD8" stroke="none" opacity="0"/>
					</svg>
				</div>
				<span class="dejoiy-nav-label-a7f40fe2">Custom Studio</span>
			</div>

			<div class="dejoiy-nav-card-a7f40fe2" data-nav="quick" data-href="https://dejoiy.tech/dejoiy-quick-mart/">
				<div class="dejoiy-nav-icon-a7f40fe2">
					<svg viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
						<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
						<line class="dj-flash1-a7f40fe2" x1="2" y1="8" x2="5" y2="8" stroke-width="2" opacity="0"/>
						<line class="dj-flash2-a7f40fe2" x1="19" y1="16" x2="22" y2="16" stroke-width="2" opacity="0"/>
					</svg>
				</div>
				<span class="dejoiy-nav-label-a7f40fe2">Quick</span>
			</div>

			<div class="dejoiy-nav-card-a7f40fe2" data-nav="refurbished" data-href="https://dejoiy.tech/dejoiy-refurbished/">
				<div class="dejoiy-nav-icon-a7f40fe2">
					<svg viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
						<polyline points="23 4 23 10 17 10"/>
						<polyline points="1 20 1 14 7 14"/>
						<path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/>
					</svg>
				</div>
				<span class="dejoiy-nav-label-a7f40fe2">Refurbished</span>
			</div>

			<div class="dejoiy-nav-card-a7f40fe2" data-nav="services" data-href="https://dejoiy.tech/dejoiy-services/">
				<div class="dejoiy-nav-icon-a7f40fe2">
					<svg viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
						<path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>
					</svg>
				</div>
				<span class="dejoiy-nav-label-a7f40fe2">Services</span>
			</div>

			<div class="dejoiy-nav-card-a7f40fe2" data-nav="support" data-href="https://dejoiy.tech/support-page/">
				<div class="dejoiy-nav-icon-a7f40fe2">
					<svg viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
						<path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
						<path d="M12 2a2 2 0 00-2 2h4a2 2 0 00-2-2z"/>
						<circle cx="12" cy="22" r="1"/>
					</svg>
				</div>
				<span class="dejoiy-nav-label-a7f40fe2">Support</span>
			</div>

		</div>
	</div>

	<div class="dj-overlay-a7f40fe2" id="djOverlay-a7f40fe2">
		<div class="dj-logo-a7f40fe2"><span class="y">DE</span><span style="background:linear-gradient(90deg,#2F6BFF,#FF4FD8);-webkit-background-clip:text;-webkit-text-fill-color:transparent">JO</span><span class="j">IY</span></div>
		<div class="dj-sub-a7f40fe2"><span class="y">YOU</span><span style="color:#999;margin:0 4px">+</span><span class="j">JOY</span></div>
		<div class="dj-spin-a7f40fe2"></div>
	</div>
	<?php
});
