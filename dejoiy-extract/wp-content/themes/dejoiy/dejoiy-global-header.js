/**
 * DEJOIY Global Header OS — Unified JS for all interactions.
 *
 * @package Dejoiy
 */
(function () {
	'use strict';

	var cfg = window.dejoiyGH;
	if (!cfg) {
		return;
	}

	var header = document.getElementById('dejoiy-global-header');
	if (!header) {
		return;
	}

	var isDesktop = window.matchMedia('(min-width: 1025px)');
	var isMobile = window.matchMedia('(max-width: 1024px)');
	var SCROLLED = 'gh-scrolled';
	var RECENT_KEY = 'dejoiy_gh_recent_v1';
	var MAX_RECENT = 6;

	/* ---------------------------------------------------------------
	   UTILITIES
	   --------------------------------------------------------------- */

	function qs(sel, root) {
		return (root || document).querySelector(sel);
	}

	function qsa(sel, root) {
		return Array.prototype.slice.call((root || document).querySelectorAll(sel));
	}

	function escapeHtml(s) {
		var d = document.createElement('div');
		d.textContent = s;
		return d.innerHTML;
	}

	function debounce(fn, ms) {
		var timer;
		return function () {
			var args = arguments;
			var ctx = this;
			clearTimeout(timer);
			timer = setTimeout(function () {
				fn.apply(ctx, args);
			}, ms);
		};
	}

	/* ---------------------------------------------------------------
	   SCROLL BEHAVIOR — compact sticky
	   --------------------------------------------------------------- */

	var scrollTicking = false;

	function onScroll() {
		if (!scrollTicking) {
			window.requestAnimationFrame(function () {
				var scrolled = window.scrollY > 10;
				document.body.classList.toggle(SCROLLED, scrolled);
				scrollTicking = false;
			});
			scrollTicking = true;
		}
	}

	window.addEventListener('scroll', onScroll, { passive: true });
	onScroll();

	/* ---------------------------------------------------------------
	   CLOSE ALL DROPDOWNS
	   --------------------------------------------------------------- */

	function closeAllDropdowns(except) {
		qsa('[data-gh-account-menu]', header).forEach(function (el) {
			if (el !== except) {
				el.hidden = true;
			}
		});
		qsa('[data-gh-account-toggle]', header).forEach(function (btn) {
			if (!except || !except.contains(btn)) {
				btn.setAttribute('aria-expanded', 'false');
			}
		});
		qsa('[data-gh-mega]', header).forEach(function (el) {
			if (el !== except) {
				el.hidden = true;
			}
		});
		qsa('[data-gh-browse-toggle]', header).forEach(function (btn) {
			if (!except || !except.contains(btn)) {
				btn.setAttribute('aria-expanded', 'false');
			}
		});
	}

	document.addEventListener('click', function (e) {
		if (!header.contains(e.target)) {
			closeAllDropdowns();
		}
	});

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') {
			closeAllDropdowns();
			closeMobileDrawer();
			closeMobileSearch();
		}
	});

	/* ---------------------------------------------------------------
	   ACCOUNT DROPDOWN (desktop)
	   --------------------------------------------------------------- */

	function setupAccountDropdown() {
		var toggle = qs('[data-gh-account-toggle]', header);
		var menu = qs('[data-gh-account-menu]', header);
		if (!toggle || !menu) {
			return;
		}

		toggle.addEventListener('click', function (e) {
			e.stopPropagation();
			var open = menu.hidden;
			closeAllDropdowns(open ? menu : null);
			menu.hidden = !open;
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
	}

	/* ---------------------------------------------------------------
	   MEGA MENU (desktop)
	   --------------------------------------------------------------- */

	function setupMegaMenu() {
		var toggle = qs('[data-gh-browse-toggle]', header);
		var mega = qs('[data-gh-mega]', header);
		if (!toggle || !mega) {
			return;
		}

		toggle.addEventListener('click', function (e) {
			e.preventDefault();
			e.stopPropagation();
			var open = mega.hidden;
			closeAllDropdowns(open ? mega : null);
			mega.hidden = !open;
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		});

		// Keep open when hovering mega
		mega.addEventListener('mouseenter', function () {
			toggle.setAttribute('aria-expanded', 'true');
		});
	}

	/* ---------------------------------------------------------------
	   DESKTOP SEARCH
	   --------------------------------------------------------------- */

	function setupDesktopSearch() {
		var form = qs('[data-gh-search]', header);
		var input = qs('[data-gh-search-input]', header);
		var panel = qs('#gh-search-panel', header);
		var results = qs('#gh-search-results', header);
		var closeBtn = qs('[data-gh-search-close]', header);

		if (!form || !input || !panel || !results) {
			return;
		}

		var timer = null;

		function openPanel() {
			panel.hidden = false;
			form.classList.add('gh-search--open');
		}

		function closePanel() {
			panel.hidden = true;
			results.hidden = true;
			results.innerHTML = '';
			form.classList.remove('gh-search--open');
		}

		function renderLoading() {
			openPanel();
			results.innerHTML = '<div class="gh-search__loading">Searching DEJOIY…</div>';
			results.hidden = false;
		}

		function renderResults(items) {
			openPanel();
			if (!items || !items.length) {
				results.innerHTML =
					'<div class="gh-search__empty">' +
					'<p>' + (cfg.i18n.noResults || 'No results found') + '</p>' +
					'<a href="' + (cfg.shopUrl || '#') + '" style="color:#2563EB;font-weight:600;">Browse all products →</a>' +
					'</div>';
				results.hidden = false;
				return;
			}
			results.innerHTML = items.map(function (item) {
				var thumb = item.thumb
					? '<img src="' + escapeHtml(item.thumb) + '" alt="" width="40" height="40" />'
					: '<span style="width:40px;height:40px;display:grid;place-items:center;background:#f1f5f9;border-radius:8px"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.62l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/></svg></span>';
				return (
					'<a class="gh-search__result" href="' + escapeHtml(item.url) + '">' +
					thumb +
					'<div>' +
					'<span class="gh-search__result-badge">' + escapeHtml(item.eco || 'Product') + '</span><br>' +
					'<span class="gh-search__result-title">' + escapeHtml(item.title) + '</span>' +
					(item.price ? '<br><span class="gh-search__result-price">' + item.price + '</span>' : '') +
					'</div></a>'
				);
			}).join('');
			results.hidden = false;
		}

		function runSearch(q) {
			if (!cfg.ajaxUrl || q.length < 2) {
				return;
			}
			var fd = new FormData();
			fd.append('action', 'dejoiy_gh_search');
			fd.append('nonce', cfg.nonce || '');
			fd.append('q', q);
			fetch(cfg.ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
				.then(function (r) { return r.json(); })
				.then(function (data) {
					renderResults((data && data.results) || []);
				})
				.catch(function () {
					closePanel();
				});
		}

		var debouncedSearch = debounce(function (q) {
			runSearch(q);
		}, 280);

		input.addEventListener('input', function () {
			var q = input.value.trim();
			if (q.length < 2) {
				closePanel();
				return;
			}
			renderLoading();
			debouncedSearch(q);
		});

		input.addEventListener('focus', function () {
			var q = input.value.trim();
			if (q.length >= 2) {
				renderLoading();
				debouncedSearch(q);
			}
		});

		if (closeBtn) {
			closeBtn.addEventListener('click', function (e) {
				e.preventDefault();
				e.stopPropagation();
				closePanel();
			});
		}

		document.addEventListener('click', function (e) {
			if (!form.contains(e.target)) {
				closePanel();
			}
		});
	}

	/* ---------------------------------------------------------------
	   MOBILE DRAWER
	   --------------------------------------------------------------- */

	function openMobileDrawer() {
		var drawer = qs('#gh-m-drawer', header);
		if (!drawer) {
			return;
		}
		drawer.hidden = false;
		drawer.setAttribute('aria-hidden', 'false');
		document.body.style.overflow = 'hidden';
	}

	function closeMobileDrawer() {
		var drawer = qs('#gh-m-drawer', header);
		if (!drawer) {
			return;
		}
		drawer.hidden = true;
		drawer.setAttribute('aria-hidden', 'true');
		document.body.style.overflow = '';
	}

	function setupMobileDrawer() {
		var browseBtn = qs('[data-gh-m-browse]', header);
		if (browseBtn) {
			browseBtn.addEventListener('click', openMobileDrawer);
		}
		qsa('[data-gh-m-close]', header).forEach(function (el) {
			el.addEventListener('click', closeMobileDrawer);
		});
	}

	/* ---------------------------------------------------------------
	   MOBILE SEARCH SHEET
	   --------------------------------------------------------------- */

	function openMobileSearch() {
		var sheet = qs('#gh-m-search-sheet', header);
		var input = qs('[data-gh-m-sheet-input]', header);
		if (!sheet) {
			return;
		}
		sheet.hidden = false;
		sheet.setAttribute('aria-hidden', 'false');
		document.body.style.overflow = 'hidden';
		if (input) {
			setTimeout(function () {
				input.focus();
			}, 100);
		}
	}

	function closeMobileSearch() {
		var sheet = qs('#gh-m-search-sheet', header);
		if (!sheet) {
			return;
		}
		sheet.hidden = true;
		sheet.setAttribute('aria-hidden', 'true');
		document.body.style.overflow = '';
	}

	function setupMobileSearch() {
		var searchWrap = qs('.gh-m-search__wrap', header);
		var searchInput = qs('[data-gh-m-search-input]', header);
		var sheetInput = qs('[data-gh-m-sheet-input]', header);
		var closeBtn = qs('[data-gh-m-search-close]', header);
		var resultsEl = qs('[data-gh-m-sheet-results]', header);

		if (searchWrap) {
			searchWrap.addEventListener('click', openMobileSearch);
			if (searchInput) {
				searchInput.addEventListener('focus', function (e) {
					e.preventDefault();
					searchInput.blur();
					openMobileSearch();
				});
			}
		}

		if (closeBtn) {
			closeBtn.addEventListener('click', closeMobileSearch);
		}

		// Mobile sheet search
		if (sheetInput && resultsEl) {
			var timer = null;

			var debouncedMobileSearch = debounce(function (q) {
				if (!cfg.ajaxUrl || q.length < 2) {
					resultsEl.innerHTML = '';
					return;
				}
				var fd = new FormData();
				fd.append('action', 'dejoiy_gh_search');
				fd.append('nonce', cfg.nonce || '');
				fd.append('q', q);
				fetch(cfg.ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
					.then(function (r) { return r.json(); })
					.then(function (data) {
						var items = (data && data.results) || [];
						if (!items.length) {
							resultsEl.innerHTML = '<p style="padding:1rem;color:#64748b;text-align:center;">' + (cfg.i18n.noResults || 'No results') + '</p>';
							return;
						}
						resultsEl.innerHTML = items.map(function (item) {
							var thumb = item.thumb
								? '<img src="' + escapeHtml(item.thumb) + '" alt="" width="40" height="40" style="border-radius:8px;object-fit:cover;" />'
								: '<span style="width:40px;height:40px;display:grid;place-items:center;background:#f1f5f9;border-radius:8px;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.1-3.1a2 2 0 0 0-2.8 0L6 21"/></svg></span>';
							return (
								'<a href="' + escapeHtml(item.url) + '" style="display:flex;gap:0.75rem;align-items:center;padding:0.65rem;border-radius:10px;text-decoration:none;color:#0f172a;border-bottom:1px solid rgba(15,23,42,0.04);">' +
								thumb +
								'<div><strong style="font-size:0.85rem;">' + escapeHtml(item.title) + '</strong>' +
								(item.price ? '<br><small style="color:#64748b;">' + item.price + '</small>' : '') +
								'</div></a>'
							);
						}).join('');
					})
					.catch(function () {
						resultsEl.innerHTML = '';
					});
			}, 280);

			sheetInput.addEventListener('input', function () {
				var q = sheetInput.value.trim();
				clearTimeout(timer);
				if (q.length < 2) {
					resultsEl.innerHTML = '';
					return;
				}
				resultsEl.innerHTML = '<p style="padding:1rem;color:#64748b;">Searching…</p>';
				timer = setTimeout(function () {
					debouncedMobileSearch(q);
				}, 50);
			});
		}
	}

	/* ---------------------------------------------------------------
	   CART BADGE SYNC
	   --------------------------------------------------------------- */

	function syncCartBadge() {
		var count = parseInt(cfg.cartCount, 10) || 0;
		qsa('[data-gh-cart-badge]', header).forEach(function (badge) {
			if (count < 1) {
				badge.hidden = true;
				return;
			}
			badge.hidden = false;
			badge.textContent = count > 99 ? '99+' : String(count);
		});
		qsa('[data-gh-m-cart-badge]', header).forEach(function (badge) {
			if (count < 1) {
				badge.hidden = true;
				return;
			}
			badge.hidden = false;
			badge.textContent = count > 99 ? '99+' : String(count);
		});
	}

	document.addEventListener('added_to_cart', syncCartBadge);
	document.addEventListener('removed_from_cart', syncCartBadge);

	/* ---------------------------------------------------------------
	   MOBILE EXPANDABLE EXPLORE PANEL
	   --------------------------------------------------------------- */

	function setupMobileExpand() {
		var mobile = header ? qs('.gh-mobile', header) : null;
		var btn = header ? qs('[data-gh-m-expand]', header) : null;
		if (!mobile || !btn) {
			return;
		}
		btn.addEventListener('click', function () {
			var expanded = mobile.classList.toggle('is-expanded');
			btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
			document.body.classList.toggle('gh-m-expanded-gh', expanded);
		});
	}

	/* ---------------------------------------------------------------
	   MOBILE HEADER CANVAS — playful motion band inside the header
	   Scoped strictly to the header; never overlays the page.
	   --------------------------------------------------------------- */

	var djyCanvas = {
		raf: null,
		running: false,
		mouse: { x: 0.5, y: 0.5 },
		t: 0,
		orbs: [],
		pointerMoved: false,
		colors: [
			[255, 124, 2],
			[255, 45, 85],
			[255, 207, 82],
			[124, 92, 255],
			[67, 20, 6]
		],
		spawn: function () {
			var orbs = [];
			for (var i = 0; i < 4; i++) {
				orbs.push({
					c: this.colors[i % this.colors.length],
					x: Math.random(),
					y: Math.random(),
					r: 0.24 + Math.random() * 0.3,
					vx: (Math.random() - 0.5) * 0.00012,
					vy: (Math.random() - 0.5) * 0.0001,
					ph: Math.random() * Math.PI * 2,
					depth: 0.6 + Math.random() * 0.9
				});
			}
			this.orbs = orbs;
		},
		frame: function () {
			var self = djyCanvas;
			if (!self.running) return;
			self.t += 1;
			var ctx = self.ctx, dpr = self.dpr, W = self.w, H = self.h;
			ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
			ctx.clearRect(0, 0, W, H);
			ctx.globalCompositeOperation = 'lighter';
			for (var i = 0; i < self.orbs.length; i++) {
				var o = self.orbs[i];
				o.x += o.vx;
				o.y += o.vy;
				if (o.x < -0.25) o.x = 1.25;
				if (o.x > 1.25) o.x = -0.25;
				if (o.y < -0.25) o.y = 1.25;
				if (o.y > 1.25) o.y = -0.25;
				var bob = Math.sin(self.t * 0.011 + o.ph) * 0.035;
				var px = self.pointerMoved ? (self.mouse.x - 0.5) * 0.05 * o.depth : 0;
				var py = self.pointerMoved ? (self.mouse.y - 0.5) * 0.05 * o.depth : 0;
				var cx = (o.x + px) * W;
				var cy = (o.y + py + bob) * H;
				var rad = (o.r + Math.sin(self.t * 0.008 + o.ph) * 0.045) * Math.max(W, H) * 0.6;
				if (rad < 8) continue;
				var g = ctx.createRadialGradient(cx, cy, 0, cx, cy, rad);
				g.addColorStop(0, 'rgba(' + o.c.join(',') + ',0.24)');
				g.addColorStop(1, 'rgba(' + o.c.join(',') + ',0)');
				ctx.fillStyle = g;
				ctx.beginPath();
				ctx.arc(cx, cy, rad, 0, Math.PI * 2);
				ctx.fill();
			}
			ctx.globalCompositeOperation = 'source-over';
			self.raf = requestAnimationFrame(self.frame);
		}
	};

	function djyCanvasResize() {
		var self = djyCanvas;
		if (!self.canvas) return;
		var r = self.canvas.getBoundingClientRect();
		self.dpr = Math.min(window.devicePixelRatio || 1, 2);
		self.w = Math.max(1, Math.round(r.width));
		self.h = Math.max(1, Math.round(r.height));
		self.canvas.width = self.w * self.dpr;
		self.canvas.height = self.h * self.dpr;
	}

	function setupMobileCanvas() {
		if (!header) return;
		var canvas = qs('[data-gh-m-canvas]', header);
		if (!canvas) return;
		if (window.matchMedia('(min-width: 1025px)').matches) return;
		if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
		var ctx = canvas.getContext('2d');
		if (!ctx) return;
		var self = djyCanvas;
		self.canvas = canvas;
		self.ctx = ctx;
		djyCanvasResize();
		self.spawn();
		self.running = true;
		window.addEventListener('pointermove', function (e) {
			self.mouse.x = e.clientX / (window.innerWidth || 1);
			self.mouse.y = e.clientY / (window.innerHeight || 1);
			self.pointerMoved = true;
		}, { passive: true });
		window.addEventListener('resize', debounce(djyCanvasResize, 160));
		document.addEventListener('visibilitychange', function () {
			if (document.hidden) {
				self.running = false;
				if (self.raf) cancelAnimationFrame(self.raf);
			} else if (self.canvas) {
				self.running = true;
				self.raf = requestAnimationFrame(self.frame);
			}
		}, false);
		self.raf = requestAnimationFrame(self.frame);
	}

	/* ---------------------------------------------------------------
	   MOBILE CHIPS — horizontal scroll active indicator
	   --------------------------------------------------------------- */

	function setupMobileChips() {
		var chips = qs('[data-gh-m-chips]', header);
		if (!chips) {
			return;
		}
		// Scroll active chip into view
		var active = chips.querySelector('.is-active');
		if (active) {
			setTimeout(function () {
				active.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
			}, 300);
		}
	}

	/* ---------------------------------------------------------------
	   INIT
	   --------------------------------------------------------------- */

	setupAccountDropdown();
	setupMegaMenu();
	setupDesktopSearch();
	setupMobileDrawer();
	setupMobileSearch();
	setupMobileExpand();
	setupMobileCanvas();
	setupMobileChips();
	syncCartBadge();

	// Mark header ready
	header.classList.add('gh--ready');
})();
