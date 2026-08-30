/**
 * DEJOIY QuickMart — pincode, cart drawer, search recent, flash timer.
 */
(function ($) {
	'use strict';

	var cfg = window.dejoiyQuickmart || {};
	var RECENT_KEY = 'dejoiy_qm_recent_searches';
	var RECENT_MAX = 8;

	function getRecent() {
		try {
			var raw = localStorage.getItem(RECENT_KEY);
			var list = raw ? JSON.parse(raw) : [];
			return Array.isArray(list) ? list : [];
		} catch (e) {
			return [];
		}
	}

	function saveRecent(list) {
		try {
			localStorage.setItem(RECENT_KEY, JSON.stringify(list.slice(0, RECENT_MAX)));
		} catch (e) {
			/* ignore */
		}
	}

	function addRecent(term, thumbUrl) {
		term = (term || '').trim();
		if (!term) {
			return;
		}
		var list = getRecent().filter(function (item) {
			return item.term !== term;
		});
		list.unshift({ term: term, thumb: thumbUrl || '' });
		saveRecent(list);
	}

	function searchPageUrl(q) {
		var base = cfg.searchUrl || cfg.homeUrl || '/dejoiy-quick-mart/';
		var url = new URL(base, window.location.origin);
		url.searchParams.set('qm_view', 'search');
		if (q) {
			url.searchParams.set('q', q);
		} else {
			url.searchParams.delete('q');
		}
		return url.toString();
	}

	function renderRecent() {
		var listEl = document.getElementById('qm-recent-list');
		var clearBtn = document.getElementById('qm-recent-clear');
		if (!listEl) {
			return;
		}
		var list = getRecent();
		listEl.innerHTML = '';
		if (!list.length) {
			if (clearBtn) {
				clearBtn.hidden = true;
			}
			return;
		}
		if (clearBtn) {
			clearBtn.hidden = false;
		}
		list.forEach(function (item) {
			var a = document.createElement('a');
			a.className = 'qm-recent__chip';
			a.href = searchPageUrl(item.term);
			if (item.thumb) {
				var img = document.createElement('img');
				img.className = 'qm-recent__thumb';
				img.src = item.thumb;
				img.alt = '';
				img.width = 28;
				img.height = 28;
				a.appendChild(img);
			}
			var span = document.createElement('span');
			span.textContent = item.term;
			a.appendChild(span);
			listEl.appendChild(a);
		});
	}

	function clearRecent() {
		saveRecent([]);
		renderRecent();
	}

	function openPin() {
		var modal = document.getElementById('qm-pin-modal');
		if (modal) {
			modal.hidden = false;
			var input = document.getElementById('qm-pin-input');
			if (input) {
				input.focus();
			}
		}
	}

	function closePin() {
		var modal = document.getElementById('qm-pin-modal');
		if (modal) {
			modal.hidden = true;
		}
	}

	function openCart() {
		var drawer = document.getElementById('qm-cart-drawer');
		if (drawer) {
			drawer.hidden = false;
			drawer.setAttribute('aria-hidden', 'false');
			document.body.classList.add('qm-drawer-open');
		}
	}

	function closeCart() {
		var drawer = document.getElementById('qm-cart-drawer');
		if (drawer) {
			drawer.hidden = true;
			drawer.setAttribute('aria-hidden', 'true');
			document.body.classList.remove('qm-drawer-open');
		}
	}

	function updateBadge(count) {
		var badges = document.querySelectorAll('.qm-top__cart-badge, .qm-header__cart-badge');
		var btn = document.querySelector('.qm-top__cart-btn') || document.querySelector('.qm-header__cart');
		if (count < 1) {
			badges.forEach(function (b) {
				b.remove();
			});
			return;
		}
		var text = count > 9 ? '9+' : String(count);
		if (badges.length) {
			badges.forEach(function (b) {
				b.textContent = text;
			});
			return;
		}
		if (!btn) {
			return;
		}
		var badge = document.createElement('span');
		badge.className = btn.classList.contains('qm-top__cart-btn')
			? 'qm-top__cart-badge'
			: 'qm-header__cart-badge';
		badge.textContent = text;
		btn.style.position = 'relative';
		btn.appendChild(badge);
	}

	function savePincode() {
		var input = document.getElementById('qm-pin-input');
		var err = document.getElementById('qm-pin-error');
		if (!input || !cfg.ajaxUrl) {
			return;
		}
		var pin = (input.value || '').replace(/\D/g, '');
		if (pin.length !== 6) {
			if (err) {
				err.hidden = false;
				err.textContent = 'Enter a valid 6-digit pincode';
			}
			return;
		}
		var body = new FormData();
		body.append('action', 'dejoiy_quickmart_pincode');
		body.append('nonce', cfg.nonce);
		body.append('pincode', pin);
		fetch(cfg.ajaxUrl, { method: 'POST', body: body, credentials: 'same-origin' })
			.then(function (r) {
				return r.json();
			})
			.then(function (data) {
				if (data && data.success) {
					closePin();
					window.location.reload();
					return;
				}
				if (err) {
					err.hidden = false;
					err.textContent =
						(data && data.data && data.data.message) ||
						'Pincode not serviceable yet';
				}
			})
			.catch(function () {
				if (err) {
					err.hidden = false;
					err.textContent = 'Could not verify pincode. Try again.';
				}
			});
	}

	function initFlashTimer() {
		var el = document.querySelector('[data-qm-flash-timer]');
		if (!el) {
			return;
		}
		var end = Date.now() + 3 * 60 * 60 * 1000;
		function tick() {
			var left = Math.max(0, end - Date.now());
			var h = Math.floor(left / 3600000);
			var m = Math.floor((left % 3600000) / 60000);
			var s = Math.floor((left % 60000) / 1000);
			el.textContent =
				String(h).padStart(2, '0') +
				':' +
				String(m).padStart(2, '0') +
				':' +
				String(s).padStart(2, '0');
			if (left > 0) {
				requestAnimationFrame(tick);
			}
		}
		tick();
	}

	function initSearchPage() {
		var input = document.getElementById('qm-search-page-input');
		var form = input ? input.closest('form') : null;
		if (input) {
			setTimeout(function () {
				input.focus();
				if (input.setSelectionRange && input.value) {
					input.setSelectionRange(input.value.length, input.value.length);
				}
			}, 120);
		}
		renderRecent();

		var clearBtn = document.getElementById('qm-recent-clear');
		if (clearBtn) {
			clearBtn.addEventListener('click', clearRecent);
		}

		document.querySelectorAll('.qm-trending__item').forEach(function (el) {
			el.addEventListener('click', function () {
				var label = el.querySelector('.qm-trending__label');
				var img = el.querySelector('.qm-trending__img');
				var term = label ? label.textContent.trim() : '';
				var thumb = img && img.src ? img.src : '';
				if (term) {
					addRecent(term, thumb);
				}
			});
		});

		if (form) {
			form.addEventListener('submit', function () {
				var q = (input.value || '').trim();
				if (q) {
					addRecent(q, '');
				}
			});
		}

		if (input && input.value.trim()) {
			addRecent(input.value.trim(), '');
		}
	}

	function bind() {
		document.querySelectorAll('[data-qm-open-pin]').forEach(function (el) {
			el.addEventListener('click', openPin);
		});
		document.querySelectorAll('[data-qm-close-pin]').forEach(function (el) {
			el.addEventListener('click', closePin);
		});
		document.querySelectorAll('[data-qm-open-cart]').forEach(function (el) {
			el.addEventListener('click', openCart);
		});
		document.querySelectorAll('[data-qm-close-cart]').forEach(function (el) {
			el.addEventListener('click', closeCart);
		});
		var submit = document.getElementById('qm-pin-submit');
		if (submit) {
			submit.addEventListener('click', savePincode);
		}
		if (!cfg.hasPin && !cfg.isSearchView) {
			setTimeout(openPin, 400);
		}
		updateBadge(cfg.cartCount || 0);
		initFlashTimer();
		if (cfg.isSearchView || document.getElementById('qm-search-page-input')) {
			initSearchPage();
		}
	}

	$(document.body).on('added_to_cart', function (e, fragments) {
		if (fragments && fragments.qm_cart_count) {
			updateBadge(parseInt(fragments.qm_cart_count, 10) || 0);
		} else {
			updateBadge((cfg.cartCount || 0) + 1);
		}
		openCart();
	});

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', bind);
	} else {
		bind();
	}
})(jQuery);
