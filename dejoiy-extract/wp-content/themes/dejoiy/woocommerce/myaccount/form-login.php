<?php
/**
 * DEJOIY Customer Login Form Override
 *
 * Provides the unified, production-grade DEJOIY authentication experience
 * on customer-facing https://dejoiy.com/my-account/
 *
 * @package Dejoiy
 */

if (!defined('ABSPATH')) {
    exit;
}

do_action('woocommerce_before_customer_login_form');
?>
<div class="dejoiy-customer-login-page">
    <?php
    if (class_exists('DSO_Login')) {
        echo DSO_Login::render_login_form([
            'is_seller' => false,
            'redirect_to' => function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/')
        ]);
    } else {
        $login_class_file = WP_PLUGIN_DIR . '/dejoiy-seller-os/includes/class-dso-login.php';
        if (file_exists($login_class_file)) {
            require_once $login_class_file;
            echo DSO_Login::render_login_form([
                'is_seller' => false,
                'redirect_to' => function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/')
            ]);
        }
    }
    ?>
</div>

<style>
    /* Ensure exact center alignment on WooCommerce My Account */
    body.woocommerce-account:not(.logged-in) .content-layout-wrapper,
    body.woocommerce-account:not(.logged-in) .page-wrapper,
    body.woocommerce-account:not(.logged-in) .page-content {
        background-color: #f8fafc;
        padding-top: 20px;
        padding-bottom: 40px;
        width: 100% !important;
    }
    body.woocommerce-account:not(.logged-in) .content.col-md-12 {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
        max-width: 100% !important;
        float: none !important;
        margin: 0 auto !important;
        padding: 0 !important;
    }
    body.woocommerce-account:not(.logged-in) .woocommerce {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 auto !important;
        padding: 0 16px !important;
    }
    body.woocommerce-account:not(.logged-in) .woocommerce-notices-wrapper {
        width: 100% !important;
        max-width: 440px !important;
        margin: 0 auto 12px !important;
    }
    .dejoiy-customer-login-page {
        width: 100% !important;
        max-width: 440px !important;
        margin: 0 auto !important;
        display: flex !important;
        justify-content: center !important;
    }
    /* Hide theme default column wrappers on login page */
    body.woocommerce-account:not(.logged-in) .col2-set {
        display: none !important;
    }
</style>
<?php
do_action('woocommerce_after_customer_login_form');
