/* Vac2Go Equipment Advisor, front-end widget (vanilla JS, no build step).
   Loaded on demand by the inline bootstrap after the visitor clicks the launcher. */
(function () {
	'use strict';

	if (window.vaAdvisorBooted) { return; }
	window.vaAdvisorBooted = true;

	var cfg = window.vaAdvisor || {};
	var CONTACT_URL = cfg.contactUrl || 'https://vac2go.com/contact/';
	var bootTime = window.vaAdvisorBootTime || Date.now();

	// Stylesheet injected at runtime (LiteSpeed's unused-CSS optimizer would purge
	// selectors for JS-built DOM); the bootstrap usually adds it before loading us.
	if (cfg.cssUrl && !document.getElementById('va-advisor-css')) {
		var vaLink = document.createElement('link');
		vaLink.id = 'va-advisor-css';
		vaLink.rel = 'stylesheet';
		vaLink.href = cfg.cssUrl;
		(document.head || document.documentElement).appendChild(vaLink);
	}

	// ---- ids ----
	function uuid() {
		if (window.crypto && crypto.randomUUID) {
			return crypto.randomUUID();
		}
		return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
			var r = (Math.random() * 16) | 0;
			var v = c === 'x' ? r : (r & 0x3) | 0x8;
			return v.toString(16);
		});
	}

	function getSessionId() {
		var id = sessionStorage.getItem('vaAdvisorSession');
		if (!id) {
			id = uuid();
			sessionStorage.setItem('vaAdvisorSession', id);
		}
		return id;
	}

	var sessionId = getSessionId();
	var contactAsked = false;
	var contactDone = sessionStorage.getItem('vaAdvisorContactDone') === '1';
	var busy = false;
	var assistantTurns = 0;

	// ---- CSRF nonce: fetched fresh at boot (never baked into cached HTML) ----
	var restNonce = null;
	function fetchNonce() {
		// A REST nonce is bound to the current user, so a CACHED nonce response is
		// worse than useless: a logged-in visitor handed a nonce minted for a logged-out
		// one gets rest_cookie_invalid_nonce from WP core before our handler ever runs.
		// The endpoint already sends no-store, so this defeats anything keyed on URL
		// alone (an edge cache, a service worker, the bfcache).
		return fetch(cfg.restUrl + '/nonce?_=' + Date.now(), {
			credentials: 'same-origin',
			cache: 'no-store',
			headers: { 'Cache-Control': 'no-cache', 'Pragma': 'no-cache' },
		})
			.then(function (r) { return r.json(); })
			.then(function (d) { restNonce = d && d.nonce ? d.nonce : null; return restNonce; })
			.catch(function () { return null; });
	}
	var nonceReady = fetchNonce();

	var GREETING =
		"Hi, I'm the Vac2Go Equipment Advisor. Tell me about your job and I'll point you to the right truck category.\n\n" +
		"A few things help: what are you vacuuming, cleaning, or excavating? Roughly how much? And what are the site conditions?";

	// ---- DOM ----
	var root = document.createElement('div');
	root.className = 'va-advisor-root';
	root.innerHTML =
		'<button class="va-launcher" aria-label="Open equipment advisor chat" aria-expanded="false">' +
			'<span class="va-dot"></span>' +
			'<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="currentColor" d="M12 3C6.5 3 2 6.6 2 11c0 2.4 1.3 4.6 3.5 6-.2 1-.8 2.3-1.8 3.4 1.7-.2 3.4-.8 4.8-1.8 1.1.3 2.3.4 3.5.4 5.5 0 10-3.6 10-8s-4.5-8-10-8z"/></svg>' +
			'<span class="va-launcher-label">Ask the Equipment Advisor</span>' +
		'</button>' +
		'<div class="va-panel" role="dialog" aria-modal="true" aria-label="Vac2Go Equipment Advisor" hidden>' +
			'<div class="va-header">' +
				'<div class="va-title">Vac2Go Equipment Advisor</div>' +
				'<button class="va-close" aria-label="Close chat">&times;</button>' +
			'</div>' +
			'<div class="va-messages" aria-live="polite"></div>' +
			'<div class="va-contact-card" hidden></div>' +
			'<form class="va-inputbar">' +
				'<input type="text" name="website" class="va-hp" value="" tabindex="-1" autocomplete="off" aria-hidden="true">' +
				'<textarea class="va-input" rows="1" placeholder="Describe your job…" maxlength="2000" aria-label="Your message"></textarea>' +
				'<button type="submit" class="va-send" aria-label="Send message">Send</button>' +
			'</form>' +
			'<div class="va-disclosure">This chat is automated and logged for quality review. No pricing or booking here.</div>' +
		'</div>';
	document.body.appendChild(root);

	// Replace the bootstrap launcher if it exists.
	var boot = document.getElementById('va-boot-launcher');
	if (boot && boot.parentNode) { boot.parentNode.removeChild(boot); }
	var bootCss = document.getElementById('va-boot-css');
	if (bootCss && bootCss.parentNode) { bootCss.parentNode.removeChild(bootCss); }

	var launcher = root.querySelector('.va-launcher');
	var panel = root.querySelector('.va-panel');
	var closeBtn = root.querySelector('.va-close');
	var messagesEl = root.querySelector('.va-messages');
	var form = root.querySelector('.va-inputbar');
	var input = root.querySelector('.va-input');
	var sendBtn = root.querySelector('.va-send');
	var hpField = root.querySelector('.va-hp');
	var contactCard = root.querySelector('.va-contact-card');

	var opened = false;

	// ---- focus trap (a11y) ----
	function focusables() {
		return Array.prototype.filter.call(
			panel.querySelectorAll('button, [href], input, textarea, select'),
			function (el) { return !el.disabled && el.offsetParent !== null && el !== hpField; }
		);
	}
	function trapKeydown(e) {
		if (e.key === 'Escape') {
			e.preventDefault();
			closePanel();
			return;
		}
		if (e.key !== 'Tab') { return; }
		var els = focusables();
		if (!els.length) { return; }
		var first = els[0];
		var last = els[els.length - 1];
		if (e.shiftKey && document.activeElement === first) {
			e.preventDefault();
			last.focus();
		} else if (!e.shiftKey && document.activeElement === last) {
			e.preventDefault();
			first.focus();
		}
	}

	function openPanel() {
		panel.hidden = false;
		launcher.classList.add('va-hidden');
		launcher.setAttribute('aria-expanded', 'true');
		panel.addEventListener('keydown', trapKeydown);
		input.focus();
		if (!opened) {
			opened = true;
			addMessage('assistant', GREETING);
		}
	}
	function closePanel() {
		panel.hidden = true;
		launcher.classList.remove('va-hidden');
		launcher.setAttribute('aria-expanded', 'false');
		panel.removeEventListener('keydown', trapKeydown);
		launcher.focus(); // focus returns to the launcher
	}

	launcher.addEventListener('click', openPanel);
	closeBtn.addEventListener('click', closePanel);

	function escapeHtml(s) {
		return String(s).replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	}

	function renderText(text) {
		var safe = escapeHtml(text);
		safe = safe.replace(/(https?:\/\/[^\s]+)/g, function (u) {
			var clean = u.replace(/[.,)]+$/, '');
			return '<a href="' + clean + '" target="_blank" rel="noopener">' + clean + '</a>';
		});
		return safe.replace(/\n/g, '<br>');
	}

	function addMessage(role, text) {
		var el = document.createElement('div');
		el.className = 'va-msg va-msg-' + role;
		el.innerHTML = '<div class="va-bubble">' + renderText(text) + '</div>';
		messagesEl.appendChild(el);
		messagesEl.scrollTop = messagesEl.scrollHeight;
		if (role === 'assistant') { assistantTurns++; }
		return el;
	}

	function showTyping() {
		var el = document.createElement('div');
		el.className = 'va-msg va-msg-assistant va-typing';
		el.innerHTML = '<div class="va-bubble"><span></span><span></span><span></span></div>';
		messagesEl.appendChild(el);
		messagesEl.scrollTop = messagesEl.scrollHeight;
		return el;
	}

	function postChat(payload) {
		return fetch(cfg.restUrl + '/chat', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': restNonce || '' },
			credentials: 'same-origin',
			body: JSON.stringify(payload),
		});
	}

	// ---- streaming (S5) ----
	// The server holds text back until the deterministic filter stages have passed on
	// it, so anything that arrives here is already safe to paint. A 'replace' event
	// means a later stage (or the end-of-answer judge) rejected the whole answer.
	var STREAM_OK = !!cfg.streaming &&
		typeof TextDecoder !== 'undefined' &&
		typeof ReadableStream !== 'undefined';

	function streamTurn(payload, typing) {
		return fetch(cfg.restUrl + '/chat/stream', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': restNonce || '' },
			credentials: 'same-origin',
			body: JSON.stringify(payload),
		}).then(function (r) {
			if (r.status === 403) { return { retryNonce: true }; }
			// 501 = streaming disabled or cURL missing. Anything non-SSE means a proxy
			// or the host rewrote the response; fall back rather than guess.
			if (!r.ok || !r.body || typeof r.body.getReader !== 'function') { return { fallback: true }; }
			if ((r.headers.get('Content-Type') || '').indexOf('text/event-stream') === -1) { return { fallback: true }; }
			return readStream(r.body.getReader(), typing);
		});
	}

	function readStream(reader, typing) {
		var decoder = new TextDecoder();
		var buf = '';
		var wrap = null;
		var text = '';
		var gotAny = false;

		function paint() {
			if (!wrap) {
				if (typing && typing.parentNode) { typing.remove(); }
				wrap = addMessage('assistant', '');
			}
			wrap.querySelector('.va-bubble').innerHTML = renderText(text);
			messagesEl.scrollTop = messagesEl.scrollHeight;
		}

		function frame(ev, dataStr) {
			var d;
			try { d = JSON.parse(dataStr); } catch (e) { return; }
			if (ev === 'delta') {
				if (d.text) { text += d.text; gotAny = true; paint(); }
			} else if (ev === 'replace') {
				text = d.text || '';
				gotAny = true;
				paint();
			}
		}

		function pump() {
			return reader.read().then(function (res) {
				if (res.value) {
					buf += decoder.decode(res.value, { stream: true });
					var idx;
					while ((idx = buf.indexOf('\n\n')) !== -1) {
						var block = buf.slice(0, idx);
						buf = buf.slice(idx + 2);
						var ev = null;
						var data = '';
						block.split('\n').forEach(function (line) {
							if (line.indexOf('event:') === 0) { ev = line.slice(6).trim(); }
							else if (line.indexOf('data:') === 0) { data += line.slice(5).trim(); }
						});
						if (ev) { frame(ev, data); }
					}
				}
				if (res.done) { return { streamed: true, gotAny: gotAny }; }
				return pump();
			});
		}

		return pump();
	}

	function bufferedTurn(payload, typing) {
		return postChat(payload)
			.then(function (r) {
				// Stale/garbage nonce: refetch once and retry the same request_id
				// (idempotent server-side, so no double model call or double row).
				if (r.status === 403) {
					return fetchNonce().then(function () { return postChat(payload); });
				}
				return r;
			})
			.then(function (r) { return r.json().catch(function () { return { reply: null }; }); })
			.then(function (data) {
				if (typing && typing.parentNode) { typing.remove(); }
				var reply =
					(data && data.reply) ||
					"Sorry, I couldn't get a response. Please reach a Vac2Go rep at " + CONTACT_URL + ".";
				addMessage('assistant', reply);
			});
	}

	function send(message) {
		if (busy || !message.trim()) { return; }
		busy = true;
		if (sendBtn) { sendBtn.disabled = true; }
		addMessage('user', message);
		var typing = showTyping();

		var payload = {
			session_id: sessionId,
			request_id: uuid(),
			message: message,
			website: hpField ? hpField.value : '',
			elapsed_ms: Date.now() - bootTime,
		};

		nonceReady
			.then(function () {
				return STREAM_OK ? streamTurn(payload, typing) : { fallback: true };
			})
			.then(function (res) {
				if (res && res.retryNonce) {
					return fetchNonce().then(function () { return streamTurn(payload, typing); });
				}
				return res;
			})
			.then(function (res) {
				if (res && res.streamed && res.gotAny) { return null; }
				// Streaming unsupported, buffered by the host, or it produced nothing.
				// Reusing the same request_id means the server replays a stored answer
				// instead of billing a second model call.
				return bufferedTurn(payload, typing);
			})
			.catch(function () {
				if (typing && typing.parentNode) { typing.remove(); }
				addMessage(
					'assistant',
					"Sorry, I'm having trouble connecting. Please reach a Vac2Go rep at " + CONTACT_URL + "."
				);
			})
			.finally(function () {
				busy = false;
				if (sendBtn) { sendBtn.disabled = false; }
				maybeAskContact();
			});
	}

	form.addEventListener('submit', function (e) {
		e.preventDefault();
		var v = input.value;
		if (!v.trim()) { return; }
		input.value = '';
		autoGrow();
		send(v);
	});

	input.addEventListener('keydown', function (e) {
		if (e.key === 'Enter' && !e.shiftKey) {
			e.preventDefault();
			form.dispatchEvent(new Event('submit', { cancelable: true }));
		}
	});
	function autoGrow() {
		input.style.height = 'auto';
		input.style.height = Math.min(input.scrollHeight, 120) + 'px';
	}
	input.addEventListener('input', autoGrow);

	// ---- contact capture (once, after the first real exchange) ----
	function maybeAskContact() {
		if (contactAsked || contactDone) { return; }
		if (assistantTurns < 2) { return; } // greeting + at least one real reply
		contactAsked = true;
		renderContactCard();
	}

	function renderContactCard() {
		var mode = cfg.captureMode || 'email_only';
		var fields = '';
		fields += '<label>Name<input type="text" class="va-c-name" autocomplete="name"></label>';
		if (mode === 'email_only' || mode === 'email_or_phone' || mode === 'email_and_phone') {
			fields += '<label>Email<input type="email" class="va-c-email" autocomplete="email"></label>';
		}
		if (mode === 'phone_only' || mode === 'email_or_phone' || mode === 'email_and_phone') {
			fields += '<label>Phone<input type="tel" class="va-c-phone" autocomplete="tel"></label>';
		}
		var helper =
			mode === 'email_or_phone'
				? 'Leave your name and an email or phone so a rep can follow up.'
				: 'Leave your details so a Vac2Go rep can follow up if needed.';

		contactCard.innerHTML =
			'<div class="va-contact-inner">' +
			'<div class="va-contact-title">Want a rep to follow up?</div>' +
			'<div class="va-contact-help">' + escapeHtml(helper) + '</div>' +
			fields +
			'<div class="va-contact-actions">' +
			'<button type="button" class="va-c-submit">Send</button>' +
			'<button type="button" class="va-c-skip">Skip</button>' +
			'</div>' +
			'<div class="va-contact-status"></div>' +
			'</div>';
		contactCard.hidden = false;
		messagesEl.scrollTop = messagesEl.scrollHeight;

		contactCard.querySelector('.va-c-skip').addEventListener('click', function () {
			finishContact();
		});
		contactCard.querySelector('.va-c-submit').addEventListener('click', submitContact);
	}

	function submitContact() {
		var mode = cfg.captureMode || 'email_only';
		var name = (contactCard.querySelector('.va-c-name') || {}).value || '';
		var email = (contactCard.querySelector('.va-c-email') || {}).value || '';
		var phone = (contactCard.querySelector('.va-c-phone') || {}).value || '';
		var status = contactCard.querySelector('.va-contact-status');

		var hasEmail = email.trim() !== '';
		var hasPhone = phone.trim() !== '';
		var needEmail = mode === 'email_only' || mode === 'email_and_phone';
		var needPhone = mode === 'phone_only' || mode === 'email_and_phone';
		var needEither = mode === 'email_or_phone';

		if ((needEmail && !hasEmail) || (needPhone && !hasPhone) || (needEither && !hasEmail && !hasPhone)) {
			status.textContent = 'Please fill in the requested contact details (or Skip).';
			status.style.color = '#b32d2e';
			return;
		}

		status.textContent = 'Sending…';
		status.style.color = '';

		fetch(cfg.restUrl + '/contact', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': restNonce || '' },
			credentials: 'same-origin',
			body: JSON.stringify({ session_id: sessionId, name: name, email: email, phone: phone }),
		})
			.then(function () {
				finishContact('Thanks, a rep can now follow up if needed.');
			})
			.catch(function () {
				finishContact();
			});
	}

	function finishContact(note) {
		contactDone = true;
		sessionStorage.setItem('vaAdvisorContactDone', '1');
		contactCard.hidden = true;
		contactCard.innerHTML = '';
		if (note) { addMessage('assistant', note); }
	}

	// The bootstrap sets vaAdvisorAutoOpen when the visitor clicked its launcher.
	if (window.vaAdvisorAutoOpen) {
		openPanel();
	}
})();
