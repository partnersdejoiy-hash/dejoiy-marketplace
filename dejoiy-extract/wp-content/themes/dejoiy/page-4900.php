<?php
    if(!defined('ABSPATH'))exit;
    add_action('wp_head',function(){
      echo '<link rel="stylesheet" href="'.get_stylesheet_directory_uri().'/dcs-hdr.css?v=7" media="all">';
      echo '<link rel="stylesheet" href="'.get_stylesheet_directory_uri().'/dcs-mobile-extra.css?v=1" media="all">';
    },99);
    add_action('wp_footer',function(){
      echo '<script src="'.get_stylesheet_directory_uri().'/dcs-hdr.js?v=7"><\/script>';
    },20);
    get_header();
    $dcs_cart=function_exists('WC')?WC()->cart->get_cart_contents_count():0;
    $dcs_cart_url=function_exists('wc_get_cart_url')?wc_get_cart_url():'https://dejoiy.com/cart/';
    $dcs_logo=get_theme_mod('custom_logo');
    $dcs_logo_url=$dcs_logo?esc_url(wp_get_attachment_image_url($dcs_logo,'medium')):'';
    ?>
    <header id="dcs-hdr" role="banner">
      <div id="dcs-hdr-top">
        <div id="dcs-hdr-top-in">
          <a href="<?php echo esc_url(home_url('/')); ?>" id="dcs-logo" aria-label="DEJOIY Home">
            <?php if($dcs_logo_url):?><img src="<?php echo $dcs_logo_url;?>" alt="DEJOIY" height="42"><?php else:?><span id="dcs-wordmark">DEJOIY</span><?php endif;?>
          </a>
          <form id="dcs-search-form" action="<?php echo esc_url(home_url('/')); ?>" method="get" role="search" autocomplete="off">
            <div id="dcs-search-wrap">
              <input type="search" name="s" id="dcs-search-in" placeholder="Search for DEJOIY." aria-label="Search" autocomplete="off" spellcheck="false">
              <button type="submit" id="dcs-search-btn" aria-label="Search"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></button>
              <div id="dcs-search-drop" hidden></div>
            </div>
          </form>
          <div id="dcs-hdr-icons">
            <a href="https://dejoiy.com/my-account/" class="dcs-icon-link" aria-label="My Account"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg><span>My Account</span></a>
            <a href="https://dejoiy.com/my-account/?et-compare-page" class="dcs-icon-link" aria-label="Comparison"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg><span>Comparison</span></a>
            <a href="https://dejoiy.com/my-account/?et-wishlist-page" class="dcs-icon-link" aria-label="Favorites"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg><span>Favorites</span></a>
            <a href="<?php echo esc_url($dcs_cart_url);?>" class="dcs-icon-link dcs-cart-link" aria-label="My Cart"><span class="dcs-icon-wrap"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg><?php if($dcs_cart>0):?><span class="dcs-icon-badge"><?php echo $dcs_cart;?></span><?php endif;?></span><span>My Cart</span></a>
            <button id="dcs-burger" aria-label="Open menu" aria-expanded="false"><span></span><span></span><span></span></button>
          </div>
        </div>
      </div>
      <div id="dcs-hdr-bot">
        <div id="dcs-hdr-bot-in">
          <button id="dcs-explore-btn" aria-label="Explore categories"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg> Explore <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg></button>
          <nav id="dcs-hdr-nav" aria-label="Main Navigation">
            <ul id="dcs-hdr-list">
              <li><a href="https://dejoiy.com/">Home</a></li>
              <li class="has-sub"><a href="https://dejoiy.com/shop/">Shop <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg></a>
                <ul class="dcs-sub">
                  <li><a href="https://dejoiy.com/shop/">All Products</a></li>
                  <li><a href="https://dejoiy.com/product-category/customized-products/">Customized Products</a></li>
                  <li><a href="https://dejoiy.com/product-category/deals-offers/">Deals &amp; Offers</a></li>
                  <li><a href="https://dejoiy.com/product-category/renewed-refurbished/">Refurbished</a></li>
                </ul>
              </li>
              <li class="dcs-active"><a href="https://dejoiy.com/dejoiy-custom-studio/" class="dcs-nav-studio">Custom Studio</a></li>
              <li><a href="https://dejoiy.com/product-category/renewed-refurbished/">Refurbished</a></li>
              <li><a href="https://dejoiy.com/shop/">QuickMart</a></li>
              <li><a href="#">Library</a></li>
              <li><a href="https://dejoiy.com/product-category/deals-offers/">Deals</a></li>
              <li><a href="#">Business</a></li>
              <li><a href="https://dejoiy.com/product-category/services-marketplace/">Services</a></li>
              <li><a href="#">Support</a></li>
            </ul>
          </nav>
          <a href="https://dejoiy.com/shop/" id="dcs-sale-badge">Sale! 30% OFF!</a>
        </div>
      </div>
      <div id="dcs-explore-panel" hidden>
        <div class="dcs-exp-in">
          <div class="dcs-exp-col"><div class="dcs-exp-title">Browse Categories</div>
            <a class="dcs-exp-link" href="https://dejoiy.com/product-category/customized-products/">Customized Products</a>
            <a class="dcs-exp-link" href="https://dejoiy.com/product-category/electronics/">Electronics</a>
            <a class="dcs-exp-link" href="https://dejoiy.com/product-category/fashion/">Fashion</a>
            <a class="dcs-exp-link" href="https://dejoiy.com/product-category/deals-offers/">Deals &amp; Offers</a>
            <a class="dcs-exp-link" href="https://dejoiy.com/product-category/renewed-refurbished/">Refurbished</a>
            <a class="dcs-exp-link" href="https://dejoiy.com/product-category/services-marketplace/">Services</a>
          </div>
          <div class="dcs-exp-col"><div class="dcs-exp-title">Custom Studio</div>
            <a class="dcs-exp-link" href="https://dejoiy.com/product-category/customized-products/custom-t-shirts/">Custom T-Shirts</a>
            <a class="dcs-exp-link" href="https://dejoiy.com/product-category/customized-products/mugs/">Custom Mugs</a>
            <a class="dcs-exp-link" href="https://dejoiy.com/product-category/customized-products/phone-cases/">Phone Cases</a>
            <a class="dcs-exp-link" href="https://dejoiy.com/product-category/customized-products/packaging/">Custom Packaging</a>
            <a class="dcs-exp-link" href="https://dejoiy.com/product-category/customized-products/corporate-gifts/">Corporate Gifts</a>
          </div>
          <div class="dcs-exp-col"><div class="dcs-exp-title">Quick Links</div>
            <a class="dcs-exp-link" href="https://dejoiy.com/dejoiy-library/">Library</a>
            <a class="dcs-exp-link" href="https://dejoiy.com/support-page/">Support</a>
            <a class="dcs-exp-link dcs-exp-sale" href="https://dejoiy.com/product-category/deals-offers/">Sale! 30% OFF</a>
            <a class="dcs-exp-link" href="https://dejoiy.com/dejoiy-custom-studio/">Custom Studio</a>
          </div>
        </div>
      </div>
    </header>
    <?php include(get_stylesheet_directory().'/dcs-mobile.php'); ?>
    <div id="dcs-drawer" aria-hidden="true"><button id="dcs-drawer-close" aria-label="Close">&#x2715;</button>
      <form class="dcs-drawer-search" action="<?php echo esc_url(home_url('/')); ?>" method="get"><input type="search" name="s" placeholder="Search DEJOIY..." aria-label="Search"><button type="submit"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></button></form>
      <ul id="dcs-drawer-list">
        <li><a href="https://dejoiy.com/">Home</a></li><li><a href="https://dejoiy.com/shop/">Shop</a></li><li><a href="https://dejoiy.com/dejoiy-custom-studio/" class="dcs-nav-studio">Custom Studio</a></li><li><a href="https://dejoiy.com/product-category/renewed-refurbished/">Refurbished</a></li><li><a href="https://dejoiy.com/shop/">QuickMart</a></li><li><a href="#">Library</a></li><li><a href="https://dejoiy.com/product-category/deals-offers/">Deals</a></li><li><a href="#">Business</a></li><li><a href="https://dejoiy.com/product-category/services-marketplace/">Services</a></li><li><a href="#">Support</a></li>
      </ul>
    </div>
    <div id="dcs-overlay"></div>
    <div id="content" class="site-content dcs-fullpage">
    <?php echo do_shortcode('[dejoiy_custom_studio]'); ?>
    </div>
    <?php get_footer();?>