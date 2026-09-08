<?php
/**
 * DEJOIY Mobile OS — site-wide mobile/tablet header + bottom nav (≤1024px).
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dedicated ecosystem pages use their own chrome — no Mobile OS header/footer.
 *
 * @return bool
 */
function dejoiy_mobile_os_is_dedicated_page() {
	if ( is_admin() ) {
		return false;
	}

	$uri = strtolower( (string) wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) );
	$needles = array(
		'dejoiy-custom-studio',
		'dejoiy-quick-mart',
		'dejoiy-services',
		'dejoiy-refurbished',
		'dejoiy-library',
		'dejoiy_library=1',
	);

	foreach ( $needles as $needle ) {
		if ( false !== strpos( $uri, $needle ) ) {
			return true;
		}
	}

	if ( is_page() ) {
		$slug = (string) get_post_field( 'post_name', get_queried_object_id() );
		$dedicated = array(
			'dejoiy-custom-studio',
			'dejoiy-quick-mart',
			'dejoiy-services',
			'dejoiy-refurbished',
			'dejoiy-library',
		);
		if ( in_array( $slug, $dedicated, true ) ) {
			return true;
		}
	}

	return false;
}

/**
 * @return bool
 */
function dejoiy_mobile_os_enabled() {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return false;
	}
	if ( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) {
		return false;
	}
	if ( dejoiy_mobile_os_is_dedicated_page() ) {
		return false;
	}
	if ( function_exists( 'is_product' ) && is_product() ) {
		return false;
	}
	return true;
}

/**
 * WooCommerce checkout (not thank-you / order-received).
 *
 * @return bool
 */
function dejoiy_mobile_os_is_checkout_focus() {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return false;
	}
	if ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'order-received' ) ) {
		return false;
	}
	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		return true;
	}
	if ( function_exists( 'wc_get_page_id' ) ) {
		$checkout_id = (int) wc_get_page_id( 'checkout' );
		if ( $checkout_id > 0 && is_page( $checkout_id ) ) {
			return true;
		}
	}
	if ( is_page() ) {
		$slug = (string) get_post_field( 'post_name', get_queried_object_id() );
		if ( 'checkout' === $slug ) {
			return true;
		}
	}
	$uri = strtolower( (string) wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) );
	if ( '' === $uri || false !== strpos( $uri, 'order-received' ) ) {
		return false;
	}
	return (bool) preg_match( '#/checkout(/|\?|#|$)#', $uri );
}

/**
 * WooCommerce cart page (not order-received / other endpoints).
 *
 * @return bool
 */
function dejoiy_mobile_os_is_cart_focus() {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return false;
	}
	if ( dejoiy_mobile_os_is_checkout_focus() ) {
		return false;
	}
	return function_exists( 'is_cart' ) && is_cart() && ! is_wc_endpoint_url();
}

/**
 * QuickMart shelf on shared /cart/ URL.
 *
 * @return bool
 */
function dejoiy_mobile_os_is_quickmart_cart_context() {
	if ( ! dejoiy_mobile_os_is_cart_focus() ) {
		return false;
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['dejoiy_quickmart'] ) && '1' === (string) wp_unslash( $_GET['dejoiy_quickmart'] ) ) {
		return true;
	}
	return function_exists( 'dejoiy_quickmart_is_cart_view' ) && dejoiy_quickmart_is_cart_view();
}

/**
 * Cart top switcher — DEJOIY Shop vs DEJOIY Quick.
 *
 * @return array<int, array<string, string>>
 */
function dejoiy_mobile_os_cart_modes() {
	$cart_url  = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );
	$quick_url = function_exists( 'dejoiy_quickmart_cart_url' )
		? dejoiy_quickmart_cart_url()
		: add_query_arg( 'dejoiy_quickmart', '1', $cart_url );
	$is_quick  = dejoiy_mobile_os_is_quickmart_cart_context();

	return array(
		array(
			'id'     => 'shop',
			'label'  => 'DEJOIY',
			'sub'    => __( 'Shop', 'dejoiy' ),
			'url'    => $cart_url,
			'active' => ! $is_quick,
		),
		array(
			'id'     => 'quick',
			'label'  => 'DEJOIY',
			'sub'    => __( 'Quick', 'dejoiy' ),
			'url'    => $quick_url,
			'active' => $is_quick,
		),
	);
}

/**
 * Emoji / icon map for marketplace categories (Flipkart-style circles).
 *
 * @param string $slug Category slug.
 * @param string $name Category name.
 * @return string
 */
function dejoiy_mobile_os_category_icon( $slug, $name ) {
	$slug = sanitize_title( $slug );
	$map  = array(
		'baby'           => '👶',
		'baby-products'  => '👶',
		'fashion'        => '👗',
		'clothing'       => '👕',
		'electronics'    => '📱',
		'mobiles'        => '📱',
		'home'           => '🏠',
		'home-kitchen'   => '🍳',
		'beauty'         => '💄',
		'beauty-health'  => '💄',
		'sports'         => '⚽',
		'books'          => '📚',
		'grocery'        => '🛒',
		'grocery-gourmet' => '🛒',
		'toys'           => '🧸',
		'jewellery'      => '💎',
		'appliances'     => '🔌',
		'furniture'      => '🛋️',
		'automotive'     => '🚗',
		'pet-supplies'   => '🐾',
		'health'         => '💊',
		'dejoiy-library' => '📚',
		'e-books'        => '📖',
	);

	if ( isset( $map[ $slug ] ) ) {
		return $map[ $slug ];
	}

	$name_l = strtolower( $name );
	foreach ( $map as $key => $icon ) {
		if ( false !== strpos( $name_l, str_replace( '-', ' ', $key ) ) ) {
			return $icon;
		}
	}

	return '◆';
}

/**
 * Fallback marketplace categories when Woo terms are sparse.
 *
 * @return array<int, array<string, string>>
 */
function dejoiy_mobile_os_market_categories_fallback() {
	$shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
	return array(
		array( 'label' => __( 'Baby', 'dejoiy' ), 'url' => add_query_arg( array( 'product_cat' => 'baby' ), $shop ), 'icon' => '👶', 'img' => '' ),
		array( 'label' => __( 'Fashion', 'dejoiy' ), 'url' => add_query_arg( array( 'product_cat' => 'fashion' ), $shop ), 'icon' => '👗', 'img' => '' ),
		array( 'label' => __( 'Electronics', 'dejoiy' ), 'url' => add_query_arg( array( 'product_cat' => 'electronics' ), $shop ), 'icon' => '📱', 'img' => '' ),
		array( 'label' => __( 'Home', 'dejoiy' ), 'url' => add_query_arg( array( 'product_cat' => 'home' ), $shop ), 'icon' => '🏠', 'img' => '' ),
		array( 'label' => __( 'Beauty', 'dejoiy' ), 'url' => add_query_arg( array( 'product_cat' => 'beauty' ), $shop ), 'icon' => '💄', 'img' => '' ),
		array( 'label' => __( 'Sports', 'dejoiy' ), 'url' => add_query_arg( array( 'product_cat' => 'sports' ), $shop ), 'icon' => '⚽', 'img' => '' ),
		array( 'label' => __( 'Books', 'dejoiy' ), 'url' => add_query_arg( array( 'product_cat' => 'books' ), $shop ), 'icon' => '📚', 'img' => '' ),
		array( 'label' => __( 'Grocery', 'dejoiy' ), 'url' => add_query_arg( array( 'product_cat' => 'grocery' ), $shop ), 'icon' => '🛒', 'img' => '' ),
		array( 'label' => __( 'Toys', 'dejoiy' ), 'url' => add_query_arg( array( 'product_cat' => 'toys' ), $shop ), 'icon' => '🧸', 'img' => '' ),
		array( 'label' => __( 'Appliances', 'dejoiy' ), 'url' => add_query_arg( array( 'product_cat' => 'appliances' ), $shop ), 'icon' => '🔌', 'img' => '' ),
	);
}

/**
 * Main WooCommerce marketplace categories for circular nav.
 *
 * @return array<int, array<string, string>>
 */
function dejoiy_mobile_os_market_categories() {
	$items = array();
	$skip  = array( 'uncategorized', 'uncategorised' );

	if ( taxonomy_exists( 'product_cat' ) ) {
		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => true,
				'parent'     => 0,
				'number'     => 14,
				'orderby'    => 'count',
				'order'      => 'DESC',
			)
		);

		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				if ( in_array( $term->slug, $skip, true ) ) {
					continue;
				}
				$link = get_term_link( $term );
				if ( is_wp_error( $link ) ) {
					continue;
				}
				$thumb_id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
				$items[]  = array(
					'label' => $term->name,
					'url'   => $link,
					'icon'  => dejoiy_mobile_os_category_icon( $term->slug, $term->name ),
					'img'   => $thumb_id ? (string) wp_get_attachment_image_url( $thumb_id, 'woocommerce_thumbnail' ) : '',
				);
			}
		}
	}

	if ( count( $items ) < 6 ) {
		$seen = array();
		foreach ( $items as $it ) {
			$seen[ $it['label'] ] = true;
		}
		foreach ( dejoiy_mobile_os_market_categories_fallback() as $fb ) {
			if ( count( $items ) >= 12 ) {
				break;
			}
			if ( isset( $seen[ $fb['label'] ] ) ) {
				continue;
			}
			$items[] = $fb;
		}
	}

	return array_slice( $items, 0, 12 );
}

/**
 * @return array<int, array<string, mixed>>
 */
function dejoiy_mobile_os_chips() {
	$home = home_url( '/' );
	return array(
		array(
			'id'     => 'foryou',
			'label'  => __( 'For You', 'dejoiy' ),
			'url'    => $home,
			'icon'   => '✨',
			'color'  => '#FACC15',
			'active' => is_front_page(),
		),
		array(
			'id'    => 'marketplace',
			'label' => 'Marketplace',
			'url'   => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : $home,
			'icon'  => '◆',
			'color' => '#2563EB',
		),
		array(
			'id'    => 'nexus',
			'label' => 'Nexus',
			'url'   => home_url( '/dejoiy-library/?dejoiy_library=1' ),
			'icon'  => '✦',
			'color' => '#7C3AED',
		),
		array(
			'id'    => 'quickmart',
			'label' => 'QuickMart',
			'url'   => home_url( '/dejoiy-quick-mart/' ),
			'icon'  => '⚡',
			'color' => '#16A34A',
		),
		array(
			'id'    => 'studio',
			'label' => 'Studio',
			'url'   => home_url( '/dejoiy-custom-studio/' ),
			'icon'  => '✿',
			'color' => '#EC4899',
		),
		array(
			'id'    => 'services',
			'label' => 'Services',
			'url'   => home_url( '/dejoiy-services/' ),
			'icon'  => '◎',
			'color' => '#EA580C',
		),
		array(
			'id'    => 'refurbished',
			'label' => 'Refurbished',
			'url'   => home_url( '/dejoiy-refurbished/' ),
			'icon'  => '↻',
			'color' => '#0D9488',
		),
	);
}

/**
 * @return array<int, array<string, string>>
 */
function dejoiy_mobile_os_modes() {
	return array(
		array(
			'id'     => 'shop',
			'label'  => 'DEJOIY',
			'sub'    => __( 'Shop', 'dejoiy' ),
			'url'    => home_url( '/' ),
			'active' => is_front_page(),
		),
		array(
			'id'    => 'nexus',
			'label' => 'Nexus',
			'sub'   => __( 'Books & courses', 'dejoiy' ),
			'url'   => home_url( '/dejoiy-library/?dejoiy_library=1' ),
		),
	);
}

/**
 * @return string
 */
function dejoiy_mobile_os_delivery_label() {
	if ( function_exists( 'WC' ) && WC()->customer ) {
		$city  = trim( (string) WC()->customer->get_shipping_city() );
		$state = trim( (string) WC()->customer->get_shipping_state() );
		if ( $city ) {
			return $state ? $city . ', ' . $state : $city;
		}
	}
	return __( 'Set delivery location', 'dejoiy' );
}

/**
 * @return string
 */
function dejoiy_mobile_os_delivery_url() {
	if ( function_exists( 'wc_get_account_endpoint_url' ) ) {
		return wc_get_account_endpoint_url( 'edit-address' );
	}
	return home_url( '/my-account/' );
}

/**
 * @return array<int, string>
 */
function dejoiy_mobile_os_search_hints() {
	return array(
		__( 'Searching for your Joy? Ask JOI! ✨', 'dejoiy' ),
		__( 'JOI is finding picks for you…', 'dejoiy' ),
		__( 'Find books & courses — Ask JOI 📚', 'dejoiy' ),
		__( 'Need something custom? Ask JOI 🎨', 'dejoiy' ),
		__( 'Discover services on DEJOIY 💼', 'dejoiy' ),
	);
}

/**
 * @return string
 */
/**
 * Minimal cart chrome — DEJOIY / DEJOIY Quick pills only.
 *
 * @return string
 */
function dejoiy_mobile_os_cart_bar_html() {
	ob_start();
	?>
	<div id="dejoiy-mobile-os-cart-bar" class="dejoiy-mobile-os-cart-bar" role="banner">
		<div class="dejoiy-mobile-os-cart-bar__inner">
			<div class="dm-modes dm-cart-modes" role="tablist" aria-label="<?php esc_attr_e( 'Cart experience', 'dejoiy' ); ?>">
				<?php foreach ( dejoiy_mobile_os_cart_modes() as $mode ) : ?>
					<a class="dm-modes__pill<?php echo ! empty( $mode['active'] ) ? ' is-active' : ''; ?>" href="<?php echo esc_url( $mode['url'] ); ?>">
						<span class="dm-modes__brand"><?php echo esc_html( $mode['label'] ); ?></span>
						<span class="dm-modes__sub"><?php echo esc_html( $mode['sub'] ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<?php
	return (string) ob_get_clean();
}

function dejoiy_mobile_os_header_html() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return '';
	}

	$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
	$cart_url = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );
	$cart_ct  = dejoiy_mobile_os_get_cart_count();
	$hints    = dejoiy_mobile_os_search_hints();

	ob_start();
	?>
	<div id="dejoiy-mobile-os-chrome" class="dejoiy-mobile-os-chrome">
		<div id="dejoiy-mobile-os-header" class="dejoiy-mobile-os-header dm-hero-only" role="banner">
		<div class="dm-hero dm-hero--blue">
			<div class="dm-hero__bg" aria-hidden="true"></div>
			<div class="dm-hero__inner">
				<div class="dm-header-collapse">
				<div class="dm-header-collapse__inner">
				<div class="dm-modes" role="tablist" aria-label="<?php esc_attr_e( 'DEJOIY experience', 'dejoiy' ); ?>">
					<?php foreach ( dejoiy_mobile_os_modes() as $mode ) : ?>
						<a class="dm-modes__pill<?php echo ! empty( $mode['active'] ) ? ' is-active' : ''; ?>" href="<?php echo esc_url( $mode['url'] ); ?>">
							<span class="dm-modes__brand"><?php echo esc_html( $mode['label'] ); ?></span>
							<span class="dm-modes__sub"><?php echo esc_html( $mode['sub'] ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>

				<a class="dm-loc" href="<?php echo esc_url( dejoiy_mobile_os_delivery_url() ); ?>">
					<span class="dm-loc__pin" aria-hidden="true">📍</span>
					<span class="dm-loc__text">
						<span class="dm-loc__label"><?php esc_html_e( 'Delivering to', 'dejoiy' ); ?></span>
						<span class="dm-loc__value"><?php echo esc_html( dejoiy_mobile_os_delivery_label() ); ?></span>
					</span>
					<span class="dm-loc__chev" aria-hidden="true">›</span>
				</a>
				</div>
				</div>

				<div class="dm-search-row">
					<div class="dm-search" role="search">
						<label class="dm-search__field dm-search__field--premium">
							<span class="dm-search__ico" aria-hidden="true">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3-3"/></svg>
							</span>
							<input type="search" class="dm-search__input" autocomplete="off" inputmode="search" placeholder="<?php echo esc_attr( $hints[0] ); ?>" aria-label="<?php esc_attr_e( 'Search DEJOIY — Ask JOI', 'dejoiy' ); ?>" data-joi-hints="<?php echo esc_attr( wp_json_encode( $hints ) ); ?>" data-joi-open />
						</label>
					</div>
					<a class="dm-search-row__action" href="<?php echo esc_url( $cart_url ); ?>" aria-label="<?php esc_attr_e( 'Cart', 'dejoiy' ); ?>">
						<span class="dm-search-row__cart-ico" aria-hidden="true">🛒</span>
						<span class="dm-search-row__badge-slot"><?php echo dejoiy_mobile_os_cart_badge_html( $cart_ct ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					</a>
				</div>

				<div class="dm-catnav-shell">
				<nav class="dm-catnav" aria-label="<?php esc_attr_e( 'Ecosystems', 'dejoiy' ); ?>">
					<?php foreach ( dejoiy_mobile_os_chips() as $chip ) : ?>
						<a class="dm-catnav__item<?php echo ! empty( $chip['active'] ) ? ' is-active' : ''; ?>" href="<?php echo esc_url( $chip['url'] ); ?>" style="--dm-accent:<?php echo esc_attr( $chip['color'] ); ?>">
							<span class="dm-catnav__ico" aria-hidden="true"><?php echo esc_html( $chip['icon'] ); ?></span>
							<span class="dm-catnav__label"><?php echo esc_html( $chip['label'] ); ?></span>
						</a>
					<?php endforeach; ?>
				</nav>
				</div>
			</div>
		</div>
		</div>
		<div id="dejoiy-mobile-os-mkt" class="dm-mkt-strip dm-mkt-strip--white">
			<nav class="dm-mkt-cats" aria-label="<?php esc_attr_e( 'Shop by category', 'dejoiy' ); ?>">
				<?php foreach ( dejoiy_mobile_os_market_categories() as $cat ) : ?>
					<a class="dm-mkt-cats__item" href="<?php echo esc_url( $cat['url'] ); ?>">
						<span class="dm-mkt-cats__circle">
							<?php if ( ! empty( $cat['img'] ) ) : ?>
								<img src="<?php echo esc_url( $cat['img'] ); ?>" alt="" loading="lazy" decoding="async" />
							<?php else : ?>
								<span class="dm-mkt-cats__emoji" aria-hidden="true"><?php echo esc_html( $cat['icon'] ); ?></span>
							<?php endif; ?>
						</span>
						<span class="dm-mkt-cats__label"><?php echo esc_html( $cat['label'] ); ?></span>
					</a>
				<?php endforeach; ?>
			</nav>
		</div>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Full-screen JOI search sheet markup.
 *
 * @return string
 */
function dejoiy_mobile_os_search_sheet_html() {
	$hints = dejoiy_mobile_os_search_hints();
	ob_start();
	?>
	<div id="dm-joi-search-sheet" class="dm-joi-sheet" hidden aria-hidden="true">
		<div class="dm-joi-sheet__panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Ask JOI search', 'dejoiy' ); ?>">
			<header class="dm-joi-sheet__head">
				<button type="button" class="dm-joi-sheet__back" data-joi-close aria-label="<?php esc_attr_e( 'Close search', 'dejoiy' ); ?>">←</button>
				<label class="dm-joi-sheet__input-wrap">
					<input type="search" class="dm-joi-sheet__input" autocomplete="off" placeholder="<?php echo esc_attr( $hints[0] ); ?>" aria-label="<?php esc_attr_e( 'Search products and pages', 'dejoiy' ); ?>" />
				</label>
			</header>
			<div class="dm-joi-sheet__body">
				<p class="dm-joi-sheet__hint"><?php esc_html_e( 'Powered by JOI — search and chat as you type', 'dejoiy' ); ?></p>
				<div class="dm-joi-sheet__chat" id="dm-joi-search-chat" hidden aria-live="polite"></div>
				<ul class="dm-joi-sheet__results" id="dm-joi-search-results" role="listbox"></ul>
			</div>
		</div>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * @return string
 */
function dejoiy_mobile_os_bottom_html() {
	$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
	$cart_url = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );

	$is_home     = is_front_page();
	$is_shop     = function_exists( 'is_shop' ) && is_shop();
	$is_nexus    = false !== strpos( (string) wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ), 'dejoiy-library' ); // phpcs:ignore
	$is_account  = function_exists( 'is_account_page' ) && is_account_page();
	$is_wishlist = isset( $_GET['et-wishlist-page'] ); // phpcs:ignore

	ob_start();
	?>
	<nav id="dejoiy-mobile-os-bottom" class="dm-bottom dejoiy-mobile-os-bottom" aria-label="<?php esc_attr_e( 'Primary', 'dejoiy' ); ?>">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="dm-bottom__item<?php echo $is_home ? ' is-active' : ''; ?>"><span>⌂</span><small><?php esc_html_e( 'Home', 'dejoiy' ); ?></small></a>
		<a href="<?php echo esc_url( $shop_url ); ?>" class="dm-bottom__item<?php echo $is_shop ? ' is-active' : ''; ?>"><span>◎</span><small><?php esc_html_e( 'Discover', 'dejoiy' ); ?></small></a>
		<a href="<?php echo esc_url( home_url( '/dejoiy-library/?dejoiy_library=1' ) ); ?>" class="dm-bottom__item<?php echo $is_nexus ? ' is-active' : ''; ?>"><span>✦</span><small><?php esc_html_e( 'Nexus', 'dejoiy' ); ?></small></a>
		<a href="<?php echo esc_url( home_url( '/my-account/?et-wishlist-page' ) ); ?>" class="dm-bottom__item<?php echo $is_wishlist ? ' is-active' : ''; ?>"><span>♡</span><small><?php esc_html_e( 'Favorites', 'dejoiy' ); ?></small></a>
		<a href="<?php echo esc_url( home_url( '/my-account/' ) ); ?>" class="dm-bottom__item<?php echo $is_account && ! $is_wishlist ? ' is-active' : ''; ?>"><span>👤</span><small><?php esc_html_e( 'Account', 'dejoiy' ); ?></small></a>
		<a href="<?php echo esc_url( $cart_url ); ?>" class="dm-bottom__item dm-bottom__item--cart<?php echo function_exists( 'is_cart' ) && is_cart() ? ' is-active' : ''; ?>"><span class="dm-bottom__ico-wrap">🛒<span class="dm-bottom__badge-slot"><?php echo dejoiy_mobile_os_bottom_cart_badge_html( dejoiy_mobile_os_get_cart_count() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></span><small><?php esc_html_e( 'Cart', 'dejoiy' ); ?></small></a>
	</nav>
	<?php
	return (string) ob_get_clean();
}

/**
 * Marketplace cart count — only lines visible on /cart (excludes hidden Nexus shelf items).
 *
 * @return int
 */
function dejoiy_mobile_os_get_cart_count() {
	if ( ! function_exists( 'WC' ) ) {
		return 0;
	}
	if ( is_null( WC()->cart ) && function_exists( 'wc_load_cart' ) ) {
		wc_load_cart();
	}
	if ( ! WC()->cart ) {
		return 0;
	}

	$qty = 0;
	foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
		$visible = apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key );
		if ( ! $visible ) {
			continue;
		}
		$qty += isset( $cart_item['quantity'] ) ? (int) $cart_item['quantity'] : 0;
	}

	return max( 0, (int) apply_filters( 'dejoiy_mobile_os_cart_count', $qty, WC()->cart ) );
}

/**
 * Header cart badge markup.
 *
 * @param int $count Item count.
 * @return string
 */
function dejoiy_mobile_os_cart_badge_html( $count ) {
	$count = max( 0, (int) $count );
	if ( $count < 1 ) {
		return '';
	}
	return '<span class="dm-search-row__badge">' . esc_html( (string) $count ) . '</span>';
}

/**
 * Bottom nav cart badge markup.
 *
 * @param int $count Item count.
 * @return string
 */
function dejoiy_mobile_os_bottom_cart_badge_html( $count ) {
	$count = max( 0, (int) $count );
	if ( $count < 1 ) {
		return '';
	}
	$label = $count > 9 ? '9+' : (string) $count;
	return '<span class="dm-bottom__badge" aria-label="' . esc_attr(
		sprintf(
			/* translators: %d: cart item count */
			_n( '%d item in cart', '%d items in cart', $count, 'dejoiy' ),
			$count
		)
	) . '">' . esc_html( $label ) . '</span>';
}

/**
 * WooCommerce AJAX fragments — keep header/bottom cart badges in sync.
 *
 * @param array<string, string> $fragments Fragments.
 * @return array<string, string>
 */
function dejoiy_mobile_os_cart_fragments( $fragments ) {
	$count = dejoiy_mobile_os_get_cart_count();
	$fragments['.dm-search-row__badge-slot'] = dejoiy_mobile_os_cart_badge_html( $count );
	$fragments['.dm-bottom__badge-slot']     = dejoiy_mobile_os_bottom_cart_badge_html( $count );
	return $fragments;
}

/**
 * AJAX: return current visible cart count (mobile header badge).
 */
function dejoiy_mobile_os_ajax_cart_count() {
	wp_send_json_success(
		array(
			'count' => dejoiy_mobile_os_get_cart_count(),
		)
	);
}

/**
 * Output fixed header once.
 */
function dejoiy_mobile_os_print_header() {
	if ( ! dejoiy_mobile_os_enabled() && ! dejoiy_mobile_os_is_cart_focus() && ! dejoiy_mobile_os_is_checkout_focus() ) {
		return;
	}
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;
	if ( dejoiy_mobile_os_is_cart_focus() ) {
		if ( ! function_exists( 'dejoiy_cart_xp_is_active' ) || ! dejoiy_cart_xp_is_active() ) {
			return;
		}
	}
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo dejoiy_mobile_os_header_html();
}
add_action( 'wp_body_open', 'dejoiy_mobile_os_print_header', 2 );
add_action( 'etheme_after_body_open', 'dejoiy_mobile_os_print_header', 2 );
add_action( 'etheme_before_page_wrapper', 'dejoiy_mobile_os_print_header', 1 );

/**
 * Search sheet + bottom nav.
 */
function dejoiy_mobile_os_print_footer_chrome() {
	if ( ! dejoiy_mobile_os_enabled() && ! dejoiy_mobile_os_is_cart_focus() && ! dejoiy_mobile_os_is_checkout_focus() ) {
		return;
	}
	if ( dejoiy_mobile_os_is_cart_focus() ) {
		return;
	}
	static $sheet_done = false;
	if ( ! $sheet_done ) {
		$sheet_done = true;
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo dejoiy_mobile_os_search_sheet_html();
	}
	if ( dejoiy_mobile_os_is_checkout_focus() ) {
		return;
	}
	static $nav_done = false;
	if ( ! $nav_done ) {
		$nav_done = true;
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo dejoiy_mobile_os_bottom_html();
	}
}
add_action( 'wp_footer', 'dejoiy_mobile_os_print_footer_chrome', 5 );

/**
 * @param array<int, string> $classes Body classes.
 * @return array<int, string>
 */
function dejoiy_mobile_os_body_class( $classes ) {
	if ( dejoiy_mobile_os_is_dedicated_page() || ( function_exists( 'is_product' ) && is_product() ) ) {
		$classes[] = 'dejoiy-mobile-os-off';
		return $classes;
	}
	if ( dejoiy_mobile_os_is_checkout_focus() ) {
		$classes[] = 'dejoiy-mobile-os';
		$classes[] = 'dejoiy-mobile-checkout-view';
		return $classes;
	}
	if ( function_exists( 'dejoiy_cart_xp_is_active' ) && dejoiy_cart_xp_is_active() ) {
		$classes[] = 'dejoiy-mobile-os';
		$classes[] = 'dejoiy-cart-xp-mobile';
		return $classes;
	}
	if ( dejoiy_mobile_os_is_cart_focus() ) {
		$classes[] = 'dejoiy-mobile-os';
		$classes[] = 'dejoiy-mobile-cart-view';
		return $classes;
	}
	if ( dejoiy_mobile_os_enabled() ) {
		$classes[] = 'dejoiy-mobile-os';
	}
	return $classes;
}
add_filter( 'body_class', 'dejoiy_mobile_os_body_class', 20 );

/**
 * Enqueue Mobile OS assets (all pages, mobile/tablet media).
 */
function dejoiy_mobile_os_assets() {
	if ( ! dejoiy_mobile_os_enabled() && ! dejoiy_mobile_os_is_cart_focus() && ! dejoiy_mobile_os_is_checkout_focus() ) {
		return;
	}
	$uri = get_stylesheet_directory_uri();
	$dir = get_stylesheet_directory();
	$css = $dir . '/dejoiy-mobile-os.css';
	$js  = $dir . '/dejoiy-mobile-os.js';
	if ( is_readable( $css ) ) {
		wp_enqueue_style(
			'dejoiy-mobile-os',
			$uri . '/dejoiy-mobile-os.css',
			array(),
			(string) filemtime( $css ),
			'screen and (max-width: 1024px)'
		);
	}
	$suppress = $dir . '/dejoiy-mobile-os-suppress-desktop.css';
	if ( is_readable( $suppress ) ) {
		wp_enqueue_style(
			'dejoiy-mobile-os-suppress-desktop',
			$uri . '/dejoiy-mobile-os-suppress-desktop.css',
			array(),
			(string) filemtime( $suppress ),
			'screen and (min-width: 1025px)'
		);
	}
	if ( is_readable( $js ) ) {
		wp_enqueue_script(
			'dejoiy-mobile-os',
			$uri . '/dejoiy-mobile-os.js',
			array(),
			(string) filemtime( $js ),
			true
		);
		wp_localize_script(
			'dejoiy-mobile-os',
			'dejoiyMobileOs',
			array(
				'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
				'nonce'       => wp_create_nonce( 'dejoiy_header_os_v4' ),
				'action'          => 'dejoiy_joi_search',
				'chatAction'      => 'dejoiy_joi_chat',
				'librarianAction' => 'dejoiy_joi_librarian_chat',
				'isNexus'         => function_exists( 'dejoiy_library_should_load_nexus_app' ) && dejoiy_library_should_load_nexus_app(),
				'storeApiUrl'     => rest_url( 'wc/store/v1/products' ),
				'searchHints'     => dejoiy_mobile_os_search_hints(),
				'minChars'        => 2,
				'i18n'            => array(
					'thinking' => __( 'JOI is thinking…', 'dejoiy' ),
					'you'      => __( 'You', 'dejoiy' ),
					'joi'      => __( 'JOI', 'dejoiy' ),
				),
				'cartCount'   => dejoiy_mobile_os_get_cart_count(),
				'cartAction'  => 'dejoiy_mobile_os_cart_count',
			)
		);
	}
}
add_filter( 'woocommerce_add_to_cart_fragments', 'dejoiy_mobile_os_cart_fragments', 20 );
add_action( 'wp_ajax_dejoiy_mobile_os_cart_count', 'dejoiy_mobile_os_ajax_cart_count' );
add_action( 'wp_ajax_nopriv_dejoiy_mobile_os_cart_count', 'dejoiy_mobile_os_ajax_cart_count' );
add_action( 'wp_enqueue_scripts', 'dejoiy_mobile_os_assets', 1008 );
