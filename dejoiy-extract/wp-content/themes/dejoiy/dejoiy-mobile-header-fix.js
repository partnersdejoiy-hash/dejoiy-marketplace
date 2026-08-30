/**
 * DEJOIY mobile header — instant nav, no loading overlay on refresh.
 */
(function () {
	'use strict';

	function hideOverlay() {
		var overlay = document.getElementById('djOverlay');
		if (overlay) {
			overlay.classList.remove('active');
			overlay.setAttribute('aria-hidden', 'true');
		}
	}

	function stripHeaderSkeleton() {
		var header = document.querySelector('.elementor-location-header');
		if (!header) {
			return;
		}
		header.querySelectorAll('.skeleton-body').forEach(function (el) {
			el.classList.remove('skeleton-body');
		});
	}

	function fixNavCards() {
		var nav = document.getElementById('djNav');
		if (!nav) {
			return;
		}

		nav.querySelectorAll('.dejoiy-nav-card').forEach(function (card) {
			card.addEventListener(
				'click',
				function (ev) {
					var href = card.getAttribute('data-href');
					if (!href) {
						return;
					}
					ev.preventDefault();
					ev.stopImmediatePropagation();
					var key = card.getAttribute('data-nav');
					if (key) {
						try {
							localStorage.setItem('dj_a', key);
						} catch (e) {
							/* ignore */
						}
					}
					nav.querySelectorAll('.dejoiy-nav-card').forEach(function (x) {
						x.classList.remove('active');
					});
					card.classList.add('active');
					hideOverlay();
					window.location.href = href;
				},
				true
			);
		});
	}

	hideOverlay();
	stripHeaderSkeleton();

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () {
			hideOverlay();
			stripHeaderSkeleton();
			fixNavCards();
		});
	} else {
		fixNavCards();
	}

	window.addEventListener('pageshow', hideOverlay);
})();
