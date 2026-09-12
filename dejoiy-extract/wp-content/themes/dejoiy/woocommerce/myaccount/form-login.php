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
    /* Ensure clean container alignment on WooCommerce My Account */
    body.woocommerce-account:not(.logged-in) .content-layout-wrapper,
    body.woocommerce-account:not(.logged-in) .page-wrapper {
        background-color: #f8fafc;
        padding-top: 20px;
        padding-bottom: 40px;
    }
    body.woocommerce-account:not(.logged-in) .woocommerce {
        display: flex;
        justify-content: center;
        width: 100%;
    }
    body.woocommerce-account:not(.logged-in) .woocommerce-notices-wrapper {
        width: 100%;
        max-width: 410px;
        margin: 0 auto 12px;
    }
    .dejoiy-customer-login-page {
        width: 100%;
        max-width: 410px;
        margin: 0 auto;
    }
    /* Hide theme default column wrappers on login page */
    body.woocommerce-account:not(.logged-in) .col2-set {
        display: none !important;
    }
</style>
<?php
do_action('woocommerce_after_customer_login_form');
