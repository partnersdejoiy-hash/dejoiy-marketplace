/**
 * DEJOIY Shop Discovery Hub — relocate loop controls, hide legacy chrome
 */
(function () {
	'use strict';

	var hub = document.getElementById('dejoiy-shop-discovery-hub');
	if (!hub) {
		return;
	}

	var body = document.body;

	if (window.matchMedia('(max-width: 1024px)').matches && document.getElementById('dejoiy-mobile-os-mkt')) {
		body.classList.add('dejoiy-mobile-os-active');
	}

	function hideLegacySections() {
		var selectors = [
			'.etheme-category-grid',
			'.etheme-category-slider',
			'.etheme-dynamic-categories-wrapper',
			'.elementor-widget-woocommerce-archive-etheme_dynamic_categories',
			'.categories-grid',
			'.swiper-entry.etheme-category-slider',
			'.dsh-categories'
		];

		selectors.forEach(function (sel) {
			document.querySelectorAll(sel).forEach(function (node) {
				var section = node.closest('.elementor-section, .elementor-top-section, .etheme-elementor-slider-on-shop');
				(section || node).classList.add('dsh-legacy-hidden');
			});
		});

		document.querySelectorAll('.elementor-section, .elementor-top-section').forEach(function (section) {
			if (section.querySelector('#dejoiy-shop-discovery-hub')) {
				return;
			}
			var text = (section.textContent || '').replace(/\s+/g, ' ');
			if (
				text.indexOf('Design Apparel That Stands Out') !== -1
				|| text.indexOf('Your Design • Your Identity') !== -1
				|| text.indexOf('Explore Everything DEJOIY Offers') !== -1
				|| text.indexOf('DEJOIY Discovery Hub') !== -1
			) {
				section.classList.add('dsh-legacy-hidden');
			}
		});

		document.querySelectorAll('.elementor-section, .elementor-top-section').forEach(function (section) {
			if (section.querySelector('#dejoiy-shop-discovery-hub')) {
				return;
			}
			if (
				section.querySelector('.dsh-legend')
				|| section.querySelector('.dsh-banner')
				|| (section.textContent || '').indexOf('Search across DEJOIY') !== -1
			) {
				section.classList.add('dsh-legacy-hidden');
			}
		});
	}

	function findLoopControlsRow() {
		var switcher = document.querySelector('.switcher-wrapper');
		if (!switcher || hub.contains(switcher)) {
			return null;
		}

		var candidate = switcher.parentElement;
		var best = switcher.parentElement;

		while (candidate && candidate !== document.body) {
			if (
				candidate.querySelector('.woocommerce-ordering')
				|| candidate.querySelector('.products-per-page, select[name="et_per_page"]')
			) {
				best = candidate;
			}
			candidate = candidate.parentElement;
		}

		if (!best || hub.contains(best)) {
			return null;
		}

		return best;
	}

	function relocateLoopControls() {
		var slot = document.getElementById('dsh-loop-controls-slot');
		if (!slot) {
			return;
		}

		var row = findLoopControlsRow();
		if (!row || row.contains(slot) || slot.contains(row)) {
			return;
		}

		row.classList.add('dsh-loop-controls-bar');
		slot.appendChild(row);

		var parent = row.parentElement;
		while (parent && parent !== slot) {
			if (
				parent.classList
				&& parent.classList.contains('elementor-section')
				&& !parent.querySelector('.dsh-hub')
				&& !parent.querySelector('ul.products')
			) {
				var onlyControls = parent.querySelector('.switcher-wrapper') && !parent.querySelector('.product');
				if (onlyControls) {
					parent.classList.add('dsh-loop-controls-source');
				}
			}
			parent = parent.parentElement;
		}
	}

	function run() {
		hideLegacySections();
		relocateLoopControls();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', run);
	} else {
		run();
	}

	window.setTimeout(run, 400);
	window.setTimeout(run, 1200);
})();
