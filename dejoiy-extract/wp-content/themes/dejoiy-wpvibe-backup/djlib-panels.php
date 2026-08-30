<?php if(!defined('ABSPATH'))exit;
// Variables inherited from djlib_html() scope: $acc, $lib, $home, $shop, $cs, $ref, $qm, $deals, $biz, $svc, $sup
$logged_in=is_user_logged_in();
$logout_url=esc_url(wp_logout_url($lib));
?><div id="djlib-mob-menu" role="dialog" aria-modal="true">
  <div id="djlib-mob-panel">
    <div id="djlib-mob-head"><span id="djlib-mob-title">Menu</span><button id="djlib-mob-close" aria-label="Close">&#10005;</button></div>
    <div class="djlib-mob-section">Browse</div>
    <a class="djlib-mob-link" href="<?=$home?>">Home</a>
    <a class="djlib-mob-link" href="<?=$shop?>">Shop</a>
    <a class="djlib-mob-link" href="<?=$cs?>">Custom Studio</a>
    <a class="djlib-mob-link" href="<?=$ref?>">Refurbished</a>
    <a class="djlib-mob-link" href="<?=$qm?>">QuickMart</a>
    <a class="djlib-mob-link" href="<?=$lib?>">Library</a>
    <a class="djlib-mob-link" href="<?=$deals?>">Deals</a>
    <a class="djlib-mob-link" href="<?=$biz?>">Business</a>
    <a class="djlib-mob-link" href="<?=$svc?>">Services</a>
    <a class="djlib-mob-link" href="<?=$sup?>">Support</a>
    <div class="djlib-mob-section">Account</div>
    <a class="djlib-mob-link" href="<?=$acc?>">My Account</a>
    <a class="djlib-mob-link" href="<?=$acc?>orders/">My Books</a>
  </div>
</div>
<div id="djlib-acc-overlay" class="djlib-panel-overlay"></div>
<aside id="djlib-acc-panel" class="djlib-slide-panel" role="dialog" aria-modal="true" aria-label="My Account">
  <div class="djlib-panel-head">
    <span class="djlib-panel-title"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> MY ACCOUNT</span>
    <button id="djlib-acc-close" class="djlib-panel-close" aria-label="Close">&#10005;</button>
  </div>
  <?php if($logged_in):?>
  <div class="djlib-acc-menu">
    <a class="djlib-acc-link" href="<?=$acc?>">Dashboard</a>
    <a class="djlib-acc-link" href="<?=$acc?>orders/">Orders</a>
    <a class="djlib-acc-link" href="<?=$acc?>downloads/">Downloads</a>
    <a class="djlib-acc-link" href="<?=$acc?>edit-address/">Addresses</a>
    <a class="djlib-acc-link" href="<?=$acc?>edit-account/">Account Details</a>
    <a class="djlib-acc-link" href="/wishlist/">Wishlist</a>
    <a class="djlib-acc-link" href="<?=$acc?>inquiries/">Inquiries</a>
    <a class="djlib-acc-link" href="/compare/">Compare</a>
    <a class="djlib-acc-link djlib-acc-logout" href="<?=$logout_url?>">Log Out</a>
  </div>
  <?php else:?>
  <div class="djlib-acc-login">
    <p class="djlib-login-hint">Sign in to access your account</p>
    <form method="post" action="<?=esc_url(wp_login_url($lib))?>">
      <input class="djlib-login-field" type="text" name="log" placeholder="Email or Username" required autocomplete="username">
      <input class="djlib-login-field" type="password" name="pwd" placeholder="Password" required autocomplete="current-password">
      <input type="hidden" name="redirect_to" value="<?=esc_attr($lib)?>">
      <input type="hidden" name="rememberme" value="forever">
      <?php wp_nonce_field('djlib_wp_login');?>
      <button class="djlib-login-btn" type="submit" name="wp-submit">Sign In</button>
    </form>
    <div class="djlib-acc-links">
      <a href="<?=esc_url(wp_lostpassword_url($lib))?>">Forgot password?</a>
      <a href="<?=$acc?>?action=register">Create Account</a>
    </div>
  </div>
  <?php endif;?>
</aside>
<div id="djlib-books-overlay" class="djlib-panel-overlay"></div>
<aside id="djlib-books-panel" class="djlib-slide-panel" role="dialog" aria-modal="true" aria-label="My Library">
  <div class="djlib-panel-head">
    <span class="djlib-panel-title"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg> MY BOOKS</span>
    <button id="djlib-books-close" class="djlib-panel-close" aria-label="Close">&#10005;</button>
  </div>
  <div class="djlib-panel-body" id="djlib-books-body">
    <div class="djlib-panel-loading"><span class="djlib-spin"></span><span>Loading&hellip;</span></div>
  </div>
</aside>
