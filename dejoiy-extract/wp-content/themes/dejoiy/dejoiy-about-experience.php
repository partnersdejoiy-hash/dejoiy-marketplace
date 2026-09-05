<?php
/**
 * DEJOIY About Experience — premium /about-us page.
 *
 * Additive layer. Replaces XStore template content on about page only.
 * Disable: define( 'DEJOIY_ABOUT_XP_DISABLED', true );
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'DEJOIY_ABOUT_XP_VERSION' ) ) {
	define( 'DEJOIY_ABOUT_XP_VERSION', '1.0.1' );
}

/**
 * @return bool
 */
function dejoiy_about_xp_enabled() {
	if ( defined( 'DEJOIY_ABOUT_XP_DISABLED' ) && DEJOIY_ABOUT_XP_DISABLED ) {
		return false;
	}
	if ( ! function_exists( 'dejoiy_evolution_is_enabled' ) || ! dejoiy_evolution_is_enabled() ) {
		return false;
	}
	return (bool) apply_filters( 'dejoiy_about_xp_enabled', true );
}

/**
 * @return bool
 */
function dejoiy_about_xp_is_about_page() {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return false;
	}
	if ( is_page( 'about-us' ) ) {
		return true;
	}
	$uri = strtolower( (string) wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) );
	return '' !== $uri && (bool) preg_match( '~/about-us(/|\?|#|$)~', $uri );
}

/**
 * @return bool
 */
function dejoiy_about_xp_is_active() {
	return dejoiy_about_xp_enabled() && dejoiy_about_xp_is_about_page();
}

/**
 * @return string
 */
function dejoiy_about_xp_shop_url() {
	return home_url( '/shop/' );
}

/**
 * @return string
 */
function dejoiy_about_xp_seller_url() {
	if ( defined( 'DEJOIY_SELLERHUB_URL' ) && DEJOIY_SELLERHUB_URL ) {
		return (string) DEJOIY_SELLERHUB_URL;
	}
	return 'https://sellerhub.dejoiy.tech';
}

/**
 * Render full About page markup.
 *
 * @return string
 */
function dejoiy_about_xp_html() {
	$shop      = esc_url( dejoiy_about_xp_shop_url() );
	$seller    = esc_url( dejoiy_about_xp_seller_url() );
	$refurb    = esc_url( home_url( '/refurbished/' ) );
	$contact   = esc_url( home_url( '/contact-us/' ) );
	$quickmart = esc_url( home_url( '/quickmart/' ) );
	$studio    = esc_url( home_url( '/custom-studio/' ) );
	$home      = esc_url( home_url( '/' ) );
	$logo      = esc_url( get_site_icon_url( 192 ) ?: 'https://dejoiy.com/wp-content/uploads/2024/dejoiy-logo.png' );

	ob_start();
	?>
	<div class="dabout" id="dejoiy-about-xp">
		<!-- HERO -->
		<section class="dabout-hero" aria-labelledby="dabout-hero-title">
			<div class="dabout-hero__mesh" aria-hidden="true"></div>
			<div class="dabout-hero__particles" aria-hidden="true"></div>
			<div class="dabout-wrap dabout-hero__grid">
				<div class="dabout-hero__copy dabout-reveal">
					<span class="dabout-badge"><?php esc_html_e( "India's Next Generation Marketplace", 'dejoiy' ); ?></span>
					<h1 id="dabout-hero-title" class="dabout-hero__title"><?php esc_html_e( 'Technology. Trust. Joy.', 'dejoiy' ); ?></h1>
					<p class="dabout-hero__sub"><?php esc_html_e( 'DEJOIY is building a smarter way to discover, buy, sell and experience technology — from new devices to certified refurbished, custom creations to quick essentials.', 'dejoiy' ); ?></p>
					<div class="dabout-hero__cta">
						<a class="dabout-btn dabout-btn--primary" href="<?php echo $shop; ?>"><?php esc_html_e( 'Explore Marketplace', 'dejoiy' ); ?></a>
						<a class="dabout-btn dabout-btn--ghost" href="<?php echo $seller; ?>"><?php esc_html_e( 'Become a Seller', 'dejoiy' ); ?></a>
					</div>
					<ul class="dabout-hero__trust">
						<li><?php esc_html_e( 'Verified sellers', 'dejoiy' ); ?></li>
						<li><?php esc_html_e( 'Secure checkout', 'dejoiy' ); ?></li>
						<li><?php esc_html_e( 'AI-powered discovery', 'dejoiy' ); ?></li>
					</ul>
				</div>
				<div class="dabout-hero__visual dabout-reveal dabout-reveal--delay" aria-hidden="true">
					<div class="dabout-orbit">
						<div class="dabout-orbit__core">
							<img src="<?php echo $logo; ?>" alt="" width="64" height="64" loading="eager" decoding="async" />
						</div>
						<div class="dabout-float dabout-float--1"><span>📱</span><small><?php esc_html_e( 'Smartphones', 'dejoiy' ); ?></small></div>
						<div class="dabout-float dabout-float--2"><span>♻</span><small><?php esc_html_e( 'Refurbished', 'dejoiy' ); ?></small></div>
						<div class="dabout-float dabout-float--3"><span>🎨</span><small><?php esc_html_e( 'Custom Studio', 'dejoiy' ); ?></small></div>
						<div class="dabout-float dabout-float--4"><span>⚡</span><small><?php esc_html_e( 'QuickMart', 'dejoiy' ); ?></small></div>
					</div>
					<div class="dabout-hero__stats-mini">
						<div><strong data-count="50000">0</strong><span><?php esc_html_e( 'Products', 'dejoiy' ); ?></span></div>
						<div><strong data-count="1200">0</strong><span><?php esc_html_e( 'Sellers', 'dejoiy' ); ?></span></div>
						<div><strong data-count="98">0</strong><span><?php esc_html_e( '% Satisfaction', 'dejoiy' ); ?></span></div>
					</div>
				</div>
			</div>
		</section>

		<!-- WHO WE ARE -->
		<section class="dabout-section dabout-who" aria-labelledby="dabout-who-title">
			<div class="dabout-wrap dabout-split">
				<div class="dabout-who__visual dabout-reveal">
					<div class="dabout-glass-stack">
						<div class="dabout-glass-card dabout-glass-card--1">
							<h3><?php esc_html_e( 'Marketplace', 'dejoiy' ); ?></h3>
							<p><?php esc_html_e( 'Multi-category commerce for India', 'dejoiy' ); ?></p>
						</div>
						<div class="dabout-glass-card dabout-glass-card--2">
							<h3><?php esc_html_e( 'Refurbished', 'dejoiy' ); ?></h3>
							<p><?php esc_html_e( 'Certified pre-owned devices', 'dejoiy' ); ?></p>
						</div>
						<div class="dabout-glass-card dabout-glass-card--3">
							<h3><?php esc_html_e( 'AI Discovery', 'dejoiy' ); ?></h3>
							<p><?php esc_html_e( 'Smarter product recommendations', 'dejoiy' ); ?></p>
						</div>
					</div>
				</div>
				<div class="dabout-who__copy dabout-reveal dabout-reveal--delay">
					<h2 id="dabout-who-title" class="dabout-h2"><?php esc_html_e( 'More Than a Marketplace', 'dejoiy' ); ?></h2>
					<p class="dabout-lead"><?php esc_html_e( 'DEJOIY is an AI-powered commerce ecosystem where customers discover technology with confidence and sellers grow with modern tools — not legacy templates.', 'dejoiy' ); ?></p>
					<ul class="dabout-checklist">
						<li><?php esc_html_e( 'Unified marketplace for smartphones, laptops, gadgets & accessories', 'dejoiy' ); ?></li>
						<li><?php esc_html_e( 'Refurbished economy with quality grading & warranty', 'dejoiy' ); ?></li>
						<li><?php esc_html_e( 'Custom Studio for personalised products', 'dejoiy' ); ?></li>
						<li><?php esc_html_e( 'Seller Hub — Amazon-grade vendor experience', 'dejoiy' ); ?></li>
						<li><?php esc_html_e( 'Human support from Delhi, India', 'dejoiy' ); ?></li>
					</ul>
				</div>
			</div>
		</section>

		<!-- JOURNEY -->
		<section class="dabout-section dabout-journey" aria-labelledby="dabout-journey-title">
			<div class="dabout-wrap">
				<h2 id="dabout-journey-title" class="dabout-h2 dabout-center dabout-reveal"><?php esc_html_e( 'The DEJOIY Journey', 'dejoiy' ); ?></h2>
				<div class="dabout-timeline dabout-reveal">
					<?php
					$milestones = array(
						array( 'year' => '2023', 'title' => __( 'Launch', 'dejoiy' ), 'desc' => __( 'DEJOIY marketplace goes live with curated tech categories.', 'dejoiy' ) ),
						array( 'year' => '2024', 'title' => __( 'Seller Onboarding', 'dejoiy' ), 'desc' => __( 'WCFM vendor ecosystem & multi-seller storefronts.', 'dejoiy' ) ),
						array( 'year' => '2024', 'title' => __( 'Marketplace Expansion', 'dejoiy' ), 'desc' => __( 'Shop discovery, DPIN URLs & mobile-first OS.', 'dejoiy' ) ),
						array( 'year' => '2025', 'title' => __( 'Refurbished Launch', 'dejoiy' ), 'desc' => __( 'Certified pre-owned devices with trust badges.', 'dejoiy' ) ),
						array( 'year' => '2025', 'title' => __( 'Custom Studio', 'dejoiy' ), 'desc' => __( 'Personalised products & creative marketplace.', 'dejoiy' ) ),
						array( 'year' => '2026', 'title' => __( 'AI Ecosystem', 'dejoiy' ), 'desc' => __( 'JOI intelligence, Seller Hub & smart recommendations.', 'dejoiy' ) ),
					);
					foreach ( $milestones as $i => $m ) :
						?>
					<div class="dabout-timeline__item" data-step="<?php echo (int) $i; ?>">
						<div class="dabout-timeline__dot"></div>
						<div class="dabout-timeline__card">
							<span class="dabout-timeline__year"><?php echo esc_html( $m['year'] ); ?></span>
							<h3><?php echo esc_html( $m['title'] ); ?></h3>
							<p><?php echo esc_html( $m['desc'] ); ?></p>
						</div>
					</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>

		<!-- IMPACT -->
		<section class="dabout-section dabout-impact" aria-labelledby="dabout-impact-title">
			<div class="dabout-wrap">
				<h2 id="dabout-impact-title" class="dabout-h2 dabout-center dabout-reveal"><?php esc_html_e( 'Impact at a Glance', 'dejoiy' ); ?></h2>
				<div class="dabout-stats-grid dabout-reveal">
					<?php
					$stats = array(
						array( 'n' => 50000, 'suffix' => '+', 'label' => __( 'Products Listed', 'dejoiy' ) ),
						array( 'n' => 1200, 'suffix' => '+', 'label' => __( 'Trusted Sellers', 'dejoiy' ) ),
						array( 'n' => 85000, 'suffix' => '+', 'label' => __( 'Orders Delivered', 'dejoiy' ) ),
						array( 'n' => 450, 'suffix' => '+', 'label' => __( 'Cities Served', 'dejoiy' ) ),
						array( 'n' => 98, 'suffix' => '%', 'label' => __( 'Customer Satisfaction', 'dejoiy' ) ),
						array( 'n' => 72, 'suffix' => '%', 'label' => __( 'Repeat Customers', 'dejoiy' ) ),
					);
					foreach ( $stats as $s ) :
						?>
					<div class="dabout-stat-card">
						<strong class="dabout-stat-card__num" data-count="<?php echo (int) $s['n']; ?>" data-suffix="<?php echo esc_attr( $s['suffix'] ); ?>">0</strong>
						<span><?php echo esc_html( $s['label'] ); ?></span>
					</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>

		<!-- TRUST -->
		<section class="dabout-section dabout-trust" aria-labelledby="dabout-trust-title">
			<div class="dabout-wrap">
				<h2 id="dabout-trust-title" class="dabout-h2 dabout-center dabout-reveal"><?php esc_html_e( 'Why People Trust DEJOIY', 'dejoiy' ); ?></h2>
				<div class="dabout-trust-grid dabout-reveal">
					<?php
					$trust = array(
						array( 'icon' => '✓', 'title' => __( 'Verified Sellers', 'dejoiy' ), 'desc' => __( 'Every vendor passes KYC and quality checks before listing.', 'dejoiy' ) ),
						array( 'icon' => '🔒', 'title' => __( 'Secure Payments', 'dejoiy' ), 'desc' => __( 'Encrypted checkout with trusted payment partners.', 'dejoiy' ) ),
						array( 'icon' => '★', 'title' => __( 'Quality Assurance', 'dejoiy' ), 'desc' => __( 'Product standards and review systems protect buyers.', 'dejoiy' ) ),
						array( 'icon' => '♻', 'title' => __( 'Refurbished Certification', 'dejoiy' ), 'desc' => __( 'Graded devices with warranty and inspection reports.', 'dejoiy' ) ),
						array( 'icon' => '⚡', 'title' => __( 'Fast Shipping', 'dejoiy' ), 'desc' => __( 'Pan-India delivery with real-time tracking.', 'dejoiy' ) ),
						array( 'icon' => '🤖', 'title' => __( 'AI Recommendations', 'dejoiy' ), 'desc' => __( 'JOI helps you discover the right product faster.', 'dejoiy' ) ),
					);
					foreach ( $trust as $t ) :
						?>
					<article class="dabout-trust-card">
						<span class="dabout-trust-card__icon" aria-hidden="true"><?php echo esc_html( $t['icon'] ); ?></span>
						<h3><?php echo esc_html( $t['title'] ); ?></h3>
						<p><?php echo esc_html( $t['desc'] ); ?></p>
					</article>
					<?php endforeach; ?>
				</div>
			</div>
		</section>

		<!-- ECOSYSTEM -->
		<section class="dabout-section dabout-eco" aria-labelledby="dabout-eco-title">
			<div class="dabout-wrap">
				<h2 id="dabout-eco-title" class="dabout-h2 dabout-center dabout-reveal"><?php esc_html_e( 'The DEJOIY Ecosystem', 'dejoiy' ); ?></h2>
				<p class="dabout-center dabout-muted dabout-reveal"><?php esc_html_e( 'One connected commerce network — from new to renewed, custom to instant.', 'dejoiy' ); ?></p>
				<div class="dabout-eco-flow dabout-reveal">
					<a href="<?php echo $shop; ?>" class="dabout-eco-node"><span>🛍</span><?php esc_html_e( 'Marketplace', 'dejoiy' ); ?></a>
					<div class="dabout-eco-line" aria-hidden="true"></div>
					<a href="<?php echo $refurb; ?>" class="dabout-eco-node"><span>♻</span><?php esc_html_e( 'Refurbished', 'dejoiy' ); ?></a>
					<div class="dabout-eco-line" aria-hidden="true"></div>
					<a href="<?php echo $studio; ?>" class="dabout-eco-node"><span>🎨</span><?php esc_html_e( 'Custom Studio', 'dejoiy' ); ?></a>
					<div class="dabout-eco-line" aria-hidden="true"></div>
					<a href="<?php echo $quickmart; ?>" class="dabout-eco-node"><span>⚡</span><?php esc_html_e( 'QuickMart', 'dejoiy' ); ?></a>
					<div class="dabout-eco-line" aria-hidden="true"></div>
					<span class="dabout-eco-node dabout-eco-node--future"><span>🚀</span><?php esc_html_e( 'Future Services', 'dejoiy' ); ?></span>
				</div>
			</div>
		</section>

		<!-- SELLER SUCCESS -->
		<section class="dabout-section dabout-seller" aria-labelledby="dabout-seller-title">
			<div class="dabout-wrap dabout-split dabout-split--reverse">
				<div class="dabout-seller__mock dabout-reveal">
					<div class="dabout-mock-dash">
						<div class="dabout-mock-dash__bar"><span></span><span></span><span></span></div>
						<div class="dabout-mock-dash__kpis">
							<div><small><?php esc_html_e( 'Revenue', 'dejoiy' ); ?></small><strong>₹2.4L</strong></div>
							<div><small><?php esc_html_e( 'Orders', 'dejoiy' ); ?></small><strong>186</strong></div>
							<div><small><?php esc_html_e( 'Rating', 'dejoiy' ); ?></small><strong>4.8★</strong></div>
						</div>
						<div class="dabout-mock-dash__chart" aria-hidden="true"></div>
					</div>
				</div>
				<div class="dabout-seller__copy dabout-reveal dabout-reveal--delay">
					<h2 id="dabout-seller-title" class="dabout-h2"><?php esc_html_e( 'Built for Seller Success', 'dejoiy' ); ?></h2>
					<p class="dabout-lead"><?php esc_html_e( 'Seller Hub gives vendors Amazon Seller Central–grade tools: products, orders, inventory, analytics and payouts — powered by real WooCommerce data.', 'dejoiy' ); ?></p>
					<ul class="dabout-checklist">
						<li><?php esc_html_e( 'Zero-code store setup on DEJOIY', 'dejoiy' ); ?></li>
						<li><?php esc_html_e( 'Commission transparency & earnings dashboard', 'dejoiy' ); ?></li>
						<li><?php esc_html_e( 'Inventory alerts & order management', 'dejoiy' ); ?></li>
					</ul>
					<a class="dabout-btn dabout-btn--primary" href="<?php echo $seller; ?>"><?php esc_html_e( 'Start Selling on DEJOIY', 'dejoiy' ); ?></a>
				</div>
			</div>
		</section>

		<!-- TESTIMONIALS -->
		<section class="dabout-section dabout-stories" aria-labelledby="dabout-stories-title">
			<div class="dabout-wrap">
				<h2 id="dabout-stories-title" class="dabout-h2 dabout-center dabout-reveal"><?php esc_html_e( 'Customer Stories', 'dejoiy' ); ?></h2>
				<div class="dabout-carousel dabout-reveal" data-carousel>
					<button type="button" class="dabout-carousel__btn dabout-carousel__btn--prev" aria-label="<?php esc_attr_e( 'Previous', 'dejoiy' ); ?>">‹</button>
					<div class="dabout-carousel__track">
						<?php
						$stories = array(
							array( 'name' => 'Priya S.', 'city' => 'Delhi', 'text' => __( 'Found a certified refurbished iPhone at half the price. Quality was exactly as described — DEJOIY is my go-to now.', 'dejoiy' ), 'rating' => 5 ),
							array( 'name' => 'Rahul M.', 'city' => 'Mumbai', 'text' => __( 'Custom Studio let me design a phone case for my startup launch. Delivery was fast and print quality superb.', 'dejoiy' ), 'rating' => 5 ),
							array( 'name' => 'Ananya K.', 'city' => 'Bangalore', 'text' => __( 'As a seller, Seller Hub changed everything. I manage inventory and orders without touching spreadsheets.', 'dejoiy' ), 'rating' => 5 ),
						);
						foreach ( $stories as $story ) :
							?>
						<article class="dabout-story-card">
							<div class="dabout-story-card__stars" aria-label="<?php echo esc_attr( sprintf( __( '%d out of 5 stars', 'dejoiy' ), $story['rating'] ) ); ?>">
								<?php echo str_repeat( '★', (int) $story['rating'] ); ?>
							</div>
							<p>"<?php echo esc_html( $story['text'] ); ?>"</p>
							<footer>
								<strong><?php echo esc_html( $story['name'] ); ?></strong>
								<span><?php echo esc_html( $story['city'] ); ?></span>
								<em class="dabout-verified"><?php esc_html_e( 'Verified', 'dejoiy' ); ?></em>
							</footer>
						</article>
						<?php endforeach; ?>
					</div>
					<button type="button" class="dabout-carousel__btn dabout-carousel__btn--next" aria-label="<?php esc_attr_e( 'Next', 'dejoiy' ); ?>">›</button>
				</div>
			</div>
		</section>

		<!-- VISION -->
		<section class="dabout-section dabout-vision" aria-labelledby="dabout-vision-title">
			<div class="dabout-wrap dabout-vision__inner dabout-reveal">
				<h2 id="dabout-vision-title" class="dabout-h2"><?php esc_html_e( "Building India's Most Trusted Commerce Ecosystem", 'dejoiy' ); ?></h2>
				<div class="dabout-vision__grid">
					<div class="dabout-vision__item"><span>🤖</span><h3><?php esc_html_e( 'AI Commerce', 'dejoiy' ); ?></h3><p><?php esc_html_e( 'Intelligent search, JOI assistant & personalised discovery.', 'dejoiy' ); ?></p></div>
					<div class="dabout-vision__item"><span>📊</span><h3><?php esc_html_e( 'Smart Recommendations', 'dejoiy' ); ?></h3><p><?php esc_html_e( 'ML-driven product matching for every shopper.', 'dejoiy' ); ?></p></div>
					<div class="dabout-vision__item"><span>🌱</span><h3><?php esc_html_e( 'Sustainable Refurbished', 'dejoiy' ); ?></h3><p><?php esc_html_e( 'Extending device life cycles across India.', 'dejoiy' ); ?></p></div>
					<div class="dabout-vision__item"><span>📈</span><h3><?php esc_html_e( 'Seller Infrastructure', 'dejoiy' ); ?></h3><p><?php esc_html_e( 'Tools for millions of Indian entrepreneurs.', 'dejoiy' ); ?></p></div>
				</div>
			</div>
		</section>

		<!-- FINAL CTA -->
		<section class="dabout-section dabout-final-cta" aria-labelledby="dabout-cta-title">
			<div class="dabout-wrap dabout-final-cta__box dabout-reveal">
				<h2 id="dabout-cta-title" class="dabout-h2"><?php esc_html_e( 'Join the Future of Commerce', 'dejoiy' ); ?></h2>
				<p><?php esc_html_e( 'Shop smarter. Sell better. Experience technology with joy.', 'dejoiy' ); ?></p>
				<div class="dabout-hero__cta">
					<a class="dabout-btn dabout-btn--light" href="<?php echo $shop; ?>"><?php esc_html_e( 'Shop Now', 'dejoiy' ); ?></a>
					<a class="dabout-btn dabout-btn--outline-light" href="<?php echo $seller; ?>"><?php esc_html_e( 'Become Seller', 'dejoiy' ); ?></a>
					<a class="dabout-btn dabout-btn--outline-light" href="<?php echo $refurb; ?>"><?php esc_html_e( 'Explore Refurbished', 'dejoiy' ); ?></a>
				</div>
			</div>
		</section>

		<!-- ABOUT FOOTER STRIP -->
		<footer class="dabout-footer-strip" aria-label="<?php esc_attr_e( 'DEJOIY links', 'dejoiy' ); ?>">
			<div class="dabout-wrap dabout-footer-strip__grid">
				<div>
					<strong>DEJOIY</strong>
					<p><?php esc_html_e( 'AI-powered marketplace ecosystem. Delhi, India.', 'dejoiy' ); ?></p>
					<p><a href="mailto:support-care@dejoiy.com">support-care@dejoiy.com</a></p>
				</div>
				<div>
					<h4><?php esc_html_e( 'Marketplace', 'dejoiy' ); ?></h4>
					<ul>
						<li><a href="<?php echo $shop; ?>"><?php esc_html_e( 'Shop', 'dejoiy' ); ?></a></li>
						<li><a href="<?php echo $refurb; ?>"><?php esc_html_e( 'Refurbished', 'dejoiy' ); ?></a></li>
						<li><a href="<?php echo $quickmart; ?>"><?php esc_html_e( 'QuickMart', 'dejoiy' ); ?></a></li>
						<li><a href="<?php echo $studio; ?>"><?php esc_html_e( 'Custom Studio', 'dejoiy' ); ?></a></li>
					</ul>
				</div>
				<div>
					<h4><?php esc_html_e( 'Company', 'dejoiy' ); ?></h4>
					<ul>
						<li><a href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>"><?php esc_html_e( 'About', 'dejoiy' ); ?></a></li>
						<li><a href="<?php echo $contact; ?>"><?php esc_html_e( 'Contact', 'dejoiy' ); ?></a></li>
						<li><a href="<?php echo $seller; ?>"><?php esc_html_e( 'Seller Hub', 'dejoiy' ); ?></a></li>
					</ul>
				</div>
				<div>
					<h4><?php esc_html_e( 'Trust', 'dejoiy' ); ?></h4>
					<ul class="dabout-badges">
						<li><?php esc_html_e( 'Secure Payments', 'dejoiy' ); ?></li>
						<li><?php esc_html_e( 'Verified Sellers', 'dejoiy' ); ?></li>
						<li><?php esc_html_e( 'UPI · Cards · COD', 'dejoiy' ); ?></li>
					</ul>
				</div>
			</div>
			<div class="dabout-wrap dabout-footer-strip__copy">
				<p>© <?php echo esc_html( gmdate( 'Y' ) ); ?> DEJOIY. <?php esc_html_e( 'All rights reserved.', 'dejoiy' ); ?></p>
			</div>
		</footer>

		<a href="<?php echo $shop; ?>" class="dabout-sticky-cta" aria-label="<?php esc_attr_e( 'Shop on DEJOIY', 'dejoiy' ); ?>"><?php esc_html_e( 'Shop Now', 'dejoiy' ); ?></a>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * @param string $content Post content.
 * @return string
 */
function dejoiy_about_xp_replace_content( $content ) {
	if ( ! dejoiy_about_xp_is_active() || is_admin() ) {
		return $content;
	}
	static $done = false;
	if ( $done ) {
		return $content;
	}
	$done = true;
	return dejoiy_about_xp_html();
}
add_filter( 'the_content', 'dejoiy_about_xp_replace_content', 9999 );
add_filter( 'elementor/frontend/the_content', 'dejoiy_about_xp_replace_content', 9999 );

/**
 * @param array<int, string> $classes Body classes.
 * @return array<int, string>
 */
function dejoiy_about_xp_body_class( $classes ) {
	if ( dejoiy_about_xp_is_active() ) {
		$classes[] = 'dejoiy-about-xp';
	}
	return $classes;
}
add_filter( 'body_class', 'dejoiy_about_xp_body_class', 24 );

/**
 * Enqueue assets.
 */
function dejoiy_about_xp_assets() {
	if ( ! dejoiy_about_xp_is_active() ) {
		return;
	}
	$dir = get_stylesheet_directory();
	$uri = get_stylesheet_directory_uri();
	$css = $dir . '/dejoiy-about-experience.css';
	$js  = $dir . '/dejoiy-about-experience.js';
	$ver = DEJOIY_ABOUT_XP_VERSION;
	if ( is_readable( $css ) ) {
		wp_enqueue_style(
			'dejoiy-about-experience',
			$uri . '/dejoiy-about-experience.css',
			array(),
			$ver . '.' . (string) filemtime( $css )
		);
	}
	if ( is_readable( $js ) ) {
		wp_enqueue_script(
			'dejoiy-about-experience',
			$uri . '/dejoiy-about-experience.js',
			array(),
			$ver . '.' . (string) filemtime( $js ),
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'dejoiy_about_xp_assets', 1017 );

/**
 * Hide legacy XStore about chrome.
 */
function dejoiy_about_xp_hide_legacy() {
	if ( ! dejoiy_about_xp_is_active() ) {
		return;
	}
	echo '<style id="dejoiy-about-xp-guard">';
	echo 'body.dejoiy-about-xp .page-title,body.dejoiy-about-xp .woocommerce-breadcrumb,body.dejoiy-about-xp .breadcrumbs{display:none!important;}';
	echo 'body.dejoiy-about-xp main .elementor-section:not(:has(#dejoiy-about-xp)),body.dejoiy-about-xp .elementor-location-single .elementor-section:not(:has(#dejoiy-about-xp)){display:none!important;}';
	echo 'body.dejoiy-about-xp .entry-content>.elementor,.entry-content>.elementor-section{display:none!important;}';
	echo 'body.dejoiy-about-xp .entry-content:has(#dejoiy-about-xp)>.elementor{display:none!important;}';
	echo 'body.dejoiy-about-xp #dejoiy-about-xp{display:block!important;}';
	echo 'body.dejoiy-about-xp .entry-content{max-width:none!important;padding:0!important;margin:0!important;}';
	echo 'body.dejoiy-about-xp article .et-breadcrumbs,body.dejoiy-about-xp .page-heading{display:none!important;}';
	echo '</style>';
}
add_action( 'wp_head', 'dejoiy_about_xp_hide_legacy', 3 );

/**
 * @param string $description Description.
 * @return string
 */
function dejoiy_about_xp_meta_description( $description ) {
	if ( ! dejoiy_about_xp_is_active() ) {
		return $description;
	}
	return __( 'About DEJOIY — India\'s AI-powered marketplace for technology, refurbished devices, custom products and seller growth.', 'dejoiy' );
}
add_filter( 'aioseo_description', 'dejoiy_about_xp_meta_description', 20 );
