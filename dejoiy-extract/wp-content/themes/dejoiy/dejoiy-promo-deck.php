<?php
/**
 * DEJOIY Marketplace Promo Deck — full-screen presentation slides.
 *
 * Premium fullscreen slide deck for DEJOIY's multi-world marketplace.
 * Each slide gets layered gradient + SVG scene art. When the OpenAI API
 * credits are available, the companion generator (`dejoiy-promo-gen.php`)
 * produces real AI art and stores it in the `dejoiy_promo_deck_images`
 * option; `dejoiy_promo_deck_art()` then renders that image instead of
 * the inline SVG fallback so nothing else changes.
 *
 * Template Name: DEJOIY Promo Deck
 *
 * @package Dejoiy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'dejoiy_promo_deck_assets' ) ) {
	function dejoiy_promo_deck_assets() {
		if ( ! is_page_template( 'dejoiy-promo-deck.php' ) && ! function_exists( 'is_page_template' ) ) {
			return;
		}
		if ( ! is_page_template( 'dejoiy-promo-deck.php' ) ) {
			return;
		}
		$uri = get_stylesheet_directory_uri();
		$dir = get_stylesheet_directory();
		$css = $dir . '/dejoiy-promo-deck.css';
		$js  = $dir . '/dejoiy-promo-deck.js';
		if ( is_readable( $css ) ) {
			wp_enqueue_style( 'dejoiy-promo-deck', $uri . '/dejoiy-promo-deck.css', array(), (string) filemtime( $css ) );
		}
		if ( is_readable( $js ) ) {
			wp_enqueue_script( 'dejoiy-promo-deck', $uri . '/dejoiy-promo-deck.js', array(), (string) filemtime( $js ), true );
		}
	}
	add_action( 'wp_enqueue_scripts', 'dejoiy_promo_deck_assets', 200 );
}

if ( ! function_exists( 'dejoiy_promo_deck_images' ) ) {
	function dejoiy_promo_deck_images() {
		$map = get_option( 'dejoiy_promo_deck_images', array() );
		return is_array( $map ) ? $map : array();
	}
}

/**
 * Renders slide art: AI image (if generated) else inline SVG scene.
 *
 * @param string $id     Slide id.
 * @param string $art    Art type for the SVG fallback (icon glyph grid).
 * @param string $ai_key Option key used to look up a generated image URL.
 * @return string
 */
function dejoiy_promo_deck_art( $id, $art = 'spark', $ai_key = '' ) {
	$images = dejoiy_promo_deck_images();
	$img    = '';
	if ( '' !== $ai_key && isset( $images[ $ai_key ] ) && '' !== $images[ $ai_key ] ) {
		$img = $images[ $ai_key ];
	}

	if ( '' !== $img ) {
		return '<div class="dpd-art" data-dpd-art="' . esc_attr( $id ) . '">'
			. '<img class="dpd-art__img" src="' . esc_url( $img ) . '" alt="" loading="lazy" decoding="async">'
			. '</div>';
	}

	$icons = array(
		'spark' => array( '🛍️', '✨', '🧺' ),
		'universe' => array( '🛒', '📚', '🎨', '⚡', '🔁', '🛠️' ),
		'festival' => array( '🎉', '🏷️', '💜' ),
		'studio' => array( '👕', '☕', '🧢' ),
		'nexus' => array( '📖', '🧠', '🚀' ),
		'quick' => array( '🛵', '📦', '🥤' ),
		'renew' => array( '📱', '💻', '✅' ),
		'services' => array( '🧑‍💻', '🎥', '🏗️' ),
		'sell' => array( '📈', '🛍️', '💰' ),
		'intern' => array( '🎓', '🏅', '⚡' ),
		'trust' => array( '🚚', '🔒', '↩️', '🏷️' ),
		'cta' => array( '🚀', '🛍️', '✨' ),
	);
	$chips = isset( $icons[ $art ] ) ? $icons[ $art ] : $icons['spark'];

	ob_start();
	?>
	<div class="dpd-art" data-dpd-art="<?php echo esc_attr( $id ); ?>" aria-hidden="true">
		<svg class="dpd-art__orb dpd-art__orb--r" viewBox="0 0 520 520"><circle cx="260" cy="260" r="250" fill="none" stroke="rgba(255,210,125,.35)" stroke-width="1.5" stroke-dasharray="4 12"/><circle cx="260" cy="260" r="180" fill="none" stroke="rgba(255,159,240,.35)" stroke-width="1.5" stroke-dasharray="3 10"/><circle cx="260" cy="260" r="110" fill="none" stroke="rgba(166,196,255,.4)" stroke-width="2" stroke-dasharray="2 8"/></svg>
		<svg class="dpd-art__orb dpd-art__orb--l" viewBox="0 0 360 360"><path d="M180 30 L300 300 H60 Z" fill="none" stroke="rgba(255,255,255,.16)" stroke-width="1.5"/><path d="M180 95 L242 270 H118 Z" fill="rgba(255,255,255,.06)"/></svg>
		<div class="dpd-art__bubbles">
			<?php foreach ( $chips as $i => $c ) : ?>
				<span class="dpd-art__bubble dpd-art__bubble--<?php echo esc_attr( $i + 1 ); ?>"><?php echo $c; // phpcs:ignore ?></span>
			<?php endforeach; ?>
		</div>
		<svg class="dpd-art__wave" viewBox="0 0 400 120" preserveAspectRatio="none"><path d="M0 70 C 60 40, 120 100, 200 70 S 340 30, 400 60 V120 H0 Z" fill="rgba(255,255,255,.09)"/></svg>
		<span class="dpd-art__tag">DEJOIY</span>
	</div>
	<?php
	return (string) ob_get_clean();
}

if ( ! function_exists( 'dejoiy_promo_deck_body_class' ) ) {
	function dejoiy_promo_deck_body_class( $classes ) {
		if ( is_page_template( 'dejoiy-promo-deck.php' ) ) {
			$classes[] = 'dejoiy-deck-page';
		}
		return $classes;
	}
	add_filter( 'body_class', 'dejoiy_promo_deck_body_class' );
}

if ( ! function_exists( 'dejoiy_promo_deck_html' ) ) {
	function dejoiy_promo_deck_html() {
		$shop     = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
		$deals    = home_url( '/dejoiy-festival-sale/' );
		$studio   = home_url( '/dejoiy-custom-studio/' );
		$nexus    = home_url( '/dejoiy-library/?dejoiy_library=1' );
		$quick    = home_url( '/dejoiy-quick-mart/' );
		$renew    = home_url( '/dejoiy-refurbished/' );
		$services = home_url( '/dejoiy-services/' );
		$seller   = home_url( '/sell-on-dejoiy/' );
		$vreg     = home_url( '/vendor-register/' );
		$interns  = 'https://internships.dejoiy.com/';
		$progs    = home_url( '/internships/' );

		$slides = array(
			array(
				'id'    => 'cover',
				'art'   => 'spark',
				'kicker'=> 'DEJOIY',
				'title' => 'Discover. Create. Own.',
				'sub'   => 'India\u2019s next-gen marketplace — shop, learn, design, renew & hire across six interconnected worlds.',
				'cta'   => 'Explore Shop',
				'url'   => $shop,
				'link'  => 'Every world, one home',
				'link2' => $shop,
				'grad'  => 'radial',
				'bg'    => 'a',
			),
			array(
				'id'    => 'universe',
				'art'   => 'universe',
				'kicker'=> 'THE DEJOIY UNIVERSE',
				'title' => 'Six worlds. One account.',
				'sub'   => 'Jump between shopping, knowledge, creation, instant essentials, refurbished tech and expert services — seamlessly.',
				'cta'   => 'Visit the Universe',
				'url'   => home_url( '/#dejoiy-worlds' ),
				'link'  => 'See all worlds',
				'link2' => home_url( '/shop/' ),
				'grad'  => 'universe',
				'bg'    => 'b',
			),
			array(
				'id'    => 'festival',
				'art'   => 'festival',
				'kicker'=> 'JOY FESTIVAL SALE',
				'title' => 'Deals that make you smile',
				'sub'   => 'Big offers on electronics, fashion, home & more. Best-price guarantee, UPI & easy returns.',
				'cta'   => 'Shop the Sale',
				'url'   => $deals,
				'link'  => 'UPTO 60% OFF',
				'link2' => $deals,
				'grad'  => 'fest',
				'bg'    => 'c',
			),
			array(
				'id'    => 'studio',
				'art'   => 'studio',
				'kicker'=> 'DEJOIY CUSTOM STUDIO',
				'title' => 'Design it. Create it. Own it.',
				'sub'   => 'Custom t-shirts, mugs, caps & tote bags made just for you. Your style, your words.',
				'cta'   => 'Open the Studio',
				'url'   => $studio,
				'link'  => '100% YOUR DESIGN · FROM ₹349',
				'link2' => $studio,
				'grad'  => 'studio',
				'bg'    => 'd',
			),
			array(
				'id'    => 'nexus',
				'art'   => 'nexus',
				'kicker'=> 'DEJOIY NEXUS',
				'title' => 'Read. Learn. Grow.',
				'sub'   => 'Books, eBooks & courses for every curious mind. Gyaan se badho, khush raho.',
				'cta'   => 'Enter Nexus',
				'url'   => $nexus,
				'link'  => 'FREE CLASSICS & COURSES INSIDE',
				'link2' => $nexus,
				'grad'  => 'nexus',
				'bg'    => 'e',
			),
			array(
				'id'    => 'quick',
				'art'   => 'quick',
				'kicker'=> 'DEJOIY QUICKMART',
				'title' => 'Instant essentials, at your door',
				'sub'   => 'Everyday needs delivered fast. Stock-up without the thinking.',
				'cta'   => 'Grab Essentials',
				'url'   => $quick,
				'link'  => 'Fast delivery',
				'link2' => $quick,
				'grad'  => 'quick',
				'bg'    => 'f',
			),
			array(
				'id'    => 'renew',
				'art'   => 'renew',
				'kicker'=> 'DEJOIY RENEW',
				'title' => 'Certified pre-owned tech',
				'sub'   => 'Refurbished smartphones & gadgets, quality-checked and backed by a warranty worth trusting.',
				'cta'   => 'Shop Renewed',
				'url'   => $renew,
				'link'  => 'Quality checked ✓',
				'link2' => $renew,
				'grad'  => 'renew',
				'bg'    => 'g',
			),
			array(
				'id'    => 'services',
				'art'   => 'services',
				'kicker'=> 'DEJOIY SERVICES',
				'title' => 'Book experts, not guesswork',
				'sub'   => 'Websites, design, marketing, repairs — hire vetted professionals who get it done.',
				'cta'   => 'Hire Experts',
				'url'   => $services,
				'link'  => 'Vetted pros · Real work',
				'link2' => $services,
				'grad'  => 'services',
				'bg'    => 'h',
			),
			array(
				'id'    => 'sell',
				'art'   => 'sell',
				'kicker'=> 'SELL ON DEJOIY',
				'title' => 'Turn your ideas into income',
				'sub'   => 'Reach more customers, grow your brand, and build your business with DEJOIY.',
				'cta'   => 'Become a Seller',
				'url'   => $vreg,
				'link'  => 'See how it works',
				'link2' => $seller,
				'grad'  => 'sell',
				'bg'    => 'i',
			),
			array(
				'id'    => 'intern',
				'art'   => 'intern',
				'kicker'=> 'DEJOIY INTERNSHIPS',
				'title' => 'Launch your career with DEJOIY',
				'sub'   => 'Get real-world experience, build your skills, and earn a verified internship certificate.',
				'cta'   => 'Explore Internships',
				'url'   => $interns,
				'link'  => 'View programs',
				'link2' => $progs,
				'grad'  => 'intern',
				'bg'    => 'j',
			),
			array(
				'id'    => 'trust',
				'art'   => 'trust',
				'kicker'=> 'WHY DEJOIY',
				'title' => 'Built for trust, end to end',
				'sub'   => 'Free delivery on eligible orders, secure UPI & card payments, easy hassle-free returns and fair prices guaranteed.',
				'cta'   => 'Start Shopping',
				'url'   => $shop,
				'link'  => '24×7 support',
				'link2' => home_url( '/contact/' ),
				'grad'  => 'trust',
				'bg'    => 'k',
			),
			array(
				'id'    => 'cta',
				'art'   => 'cta',
				'kicker'=> 'GROW WITH DEJOIY',
				'title' => 'Build your business on DEJOIY',
				'sub'   => 'Sell products, offer services or publish books — and power your growth with India\u2019s next-gen marketplace.',
				'cta'   => 'Open the Shop',
				'url'   => $shop,
				'link'  => 'Join as a Seller',
				'link2' => $vreg,
				'grad'  => 'cta',
				'bg'    => 'l',
			),
		);

		$tot  = count( $slides );
		$user = is_user_logged_in();

		ob_start();
		?>
		<div class="dpd" data-dpd>
			<div class="dpd__chrome">
				<a class="dpd__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php
					$logo = get_theme_mod( 'custom_logo' );
					if ( $logo ) {
						$logo_url = wp_get_attachment_image_url( $logo, 'medium' );
						if ( $logo_url ) {
							echo '<img class="dpd__logo" src="' . esc_url( $logo_url ) . '" alt="DEJOIY" height="38">';
						}
					}
					?>
					<span class="dpd__word"><?php esc_html_e( 'DEJOIY', 'dejoiy' ); ?></span>
				</a>
				<div class="dpd__nav">
					<button type="button" class="dpd__btn dpd__btn--prev" data-dpd-prev aria-label="Previous slide">‹</button>
					<span class="dpd__count" data-dpd-count>01</span>
					<button type="button" class="dpd__btn dpd__btn--next" data-dpd-next aria-label="Next slide">›</button>
				</div>
				<div class="dpd__tools">
					<button type="button" class="dpd__tool" data-dpd-play aria-label="Auto-play toggle">▶</button>
					<button type="button" class="dpd__tool" data-dpd-full aria-label="Fullscreen">⛶</button>
				</div>
			</div>

			<div class="dpd__track" data-dpd-track>
				<?php foreach ( $slides as $i => $s ) : ?>
					<section class="dpd__slide dpd__slide--<?php echo esc_attr( $s['bg'] ); ?> is-<?php echo 0 === $i ? 'active' : ''; ?>" data-dpd-slide data-titles="<?php echo esc_attr( $s['title'] ); ?>">
						<div class="dpd__bg" aria-hidden="true"></div>
						<div class="dpd__glow dpd__glow--1" aria-hidden="true"></div>
						<div class="dpd__glow dpd__glow--2" aria-hidden="true"></div>

						<div class="dpd__in">
							<div class="dpd__copy">
								<p class="dpd__kicker"><span class="dpd__spark" aria-hidden="true">✦</span><?php echo esc_html( $s['kicker'] ); ?></p>
								<h2 class="dpd__title"><?php echo esc_html( $s['title'] ); ?></h2>
								<p class="dpd__sub"><?php echo esc_html( $s['sub'] ); ?></p>
								<div class="dpd__actions">
									<a class="dpd__cta" href="<?php echo esc_url( $s['url'] ); ?>"><?php echo esc_html( $s['cta'] ); ?> <span class="dpd__cta-arrow" aria-hidden="true">→</span></a>
									<a class="dpd__link" href="<?php echo esc_url( $s['link2'] ); ?>"><?php echo esc_html( $s['link'] ); ?></a>
								</div>
								<?php if ( $user ) : ?>
									<p class="dpd__userline" data-slide="<?php echo esc_attr( $i + 1 ); ?>">Slide <?php echo esc_html( $i + 1 ); ?> / <?php echo esc_html( $tot ); ?></p>
								<?php endif; ?>
							</div>
							<?php echo dejoiy_promo_deck_art( $s['id'], $s['art'], $s['id'] . '_img' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
					</section>
				<?php endforeach; ?>
			</div>

			<div class="dpd__dots" data-dpd-dots>
				<?php foreach ( $slides as $i => $s ) : ?>
					<button type="button" class="dpd__dot <?php echo 0 === $i ? 'is-on' : ''; ?>" data-dpd-dot="<?php echo esc_attr( $i ); ?>" aria-label="Go to slide <?php echo esc_attr( $i + 1 ); ?>" title="<?php echo esc_attr( $s['title'] ); ?>"></button>
				<?php endforeach; ?>
			</div>

			<div class="dpd__progress" data-dpd-progress><span data-dpd-progressbar></span></div>
		</div>
		<?php
		return (string) ob_get_clean();
	}
}

if ( ! function_exists( 'dejoiy_promo_deck_render' ) ) {
	function dejoiy_promo_deck_render() {
		return dejoiy_promo_deck_html();
	}
}

get_header();
echo dejoiy_promo_deck_render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
get_footer();