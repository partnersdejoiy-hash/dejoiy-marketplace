<?php
/**
 * Studio checkout — isolated layout, standard WooCommerce checkout.
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

dejoiy_studio_maybe_set_cookie();

if ( function_exists( 'dejoiy_studio_document_start' ) ) {
	dejoiy_studio_document_start();
} else {
	get_header( 'shop' );
}
?>
<div class="dsu-wc-wrap">
	<?php require get_stylesheet_directory() . '/studio-header.php'; ?>
	<main class="dsu-wc-main dsu-checkout woocommerce">
		<div class="dsu-wc-breadcrumb">
			<a href="<?php echo esc_url( home_url( '/dejoiy-custom-studio/' ) ); ?>">← Custom Studio</a>
		</div>
		<h1 class="dsu-checkout-title">Studio Checkout</h1>
		<p class="dsu-checkout-sub">Complete your custom creation order. Your customization details are attached to each line item.</p>
		<div class="dsu-checkout-shell">
			<?php
			if ( function_exists( 'woocommerce_checkout' ) ) {
				woocommerce_checkout();
			} else {
				echo do_shortcode( '[woocommerce_checkout]' );
			}
			?>
		</div>
	</main>
	<?php require get_stylesheet_directory() . '/studio-footer.php'; ?>
</div>
<?php
if ( function_exists( 'dejoiy_studio_document_end' ) ) {
	dejoiy_studio_document_end();
} else {
	get_footer( 'shop' );
}
