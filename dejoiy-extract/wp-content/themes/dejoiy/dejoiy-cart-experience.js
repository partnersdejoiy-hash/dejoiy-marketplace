/**
 * DEJOIY Cart Experience — Amazon-Grade Interactive Stepper & Synchronisation
 */
(function ($) {
	'use strict';

	if (!document.body.classList.contains('dejoiy-cart-xp')) {
		return;
	}

	var updateTimer = null;
	var mobileTotal = document.querySelector('[data-dcart-mobile-total]');
	var mqDesktop = window.matchMedia('(min-width: 1025px)');

	function syncTotals() {
		// Sync mobile total from order total or cart subtotal
		if (mobileTotal) {
			var totalCell = document.querySelector('.cart_totals .order-total .woocommerce-Price-amount');
			if (!totalCell) {
				totalCell = document.querySelector('.cart_totals .cart-subtotal .woocommerce-Price-amount');
			}
			if (totalCell) {
				mobileTotal.innerHTML = totalCell.innerHTML;
			}
		}

		// Sync items subtotal bar
		var itemsSubtotal = document.getElementById('dcart-items-subtotal');
		if (itemsSubtotal) {
			var subtotalCell = document.querySelector('.cart_totals .cart-subtotal .woocommerce-Price-amount');
			if (subtotalCell) {
				var valEl = itemsSubtotal.querySelector('.dcart-items-subtotal__value');
				if (valEl) {
					valEl.innerHTML = subtotalCell.innerHTML;
				}
			}
		}
	}

	function toggleMobileBar() {
		var bar = document.querySelector('[data-dcart-mobile-bar]');
		if (!bar) {
			return;
		}
		bar.style.display = mqDesktop.matches ? 'none' : 'flex';
	}

	// Stepper Plus
	$(document).on('click', '.dcart-stepper-plus', function (e) {
		e.preventDefault();
		var key = $(this).data('key');
		if (!key) return;

		var $valEl = $('.dcart-stepper-val[data-key="' + key + '"]');
		var current = parseInt($valEl.text(), 10) || 1;
		var next = current + 1;
		$valEl.text(next);

		var $inputs = $('input[name="cart[' + key + '][qty]"], input[name="cart[' + key + '][qty_duplicated]"]');
		if ($inputs.length) {
			$inputs.val(next).trigger('change');
		}

		var $row = $(this).closest('.cart_item');
		if ($row.length) {
			$row.addClass('dcart-item--pulse');
		}

		clearTimeout(updateTimer);
		updateTimer = setTimeout(function () {
			$('button[name="update_cart"]').trigger('click');
		}, 400);
	});

	// Stepper Minus
	$(document).on('click', '.dcart-stepper-minus', function (e) {
		e.preventDefault();
		var key = $(this).data('key');
		if (!key) return;

		var $valEl = $('.dcart-stepper-val[data-key="' + key + '"]');
		var current = parseInt($valEl.text(), 10) || 1;

		if (current <= 1) {
			var $remove = $(this).closest('.dcart-item').find('.dcart-action-link--remove');
			if ($remove.length) {
				if (window.confirm('Do you want to remove this item from your cart?')) {
					window.location.href = $remove.attr('href');
				}
			}
			return;
		}

		var next = current - 1;
		$valEl.text(next);

		var $inputs = $('input[name="cart[' + key + '][qty]"], input[name="cart[' + key + '][qty_duplicated]"]');
		if ($inputs.length) {
			$inputs.val(next).trigger('change');
		}

		var $row = $(this).closest('.cart_item');
		if ($row.length) {
			$row.addClass('dcart-item--pulse');
		}

		clearTimeout(updateTimer);
		updateTimer = setTimeout(function () {
			$('button[name="update_cart"]').trigger('click');
		}, 400);
	});

	// Handle native updated cart event
	$(document.body).on('updated_cart_totals', function () {
		document.body.classList.remove('dcart-updating');
		$('.cart_item').removeClass('dcart-item--pulse');
		syncTotals();
	});

	$('.woocommerce-cart-form').on('click', 'button[name="update_cart"]', function () {
		document.body.classList.add('dcart-updating');
	});

	syncTotals();
	toggleMobileBar();
	mqDesktop.addEventListener('change', toggleMobileBar);

	var shell = document.getElementById('dejoiy-cart-xp');
	if (shell) {
		shell.classList.add('dcart-ready');
	}
})(jQuery);
