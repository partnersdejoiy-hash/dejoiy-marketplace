<?php if(!defined('ABSPATH'))exit; ?>
    <!-- MOBILE HEADER (shown <=1024px via CSS) -->
    <header id="dcs-mhdr">
      <div id="dcs-mhdr-top">
        <a href="<?php echo esc_url(home_url('/')); ?>" id="dcs-mlogo" aria-label="DEJOIY Home">
          <?php if(!empty($dcs_logo_url)):?><img src="<?php echo esc_attr($dcs_logo_url);?>" alt="DEJOIY" height="38"><?php else:?><span class="dcs-mwordmark">DEJOIY</span><?php endif;?>
        </a>
        <div class="dcs-micons">
          <a href="https://dejoiy.com/my-account/" class="dcs-micon" aria-label="Account"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></a>
          <a href="https://dejoiy.com/my-account/?et-compare-page" class="dcs-micon" aria-label="Compare"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></a>
          <a href="https://dejoiy.com/my-account/?et-wishlist-page" class="dcs-micon" aria-label="Wishlist"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></a>
          <a href="<?php echo esc_url($dcs_cart_url);?>" class="dcs-micon dcs-mcart" aria-label="Cart">
            <span style="position:relative;display:inline-flex">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
              <?php if($dcs_cart>0):?><span class="dcs-mbadge"><?php echo $dcs_cart;?></span><?php endif;?>
            </span>
          </a>
          <button id="dcs-burger" aria-label="Open menu" aria-expanded="false"><span></span><span></span><span></span></button>
        </div>
      </div>
      <div id="dcs-mhdr-search">
        <form id="dcs-msearch-form" action="<?php echo esc_url(home_url('/shop/')); ?>" method="get" role="search" autocomplete="off" style="position:relative">
          <div class="dcs-ms-wrap">
            <input type="search" id="dcs-msearch-in" name="s" placeholder="Search custom products..." aria-label="Search" autocomplete="off" spellcheck="false">
            <input type="hidden" name="post_type" value="product">
            <button type="submit" class="dcs-ms-btn" aria-label="Search"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></button>
          </div>
          <div id="dcs-msearch-drop" hidden></div>
        </form>
      </div>
    </header>
    <!-- MOBILE NAV CARDS (shown <=1024px via CSS) -->
    <div class="dejoiy-nav-wrapper" id="djNav">
      <div class="dejoiy-nav-scroll" id="djScroll">
        <div class="dejoiy-nav-card dcs-active-card" data-nav="studio" data-href="https://dejoiy.com/dejoiy-custom-studio/"><div class="dejoiy-nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#E8609A" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l2.09 6.26L20.18 9l-5 4.09L16.82 20 12 16.54 7.18 20l1.64-6.91L3.82 9l6.09-.74z"/></svg></div><span class="dejoiy-nav-label">Custom Studio</span></div>
        <div class="dejoiy-nav-card" data-nav="shop" data-href="https://dejoiy.com/shop/"><div class="dejoiy-nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#7C5CE4" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg></div><span class="dejoiy-nav-label">Shop</span></div>
        <div class="dejoiy-nav-card" data-nav="library" data-href="https://dejoiy.com/dejoiy-library/"><div class="dejoiy-nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#A88BF5" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg></div><span class="dejoiy-nav-label">Library</span></div>
        <div class="dejoiy-nav-card" data-nav="quick" data-href="https://dejoiy.com/shop/"><div class="dejoiy-nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg></div><span class="dejoiy-nav-label">QuickMart</span></div>
        <div class="dejoiy-nav-card" data-nav="refurbished" data-href="https://dejoiy.com/product-category/renewed-refurbished/"><div class="dejoiy-nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#00C9A7" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/></svg></div><span class="dejoiy-nav-label">Refurbished</span></div>
        <div class="dejoiy-nav-card" data-nav="services" data-href="https://dejoiy.com/product-category/services-marketplace/"><div class="dejoiy-nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg></div><span class="dejoiy-nav-label">Services</span></div>
        <div class="dejoiy-nav-card" data-nav="support" data-href="https://dejoiy.com/support-page/"><div class="dejoiy-nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg></div><span class="dejoiy-nav-label">Support</span></div>
      </div>
    </div>
    <!-- DEJOIY loading overlay -->
    <div class="dj-overlay" id="djOverlay">
      <div class="dj-logo"><span class="y">DE</span><span style="background:linear-gradient(90deg,#7C5CE4,#E8609A);-webkit-background-clip:text;-webkit-text-fill-color:transparent">JO</span><span class="j">IY</span></div>
      <div class="dj-sub"><span class="y">YOU</span><span style="color:rgba(245,245,240,.4);margin:0 4px">+</span><span class="j">JOY</span></div>
      <div class="dj-spin"></div>
    </div>
    <!-- CUSTOM BOTTOM NAV: replaces xStore bottom menu, dark purple theme -->
    <nav id="dcs-bnav" aria-label="DEJOIY navigation">
      <a href="https://dejoiy.com/" class="dcs-bn-item" aria-label="Home">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        <span>Home</span>
      </a>
      <a href="https://dejoiy.com/shop/" class="dcs-bn-item" aria-label="Shop">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
        <span>Shop</span>
      </a>
      <a href="https://dejoiy.com/dejoiy-custom-studio/" class="dcs-bn-item dcs-bn-active" aria-label="Studio">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l2.09 6.26L20.18 9l-5 4.09L16.82 20 12 16.54 7.18 20l1.64-6.91L3.82 9l6.09-.74z"/></svg>
        <span>Studio</span>
      </a>
      <a href="https://dejoiy.com/dejoiy-library/" class="dcs-bn-item" aria-label="Library">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
        <span>Library</span>
      </a>
      <button id="dcs-bn-menu" class="dcs-bn-item" aria-label="Menu">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        <span>Menu</span>
      </button>
    </nav>