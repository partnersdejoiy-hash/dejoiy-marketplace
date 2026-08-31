/**
 * DEJOIY Desktop Marketplace Header — sticky, search, dropdowns (≥1025px only).
 */
(function () {
	'use strict';

	var mq = window.matchMedia('(min-width: 1025px)');
	var cfg = window.dejoiyDmHeader || {};
	var header = document.getElementById('dejoiy-marketplace-header');

	if (!header) {
		return;
	}

	if (!mq.matches) {
		return;
	}

	header.removeAttribute('hidden');
	header.setAttribute('aria-hidden', 'false');

	/* Trigger nav stagger animation for Three.js header */
	setTimeout(function () {
		var sub = header.querySelector('.dmh__sub');
		if (sub) sub.classList.add('is-visible');
	}, 180);

	var sticky = header.querySelector('.dmh__sticky');
	var searchInput = header.querySelector('[data-dmh-search-input]');
	var searchPanel = document.getElementById('dmh-search-panel');
	var searchResults = document.getElementById('dmh-search-results');
	var searchClose = header.querySelector('[data-dmh-search-close]');
	var searchTimer = null;

	if (sticky) {
		var onScroll = function () {
			var scrolled = window.scrollY > 6;
			sticky.classList.toggle('is-scrolled', scrolled);
			header.classList.toggle('dmh--scrolled', scrolled);
		};
		window.addEventListener('scroll', onScroll, { passive: true });
		onScroll();
	}

	function setSearchOpen(open) {
		header.classList.toggle('dmh--search-open', !!open);
	}

	function closeSearchPanel() {
		if (searchResults) {
			searchResults.hidden = true;
			searchResults.innerHTML = '';
		}
		if (searchPanel) {
			searchPanel.hidden = true;
		}
		setSearchOpen(false);
	}

	function openSearchPanel() {
		if (searchPanel) {
			searchPanel.hidden = false;
		}
	}

	function closeAllDropdowns(except) {
		header.querySelectorAll('[data-dmh-lang-menu],[data-dmh-account-menu],[data-dmh-catlist-panel]').forEach(function (el) {
			if (el !== except) {
				el.hidden = true;
			}
		});
		header.querySelectorAll('[data-dmh-lang-toggle],[data-dmh-account-toggle],[data-dmh-catlist-toggle]').forEach(function (btn) {
			if (!except || (except !== catListPanel && !except.contains(btn))) {
				btn.setAttribute('aria-expanded', 'false');
			}
		});
		if (!except || except !== catListPanel) {
			if (catListWrap) {
				catListWrap.classList.remove('is-open');
			}
			header.classList.remove('dmh--cat-open');
		}
		if (searchPanel && (!except || !except.contains(searchInput))) {
			closeSearchPanel();
		}
	}

	/* All Categories dropdown */
	var catListWrap = header.querySelector('[data-dmh-dropdown-wrap]');
	var catListToggle = header.querySelector('[data-dmh-catlist-toggle]');
	var catListPanel = header.querySelector('[data-dmh-catlist-panel]');

	function setCatDropOpen(open) {
		if (!catListPanel || !catListToggle) {
			return;
		}
		catListPanel.hidden = !open;
		catListToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		if (catListWrap) {
			catListWrap.classList.toggle('is-open', !!open);
		}
		header.classList.toggle('dmh--cat-open', !!open);
	}

	if (catListToggle && catListPanel) {
		catListToggle.addEventListener('click', function (e) {
			e.preventDefault();
			e.stopPropagation();
			var open = catListPanel.hidden;
			closeAllDropdowns(open ? catListPanel : null);
			setCatDropOpen(open);
		});
	}

	/* Language dropdown */
	var langToggle = header.querySelector('[data-dmh-lang-toggle]');
	var langMenu = header.querySelector('[data-dmh-lang-menu]');
	if (langToggle && langMenu) {
		langToggle.addEventListener('click', function (e) {
			e.stopPropagation();
			var open = langMenu.hidden;
			closeAllDropdowns(open ? langMenu : null);
			langMenu.hidden = !open;
			langToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
		langMenu.querySelectorAll('[data-lang]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				try {
					localStorage.setItem('dejoiy_dm_lang', btn.getAttribute('data-lang') || 'en');
				} catch (err) {
					/* ignore */
				}
				var label = btn.textContent.trim();
				var span = langToggle.querySelector('span');
				if (span) {
					span.textContent = label;
				}
				langMenu.hidden = true;
				langToggle.setAttribute('aria-expanded', 'false');
			});
		});
	}

	/* Account dropdown */
	var accountToggle = header.querySelector('[data-dmh-account-toggle]');
	var accountMenu = header.querySelector('[data-dmh-account-menu]');
	if (accountToggle && accountMenu) {
		accountToggle.addEventListener('click', function (e) {
			e.stopPropagation();
			var open = accountMenu.hidden;
			closeAllDropdowns(open ? accountMenu : null);
			accountMenu.hidden = !open;
			accountToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
	}

	function renderResults(items) {
		if (!searchResults) {
			return;
		}
		openSearchPanel();
		if (!items || !items.length) {
			searchResults.innerHTML =
				'<p class="dmh-search__empty">' + (cfg.i18n && cfg.i18n.noResults ? cfg.i18n.noResults : 'No results') + '</p>';
			searchResults.hidden = false;
			setSearchOpen(true);
			return;
		}
		searchResults.innerHTML = items
			.map(function (item) {
				var thumb = item.thumb
					? '<img src="' + item.thumb + '" alt="" width="40" height="40" />'
					: '<span style="width:40px;height:40px;display:grid;place-items:center;background:#f1f5f9;border-radius:8px">📦</span>';
				var meta = item.meta ? '<span class="dmh-search__result-meta">' + item.meta + '</span>' : '';
				return (
					'<a class="dmh-search__result" href="' +
					item.url +
					'">' +
					thumb +
					'<div><span class="dmh-search__result-badge">' +
					(item.badge || item.eco || 'Product') +
					'</span><br><strong>' +
					item.title +
					'</strong>' +
					meta +
					(item.price ? '<br><small>' + item.price + '</small>' : '') +
					'</div></a>'
				);
			})
			.join('');
		searchResults.hidden = false;
		setSearchOpen(true);
	}

	function runSearch(q) {
		if (!cfg.ajaxUrl || q.length < 2) {
			return;
		}
		var fd = new FormData();
		fd.append('action', 'dejoiy_joi_search');
		fd.append('nonce', cfg.nonce || '');
		fd.append('q', q);
		fd.append('scope', 'all');
		fetch(cfg.ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
			.then(function (r) {
				return r.json();
			})
			.then(function (data) {
				renderResults((data && data.results) || []);
			})
			.catch(function () {
				closeSearchPanel();
			});
	}

	if (searchClose) {
		searchClose.addEventListener('click', function (e) {
			e.preventDefault();
			e.stopPropagation();
			closeSearchPanel();
		});
	}

	if (searchInput) {
		searchInput.addEventListener('input', function () {
			var q = searchInput.value.trim();
			clearTimeout(searchTimer);
			if (q.length < 2) {
				closeSearchPanel();
				return;
			}
			searchTimer = setTimeout(function () {
				runSearch(q);
			}, 280);
		});
		searchInput.addEventListener('focus', function () {
			var q = searchInput.value.trim();
			if (q.length >= 2) {
				runSearch(q);
			}
		});
	}

	function syncCartBadge() {
		var badge = header.querySelector('[data-dmh-cart-badge]');
		if (!badge) {
			return;
		}
		var count = parseInt(cfg.cartCount, 10) || 0;
		if (count < 1) {
			badge.hidden = true;
			return;
		}
		badge.hidden = false;
		badge.textContent = count > 99 ? '99+' : String(count);
	}
	syncCartBadge();
	document.addEventListener('added_to_cart', syncCartBadge);
	document.addEventListener('removed_from_cart', syncCartBadge);

	document.addEventListener('click', function (e) {
		if (!header.contains(e.target)) {
			closeAllDropdowns();
		}
	});
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') {
			closeAllDropdowns();
		}
	});
})();
