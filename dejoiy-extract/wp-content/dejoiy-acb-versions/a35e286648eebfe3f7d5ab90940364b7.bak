<?php
/**
 * Studio cart — isolated layout, studio-line items only.
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

$dsu_studio_items = function_exists( 'dejoiy_studio_get_cart_studio_items' )
	? dejoiy_studio_get_cart_studio_items()
	: array();
?>
<div class="dsu-wc-wrap">
	<?php require get_stylesheet_directory() . '/studio-header.php'; ?>
	<main class="dsu-wc-main dsu-cart woocommerce">
		<div class="dsu-wc-breadcrumb">
			<a href="<?php echo esc_url( home_url( '/dejoiy-custom-studio/' ) ); ?>">← Custom Studio</a>
		</div>
		<h1 class="dsu-cart-title">Studio Cart</h1>
		<p class="dsu-cart-sub">Your custom studio creations only — add more from the studio or proceed to checkout.</p>
		<div class="dsu-cart-shell">
			<?php
			if ( empty( $dsu_studio_items ) ) {
				?>
				<div class="dsu-cart-empty-msg">
					<p>Your studio cart is empty. Add a customizable product from the studio with your design details.</p>
					<a href="<?php echo esc_url( home_url( '/dejoiy-custom-studio/#dsu-bestsellers' ) ); ?>" class="dsu-btn dsu-btn-primary">Browse best sellers</a>
				</div>
				<?php
			} elseif ( function_exists( 'dejoiy_studio_render_cart_table' ) ) {
				dejoiy_studio_render_cart_table( $dsu_studio_items );
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
