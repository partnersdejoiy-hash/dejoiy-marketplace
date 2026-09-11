/**
 * DEJOIY Image Fix — client side backstop.
 *
 * 1) Swaps any image still marked as lazy (real url parked in data-src /
 *    data-original) back into src, so product photos appear everywhere
 *    (shop, categories, cart, single product gallery, quick view).
 * 2) Turns WooCommerce "no image" placeholders (and broken product images)
 *    into colourful DEJOIY canva cards using the product initial + gradient.
 *
 * Runs on DOM ready, on page show, and on a MutationObserver so AJAX-injected
 * content (quick view, cart fragments) is fixed as soon as it appears.
 */
(function () {
	'use strict';

	var PALETTES = [
		['#7c3aed', '#db2777'],
		['#2563eb', '#06b6d4'],
		['#059669', '#22d3ee'],
		['#ea580c', '#f472b6'],
		['#4338ca', '#a855f7'],
		['#0f766e', '#84cc16']
	];

	function canvaCard(seed) {
		var s = String(seed || '').trim().toLowerCase() || 'dejoiy';
		var h = 0;
		for (var i = 0; i < s.length; i++) {
			h = (h * 31 + s.charCodeAt(i)) | 0;
		}
		var g = PALETTES[Math.abs(h) % PALETTES.length];
		var initial = s.charAt(0).toUpperCase();
		var svg =
			'<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 400 400">' +
			'<defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1">' +
			'<stop offset="0" stop-color="' + g[0] + '"/><stop offset="1" stop-color="' + g[1] + '"/></linearGradient>' +
			'<radialGradient id="r" cx="0.5" cy="0.28" r="0.85">' +
			'<stop offset="0" stop-color="#ffffff" stop-opacity="0.24"/><stop offset="1" stop-color="#ffffff" stop-opacity="0"/></radialGradient></defs>' +
			'<rect width="400" height="400" fill="url(#g)"/>' +
			'<rect width="400" height="400" fill="url(#r)"/>' +
			'<circle cx="322" cy="74" r="34" fill="#ffffff" fill-opacity="0.14"/>' +
			'<circle cx="58" cy="336" r="22" fill="#ffffff" fill-opacity="0.1"/>' +
			'<text x="200" y="238" font-family="Arial, sans-serif" font-size="150" font-weight="800" text-anchor="middle" dominant-baseline="central" fill="#ffffff">' + initial + '</text>' +
			'<rect x="30" y="330" width="96" height="28" rx="14" fill="#ffffff" fill-opacity="0.92"/>' +
			'<text x="78" y="348" font-family="Arial, sans-serif" font-size="14" font-weight="800" text-anchor="middle" fill="#0f172a">DEJOIY</text>' +
			'</svg>';
		return 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svg);
	}

	function initialFor(img) {
		var root = img.closest ? img.closest('.product, li.product, .cart_item, .mpb, [class*="product"]') : null;
		if (!root) {
			root = img.parentElement ? img.parentElement.parentElement : null;
		}
		if (root) {
			var t = root.querySelector('.product-title, .product-name, .dcart-item__title, .woocommerce-loop-product__title, h3, h2');
			if (t && t.textContent) {
				return t.textContent.trim();
			}
		}
		return img.getAttribute('alt') || img.dataset.name || 'DEJOIY';
	}

	function isLazy(img) {
		return img.getAttribute('data-src') || img.getAttribute('data-original') || img.getAttribute('data-lazy-src');
	}

	function swapLazy(img) {
		var ds = img.getAttribute('data-src');
		if (ds) {
			img.setAttribute('src', ds);
		}
		var do2 = img.getAttribute('data-original');
		if (do2) {
			img.setAttribute('src', do2);
		}
		var dl = img.getAttribute('data-lazy-src');
		if (dl) {
			img.setAttribute('src', dl);
		}
		var dss = img.getAttribute('data-srcset');
		if (dss) {
			img.setAttribute('srcset', dss);
		}
		var dss2 = img.getAttribute('data-sizes');
		if (dss2) {
			img.setAttribute('sizes', dss2);
		}
		img.removeAttribute('data-src');
		img.removeAttribute('data-original');
		img.removeAttribute('data-lazy-src');
		img.removeAttribute('data-srcset');
		img.classList.remove('lazyload', 'lazyload-lqip', 'lazyload-simple', 'et-lazyload-fadeIn');
		if (img.getAttribute('loading') !== 'eager') {
			img.setAttribute('loading', 'eager');
		}
	}

	function convertToCard(img) {
		if (img.dataset.dejoiyCanva) {
			return;
		}
		img.dataset.dejoiyCanva = '1';
		img.classList.add('dejoiy-canva-card');
		img.classList.remove('woocommerce-placeholder');
		img.setAttribute('src', canvaCard(initialFor(img)));
		img.removeAttribute('srcset');
		img.removeAttribute('data-srcset');
		img.removeAttribute('data-src');
	}

	function looksBroken(img) {
		var src = (img.getAttribute('src') || '').toLowerCase();
		return src === '' ||
			src.indexOf('placeholder') !== -1 ||
			src.indexOf('placehold') !== -1 ||
			src.indexOf('no-image') !== -1 ||
			/\/wp-content\/uploads\/[^\s"]*.png$/.test(src) && img.complete && img.naturalWidth === 0;
	}

	function walk(root) {
		var nodes = (root || document).querySelectorAll ? (root || document).querySelectorAll('img') : [];
		nodes.forEach(function (img) {
			if (isLazy(img)) {
				swapLazy(img);
			}
			var isPlaceholder = img.classList.contains('woocommerce-placeholder') ||
				img.classList.contains('wp-post-image') && looksBroken(img);
			if (isPlaceholder && !img.dataset.dejoiyCanva) {
				convertToCard(img);
			} else if (img.complete && img.naturalWidth === 0 && srcLooksLikeProduct(img)) {
				convertToCard(img);
			}
		});
	}

	function srcLooksLikeProduct(img) {
		var src = (img.getAttribute('src') || '').toLowerCase();
		return src !== '' && src.indexOf('data:image') !== 0 && (/\/uploads\//.test(src) || /\.(png|jpe?g|webp|gif)(\?|$)/.test(src));
	}

	function boot() {
		if (window.__dejoiyImgFix) {
			return;
		}
		window.__dejoiyImgFix = true;
		walk(document);

		var mo;
		try {
			mo = new MutationObserver(function (muts) {
				muts.forEach(function (m) {
					walk(m.target);
				});
			});
			mo.observe(document.documentElement, { childList: true, subtree: true });
		} catch (e) { /* older browsers */ }
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
	window.addEventListener('pageshow', function () {
		walk(document);
	});
})();