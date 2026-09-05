<?php
/**
 * Empty cart page (child override).
 *
 * Keeps a single <h1> on the cart page; the empty-cart heading is demoted to <h2>.
 *
 * @package dejoiy
 */

defined( 'ABSPATH' ) || exit;

wc_print_notices();

do_action( 'woocommerce_cart_is_empty' );

$button_class       = wc_wp_theme_get_element_class_name( 'button' );
$return_shop_url    = wc_get_page_id( 'shop' ) > 0 ? get_permalink( wc_get_page_id( 'shop' ) ) : '#';
?>

<div class="cart-empty empty-cart-block wc-empty-cart-message">
	<h2 class="cart-empty-title"><?php esc_html_e( 'Your shopping cart is empty', 'dejoiy' ); ?></h2>
	<p><?php esc_html_e( 'We invite you to get acquainted with an assortment of our shop. Surely you can find something for yourself!', 'dejoiy' ); ?></p>
	<p>
		<a class="btn black<?php echo esc_attr( $button_class ? ' ' . $button_class : '' ); ?>" href="<?php echo esc_url( $return_shop_url ); ?>">
			<span><?php esc_html_e( 'Return To Shop', 'dejoiy' ); ?></span>
		</a>
	</p>
</div>