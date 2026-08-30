/**
 * DEJOIY Checkout Experience — mobile drawer, totals, micro-interactions
 */
(function () {
	'use strict';

	if (!document.body.classList.contains('dejoiy-checkout-xp')) {
		return;
	}

	var shell = document.getElementById('dejoiy-checkout-xp');
	var summary = document.querySelector('[data-dcx-summary]');
	var mobileBar = document.querySelector('[data-dcx-mobile-bar]');
	var mobileTotal = document.querySelector('[data-dcx-mobile-total]');
	var drawer = document.querySelector('[data-dcx-drawer]');
	var drawerBody = document.querySelector('[data-dcx-drawer-body]');
	var drawerOpenBtn = document.querySelector('[data-dcx-drawer-open]');
	var mqDesktop = window.matchMedia('(min-width: 1025px)');

	function formatMoney(text) {
		return (text || '').trim();
	}

	function syncMobileTotal() {
		if (!mobileTotal) {
			return;
		}
		var totalCell = document.querySelector('.order-total .woocommerce-Price-amount');
		if (totalCell) {
			mobileTotal.textContent = formatMoney(totalCell.textContent);
		}
	}

	function toggleMobileChrome() {
		var desktop = mqDesktop.matches;
		if (mobileBar) {
			mobileBar.hidden = desktop;
		}
		if (drawer && desktop) {
			drawer.classList.remove('is-open');
			drawer.hidden = true;
			drawer.setAttribute('aria-hidden', 'true');
		}
	}

	function openDrawer() {
		if (!drawer || !summary || !drawerBody) {
			return;
		}
		drawerBody.innerHTML = '';
		var title = document.createElement('h3');
		title.className = 'dcx-summary-card__title';
		title.textContent = 'Order Summary';
		drawerBody.appendChild(title);
		var table = summary.querySelector('.woocommerce-checkout-review-order-table');
		if (table) {
			drawerBody.appendChild(table.cloneNode(true));
		}
		drawer.hidden = false;
		drawer.classList.add('is-open');
		drawer.setAttribute('aria-hidden', 'false');
		if (drawerOpenBtn) {
			drawerOpenBtn.setAttribute('aria-expanded', 'true');
		}
		document.body.style.overflow = 'hidden';
	}

	function closeDrawer() {
		if (!drawer) {
			return;
		}
		drawer.classList.remove('is-open');
		drawer.hidden = true;
		drawer.setAttribute('aria-hidden', 'true');
		if (drawerOpenBtn) {
			drawerOpenBtn.setAttribute('aria-expanded', 'false');
		}
		document.body.style.overflow = '';
	}

	function bindDrawer() {
		if (drawerOpenBtn) {
			drawerOpenBtn.addEventListener('click', openDrawer);
		}
		document.querySelectorAll('[data-dcx-drawer-close]').forEach(function (btn) {
			btn.addEventListener('click', closeDrawer);
		});
	}

	function enhanceFields() {
		document.querySelectorAll('form.checkout .form-row input.input-text, form.checkout .form-row select, form.checkout .form-row textarea').forEach(function (input) {
			var row = input.closest('.form-row');
			if (!row || row.classList.contains('dcx-field-ready')) {
				return;
			}
			row.classList.add('dcx-field-ready');
			function mark() {
				if (input.value && String(input.value).trim() !== '') {
					row.classList.add('dcx-field--filled');
				} else {
					row.classList.remove('dcx-field--filled');
				}
			}
			input.addEventListener('input', mark);
			input.addEventListener('change', mark);
			mark();
		});
	}

	function onCheckoutUpdate() {
		syncMobileTotal();
		enhanceFields();
	}

	if (typeof jQuery !== 'undefined') {
		jQuery(document.body).on('updated_checkout', onCheckoutUpdate);
	}

	toggleMobileChrome();
	syncMobileTotal();
	enhanceFields();
	bindDrawer();

	mqDesktop.addEventListener('change', toggleMobileChrome);

	if (shell) {
		shell.classList.add('dcx-ready');
	}
})();
