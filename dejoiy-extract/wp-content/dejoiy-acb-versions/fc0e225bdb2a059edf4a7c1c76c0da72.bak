<?php
/**
 * Library Checkout — Complete Your Collection.
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( function_exists( 'dejoiy_library_prepare_nexus_checkout' ) ) {
	dejoiy_library_prepare_nexus_checkout();
} elseif ( function_exists( 'dejoiy_library_ensure_cart_loaded' ) ) {
	dejoiy_library_ensure_cart_loaded();
}

$has_shelf = function_exists( 'dejoiy_library_get_nexus_cart_quantity' )
	? dejoiy_library_get_nexus_cart_quantity() > 0
	: ( function_exists( 'dejoiy_library_cart_has_items' ) && dejoiy_library_cart_has_items() );

dejoiy_library_document_start();
require get_stylesheet_directory() . '/library-header.php';
?>
<main class="dlu-page dlu-checkout-page">
	<div class="dlu-page-in">
		<p class="dlu-crumb"><a href="<?php echo esc_url( dejoiy_library_get_cart_url() ); ?>">← DEJOIY Nexus</a></p>
		<h1 class="dlu-checkout-heading">Complete Your Collection</h1>
		<p class="dlu-page-sub dlu-checkout-tagline">You're one step from expanding your universe.</p>
		<div class="dlu-checkout-shell woocommerce">
			<?php
			if ( ! $has_shelf ) {
				?>
				<div class="dlu-empty dlu-checkout-empty">
					<p><?php esc_html_e( 'Your Nexus shelf is empty. Add a library book, then return here to checkout.', 'dejoiy' ); ?></p>
					<a href="<?php echo esc_url( dejoiy_library_get_landing_url() ); ?>#dlu-discover" class="dlu-btn-primary"><?php esc_html_e( 'Discover books', 'dejoiy' ); ?></a>
					<a href="<?php echo esc_url( dejoiy_library_get_cart_url() ); ?>" class="dlu-btn-secondary"><?php esc_html_e( 'View Nexus shelf', 'dejoiy' ); ?></a>
				</div>
				<?php
			} elseif ( function_exists( 'dejoiy_library_render_nexus_checkout_html' ) ) {
				dejoiy_library_render_nexus_checkout_html();
			}
			?>
		</div>
	</div>
</main>
<?php
require get_stylesheet_directory() . '/library-footer.php';
if ( function_exists( 'wp_reset_postdata' ) ) {
	wp_reset_postdata();
}
dejoiy_library_document_end();
