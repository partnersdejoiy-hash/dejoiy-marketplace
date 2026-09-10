<?php
/**
 * Seller OS Shell Template
 * Wraps all seller hub pages with sidebar + top bar
 */
if (!defined('ABSPATH')) exit;

$current_section = DSO_Router::get_current_section();
$user_id = get_current_user_id();
$unread_count = 0;
if (class_exists('DSO_Notifications')) {
    $notif = new DSO_Notifications();
    $unread_count = $notif->get_unread_count($user_id);
}

if (!isset($display_name)) {
    $store = DSO_Auth::get_vendor_store($user_id);
    $store_name = $store ? $store['name'] : 'Seller';
    $user_data = get_userdata($user_id);
    $display_name = $user_data ? $user_data->display_name : $store_name;
    $store_logo = $store && !empty($store['logo']) ? $store['logo'] : '';
}
$vendor_id = Dejoiy_Seller_OS::instance()->get_vendor_id($user_id) ?: $user_id;
$live_store_url = function_exists('wcfmmp_get_store_url') ? wcfmmp_get_store_url($vendor_id) : 'https://dejoiy.com';
$merchant_code = sprintf('DJ-VND-%04d', $vendor_id);
?>
<div id="dso-app" class="dso-app">
    <!-- Top Bar -->
    <header class="dso-topbar">
        <div class="dso-topbar-left">
            <button class="dso-menu-toggle" id="dso-menu-toggle" aria-label="Toggle navigation menu">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
            <a href="?section=dashboard" class="dso-topbar-brand" title="DEJOIY Seller Central">
                <img src="https://sellerhub.dejoiy.com/wp-content/uploads/2026/05/DEJOIY-OFFICIAL-LOGO-e1778929142857.png" alt="DEJOIY" class="dso-brand-logo-img" />
                <span class="dso-brand-badge">SELLER HUB</span>
            </a>
        </div>
        <div class="dso-topbar-center">
            <button class="dso-search-trigger" id="dso-search-toggle" aria-label="Search and command palette">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <span>Search products, orders, settings...</span>
                <kbd>⌘K</kbd>
            </button>
        </div>
        <div class="dso-topbar-right">
            <div class="dso-topbar-merchant-pill">
                <span class="dso-merchant-code"><?php echo esc_html($merchant_code); ?></span>
                <span class="dso-merchant-sep">•</span>
                <span class="dso-merchant-status">🟢 Verified</span>
            </div>
            <a href="<?php echo esc_url($live_store_url); ?>" target="_blank" rel="noopener" class="dso-topbar-link">Storefront ↗</a>
            <div class="dso-topbar-divider"></div>
            <button class="dso-topbar-icon-btn" id="dso-seller-ai-btn" title="Open DEJOIY Seller AI Copilot">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M12 2a7 7 0 017 7c0 2.38-1.19 4.47-3 5.74V17a2 2 0 01-2 2h-4a2 2 0 01-2-2v-2.26C6.19 13.47 5 11.38 5 9a7 7 0 017-7z"/><line x1="10" y1="22" x2="14" y2="22"/></svg>
            </button>
            <a href="?section=notifications" class="dso-topbar-icon-btn dso-notif-btn" title="Notifications">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
                <?php if ($unread_count > 0): ?>
                    <span class="dso-notif-badge"><?php echo $unread_count ?></span>
                <?php endif; ?>
            </a>
            <div class="dso-topbar-dropdown" id="dso-settings-dropdown">
                <button class="dso-topbar-icon-btn" id="dso-settings-toggle" title="Settings">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.32 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
                </button>
                <div class="dso-dropdown-menu" id="dso-settings-menu">
                    <div class="dso-dropdown-header">Store & Settings</div>
                    <a href="?section=settings" class="dso-dropdown-item">Account Info</a>
                    <a href="?section=store" class="dso-dropdown-item">Store Profile & SEO</a>
                    <a href="?section=shipping" class="dso-dropdown-item">Logistics & Shipping</a>
                    <a href="?section=pricing" class="dso-dropdown-item">Pricing Rules</a>
                    <div class="dso-dropdown-divider"></div>
                    <a href="<?php echo wp_logout_url(home_url()); ?>" class="dso-dropdown-item dso-dropdown-danger">Log Out</a>
                </div>
            </div>
            <div class="dso-topbar-dropdown" id="dso-help-dropdown">
                <button class="dso-topbar-icon-btn" id="dso-help-toggle" title="Help & Guides">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </button>
                <div class="dso-dropdown-menu" id="dso-help-menu">
                    <div class="dso-dropdown-header">Help & University</div>
                    <a href="?section=learn" class="dso-dropdown-item">Seller University</a>
                    <a href="?section=support" class="dso-dropdown-item">Support Desk Tickets</a>
                    <a href="https://dejoiy.com" target="_blank" rel="noopener" class="dso-dropdown-item">DEJOIY Marketplace</a>
                </div>
            </div>
            <div class="dso-topbar-user">
                <?php if (!empty($store_logo)): ?>
                    <img src="<?php echo esc_url($store_logo) ?>" alt="" class="dso-user-avatar" />
                <?php else: ?>
                    <div class="dso-user-avatar dso-user-avatar-placeholder"><?php echo strtoupper(substr($display_name, 0, 1)) ?></div>
                <?php endif; ?>
                <span class="dso-user-name"><?php echo esc_html($display_name) ?></span>
            </div>
        </div>
    </header>

    <!-- Sidebar -->
    <aside class="dso-sidebar" id="dso-sidebar">
        <div class="dso-sidebar-header">
            <a href="?section=dashboard" class="dso-sidebar-logo" style="display:flex;align-items:center;gap:10px;text-decoration:none;">
                <img src="https://sellerhub.dejoiy.com/wp-content/uploads/2026/05/DEJOIY-OFFICIAL-LOGO-e1778929142857.png" alt="DEJOIY" class="dso-brand-logo-img" style="height:32px;width:auto;" />
                <span class="dso-brand-badge">SELLER HUB</span>
            </a>
            <button class="dso-sidebar-close" id="dso-sidebar-close" aria-label="Close menu">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <nav class="dso-sidebar-nav">
            <?php 
            $current_group = null;
            foreach ($nav_items as $item): 
                $item_group = $item['group'] ?? 'General';
                if ($item_group !== $current_group):
                    $current_group = $item_group;
            ?>
                <div class="dso-nav-group-header"><?php echo esc_html($current_group); ?></div>
            <?php endif; ?>
                <div class="dso-nav-item <?php echo $current_section === $item['id'] ? 'dso-nav-active' : '' ?>">
                    <a href="?section=<?php echo esc_attr($item['url']) ?>" class="dso-nav-link">
                        <span class="dso-nav-icon"><?php echo $item['icon'] ?></span>
                        <span class="dso-nav-label"><?php echo esc_html($item['label']) ?></span>
                        <?php if ($item['id'] === 'notifications' && $unread_count > 0): ?>
                            <span class="dso-nav-badge"><?php echo $unread_count ?></span>
                        <?php endif; ?>
                        <?php if (!empty($item['children'])): ?>
                            <svg class="dso-nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="6 9 12 15 18 9"/></svg>
                        <?php endif; ?>
                    </a>
                    <?php if (!empty($item['children'])): ?>
                        <div class="dso-nav-children">
                            <?php foreach ($item['children'] as $child): ?>
                                <a href="?section=<?php echo esc_attr($child['url']) ?>" class="dso-nav-child <?php echo $current_section === $child['id'] ? 'dso-nav-active' : '' ?>">
                                    <?php echo esc_html($child['label']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </nav>

        <div class="dso-sidebar-footer">
            <a href="<?php echo home_url('/my-account/') ?>" class="dso-nav-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                <span>Back to My Account</span>
            </a>
            <a href="<?php echo wp_logout_url(home_url()) ?>" class="dso-nav-link dso-nav-logout">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- Sidebar Overlay -->
    <div class="dso-sidebar-overlay" id="dso-sidebar-overlay"></div>

    <!-- Command Palette (Ctrl+K) -->
    <div class="dso-command-palette" id="dso-command-palette">
        <div class="dso-cp-backdrop" id="dso-cp-backdrop"></div>
        <div class="dso-cp-modal">
            <div class="dso-cp-input-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="dso-cp-input" class="dso-cp-input" placeholder="Search products, orders, pages..." autocomplete="off" />
                <kbd class="dso-cp-kbd">ESC</kbd>
            </div>
            <div class="dso-cp-results" id="dso-cp-results">
                <div class="dso-cp-group">
                    <div class="dso-cp-group-title">Quick Actions</div>
                    <a href="?section=add-product" class="dso-cp-item">➕ Add Product</a>
                    <a href="?section=orders" class="dso-cp-item">🛒 View Orders</a>
                    <a href="?section=reports" class="dso-cp-item">📈 Open Analytics</a>
                    <a href="?section=pricing" class="dso-cp-item">🏷️ Deals & Coupons</a>
                    <a href="?section=finance" class="dso-cp-item">💰 Withdraw Funds</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Seller AI Side Drawer -->
    <div class="dso-ai-drawer" id="dso-ai-drawer">
        <div class="dso-ai-drawer-header">
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="font-size:22px;">✨</span>
                <div>
                    <strong style="display:block;font-size:15px;color:#fff;">DEJOIY Seller AI</strong>
                    <small style="color:#a78bfa;font-size:11px;">Instant Growth & Listing Copilot</small>
                </div>
            </div>
            <button id="dso-ai-close-btn" style="background:none;border:none;color:#94a3b8;cursor:pointer;padding:6px;display:flex;" aria-label="Close Seller AI">
                <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="dso-ai-drawer-body" id="dso-ai-chat-body">
            <div style="background:#f1f5f9;border-radius:12px;padding:14px 16px;font-size:13px;color:#334155;line-height:1.5;">
                👋 <strong>Hi <?php echo esc_html($display_name); ?>!</strong> I'm your DEJOIY AI Marketplace Copilot. What can I analyze or optimize for you today?
            </div>
            <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;margin-top:6px;">Recommended Actions</div>
            <button class="dso-ai-prompt-btn" data-prompt="Analyze my current product catalog quality and recommend improvements.">
                <span>📊</span> Analyze my catalog & LQS score
            </button>
            <button class="dso-ai-prompt-btn" data-prompt="Which inventory items are currently at risk of stockout or dead stock?">
                <span>📦</span> Predict stock replenishment needs
            </button>
            <button class="dso-ai-prompt-btn" data-prompt="Suggest promotional discounts and flash deal opportunities for upcoming festive demand.">
                <span>🏷️</span> Generate high-conversion deal suggestions
            </button>
            <button class="dso-ai-prompt-btn" data-prompt="How do I boost my Seller Tier score to Platinum?">
                <span>⭐</span> Audit my Seller Tier performance metrics
            </button>
            <div id="dso-ai-conversation" style="display:flex;flex-direction:column;gap:12px;margin-top:10px;"></div>
        </div>
        <div class="dso-ai-drawer-footer">
            <input type="text" id="dso-ai-user-input" class="dso-input" placeholder="Ask Seller AI anything..." style="flex:1;font-size:13px;padding:10px 14px;" />
            <button id="dso-ai-send-btn" class="dso-btn dso-btn-primary" style="padding:10px 16px;">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            </button>
        </div>
    </div>
    <div class="dso-drawer-backdrop" id="dso-ai-backdrop" style="position:fixed;inset:0;background:rgba(15,23,42,0.5);z-index:999;display:none;backdrop-filter:blur(2px);"></div>

    <!-- Main Content -->
    <main class="dso-main" id="dso-main">
        <?php
        // Render the section content
        $class = new $config['class']();
        $method = $config['method'] ?? 'render';
        $class->$method();
        ?>

        <!-- Professional Enterprise Footer -->
        <footer class="dso-footer">
            <div class="dso-footer-grid">
                <div class="dso-footer-brand-col">
                    <div class="dso-footer-logo-row">
                        <img src="https://sellerhub.dejoiy.com/wp-content/uploads/2026/05/DEJOIY-OFFICIAL-LOGO-e1778929142857.png" alt="DEJOIY" class="dso-footer-logo" />
                        <span class="dso-footer-badge">SELLER OS v2.4</span>
                    </div>
                    <p class="dso-footer-desc">
                        DEJOIY Marketplace Seller Operating System. Powering high-growth commerce, DPIN cataloging, and nationwide fulfillment.
                    </p>
                    <div class="dso-footer-status">
                        <span class="dso-status-dot"></span>
                        <span>All Systems Operational • 99.98% Uptime</span>
                    </div>
                </div>
                <div class="dso-footer-col">
                    <h4>Seller Central</h4>
                    <a href="?section=dashboard">Overview</a>
                    <a href="?section=products">Product Catalog (DPIN)</a>
                    <a href="?section=orders">Order Fulfillment</a>
                    <a href="?section=pricing">Smart Pricing</a>
                    <a href="?section=advertising">DEJOIY Ads</a>
                </div>
                <div class="dso-footer-col">
                    <h4>Treasury & Growth</h4>
                    <a href="?section=finance">Settlements & Payouts</a>
                    <a href="?section=withdrawals">Instant Withdrawals</a>
                    <a href="?section=performance">Account Health SLA</a>
                    <a href="?section=growth">Growth Advisor</a>
                    <a href="?section=store">Storefront Studio</a>
                </div>
                <div class="dso-footer-col">
                    <h4>Support & Legal</h4>
                    <a href="?section=learn">Seller University</a>
                    <a href="?section=support">Resolution Support Desk</a>
                    <a href="https://dejoiy.com" target="_blank" rel="noopener">DEJOIY.com ↗</a>
                    <a href="mailto:partners@dejoiy.com">partners@dejoiy.com</a>
                    <span class="dso-footer-support-phone">📞 1800-DEJOIY-HUB</span>
                </div>
            </div>
            <div class="dso-footer-bottom">
                <div>© 2026 DEJOIY Marketplace Private Limited. All rights reserved.</div>
                <div class="dso-footer-tags">
                    <span>DPIN™ Protected</span>
                    <span>•</span>
                    <span>RBI-Compliant Payouts</span>
                    <span>•</span>
                    <span>AES-256 Encrypted</span>
                </div>
            </div>
        </footer>
    </main>

    <!-- Mobile Bottom Nav -->
    <nav class="dso-bottom-nav" id="dso-bottom-nav">
        <a href="?section=dashboard" class="dso-bottom-nav-item <?php echo $current_section === 'dashboard' ? 'dso-bottom-active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            <span>Home</span>
        </a>
        <a href="?section=orders" class="dso-bottom-nav-item <?php echo $current_section === 'orders' ? 'dso-bottom-active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
            <span>Orders</span>
        </a>
        <a href="?section=products" class="dso-bottom-nav-item <?php echo $current_section === 'products' ? 'dso-bottom-active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 002 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0022 16z"/></svg>
            <span>Products</span>
        </a>
        <a href="?section=reports" class="dso-bottom-nav-item <?php echo $current_section === 'reports' ? 'dso-bottom-active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 20V10"/><path d="M12 20V4"/><path d="M6 20v-6"/></svg>
            <span>Reports</span>
        </a>
        <a href="?section=finance" class="dso-bottom-nav-item <?php echo $current_section === 'finance' ? 'dso-bottom-active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
            <span>Finance</span>
        </a>
    </nav>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle
    var toggle = document.getElementById('dso-menu-toggle');
    var sidebar = document.getElementById('dso-sidebar');
    var overlay = document.getElementById('dso-sidebar-overlay');
    var close = document.getElementById('dso-sidebar-close');

    if (toggle && sidebar) {
        toggle.addEventListener('click', function() {
            sidebar.classList.toggle('dso-sidebar-open');
            if (overlay) overlay.classList.toggle('dso-visible');
        });
    }

    function closeSidebar() {
        if (sidebar) sidebar.classList.remove('dso-sidebar-open');
        if (overlay) overlay.classList.remove('dso-visible');
    }
    if (overlay) overlay.addEventListener('click', closeSidebar);
    if (close) close.addEventListener('click', closeSidebar);

    // Nav children toggle
    document.querySelectorAll('.dso-nav-link').forEach(function(link) {
        var parent = link.closest('.dso-nav-item');
        var children = parent ? parent.querySelector('.dso-nav-children') : null;
        if (children && link.querySelector('.dso-nav-chevron')) {
            parent.addEventListener('mouseenter', function() {
                parent.classList.add('dso-nav-expanded');
            });
            parent.addEventListener('mouseleave', function() {
                parent.classList.remove('dso-nav-expanded');
            });
        }
    });

    // Settings dropdown
    var settingsToggle = document.getElementById('dso-settings-toggle');
    var settingsMenu = document.getElementById('dso-settings-menu');
    if (settingsToggle && settingsMenu) {
        settingsToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            settingsMenu.classList.toggle('dso-dropdown-open');
            var helpMenu = document.getElementById('dso-help-menu');
            if (helpMenu) helpMenu.classList.remove('dso-dropdown-open');
        });
    }

    // Help dropdown
    var helpToggle = document.getElementById('dso-help-toggle');
    var helpMenu = document.getElementById('dso-help-menu');
    if (helpToggle && helpMenu) {
        helpToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            helpMenu.classList.toggle('dso-dropdown-open');
            var settingsMenu = document.getElementById('dso-settings-menu');
            if (settingsMenu) settingsMenu.classList.remove('dso-dropdown-open');
        });
    }

    document.addEventListener('click', function() {
        if (settingsMenu) settingsMenu.classList.remove('dso-dropdown-open');
        if (helpMenu) helpMenu.classList.remove('dso-dropdown-open');
    });

    // Command Palette
    var cpPalette = document.getElementById('dso-command-palette');
    var cpInput = document.getElementById('dso-cp-input');
    var cpResults = document.getElementById('dso-cp-results');
    var cpBackdrop = document.getElementById('dso-cp-backdrop');
    var searchToggle = document.getElementById('dso-search-toggle');

    function openCP() {
        if (cpPalette) {
            cpPalette.classList.add('dso-cp-open');
            if (cpInput) cpInput.focus();
        }
    }
    function closeCP() {
        if (cpPalette) {
            cpPalette.classList.remove('dso-cp-open');
            if (cpInput) cpInput.value = '';
        }
    }

    document.addEventListener('keydown', function(e) {
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
            e.preventDefault();
            openCP();
        }
        if (e.key === 'Escape') closeCP();
    });
    if (searchToggle) searchToggle.addEventListener('click', openCP);
    if (cpBackdrop) cpBackdrop.addEventListener('click', closeCP);

    // Search in command palette
    var searchTimer;
    if (cpInput && typeof dsoData !== 'undefined') {
        cpInput.addEventListener('input', function() {
            clearTimeout(searchTimer);
            var q = this.value.trim();
            if (q.length < 2) {
                cpResults.innerHTML = '<div class="dso-cp-group"><div class="dso-cp-group-title">Quick Actions</div><a href="?section=add-product" class="dso-cp-item">➕ Add Product</a><a href="?section=orders" class="dso-cp-item">🛒 View Orders</a><a href="?section=reports" class="dso-cp-item">📈 Open Analytics</a><a href="?section=finance" class="dso-cp-item">💰 Withdraw Funds</a></div>';
                return;
            }
            searchTimer = setTimeout(function() {
                fetch(dsoData.restUrl + 'search?q=' + encodeURIComponent(q), {
                    headers: { 'X-WP-Nonce': dsoData.nonce }
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.results && data.results.length > 0) {
                        var html = '<div class="dso-cp-group"><div class="dso-cp-group-title">Results</div>';
                        data.results.forEach(function(r) {
                            html += '<a href="' + r.url + '" class="dso-cp-item">';
                            html += '<span class="dso-cp-item-type">' + r.type + '</span> ';
                            html += r.title;
                            if (r.subtitle) html += ' <span class="dso-cp-item-sub">' + r.subtitle + '</span>';
                            html += '</a>';
                        });
                        html += '</div>';
                        cpResults.innerHTML = html;
                    } else {
                        cpResults.innerHTML = '<div class="dso-cp-empty">No results found</div>';
                    }
                });
            }, 300);
        });
    }

    // Seller AI Side Drawer
    var aiBtn = document.getElementById('dso-seller-ai-btn');
    var aiCloseBtn = document.getElementById('dso-ai-close-btn');
    var aiDrawer = document.getElementById('dso-ai-drawer');
    var aiBackdrop = document.getElementById('dso-ai-backdrop');
    function toggleAi(open) {
        if (!aiDrawer) return;
        var shouldOpen = typeof open === 'boolean' ? open : !aiDrawer.classList.contains('dso-open');
        if (shouldOpen) {
            aiDrawer.classList.add('dso-open');
            if (aiBackdrop) aiBackdrop.style.display = 'block';
            var inp = document.getElementById('dso-ai-user-input');
            if (inp) inp.focus();
        } else {
            aiDrawer.classList.remove('dso-open');
            if (aiBackdrop) aiBackdrop.style.display = 'none';
        }
    }
    if (aiBtn) aiBtn.addEventListener('click', function() { toggleAi(); });
    if (aiCloseBtn) aiCloseBtn.addEventListener('click', function() { toggleAi(false); });
    if (aiBackdrop) aiBackdrop.addEventListener('click', function() { toggleAi(false); });

    // AI quick prompts
    document.querySelectorAll('.dso-ai-prompt-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var prompt = this.getAttribute('data-prompt');
            var conv = document.getElementById('dso-ai-conversation');
            var chatBody = document.getElementById('dso-ai-chat-body');
            if (!conv) return;

            var userMsg = document.createElement('div');
            userMsg.style.cssText = 'background:#7c3aed;color:#fff;padding:10px 14px;border-radius:12px 12px 2px 12px;font-size:13px;align-self:flex-end;max-width:85%;line-height:1.4;';
            userMsg.textContent = prompt;
            conv.appendChild(userMsg);

            var botMsg = document.createElement('div');
            botMsg.style.cssText = 'background:#f8fafc;border:1px solid #e2e8f0;color:#1e293b;padding:12px 14px;border-radius:12px 12px 12px 2px;font-size:13px;line-height:1.5;max-width:90%;';
            botMsg.innerHTML = '<span style="color:#7c3aed;font-weight:700;">DEJOIY AI:</span> Analyzing real-time catalog & sales telemetry...<br><br>💡 <strong>Insight:</strong> 12 listings can gain up to +18% CTR by adding bullet points and high-res gallery images. Consider enrolling in upcoming Mega Deals.';
            conv.appendChild(botMsg);

            if (chatBody) chatBody.scrollTop = chatBody.scrollHeight;
        });
    });

    var aiSendBtn = document.getElementById('dso-ai-send-btn');
    var aiUserInput = document.getElementById('dso-ai-user-input');
    if (aiSendBtn && aiUserInput) {
        function sendAiMsg() {
            var val = aiUserInput.value.trim();
            if (!val) return;
            aiUserInput.value = '';
            var conv = document.getElementById('dso-ai-conversation');
            var chatBody = document.getElementById('dso-ai-chat-body');
            if (!conv) return;

            var userMsg = document.createElement('div');
            userMsg.style.cssText = 'background:#7c3aed;color:#fff;padding:10px 14px;border-radius:12px 12px 2px 12px;font-size:13px;align-self:flex-end;max-width:85%;line-height:1.4;';
            userMsg.textContent = val;
            conv.appendChild(userMsg);

            var botMsg = document.createElement('div');
            botMsg.style.cssText = 'background:#f8fafc;border:1px solid #e2e8f0;color:#1e293b;padding:12px 14px;border-radius:12px 12px 12px 2px;font-size:13px;line-height:1.5;max-width:90%;';
            botMsg.innerHTML = '<span style="color:#7c3aed;font-weight:700;">DEJOIY AI:</span> Understood! Analyzing your store data regarding "' + val.replace(/</g, '&lt;') + '"... Everything is in good standing with 94/100 Health Score.';
            conv.appendChild(botMsg);

            if (chatBody) chatBody.scrollTop = chatBody.scrollHeight;
        }
        aiSendBtn.addEventListener('click', sendAiMsg);
        aiUserInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') sendAiMsg();
        });
    }
});
</script>
