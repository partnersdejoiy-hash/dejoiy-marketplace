/**
 * DEJOIY mobile single product — layout, buy bar, recommendations placement.
 */
(function () {
	'use strict';

	var isProduct = document.body.classList.contains('dejoiy-mobile-product-view');
	var isCheckout = document.body.classList.contains('dejoiy-mobile-checkout-view');

	if (!isProduct && !isCheckout) {
		return;
	}

	var CART_LABEL = 'Add To Cart';
	var BUY_LABEL = 'Buy Now';

	var cartBtnSelector =
		'.single_add_to_cart_button:not(.et-single-buy-now):not(.buy_now_button):not(.buy-now)';

	var buyBtnSelector = '.et-single-buy-now, .buy_now_button, .button.buy-now';

	function isBuyButton(btn) {
		return (
			btn &&
			(btn.classList.contains('et-single-buy-now') ||
				btn.classList.contains('buy_now_button') ||
				btn.classList.contains('buy-now'))
		);
	}

	function normalizeButtonLabel(btn, label) {
		if (!btn) {
			return;
		}
		btn.setAttribute('aria-label', label);
		btn.setAttribute('title', label);
		var textEl = btn.querySelector('.button-text');
		if (textEl) {
			textEl.textContent = label;
			return;
		}
		if (!btn.textContent || !btn.textContent.trim()) {
			btn.textContent = label;
		}
	}

	function enhanceProductBuyButtons() {
		if (!isProduct) {
			return;
		}

		document.querySelectorAll('.etheme-add-to-cart-form').forEach(function (formWrap) {
			if (formWrap.closest('.etheme-sticky-cart')) {
				return;
			}

			formWrap.querySelectorAll('.et-or-wrapper').forEach(function (el) {
				el.style.setProperty('display', 'none', 'important');
			});

			var row =
				formWrap.querySelector('.woocommerce-variation-add-to-cart') ||
				formWrap.querySelector('form.cart:not(.variations_form)');
			if (row) {
				row.classList.add('dm-product-actions--stacked');
			}
		});

		document.querySelectorAll('.etheme-add-to-cart-form ' + cartBtnSelector).forEach(function (btn) {
			if (btn.closest('.etheme-sticky-cart')) {
				return;
			}
			btn.classList.add('dm-cart-label-btn');
			normalizeButtonLabel(btn, CART_LABEL);
		});

		document.querySelectorAll('.etheme-add-to-cart-form ' + buyBtnSelector).forEach(function (btn) {
			if (btn.closest('.etheme-sticky-cart')) {
				return;
			}
			btn.classList.add('dm-buy-text-btn');
			btn.classList.remove('elementor-hidden-mobile', 'elementor-hidden-tablet', 'hidden');
			normalizeButtonLabel(btn, BUY_LABEL);
		});

		var sticky = document.querySelector('.etheme-sticky-cart');
		if (sticky) {
			sticky.querySelectorAll('.price, p.price, .woocommerce-Price-amount').forEach(function (el) {
				el.style.setProperty('display', 'none', 'important');
			});
			sticky.querySelectorAll(buyBtnSelector).forEach(function (btn) {
				btn.classList.remove('elementor-hidden-mobile', 'elementor-hidden-tablet', 'hidden');
			});
		}
	}

	function hideThemeRelatedBlocks() {
		if (!isProduct) {
			return;
		}
		document.querySelectorAll('h1, h2, h3, h4, .title').forEach(function (heading) {
			var text = (heading.textContent || '').trim();
			if (!/related\s*products/i.test(text) || heading.closest('.dejoiy-mobile-product-reco')) {
				return;
			}
			var block = heading.closest(
				'section.elementor-section, .related.products, .upsells.products, .products-related'
			);
			if (block) {
				block.style.setProperty('display', 'none', 'important');
			}
		});
	}

	function adjustStickyPadding() {
		var sticky = document.querySelector('.etheme-sticky-cart.etheme-sticky-panel');
		if (!sticky) {
			return;
		}
		var h = Math.ceil(sticky.getBoundingClientRect().height);
		document.body.style.setProperty('padding-bottom', Math.max(h + 20, 120) + 'px');
	}

	function placeSections() {
		if (!isProduct) {
			return;
		}

		var reco = document.querySelector('.dejoiy-mobile-product-reco');
		if (!reco) {
			return;
		}

		var insertBefore =
			document.querySelector('.et-mobile-panel-wrapper') ||
			document.querySelector('.et-footers-wrapper') ||
			document.querySelector('footer.site-footer') ||
			document.querySelector('.elementor-location-footer') ||
			document.querySelector('.prefooter');

		if (insertBefore && insertBefore.parentNode) {
			insertBefore.parentNode.insertBefore(reco, insertBefore);
		} else {
			var tabs = document.querySelector('.woocommerce-tabs.wc-tabs-wrapper, .woocommerce-tabs.et-clearfix');
			var anchor = tabs;
			if (tabs) {
				var section = tabs.closest('section.elementor-top-section');
				if (section) {
					anchor = section;
				}
			} else {
				anchor = document.querySelector('.elementor-location-single');
			}
			if (anchor) {
				if (anchor.parentElement && reco.parentElement !== anchor.parentElement) {
					anchor.parentElement.appendChild(reco);
				} else if (anchor.nextElementSibling !== reco) {
					anchor.insertAdjacentElement('afterend', reco);
				}
			}
		}

		reco.classList.add('is-placed');
	}

	function bindVariationRefresh() {
		if (!isProduct || typeof jQuery === 'undefined') {
			return;
		}
		jQuery(document.body).on('found_variation reset_variation show_variation', function () {
			window.setTimeout(enhanceProductBuyButtons, 0);
			window.setTimeout(enhanceProductBuyButtons, 200);
		});
	}

	function run() {
		hideThemeRelatedBlocks();
		placeSections();
		enhanceProductBuyButtons();
		adjustStickyPadding();

		[150, 500, 1200].forEach(function (ms) {
			window.setTimeout(function () {
				hideThemeRelatedBlocks();
				placeSections();
				enhanceProductBuyButtons();
				adjustStickyPadding();
			}, ms);
		});
	}

	bindVariationRefresh();

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', run);
	} else {
		run();
	}
})();
