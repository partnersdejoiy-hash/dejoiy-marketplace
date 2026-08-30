<?php
/**
 * DEJOIY Homepage V3 — Part 3: HTML Template
 */
if (!defined('ABSPATH')) exit;

function dejoiy_v3_render_header() {
    $shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');
    ?>
    <header class="djv3-header" id="djv3-header">
        <!-- Utility Bar -->
        <div class="djv3-utility">
            <div class="djv3-container djv3-utility__in">
                <a href="<?php echo esc_url(home_url('/')); ?>">🏠 Home</a>
                <span class="djv3-utility__sep"></span>
                <a href="<?php echo esc_url(home_url('/sell-on-dejoiy/')); ?>">Sell on DEJOIY</a>
                <span class="djv3-utility__sep"></span>
                <a href="<?php echo esc_url(home_url('/dejoy-festival-sale/')); ?>">🎉 Deals</a>
                <span class="djv3-utility__sep"></span>
                <a href="<?php echo esc_url(home_url('/my-account/orders/')); ?>">📦 Track Order</a>
                <span class="djv3-utility__sep"></span>
                <a href="<?php echo esc_url(home_url('/contact/')); ?>">💬 Support</a>
            </div>
        </div>

        <!-- Primary Header -->
        <div class="djv3-container djv3-primary">
            <!-- Logo -->
            <a href="<?php echo esc_url(home_url('/')); ?>" class="djv3-logo djv3-logo--wordmark">
                <span>DEJOIY</span>
            </a>

            <!-- Search -->
            <div class="djv3-search">
                <form class="djv3-search__form" action="<?php echo esc_url($shop_url); ?>" method="get" role="search">
                    <input type="hidden" name="post_type" value="product">
                    <button type="button" class="djv3-search__cat">All ▾</button>
                    <input class="djv3-search__input" type="text" name="s" placeholder="Search products, services, books, custom items..." autocomplete="off">
                    <button type="submit" class="djv3-search__btn" aria-label="Search">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </button>
                    <a href="<?php echo esc_url(home_url('/?joi=1')); ?>" class="djv3-search__joi" title="Ask JOI AI">JOI</a>
                </form>
            </div>

            <!-- Actions -->
            <div class="djv3-actions">
                <a href="<?php echo esc_url(home_url('/my-account/')); ?>" class="djv3-action" title="Account">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span>Account</span>
                </a>
                <a href="<?php echo esc_url(home_url('/my-account/wishlist/')); ?>" class="djv3-action" title="Wishlist">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    <span>Wishlist</span>
                </a>
                <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="djv3-action" title="Cart">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                    <span>Cart</span>
                    <?php if (function_exists('WC') && WC()->cart->get_cart_contents_count() > 0): ?>
                        <span class="djv3-action__count"><?php echo esc_html(WC()->cart->get_cart_contents_count()); ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>

        <!-- Secondary Nav -->
        <nav class="djv3-nav" id="djv3-nav">
            <div class="djv3-container djv3-nav__in">
                <button type="button" class="djv3-nav__browse" id="djv3-browse-btn" aria-expanded="false" aria-controls="djv3-mega">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
                    Browse All
                </button>
                <a href="<?php echo esc_url($shop_url); ?>" class="djv3-nav__item">Shop</a>
                <a href="<?php echo esc_url(home_url('/dejoiy-library/?dejoiy_library=1')); ?>" class="djv3-nav__item">Nexus</a>
                <a href="<?php echo esc_url(home_url('/dejoiy-custom-studio/')); ?>" class="djv3-nav__item">Custom Studio</a>
                <a href="<?php echo esc_url(home_url('/dejoiy-quick-mart/')); ?>" class="djv3-nav__item">QuickMart</a>
                <a href="<?php echo esc_url(home_url('/dejoiy-refurbished/')); ?>" class="djv3-nav__item">Renew</a>
                <a href="<?php echo esc_url(home_url('/dejoiy-services/')); ?>" class="djv3-nav__item">Hire</a>
                <a href="<?php echo esc_url(home_url('/dejoiy-festival-sale/')); ?>" class="djv3-nav__item" style="color:#FF168C">Deals</a>
                <a href="<?php echo esc_url(home_url('/?joi=1')); ?>" class="djv3-nav__item">✨ Ask JOI</a>
            </div>

            <!-- Mega Menu -->
            <div class="djv3-mega" id="djv3-mega" role="menu">
                <div class="djv3-mega__grid">
                    <div class="djv3-mega__col">
                        <h4>Shop</h4>
                        <a href="<?php echo esc_url($shop_url); ?>"><span class="mega-icon mega-icon--shop">🛍️</span> Marketplace</a>
                        <a href="<?php echo esc_url(home_url('/fashion/fashion/')); ?>"><span class="mega-icon mega-icon--shop">👗</span> Fashion</a>
                        <a href="<?php echo esc_url(home_url('/electronics/electronics/')); ?>"><span class="mega-icon mega-icon--shop">📱</span> Electronics</a>
                        <a href="<?php echo esc_url(home_url('/home-kitchen/home-kitchen/')); ?>"><span class="mega-icon mega-icon--shop">🏠</span> Home & Kitchen</a>
                        <a href="<?php echo esc_url(home_url('/beauty-personal-care/beauty-personal-care/')); ?>"><span class="mega-icon mega-icon--shop">💄</span> Beauty</a>
                        <a href="<?php echo esc_url(home_url('/sports-fitness/sports-fitness/')); ?>"><span class="mega-icon mega-icon--shop">⚡</span> Sports</a>
                    </div>
                    <div class="djv3-mega__col">
                        <h4>Learn</h4>
                        <a href="<?php echo esc_url(home_url('/dejoiy-library/?dejoiy_library=1')); ?>"><span class="mega-icon mega-icon--learn">📚</span> Nexus Library</a>
                        <a href="<?php echo esc_url(home_url('/dejoiy-library/?dejoiy_library=1')); ?>"><span class="mega-icon mega-icon--learn">📖</span> Books</a>
                        <a href="<?php echo esc_url(home_url('/dejoiy-nexus-lms/')); ?>"><span class="mega-icon mega-icon--learn">🎓</span> Courses</a>
                    </div>
                    <div class="djv3-mega__col">
                        <h4>Create</h4>
                        <a href="<?php echo esc_url(home_url('/dejoiy-custom-studio/')); ?>"><span class="mega-icon mega-icon--create">🎨</span> Custom Studio</a>
                        <a href="<?php echo esc_url(home_url('/dejoiy-custom-studio/')); ?>"><span class="mega-icon mega-icon--create">👕</span> Custom T-Shirts</a>
                        <a href="<?php echo esc_url(home_url('/dejoiy-custom-studio/')); ?>"><span class="mega-icon mega-icon--create">☕</span> Custom Mugs</a>
                    </div>
                    <div class="djv3-mega__col">
                        <h4>Grab & Renew</h4>
                        <a href="<?php echo esc_url(home_url('/dejoiy-quick-mart/')); ?>"><span class="mega-icon mega-icon--grab">⚡</span> QuickMart</a>
                        <a href="<?php echo esc_url(home_url('/dejoiy-refurbished/')); ?>"><span class="mega-icon mega-icon--renew">♻️</span> Renew</a>
                        <a href="<?php echo esc_url(home_url('/dejoiy-refurbished/')); ?>"><span class="mega-icon mega-icon--renew">💻</span> Refurbished Laptops</a>
                    </div>
                    <div class="djv3-mega__col">
                        <h4>Hire & Discover</h4>
                        <a href="<?php echo esc_url(home_url('/dejoiy-services/')); ?>"><span class="mega-icon mega-icon--hire">🤝</span> Services</a>
                        <a href="<?php echo esc_url(home_url('/dejoiy-festival-sale/')); ?>"><span class="mega-icon mega-icon--shop">🏷️</span> Deals</a>
                        <a href="<?php echo esc_url(home_url('/all-categories/')); ?>"><span class="mega-icon mega-icon--shop">📂</span> All Categories</a>
                    </div>
                </div>
                <div class="djv3-mega__promo">
                    <div class="djv3-mega__promo-text">
                        <h4>✨ Build Your Business on DEJOIY</h4>
                        <p>Sell products, offer services, publish books — start your journey today.</p>
                    </div>
                    <a href="<?php echo esc_url(home_url('/sell-on-dejoiy/')); ?>" class="djv3-btn djv3-btn--primary djv3-btn--sm">Start Selling →</a>
                </div>
            </div>
        </nav>
    </header>
    <?php
}

function dejoiy_v3_render_hero() {
    $gateways = dejoiy_universe_gateways();
    ?>
    <section class="djv3-hero djv3-reveal">
        <div class="djv3-hero__bg">
            <div class="djv3-hero__orb djv3-hero__orb--1"></div>
            <div class="djv3-hero__orb djv3-hero__orb--2"></div>
            <div class="djv3-hero__orb djv3-hero__orb--3"></div>
        </div>
        <div class="djv3-container djv3-hero__in">
            <div class="djv3-hero__content">
                <div class="djv3-hero__kicker">
                    <span class="djv3-hero__kicker-dot"></span>
                    India's Next-Gen Marketplace
                </div>
                <h1 class="djv3-hero__title">
                    Everything You Need.<br><span>One DEJOIY.</span>
                </h1>
                <p class="djv3-hero__desc">Shop, learn, create, grab, renew and hire — all in one joyful platform built for India.</p>
                <div class="djv3-hero__ctas">
                    <a href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/')); ?>" class="djv3-btn djv3-btn--primary djv3-btn--lg">Explore DEJOIY →</a>
                    <a href="<?php echo esc_url(home_url('/welcome-to-the-dejoiy-universe-indias-next-generation-marketplace/')); ?>" class="djv3-btn djv3-btn--secondary djv3-btn--lg" style="border-color:rgba(255,255,255,0.2);color:#fff;background:rgba(255,255,255,0.06);">Discover the Universe</a>
                </div>
            </div>
            <div class="djv3-hero__worlds" role="navigation" aria-label="DEJOIY Ecosystem">
                <?php
                $world_icons = array(
                    'marketplace' => array('icon' => '🛍️', 'cls' => 'shop'),
                    'nexus' => array('icon' => '📚', 'cls' => 'learn'),
                    'studio' => array('icon' => '🎨', 'cls' => 'create'),
                    'quickmart' => array('icon' => '⚡', 'cls' => 'grab'),
                    'refurbished' => array('icon' => '♻️', 'cls' => 'renew'),
                    'services' => array('icon' => '🤝', 'cls' => 'hire'),
                );
                foreach ($gateways as $key => $g) :
                    $wi = isset($world_icons[$key]) ? $world_icons[$key] : array('icon' => '◆', 'cls' => 'shop');
                ?>
                <a href="<?php echo esc_url($g['url']); ?>" class="djv3-hero-world">
                    <span class="djv3-hero-world__icon djv3-hero-world__icon--<?php echo esc_attr($wi['cls']); ?>"><?php echo $wi['icon']; ?></span>
                    <span class="djv3-hero-world__label"><?php echo esc_html($g['verb']); ?></span>
                    <span class="djv3-hero-world__sub"><?php echo esc_html($g['label']); ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}

function dejoiy_v3_render_joi() {
    $shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');
    $examples = dejoiy_universe_joi_examples();
    ?>
    <section class="djv3-joi djv3-reveal">
        <div class="djv3-container djv3-joi__in">
            <div class="djv3-joi__badge">✨ Powered by JOI Intelligence</div>
            <h2 class="djv3-joi__title">Ask JOI anything</h2>
            <p class="djv3-joi__desc">JOI understands what you need across all DEJOIY worlds — products, books, services, custom items and more.</p>
            <form class="djv3-joi__box" action="<?php echo esc_url($shop_url); ?>" method="get" role="search">
                <input type="hidden" name="post_type" value="product">
                <input class="djv3-joi__input" type="text" name="s" placeholder="Find me a laptop under ₹40,000..." autocomplete="off">
                <button type="submit" class="djv3-joi__submit">Discover</button>
            </form>
            <div class="djv3-joi__chips">
                <?php foreach ($examples as $ex) : ?>
                    <a class="djv3-joi__chip" href="<?php echo esc_url($ex['url']); ?>"><?php echo esc_html($ex['label']); ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}
