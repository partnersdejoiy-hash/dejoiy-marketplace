<?php
/**
 * Seller OS Shell Template
 * Wraps all seller hub pages with sidebar + top bar
 */
if (!defined('ABSPATH')) exit;

$current_section = DSO_Router::get_current_section();
$unread_count = 0;
if (class_exists('DSO_Notifications')) {
    $notif = new DSO_Notifications();
    $unread_count = $notif->get_unread_count($user_id);
}
?>
<div id="dso-app" class="dso-app">
    <!-- Mobile Top Bar -->
    <div class="dso-topbar">
        <button class="dso-menu-toggle" id="dso-menu-toggle" aria-label="Toggle menu">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <div class="dso-topbar-title">
            <span class="dso-logo-text">🏪 Seller Hub</span>
        </div>
        <div class="dso-topbar-actions">
            <button class="dso-search-toggle" id="dso-search-toggle" aria-label="Search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </button>
            <a href="?section=notifications" class="dso-topbar-notif">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
                <?php if ($unread_count > 0): ?>
                    <span class="dso-notif-badge"><?php echo $unread_count ?></span>
                <?php endif; ?>
            </a>
        </div>
    </div>

    <!-- Sidebar -->
    <aside class="dso-sidebar" id="dso-sidebar">
        <div class="dso-sidebar-header">
            <a href="?section=dashboard" class="dso-sidebar-logo">
                <span class="dso-logo-icon">🏪</span>
                <span class="dso-logo-text">Seller Hub</span>
            </a>
            <button class="dso-sidebar-close" id="dso-sidebar-close" aria-label="Close menu">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <nav class="dso-sidebar-nav">
            <?php foreach ($nav_items as $item): ?>
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

    <!-- Main Content -->
    <main class="dso-main" id="dso-main">
        <?php
        // Render the section content
        $class = new $config['class']();
        $method = $config['method'] ?? 'render';
        $class->$method();
        ?>
    </main>

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
                    <a href="?section=analytics" class="dso-cp-item">📈 Open Analytics</a>
                    <a href="?section=finance" class="dso-cp-item">💰 Withdraw Funds</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle
    var toggle = document.getElementById('dso-menu-toggle');
    var sidebar = document.getElementById('dso-sidebar');
    var overlay = document.getElementById('dso-sidebar-overlay');
    var close = document.getElementById('dso-sidebar-close');

    if (toggle) toggle.addEventListener('click', function() {
        sidebar.classList.toggle('dso-sidebar-open');
        overlay.classList.toggle('dso-visible');
    });

    function closeSidebar() {
        sidebar.classList.remove('dso-sidebar-open');
        overlay.classList.remove('dso-visible');
    }
    if (overlay) overlay.addEventListener('click', closeSidebar);
    if (close) close.addEventListener('click', closeSidebar);

    // Nav children toggle
    document.querySelectorAll('.dso-nav-link').forEach(function(link) {
        var parent = link.closest('.dso-nav-item');
        var children = parent ? parent.querySelector('.dso-nav-children') : null;
        if (children && link.querySelector('.dso-nav-chevron')) {
            link.addEventListener('click', function(e) {
                if (parent.classList.contains('dso-nav-expanded')) return;
                // Let the link navigate
            });
            // Expand on hover
            parent.addEventListener('mouseenter', function() {
                parent.classList.add('dso-nav-expanded');
            });
            parent.addEventListener('mouseleave', function() {
                parent.classList.remove('dso-nav-expanded');
            });
        }
    });

    // Command Palette
    var cpPalette = document.getElementById('dso-command-palette');
    var cpInput = document.getElementById('dso-cp-input');
    var cpResults = document.getElementById('dso-cp-results');
    var cpBackdrop = document.getElementById('dso-cp-backdrop');
    var searchToggle = document.getElementById('dso-search-toggle');

    function openCP() {
        cpPalette.classList.add('dso-cp-open');
        cpInput.focus();
    }
    function closeCP() {
        cpPalette.classList.remove('dso-cp-open');
        cpInput.value = '';
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
    cpInput.addEventListener('input', function() {
        clearTimeout(searchTimer);
        var q = this.value.trim();
        if (q.length < 2) {
            cpResults.innerHTML = '<div class="dso-cp-group"><div class="dso-cp-group-title">Quick Actions</div><a href="?section=add-product" class="dso-cp-item">➕ Add Product</a><a href="?section=orders" class="dso-cp-item">🛒 View Orders</a><a href="?section=analytics" class="dso-cp-item">📈 Open Analytics</a><a href="?section=finance" class="dso-cp-item">💰 Withdraw Funds</a></div>';
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
});
</script>
