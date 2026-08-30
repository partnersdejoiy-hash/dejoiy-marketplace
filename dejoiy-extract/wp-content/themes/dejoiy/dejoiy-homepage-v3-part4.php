<?php
/**
 * DEJOIY Homepage V3 — Part 4: Remaining Sections + Footer + JS
 */
if (!defined('ABSPATH')) exit;

function dejoiy_v3_render_categories() {
    $cats = array(
        array('icon' => '📱', 'label' => 'Electronics', 'url' => home_url('/electronics/electronics/')),
        array('icon' => '👗', 'label' => 'Fashion', 'url' => home_url('/fashion/fashion/')),
        array('icon' => '🏠', 'label' => 'Home', 'url' => home_url('/home-kitchen/home-kitchen/')),
        array('icon' => '💄', 'label' => 'Beauty', 'url' => home_url('/beauty-personal-care/beauty-personal-care/')),
        array('icon' => '📚', 'label' => 'Books', 'url' => home_url('/dejoiy-library/?dejoiy_library=1')),
        array('icon' => '🎮', 'label' => 'Toys', 'url' => home_url('/toys-games/toys-games/')),
        array('icon' => '🏋️', 'label' => 'Sports', 'url' => home_url('/sports-fitness/sports-fitness/')),
        array('icon' => '🎨', 'label' => 'Studio', 'url' => home_url('/dejoiy-custom-studio/')),
        array('icon' => '🤝', 'label' => 'Services', 'url' => home_url('/dejoiy-services/')),
        array('icon' => '♻️', 'label' => 'Renew', 'url' => home_url('/dejoiy-refurbished/')),
        array('icon' => '⚡', 'label' => 'QuickMart', 'url' => home_url('/dejoiy-quick-mart/')),
        array('icon' => '🏷️', 'label' => 'Deals', 'url' => home_url('/dejoiy-festival-sale/')),
    );
    ?>
    <section class="djv3-cats djv3-reveal">
        <div class="djv3-container">
            <div class="djv3-section__header">
                <div>
                    <h2 class="djv3-section__title">Popular Categories</h2>
                    <p class="djv3-section__subtitle">Explore what DEJOIY has to offer</p>
                </div>
                <a href="<?php echo esc_url(home_url('/all-categories/')); ?>" class="djv3-section__action">View All →</a>
            </div>
            <div class="djv3-cats__scroll">
                <?php foreach ($cats as $cat) : ?>
                    <a href="<?php echo esc_url($cat['url']); ?>" class="djv3-cat">
                        <span class="djv3-cat__icon"><?php echo $cat['icon']; ?></span>
                        <span class="djv3-cat__label"><?php echo esc_html($cat['label']); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}

function dejoiy_v3_render_worlds() {
    $worlds = array(
        array('icon' => '🛍️', 'title' => 'Shop', 'desc' => 'Everything you love.', 'url' => home_url('/shop/'), 'cls' => 'shop', 'cta' => 'Explore Marketplace'),
        array('icon' => '📚', 'title' => 'Nexus', 'desc' => 'Read. Learn. Grow.', 'url' => home_url('/dejoiy-library/?dejoiy_library=1'), 'cls' => 'learn', 'cta' => 'Enter Nexus'),
        array('icon' => '🎨', 'title' => 'Custom Studio', 'desc' => 'Create it your way.', 'url' => home_url('/dejoiy-custom-studio/'), 'cls' => 'create', 'cta' => 'Open Studio'),
        array('icon' => '⚡', 'title' => 'QuickMart', 'desc' => 'Essentials, instantly.', 'url' => home_url('/dejoiy-quick-mart/'), 'cls' => 'grab', 'cta' => 'Grab Now'),
        array('icon' => '♻️', 'title' => 'Renew', 'desc' => 'Premium tech. Smarter prices.', 'url' => home_url('/dejoiy-refurbished/'), 'cls' => 'renew', 'cta' => 'Shop Renew'),
        array('icon' => '🤝', 'title' => 'Hire', 'desc' => 'Find trusted expertise.', 'url' => home_url('/dejoiy-services/'), 'cls' => 'hire', 'cta' => 'Explore Services'),
    );
    ?>
    <section class="djv3-section djv3-reveal">
        <div class="djv3-container">
            <div class="djv3-section__header">
                <div>
                    <h2 class="djv3-section__title">Explore the DEJOIY Universe</h2>
                    <p class="djv3-section__subtitle">One platform. Many worlds.</p>
                </div>
            </div>
            <div class="djv3-worlds__grid">
                <?php foreach ($worlds as $w) : ?>
                    <a href="<?php echo esc_url($w['url']); ?>" class="djv3-world-card djv3-world-card--<?php echo esc_attr($w['cls']); ?>">
                        <span class="djv3-world-card__icon"><?php echo $w['icon']; ?></span>
                        <h3 class="djv3-world-card__title"><?php echo esc_html($w['title']); ?></h3>
                        <p class="djv3-world-card__desc"><?php echo esc_html($w['desc']); ?></p>
                        <span class="djv3-world-card__cta"><?php echo esc_html($w['cta']); ?> →</span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}

function dejoiy_v3_render_trust() {
    ?>
    <section class="djv3-trust djv3-reveal">
        <div class="djv3-container">
            <div class="djv3-trust__grid">
                <div class="djv3-trust__item">
                    <span class="djv3-trust__icon">🚚</span>
                    <div class="djv3-trust__text">
                        <h4>Convenient Delivery</h4>
                        <p>Fast & reliable shipping</p>
                    </div>
                </div>
                <div class="djv3-trust__item">
                    <span class="djv3-trust__icon">🔒</span>
                    <div class="djv3-trust__text">
                        <h4>100% Secure Payments</h4>
                        <p>Razorpay protected</p>
                    </div>
                </div>
                <div class="djv3-trust__item">
                    <span class="djv3-trust__icon">💰</span>
                    <div class="djv3-trust__text">
                        <h4>Best Price Guarantee</h4>
                        <p>Competitive pricing</p>
                    </div>
                </div>
                <div class="djv3-trust__item">
                    <span class="djv3-trust__icon">🔄</span>
                    <div class="djv3-trust__text">
                        <h4>Easy Returns</h4>
                        <p>Hassle-free return policy</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php
}

function dejoiy_v3_render_seller_cta() {
    ?>
    <section class="djv3-seller-cta djv3-reveal">
        <div class="djv3-seller-cta__bg"></div>
        <div class="djv3-container djv3-seller-cta__in">
            <h2 class="djv3-seller-cta__title">Build Your Business on DEJOIY</h2>
            <p class="djv3-seller-cta__desc">DEJOIY is not just for buyers. Sell products, offer services, publish books — become part of India's growing ecosystem.</p>
            <div class="djv3-seller-cta__roles">
                <a href="<?php echo esc_url(home_url('/sell-on-dejoiy/')); ?>" class="djv3-seller-role">🛍️ Become a Seller</a>
                <a href="<?php echo esc_url(home_url('/dejoiy-services/')); ?>" class="djv3-seller-role">🤝 Become a Service Provider</a>
                <a href="<?php echo esc_url(home_url('/dejoiy-library/?dejoiy_library=1')); ?>" class="djv3-seller-role">📚 Become an Author</a>
                <a href="<?php echo esc_url(home_url('/dejoiy-custom-studio/')); ?>" class="djv3-seller-role">🎨 Become a Creator</a>
            </div>
            <a href="<?php echo esc_url(home_url('/vendor-register/')); ?>" class="djv3-btn djv3-btn--primary djv3-btn--lg">Start Your Journey →</a>
        </div>
    </section>
    <?php
}

function dejoiy_v3_render_footer() {
    ?>
    <footer class="djv3-footer">
        <div class="djv3-container">
            <div class="djv3-footer__grid">
                <div class="djv3-footer__brand">
                    <div class="djv3-footer__brand-name">DEJOIY</div>
                    <p class="djv3-footer__brand-desc">India's next-generation digital marketplace. One platform. Many worlds.</p>
                </div>
                <div class="djv3-footer__col">
                    <h4>Shop</h4>
                    <a href="<?php echo esc_url(home_url('/shop/')); ?>">Marketplace</a>
                    <a href="<?php echo esc_url(home_url('/all-categories/')); ?>">Categories</a>
                    <a href="<?php echo esc_url(home_url('/dejoiy-festival-sale/')); ?>">Deals</a>
                    <a href="<?php echo esc_url(home_url('/dejoiy-refurbished/')); ?>">Renew</a>
                </div>
                <div class="djv3-footer__col">
                    <h4>Explore</h4>
                    <a href="<?php echo esc_url(home_url('/dejoiy-library/?dejoiy_library=1')); ?>">Nexus</a>
                    <a href="<?php echo esc_url(home_url('/dejoiy-custom-studio/')); ?>">Custom Studio</a>
                    <a href="<?php echo esc_url(home_url('/dejoiy-quick-mart/')); ?>">QuickMart</a>
                    <a href="<?php echo esc_url(home_url('/dejoiy-services/')); ?>">Services</a>
                </div>
                <div class="djv3-footer__col">
                    <h4>Support</h4>
                    <a href="<?php echo esc_url(home_url('/contact/')); ?>">Contact Us</a>
                    <a href="<?php echo esc_url(home_url('/my-account/orders/')); ?>">Track Orders</a>
                    <a href="<?php echo esc_url(home_url('/my-account/')); ?>">My Account</a>
                    <a href="<?php echo esc_url(home_url('/faq/')); ?>">FAQ</a>
                </div>
                <div class="djv3-footer__col">
                    <h4>Business</h4>
                    <a href="<?php echo esc_url(home_url('/sell-on-dejoiy/')); ?>">Become a Seller</a>
                    <a href="<?php echo esc_url(home_url('/vendor-register/')); ?>">Vendor Registration</a>
                    <a href="<?php echo esc_url(home_url('/dejoiy-services/')); ?>">Offer Services</a>
                </div>
            </div>
            <div class="djv3-footer__bottom">
                <span>© <?php echo date('Y'); ?> DEJOIY. All rights reserved.</span>
                <div class="djv3-footer__social">
                    <a href="#" aria-label="Instagram">📷</a>
                    <a href="#" aria-label="Twitter">🐦</a>
                    <a href="#" aria-label="YouTube">📺</a>
                    <a href="#" aria-label="LinkedIn">💼</a>
                </div>
            </div>
        </div>
    </footer>
    <?php
}

function dejoiy_v3_render_bottom_nav() {
    ?>
    <nav class="djv3-bottom-nav" id="djv3-bottom-nav" aria-label="Mobile navigation">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="djv3-bottom-nav__item is-active">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Home
        </a>
        <a href="<?php echo esc_url(home_url('/all-categories/')); ?>" class="djv3-bottom-nav__item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            Discover
        </a>
        <a href="<?php echo esc_url(home_url('/?joi=1')); ?>" class="djv3-bottom-nav__item djv3-bottom-nav__item--joi">
            <span class="djv3-bottom-nav__icon">✨</span>
            JOI
        </a>
        <a href="<?php echo esc_url(home_url('/my-account/wishlist/')); ?>" class="djv3-bottom-nav__item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            Favorites
        </a>
        <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="djv3-bottom-nav__item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            Cart
        </a>
    </nav>
    <?php
}

function dejoiy_v3_render_js() {
    ?>
    <script>
    (function(){
        var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        /* Mega menu toggle */
        var btn = document.getElementById('djv3-browse-btn');
        var mega = document.getElementById('djv3-mega');
        if(btn && mega) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                var open = mega.classList.toggle('is-open');
                btn.setAttribute('aria-expanded', open);
            });
            document.addEventListener('click', function(e) {
                if(!mega.contains(e.target) && e.target !== btn) {
                    mega.classList.remove('is-open');
                    btn.setAttribute('aria-expanded', 'false');
                }
            });
        }
        /* Scroll reveal */
        if(!reduce) {
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if(entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, {threshold: 0.1});
            document.querySelectorAll('.djv3-reveal').forEach(function(el) {
                observer.observe(el);
            });
        } else {
            document.querySelectorAll('.djv3-reveal').forEach(function(el) {
                el.classList.add('is-visible');
            });
        }
        /* Sticky header shadow */
        var header = document.getElementById('djv3-header');
        if(header) {
            window.addEventListener('scroll', function() {
                header.style.boxShadow = window.scrollY > 10 ? '0 2px 12px rgba(0,0,0,0.1)' : 'none';
            }, {passive: true});
        }
        /* Search suggestions */
        var searchInputs = document.querySelectorAll('.djv3-search__input, .djv3-joi__input');
        searchInputs.forEach(function(input) {
            input.addEventListener('focus', function() {
                this.closest('.djv3-search__form, .djv3-joi__box').style.borderColor = 'var(--djv3-blue)';
            });
            input.addEventListener('blur', function() {
                this.closest('.djv3-search__form, .djv3-joi__box').style.borderColor = '';
            });
        });
    })();
    </script>
    <?php
}
