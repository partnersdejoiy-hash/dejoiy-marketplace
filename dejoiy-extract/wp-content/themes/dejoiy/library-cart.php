<?php
/**
 * Library Cart — Nexus shelf (purchased / owned editions).
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( function_exists( 'dejoiy_library_ensure_cart_loaded' ) ) {
	dejoiy_library_ensure_cart_loaded();
}
if ( function_exists( 'dejoiy_library_set_active_cart_nexus' ) ) {
	dejoiy_library_set_active_cart_nexus();
}

dejoiy_library_document_start();
if ( function_exists( 'dejoiy_library_get_nexus_cart_panel_data' ) ) {
	$GLOBALS['dejoiy_nexus_shelf_badge'] = function_exists( 'dejoiy_library_nexus_badge_count' )
		? dejoiy_library_nexus_badge_count( dejoiy_library_get_nexus_cart_panel_data() )
		: 0;
}
require get_stylesheet_directory() . '/library-header.php';

$entries = array();
if ( is_user_logged_in() && function_exists( 'dejoiy_library_lms_get_shelf_entries' ) ) {
	$entries = dejoiy_library_lms_get_shelf_entries();
}

$pending = function_exists( 'dejoiy_library_get_pending_nexus_cart_data' )
	? dejoiy_library_get_pending_nexus_cart_data()
	: array( 'count' => 0, 'items' => array() );
?>
<main class="dlu-page dlu-cart-page">
	<div class="dlu-page-in">
		<p class="dlu-crumb"><a href="<?php echo esc_url( dejoiy_library_get_landing_url() ); ?>">← DEJOIY Nexus</a></p>
		<h1>Your Nexus Shelf</h1>
		<p class="dlu-page-sub">Purchased and unlocked editions in your personal library — not your checkout cart.</p>
		<?php if ( ! empty( $pending['items'] ) ) : ?>
			<section class="dlu-nexus-pending-cart">
				<h2><?php esc_html_e( 'In your checkout queue', 'dejoiy' ); ?></h2>
				<p class="dlu-page-sub"><?php esc_html_e( 'These editions are waiting for payment — they join your shelf after checkout completes.', 'dejoiy' ); ?></p>
				<ul class="dlu-pending-cart-list">
					<?php foreach ( $pending['items'] as $item ) : ?>
						<li>
							<a href="<?php echo esc_url( $item['read_url'] ?? dejoiy_library_product_url( (int) ( $item['id'] ?? 0 ) ) ); ?>"><?php echo esc_html( $item['title'] ?? '' ); ?></a>
							<?php if ( ! empty( $item['price'] ) ) : ?>
								<span class="dlu-pending-price"><?php echo esc_html( $item['price'] ); ?></span>
							<?php endif; ?>
							<?php if ( ! empty( $item['remove_url'] ) ) : ?>
								<a href="<?php echo esc_url( $item['remove_url'] ); ?>" class="dlu-shelf-remove"><?php esc_html_e( 'Remove', 'dejoiy' ); ?></a>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
				<a href="<?php echo esc_url( dejoiy_library_get_checkout_url() ); ?>" class="dlu-btn-primary"><?php esc_html_e( 'Complete Your Collection', 'dejoiy' ); ?></a>
			</section>
		<?php endif; ?>

		<?php if ( ! is_user_logged_in() ) : ?>
			<?php if ( empty( $pending['items'] ) ) : ?>
			<div class="dlu-empty">
				<p><?php esc_html_e( 'Log in to see books you own on your Nexus shelf.', 'dejoiy' ); ?></p>
				<a href="<?php echo esc_url( wp_login_url( dejoiy_library_get_cart_url() ) ); ?>" class="dlu-btn-primary"><?php esc_html_e( 'Log in', 'dejoiy' ); ?></a>
			</div>
			<?php else : ?>
			<p class="dlu-page-sub"><?php esc_html_e( 'Log in to keep purchased editions on your shelf after checkout.', 'dejoiy' ); ?></p>
			<a href="<?php echo esc_url( wp_login_url( dejoiy_library_get_cart_url() ) ); ?>" class="dlu-btn-secondary"><?php esc_html_e( 'Log in', 'dejoiy' ); ?></a>
			<?php endif; ?>
		<?php elseif ( empty( $entries ) ) : ?>
			<?php if ( empty( $pending['items'] ) ) : ?>
			<div class="dlu-empty">
				<p><?php esc_html_e( 'No purchased books on your shelf yet.', 'dejoiy' ); ?></p>
				<a href="<?php echo esc_url( dejoiy_library_get_landing_url() ); ?>#dlu-discover" class="dlu-btn-primary"><?php esc_html_e( 'Discover books', 'dejoiy' ); ?></a>
			</div>
			<?php endif; ?>
		<?php else : ?>
			<div class="dlu-shelf-list">
				<?php
				foreach ( $entries as $entry ) {
					$id    = isset( $entry['id'] ) ? (int) $entry['id'] : 0;
					$cover = $entry['cover'] ?? '';
					$read  = $entry['read_url'] ?? dejoiy_library_reader_url( $id );
					$prog  = isset( $entry['progress'] ) ? (int) $entry['progress'] : 0;
					?>
					<div class="dlu-shelf-item">
						<a href="<?php echo esc_url( $read ); ?>" class="dlu-shelf-stack-link">
							<div class="dlu-shelf-stack" style="background-image:url(<?php echo esc_url( $cover ); ?>)"></div>
						</a>
						<div class="dlu-shelf-meta">
							<h2><a href="<?php echo esc_url( $read ); ?>"><?php echo esc_html( $entry['title'] ?? '' ); ?></a></h2>
							<p><?php echo esc_html( $entry['author'] ?? '' ); ?> · <?php esc_html_e( 'Digital', 'dejoiy' ); ?></p>
							<?php if ( $prog > 0 ) : ?>
								<p class="dlu-shelf-price"><?php echo esc_html( sprintf( /* translators: %d: reading progress percent */ __( '%d%% read', 'dejoiy' ), $prog ) ); ?></p>
							<?php endif; ?>
							<a href="<?php echo esc_url( dejoiy_library_get_shelf_remove_url( $id ) ); ?>" class="dlu-shelf-remove"><?php esc_html_e( 'Remove from shelf', 'dejoiy' ); ?></a>
						</div>
					</div>
					<?php
				}
				?>
			</div>
		<?php endif; ?>

<?php
require get_stylesheet_directory() . '/library-footer.php';
dejoiy_library_document_end();
