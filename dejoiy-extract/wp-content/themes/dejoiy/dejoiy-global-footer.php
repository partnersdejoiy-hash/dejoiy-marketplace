<?php
/**
 * DEJOIY Global Footer.
 *
 * One modern marketplace footer for the whole site, replacing the legacy
 * Elementor theme-builder footer (4225) and the XStore default footer.
 * Rendered from the child theme footer.php override so the theme's
 * structural wrappers (page-wrapper / template-content / template-container)
 * are preserved exactly.
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return bool
 */
function dejoiy_global_footer_active() {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return false;
	}
	if ( function_exists( 'elementor_location_exits' ) && wp_is_block_theme() ) {
		return false;
	}
	return true;
}

/**
 * Top-level product categories for the footer column (cached per request).
 *
 * @return array<int, array{slug: string, name: string}>
 */
function dejoiy_global_footer_categories() {
	$cats = array(
		array( 'slug' => 'fashion', 'name' => 'Fashion' ),
		array( 'slug' => 'electronics', 'name' => 'Electronics' ),
		array( 'slug' => 'computers-office', 'name' => 'Computers & Office' ),
		array( 'slug' => 'home-kitchen', 'name' => 'Home & Kitchen' ),
		array( 'slug' => 'beauty-personal-care', 'name' => 'Beauty & Personal Care' ),
		array( 'slug' => 'customized-products', 'name' => 'Customized Products' ),
	);

	if ( ! function_exists( 'get_terms' ) ) {
		return $cats;
	}

	$raw = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'parent'     => 0,
			'orderby'    => 'count',
			'order'      => 'DESC',
			'number'     => 12,
		)
	);

	if ( is_wp_error( $raw ) || empty( $raw ) ) {
		return $cats;
	}

	$skip = array( 'uncategorized', 'dejoiy-library' );
	$out  = array();
	foreach ( $raw as $term ) {
		if ( in_array( $term->slug, $skip, true ) ) {
			continue;
		}
		$out[] = array(
			'slug' => (string) $term->slug,
			'name' => (string) $term->name,
		);
		if ( count( $out ) >= 8 ) {
			break;
		}
	}
	return ! empty( $out ) ? $out : $cats;
}

/**
 * Shared footer link list builder.
 *
 * @return array<string, array<string, string>>
 */
function dejoiy_global_footer_links() {
	static $links = null;
	if ( null !== $links ) {
		return $links;
	}
	$base = home_url( '/' );
	$links = array(
		'shop'    => $base . 'shop/',
		'deals'   => $base . 'dejoiy-festival-sale/',
		'library' => $base . 'dejoiy-library/',
		'services' => $base . 'dejoiy-services/',
		'sell'    => $base . 'sell-on-dejoiy/',
		'seller'  => $base . 'seller-center/',
		'vendor'  => $base . 'vendor-register/',
		'vmember' => $base . 'vendor-membership/',
		'contact' => $base . 'contact-us/',
		'support' => $base . 'support-page/',
		'about'   => $base . 'about-us/',
		'terms'   => $base . 'terms-and-conditions/',
		'privacy' => $base . 'privacy-policy/',
		'returns' => $base . 'returns-and-refunds/',
		'account' => $base . 'my-account/',
		'orders'  => $base . 'my-account/orders/',
		'addresses' => $base . 'my-account/edit-address/',
	);
	return $links;
}

/**
 * Render the global footer.
 */
function dejoiy_global_footer_render() {
	if ( ! dejoiy_global_footer_active() ) {
		do_action( 'etheme_footer' );
		return;
	}

	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;

	$L    = dejoiy_global_footer_links();
	$cats = dejoiy_global_footer_categories();
	$year = gmdate( 'Y' );
	$dgf_logo_id = 4592;
	$dgf_logo = wp_get_attachment_image( $dgf_logo_id, array( 220, 84 ), false, array( 'class' => 'dgf__logo-img', 'alt' => 'DEJOIY logo', 'loading' => 'lazy' ) );
	?>
	<footer class="dgf" id="dejoiy-global-footer">
		<div class="dgf__inner">
			<div class="dgf__top">
				<div class="dgf__brand">
					<a class="dgf__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="DEJOIY home">
						<?php echo $dgf_logo ? $dgf_logo : '<span aria-hidden="true">DEJOIY</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
					<p class="dgf__tag">Discover &middot; Create &middot; Own &mdash; one multi-world marketplace.</p>
					<div class="dgf__contact">
						<span class="dgf__contact-line">24&times;7 Support &middot; <a href="tel:+01146594425">+011&nbsp;46594425</a></span>
						<span class="dgf__contact-line"><a href="mailto:support-care@dejoiy.com">support-care@dejoiy.com</a></span>
					</div>
				</div>

				<nav class="dgf__col" aria-label="Shop by category">
					<h3 class="dgf__title">Shop by Category</h3>
					<ul class="dgf__list">
						<?php foreach ( $cats as $cat ) : ?>
							<li><a class="dgf__link" href="<?php echo esc_url( home_url( '/product-category/' . $cat['slug'] . '/' ) ); ?>"><?php echo esc_html( $cat['name'] ); ?></a></li>
						<?php endforeach; ?>
						<li><a class="dgf__link dgf__link--all" href="<?php echo esc_url( $L['shop'] ); ?>">Shop All</a></li>
					</ul>
				</nav>

				<nav class="dgf__col" aria-label="DEJOIY worlds">
					<h3 class="dgf__title">Explore DEJOIY</h3>
					<ul class="dgf__list">
						<li><a class="dgf__link" href="<?php echo esc_url( $L['shop'] ); ?>">Marketplace</a></li>
						<li><a class="dgf__link" href="<?php echo esc_url( $L['deals'] ); ?>">Festival Sale</a></li>
						<li><a class="dgf__link" href="<?php echo esc_url( $L['library'] ); ?>">DEJOIY Library</a></li>
						<li><a class="dgf__link" href="<?php echo esc_url( $L['services'] ); ?>">DEJOIY Services</a></li>
						<li><a class="dgf__link" href="<?php echo esc_url( $L['sell'] ); ?>">Sell on DEJOIY</a></li>
						<li><a class="dgf__link" href="<?php echo esc_url( $L['seller'] ); ?>">Seller Center</a></li>
					</ul>
				</nav>

				<nav class="dgf__col" aria-label="Account">
					<h3 class="dgf__title">My Account</h3>
					<ul class="dgf__list">
						<li><a class="dgf__link" href="<?php echo esc_url( $L['account'] ); ?>">Login / Register</a></li>
						<li><a class="dgf__link" href="<?php echo esc_url( $L['orders'] ); ?>">My Orders</a></li>
						<li><a class="dgf__link" href="<?php echo esc_url( $L['orders'] ); ?>">Track Order</a></li>
						<li><a class="dgf__link" href="<?php echo esc_url( $L['addresses'] ); ?>">Addresses</a></li>
						<li><a class="dgf__link" href="<?php echo esc_url( $L['vendor'] ); ?>">Vendor Registration</a></li>
					</ul>
				</nav>

				<nav class="dgf__col" aria-label="Help and support">
					<h3 class="dgf__title">Help &amp; Support</h3>
					<ul class="dgf__list">
						<li><a class="dgf__link" href="<?php echo esc_url( $L['contact'] ); ?>">Contact Us</a></li>
						<li><a class="dgf__link" href="<?php echo esc_url( $L['support'] ); ?>">Support Page</a></li>
						<li><a class="dgf__link" href="<?php echo esc_url( $L['about'] ); ?>">About Us</a></li>
						<li><a class="dgf__link" href="<?php echo esc_url( $L['returns'] ); ?>">Returns &amp; Refunds</a></li>
						<li><a class="dgf__link" href="<?php echo esc_url( $L['privacy'] ); ?>">Privacy Policy</a></li>
						<li><a class="dgf__link" href="<?php echo esc_url( $L['vmember'] ); ?>">Vendor Membership</a></li>
						<li><a class="dgf__link" href="<?php echo esc_url( $L['terms'] ); ?>">Terms &amp; Conditions</a></li>
					</ul>
				</nav>
			</div>

			<div class="dgf__trust">
				<div class="dgf__trust-item">Secure Payments</div>
				<div class="dgf__trust-item">DEJOIY Buyer Protection</div>
				<div class="dgf__trust-item">Easy Returns</div>
				<div class="dgf__trust-item">24&times;7 Support</div>
			</div>

			<div class="dgf__bottom">
				<span class="dgf__copy">&copy; <?php echo esc_html( $year ); ?> DEJOIY. All rights reserved.</span>
				<span class="dgf__meta">
					<a class="dgf__link" href="<?php echo esc_url( $L['terms'] ); ?>">Terms</a>
					<a class="dgf__link" href="<?php echo esc_url( $L['privacy'] ); ?>">Privacy</a>
					<a class="dgf__link" href="<?php echo esc_url( $L['returns'] ); ?>">Returns</a>
					<a class="dgf__link" href="<?php echo esc_url( $L['support'] ); ?>">Support</a>
					<a class="dgf__link" href="<?php echo esc_url( $L['contact'] ); ?>">Contact</a>
				</span>
			</div>
		</div>
	</footer>
	<?php
}

/**
 * Enqueue global footer assets.
 */
function dejoiy_global_footer_assets() {
	$uri = get_stylesheet_directory_uri();
	$dir = get_stylesheet_directory();
	$css = $dir . '/dejoiy-global-footer.css';
	if ( is_readable( $css ) ) {
		wp_enqueue_style(
			'dejoiy-global-footer',
			$uri . '/dejoiy-global-footer.css',
			array(),
			(string) filemtime( $css )
		);
	}
	$js = $dir . '/dejoiy-global-footer.js';
	if ( is_readable( $js ) ) {
		wp_enqueue_script(
			'dejoiy-global-footer',
			$uri . '/dejoiy-global-footer.js',
			array(),
			(string) filemtime( $js ),
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'dejoiy_global_footer_assets', 10050 );