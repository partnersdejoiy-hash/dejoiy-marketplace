<?php
/**
 * Preferred DEJOIY Nexus virtual pages.
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( function_exists( 'dejoiy_library_ensure_customize_loaded' ) ) {
	dejoiy_library_ensure_customize_loaded();
}

$screen = function_exists( 'dejoiy_library_nexus_route_screen' ) ? dejoiy_library_nexus_route_screen() : 'home';
if ( '' === $screen ) {
	$screen = 'home';
}

status_header( 200 );

if ( function_exists( 'dejoiy_library_document_start' ) ) {
	dejoiy_library_document_start();
} else {
	get_header();
}

if ( in_array( $screen, array( 'home', 'shop' ), true ) && function_exists( 'dejoiy_library_universe_render' ) ) {
	echo dejoiy_library_universe_render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
} else {
	require get_stylesheet_directory() . '/library-header.php';
	dejoiy_nexus_virtual_page_render( $screen );
	require get_stylesheet_directory() . '/library-footer.php';
}

if ( function_exists( 'dejoiy_library_document_end' ) ) {
	dejoiy_library_document_end();
} else {
	get_footer();
}

/**
 * Render a safe read-only Nexus utility page.
 *
 * @param string $screen Screen key.
 */
function dejoiy_nexus_virtual_page_render( $screen ) {
	$config = dejoiy_nexus_virtual_page_config( $screen );
	?>
	<main class="dlu-page dlu-nexus-virtual dlu-nexus-<?php echo esc_attr( $screen ); ?>">
		<div class="dlu-page-in">
			<p class="dlu-crumb"><a href="<?php echo esc_url( dejoiy_library_get_landing_url() ); ?>">&larr; DEJOIY Nexus</a></p>
			<section class="dlu-nexus-panel dlu-nexus-panel--hero">
				<p class="dlu-kicker"><?php echo esc_html( $config['kicker'] ); ?></p>
				<h1><?php echo esc_html( $config['title'] ); ?></h1>
				<p class="dlu-page-sub"><?php echo esc_html( $config['description'] ); ?></p>
				<div class="dlu-hero-ctas">
					<a class="dlu-btn-primary" href="<?php echo esc_url( home_url( '/nexus/shop/' ) ); ?>">Explore Library</a>
					<a class="dlu-btn-secondary" href="<?php echo esc_url( home_url( '/nexus/shelf/' ) ); ?>">Continue Learning</a>
				</div>
			</section>
			<?php
			if ( 'shelf' === $screen ) {
				dejoiy_nexus_render_shelf();
			} elseif ( 'library' === $screen ) {
				dejoiy_nexus_render_library_dashboard();
			} elseif ( 'learning' === $screen ) {
				dejoiy_nexus_render_learning_dashboard();
			} elseif ( 'account' === $screen ) {
				dejoiy_nexus_render_account_bridge();
			}
			?>
		</div>
	</main>
	<?php
}

/**
 * @param string $screen Screen key.
 * @return array<string,string>
 */
function dejoiy_nexus_virtual_page_config( $screen ) {
	$pages = array(
		'shelf'   => array(
			'kicker'      => 'Nexus Shelf',
			'title'       => 'Your Digital Library',
			'description' => 'Purchased, free, downloaded, and saved knowledge resources stay connected to your DEJOIY account.',
		),
		'library' => array(
			'kicker'      => 'Nexus Library Dashboard',
			'title'       => 'Learning Intelligence',
			'description' => 'A premium dashboard for books, courses, downloads, progress, and future certificates.',
		),
		'learning' => array(
			'kicker'      => 'My Learning',
			'title'       => 'Continue Learning',
			'description' => 'Learning paths, courses, certificates, quizzes, and Moodle integrations are prepared for future expansion.',
		),
		'account' => array(
			'kicker'      => 'Nexus Account',
			'title'       => 'One DEJOIY Identity',
			'description' => 'Nexus uses the same WordPress, WooCommerce, login, payments, orders, and customer account system.',
		),
	);
	return isset( $pages[ $screen ] ) ? $pages[ $screen ] : $pages['library'];
}

/**
 * Render user's downloadable shelf without changing orders or accounts.
 */
function dejoiy_nexus_render_shelf() {
	if ( ! is_user_logged_in() ) {
		?>
		<div class="dlu-empty dlu-nexus-empty">
			<p>Log in to see purchased books, downloads, and saved learning resources in your Nexus Shelf.</p>
			<a class="dlu-btn-primary" href="<?php echo esc_url( wp_login_url( home_url( '/nexus/shelf/' ) ) ); ?>">Log in to Nexus</a>
		</div>
		<?php
		return;
	}

	$downloads = function_exists( 'wc_get_customer_available_downloads' ) ? wc_get_customer_available_downloads( get_current_user_id() ) : array();
	?>
	<section class="dlu-nexus-grid" aria-label="Nexus Shelf sections">
		<?php foreach ( array( 'Currently Reading', 'Purchased', 'Downloaded', 'Saved', 'Wishlist', 'Recently Opened' ) as $label ) : ?>
			<article class="dlu-nexus-mini-card">
				<span><?php echo esc_html( $label ); ?></span>
				<strong><?php echo 'Downloaded' === $label ? esc_html( (string) count( $downloads ) ) : '0'; ?></strong>
				<small>Connected to WooCommerce access</small>
			</article>
		<?php endforeach; ?>
	</section>
	<section class="dlu-nexus-panel">
		<h2>Available Downloads</h2>
		<?php if ( empty( $downloads ) ) : ?>
			<p class="dlu-page-sub">No unlocked downloadable Nexus resources yet. Purchased digital products will appear here automatically when WooCommerce grants access.</p>
		<?php else : ?>
			<div class="dlu-nexus-resource-grid">
				<?php foreach ( array_slice( $downloads, 0, 12 ) as $download ) : ?>
					<article class="dlu-nexus-resource-card">
						<h3><?php echo esc_html( $download['product_name'] ?? 'Nexus Resource' ); ?></h3>
						<p><?php echo esc_html( $download['download_name'] ?? 'Digital resource' ); ?></p>
						<a class="dlu-btn-secondary" href="<?php echo esc_url( $download['download_url'] ?? '#' ); ?>">Download</a>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</section>
	<?php
}

/**
 * Render read-only dashboard widgets.
 */
function dejoiy_nexus_render_library_dashboard() {
	$total_books = 0;
	if ( function_exists( 'dejoiy_library_query_books' ) ) {
		$query = dejoiy_library_query_books( array( 'posts_per_page' => 1 ) );
		if ( $query instanceof WP_Query ) {
			$total_books = (int) $query->found_posts;
			wp_reset_postdata();
		}
	}
	$downloads = ( is_user_logged_in() && function_exists( 'wc_get_customer_available_downloads' ) ) ? wc_get_customer_available_downloads( get_current_user_id() ) : array();
	$widgets = array(
		'Total Books'   => $total_books,
		'Total Courses' => 0,
		'Hours Learned' => 0,
		'Certificates'  => 0,
		'Downloads'     => count( $downloads ),
		'Progress'      => 'Ready',
	);
	?>
	<section class="dlu-nexus-grid" aria-label="Nexus Library dashboard widgets">
		<?php foreach ( $widgets as $label => $value ) : ?>
			<article class="dlu-nexus-mini-card">
				<span><?php echo esc_html( $label ); ?></span>
				<strong><?php echo esc_html( (string) $value ); ?></strong>
				<small>Nexus knowledge ecosystem</small>
			</article>
		<?php endforeach; ?>
	</section>
	<section class="dlu-nexus-panel">
		<h2>Future-ready learning architecture</h2>
		<p>Prepared for Moodle integration, learning paths, certificates, quizzes, AI tutor, learning streaks, community discussions, author profiles, and reading analytics without changing current marketplace systems.</p>
	</section>
	<?php
}

/**
 * Render learning placeholder using current account/session only.
 */
function dejoiy_nexus_render_learning_dashboard() {
	?>
	<section class="dlu-nexus-panel">
		<h2>Courses and learning paths</h2>
		<p>Purchased courses will unlock through WooCommerce access rules. Moodle, certificates, quizzes, and AI tutor features can be added later on this route without a separate WordPress install.</p>
	</section>
	<?php
}

/**
 * Render account bridge.
 */
function dejoiy_nexus_render_account_bridge() {
	$account_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/my-account/' );
	?>
	<section class="dlu-nexus-panel">
		<h2>Same account, Nexus experience</h2>
		<p>Use your existing DEJOIY customer account for orders, downloads, payment methods, addresses, and future learning records.</p>
		<a class="dlu-btn-primary" href="<?php echo esc_url( $account_url ); ?>">Open DEJOIY Account</a>
	</section>
	<?php
}
