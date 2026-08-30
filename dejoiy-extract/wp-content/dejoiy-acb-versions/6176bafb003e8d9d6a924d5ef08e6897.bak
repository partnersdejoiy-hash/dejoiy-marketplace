<?php
/**
 * My Nexus — purchased books, reading progress, downloads.
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require get_stylesheet_directory() . '/library-header.php';

$shelf     = function_exists( 'dejoiy_library_lms_get_shelf_entries' ) ? dejoiy_library_lms_get_shelf_entries() : array();
$downloads = function_exists( 'dejoiy_library_lms_get_download_entries' ) ? dejoiy_library_lms_get_download_entries() : array();
$logged_in = is_user_logged_in();
?>
<main class="dlu-page dlu-my-universe" id="dlu-my-universe">
	<div class="dlu-page-in">
		<p class="dlu-crumb"><a href="<?php echo esc_url( dejoiy_library_get_landing_url() ); ?>">← DEJOIY Nexus</a></p>
		<h1>My Nexus</h1>
		<p class="dlu-page-sub">Your living knowledge collection — resume reading, downloads, and favorites.</p>

		<?php if ( ! $logged_in ) : ?>
			<p class="dlu-reader-note"><?php esc_html_e( 'Sign in to see purchased books and sync reading progress across devices.', 'dejoiy' ); ?></p>
			<p><a class="dlu-btn-primary" href="<?php echo esc_url( wp_login_url( dejoiy_library_my_universe_url() ) ); ?>"><?php esc_html_e( 'Sign in', 'dejoiy' ); ?></a></p>
		<?php endif; ?>

		<section class="dlu-dash-sec">
			<h2><?php esc_html_e( 'Your library (LMS)', 'dejoiy' ); ?></h2>
			<p class="dlu-page-sub"><?php esc_html_e( 'Books you purchased appear here for in-browser reading with resume. PDF/download-only editions are listed under Downloads.', 'dejoiy' ); ?></p>
			<?php if ( $logged_in && empty( $shelf ) ) : ?>
				<p class="dlu-reader-note"><?php esc_html_e( 'No in-browser editions yet. Buy a book from Discover or check Downloads below.', 'dejoiy' ); ?></p>
			<?php endif; ?>
			<ul class="products dlu-books-grid columns-4 dlu-lms-shelf">
				<?php foreach ( $shelf as $entry ) : ?>
					<li class="dlu-book dlu-lms-card">
						<a href="<?php echo esc_url( $entry['read_url'] ); ?>" class="dlu-book-link">
							<img src="<?php echo esc_url( $entry['cover'] ); ?>" alt="" loading="lazy" />
							<h3><?php echo esc_html( $entry['title'] ); ?></h3>
							<?php if ( ! empty( $entry['author'] ) ) : ?>
								<p class="dlu-book-author"><?php echo esc_html( $entry['author'] ); ?></p>
							<?php endif; ?>
							<progress class="dlu-lms-progress-bar" value="<?php echo esc_attr( (string) $entry['progress'] ); ?>" max="100"></progress>
							<span class="dlu-lms-progress-label"><?php echo esc_html( (string) $entry['progress'] ); ?>% <?php esc_html_e( 'read', 'dejoiy' ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>

		<?php if ( ! empty( $downloads ) ) : ?>
		<section class="dlu-dash-sec">
			<h2><?php esc_html_e( 'Downloads', 'dejoiy' ); ?></h2>
			<ul class="dlu-lms-downloads">
				<?php foreach ( $downloads as $entry ) : ?>
					<li class="dlu-lms-download-item">
						<strong><?php echo esc_html( $entry['title'] ); ?></strong>
						<?php foreach ( $entry['downloads'] as $dl ) : ?>
							<a href="<?php echo esc_url( $dl['url'] ); ?>" class="dlu-btn-secondary" download><?php echo esc_html( $dl['name'] ); ?></a>
						<?php endforeach; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
		<?php endif; ?>

		<section class="dlu-dash-sec">
			<h2><?php esc_html_e( 'Nexus Wishlist', 'dejoiy' ); ?></h2>
			<div id="dlu-favorites" class="dlu-fav-grid"></div>
		</section>
	</div>
</main>
<?php
require get_stylesheet_directory() . '/library-footer.php';
