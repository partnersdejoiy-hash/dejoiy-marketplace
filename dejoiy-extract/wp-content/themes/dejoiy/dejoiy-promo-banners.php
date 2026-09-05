<?php
/**
 * DEJOIY Marketplace Promo Banners — Inside the white marketplace section.
 *
 * Two premium responsive campaign containers:
 *   1. DEJOIY INTERNSHIPS  ("Launch Your Career with DEJOIY")
 *   2. SELL ON DEJOIY      ("Turn Your Ideas Into Income")
 *
 * Pure-CSS 3D-style artwork (floating glass cards, animated growth graphs,
 * laptop/package mockups), subtle float + draw animations, viewport-triggered.
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function dejoiy_promo_banners_enabled() {
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return false;
	}
	return is_front_page() && function_exists( 'dejoiy_marketplace_home_active' ) && dejoiy_marketplace_home_active();
}

function dejoiy_promo_banners_assets() {
	if ( ! dejoiy_promo_banners_enabled() ) {
		return;
	}
	$uri = get_stylesheet_directory_uri();
	$dir = get_stylesheet_directory();
	$css = $dir . '/dejoiy-promo-banners.css';
	if ( is_readable( $css ) ) {
		wp_enqueue_style(
			'dejoiy-promo-banners',
			$uri . '/dejoiy-promo-banners.css',
			array(),
			(string) filemtime( $css )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'dejoiy_promo_banners_assets', 1045 );

/**
 * Emit the two promotional banners as one white-list section.
 *
 * @return string
 */
function dejoiy_promo_banners_markup() {
	if ( ! dejoiy_promo_banners_enabled() ) {
		return '';
	}

	$internship_url = home_url( '/internships/' );
	$programs_url   = home_url( '/dejoiy-library/?dejoiy_library=1' );
	$seller_url     = home_url( '/vendor-register/' );
	$how_url        = home_url( '/sell-on-dejoiy/' );

	ob_start();
	?>
	<section class="mpb" aria-label="DEJOIY campaigns" data-mpb-promos>
		<div class="mpb__wrap">

			<?php /* ============ BANNER 1 — DEJOIY INTERNSHIPS ============ */ ?>
			<article class="mpb-banner mpb-banner--intern" data-mpb-banner>
				<div class="mpb-glow" aria-hidden="true"></div>
				<div class="mpb-banner__copy">
					<p class="mpb-label"><span class="mpb-label__spark" aria-hidden="true">✦</span>DEJOIY INTERNSHIPS</p>
					<h2 class="mpb-head">Launch Your Career with <span class="mpb-head__accent">DEJOIY</span></h2>
					<p class="mpb-sub">Get real-world experience, build your skills, and earn a verified internship certificate.</p>
					<div class="mpb-actions">
						<a class="mpb-cta mpb-cta--primary" href="<?php echo esc_url( $internship_url ); ?>">Explore Internships <span class="mpb-cta__arrow" aria-hidden="true">→</span></a>
						<a class="mpb-cta mpb-cta--ghost" href="<?php echo esc_url( $programs_url ); ?>">View Programs</a>
					</div>
					<ul class="mpb-bits">
						<li>Real project work</li>
						<li>Mentor support</li>
						<li>Verified certificate</li>
					</ul>
				</div>

				<div class="mpb-scene mpb-scene--intern" aria-hidden="true">
					<div class="sc-laptop">
						<div class="sc-laptop__screen">
							<div class="sc-dash">
								<div class="sc-dash__bar sc-dash__bar--1"></div>
								<div class="sc-dash__bar sc-dash__bar--2"></div>
								<div class="sc-dash__bar sc-dash__bar--3"></div>
								<span class="sc-dash__pill">Onboarding ✓</span>
							</div>
						</div>
						<div class="sc-laptop__base"></div>
					</div>
					<svg class="sc-graph" viewBox="0 0 220 90" preserveAspectRatio="none" data-mpb-graph="loop">
						<path d="M4 82 C 34 78, 46 60, 66 62 S 98 34, 118 40 S 150 18, 178 24 S 202 10, 216 14" fill="none" stroke="url(#mpbGrad1)" stroke-width="3" stroke-linecap="round"/>
						<defs><linearGradient id="mpbGrad1" x1="0" y1="0" x2="1" y2="0">
							<stop offset="0" stop-color="#ffd27d"/><stop offset=".55" stop-color="#ff9ff0"/><stop offset="1" stop-color="#9fb6ff"/>
						</linearGradient></defs>
					</svg>
					<div class="sc-card sc-card--cert">
						<span class="sc-cert__seal" aria-hidden="true">DEJOIY</span>
						<b>Verified Certificate</b>
						<small>Internship · 2026</small>
					</div>
					<div class="sc-card sc-card--role">
						<small>Role completed</small>
						<b>Product Intern</b>
						<span class="sc-ribbon">★ 5.0</span>
					</div>
					<span class="sc-badge sc-badge--a">⚡</span>
					<span class="sc-badge sc-badge--b">🏅</span>
					<div class="sc-skills">
						<span>UI</span><span>Growth</span><span>AI</span>
					</div>
				</div>
			</article>

			<?php /* ============ BANNER 2 — SELL ON DEJOIY ============ */ ?>
			<article class="mpb-banner mpb-banner--sell" data-mpb-banner>
				<div class="mpb-glow mpb-glow--orange" aria-hidden="true"></div>
				<div class="mpb-banner__copy">
					<p class="mpb-label"><span class="mpb-label__spark" aria-hidden="true">✦</span>SELL ON DEJOIY</p>
					<h2 class="mpb-head">Turn Your Ideas Into <span class="mpb-head__accent">Income</span></h2>
					<p class="mpb-sub">Reach more customers, grow your brand, and build your business with DEJOIY.</p>
					<div class="mpb-actions">
						<a class="mpb-cta mpb-cta--primary mpb-cta--sell" href="<?php echo esc_url( $seller_url ); ?>">Become a Seller Now <span class="mpb-cta__arrow" aria-hidden="true">→</span></a>
						<a class="mpb-cta mpb-cta--ghost mpb-cta--ghost-dk" href="<?php echo esc_url( $how_url ); ?>">See How It Works</a>
					</div>
					<div class="mpb-bits mpb-bits--benefits">
						<div class="mpb-benefit"><span aria-hidden="true">🛍️</span>Grow Your Brand</div>
						<div class="mpb-benefit"><span aria-hidden="true">📈</span>Reach More Customers</div>
						<div class="mpb-benefit"><span aria-hidden="true">🛠️</span>Powerful Seller Tools</div>
					</div>
				</div>

				<div class="mpb-scene mpb-scene--sell" aria-hidden="true">
					<div class="sc-store">
						<div class="sc-store__top"><b>Your Store</b><span class="sc-store__live">● live</span></div>
						<div class="sc-store__stats">
							<div><small>Sales</small><b>₹48.2k</b></div>
							<div><small>Orders</small><b>312</b></div>
							<div><small>Products</small><b>27</b></div>
						</div>
						<div class="sc-store__row"><small>Customers</small><span>1,240</span><small>Analytics</small><span>+24%</span></div>
						<svg class="sc-graph sc-graph--sm" viewBox="0 0 200 60" preserveAspectRatio="none" data-mpb-graph="once">
							<path d="M4 52 C 30 48, 44 36, 64 38 S 98 22, 118 26 S 150 12, 178 16 S 196 8, 204 10" fill="none" stroke="url(#mpbGrad2)" stroke-width="3" stroke-linecap="round"/>
							<defs><linearGradient id="mpbGrad2" x1="0" y1="0" x2="1" y2="0">
								<stop offset="0" stop-color="#ffb36b"/><stop offset=".55" stop-color="#ff7fd6"/><stop offset="1" stop-color="#7f9bff"/>
							</linearGradient></defs>
						</svg>
					</div>
					<div class="sc-box sc-box--a"><span class="sc-box__tag">DEJOIY</span><small>Ship</small></div>
					<div class="sc-box sc-box--b"><span class="sc-box__tag">DEJOIY</span><small>Ship</small></div>
					<div class="sc-truck" aria-hidden="true">
						<div class="sc-truck__body"></div>
						<div class="sc-truck__door"></div>
						<span class="sc-truck__wheels"></span>
					</div>
					<div class="sc-card sc-card--sale">
						<small>Last order</small>
						<b>+ ₹1,290</b>
						<span class="sc-ribbon">→ Delivered</span>
					</div>
				</div>
			</article>

		</div>

		<script>
		(function () {
			var sec = document.querySelector('[data-mpb-promos]');
			if (!sec || !('IntersectionObserver' in window)) { return; }
			var io = new IntersectionObserver(function (entries) {
				entries.forEach(function (e) {
					if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); }
				});
			}, { threshold: 0.15 });
			io.observe(sec);
		})();
		</script>
	</section>
	<?php
	return (string) ob_get_clean();
}

/**
 * Alias used by the Marketplace Home emit pipeline.
 *
 * @return string
 */
function dejoiy_mph_promos() {
	return dejoiy_promo_banners_markup();
}