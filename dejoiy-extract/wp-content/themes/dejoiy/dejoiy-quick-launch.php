<?php
/**
 * DEJOIY Quick — premium "Launching Soon" landing (isolated module).
 *
 * Disable via wp-config: define( 'DEJOIY_QUICK_LAUNCH_DISABLED', true );
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return bool
 */
function dejoiy_quick_launch_is_enabled() {
	if ( defined( 'DEJOIY_QUICK_LAUNCH_DISABLED' ) && DEJOIY_QUICK_LAUNCH_DISABLED ) {
		return false;
	}
	return (bool) apply_filters( 'dejoiy_quick_launch_enabled', true );
}

/**
 * QuickMart home without sub-views (cart, search, checkout, orders, category).
 *
 * @return bool
 */
function dejoiy_quickmart_is_launch_home() {
	if ( ! dejoiy_quick_launch_is_enabled() ) {
		return false;
	}
	if ( ! function_exists( 'dejoiy_quickmart_is_quickmart_page' ) || ! dejoiy_quickmart_is_quickmart_page() ) {
		return false;
	}
	if ( is_admin() ) {
		return false;
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['qm_view'] ) && '' !== (string) wp_unslash( $_GET['qm_view'] ) ) {
		return false;
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['qm_cat'] ) && '' !== (string) wp_unslash( $_GET['qm_cat'] ) ) {
		return false;
	}
	return true;
}

/**
 * @return string
 */
function dejoiy_quick_launch_marketplace_url() {
	if ( function_exists( 'wc_get_page_permalink' ) ) {
		$shop = wc_get_page_permalink( 'shop' );
		if ( $shop ) {
			return $shop;
		}
	}
	return home_url( '/' );
}

/**
 * @return array<int, array<string, string>>
 */
function dejoiy_quick_launch_feature_cards() {
	return array(
		array(
			'icon'  => '⚡',
			'title' => __( 'Fast Delivery', 'dejoiy' ),
			'desc'  => __( '10–30 Minute delivery experience.', 'dejoiy' ),
		),
		array(
			'icon'  => '📍',
			'title' => __( 'Smart Location Coverage', 'dejoiy' ),
			'desc'  => __( 'Location and pincode based service availability.', 'dejoiy' ),
		),
		array(
			'icon'  => '🛒',
			'title' => __( 'Easy Ordering', 'dejoiy' ),
			'desc'  => __( 'Quick cart and simplified checkout.', 'dejoiy' ),
		),
		array(
			'icon'  => '🚀',
			'title' => __( 'Growing Network', 'dejoiy' ),
			'desc'  => __( 'Expanding to serve more locations soon.', 'dejoiy' ),
		),
	);
}

/**
 * @return array<int, array<string, string>>
 */
function dejoiy_quick_launch_category_cards() {
	return array(
		array( 'icon' => '🥬', 'label' => __( 'Groceries', 'dejoiy' ) ),
		array( 'icon' => '🧴', 'label' => __( 'Daily Essentials', 'dejoiy' ) ),
		array( 'icon' => '✏️', 'label' => __( 'Stationery', 'dejoiy' ) ),
		array( 'icon' => '💆', 'label' => __( 'Personal Care', 'dejoiy' ) ),
		array( 'icon' => '🥤', 'label' => __( 'Snacks & Beverages', 'dejoiy' ) ),
		array( 'icon' => '📱', 'label' => __( 'Electronics Accessories', 'dejoiy' ) ),
	);
}

/**
 * Full landing markup.
 *
 * @return string
 */
function dejoiy_quick_launch_page_html() {
	$market_url = dejoiy_quick_launch_marketplace_url();

	ob_start();
	?>
	<div id="dejoiy-quick-launch" class="dq-launch" data-dq-launch>
		<div class="dq-launch__bg" aria-hidden="true">
			<span class="dq-launch__orb dq-launch__orb--1"></span>
			<span class="dq-launch__orb dq-launch__orb--2"></span>
			<span class="dq-launch__orb dq-launch__orb--3"></span>
			<span class="dq-launch__bolt" aria-hidden="true">⚡</span>
			<span class="dq-launch__float dq-launch__float--bag" aria-hidden="true">🛍️</span>
			<span class="dq-launch__float dq-launch__float--box" aria-hidden="true">📦</span>
			<span class="dq-launch__float dq-launch__float--pin" aria-hidden="true">📍</span>
		</div>

		<header class="dq-launch__top">
			<a class="dq-launch__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<span class="dq-launch__logo-mark" aria-hidden="true">⚡</span>
				<span class="dq-launch__logo-text">DEJOIY <strong>Quick</strong></span>
			</a>
			<span class="dq-launch__badge"><?php esc_html_e( 'Launching Soon', 'dejoiy' ); ?></span>
		</header>

		<section class="dq-launch__hero dq-reveal">
			<p class="dq-launch__kicker">⚡ <?php esc_html_e( 'DEJOIY Quick', 'dejoiy' ); ?></p>
			<h1 class="dq-launch__headline">⚡ <?php esc_html_e( 'DEJOIY Quick', 'dejoiy' ); ?></h1>
			<p class="dq-launch__subhead"><?php esc_html_e( '10–30 Minute Delivery', 'dejoiy' ); ?></p>
			<p class="dq-launch__lede">
				<?php esc_html_e( 'Groceries, Daily Essentials, Stationery, Personal Care, Electronics Accessories and More — Delivered Fast.', 'dejoiy' ); ?>
			</p>
			<p class="dq-launch__desc">
				<?php esc_html_e( 'Get your daily essentials delivered in minutes. Experience the next generation of fast commerce from DEJOIY.', 'dejoiy' ); ?>
			</p>
			<div class="dq-launch__cta-row">
				<button type="button" class="dq-btn dq-btn--primary" data-dq-open-notify>
					<?php esc_html_e( 'Notify Me When We Launch', 'dejoiy' ); ?>
				</button>
				<a class="dq-btn dq-btn--ghost" href="<?php echo esc_url( $market_url ); ?>">
					<?php esc_html_e( 'Explore DEJOIY Marketplace', 'dejoiy' ); ?>
				</a>
			</div>
		</section>

		<section class="dq-launch__features dq-reveal" aria-labelledby="dq-features-title">
			<h2 id="dq-features-title" class="dq-section__title"><?php esc_html_e( 'Built for speed', 'dejoiy' ); ?></h2>
			<div class="dq-features__grid">
				<?php foreach ( dejoiy_quick_launch_feature_cards() as $card ) : ?>
					<article class="dq-glass dq-feature">
						<span class="dq-feature__icon" aria-hidden="true"><?php echo esc_html( $card['icon'] ); ?></span>
						<h3 class="dq-feature__title"><?php echo esc_html( $card['title'] ); ?></h3>
						<p class="dq-feature__desc"><?php echo esc_html( $card['desc'] ); ?></p>
					</article>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="dq-launch__soon dq-reveal" aria-labelledby="dq-soon-title">
			<h2 id="dq-soon-title" class="dq-section__title"><?php esc_html_e( 'Coming Soon', 'dejoiy' ); ?></h2>
			<p class="dq-section__sub"><?php esc_html_e( 'Categories we are preparing for launch', 'dejoiy' ); ?></p>
			<div class="dq-soon__grid">
				<?php foreach ( dejoiy_quick_launch_category_cards() as $cat ) : ?>
					<div class="dq-glass dq-soon__card">
						<span class="dq-soon__icon" aria-hidden="true"><?php echo esc_html( $cat['icon'] ); ?></span>
						<span class="dq-soon__label"><?php echo esc_html( $cat['label'] ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="dq-launch__finale dq-reveal">
			<div class="dq-glass dq-finale">
				<h2 class="dq-finale__title"><?php esc_html_e( 'Something Fast is Coming.', 'dejoiy' ); ?></h2>
				<p class="dq-finale__text">
					<?php esc_html_e( 'Join the waitlist and be among the first to experience DEJOIY Quick.', 'dejoiy' ); ?>
				</p>
				<button type="button" class="dq-btn dq-btn--primary dq-btn--wide" data-dq-open-notify>
					<?php esc_html_e( 'Join the Waitlist', 'dejoiy' ); ?>
				</button>
			</div>
		</section>

		<footer class="dq-launch__footer">
			<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> DEJOIY. <?php esc_html_e( 'All rights reserved.', 'dejoiy' ); ?></p>
		</footer>

		<div class="dq-modal" id="dq-notify-modal" hidden role="dialog" aria-modal="true" aria-labelledby="dq-notify-title">
			<div class="dq-modal__backdrop" data-dq-close-modal tabindex="-1"></div>
			<div class="dq-modal__panel dq-glass">
				<button type="button" class="dq-modal__close" data-dq-close-modal aria-label="<?php esc_attr_e( 'Close', 'dejoiy' ); ?>">&times;</button>
				<h2 id="dq-notify-title" class="dq-modal__title"><?php esc_html_e( 'Get launch updates', 'dejoiy' ); ?></h2>
				<p class="dq-modal__sub"><?php esc_html_e( 'Be the first to know when DEJOIY Quick goes live in your area.', 'dejoiy' ); ?></p>
				<form class="dq-notify-form" id="dq-notify-form" novalidate>
					<div class="dq-field">
						<label for="dq-notify-name"><?php esc_html_e( 'Name', 'dejoiy' ); ?></label>
						<input type="text" id="dq-notify-name" name="name" autocomplete="name" required minlength="2" maxlength="80" />
						<span class="dq-field__error" data-error-for="name"></span>
					</div>
					<div class="dq-field">
						<label for="dq-notify-email"><?php esc_html_e( 'Email', 'dejoiy' ); ?></label>
						<input type="email" id="dq-notify-email" name="email" autocomplete="email" required maxlength="120" />
						<span class="dq-field__error" data-error-for="email"></span>
					</div>
					<div class="dq-field">
						<label for="dq-notify-phone"><?php esc_html_e( 'Mobile Number', 'dejoiy' ); ?></label>
						<input type="tel" id="dq-notify-phone" name="phone" inputmode="numeric" autocomplete="tel" required pattern="[0-9]{10}" maxlength="10" placeholder="10-digit mobile" />
						<span class="dq-field__error" data-error-for="phone"></span>
					</div>
					<button type="submit" class="dq-btn dq-btn--primary dq-btn--wide" data-dq-submit>
						<?php esc_html_e( 'Submit', 'dejoiy' ); ?>
					</button>
					<p class="dq-notify-form__success" id="dq-notify-success" hidden role="status">
						<?php esc_html_e( "Thank you! We'll notify you when DEJOIY Quick launches.", 'dejoiy' ); ?>
					</p>
				</form>
			</div>
		</div>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * @param array<int, string> $classes Body classes.
 * @return array<int, string>
 */
function dejoiy_quick_launch_body_class( $classes ) {
	if ( dejoiy_quickmart_is_launch_home() ) {
		$classes[] = 'dejoiy-quick-launch';
		$classes[] = 'dejoiy-mobile-os-off';
	}
	return $classes;
}
add_filter( 'body_class', 'dejoiy_quick_launch_body_class', 26 );

/**
 * SEO title.
 *
 * @param array<string, string> $parts Title parts.
 * @return array<string, string>
 */
function dejoiy_quick_launch_document_title( $parts ) {
	if ( dejoiy_quickmart_is_launch_home() ) {
		$parts['title'] = 'DEJOIY Quick | 10–30 Minute Delivery';
	}
	return $parts;
}
add_filter( 'document_title_parts', 'dejoiy_quick_launch_document_title', 20 );

/**
 * Meta description.
 */
function dejoiy_quick_launch_meta() {
	if ( ! dejoiy_quickmart_is_launch_home() ) {
		return;
	}
	echo '<meta name="description" content="' . esc_attr( 'DEJOIY Quick is launching soon. Experience fast delivery of groceries, essentials, stationery, personal care products and more.' ) . '" />' . "\n";
}
add_action( 'wp_head', 'dejoiy_quick_launch_meta', 1 );

/**
 * Enqueue launch assets only on launch home.
 */
function dejoiy_quick_launch_assets() {
	if ( ! dejoiy_quickmart_is_launch_home() ) {
		return;
	}
	$uri = get_stylesheet_directory_uri();
	$dir = get_stylesheet_directory();
	$css = $dir . '/dejoiy-quick-launch.css';
	$js  = $dir . '/dejoiy-quick-launch.js';
	if ( is_readable( $css ) ) {
		wp_enqueue_style(
			'dejoiy-quick-launch',
			$uri . '/dejoiy-quick-launch.css',
			array(),
			(string) filemtime( $css )
		);
	}
	if ( is_readable( $js ) ) {
		wp_enqueue_script(
			'dejoiy-quick-launch',
			$uri . '/dejoiy-quick-launch.js',
			array(),
			(string) filemtime( $js ),
			true
		);
		wp_localize_script(
			'dejoiy-quick-launch',
			'dejoiyQuickLaunch',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'dejoiy_quick_launch' ),
				'action'  => 'dejoiy_quick_launch_notify',
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'dejoiy_quick_launch_assets', 10071 );

/**
 * AJAX: waitlist signup.
 */
function dejoiy_quick_launch_ajax_notify() {
	check_ajax_referer( 'dejoiy_quick_launch', 'nonce' );

	$name  = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$phone = isset( $_POST['phone'] ) ? preg_replace( '/\D/', '', (string) wp_unslash( $_POST['phone'] ) ) : '';

	if ( strlen( $name ) < 2 ) {
		wp_send_json_error( array( 'message' => __( 'Please enter your name.', 'dejoiy' ) ) );
	}
	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'dejoiy' ) ) );
	}
	if ( ! preg_match( '/^[6-9]\d{9}$/', $phone ) ) {
		wp_send_json_error( array( 'message' => __( 'Please enter a valid 10-digit mobile number.', 'dejoiy' ) ) );
	}

	$list = get_option( 'dejoiy_quick_launch_waitlist', array() );
	if ( ! is_array( $list ) ) {
		$list = array();
	}

	foreach ( $list as $row ) {
		if ( is_array( $row ) && isset( $row['email'] ) && strtolower( (string) $row['email'] ) === strtolower( $email ) ) {
			wp_send_json_success(
				array(
					'message' => __( "Thank you! We'll notify you when DEJOIY Quick launches.", 'dejoiy' ),
				)
			);
		}
	}

	$list[] = array(
		'name'    => $name,
		'email'   => $email,
		'phone'   => $phone,
		'created' => gmdate( 'c' ),
		'ip_hash' => hash( 'sha256', (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) ),
	);

	// Cap stored entries to avoid option bloat.
	if ( count( $list ) > 5000 ) {
		$list = array_slice( $list, -5000 );
	}

	update_option( 'dejoiy_quick_launch_waitlist', $list, false );

	wp_send_json_success(
		array(
			'message' => __( "Thank you! We'll notify you when DEJOIY Quick launches.", 'dejoiy' ),
		)
	);
}
add_action( 'wp_ajax_dejoiy_quick_launch_notify', 'dejoiy_quick_launch_ajax_notify' );
add_action( 'wp_ajax_nopriv_dejoiy_quick_launch_notify', 'dejoiy_quick_launch_ajax_notify' );
