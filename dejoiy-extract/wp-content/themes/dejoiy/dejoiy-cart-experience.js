/**
 * DEJOIY Cart Experience — mobile totals, micro-interactions
 */
(function ($) {
	'use strict';

	if (!document.body.classList.contains('dejoiy-cart-xp')) {
		return;
	}

	var mobileTotal = document.querySelector('[data-dcart-mobile-total]');
	var mqDesktop = window.matchMedia('(min-width: 1025px)');

	function syncMobileTotal() {
		if (!mobileTotal) {
			return;
		}
		var cell = document.querySelector('.cart_totals .order-total .woocommerce-Price-amount');
		if (cell) {
			mobileTotal.textContent = (cell.textContent || '').trim();
		}
	}

	function toggleMobileBar() {
		var bar = document.querySelector('[data-dcart-mobile-bar]');
		if (!bar) {
			return;
		}
		bar.style.display = mqDesktop.matches ? 'none' : 'flex';
	}

	$('.woocommerce-cart-form').on('click', 'button[name="update_cart"]', function () {
		document.body.classList.add('dcart-updating');
	});

	$(document.body).on('updated_cart_totals', function () {
		document.body.classList.remove('dcart-updating');
		syncMobileTotal();
	});

	$('.cart_item .qty').on('change input', function () {
		var row = this.closest('.cart_item');
		if (row) {
			row.classList.add('dcart-item--pulse');
			window.setTimeout(function () {
				row.classList.remove('dcart-item--pulse');
			}, 420);
		}
	});

	syncMobileTotal();
	toggleMobileBar();
	mqDesktop.addEventListener('change', toggleMobileBar);

	var shell = document.getElementById('dejoiy-cart-xp');
	if (shell) {
		shell.classList.add('dcart-ready');
	}
})(jQuery);
