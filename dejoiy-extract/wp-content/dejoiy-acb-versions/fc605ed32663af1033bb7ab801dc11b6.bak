(function () {
	'use strict';

	var cfg = window.dejoiyContactXp || {};
	var form = document.getElementById('dejoiy-contact-form');
	if (!form || !cfg.ajaxUrl) {
		return;
	}

	var statusEl = document.getElementById('dcu-form-status');
	var submitBtn = document.getElementById('dcu-submit');
	var labelEl = submitBtn ? submitBtn.querySelector('.dcu-submit__label') : null;
	var spinnerEl = submitBtn ? submitBtn.querySelector('.dcu-submit__spinner') : null;

	function setStatus(message, type) {
		if (!statusEl) {
			return;
		}
		statusEl.textContent = message || '';
		statusEl.classList.remove('is-success', 'is-error');
		if (type) {
			statusEl.classList.add(type === 'error' ? 'is-error' : 'is-success');
		}
	}

	function setLoading(loading) {
		if (!submitBtn) {
			return;
		}
		submitBtn.disabled = loading;
		if (labelEl) {
			labelEl.textContent = loading ? (cfg.i18n && cfg.i18n.sending) || 'Sending…' : (cfg.i18n && cfg.i18n.submit) || 'Submit';
		}
		if (spinnerEl) {
			spinnerEl.hidden = !loading;
		}
	}

	form.addEventListener('submit', function (event) {
		event.preventDefault();
		setStatus('');

		if (!form.checkValidity()) {
			form.reportValidity();
			return;
		}

		var data = new FormData(form);
		data.append('action', 'dejoiy_contact_submit');
		data.append('nonce', cfg.nonce || '');

		setLoading(true);

		fetch(cfg.ajaxUrl, {
			method: 'POST',
			body: data,
			credentials: 'same-origin',
		})
			.then(function (response) {
				return response.json().then(function (json) {
					return { ok: response.ok, json: json };
				});
			})
			.then(function (result) {
				var json = result.json || {};
				var payload = json.data || {};
				if (json.success) {
					setStatus(payload.message || 'Thank you!', 'success');
					form.reset();
					return;
				}
				var errMsg = (payload && payload.message) || (cfg.i18n && cfg.i18n.error) || 'Something went wrong.';
				setStatus(errMsg, 'error');
			})
			.catch(function () {
				setStatus((cfg.i18n && cfg.i18n.error) || 'Something went wrong.', 'error');
			})
			.finally(function () {
				setLoading(false);
			});
	});
})();
