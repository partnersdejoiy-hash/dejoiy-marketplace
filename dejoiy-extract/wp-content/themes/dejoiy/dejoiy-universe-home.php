<?php
/**
 * DEJOIY Universe — Phase 2 homepage (front page only).
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return bool
 */
function dejoiy_universe_home_is_active() {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return false;
	}
	return '1' === get_option( 'dejoiy_universe_home_active', '1' );
}

/**
 * @return array<string, array<string, string>>
 */
function dejoiy_universe_gateways() {
	$shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
	return array(
		'marketplace' => array(
			'label'   => __( 'Marketplace', 'dejoiy' ),
			'tagline' => __( 'Shop everything', 'dejoiy' ),
			'verb'    => __( 'Shop', 'dejoiy' ),
			'url'     => $shop,
			'icon'    => '◆',
			'theme'   => 'market',
		),
		'nexus'       => array(
			'label'   => __( 'Nexus', 'dejoiy' ),
			'tagline' => __( 'Books & courses', 'dejoiy' ),
			'verb'    => __( 'Learn', 'dejoiy' ),
			'url'     => home_url( '/dejoiy-library/?dejoiy_library=1' ),
			'icon'    => '✦',
			'theme'   => 'nexus',
		),
		'studio'      => array(
			'label'   => __( 'Custom Studio', 'dejoiy' ),
			'tagline' => __( 'Create your vision', 'dejoiy' ),
			'verb'    => __( 'Create', 'dejoiy' ),
			'url'     => home_url( '/dejoiy-custom-studio/' ),
			'icon'    => '✿',
			'theme'   => 'studio',
		),
		'quickmart'   => array(
			'label'   => __( 'QuickMart', 'dejoiy' ),
			'tagline' => __( 'Instant essentials', 'dejoiy' ),
			'verb'    => __( 'Grab', 'dejoiy' ),
			'url'     => home_url( '/dejoiy-quick-mart/' ),
			'icon'    => '⚡',
			'theme'   => 'quickmart',
		),
		'refurbished' => array(
			'label'   => __( 'Refurbished', 'dejoiy' ),
			'tagline' => __( 'Certified pre-owned tech', 'dejoiy' ),
			'verb'    => __( 'Renew', 'dejoiy' ),
			'url'     => home_url( '/dejoiy-refurbished/' ),
			'icon'    => '◈',
			'theme'   => 'refurbished',
		),
		'services'    => array(
			'label'   => __( 'Services', 'dejoiy' ),
			'tagline' => __( 'Book experts', 'dejoiy' ),
			'verb'    => __( 'Hire', 'dejoiy' ),
			'url'     => home_url( '/dejoiy-services/' ),
			'icon'    => '◎',
			'theme'   => 'services',
		),
	);
}

/**
 * @param array<string, mixed> $args Query args.
 * @return array<int, WP_Post>
 */
function dejoiy_universe_get_products( $args = array() ) {
	$defaults = array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => 8,
		'no_found_rows'  => true,
	);
	$q = new WP_Query( array_merge( $defaults, $args ) );
	$posts = $q->posts;
	wp_reset_postdata();
	return is_array( $posts ) ? $posts : array();
}

/**
 * Ecosystem badge label for cards.
 *
 * @param int $product_id Product ID.
 * @return string
 */
function dejoiy_universe_eco_badge( $product_id ) {
	if ( function_exists( 'dejoiy_ecosystem_badge_label' ) ) {
		return (string) dejoiy_ecosystem_badge_label( $product_id );
	}
	if ( function_exists( 'dejoiy_get_product_ecosystem' ) ) {
		$eco = dejoiy_get_product_ecosystem( $product_id );
		$reg = function_exists( 'dejoiy_ecosystem_registry' ) ? dejoiy_ecosystem_registry() : array();
		if ( isset( $reg[ $eco ]['label'] ) ) {
			return (string) $reg[ $eco ]['label'];
		}
	}
	return '';
}

/**
 * Seller display name for trust badge.
 *
 * @param int $product_id Product ID.
 * @return string
 */
function dejoiy_universe_seller_label( $product_id ) {
	$author_id = (int) get_post_field( 'post_author', $product_id );
	if ( $author_id > 0 && function_exists( 'wcfm_get_vendor_store_name' ) ) {
		$name = wcfm_get_vendor_store_name( $author_id );
		if ( is_string( $name ) && '' !== $name ) {
			return $name;
		}
	}
	if ( $author_id > 0 ) {
		return get_the_author_meta( 'display_name', $author_id );
	}
	return __( 'DEJOIY Partner', 'dejoiy' );
}

/**
 * Load Nexus cover helpers when Universe home renders product cards.
 */
function dejoiy_universe_ensure_cover_helpers() {
	static $loaded = false;
	if ( $loaded ) {
		return;
	}
	$loaded = true;
	if ( function_exists( 'dejoiy_library_get_cover_url' ) ) {
		return;
	}
	$path = get_stylesheet_directory() . '/library-customize.php';
	if ( is_readable( $path ) ) {
		require_once $path;
	}
}

/**
 * Resolve product cover + fallback (mirrors Nexus library cards).
 *
 * @param int $product_id Product ID.
 * @return array{src: string, fallback: string}
 */
function dejoiy_universe_product_cover_urls( $product_id ) {
	$product_id = (int) $product_id;
	$src        = '';
	$fallback   = '';

	foreach ( array( 'woocommerce_single', 'woocommerce_thumbnail', 'medium_large', 'medium', 'full' ) as $size ) {
		$src = get_the_post_thumbnail_url( $product_id, $size );
		if ( $src ) {
			break;
		}
	}

	if ( ! $src && function_exists( 'wc_get_product' ) ) {
		$wc = wc_get_product( $product_id );
		if ( $wc ) {
			$gallery = $wc->get_gallery_image_ids();
			if ( ! empty( $gallery ) ) {
				$src = wp_get_attachment_image_url( (int) $gallery[0], 'woocommerce_thumbnail' );
			}
		}
	}

	$meta_cover = get_post_meta( $product_id, '_dejoiy_library_cover', true );
	if ( ! $src && $meta_cover ) {
		$src = (string) $meta_cover;
	}

	dejoiy_universe_ensure_cover_helpers();

	if ( ! $src && function_exists( 'dejoiy_library_get_cover_url' ) ) {
		foreach ( array( 'woocommerce_thumbnail', 'medium', 'large' ) as $size ) {
			$src = dejoiy_library_get_cover_url( $product_id, $size );
			if ( $src ) {
				break;
			}
		}
	}

	if ( function_exists( 'dejoiy_library_get_cover_fallback_url' ) ) {
		$fallback = (string) dejoiy_library_get_cover_fallback_url( $product_id );
	}
	if ( ! $fallback ) {
		$meta_fb = get_post_meta( $product_id, '_dejoiy_library_cover_fallback', true );
		if ( $meta_fb ) {
			$fallback = (string) $meta_fb;
		}
	}

	return array(
		'src'      => $src ? (string) $src : '',
		'fallback' => $fallback ? (string) $fallback : '',
	);
}

/**
 * Product Card V2.
 *
 * @param int    $product_id Product ID.
 * @param string $world      Visual world slug.
 */
function dejoiy_universe_render_card_v2( $product_id, $world = 'market' ) {
	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		return;
	}

	$url    = function_exists( 'dejoiy_ecosystem_product_url' ) ? dejoiy_ecosystem_product_url( $product_id ) : get_permalink( $product_id );
	$covers = dejoiy_universe_product_cover_urls( $product_id );
	$cover  = $covers['src'];
	$fb     = $covers['fallback'];
	if ( ! $cover && $fb ) {
		$cover = $fb;
		$fb    = '';
	}

	$badge  = dejoiy_universe_eco_badge( $product_id );
	$dpin   = function_exists( 'dejoiy_display_product_dpin' ) ? dejoiy_display_product_dpin( $product_id ) : '';
	$seller = dejoiy_universe_seller_label( $product_id );
	?>
	<article class="du-card-v2 du-card-v2--<?php echo esc_attr( $world ); ?>" data-product-id="<?php echo esc_attr( (string) $product_id ); ?>">
		<button type="button" class="du-card-v2__fav" aria-label="<?php esc_attr_e( 'Save to favorites', 'dejoiy' ); ?>" aria-pressed="false" data-fav-id="<?php echo esc_attr( (string) $product_id ); ?>">
			<span aria-hidden="true">♡</span>
		</button>
		<a class="du-card-v2__link" href="<?php echo esc_url( $url ); ?>">
			<div class="du-card-v2__media">
				<?php if ( $cover ) : ?>
					<img class="du-card-v2__img" src="<?php echo esc_url( $cover ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>" loading="lazy" decoding="async" width="280" height="340"<?php echo $fb ? ' data-fallback="' . esc_url( $fb ) . '" onerror="if(this.dataset.fallback){this.src=this.dataset.fallback;this.onerror=null;}"' : ''; ?> />
				<?php else : ?>
					<span class="du-card-v2__ph" aria-hidden="true"></span>
				<?php endif; ?>
				<?php if ( $badge ) : ?>
					<span class="du-card-v2__eco"><?php echo esc_html( $badge ); ?></span>
				<?php endif; ?>
			</div>
			<div class="du-card-v2__body">
				<?php if ( $dpin ) : ?>
					<span class="du-card-v2__dpin" title="<?php esc_attr_e( 'DEJOIY Product ID', 'dejoiy' ); ?>"><?php echo esc_html( $dpin ); ?></span>
				<?php endif; ?>
				<h3 class="du-card-v2__title"><?php echo esc_html( $product->get_name() ); ?></h3>
				<span class="du-card-v2__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
				<span class="du-card-v2__seller">
					<span class="du-card-v2__seller-dot" aria-hidden="true"></span>
					<?php echo esc_html( $seller ); ?>
				</span>
			</div>
		</a>
	</article>
	<?php
}

/**
 * @param string               $world World slug.
 * @param array<string, mixed> $args  Query args.
 * @param int                  $limit Max cards.
 * @return bool
 */
function dejoiy_universe_render_shelf_v2( $world, $args, $limit = 4 ) {
	$args['posts_per_page'] = $limit;
	$posts                  = dejoiy_universe_get_products( $args );
	if ( empty( $posts ) ) {
		return false;
	}
	echo '<div class="du-shelf-v2 du-shelf-v2--' . esc_attr( $world ) . '">';
	foreach ( $posts as $p ) {
		dejoiy_universe_render_card_v2( $p->ID, $world );
	}
	echo '</div>';
	return true;
}

/**
 * @return array<int, string>
 */
function dejoiy_universe_get_searched_terms() {
	$raw  = isset( $_COOKIE['dejoiy_searched'] ) ? wp_unslash( $_COOKIE['dejoiy_searched'] ) : ''; // phpcs:ignore
	$list = $raw ? json_decode( $raw, true ) : array();
	if ( ! is_array( $list ) ) {
		return array();
	}
	return array_slice( array_filter( array_map( 'sanitize_text_field', $list ) ), 0, 6 );
}

/**
 * Track search cookie.
 */
function dejoiy_universe_track_search_cookie() {
	if ( is_admin() ) {
		return;
	}
	$term = '';
	if ( isset( $_GET['dlu_q'] ) ) { // phpcs:ignore
		$term = sanitize_text_field( wp_unslash( $_GET['dlu_q'] ) ); // phpcs:ignore
	} elseif ( isset( $_GET['s'] ) && isset( $_GET['post_type'] ) && 'product' === $_GET['post_type'] ) { // phpcs:ignore
		$term = sanitize_text_field( wp_unslash( $_GET['s'] ) ); // phpcs:ignore
	}
	if ( strlen( $term ) < 2 ) {
		return;
	}
	$list = dejoiy_universe_get_searched_terms();
	$list = array_values( array_diff( $list, array( $term ) ) );
	array_unshift( $list, $term );
	$list = array_slice( $list, 0, 8 );
	setcookie( 'dejoiy_searched', wp_json_encode( $list ), time() + MONTH_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
}
add_action( 'template_redirect', 'dejoiy_universe_track_search_cookie', 8 );

/**
 * JOI example prompts → shop search URLs.
 *
 * @return array<int, array{label: string, q: string}>
 */
function dejoiy_universe_joi_examples() {
	$shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
	$base = add_query_arg( array( 'post_type' => 'product' ), $shop );
	$mk   = static function ( $label, $q ) use ( $base ) {
		return array(
			'label' => $label,
			'url'   => add_query_arg( 's', $q, $base ),
			'q'     => $q,
		);
	};
	return array(
		$mk( __( 'Find business books', 'dejoiy' ), 'business books' ),
		$mk( __( 'Show custom t-shirts', 'dejoiy' ), 'custom t shirt' ),
		$mk( __( 'Find logo designers', 'dejoiy' ), 'logo design' ),
		$mk( __( 'Refurbished laptops', 'dejoiy' ), 'refurbished laptop' ),
		$mk( __( 'Trending products', 'dejoiy' ), 'trending' ),
	);
}

/**
 * @return string
 */
function dejoiy_universe_home_html() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return '';
	}

	$gateways = dejoiy_universe_gateways();
	$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
	$viewed   = function_exists( 'dejoiy_home_get_viewed_ids' ) ? dejoiy_home_get_viewed_ids() : array();

	$trending_posts = dejoiy_universe_get_products(
		array(
			'posts_per_page' => 6,
			'meta_key'       => 'total_sales',
			'orderby'        => 'meta_value_num',
			'order'          => 'DESC',
		)
	);

	$popular_posts = dejoiy_universe_get_products(
		array(
			'posts_per_page' => 6,
			'orderby'        => 'comment_count',
			'order'        => 'DESC',
		)
	);

	$personal_args = array( 'posts_per_page' => 6, 'orderby' => 'rand' );
	if ( ! empty( $viewed ) ) {
		$personal_args = array(
			'post__in'       => array_slice( $viewed, 0, 8 ),
			'orderby'        => 'post__in',
			'posts_per_page' => 6,
		);
	}
	$personal_posts = dejoiy_universe_get_products( $personal_args );

	$hub_url = home_url( '/my-account/' );
	if ( function_exists( 'wcfm_get_page_id' ) ) {
		$dash_id = wcfm_get_page_id( 'dashboard' );
		if ( $dash_id ) {
			$hub_url = get_permalink( $dash_id );
		}
	}

	ob_start();
	?>
	<div id="dejoiy-universe" class="dejoiy-universe" data-du-version="2">

		<!-- §1 Hero — ecosystem gateways -->
		<section class="du-hero du-reveal" aria-labelledby="du-hero-title">
			<div class="du-hero__orb du-hero__orb--a" aria-hidden="true"></div>
			<div class="du-hero__ag" aria-hidden="true">
				<span class="du-ag-float du-ag-float--1"></span>
				<span class="du-ag-float du-ag-float--2"></span>
				<span class="du-ag-float du-ag-float--3"></span>
			</div>
			<div class="du-hero__orb du-hero__orb--b" aria-hidden="true"></div>
			<div class="du-hero__in">
				<p class="du-hero__kicker"><?php esc_html_e( 'Enter', 'dejoiy' ); ?></p>
				<h1 id="du-hero-title" class="du-hero__title"><?php esc_html_e( 'DEJOIY Universe', 'dejoiy' ); ?></h1>
				<p class="du-hero__lead du-hero__lead--desktop"><?php esc_html_e( 'Shop · Learn · Create · Hire · Renew · Grow — six worlds, one joyful platform.', 'dejoiy' ); ?></p>
				<p class="du-hero__lead du-hero__lead--mobile"><?php esc_html_e( 'Shop · Learn · Create · Hire · Grow — five worlds, one joyful platform.', 'dejoiy' ); ?></p>
				<div class="du-gates" role="navigation" aria-label="<?php esc_attr_e( 'DEJOIY ecosystems', 'dejoiy' ); ?>">
					<?php foreach ( $gateways as $g ) : ?>
						<a href="<?php echo esc_url( $g['url'] ); ?>" class="du-gate du-gate--<?php echo esc_attr( $g['theme'] ); ?>">
							<span class="du-gate__art" aria-hidden="true"></span>
							<span class="du-gate__icon" aria-hidden="true"><?php echo esc_html( $g['icon'] ); ?></span>
							<span class="du-gate__verb"><?php echo esc_html( $g['verb'] ); ?></span>
							<span class="du-gate__label"><?php echo esc_html( $g['label'] ); ?></span>
							<span class="du-gate__tag"><?php echo esc_html( $g['tagline'] ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		</section>

		<!-- §2 Brand story -->
		<section class="du-story du-reveal" aria-labelledby="du-story-title">
			<div class="du-story__in">
				<p class="du-story__formula" aria-label="<?php esc_attr_e( 'You plus Joy equals DEJOIY', 'dejoiy' ); ?>">
					<span class="du-story__you"><?php esc_html_e( 'YOU', 'dejoiy' ); ?></span>
					<span class="du-story__plus" aria-hidden="true">+</span>
					<span class="du-story__joy"><?php esc_html_e( 'JOY', 'dejoiy' ); ?></span>
					<span class="du-story__eq" aria-hidden="true">=</span>
					<span class="du-story__brand">DEJOIY</span>
				</p>
				<h2 id="du-story-title" class="du-story__title"><?php esc_html_e( 'One platform. Many worlds.', 'dejoiy' ); ?></h2>
				<ul class="du-story__verbs">
					<li><?php esc_html_e( 'Shop', 'dejoiy' ); ?></li>
					<li><?php esc_html_e( 'Learn', 'dejoiy' ); ?></li>
					<li><?php esc_html_e( 'Create', 'dejoiy' ); ?></li>
					<li><?php esc_html_e( 'Hire', 'dejoiy' ); ?></li>
					<li class="du-story__verb--desktop"><?php esc_html_e( 'Renew', 'dejoiy' ); ?></li>
					<li><?php esc_html_e( 'Grow', 'dejoiy' ); ?></li>
				</ul>
			</div>
		</section>

		<!-- §3 JOI discovery -->
		<section class="du-joi du-reveal" aria-labelledby="du-joi-title">
			<div class="du-joi__in">
				<div class="du-joi__head">
					<p class="du-joi__badge"><?php esc_html_e( 'Powered by DEJOIY intelligence', 'dejoiy' ); ?></p>
					<h2 id="du-joi-title" class="du-joi__title"><?php esc_html_e( 'Ask JOI', 'dejoiy' ); ?></h2>
				</div>
				<form class="du-joi__form" action="<?php echo esc_url( $shop_url ); ?>" method="get" role="search" data-du-joi-form>
					<input type="hidden" name="post_type" value="product" />
					<label class="screen-reader-text" for="du-joi-input"><?php esc_html_e( 'Ask JOI', 'dejoiy' ); ?></label>
					<input id="du-joi-input" class="du-joi__input" type="text" name="s" inputmode="search" enterkeyhint="search" placeholder="<?php esc_attr_e( 'Ask JOI anything…', 'dejoiy' ); ?>" autocomplete="off" data-du-joi-input />
					<button type="submit" class="du-joi__submit" data-du-joi-submit><?php esc_html_e( 'Discover', 'dejoiy' ); ?></button>
				</form>
				<div class="du-joi__panel" id="du-joi-panel" hidden>
					<div class="du-joi__panel-bar">
						<span class="du-joi__panel-label"><?php esc_html_e( 'Chat with JOI', 'dejoiy' ); ?></span>
						<button type="button" class="du-joi__close" data-du-joi-close aria-label="<?php esc_attr_e( 'Close JOI chat', 'dejoiy' ); ?>">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
						</button>
					</div>
					<div class="du-joi__conversation" data-du-joi-chat aria-live="polite"></div>
					<div class="du-joi__results" data-du-joi-results hidden></div>
				</div>
				<div class="du-joi__examples">
					<?php foreach ( dejoiy_universe_joi_examples() as $ex ) : ?>
						<a class="du-joi__chip" href="<?php echo esc_url( $ex['url'] ); ?>" data-joi-q="<?php echo esc_attr( $ex['q'] ); ?>"><?php echo esc_html( $ex['label'] ); ?></a>
					<?php endforeach; ?>
				</div>
			</div>
		</section>

		<!-- §4 Unified recommendations -->
		<?php if ( ! empty( $trending_posts ) || ! empty( $personal_posts ) || ! empty( $popular_posts ) ) : ?>
		<section class="du-reco du-reveal" aria-labelledby="du-reco-title">
			<div class="du-reco__in">
				<h2 id="du-reco-title" class="du-reco__title"><?php esc_html_e( 'Recommended for you', 'dejoiy' ); ?></h2>
				<div class="du-reco__tabs" role="tablist" aria-label="<?php esc_attr_e( 'Recommendation filters', 'dejoiy' ); ?>">
					<button type="button" class="du-reco__tab is-active" role="tab" aria-selected="true" data-reco-panel="trending"><?php esc_html_e( 'Trending', 'dejoiy' ); ?></button>
					<button type="button" class="du-reco__tab" role="tab" aria-selected="false" data-reco-panel="personal"><?php esc_html_e( 'For you', 'dejoiy' ); ?></button>
					<button type="button" class="du-reco__tab" role="tab" aria-selected="false" data-reco-panel="popular"><?php esc_html_e( 'Popular', 'dejoiy' ); ?></button>
				</div>
				<div class="du-reco__panel is-active" id="du-reco-trending" role="tabpanel">
					<div class="du-shelf-v2">
						<?php
						foreach ( $trending_posts as $p ) {
							dejoiy_universe_render_card_v2( $p->ID, 'reco' );
						}
						?>
					</div>
				</div>
				<div class="du-reco__panel" id="du-reco-personal" role="tabpanel" hidden>
					<div class="du-shelf-v2">
						<?php
						foreach ( $personal_posts as $p ) {
							dejoiy_universe_render_card_v2( $p->ID, 'reco' );
						}
						?>
					</div>
				</div>
				<div class="du-reco__panel" id="du-reco-popular" role="tabpanel" hidden>
					<div class="du-shelf-v2">
						<?php
						foreach ( $popular_posts as $p ) {
							dejoiy_universe_render_card_v2( $p->ID, 'reco' );
						}
						?>
					</div>
				</div>
			</div>
		</section>
		<?php endif; ?>

		<?php
		$nexus_posts = dejoiy_universe_get_products(
			array(
				'posts_per_page' => 4,
				'tax_query'      => array( // phpcs:ignore
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'slug',
						'terms'    => array( 'dejoiy-library', 'e-books', 'courses' ),
					),
				),
			)
		);
		if ( ! empty( $nexus_posts ) ) :
			?>
		<section class="du-world du-world--nexus du-reveal">
			<div class="du-world__banner" aria-hidden="true"></div>
			<div class="du-world__ag" aria-hidden="true"><span class="du-ag-float du-ag-float--1"></span></div>
			<div class="du-world__in">
				<header class="du-world__head">
					<p class="du-world__kicker"><?php esc_html_e( 'Nexus World', 'dejoiy' ); ?></p>
					<h2 class="du-world__title"><?php esc_html_e( 'Read. Learn. Grow.', 'dejoiy' ); ?></h2>
					<p class="du-world__desc"><?php esc_html_e( 'Books, eBooks & courses — a premium knowledge shelf.', 'dejoiy' ); ?></p>
					<a class="du-world__cta" href="<?php echo esc_url( $gateways['nexus']['url'] ); ?>"><?php esc_html_e( 'Enter Nexus', 'dejoiy' ); ?></a>
				</header>
				<div class="du-shelf-v2 du-shelf-v2--nexus">
					<?php
					foreach ( $nexus_posts as $p ) {
						dejoiy_universe_render_card_v2( $p->ID, 'nexus' );
					}
					?>
				</div>
			</div>
		</section>
		<?php endif; ?>

		<?php
		$studio_posts = dejoiy_universe_get_products(
			array(
				'posts_per_page' => 4,
				'tax_query'      => array( // phpcs:ignore
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'slug',
						'terms'    => array( 'customized-products', 'custom-t-shirts' ),
					),
				),
			)
		);
		if ( ! empty( $studio_posts ) ) :
			?>
		<section class="du-world du-world--studio du-reveal">
			<div class="du-world__banner" aria-hidden="true"></div>
			<div class="du-world__ag" aria-hidden="true"><span class="du-ag-float du-ag-float--1"></span></div>
			<div class="du-world__in">
				<header class="du-world__head">
					<p class="du-world__kicker"><?php esc_html_e( 'Custom Studio', 'dejoiy' ); ?></p>
					<h2 class="du-world__title"><?php esc_html_e( 'Design what is yours', 'dejoiy' ); ?></h2>
					<p class="du-world__desc"><?php esc_html_e( 'Visual, interactive creations made your way.', 'dejoiy' ); ?></p>
					<a class="du-world__cta" href="<?php echo esc_url( $gateways['studio']['url'] ); ?>"><?php esc_html_e( 'Open Studio', 'dejoiy' ); ?></a>
				</header>
				<div class="du-shelf-v2 du-shelf-v2--studio">
					<?php
					foreach ( $studio_posts as $p ) {
						dejoiy_universe_render_card_v2( $p->ID, 'studio' );
					}
					?>
				</div>
			</div>
		</section>
		<?php endif; ?>

		<?php
		$qm_posts = dejoiy_universe_get_products(
			array(
				'posts_per_page' => 4,
				'tax_query'      => array( // phpcs:ignore
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'slug',
						'terms'    => array( 'quick-products' ),
					),
				),
			)
		);
		if ( ! empty( $qm_posts ) ) :
			?>
		<section class="du-world du-world--quickmart du-reveal">
			<div class="du-world__banner" aria-hidden="true"></div>
			<div class="du-world__ag" aria-hidden="true"><span class="du-ag-float du-ag-float--1"></span></div>
			<div class="du-world__in">
				<header class="du-world__head">
					<p class="du-world__kicker"><?php esc_html_e( 'QuickMart', 'dejoiy' ); ?></p>
					<h2 class="du-world__title"><?php esc_html_e( 'Fast essentials', 'dejoiy' ); ?></h2>
					<p class="du-world__desc"><?php esc_html_e( 'Modern, focused commerce — in and out.', 'dejoiy' ); ?></p>
					<a class="du-world__cta" href="<?php echo esc_url( $gateways['quickmart']['url'] ); ?>"><?php esc_html_e( 'Shop QuickMart', 'dejoiy' ); ?></a>
				</header>
				<div class="du-shelf-v2 du-shelf-v2--quickmart">
					<?php
					foreach ( $qm_posts as $p ) {
						dejoiy_universe_render_card_v2( $p->ID, 'quickmart' );
					}
					?>
				</div>
			</div>
		</section>
		<?php endif; ?>

		<?php
		$svc_posts = dejoiy_universe_get_products(
			array(
				'posts_per_page' => 4,
				'tax_query'      => array( // phpcs:ignore
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'slug',
						'terms'    => array( 'services-marketplace', 'web-development', 'graphic-design', 'digital-marketing', 'content-writing' ),
					),
				),
			)
		);
		if ( ! empty( $svc_posts ) ) :
			?>
		<section class="du-world du-world--services du-reveal">
			<div class="du-world__banner" aria-hidden="true"></div>
			<div class="du-world__ag" aria-hidden="true"><span class="du-ag-float du-ag-float--1"></span></div>
			<div class="du-world__in">
				<header class="du-world__head">
					<p class="du-world__kicker"><?php esc_html_e( 'Services', 'dejoiy' ); ?></p>
					<h2 class="du-world__title"><?php esc_html_e( 'Book expertise', 'dejoiy' ); ?></h2>
					<p class="du-world__desc"><?php esc_html_e( 'Professional partners you can trust.', 'dejoiy' ); ?></p>
					<a class="du-world__cta" href="<?php echo esc_url( $gateways['services']['url'] ); ?>"><?php esc_html_e( 'Explore Services', 'dejoiy' ); ?></a>
				</header>
				<div class="du-shelf-v2 du-shelf-v2--services">
					<?php
					foreach ( $svc_posts as $p ) {
						dejoiy_universe_render_card_v2( $p->ID, 'services' );
					}
					?>
				</div>
			</div>
		</section>
		<?php endif; ?>

		<!-- §9 Become part of DEJOIY -->
		<section class="du-join du-reveal" aria-labelledby="du-join-title">
			<div class="du-join__in">
				<h2 id="du-join-title" class="du-join__title"><?php esc_html_e( 'Become part of DEJOIY', 'dejoiy' ); ?></h2>
				<p class="du-join__lead"><?php esc_html_e( 'Sell, create, teach, or serve — grow inside the Universe.', 'dejoiy' ); ?></p>
				<div class="du-join__roles">
					<span><?php esc_html_e( 'Seller', 'dejoiy' ); ?></span>
					<span><?php esc_html_e( 'Creator', 'dejoiy' ); ?></span>
					<span><?php esc_html_e( 'Author', 'dejoiy' ); ?></span>
					<span><?php esc_html_e( 'Service Provider', 'dejoiy' ); ?></span>
				</div>
				<a class="du-join__btn" href="<?php echo esc_url( $hub_url ); ?>"><?php esc_html_e( 'Start your journey', 'dejoiy' ); ?></a>
			</div>
		</section>

	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * @param string $content Post content.
 * @return string
 */
function dejoiy_universe_home_replace_content( $content ) {
	if ( ! is_front_page() || is_admin() ) {
		return $content;
	}
	if ( ! dejoiy_universe_home_is_active() ) {
		return $content;
	}

	static $done = false;
	if ( $done ) {
		return $content;
	}
	$done = true;

	return dejoiy_universe_home_html();
}
add_filter( 'the_content', 'dejoiy_universe_home_replace_content', 9999 );
add_filter( 'elementor/frontend/the_content', 'dejoiy_universe_home_replace_content', 9999 );

/**
 * @param array<int, string> $classes Body classes.
 * @return array<int, string>
 */
function dejoiy_universe_home_body_class( $classes ) {
	if ( is_front_page() && dejoiy_universe_home_is_active() ) {
		$classes[] = 'dejoiy-universe-home';
		$classes[] = 'dejoiy-universe-v2';
	}
	return $classes;
}
add_filter( 'body_class', 'dejoiy_universe_home_body_class' );

/**
 * Enqueue Universe home assets.
 */
function dejoiy_universe_home_assets() {
	if ( ! is_front_page() || ! dejoiy_universe_home_is_active() ) {
		return;
	}
	$uri = get_stylesheet_directory_uri();
	$dir = get_stylesheet_directory();
	$css  = $dir . '/dejoiy-universe-home.css';
	$rcss = $dir . '/dejoiy-universe-responsive.css';
	$js   = $dir . '/dejoiy-universe-home.js';
	if ( file_exists( $css ) ) {
		wp_enqueue_style(
			'dejoiy-universe-home',
			$uri . '/dejoiy-universe-home.css',
			array(),
			(string) filemtime( $css )
		);
	}
	if ( file_exists( $rcss ) ) {
		wp_enqueue_style(
			'dejoiy-universe-responsive',
			$uri . '/dejoiy-universe-responsive.css',
			array( 'dejoiy-universe-home' ),
			(string) filemtime( $rcss )
		);
	}
	if ( file_exists( $js ) ) {
		wp_enqueue_script(
			'dejoiy-universe-home',
			$uri . '/dejoiy-universe-home.js',
			array(),
			(string) filemtime( $js ),
			true
		);
		wp_localize_script(
			'dejoiy-universe-home',
			'dejoiyUniverse',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'dejoiy_header_os_v4' ),
				'joiApi'  => function_exists( 'dejoiy_joi_intelligence_api_base' ) ? dejoiy_joi_intelligence_api_base() : 'https://joi.dejoiy.tech/api',
				'siteUrl' => home_url( '/' ),
				'i18n'    => array(
					'searching'  => __( 'Searching DEJOIY…', 'dejoiy' ),
					'noResults'  => __( 'No products found. Try another term or ask JOI.', 'dejoiy' ),
					'thinking'   => __( 'JOI is thinking…', 'dejoiy' ),
					'you'        => __( 'You', 'dejoiy' ),
					'joi'        => __( 'JOI', 'dejoiy' ),
					'closeChat'  => __( 'Close JOI chat', 'dejoiy' ),
				),
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'dejoiy_universe_home_assets', 1006 );

/**
 * Dequeue legacy OS home styles on Universe v2.
 */
function dejoiy_universe_dequeue_legacy_home() {
	if ( ! is_front_page() || ! dejoiy_universe_home_is_active() ) {
		return;
	}
	wp_dequeue_style( 'dejoiy-marketplace-os' );
	wp_dequeue_script( 'dejoiy-marketplace-os' );
}
add_action( 'wp_enqueue_scripts', 'dejoiy_universe_dequeue_legacy_home', 1007 );
