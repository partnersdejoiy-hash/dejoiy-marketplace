<?php
/**
 * Studio single product — isolated layout, standard WooCommerce product UI.
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
	<main class="dsu-wc-main dsu-single-product">
		<div class="dsu-wc-breadcrumb">
			<a href="<?php echo esc_url( home_url( '/dejoiy-custom-studio/' ) ); ?>">← Custom Studio</a>
		</div>
		<div class="dsu-single-shell woocommerce">
			<?php
			while ( have_posts() ) :
				the_post();
				wc_get_template_part( 'content', 'single-product' );
			endwhile;
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
