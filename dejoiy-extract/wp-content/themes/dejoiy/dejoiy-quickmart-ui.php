<?php
/**
 * QuickMart UI — Blinkit-inspired layouts (DEJOIY branding).
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return string
 */
function dejoiy_quickmart_base_url() {
	return home_url( '/dejoiy-quick-mart/' );
}

/**
 * @return bool
 */
function dejoiy_quickmart_is_search_view() {
	if ( ! function_exists( 'dejoiy_quickmart_is_quickmart_page' ) || ! dejoiy_quickmart_is_quickmart_page() ) {
		return false;
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$view = isset( $_GET['qm_view'] ) ? sanitize_key( wp_unslash( $_GET['qm_view'] ) ) : '';
	return 'search' === $view;
}

/**
 * @return bool
 */
function dejoiy_quickmart_is_orders_view() {
	if ( ! function_exists( 'dejoiy_quickmart_is_quickmart_page' ) || ! dejoiy_quickmart_is_quickmart_page() ) {
		return false;
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$view = isset( $_GET['qm_view'] ) ? sanitize_key( wp_unslash( $_GET['qm_view'] ) ) : '';
	return 'orders' === $view;
}

/**
 * @return bool
 */
function dejoiy_quickmart_is_cart_view() {
	if ( ! function_exists( 'dejoiy_quickmart_is_quickmart_page' ) || ! dejoiy_quickmart_is_quickmart_page() ) {
		return false;
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$view = isset( $_GET['qm_view'] ) ? sanitize_key( wp_unslash( $_GET['qm_view'] ) ) : '';
	return 'cart' === $view;
}

/**
 * @return bool
 */
function dejoiy_quickmart_is_checkout_view() {
	if ( ! function_exists( 'dejoiy_quickmart_is_quickmart_page' ) || ! dejoiy_quickmart_is_quickmart_page() ) {
		return false;
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$view = isset( $_GET['qm_view'] ) ? sanitize_key( wp_unslash( $_GET['qm_view'] ) ) : '';
	return 'checkout' === $view;
}

/**
 * QuickMart cart URL (in-app, not marketplace /cart/).
 *
 * @return string
 */
function dejoiy_quickmart_cart_url() {
	return add_query_arg( 'qm_view', 'cart', dejoiy_quickmart_base_url() );
}

/**
 * QuickMart checkout URL (in-app).
 *
 * @return string
 */
function dejoiy_quickmart_checkout_url() {
	return add_query_arg( 'qm_view', 'checkout', dejoiy_quickmart_base_url() );
}

/**
 * @return array<int, array<string, mixed>>
 */
function dejoiy_quickmart_get_cart_lines() {
	$lines = array();
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return $lines;
	}
	foreach ( WC()->cart->get_cart() as $key => $item ) {
		if ( ! function_exists( 'dejoiy_quickmart_cart_line_is_quickmart' ) || ! dejoiy_quickmart_cart_line_is_quickmart( $item ) ) {
			continue;
		}
		$product = isset( $item['data'] ) ? $item['data'] : null;
		if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
			continue;
		}
		$lines[] = array(
			'key'          => $key,
			'product'      => $product,
			'qty'          => isset( $item['quantity'] ) ? (int) $item['quantity'] : 1,
			'subtotal'     => WC()->cart->get_product_subtotal( $product, $item['quantity'] ),
			'line_subtotal'=> isset( $item['line_subtotal'] ) ? (float) $item['line_subtotal'] : 0,
		);
	}
	return $lines;
}

/**
 * @param string $q Optional prefilled query.
 * @return string
 */
function dejoiy_quickmart_search_url( $q = '' ) {
	$args = array( 'qm_view' => 'search' );
	if ( '' !== $q ) {
		$args['q'] = $q;
	}
	return add_query_arg( $args, dejoiy_quickmart_base_url() );
}

/**
 * @return array<int, array<string, string>>
 */
function dejoiy_quickmart_nav_tabs() {
	$tabs = array(
		array( 'id' => 'all', 'label' => __( 'All', 'dejoiy' ), 'slug' => '' ),
	);
	if ( function_exists( 'dejoiy_quickmart_categories' ) ) {
		foreach ( dejoiy_quickmart_categories() as $cat ) {
			$tabs[] = array(
				'id'    => $cat['slug'],
				'label' => $cat['label'],
				'slug'  => $cat['slug'],
			);
		}
	}
	return $tabs;
}

/**
 * Bestseller category tiles (Blinkit-style grid cards).
 *
 * @return array<int, array<string, string>>
 */
function dejoiy_quickmart_bestseller_groups() {
	return array(
		array( 'slug' => 'fruits', 'label' => __( 'Vegetables & Fruits', 'dejoiy' ), 'more' => '+120' ),
		array( 'slug' => 'dairy', 'label' => __( 'Dairy, Bread & Eggs', 'dejoiy' ), 'more' => '+85' ),
		array( 'slug' => 'snacks', 'label' => __( 'Snacks & Munchies', 'dejoiy' ), 'more' => '+200' ),
		array( 'slug' => 'beverages', 'label' => __( 'Cold Drinks & Juices', 'dejoiy' ), 'more' => '+90' ),
		array( 'slug' => 'bakery', 'label' => __( 'Bakery & Biscuits', 'dejoiy' ), 'more' => '+60' ),
		array( 'slug' => 'personal-care', 'label' => __( 'Personal Care', 'dejoiy' ), 'more' => '+150' ),
	);
}

/**
 * @param array<string, string> $group Group config.
 * @return string
 */

/**
 * @param WC_Product $product Product.
 * @return bool
 */
function dejoiy_quickmart_product_has_image( $product ) {
	if ( ! $product ) {
		return false;
	}
	$img_id = $product->get_image_id();
	if ( ! $img_id ) {
		return false;
	}
	$src = wp_get_attachment_image_url( $img_id, 'thumbnail' );
	return is_string( $src ) && '' !== $src;
}

function dejoiy_quickmart_render_bestseller_tile( $group ) {
	$home      = dejoiy_quickmart_base_url();
	$url       = add_query_arg( 'qm_cat', $group['slug'], $home );
	$mock_urls = function_exists( 'dejoiy_quickmart_mock_images' ) ? dejoiy_quickmart_mock_images( $group['slug'] ) : array();
	$thumbs    = array();
	for ( $i = 0; $i < 4; $i++ ) {
		if ( isset( $mock_urls[ $i ] ) && function_exists( 'dejoiy_quickmart_mock_img_html' ) ) {
			$thumbs[] = dejoiy_quickmart_mock_img_html( $mock_urls[ $i ], 'qm-bestseller__thumb', $group['label'] );
		} else {
			$thumbs[] = '<span class="qm-bestseller__ph" aria-hidden="true"></span>';
		}
	}

	ob_start();
	?>
	<a class="qm-bestseller" href="<?php echo esc_url( $url ); ?>">
		<div class="qm-bestseller__grid">
			<?php
			foreach ( $thumbs as $i => $thumb ) {
				$more = ( 0 === $i && ! empty( $group['more'] ) ) ? '<span class="qm-bestseller__more">' . esc_html( $group['more'] ) . ' ' . esc_html__( 'more', 'dejoiy' ) . '</span>' : '';
				echo '<div class="qm-bestseller__cell">' . $thumb . $more . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			?>
		</div>
		<span class="qm-bestseller__label"><?php echo esc_html( $group['label'] ); ?></span>
	</a>
	<?php
	return (string) ob_get_clean();
}

/**
 * @param WC_Product $product Product.
 * @param string       $tint  Pastel background.
 * @return string
 */
function dejoiy_quickmart_render_grocery_tile( $product, $tint = '#E8F5E9' ) {
	if ( ! $product ) {
		return '';
	}
	$url = function_exists( 'dejoiy_ecosystem_product_url' ) ? dejoiy_ecosystem_product_url( $product->get_id(), 'quickmart' ) : $product->get_permalink();
	ob_start();
	?>
	<a class="qm-grocery-tile" href="<?php echo esc_url( $url ); ?>" style="--qm-tile-bg:<?php echo esc_attr( $tint ); ?>">
		<?php echo $product->get_image( 'woocommerce_thumbnail', array( 'class' => 'qm-grocery-tile__img' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<span class="qm-grocery-tile__name"><?php echo esc_html( wp_trim_words( $product->get_name(), 4, '…' ) ); ?></span>
	</a>
	<?php
	return (string) ob_get_clean();
}

/**
 * Blinkit-style home content.
 *
 * @return string
 */
function dejoiy_quickmart_blinkit_home_html() {
	$pin  = dejoiy_quickmart_get_pincode();
	$loc  = dejoiy_quickmart_get_location_label();
	$tabs = dejoiy_quickmart_nav_tabs();
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$active_tab = isset( $_GET['qm_tab'] ) ? sanitize_key( wp_unslash( $_GET['qm_tab'] ) ) : '';
	if ( '' === $active_tab && isset( $_GET['qm_cat'] ) ) {
		$active_tab = sanitize_key( wp_unslash( $_GET['qm_cat'] ) );
	}
	if ( '' === $active_tab ) {
		$active_tab = 'all';
	}

	$grocery = dejoiy_quickmart_query_products( array( 'posts_per_page' => 12, 'orderby' => 'rand' ) );
	$tints   = array( '#E3F2FD', '#E8F5E9', '#FFF8E1', '#FCE4EC', '#F3E5F5', '#E0F7FA' );

	ob_start();
	?>
	<div id="dejoiy-quickmart-app" class="dejoiy-quickmart-app dejoiy-quickmart-app--home">
		<section class="qm-fk-promo" aria-label="<?php esc_attr_e( 'Offers', 'dejoiy' ); ?>">
			<div class="qm-fk-promo__inner">
				<p class="qm-fk-promo__tag"><?php esc_html_e( 'Quick deals', 'dejoiy' ); ?></p>
				<h2 class="qm-fk-promo__title"><?php esc_html_e( 'Stock up on essentials', 'dejoiy' ); ?></h2>
				<span class="qm-fk-promo__cta"><?php esc_html_e( 'Explore', 'dejoiy' ); ?> →</span>
			</div>
		</section>
		<nav class="qm-fk-cats" aria-label="<?php esc_attr_e( 'Shop by category', 'dejoiy' ); ?>">
			<a class="qm-fk-cats__item<?php echo empty( $_GET['qm_cat'] ) ? ' is-active' : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>" href="<?php echo esc_url( dejoiy_quickmart_base_url() ); ?>"><span>🛒</span><?php esc_html_e( 'For You', 'dejoiy' ); ?></a>
			<?php
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$qm_cat_active = isset( $_GET['qm_cat'] ) ? sanitize_key( wp_unslash( $_GET['qm_cat'] ) ) : '';
			if ( function_exists( 'dejoiy_quickmart_categories' ) ) :
				foreach ( dejoiy_quickmart_categories() as $cat ) :
					$cat_href = add_query_arg( 'qm_cat', $cat['slug'], dejoiy_quickmart_base_url() );
					?>
				<a class="qm-fk-cats__item<?php echo $qm_cat_active === $cat['slug'] ? ' is-active' : ''; ?>" href="<?php echo esc_url( $cat_href ); ?>"><span><?php echo esc_html( $cat['icon'] ); ?></span><?php echo esc_html( $cat['label'] ); ?></a>
				<?php
				endforeach;
			endif;
			?>
		</nav>
		<nav class="qm-tabs" aria-label="<?php esc_attr_e( 'Browse', 'dejoiy' ); ?>">
			<?php foreach ( $tabs as $tab ) : ?>
				<a class="qm-tabs__item<?php echo $active_tab === $tab['id'] ? ' is-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'qm_tab', $tab['id'], dejoiy_quickmart_base_url() ) ); ?>"><?php echo esc_html( $tab['label'] ); ?></a>
			<?php endforeach; ?>
		</nav>

		<section class="qm-section">
			<h2 class="qm-section__title"><?php esc_html_e( 'Bestsellers', 'dejoiy' ); ?></h2>
			<div class="qm-bestsellers">
				<?php foreach ( dejoiy_quickmart_bestseller_groups() as $group ) : ?>
					<?php echo dejoiy_quickmart_render_bestseller_tile( $group ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="qm-section">
			<h2 class="qm-section__title"><?php esc_html_e( 'Grocery & Kitchen', 'dejoiy' ); ?></h2>
			<div class="qm-grocery-track">
				<?php
				$i = 0;
				while ( $grocery->have_posts() ) {
					$grocery->the_post();
					$p = wc_get_product( get_the_ID() );
					if ( $p ) {
						echo dejoiy_quickmart_render_grocery_tile( $p, $tints[ $i % count( $tints ) ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						++$i;
					}
				}
				wp_reset_postdata();
				?>
			</div>
		</section>

		<section class="qm-section">
			<h2 class="qm-section__title"><?php esc_html_e( 'Snacks & drinks', 'dejoiy' ); ?></h2>
			<div class="qm-shelf__track qm-shelf__track--cards">
				<?php
				$snacks = dejoiy_quickmart_query_products( array( 'posts_per_page' => 10, 'offset' => 3 ) );
				while ( $snacks->have_posts() ) {
					$snacks->the_post();
					$p = wc_get_product( get_the_ID() );
					if ( $p ) {
						echo dejoiy_quickmart_render_product_card( $p ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
				}
				wp_reset_postdata();
				?>
			</div>
		</section>

		<?php if ( ! $pin ) : ?>
			<p class="qm-pin-hint"><button type="button" class="qm-link-btn" data-qm-open-pin><?php esc_html_e( 'Add pincode to check delivery time', 'dejoiy' ); ?></button></p>
		<?php else : ?>
			<p class="qm-pin-hint qm-pin-hint--ok">📍 <?php echo esc_html( $loc ); ?></p>
		<?php endif; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}



/**
 * QuickMart cart (only quick lines — stays on QuickMart, not marketplace cart).
 *
 * @return string
 */
function dejoiy_quickmart_cart_page_html() {
	$home     = dejoiy_quickmart_base_url();
	$checkout = dejoiy_quickmart_checkout_url();
	$lines    = dejoiy_quickmart_get_cart_lines();
	$total    = 0.0;

	if ( $lines ) {
		foreach ( $lines as $line ) {
			$total += isset( $line['line_subtotal'] ) ? (float) $line['line_subtotal'] : 0;
		}
	}

	ob_start();
	?>
	<div id="dejoiy-quickmart-app" class="dejoiy-quickmart-app dejoiy-quickmart-app--cart">
		<section class="qm-section qm-cart-page">
			<h2 class="qm-section__title"><?php esc_html_e( 'QuickMart Cart', 'dejoiy' ); ?></h2>
			<p class="qm-cart-page__sub"><?php esc_html_e( 'Groceries & essentials only — separate from the main DEJOIY marketplace cart.', 'dejoiy' ); ?></p>
			<?php if ( empty( $lines ) ) : ?>
				<p class="qm-empty"><?php esc_html_e( 'Your quick cart is empty.', 'dejoiy' ); ?></p>
				<p><a class="qm-search-all" href="<?php echo esc_url( $home ); ?>"><?php esc_html_e( 'Start shopping', 'dejoiy' ); ?> →</a></p>
			<?php else : ?>
				<ul class="qm-cart-lines">
					<?php foreach ( $lines as $line ) : ?>
						<?php
						$p   = $line['product'];
						$key = $line['key'];
						$img = $p->get_image( 'thumbnail', array( 'class' => 'qm-cart-lines__img' ) );
						$url = function_exists( 'dejoiy_ecosystem_product_url' ) ? dejoiy_ecosystem_product_url( $p->get_id(), 'quickmart' ) : $p->get_permalink();
						$remove = wp_nonce_url(
							add_query_arg(
								array(
									'qm_view'      => 'cart',
									'remove_item'  => $key,
								),
								$home
							),
							'dejoiy-qm-remove-' . $key
						);
						?>
						<li class="qm-cart-lines__item">
							<a class="qm-cart-lines__media" href="<?php echo esc_url( $url ); ?>"><?php echo $img ? $img : '<span class="qm-cart-lines__ph"></span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
							<div class="qm-cart-lines__meta">
								<a class="qm-cart-lines__name" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $p->get_name() ); ?></a>
								<span class="qm-cart-lines__qty"><?php echo esc_html( sprintf( /* translators: %d quantity */ __( 'Qty %d', 'dejoiy' ), $line['qty'] ) ); ?></span>
								<span class="qm-cart-lines__price"><?php echo wp_kses_post( $line['subtotal'] ); ?></span>
							</div>
							<a class="qm-cart-lines__remove" href="<?php echo esc_url( $remove ); ?>" aria-label="<?php esc_attr_e( 'Remove', 'dejoiy' ); ?>">&times;</a>
						</li>
					<?php endforeach; ?>
				</ul>
				<div class="qm-cart-page__foot">
					<p class="qm-cart-page__total"><strong><?php esc_html_e( 'Subtotal', 'dejoiy' ); ?></strong> <?php echo wp_kses_post( wc_price( $total ) ); ?></p>
					<a class="qm-cart-page__checkout" href="<?php echo esc_url( $checkout ); ?>"><?php esc_html_e( 'Proceed to checkout', 'dejoiy' ); ?></a>
					<a class="qm-cart-page__continue" href="<?php echo esc_url( $home ); ?>"><?php esc_html_e( 'Continue shopping', 'dejoiy' ); ?></a>
				</div>
			<?php endif; ?>
		</section>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * QuickMart checkout inside app shell (avoids broken /checkout/ template when possible).
 *
 * @return string
 */
function dejoiy_quickmart_checkout_page_html() {
	$home = dejoiy_quickmart_base_url();
	$cart = dejoiy_quickmart_cart_url();
	if ( ! dejoiy_quickmart_get_cart_count() ) {
		ob_start();
		?>
		<div id="dejoiy-quickmart-app" class="dejoiy-quickmart-app dejoiy-quickmart-app--checkout">
			<p class="qm-empty"><?php esc_html_e( 'Your quick cart is empty.', 'dejoiy' ); ?></p>
			<p><a class="qm-search-all" href="<?php echo esc_url( $home ); ?>"><?php esc_html_e( 'Browse QuickMart', 'dejoiy' ); ?> →</a></p>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	ob_start();
	?>
	<div id="dejoiy-quickmart-app" class="dejoiy-quickmart-app dejoiy-quickmart-app--checkout">
		<section class="qm-section qm-checkout-page">
			<p class="qm-cart-page__sub"><a href="<?php echo esc_url( $cart ); ?>">← <?php esc_html_e( 'Back to QuickMart cart', 'dejoiy' ); ?></a></p>
			<h2 class="qm-section__title"><?php esc_html_e( 'QuickMart Checkout', 'dejoiy' ); ?></h2>
			<div class="qm-checkout-page__form">
				<?php echo do_shortcode( '[woocommerce_checkout]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</section>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Handle remove-from-cart on QuickMart cart view.
 */
function dejoiy_quickmart_cart_view_actions() {
	if ( ! dejoiy_quickmart_is_cart_view() ) {
		return;
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( empty( $_GET['remove_item'] ) ) {
		return;
	}
	$key = wc_clean( wp_unslash( $_GET['remove_item'] ) );
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! wp_verify_nonce( isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '', 'dejoiy-qm-remove-' . $key ) ) {
		return;
	}
	if ( function_exists( 'WC' ) && WC()->cart ) {
		WC()->cart->remove_cart_item( $key );
	}
	wp_safe_redirect( dejoiy_quickmart_cart_url() );
	exit;
}
add_action( 'template_redirect', 'dejoiy_quickmart_cart_view_actions', 5 );

/**
 * QuickMart-only orders view (no marketplace redirect).
 *
 * @return string
 */
function dejoiy_quickmart_orders_page_html() {
	$home    = dejoiy_quickmart_base_url();
	$account = home_url( '/my-account/' );
	$items   = array();

	if ( is_user_logged_in() && function_exists( 'wc_get_orders' ) ) {
		$orders = wc_get_orders(
			array(
				'customer_id' => get_current_user_id(),
				'limit'       => 12,
				'orderby'     => 'date',
				'order'       => 'DESC',
				'status'      => array( 'processing', 'completed', 'on-hold' ),
			)
		);
		foreach ( $orders as $order ) {
			if ( ! $order instanceof WC_Order ) {
				continue;
			}
			$is_quick = false;
			foreach ( $order->get_items() as $item ) {
				$product = $item->get_product();
				if ( $product && function_exists( 'dejoiy_quickmart_is_product' ) && dejoiy_quickmart_is_product( $product->get_id() ) ) {
					$is_quick = true;
					break;
				}
			}
			if ( $is_quick ) {
				$items[] = $order;
			}
		}
	}

	ob_start();
	?>
	<div id="dejoiy-quickmart-app" class="dejoiy-quickmart-app dejoiy-quickmart-app--orders">
		<section class="qm-section">
			<h2 class="qm-section__title"><?php esc_html_e( 'Your QuickMart orders', 'dejoiy' ); ?></h2>
			<?php if ( ! is_user_logged_in() ) : ?>
				<p class="qm-empty"><?php esc_html_e( 'Sign in to see your quick orders.', 'dejoiy' ); ?></p>
				<p><a class="qm-search-all" href="<?php echo esc_url( wp_login_url( add_query_arg( 'qm_view', 'orders', $home ) ) ); ?>"><?php esc_html_e( 'Sign in', 'dejoiy' ); ?> →</a></p>
			<?php elseif ( empty( $items ) ) : ?>
				<p class="qm-empty"><?php esc_html_e( 'No QuickMart orders yet. Start shopping!', 'dejoiy' ); ?></p>
				<p><a class="qm-search-all" href="<?php echo esc_url( $home ); ?>"><?php esc_html_e( 'Browse QuickMart', 'dejoiy' ); ?> →</a></p>
			<?php else : ?>
				<ul class="qm-orders-list">
					<?php foreach ( $items as $order ) : ?>
						<li class="qm-orders-list__item">
							<strong>#<?php echo esc_html( $order->get_order_number() ); ?></strong>
							<span><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></span>
							<span><?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></span>
							<span><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</section>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Dedicated search page (screenshot 2 style).
 *
 * @return string
 */
function dejoiy_quickmart_search_page_html() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$q         = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
	$trending  = dejoiy_quickmart_query_products( array( 'posts_per_page' => 8, 'orderby' => 'rand' ) );
	$home      = dejoiy_quickmart_base_url();
	$shop_url  = add_query_arg(
		array(
			'post_type'   => 'product',
			'product_cat' => 'quick-products',
			's'           => $q,
		),
		home_url( '/' )
	);

	ob_start();
	?>
	<div id="dejoiy-quickmart-app" class="dejoiy-quickmart-app dejoiy-quickmart-app--search">
		<?php if ( '' === $q ) : ?>
		<section class="qm-search-block">
			<div class="qm-search-block__head">
				<h2><?php esc_html_e( 'Recent searches', 'dejoiy' ); ?></h2>
				<button type="button" class="qm-search-block__clear" id="qm-recent-clear" hidden><?php esc_html_e( 'clear', 'dejoiy' ); ?></button>
			</div>
			<div class="qm-recent" id="qm-recent-list" aria-live="polite"></div>
		</section>

		<section class="qm-search-block qm-search-block--trending">
			<h2><?php esc_html_e( 'Trending in your city', 'dejoiy' ); ?></h2>
			<div class="qm-trending">
				<?php
				while ( $trending->have_posts() ) {
					$trending->the_post();
					$p = wc_get_product( get_the_ID() );
					if ( ! $p ) {
						continue;
					}
					$url   = dejoiy_quickmart_search_url( $p->get_name() );
					$thumb = $p->get_image( 'thumbnail', array( 'class' => 'qm-trending__img' ) );
					?>
					<a class="qm-trending__item" href="<?php echo esc_url( $url ); ?>">
						<span class="qm-trending__img-wrap"><?php echo $thumb ? $thumb : '<span class="qm-trending__ph"></span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="qm-trending__label"><?php echo esc_html( wp_trim_words( $p->get_name(), 4, '…' ) ); ?></span>
					</a>
					<?php
				}
				wp_reset_postdata();
				?>
			</div>
		</section>
		<?php else : ?>
		<section class="qm-search-block">
			<h2><?php echo esc_html( sprintf( /* translators: %s search query */ __( 'Results for "%s"', 'dejoiy' ), $q ) ); ?></h2>
			<p><a href="<?php echo esc_url( $shop_url ); ?>" class="qm-search-all"><?php esc_html_e( 'View all on QuickMart shop', 'dejoiy' ); ?> →</a></p>
			<div class="qm-shelf__grid">
				<?php
				$results = dejoiy_quickmart_query_products(
					array(
						'posts_per_page' => 12,
						's'              => $q,
					)
				);
				if ( $results->have_posts() ) {
					while ( $results->have_posts() ) {
						$results->the_post();
						$p = wc_get_product( get_the_ID() );
						if ( $p ) {
							echo dejoiy_quickmart_render_product_card( $p ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						}
					}
					wp_reset_postdata();
				} else {
					echo '<p class="qm-empty">' . esc_html__( 'No products found. Try another search.', 'dejoiy' ) . '</p>';
				}
				?>
			</div>
		</section>
		<?php endif; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}


/**
 * Pin modal + cart drawer (shared across chrome modes).
 */
function dejoiy_quickmart_print_shared_modals() {
	$cart = dejoiy_quickmart_get_cart_url();
	?>
	<div id="qm-pin-modal" class="qm-modal" hidden>
		<div class="qm-modal__backdrop" data-qm-close-pin></div>
		<div class="qm-modal__panel" role="dialog" aria-labelledby="qm-pin-title">
			<h2 id="qm-pin-title"><?php esc_html_e( 'Enter delivery pincode', 'dejoiy' ); ?></h2>
			<p><?php esc_html_e( 'We deliver groceries & essentials in minutes.', 'dejoiy' ); ?></p>
			<input type="text" class="qm-modal__input" id="qm-pin-input" inputmode="numeric" maxlength="6" placeholder="110001" />
			<p class="qm-modal__error" id="qm-pin-error" hidden></p>
			<button type="button" class="qm-modal__btn" id="qm-pin-submit"><?php esc_html_e( 'Check availability', 'dejoiy' ); ?></button>
		</div>
	</div>
	<aside id="qm-cart-drawer" class="qm-drawer" hidden aria-hidden="true">
		<div class="qm-drawer__backdrop" data-qm-close-cart></div>
		<div class="qm-drawer__panel">
			<header class="qm-drawer__head">
				<h2><?php esc_html_e( 'QuickMart Cart', 'dejoiy' ); ?></h2>
				<button type="button" data-qm-close-cart aria-label="<?php esc_attr_e( 'Close', 'dejoiy' ); ?>">&times;</button>
			</header>
			<div class="qm-drawer__body" id="qm-cart-drawer-body">
				<p class="qm-drawer__empty"><?php esc_html_e( 'Your quick cart is empty.', 'dejoiy' ); ?></p>
			</div>
			<footer class="qm-drawer__foot">
				<a class="qm-drawer__checkout" href="<?php echo esc_url( $cart ); ?>"><?php esc_html_e( 'Open QuickMart cart', 'dejoiy' ); ?></a>
			</footer>
		</div>
	</aside>
	<?php
}

/**
 * Blinkit-style header chrome.
 */
function dejoiy_quickmart_print_blinkit_chrome() {
	if ( function_exists( 'dejoiy_quickmart_is_flow_surface' ) && ! dejoiy_quickmart_is_flow_surface() ) {
		return;
	}
	if ( ! function_exists( 'dejoiy_quickmart_is_flow_surface' ) && ! dejoiy_quickmart_is_app_page() ) {
		return;
	}
	if ( function_exists( 'dejoiy_quickmart_chrome_mode' ) && 'flow' === dejoiy_quickmart_chrome_mode() ) {
		if ( function_exists( 'dejoiy_quickmart_print_flow_chrome' ) ) {
			dejoiy_quickmart_print_flow_chrome();
		}
		return;
	}
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;

	$home    = dejoiy_quickmart_base_url();
	$search  = dejoiy_quickmart_search_url();
	$cart    = dejoiy_quickmart_get_cart_url();
	$account = home_url( '/my-account/' );
	$orders  = function_exists( 'dejoiy_quickmart_orders_url' ) ? dejoiy_quickmart_orders_url() : add_query_arg( 'qm_view', 'orders', $home );
	$pin     = dejoiy_quickmart_get_pincode();
	$loc     = dejoiy_quickmart_get_location_label();
	$count   = dejoiy_quickmart_get_cart_count();
	$eta     = function_exists( 'dejoiy_quickmart_eta_display' ) ? dejoiy_quickmart_eta_display() : dejoiy_quickmart_eta_label();
	$is_search = dejoiy_quickmart_is_search_view();
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$prefill = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';

	?>
	<div id="dejoiy-quickmart-chrome" class="dejoiy-quickmart-chrome<?php echo $is_search ? ' dejoiy-quickmart-chrome--search' : ''; ?>">
		<?php if ( ! $is_search ) : ?>
		<p class="qm-roots-banner qm-roots-banner--chrome" role="status"><?php esc_html_e( 'We are setting our roots to your location', 'dejoiy' ); ?></p>
		<?php endif; ?>
		<?php if ( $is_search ) : ?>
		<header class="qm-top qm-top--search" role="banner">
			<div class="qm-top__searchbar">
				<a class="qm-top__back" href="<?php echo esc_url( $home ); ?>" aria-label="<?php esc_attr_e( 'Back', 'dejoiy' ); ?>">←</a>
				<form class="qm-top__search-form" action="<?php echo esc_url( dejoiy_quickmart_search_url() ); ?>" method="get" role="search">
					<input type="hidden" name="qm_view" value="search" />
					<input type="search" name="q" class="qm-top__search-input" id="qm-search-page-input" value="<?php echo esc_attr( $prefill ); ?>" placeholder="<?php esc_attr_e( 'Search atta, dal, snacks and more', 'dejoiy' ); ?>" autocomplete="off" autofocus />
					<span class="qm-top__mic" aria-hidden="true">🎤</span>
				</form>
			</div>
		</header>
		<?php else : ?>
		<div class="qm-fk-mobile-head">
			<nav class="qm-fk-tabs" aria-label="<?php esc_attr_e( 'DEJOIY services', 'dejoiy' ); ?>">
				<a class="qm-fk-tabs__item" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'DEJOIY', 'dejoiy' ); ?></a>
				<a class="qm-fk-tabs__item is-active" href="<?php echo esc_url( $home ); ?>"><?php esc_html_e( 'QuickMart', 'dejoiy' ); ?></a>
			</nav>
		</div>
		<header class="qm-top qm-top--fk" role="banner">
			<div class="qm-top__delivery">
				<div class="qm-top__delivery-main">
					<span class="qm-top__brand-kicker"><?php esc_html_e( 'QuickMart in', 'dejoiy' ); ?></span>
					<strong class="qm-top__eta"><?php echo esc_html( $eta ); ?></strong>
				</div>
				<div class="qm-top__actions">
					<button type="button" class="qm-top__cart-btn" data-qm-open-cart aria-label="<?php esc_attr_e( 'Cart', 'dejoiy' ); ?>">
						<span class="qm-top__cart-ico" aria-hidden="true">🛒</span>
						<?php if ( $count > 0 ) : ?>
							<span class="qm-top__cart-badge"><?php echo esc_html( (string) $count ); ?></span>
						<?php endif; ?>
					</button>
					<a class="qm-top__profile" href="<?php echo esc_url( $account ); ?>" aria-label="<?php esc_attr_e( 'Account', 'dejoiy' ); ?>">👤</a>
				</div>
			</div>
			<button type="button" class="qm-top__address" data-qm-open-pin>
				<span class="qm-top__address-text"><?php echo esc_html( $pin ? $loc : __( 'Set delivery location', 'dejoiy' ) ); ?></span>
				<span class="qm-top__address-chev" aria-hidden="true">▾</span>
			</button>
			<a class="qm-top__search-pill" href="<?php echo esc_url( $search ); ?>">
				<span class="qm-top__search-ico" aria-hidden="true">🔍</span>
				<span class="qm-top__search-ph"><?php esc_html_e( 'Search "snacks, dal, cold drink"', 'dejoiy' ); ?></span>
			</a>
		</header>
		<?php endif; ?>

		<nav class="qm-bottom-nav" aria-label="<?php esc_attr_e( 'QuickMart', 'dejoiy' ); ?>">
			<a href="<?php echo esc_url( $home ); ?>" class="qm-bottom-nav__item<?php echo ! $is_search && ! ( function_exists( 'dejoiy_quickmart_is_cart_view' ) && dejoiy_quickmart_is_cart_view() ) && ! ( function_exists( 'dejoiy_quickmart_is_checkout_view' ) && dejoiy_quickmart_is_checkout_view() ) && ! ( function_exists( 'dejoiy_quickmart_is_orders_view' ) && dejoiy_quickmart_is_orders_view() ) ? ' is-active' : ''; ?>">
				<span aria-hidden="true">⌂</span>
				<?php esc_html_e( 'Home', 'dejoiy' ); ?>
			</a>
			<a href="<?php echo esc_url( $orders ); ?>" class="qm-bottom-nav__item<?php echo function_exists( 'dejoiy_quickmart_is_orders_view' ) && dejoiy_quickmart_is_orders_view() ? ' is-active' : ''; ?>">
				<span aria-hidden="true">🛍</span>
				<?php esc_html_e( 'Order again', 'dejoiy' ); ?>
			</a>
			<a href="<?php echo esc_url( add_query_arg( 'qm_tab', 'all', $home ) ); ?>" class="qm-bottom-nav__item">
				<span aria-hidden="true">▦</span>
				<?php esc_html_e( 'Categories', 'dejoiy' ); ?>
			</a>
			<a href="<?php echo esc_url( $search ); ?>" class="qm-bottom-nav__item<?php echo $is_search ? ' is-active' : ''; ?>">
				<span aria-hidden="true">🔍</span>
				<?php esc_html_e( 'Search', 'dejoiy' ); ?>
			</a>
		</nav>
	</div>

	<?php dejoiy_quickmart_print_shared_modals(); ?>
	<?php
}
