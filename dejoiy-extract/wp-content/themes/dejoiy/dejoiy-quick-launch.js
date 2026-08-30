/**
 * DEJOIY Quick — Launch landing (notify modal + waitlist).
 */
(function () {
	'use strict';

	var root = document.getElementById('dejoiy-quick-launch');
	if (!root) {
		return;
	}

	var cfg = window.dejoiyQuickLaunch || {};
	var modal = document.getElementById('dq-notify-modal');
	var form = document.getElementById('dq-notify-form');
	var successEl = document.getElementById('dq-notify-success');
	var lastFocus = null;

	function openModal() {
		if (!modal) {
			return;
		}
		lastFocus = document.activeElement;
		modal.hidden = false;
		document.body.style.overflow = 'hidden';
		var first = modal.querySelector('#dq-notify-name');
		if (first) {
			window.setTimeout(function () {
				first.focus();
			}, 80);
		}
	}

	function closeModal() {
		if (!modal) {
			return;
		}
		modal.hidden = true;
		document.body.style.overflow = '';
		if (lastFocus && typeof lastFocus.focus === 'function') {
			lastFocus.focus();
		}
	}

	function setError(field, message) {
		var el = form ? form.querySelector('[data-error-for="' + field + '"]') : null;
		var input = form ? form.querySelector('[name="' + field + '"]') : null;
		if (el) {
			el.textContent = message || '';
		}
		if (input) {
			input.classList.toggle('is-invalid', !!message);
		}
	}

	function clearErrors() {
		['name', 'email', 'phone'].forEach(function (f) {
			setError(f, '');
		});
	}

	function validate() {
		clearErrors();
		var valid = true;
		var name = form.querySelector('[name="name"]');
		var email = form.querySelector('[name="email"]');
		var phone = form.querySelector('[name="phone"]');

		if (!name || name.value.trim().length < 2) {
			setError('name', 'Please enter your name (at least 2 characters).');
			valid = false;
		}
		if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
			setError('email', 'Please enter a valid email address.');
			valid = false;
		}
		var digits = phone ? phone.value.replace(/\D/g, '') : '';
		if (!/^[6-9]\d{9}$/.test(digits)) {
			setError('phone', 'Please enter a valid 10-digit Indian mobile number.');
			valid = false;
		}
		return valid;
	}

	function onSubmit(ev) {
		ev.preventDefault();
		if (!validate() || !cfg.ajaxUrl) {
			return;
		}
		var submitBtn = form.querySelector('[data-dq-submit]');
		if (submitBtn) {
			submitBtn.disabled = true;
			submitBtn.textContent = 'Submitting…';
		}

		var body = new FormData(form);
		body.append('action', cfg.action || 'dejoiy_quick_launch_notify');
		body.append('nonce', cfg.nonce || '');
		if (form.querySelector('[name="phone"]')) {
			body.set('phone', form.querySelector('[name="phone"]').value.replace(/\D/g, ''));
		}

		fetch(cfg.ajaxUrl, { method: 'POST', body: body, credentials: 'same-origin' })
			.then(function (r) {
				return r.json();
			})
			.then(function (data) {
				if (data && data.success) {
					if (successEl) {
						successEl.hidden = false;
					}
					form.querySelectorAll('.dq-field, [data-dq-submit]').forEach(function (el) {
						el.style.display = 'none';
					});
					var msg = (data.data && data.data.message) || successEl.textContent;
					if (successEl && msg) {
						successEl.textContent = msg;
					}
				} else {
					var errMsg =
						(data && data.data && data.data.message) || 'Something went wrong. Please try again.';
					setError('email', errMsg);
					if (submitBtn) {
						submitBtn.disabled = false;
						submitBtn.textContent = 'Submit';
					}
				}
			})
			.catch(function () {
				setError('email', 'Network error. Please try again.');
				if (submitBtn) {
					submitBtn.disabled = false;
					submitBtn.textContent = 'Submit';
				}
			});
	}

	root.querySelectorAll('[data-dq-open-notify]').forEach(function (btn) {
		btn.addEventListener('click', openModal);
	});

	if (modal) {
		modal.querySelectorAll('[data-dq-close-modal]').forEach(function (el) {
			el.addEventListener('click', closeModal);
		});
		document.addEventListener('keydown', function (ev) {
			if (!modal.hidden && ev.key === 'Escape') {
				closeModal();
			}
		});
	}

	if (form) {
		form.addEventListener('submit', onSubmit);
	}

	/* Scroll reveal */
	var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	if (!reduceMotion && 'IntersectionObserver' in window) {
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
		root.querySelectorAll('.dq-reveal').forEach(function (el) {
			io.observe(el);
		});
	} else {
		root.querySelectorAll('.dq-reveal').forEach(function (el) {
			el.classList.add('is-visible');
		});
	}
})();
