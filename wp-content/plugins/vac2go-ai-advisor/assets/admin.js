/* Vac2Go Advisor admin review interactions (correction UI, no page reload). */
(function () {
	'use strict';

	function post(url, body) {
		return fetch(url, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': window.vaAdvisorAdmin.nonce,
			},
			credentials: 'same-origin',
			body: JSON.stringify(body),
		}).then(function (r) {
			return r.json().then(function (j) {
				return { ok: r.ok, data: j };
			});
		});
	}

	document.addEventListener('click', function (e) {
		var toggle = e.target.closest('.va-mark-btn');
		if (toggle) {
			var wrap = toggle.parentElement.querySelector('.va-correct-form');
			if (wrap) {
				wrap.style.display = wrap.style.display === 'none' ? 'block' : 'none';
			}
			return;
		}

		var save = e.target.closest('.va-save-correction');
		if (save) {
			var logId = save.getAttribute('data-log-id');
			var form = save.closest('.va-correct-form');
			var textarea = form.querySelector('.va-correction-text');
			var status = form.querySelector('.va-correct-status');
			status.textContent = 'Saving…';
			post(window.vaAdvisorAdmin.restUrl + '/correction', {
				log_id: parseInt(logId, 10),
				correction_text: textarea.value,
			})
				.then(function (res) {
					if (res.ok && res.data && res.data.ok) {
						status.textContent = 'Saved ✓';
						status.style.color = '#1a7f37';
					} else {
						status.textContent = 'Error saving';
						status.style.color = '#b32d2e';
					}
				})
				.catch(function () {
					status.textContent = 'Network error';
					status.style.color = '#b32d2e';
				});
		}
	});
})();
