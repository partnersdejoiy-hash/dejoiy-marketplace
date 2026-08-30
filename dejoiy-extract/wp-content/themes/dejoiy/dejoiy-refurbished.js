(function () {
	'use strict';

	var cfg = window.dejoiyRefurbished || {};
	var body = document.body;
	if (!body || !body.classList.contains('dejoiy-refurbished-app')) {
		return;
	}

	/* Sticky header */
	var header = document.getElementById('drf-header');
	if (header) {
		var onScroll = function () {
			header.classList.toggle('is-scrolled', window.scrollY > 8);
		};
		window.addEventListener('scroll', onScroll, { passive: true });
		onScroll();
	}

	/* Mobile search sheet */
	var mobileSearch = document.getElementById('drf-mobile-search');
	var searchSheet = document.getElementById('drf-search-sheet');
	var searchInputSheet = document.getElementById('drf-search-input-sheet');
	var activeInput = null;

	function openSearchSheet() {
		if (!searchSheet) {
			return;
		}
		searchSheet.hidden = false;
		searchSheet.setAttribute('aria-hidden', 'false');
		body.classList.add('drf-search-open');
		if (searchInputSheet) {
			activeInput = searchInputSheet;
			setTimeout(function () {
				searchInputSheet.focus();
				if (searchInputSheet.value.trim().length < 2) {
					renderSuggestions(searchInputSheet, document.getElementById('drf-search-dropdown-sheet'));
				}
			}, 60);
		}
	}

	function closeSearchSheet() {
		if (!searchSheet) {
			return;
		}
		searchSheet.hidden = true;
		searchSheet.setAttribute('aria-hidden', 'true');
		body.classList.remove('drf-search-open');
		hideAllDropdowns();
	}

	if (mobileSearch) {
		mobileSearch.addEventListener('click', openSearchSheet);
	}
	if (searchSheet) {
		searchSheet.querySelectorAll('[data-drf-search-close]').forEach(function (el) {
			el.addEventListener('click', closeSearchSheet);
		});
	}

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && body.classList.contains('drf-search-open')) {
			closeSearchSheet();
		}
	});

	/* Reveal on scroll */
	if ('IntersectionObserver' in window) {
		var io = new IntersectionObserver(
			function (entries) {
				entries.forEach(function (entry) {
					if (entry.isIntersecting) {
						entry.target.classList.add('is-visible');
						io.unobserve(entry.target);
					}
				});
			},
			{ threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
		);
		document.querySelectorAll('[data-reveal]').forEach(function (el) {
			io.observe(el);
		});
	} else {
		document.querySelectorAll('[data-reveal]').forEach(function (el) {
			el.classList.add('is-visible');
		});
	}

	/* Animated stat counters */
	function animateCount(el) {
		var target = parseFloat(el.getAttribute('data-drf-count') || '0');
		var suffix = el.getAttribute('data-drf-suffix') || '';
		var decimals = parseInt(el.getAttribute('data-drf-decimal') || '0', 10);
		var start = 0;
		var duration = 1400;
		var t0 = performance.now();
		function frame(now) {
			var p = Math.min(1, (now - t0) / duration);
			var eased = 1 - Math.pow(1 - p, 3);
			var val = start + (target - start) * eased;
			el.textContent = (decimals ? val.toFixed(decimals) : Math.round(val).toLocaleString()) + suffix;
			if (p < 1) {
				requestAnimationFrame(frame);
			}
		}
		requestAnimationFrame(frame);
	}

	if ('IntersectionObserver' in window) {
		var cio = new IntersectionObserver(
			function (entries) {
				entries.forEach(function (entry) {
					if (entry.isIntersecting) {
						animateCount(entry.target);
						cio.unobserve(entry.target);
					}
				});
			},
			{ threshold: 0.3 }
		);
		document.querySelectorAll('[data-drf-count]').forEach(function (el) {
			cio.observe(el);
		});
	}

	/* Battery ring gauge */
	var gauge = document.querySelector('[data-drf-gauge]');
	if (gauge) {
		var ioGauge = new IntersectionObserver(
			function (entries) {
				if (entries[0].isIntersecting) {
					gauge.style.strokeDashoffset = String(327 * (1 - 0.92));
					ioGauge.disconnect();
				}
			},
			{ threshold: 0.2 }
		);
		ioGauge.observe(gauge.closest('.drf-battery') || gauge);
	}

	/* Search — refurbished products + pages */
	var searchTimer;
	var popular = ['iPhone 14', 'MacBook Air', 'Samsung Galaxy', 'Washing machine', 'Warranty'];
	var recentKey = 'drf_recent_searches';

	var dropdownMap = {
		'drf-search-input': 'drf-search-dropdown',
		'drf-search-input-mobile': 'drf-search-dropdown-mobile',
		'drf-search-input-sheet': 'drf-search-dropdown-sheet',
	};

	function getDropdownForInput(input) {
		if (!input || !input.id) {
			return null;
		}
		var id = dropdownMap[input.id];
		return id ? document.getElementById(id) : null;
	}

	function hideAllDropdowns() {
		Object.keys(dropdownMap).forEach(function (inputId) {
			var dd = document.getElementById(dropdownMap[inputId]);
			if (dd) {
				dd.hidden = true;
			}
		});
	}

	function getRecent() {
		try {
			return JSON.parse(localStorage.getItem(recentKey) || '[]');
		} catch (e) {
			return [];
		}
	}

	function saveRecent(q) {
		var list = getRecent().filter(function (x) {
			return x !== q;
		});
		list.unshift(q);
		localStorage.setItem(recentKey, JSON.stringify(list.slice(0, 5)));
	}

	function submitSearch(input, q) {
		if (!input || !q) {
			return;
		}
		input.value = q;
		saveRecent(q);
		if (input.form) {
			input.form.submit();
		}
	}

	function renderProductItem(item) {
		var thumb = item.thumb
			? '<img src="' + item.thumb + '" alt="" width="40" height="40" style="border-radius:8px;object-fit:cover" />'
			: '<span class="drf-search__item-icon" aria-hidden="true">📱</span>';
		return (
			'<a class="drf-search__item drf-search__item--product" href="' +
			item.url +
			'">' +
			thumb +
			'<div><strong>' +
			item.title +
			'</strong><br><small>Grade ' +
			(item.grade || 'A') +
			(item.price ? ' · ' + item.price : '') +
			'</small></div></a>'
		);
	}

	function renderPageItem(item) {
		return (
			'<a class="drf-search__item drf-search__item--page" href="' +
			item.url +
			'"><span class="drf-search__item-icon" aria-hidden="true">📄</span><div><strong>' +
			item.title +
			'</strong><br><small>' +
			(item.badge || 'Page') +
			'</small></div></a>'
		);
	}

	function renderAjaxResults(dropdown, products, pages) {
		if (!dropdown) {
			return;
		}
		var html = '';
		if (products.length) {
			html += '<div class="drf-search__section"><div class="drf-search__section-title">Refurbished</div>';
			html += products.map(renderProductItem).join('');
			html += '</div>';
		}
		if (pages.length) {
			html += '<div class="drf-search__section"><div class="drf-search__section-title">Pages</div>';
			html += pages.map(renderPageItem).join('');
			html += '</div>';
		}
		if (!html) {
			html = '<p class="drf-search__empty">No refurbished devices or pages found.</p>';
		}
		dropdown.innerHTML = html;
		dropdown.hidden = false;
	}

	function renderSuggestions(input, dropdown) {
		if (!dropdown) {
			return;
		}
		var recent = getRecent();
		var html = '';
		if (recent.length) {
			html += '<div class="drf-search__section"><div class="drf-search__section-title">Recent</div>';
			recent.forEach(function (q) {
				html += '<button type="button" class="drf-search__chip" data-q="' + q.replace(/"/g, '&quot;') + '">' + q + '</button>';
			});
			html += '</div>';
		}
		html += '<div class="drf-search__section"><div class="drf-search__section-title">Popular</div>';
		popular.forEach(function (q) {
			html += '<button type="button" class="drf-search__chip" data-q="' + q + '">' + q + '</button>';
		});
		html += '</div>';
		dropdown.innerHTML = html;
		dropdown.hidden = false;
		dropdown.querySelectorAll('[data-q]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				submitSearch(input, btn.getAttribute('data-q'));
			});
		});
	}

	function bindSearch(input) {
		if (!input) {
			return;
		}
		var dropdown = getDropdownForInput(input);

		input.addEventListener('focus', function () {
			activeInput = input;
			if (input.value.trim().length < 2) {
				renderSuggestions(input, dropdown);
			}
		});

		input.addEventListener('input', function () {
			var q = input.value.trim();
			activeInput = input;
			dropdown = getDropdownForInput(input);
			clearTimeout(searchTimer);
			if (q.length < 2) {
				renderSuggestions(input, dropdown);
				return;
			}
			searchTimer = setTimeout(function () {
				if (!cfg.ajaxUrl || !dropdown) {
					return;
				}
				var fd = new FormData();
				fd.append('action', 'dejoiy_refurbished_search');
				fd.append('nonce', cfg.nonce || '');
				fd.append('q', q);
				fetch(cfg.ajaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
					.then(function (r) {
						return r.json();
					})
					.then(function (data) {
						var payload = (data && data.data) || data || {};
						var products = payload.products || [];
						var pages = payload.pages || [];
						if (!products.length && !pages.length && payload.items && payload.items.length) {
							products = payload.items.filter(function (i) {
								return i.type === 'product' || i.grade;
							});
							pages = payload.items.filter(function (i) {
								return i.type === 'page';
							});
						}
						renderAjaxResults(dropdown, products, pages);
					})
					.catch(function () {
						if (dropdown) {
							dropdown.hidden = true;
						}
					});
			}, 280);
		});

		if (input.form) {
			input.form.addEventListener('submit', function () {
				if (input.value.trim()) {
					saveRecent(input.value.trim());
				}
				closeSearchSheet();
			});
		}
	}

	document.querySelectorAll('[data-drf-search-input]').forEach(bindSearch);

	document.addEventListener('click', function (e) {
		if (body.classList.contains('drf-search-open')) {
			return;
		}
		if (!e.target.closest('.drf-search') && !e.target.closest('.drf-search-sheet__results')) {
			hideAllDropdowns();
		}
	});

	/* Gallery thumbs */
	document.querySelectorAll('.drf-viewer__thumb').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var src = btn.getAttribute('data-src');
			var main = document.getElementById('drf-main-img');
			if (main && src) {
				main.src = src;
			}
			document.querySelectorAll('.drf-viewer__thumb').forEach(function (t) {
				t.classList.remove('is-active');
			});
			btn.classList.add('is-active');
		});
	});

	/* Health bars */
	setTimeout(function () {
		document.querySelectorAll('.drf-health__bar span').forEach(function (bar) {
			var val = bar.getAttribute('data-value') || '0';
			bar.style.width = val + '%';
		});
	}, 400);

	/* Countdown */
	var cd = document.querySelector('[data-drf-countdown]');
	if (cd) {
		var end = Date.now() + 12 * 60 * 60 * 1000;
		setInterval(function () {
			var diff = Math.max(0, end - Date.now());
			var h = Math.floor(diff / 3600000);
			var m = Math.floor((diff % 3600000) / 60000);
			var s = Math.floor((diff % 60000) / 1000);
			cd.textContent =
				String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
		}, 1000);
	}

	/* Compare */
	var COMPARE_KEY = 'drf_compare';
	var compareBar = document.getElementById('drf-compare');
	var compareCount = document.getElementById('drf-compare-count');
	var compareClear = document.getElementById('drf-compare-clear');

	function getCompare() {
		try {
			return JSON.parse(localStorage.getItem(COMPARE_KEY) || '[]');
		} catch (e) {
			return [];
		}
	}
	function setCompare(list) {
		localStorage.setItem(COMPARE_KEY, JSON.stringify(list));
		if (compareCount) {
			compareCount.textContent = String(list.length);
		}
		if (compareBar) {
			compareBar.hidden = list.length === 0;
		}
	}
	setCompare(getCompare());

	document.querySelectorAll('[data-drf-compare]').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var id = btn.getAttribute('data-drf-compare');
			if (!id) {
				return;
			}
			var list = getCompare();
			if (list.indexOf(id) === -1 && list.length < 3) {
				list.push(id);
				setCompare(list);
			}
		});
	});
	if (compareClear) {
		compareClear.addEventListener('click', function () {
			setCompare([]);
		});
	}

	/* Particles */
	var canvas = document.getElementById('drf-particles');
	if (canvas && canvas.getContext) {
		var ctx = canvas.getContext('2d');
		var particles = [];
		var count = window.innerWidth < 768 ? 20 : 40;

		function resize() {
			canvas.width = canvas.offsetWidth;
			canvas.height = canvas.offsetHeight;
		}
		resize();
		window.addEventListener('resize', resize, { passive: true });

		for (var i = 0; i < count; i++) {
			particles.push({
				x: Math.random() * canvas.width,
				y: Math.random() * canvas.height,
				r: Math.random() * 2 + 0.5,
				vx: (Math.random() - 0.5) * 0.35,
				vy: (Math.random() - 0.5) * 0.35,
			});
		}

		function draw() {
			if (!ctx) {
				return;
			}
			ctx.clearRect(0, 0, canvas.width, canvas.height);
			ctx.fillStyle = 'rgba(0, 207, 255, 0.28)';
			particles.forEach(function (p) {
				p.x += p.vx;
				p.y += p.vy;
				if (p.x < 0 || p.x > canvas.width) {
					p.vx *= -1;
				}
				if (p.y < 0 || p.y > canvas.height) {
					p.vy *= -1;
				}
				ctx.beginPath();
				ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
				ctx.fill();
			});
			requestAnimationFrame(draw);
		}
		draw();
	}
})();
