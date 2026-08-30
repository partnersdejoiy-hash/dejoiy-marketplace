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
					: '<span style="width:40px;height:40px;display:grid;place-items:center;background:#f1f5f9;border-radius:8px;font-size:1.2rem">📦</span>';
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
								: '<span style="width:40px;height:40px;display:grid;place-items:center;background:#f1f5f9;border-radius:8px;">📦</span>';
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
	setupMobileChips();
	syncCartBadge();

	// Mark header ready
	header.classList.add('gh--ready');
})();
