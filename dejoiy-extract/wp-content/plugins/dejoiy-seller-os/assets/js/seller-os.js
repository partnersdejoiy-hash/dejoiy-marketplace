/**
 * DEJOIY Seller Hub - Production JavaScript
 * @version 2.0.0
 */
(function(window, document) {
    'use strict';

    var DSO = window.DSO = {
        state: {
            sidebarOpen: false,
            searchOpen: false,
            commandOpen: false,
            settingsOpen: false,
            helpOpen: false,
            favoritesOpen: false,
            charts: {},
            searchTimeout: null,
            refreshTimer: null,
            recentSearches: [],
            selectedProducts: {},
            selectedCount: 0,
            searchIndex: -1,
            commandIndex: -1
        },

        config: {
            apiBase: '',
            ajaxUrl: '',
            nonce: '',
            vendorId: 0,
            userId: 0,
            baseUrl: '/',
            currency: 'USD',
            searchDebounce: 300,
            refreshInterval: 60000,
            maxRecent: 10
        },

        init: function() {
            this.loadConfig();
            this.loadRecentSearches();
            this.initSidebar();
            this.initNavChildren();
            this.initSearch();
            this.initCommandPalette();
            this.initSettings();
            this.initHelp();
            this.initFavorites();
            this.initKeyboardShortcuts();
            this.initAutoRefresh();
            this.initResponsive();
            this.initSmoothScroll();
            this.initTableSort();
        },

        loadConfig: function() {
            if (typeof dsoData === 'undefined') return;
            var c = this.config;
            c.apiBase = dsoData.restUrl || '/wp-json/dejoiy-seller-os/v1';
            c.ajaxUrl = dsoData.ajaxUrl || '';
            c.nonce = dsoData.nonce || '';
            c.vendorId = dsoData.vendorId || 0;
            c.userId = dsoData.userId || 0;
            c.baseUrl = dsoData.baseUrl || '/';
            c.currency = dsoData.currency || 'USD';
        },

        // ─── API Helper ────────────────────────────────────
        api: function(endpoint, opts) {
            var url = this.config.apiBase + endpoint;
            var defaults = {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': this.config.nonce
                }
            };
            var options = Object.assign({}, defaults, opts || {});
            return fetch(url, options).then(function(res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            });
        },

        // ─── 1. Sidebar Toggle ─────────────────────────────
        initSidebar: function() {
            var self = this;
            document.querySelectorAll('[data-dso-toggle="sidebar"]').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    self.toggleSidebar();
                });
            });
            var overlay = document.getElementById('dso-sidebar-overlay');
            if (overlay) {
                overlay.addEventListener('click', function() { self.closeSidebar(); });
            }
            var sidebar = document.getElementById('dso-sidebar');
            if (sidebar) {
                sidebar.querySelectorAll('.nav-item > a').forEach(function(link) {
                    link.addEventListener('click', function() {
                        if (window.innerWidth < 1024) self.closeSidebar();
                    });
                });
            }
        },

        toggleSidebar: function() {
            this.state.sidebarOpen ? this.closeSidebar() : this.openSidebar();
        },

        openSidebar: function() {
            var sidebar = document.getElementById('dso-sidebar');
            var overlay = document.getElementById('dso-sidebar-overlay');
            if (sidebar) sidebar.classList.add('open');
            if (overlay) overlay.classList.add('active');
            document.body.classList.add('dso-sidebar-open');
            this.state.sidebarOpen = true;
        },

        closeSidebar: function() {
            var sidebar = document.getElementById('dso-sidebar');
            var overlay = document.getElementById('dso-sidebar-overlay');
            if (sidebar) sidebar.classList.remove('open');
            if (overlay) overlay.classList.remove('active');
            document.body.classList.remove('dso-sidebar-open');
            this.state.sidebarOpen = false;
        },

        // ─── 2. Nav Children Expand ────────────────────────
        initNavChildren: function() {
            var self = this;
            document.querySelectorAll('.nav-item.has-children').forEach(function(item) {
                var link = item.querySelector(':scope > a');
                if (link) {
                    link.addEventListener('click', function(e) {
                        if (window.innerWidth < 1024) {
                            e.preventDefault();
                            self.toggleNavItem(item);
                        }
                    });
                }
                item.addEventListener('mouseenter', function() {
                    if (window.innerWidth >= 1024) item.classList.add('expanded');
                });
                item.addEventListener('mouseleave', function() {
                    if (window.innerWidth >= 1024) item.classList.remove('expanded');
                });
            });
        },

        toggleNavItem: function(item) {
            var wasExpanded = item.classList.contains('expanded');
            var siblings = item.parentElement.querySelectorAll('.nav-item.has-children.expanded');
            for (var i = 0; i < siblings.length; i++) siblings[i].classList.remove('expanded');
            if (!wasExpanded) item.classList.add('expanded');
        },

        // ─── 3. Universal Search ───────────────────────────
        initSearch: function() {
            var self = this;
            document.querySelectorAll('[data-dso-toggle="search"]').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    self.openSearch();
                });
            });
            var overlay = document.getElementById('dso-search-overlay');
            if (overlay) {
                overlay.addEventListener('click', function(e) {
                    if (e.target === overlay) self.closeSearch();
                });
            }
            var closeBtn = document.getElementById('dso-search-close');
            if (closeBtn) closeBtn.addEventListener('click', function() { self.closeSearch(); });
            var input = document.getElementById('dso-search-input');
            if (input) {
                input.addEventListener('input', function() { self.onSearchInput(this.value); });
                input.addEventListener('keydown', function(e) { self.onSearchKeydown(e); });
            }
            this.renderRecentSearches();
        },

        openSearch: function() {
            var overlay = document.getElementById('dso-search-overlay');
            var input = document.getElementById('dso-search-input');
            if (overlay) overlay.classList.add('active');
            if (input) { input.value = ''; input.focus(); }
            this.state.searchOpen = true;
            this.state.searchIndex = -1;
            this.renderRecentSearches();
            document.body.classList.add('dso-overlay-open');
        },

        closeSearch: function() {
            var overlay = document.getElementById('dso-search-overlay');
            if (overlay) overlay.classList.remove('active');
            this.state.searchOpen = false;
            this.state.searchIndex = -1;
            document.body.classList.remove('dso-overlay-open');
        },

        onSearchInput: function(query) {
            var self = this;
            clearTimeout(this.state.searchTimeout);
            if (!query) { this.renderRecentSearches(); return; }
            if (query.length < 2) return;
            this.showSearchLoading();
            this.state.searchTimeout = setTimeout(function() {
                self.fetchSearch(query);
            }, this.config.searchDebounce);
        },

        fetchSearch: function(query) {
            var self = this;
            this.api('/search?q=' + encodeURIComponent(query))
                .then(function(data) {
                    self.addRecentSearch(query);
                    self.renderSearchResults(data, query);
                })
                .catch(function() {
                    self.renderSearchEmpty('Search failed. Please try again.');
                });
        },

        renderSearchResults: function(data, query) {
            var container = document.getElementById('dso-search-results');
            if (!container) return;
            var html = '';
            var types = ['products', 'orders'];
            var self = this;
            types.forEach(function(type) {
                var items = data[type];
                if (!items || !items.length) return;
                html += '<div class="search-group">';
                html += '<div class="search-group-title">' + type.charAt(0).toUpperCase() + type.slice(1) + '</div>';
                items.forEach(function(item) {
                    var title = item.title || item.name || ('#' + item.id);
                    var subtitle = item.subtitle || item.status || '';
                    var url = item.url || '#';
                    html += '<a href="' + url + '" class="search-result-item">';
                    html += '<span class="search-result-title">' + self.highlightMatch(title, query) + '</span>';
                    if (subtitle) html += '<span class="search-result-subtitle">' + subtitle + '</span>';
                    html += '</a>';
                });
                html += '</div>';
            });
            if (!html) { this.renderSearchEmpty('No results found for "' + query + '"'); return; }
            container.innerHTML = html;
            container.classList.add('has-results');
            this.state.searchIndex = -1;
            this.bindSearchResultClicks();
        },

        renderSearchEmpty: function(msg) {
            var c = document.getElementById('dso-search-results');
            if (!c) return;
            c.innerHTML = '<div class="search-empty">' + msg + '</div>';
            c.classList.remove('has-results');
        },

        showSearchLoading: function() {
            var c = document.getElementById('dso-search-results');
            if (!c) return;
            c.innerHTML = '<div class="search-loading"><span class="dso-spinner"></span></div>';
            c.classList.add('has-results');
        },

        highlightMatch: function(text, query) {
            if (!query) return text;
            var escaped = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            return text.replace(new RegExp('(' + escaped + ')', 'gi'), '<mark>$1</mark>');
        },

        addRecentSearch: function(query) {
            var searches = this.state.recentSearches.filter(function(s) { return s !== query; });
            searches.unshift(query);
            if (searches.length > this.config.maxRecent) searches.pop();
            this.state.recentSearches = searches;
            localStorage.setItem('dso_recent_searches', JSON.stringify(searches));
        },

        loadRecentSearches: function() {
            try {
                this.state.recentSearches = JSON.parse(localStorage.getItem('dso_recent_searches') || '[]');
            } catch(e) {
                this.state.recentSearches = [];
            }
        },

        renderRecentSearches: function() {
            var container = document.getElementById('dso-search-recent');
            var results = document.getElementById('dso-search-results');
            if (!container) return;
            if (!this.state.recentSearches.length) {
                container.innerHTML = '';
                container.style.display = 'none';
                return;
            }
            var self = this;
            var html = '<div class="search-recent-title">Recent Searches</div>';
            this.state.recentSearches.forEach(function(q) {
                html += '<button class="search-recent-item" data-query="' + q + '">';
                html += '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>';
                html += '<span>' + q + '</span></button>';
            });
            container.innerHTML = html;
            container.style.display = 'block';
            if (results) results.classList.remove('has-results');
            container.querySelectorAll('.search-recent-item').forEach(function(item) {
                item.addEventListener('click', function() {
                    var q = item.getAttribute('data-query');
                    var input = document.getElementById('dso-search-input');
                    if (input) { input.value = q; input.focus(); }
                    self.onSearchInput(q);
                });
            });
        },

        onSearchKeydown: function(e) {
            var container = document.getElementById('dso-search-results');
            if (!container) return;
            var items = container.querySelectorAll('.search-result-item');
            if (!items.length) return;
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                this.state.searchIndex = Math.min(this.state.searchIndex + 1, items.length - 1);
                this.highlightResult(items, this.state.searchIndex);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                this.state.searchIndex = Math.max(this.state.searchIndex - 1, 0);
                this.highlightResult(items, this.state.searchIndex);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (this.state.searchIndex >= 0 && items[this.state.searchIndex]) {
                    var url = items[this.state.searchIndex].getAttribute('href');
                    if (url && url !== '#') window.location.href = url;
                }
            }
        },

        highlightResult: function(items, idx) {
            for (var i = 0; i < items.length; i++) items[i].classList.remove('active');
            if (idx >= 0 && items[idx]) {
                items[idx].classList.add('active');
                items[idx].scrollIntoView({ block: 'nearest' });
            }
        },

        bindSearchResultClicks: function() {
            var self = this;
            document.querySelectorAll('#dso-search-results .search-result-item').forEach(function(item) {
                item.addEventListener('click', function(e) {
                    var url = item.getAttribute('href');
                    if (!url || url === '#') e.preventDefault();
                    else self.closeSearch();
                });
            });
        },

        // ─── 4. Command Palette ────────────────────────────
        commands: [
            { id: 'add-product', label: 'Add Product', icon: '📦', action: 'addProduct' },
            { id: 'find-order', label: 'Find Order', icon: '🔍', action: 'findOrder' },
            { id: 'analytics', label: 'View Analytics', icon: '📊', action: 'viewAnalytics' },
            { id: 'inventory', label: 'Manage Inventory', icon: '📋', action: 'manageInventory' },
            { id: 'reports', label: 'View Reports', icon: '📈', action: 'viewReports' },
            { id: 'performance', label: 'Check Performance', icon: '⚡', action: 'checkPerformance' },
            { id: 'settings', label: 'Settings', icon: '⚙️', action: 'openSettings' },
            { id: 'help', label: 'Help & Support', icon: '❓', action: 'openHelp' },
            { id: 'dashboard', label: 'Go to Dashboard', icon: '🏠', action: 'goDashboard' },
            { id: 'finance', label: 'View Finance', icon: '💰', action: 'goFinance' }
        ],

        initCommandPalette: function() {
            var self = this;
            var overlay = document.getElementById('dso-command-overlay');
            if (overlay) {
                overlay.addEventListener('click', function(e) {
                    if (e.target === overlay) self.closeCommand();
                });
            }
            var closeBtn = document.getElementById('dso-command-close');
            if (closeBtn) closeBtn.addEventListener('click', function() { self.closeCommand(); });
            var input = document.getElementById('dso-command-input');
            if (input) {
                input.addEventListener('input', function() { self.filterCommands(this.value); });
                input.addEventListener('keydown', function(e) { self.onCommandKeydown(e); });
            }
        },

        openCommand: function() {
            var overlay = document.getElementById('dso-command-overlay');
            var input = document.getElementById('dso-command-input');
            if (overlay) overlay.classList.add('active');
            if (input) { input.value = ''; input.focus(); }
            this.state.commandOpen = true;
            this.state.commandIndex = -1;
            this.renderCommands(this.commands);
            document.body.classList.add('dso-overlay-open');
        },

        closeCommand: function() {
            var overlay = document.getElementById('dso-command-overlay');
            if (overlay) overlay.classList.remove('active');
            this.state.commandOpen = false;
            this.state.commandIndex = -1;
            document.body.classList.remove('dso-overlay-open');
        },

        renderCommands: function(cmds) {
            var container = document.getElementById('dso-command-list');
            if (!container) return;
            if (!cmds.length) {
                container.innerHTML = '<div class="command-empty">No commands found</div>';
                return;
            }
            var self = this;
            var html = '';
            cmds.forEach(function(cmd) {
                html += '<button class="command-item" data-action="' + cmd.action + '">';
                html += '<span class="command-icon">' + cmd.icon + '</span>';
                html += '<span class="command-label">' + cmd.label + '</span></button>';
            });
            container.innerHTML = html;
            this.state.commandIndex = -1;
            container.querySelectorAll('.command-item').forEach(function(item) {
                item.addEventListener('click', function() {
                    self.executeCommand(item.getAttribute('data-action'));
                    self.closeCommand();
                });
            });
        },

        filterCommands: function(query) {
            if (!query) { this.renderCommands(this.commands); return; }
            var lower = query.toLowerCase();
            var filtered = this.commands.filter(function(cmd) {
                return cmd.label.toLowerCase().indexOf(lower) !== -1;
            });
            this.renderCommands(filtered);
        },

        onCommandKeydown: function(e) {
            var items = document.querySelectorAll('#dso-command-list .command-item');
            if (!items.length) return;
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                this.state.commandIndex = Math.min(this.state.commandIndex + 1, items.length - 1);
                this.highlightResult(items, this.state.commandIndex);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                this.state.commandIndex = Math.max(this.state.commandIndex - 1, 0);
                this.highlightResult(items, this.state.commandIndex);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (this.state.commandIndex >= 0 && items[this.state.commandIndex]) {
                    items[this.state.commandIndex].click();
                }
            }
        },

        executeCommand: function(action) {
            var base = this.config.baseUrl;
            var routes = {
                addProduct: base + 'seller/products/add/',
                findOrder: base + 'seller/orders/',
                viewAnalytics: base + 'seller/analytics/',
                manageInventory: base + 'seller/inventory/',
                viewReports: base + 'seller/reports/',
                checkPerformance: base + 'seller/performance/',
                openSettings: base + 'seller/settings/',
                openHelp: base + 'seller/help/',
                goDashboard: base + 'seller/dashboard/',
                goFinance: base + 'seller/finance/'
            };
            if (routes[action]) window.location.href = routes[action];
        },

        // ─── 5. Dashboard Charts ───────────────────────────
        _chartDefaults: function() {
            return {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1a1d2e',
                        titleFont: { size: 13, weight: '600' },
                        bodyFont: { size: 13 },
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11, weight: '500' }, color: '#9ca3af' } },
                    y: { grid: { color: '#f3f4f6' }, ticks: { font: { size: 11, weight: '500' }, color: '#9ca3af' } }
                }
            };
        },

        _makeGradient: function(ctx, color1, color2) {
            var g = ctx.createLinearGradient(0, 0, 0, 280);
            g.addColorStop(0, color1);
            g.addColorStop(1, color2);
            return g;
        },

        _buildChart: function(canvasId, type, data, opts) {
            var canvas = document.getElementById(canvasId);
            if (!canvas || typeof Chart === 'undefined') return null;
            if (this.state.charts[canvasId]) this.state.charts[canvasId].destroy();
            this.state.charts[canvasId] = new Chart(canvas.getContext('2d'), {
                type: type,
                data: data,
                options: opts || this._chartDefaults()
            });
            return this.state.charts[canvasId];
        },

        initDashboard: function(chartData) {
            if (!chartData || typeof Chart === 'undefined') return;
            var canvas = document.getElementById('dso-sales-chart');
            if (!canvas) return;
            var gradient = this._makeGradient(canvas.getContext('2d'), 'rgba(79,70,229,0.15)', 'rgba(79,70,229,0)');
            var opts = this._chartDefaults();
            opts.plugins.legend.display = false;
            opts.plugins.tooltip.callbacks = {
                label: function(ctx) { return 'Sales: ' + this.config.currency + ctx.parsed.y.toLocaleString(); }.bind(this)
            };
            opts.scales.y.ticks.callback = function(v) { return this.config.currency + v.toLocaleString(); }.bind(this);
            this._buildChart('dso-sales-chart', 'line', {
                labels: chartData.labels,
                datasets: [{
                    label: 'Sales', data: chartData.sales, borderColor: '#4f46e5', backgroundColor: gradient,
                    borderWidth: 2.5, fill: true, tension: 0.4, pointRadius: 0, pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#4f46e5', pointHoverBorderColor: '#fff', pointHoverBorderWidth: 3
                }]
            }, opts);
            this.bindPeriodFilter('dso-dashboard-period', 'dashboard');
        },

        initFinance: function(chartData) {
            if (!chartData || typeof Chart === 'undefined') return;
            var canvas = document.getElementById('dso-earnings-chart');
            if (!canvas) return;
            var gradient = this._makeGradient(canvas.getContext('2d'), 'rgba(20,184,166,0.15)', 'rgba(20,184,166,0)');
            var opts = this._chartDefaults();
            opts.scales.y.ticks.callback = function(v) { return this.config.currency + v.toLocaleString(); }.bind(this);
            this._buildChart('dso-earnings-chart', 'line', {
                labels: chartData.labels,
                datasets: [{
                    label: 'Earnings', data: chartData.earnings, borderColor: '#14b8a6', backgroundColor: gradient,
                    borderWidth: 2.5, fill: true, tension: 0.4, pointRadius: 0, pointHoverRadius: 5
                }]
            }, opts);
            this.bindPeriodFilter('dso-finance-period', 'finance');
        },

        initAnalytics: function(chartData) {
            if (!chartData || typeof Chart === 'undefined') return;
            var opts = this._chartDefaults();
            // Revenue line
            var revCanvas = document.getElementById('dso-revenue-chart');
            if (revCanvas) {
                var gradient = this._makeGradient(revCanvas.getContext('2d'), 'rgba(16,185,129,0.15)', 'rgba(16,185,129,0)');
                this._buildChart('dso-revenue-chart', 'line', {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Revenue', data: chartData.revenue, borderColor: '#10b981', backgroundColor: gradient,
                        borderWidth: 2.5, fill: true, tension: 0.4, pointRadius: 0, pointHoverRadius: 5
                    }]
                }, opts);
            }
            // Orders bar
            var ordCanvas = document.getElementById('dso-orders-chart');
            if (ordCanvas) {
                var barOpts = this._chartDefaults();
                this._buildChart('dso-orders-chart', 'bar', {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Orders', data: chartData.orders, backgroundColor: 'rgba(79,70,229,0.8)',
                        borderRadius: 4, borderSkipped: false, barThickness: 'flex', maxBarThickness: 12
                    }]
                }, barOpts);
            }
            this.bindPeriodFilter('dso-analytics-period', 'analytics');
        },

        initReports: function(chartData) {
            if (!chartData || typeof Chart === 'undefined') return;
            var canvas = document.getElementById('dso-reports-chart');
            if (!canvas) return;
            var gradient = this._makeGradient(canvas.getContext('2d'), 'rgba(139,92,246,0.15)', 'rgba(139,92,246,0)');
            var opts = this._chartDefaults();
            opts.scales.y.ticks.callback = function(v) { return this.config.currency + v.toLocaleString(); }.bind(this);
            this._buildChart('dso-reports-chart', 'line', {
                labels: chartData.labels,
                datasets: [{
                    label: 'Revenue', data: chartData.values || chartData.revenue, borderColor: '#8b5cf6',
                    backgroundColor: gradient, borderWidth: 2.5, fill: true, tension: 0.4, pointRadius: 0, pointHoverRadius: 5
                }]
            }, opts);
            this.bindPeriodFilter('dso-reports-period', 'reports');
        },

        initPerformance: function(chartData) {
            if (!chartData || typeof Chart === 'undefined') return;
            var canvas = document.getElementById('dso-performance-chart');
            if (!canvas) return;
            var gradient = this._makeGradient(canvas.getContext('2d'), 'rgba(6,182,212,0.15)', 'rgba(6,182,212,0)');
            var opts = this._chartDefaults();
            opts.scales.y.ticks.callback = function(v) { return this.config.currency + v.toLocaleString(); }.bind(this);
            opts.scales.y1 = { position: 'right', grid: { drawOnChartArea: false }, ticks: { font: { size: 11 }, color: '#9ca3af' } };
            opts.plugins.legend.display = true;
            opts.plugins.legend.position = 'top';
            this._buildChart('dso-performance-chart', 'line', {
                labels: chartData.labels,
                datasets: [{
                    label: 'Revenue', data: chartData.revenue || chartData.values, borderColor: '#06b6d4',
                    backgroundColor: gradient, borderWidth: 2.5, fill: true, tension: 0.4, pointRadius: 0, pointHoverRadius: 5,
                    yAxisID: 'y'
                }, {
                    label: 'Orders', data: chartData.orders, borderColor: '#f97316',
                    backgroundColor: 'transparent', borderWidth: 2, borderDash: [5, 5], tension: 0.4,
                    pointRadius: 0, pointHoverRadius: 5, yAxisID: 'y1'
                }]
            }, opts);
            this.bindPeriodFilter('dso-performance-period', 'performance');
        },

        bindPeriodFilter: function(containerId, section) {
            var self = this;
            var container = document.getElementById(containerId);
            if (!container) return;
            container.querySelectorAll('button').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    container.querySelectorAll('button').forEach(function(b) { b.classList.remove('active'); });
                    btn.classList.add('active');
                    self.fetchChartData(section, btn.getAttribute('data-period'));
                });
            });
        },

        fetchChartData: function(section, period) {
            var self = this;
            var canvasId = 'dso-' + (section === 'dashboard' ? 'sales' : section === 'finance' ? 'earnings' : section) + '-chart';
            var container = document.getElementById(canvasId);
            if (container) this.showSkeleton(container.parentElement);
            this.api('/charts/' + section + '?period=' + period + '&vendor_id=' + this.config.vendorId)
                .then(function(data) {
                    if (container) self.hideSkeleton(container.parentElement);
                    var method = 'init' + section.charAt(0).toUpperCase() + section.slice(1);
                    if (self[method]) self[method](data);
                })
                .catch(function() {
                    if (container) self.hideSkeleton(container.parentElement);
                    self.toast('Failed to load chart data', 'error');
                });
        },

        // ─── 6. Products ───────────────────────────────────
        initProducts: function() {
            var self = this;
            var search = document.getElementById('dso-product-search');
            var statusFilter = document.getElementById('dso-product-status');
            var stockFilter = document.getElementById('dso-product-stock');
            var selectAll = document.getElementById('dso-select-all');
            var bulkBtn = document.getElementById('dso-bulk-apply');

            if (search) search.addEventListener('input', function() { self.filterProducts(); });
            if (statusFilter) statusFilter.addEventListener('change', function() { self.filterProducts(); });
            if (stockFilter) stockFilter.addEventListener('change', function() { self.filterProducts(); });

            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    var checked = selectAll.checked;
                    document.querySelectorAll('.dso-product-row input[type="checkbox"]').forEach(function(cb) {
                        cb.checked = checked;
                        var id = cb.getAttribute('data-id');
                        if (checked) self.state.selectedProducts[id] = true;
                        else delete self.state.selectedProducts[id];
                    });
                    self.state.selectedCount = Object.keys(self.state.selectedProducts).length;
                    self.updateBulkBar();
                });
            }

            document.querySelectorAll('.dso-product-row input[type="checkbox"]').forEach(function(cb) {
                cb.addEventListener('change', function() {
                    var id = cb.getAttribute('data-id');
                    if (cb.checked) self.state.selectedProducts[id] = true;
                    else delete self.state.selectedProducts[id];
                    self.state.selectedCount = Object.keys(self.state.selectedProducts).length;
                    self.updateBulkBar();
                    var all = document.querySelectorAll('.dso-product-row input[type="checkbox"]');
                    var sa = document.getElementById('dso-select-all');
                    if (sa) sa.checked = self.state.selectedCount === all.length;
                });
            });

            if (bulkBtn) {
                bulkBtn.addEventListener('click', function() {
                    var action = (document.getElementById('dso-bulk-action') || {}).value || '';
                    if (!action || self.state.selectedCount === 0) {
                        self.toast('Select products and an action', 'warning');
                        return;
                    }
                    self.executeBulkAction(action);
                });
            }
        },

        filterProducts: function() {
            var search = (document.getElementById('dso-product-search') || {}).value || '';
            var status = (document.getElementById('dso-product-status') || {}).value || '';
            var stock = (document.getElementById('dso-product-stock') || {}).value || '';
            var lower = search.toLowerCase();
            document.querySelectorAll('.dso-product-row').forEach(function(row) {
                var name = (row.getAttribute('data-name') || '').toLowerCase();
                var rStatus = row.getAttribute('data-status') || '';
                var rStock = row.getAttribute('data-stock') || '';
                var show = true;
                if (lower && name.indexOf(lower) === -1) show = false;
                if (status && rStatus !== status) show = false;
                if (stock && rStock !== stock) show = false;
                row.style.display = show ? '' : 'none';
            });
        },

        updateBulkBar: function() {
            var bar = document.getElementById('dso-bulk-bar');
            var count = document.getElementById('dso-bulk-count');
            if (!bar) return;
            if (this.state.selectedCount > 0) {
                bar.classList.add('active');
                if (count) count.textContent = this.state.selectedCount;
            } else {
                bar.classList.remove('active');
            }
        },

        executeBulkAction: function(action) {
            var self = this;
            var ids = Object.keys(this.state.selectedProducts);
            this.api('/products/bulk', {
                method: 'POST',
                body: JSON.stringify({ action: action, ids: ids })
            }).then(function() {
                self.toast('Bulk action completed', 'success');
                setTimeout(function() { window.location.reload(); }, 1200);
            }).catch(function() {
                self.toast('Bulk action failed', 'error');
            });
        },

        // ─── 7. Add / Edit Product ─────────────────────────
        initAddProduct: function() { this._initProductForm(); },
        initEditProduct: function() { this._initProductForm(); },

        _initProductForm: function() {
            var self = this;
            var manageStock = document.getElementById('manage_stock');
            var stockFields = document.getElementById('stock-fields');
            var statusField = document.getElementById('stock-status-field');
            if (manageStock) {
                var toggle = function() {
                    if (manageStock.checked) {
                        if (stockFields) stockFields.style.display = '';
                        if (statusField) statusField.style.display = 'none';
                    } else {
                        if (stockFields) stockFields.style.display = 'none';
                        if (statusField) statusField.style.display = '';
                    }
                };
                manageStock.addEventListener('change', toggle);
                toggle();
            }
            document.querySelectorAll('[data-dso-upload]').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    self.openMediaLibrary(btn.getAttribute('data-dso-upload'));
                });
            });
        },

        openMediaLibrary: function(targetId) {
            if (typeof wp === 'undefined' || !wp.media) {
                this.toast('Media library not available', 'error');
                return;
            }
            var frame = wp.media({
                title: 'Select Image',
                button: { text: 'Use this image' },
                multiple: false,
                library: { type: 'image' }
            });
            frame.on('select', function() {
                var att = frame.state().get('selection').first().toJSON();
                var input = document.getElementById(targetId);
                var preview = document.getElementById(targetId + '-preview');
                if (input) input.value = att.id;
                if (preview) {
                    preview.src = (att.sizes && att.sizes.medium) ? att.sizes.medium.url : att.url;
                    preview.style.display = 'block';
                }
            });
            frame.open();
        },

        // ─── 8. Inventory ──────────────────────────────────
        initInventory: function() {
            var self = this;
            var filter = document.getElementById('dso-inv-filter');
            if (filter) {
                filter.addEventListener('change', function() {
                    var val = this.value;
                    document.querySelectorAll('.dso-table tbody tr[data-stock]').forEach(function(row) {
                        row.style.display = (!val || row.getAttribute('data-stock') === val) ? '' : 'none';
                    });
                });
            }
            document.querySelectorAll('.dso-stock-edit-btn').forEach(function(btn) {
                btn.addEventListener('click', function() { self.inlineStockEdit(btn); });
            });
            document.querySelectorAll('.dso-update-stock').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var id = btn.getAttribute('data-id');
                    var input = document.querySelector('.dso-stock-input[data-id="' + id + '"]');
                    if (!input) return;
                    var stock = parseInt(input.value, 10);
                    if (isNaN(stock) || stock < 0) return;
                    btn.disabled = true;
                    btn.textContent = 'Updating...';
                    self.api('/products/' + id + '/stock', {
                        method: 'POST',
                        body: JSON.stringify({ stock: stock })
                    }).then(function(data) {
                        btn.disabled = false;
                        if (data.success) {
                            btn.style.background = '#10b981';
                            btn.textContent = '\u2713 Saved';
                            setTimeout(function() { btn.style.background = ''; btn.textContent = 'Update'; }, 1500);
                        } else {
                            btn.textContent = 'Update';
                        }
                    }).catch(function() {
                        btn.disabled = false;
                        btn.textContent = 'Update';
                    });
                });
            });
        },

        inlineStockEdit: function(btn) {
            var self = this;
            var row = btn.closest('tr') || btn.closest('.dso-inventory-row');
            if (!row) return;
            var stockEl = row.querySelector('.dso-stock-value');
            var inputEl = row.querySelector('.dso-stock-input');
            var saveEl = row.querySelector('.dso-stock-save');
            var cancelEl = row.querySelector('.dso-stock-cancel');
            var id = row.getAttribute('data-product-id') || btn.getAttribute('data-id');
            if (stockEl) stockEl.style.display = 'none';
            btn.style.display = 'none';
            if (inputEl) { inputEl.style.display = ''; inputEl.focus(); }
            if (saveEl) saveEl.style.display = '';
            if (cancelEl) cancelEl.style.display = '';

            var save = function() {
                var qty = parseInt(inputEl.value, 10);
                if (isNaN(qty) || qty < 0) { self.toast('Enter a valid quantity', 'warning'); return; }
                self.api('/products/' + id + '/stock', {
                    method: 'POST',
                    body: JSON.stringify({ stock: qty })
                }).then(function(data) {
                    if (data.success) {
                        if (stockEl) { stockEl.textContent = qty; stockEl.style.display = ''; }
                        if (inputEl) inputEl.style.display = 'none';
                        if (saveEl) saveEl.style.display = 'none';
                        if (cancelEl) cancelEl.style.display = 'none';
                        btn.style.display = '';
                        self.toast('Stock updated', 'success');
                    }
                }).catch(function() { self.toast('Update failed', 'error'); });
            };
            var cancel = function() {
                if (stockEl) stockEl.style.display = '';
                btn.style.display = '';
                if (inputEl) inputEl.style.display = 'none';
                if (saveEl) saveEl.style.display = 'none';
                if (cancelEl) cancelEl.style.display = 'none';
                saveEl.removeEventListener('click', save);
                cancelEl.removeEventListener('click', cancel);
            };
            if (saveEl) saveEl.addEventListener('click', save);
            if (cancelEl) cancelEl.addEventListener('click', cancel);
        },

        // ─── 9. Settings / Help / Favorites ────────────────
        initSettings: function() {
            var self = this;
            document.querySelectorAll('[data-dso-toggle="settings"]').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    self.toggleDropdown('settings');
                });
            });
        },

        initHelp: function() {
            var self = this;
            document.querySelectorAll('[data-dso-toggle="help"]').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    self.toggleDropdown('help');
                });
            });
        },

        initFavorites: function() {
            var self = this;
            document.querySelectorAll('[data-dso-toggle="favorites"]').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    self.toggleDropdown('favorites');
                });
            });
        },

        toggleDropdown: function(name) {
            var key = name + 'Open';
            var el = document.getElementById('dso-' + name + '-dropdown');
            var stateKey = name + 'Open';
            // Close others
            ['settings', 'help', 'favorites'].forEach(function(n) {
                if (n !== name) {
                    var d = document.getElementById('dso-' + n + '-dropdown');
                    if (d) d.classList.remove('active');
                    this.state[n + 'Open'] = false;
                }
            }.bind(this));
            if (el) el.classList.toggle('active');
            this.state[stateKey] = !this.state[stateKey];
        },

        closeAllDropdowns: function() {
            var self = this;
            ['settings', 'help', 'favorites'].forEach(function(n) {
                var el = document.getElementById('dso-' + n + '-dropdown');
                if (el) el.classList.remove('active');
                self.state[n + 'Open'] = false;
            });
        },

        // ─── 10. Toast Notifications ───────────────────────
        toast: function(message, type) {
            type = type || 'info';
            var container = document.getElementById('dso-toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'dso-toast-container';
                container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:99999;display:flex;flex-direction:column;gap:8px;';
                document.body.appendChild(container);
            }
            var toast = document.createElement('div');
            toast.className = 'dso-toast dso-toast-' + type;
            var icons = { success: '✓', error: '✕', warning: '⚠', info: 'ℹ' };
            toast.innerHTML = '<span class="dso-toast-icon">' + (icons[type] || icons.info) + '</span><span class="dso-toast-msg">' + message + '</span>';
            container.appendChild(toast);
            requestAnimationFrame(function() { toast.classList.add('show'); });
            setTimeout(function() {
                toast.classList.remove('show');
                setTimeout(function() { toast.remove(); }, 300);
            }, 4000);
        },

        // ─── 11. Keyboard Shortcuts ────────────────────────
        initKeyboardShortcuts: function() {
            var self = this;
            document.addEventListener('keydown', function(e) {
                var isMeta = e.metaKey || e.ctrlKey;
                // Cmd/Ctrl+K: open search
                if (isMeta && e.key === 'k') {
                    e.preventDefault();
                    if (self.state.commandOpen) { self.closeCommand(); return; }
                    if (self.state.searchOpen) { self.closeSearch(); return; }
                    self.openSearch();
                    return;
                }
                // Escape: close overlays
                if (e.key === 'Escape') {
                    if (self.state.searchOpen) { self.closeSearch(); return; }
                    if (self.state.commandOpen) { self.closeCommand(); return; }
                    if (self.state.sidebarOpen) { self.closeSidebar(); return; }
                    self.closeAllDropdowns();
                }
            });
        },

        // ─── 12. Auto-refresh Notifications ─────────────────
        initAutoRefresh: function() {
            var self = this;
            this.pollNotifications();
            this.state.refreshTimer = setInterval(function() {
                self.pollNotifications();
            }, this.config.refreshInterval);
        },

        pollNotifications: function() {
            var badge = document.getElementById('dso-notif-badge');
            if (!badge) return;
            this.api('/notifications/count?vendor_id=' + this.config.vendorId)
                .then(function(data) {
                    if (data && typeof data.count !== 'undefined') {
                        if (data.count > 0) {
                            badge.textContent = data.count > 99 ? '99+' : data.count;
                            badge.style.display = '';
                        } else {
                            badge.style.display = 'none';
                        }
                    }
                })
                .catch(function() {});
        },

        // ─── 13. Responsive Handling ───────────────────────
        initResponsive: function() {
            var self = this;
            var resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    if (window.innerWidth >= 1024 && self.state.sidebarOpen) {
                        self.closeSidebar();
                    }
                    // Close dropdowns on resize to desktop
                    if (window.innerWidth >= 1024) self.closeAllDropdowns();
                }, 150);
            });
        },

        // ─── 14. Smooth Scroll ─────────────────────────────
        initSmoothScroll: function() {
            document.querySelectorAll('a[href^="#"]').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    var hash = this.getAttribute('href');
                    if (!hash || hash === '#' || hash === '#0') return;
                    var target = document.querySelector(hash);
                    if (target) {
                        e.preventDefault();
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            });
        },

        // ─── 15. Table Sorting ─────────────────────────────
        initTableSort: function() {
            document.querySelectorAll('.dso-table th[data-sort]').forEach(function(th) {
                th.style.cursor = 'pointer';
                th.addEventListener('click', function() {
                    var table = th.closest('table');
                    if (!table) return;
                    var tbody = table.querySelector('tbody');
                    if (!tbody) return;
                    var key = th.getAttribute('data-sort');
                    var dir = th.getAttribute('data-sort-dir') === 'asc' ? 'desc' : 'asc';
                    th.setAttribute('data-sort-dir', dir);
                    // Clear other sort indicators
                    table.querySelectorAll('th[data-sort]').forEach(function(h) {
                        if (h !== th) h.removeAttribute('data-sort-dir');
                    });
                    var rows = Array.from(tbody.querySelectorAll('tr'));
                    var idx = Array.from(th.parentElement.children).indexOf(th);
                    rows.sort(function(a, b) {
                        var aVal = (a.children[idx] || {}).textContent || '';
                        var bVal = (b.children[idx] || {}).textContent || '';
                        var aNum = parseFloat(aVal.replace(/[^0-9.\-]/g, ''));
                        var bNum = parseFloat(bVal.replace(/[^0-9.\-]/g, ''));
                        if (!isNaN(aNum) && !isNaN(bNum)) {
                            return dir === 'asc' ? aNum - bNum : bNum - aNum;
                        }
                        return dir === 'asc' ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
                    });
                    rows.forEach(function(row) { tbody.appendChild(row); });
                    // Update visual indicator
                    table.querySelectorAll('th[data-sort]').forEach(function(h) {
                        h.classList.remove('sort-asc', 'sort-desc');
                    });
                    th.classList.add(dir === 'asc' ? 'sort-asc' : 'sort-desc');
                });
            });
        },

        // ─── 16. Skeleton Loading ──────────────────────────
        showSkeleton: function(container) {
            if (!container) return;
            container.classList.add('dso-skeleton-active');
            var overlay = container.querySelector('.dso-skeleton-overlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.className = 'dso-skeleton-overlay';
                overlay.innerHTML = '<div class="dso-skeleton-pulse"></div>';
                container.style.position = 'relative';
                container.appendChild(overlay);
            }
        },

        hideSkeleton: function(container) {
            if (!container) return;
            container.classList.remove('dso-skeleton-active');
            var overlay = container.querySelector('.dso-skeleton-overlay');
            if (overlay) overlay.remove();
        },

        // ─── 17. Orders / Customers (generic init) ─────────
        initOrders: function() {
            var self = this;
            var searchInput = document.getElementById('dso-order-search');
            var statusFilter = document.getElementById('dso-order-status');
            function filter() {
                var query = (searchInput ? searchInput.value : '').toLowerCase();
                var status = statusFilter ? statusFilter.value : '';
                document.querySelectorAll('.dso-order-row').forEach(function(row) {
                    var text = row.textContent.toLowerCase();
                    var rowStatus = row.getAttribute('data-status') || '';
                    var show = true;
                    if (query && text.indexOf(query) === -1) show = false;
                    if (status && rowStatus !== status) show = false;
                    row.style.display = show ? '' : 'none';
                });
            }
            if (searchInput) searchInput.addEventListener('input', filter);
            if (statusFilter) statusFilter.addEventListener('change', filter);
        },

        initCustomers: function() {
            var searchInput = document.getElementById('dso-customer-search');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    var query = this.value.toLowerCase();
                    document.querySelectorAll('.dso-table tbody tr').forEach(function(row) {
                        row.style.display = row.textContent.toLowerCase().indexOf(query) > -1 ? '' : 'none';
                    });
                });
            }
        }
    };

    // ─── Auto-init on DOMContentLoaded ────────────────────
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() { DSO.init(); });
    } else {
        DSO.init();
    }

})(window, document);
