<?php
/**
 * DEJOIY Dedicated My Account Pages.
 * 
 * Hides the sidebar navigation on endpoint pages so they appear as dedicated full-width pages.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function dejoiy_my_account_dedicated_pages_css() {
	if ( ! is_account_page() || ( is_account_page() && empty( WC()->query->get_current_endpoint() ) ) ) {
		return;
	}
	// We are on a sub-page (endpoint)
	?>
	<style>
		.woocommerce-MyAccount-navigation-wrapper { display: none !important; }
		.woocommerce-MyAccount-content { width: 100% !important; flex: 0 0 100% !important; max-width: 100% !important; border: none !important; padding: 0 !important; }
        .woocommerce-account .row.content-layout-wrapper > .woocommerce { display: block !important; }
        .woocommerce-account .woocommerce-MyAccount-content .title, .woocommerce-account .woocommerce-MyAccount-content h3 { border-bottom: 2px solid #f0f2f2; padding-bottom: 12px; margin-bottom: 24px; font-weight: 700; color: #0f1111; }
        /* Add a back to dashboard link */
	</style>
	<?php
}
add_action( 'wp_head', 'dejoiy_my_account_dedicated_pages_css', 999 );

function dejoiy_my_account_back_link() {
    if ( ! is_account_page() || empty( WC()->query->get_current_endpoint() ) ) {
        return;
    }
    ?>
    <div style="margin-bottom: 20px;">
        <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'dashboard' ) ); ?>" style="display:inline-flex; align-items:center; color:#007185; font-weight:600; text-decoration:none;">
            <span style="margin-right:6px; font-size:16px;">←</span> <?php esc_html_e( 'Back to DEJOIY Space', 'dejoiy' ); ?>
        </a>
    </div>
    <?php
}
add_action( 'woocommerce_account_content', 'dejoiy_my_account_back_link', 1 );
