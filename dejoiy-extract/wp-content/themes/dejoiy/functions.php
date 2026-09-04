<?php
  /**
   * DEJOIY Child Theme Functions
   */

  /**
   * Load child theme styles.
   */
  add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles', 1001 );

  function theme_enqueue_styles() {
  	if ( function_exists( 'etheme_child_styles' ) ) {
  		etheme_child_styles();
  	}
  }

  /**
   * DEJOIY Custom WooCommerce Order Numbers
   *
   * Normal Orders Format:
   * 2605181-482-0000123
   * [7-digit date]-[3 random digits]-[7-digit order number]
   *
   * Subscription Orders Format:
   * DJS-2605181-0000123
   * DJS-[7-digit date]-[7-digit order number]
   */

  /**
   * Build custom order number.
   */
  function dejoiy_build_custom_order_number( $order ) {

  	if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
  		return '';
  	}

  	$order_id = $order->get_id();

  	/*
  	 * Date format:
  	 * yymmdd + day of week (1-7)
  	 * Example: 2605181
  	 */
  	$date_part = wp_date( 'ymdN' );

  	/*
  	 * Order ID padded to 7 digits.
  	 * Example: 123 => 0000123
  	 */
  	$order_part = str_pad( (string) $order_id, 7, '0', STR_PAD_LEFT );

  	/*
  	 * Detect subscription orders.
  	 */
  	$is_subscription = false;

  	if ( function_exists( 'wcs_order_contains_subscription' ) ) {
  		$is_subscription = wcs_order_contains_subscription(
  			$order,
  			array( 'parent', 'renewal', 'switch', 'resubscribe' )
  		);
  	}

  	/*
  	 * Subscription order format:
  	 * DJS-2605181-0000123
  	 */
  	if ( $is_subscription ) {
  		return 'DJS-' . $date_part . '-' . $order_part;
  	}

  	/*
  	 * Random 3-digit number.
  	 */
  	$random_part = str_pad( (string) wp_rand( 0, 999 ), 3, '0', STR_PAD_LEFT );

  	/*
  	 * Normal order format:
  	 * 2605181-482-0000123
  	 */
  	return $date_part . '-' . $random_part . '-' . $order_part;
  }

  /**
   * Save custom order number.
   */
  function dejoiy_generate_custom_order_number( $order_id ) {

  	if ( ! $order_id ) {
  		return;
  	}

  	$order = wc_get_order( $order_id );

  	if ( ! $order ) {
  		return;
  	}

  	/*
  	 * Do not overwrite if already generated.
  	 */
  	$existing = $order->get_meta( '_dejoiy_custom_order_number', true );

  	if ( ! empty( $existing ) ) {
  		return;
  	}

  	$custom_number = dejoiy_build_custom_order_number( $order );

  	if ( ! empty( $custom_number ) ) {
  		$order->update_meta_data( '_dejoiy_custom_order_number', $custom_number );
  		$order->save();
  	}
  }

  /**
   * Generate custom number for checkout orders.
   */
  add_action(
  	'woocommerce_checkout_order_processed',
  	'dejoiy_generate_custom_order_number',
  	20,
  	1
  );

  /**
   * Generate custom number for manually created orders.
   */
  add_action(
  	'woocommerce_new_order',
  	'dejoiy_generate_custom_order_number',
  	20,
  	1
  );

  /**
   * Display custom order number everywhere.
   */
  function dejoiy_display_custom_order_number( $order_number, $order ) {

  	if ( ! $order ) {
  		return $order_number;
  	}

  	$custom_number = $order->get_meta( '_dejoiy_custom_order_number', true );

  	if ( ! empty( $custom_number ) ) {
  		return $custom_number;
  	}

  	return $order_number;
  }

  add_filter(
  	'woocommerce_order_number',
  	'dejoiy_display_custom_order_number',
  	10,
  	2
  );

    /* ===== DEJOIY CUSTOM STUDIO v7 ===== */
    function dejoiy_custom_studio_enqueue() {
        if ( ! is_page( 4900 ) && get_queried_object_id() !== 4900 ) return;
        $uri = get_stylesheet_directory_uri();
        wp_register_script( 'dcs-gsap',  'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js', array(), '3.12.5', true );
        wp_register_script( 'dcs-st',    'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js', array('dcs-gsap'), '3.12.5', true );
        wp_register_script( 'dcs-lenis', 'https://cdn.jsdelivr.net/npm/@studio-freight/lenis@1.0.42/bundled/lenis.min.js', array(), '1.0.42', true );
        wp_register_script( 'dcs-tilt',  'https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.8.1/vanilla-tilt.min.js', array(), '1.8.1', true );
        wp_enqueue_style(  'dcs-style',  $uri . '/dcs.css', array(), '7.1' );
        wp_enqueue_script( 'dcs-script', $uri . '/dcs.js', array('dcs-gsap','dcs-st','dcs-lenis','dcs-tilt'), '7.1', true );
    }
    add_action( 'wp_enqueue_scripts', 'dejoiy_custom_studio_enqueue' );

    require_once get_stylesheet_directory() . '/dcs-shortcode.php';
    require_once get_stylesheet_directory() . '/dcs-template.php';

    /* Force page 4900 to render our content, bypassing Elementor at any priority */
    add_filter( 'the_content', 'dcs_force_content', 9999 );
    add_filter( 'elementor/frontend/the_content', 'dcs_force_content', 9999 );
    function dcs_force_content( $content ) {
        global $post;
        if ( ! empty( $post ) && intval( $post->ID ) === 4900 ) {
            return do_shortcode( '[dejoiy_custom_studio]' );
        }
        return $content;
    }

    /* DCS AJAX Product & Page Search */
    add_action('wp_ajax_dcs_search','dcs_search_handler');
    add_action('wp_ajax_nopriv_dcs_search','dcs_search_handler');
    function dcs_search_handler(){
        $t=sanitize_text_field(isset($_GET['term'])?$_GET['term']:'');
        if(strlen($t)<2){header('Content-Type:application/json');echo'[]';wp_die();}
        $out=[];
        $pq=new WP_Query(['post_type'=>'product','post_status'=>'publish','s'=>$t,'posts_per_page'=>8,'orderby'=>'relevance','no_found_rows'=>true]);
        foreach($pq->posts as $p){$pr=function_exists('wc_get_product')?wc_get_product($p->ID):null;$out[]=['id'=>$p->ID,'title'=>html_entity_decode(get_the_title($p->ID),ENT_QUOTES),'url'=>get_permalink($p->ID),'thumb'=>(string)(get_the_post_thumbnail_url($p->ID,'thumbnail')?:''),'price'=>$pr?wp_strip_all_tags($pr->get_price_html()):'','type'=>'product'];}
        wp_reset_postdata();
        $gq=new WP_Query(['post_type'=>'page','post_status'=>'publish','s'=>$t,'posts_per_page'=>4,'orderby'=>'relevance','no_found_rows'=>true]);
        foreach($gq->posts as $p){$out[]=['id'=>$p->ID,'title'=>html_entity_decode(get_the_title($p->ID),ENT_QUOTES),'url'=>get_permalink($p->ID),'thumb'=>'','price'=>'','type'=>'page'];}
        wp_reset_postdata();
        header('Content-Type:application/json;charset=utf-8');
        header('Cache-Control:no-store');
        echo wp_json_encode($out);
        wp_die();
    }

/* DEJOIY Custom Studio — Elementor page 4399 motion & layout sync */
require_once get_stylesheet_directory() . '/dejoiy-studio-sync.php';

function dejoiy_studio_elementor_enqueue() {
	/* Superseded by studio-universe v9 */
	return;
	if ( ! is_page( 4399 ) && (int) get_queried_object_id() !== 4399 ) {
		return;
	}
	$uri = get_stylesheet_directory_uri();
	$dir = get_stylesheet_directory();
	$css = $dir . '/dejoiy-studio-motion.css';
	$js  = $dir . '/dejoiy-studio-motion.js';
	if ( file_exists( $css ) ) {
		wp_enqueue_style(
			'dejoiy-studio-motion',
			$uri . '/dejoiy-studio-motion.css',
			array(),
			(string) filemtime( $css )
		);
	}
	if ( file_exists( $js ) ) {
		wp_enqueue_script(
			'dejoiy-studio-motion',
			$uri . '/dejoiy-studio-motion.js',
			array(),
			(string) filemtime( $js ),
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'dejoiy_studio_elementor_enqueue', 1002 );

require_once get_stylesheet_directory() . '/studio-universe.php';

// DEJOIY Library Universe
require_once get_stylesheet_directory() . '/library-universe.php';
require_once get_stylesheet_directory() . '/dejoiy-mobile-header-fix.php';
// DEJOIY Nexus LMS
$library_lms = get_stylesheet_directory() . '/library-lms.php';
if ( is_readable( $library_lms ) ) {
	require_once $library_lms;
}

// DEJOIY Marketplace Evolution — Phase 1
require_once get_stylesheet_directory() . '/dejoiy-marketplace-evolution.php';
// DEJOIY Global Header OS — unified canonical header
$gh_path = get_stylesheet_directory() . '/dejoiy-global-header.php';
if ( is_readable( $gh_path ) ) {
	require_once $gh_path;
}

// DEJOIY Global Footer — unified marketplace footer (optional, fallback safe)
$gf_path = get_stylesheet_directory() . '/dejoiy-global-footer.php';
if ( is_readable( $gf_path ) ) {
	require_once $gf_path;
}

// DEJOIY Product Detail — trust strip + Buy Now (optional, fallback safe)
$pd_path = get_stylesheet_directory() . '/dejoiy-product-detail.php';
if ( is_readable( $pd_path ) ) {
	require_once $pd_path;
}

// DEJOIY Header OS V4 — Elementor header ecosystem navigation (legacy, disabled when global header active)
if ( ! defined( 'DEJOIY_GH_DISABLED' ) || ! DEJOIY_GH_DISABLED ) {
	// Header OS V4 is superseded by Global Header OS
} else {
	require_once get_stylesheet_directory() . '/dejoiy-header-os-v4.php';

/* ===== DEJOIY Animated Header — Three.js Canvas ===== */
function dejoiy_animated_header_assets() {
	if ( ! function_exists( 'dejoiy_desktop_marketplace_header_enabled' ) || ! dejoiy_desktop_marketplace_header_enabled() ) {
		return;
	}
	$uri = get_stylesheet_directory_uri();
	$dir = get_stylesheet_directory();

	wp_enqueue_script(
		'dejoiy-three-js',
		'https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js',
		array(),
		'r128',
		true
	);

	$css = $dir . '/dejoiy-header-three.css';
	if ( is_readable( $css ) ) {
		wp_enqueue_style(
			'dejoiy-header-three',
			$uri . '/dejoiy-header-three.css',
			array(),
			(string) filemtime( $css )
		);
	}

	$js = $dir . '/dejoiy-header-three-canvas.js';
	if ( is_readable( $js ) ) {
		wp_enqueue_script(
			'dejoiy-header-three-canvas',
			$uri . '/dejoiy-header-three-canvas.js',
			array( 'dejoiy-three-js' ),
			(string) filemtime( $js ),
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'dejoiy_animated_header_assets', 10055 );
}

// DEJOIY Motion Adapters were removed — the legacy full-viewport ambient-orb
// overlay (#dejoiy-motion-root) painted over the page on every layout and was a
// source of duplicate-visual reports. Motion now lives inside the header canvas
// (see dejoiy-header-motion) and marketplace home, scoped to their containers.

/**
 * The xstore product template renders its own gallery, so the
 * gallery-slider-for-woocommerce markup (#wpgs-gallery) never appears.
 * Its assets are dead weight here and log an IntersectionObserver
 * TypeError on every product page — dequeue them.
 */
add_action( 'wp_enqueue_scripts', 'dejoiy_drop_unused_gallery_slider_assets', 10120 );
function dejoiy_drop_unused_gallery_slider_assets() {
	$handles = array(
		'gallery-slider-for-woocommerce',
		'wcgs-swiper',
		'wcgs-fancybox',
		'sp_wcgs-fontello-fontende-icons',
		'sp_wcgs-fontello-icons',
	);
	foreach ( $handles as $handle ) {
		wp_dequeue_style( $handle );
	}
	wp_dequeue_script( 'gallery-slider-for-woocommerce' );
}

/**
 * Block REST user enumeration for logged-out visitors.
 * /wp/v2/users exposes admin slugs and email addresses otherwise.
 */
add_filter( 'rest_endpoints', 'dejoiy_harden_rest_users' );
function dejoiy_harden_rest_users( $endpoints ) {
	if ( is_user_logged_in() ) {
		return $endpoints;
	}
	unset( $endpoints['/wp/v2/users'] );
	unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
	return $endpoints;
}

/**
 * Dead author archives are a secondary enumeration vector — bounce them home.
 */
add_action( 'template_redirect', 'dejoiy_block_author_archives' );
function dejoiy_block_author_archives() {
	if ( is_author() ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}
