/* Vac2Go Equipment Advisor, front-end widget (vanilla JS, no build step).
   Loaded on demand by the inline bootstrap after the visitor clicks the launcher. */
(function () {
	'use strict';

	if (window.vaAdvisorBooted) { return; }
	window.vaAdvisorBooted = true;

	var cfg = window.vaAdvisor || {};
	var CONTACT_URL = cfg.contactUrl || 'https://vac2go.com/contact/';
	var REP_PHONE = cfg.repPhone || '855-822-7246';
	var MAX_MESSAGE = 2000; // server-side cap per turn (VA_REST::MAX_MESSAGE_CH)

	// tel: link from a human-formatted number. A bare 10-digit number is North American.
	function telHref(phone) {
		var digits = String(phone).replace(/[^0-9+]/g, '');
		if (/^\d{10}$/.test(digits)) { digits = '+1' + digits; }
		return 'tel:' + digits;
	}
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

	var sessionId = getSessionId();  // reassigned by newChat()
	var contactAsked = false;
	var contactDone = sessionStorage.getItem('vaAdvisorContactDone') === '1';
	var contactSent = sessionStorage.getItem('vaAdvisorContactSent') === '1'; // details actually submitted
	var busy = false;
	var restoring = false; // redrawing an earlier conversation; input is held until done
	var snapOnReply = false; // pull back to the bottom when the pending answer starts
	var lastReplyEl = null;  // newest assistant message, so the follow-up card sits right under it

	// Messages typed while an answer is still coming. They show at once, and go out
	// together as the next turn when the current answer finishes.
	var queue = [];

	// Rate-limit lock. 'ip': wait until lockUntil. 'session': this conversation is
	// full, only a new chat continues. Kept in sessionStorage so a reload stays locked.
	var lockKind = null;
	var lockUntil = 0;
	var lockTimer = null;

	// ---- CSRF nonce: fetched fresh at boot (never baked into cached HTML) ----
	//
	// NOTE: every public request below uses credentials:'omit', deliberately.
	// These endpoints are public and must behave identically for every visitor. Sending
	// the WordPress auth cookie drags the request into core's cookie-authentication
	// path, where rest_cookie_check_errors() requires the nonce to match the logged-in
	// user and rejects it with rest_cookie_invalid_nonce BEFORE our permission callback
	// runs. That broke the chat for anyone browsing while logged into wp-admin, while
	// working fine for real (logged-out) customers.
	//
	// With no cookie sent, core skips cookie auth, and both the nonce fetch and the
	// request that uses it run as user 0, so they always agree. The nonce remains CSRF
	// protection only; it never authenticated anyone. Abuse is bounded by the rate
	// layers, not by this.
	//
	// admin.js is different and MUST keep credentials:'same-origin': /correction is a
	// genuinely privileged endpoint gated on manage_options.
	var restNonce = null;
	function fetchNonce() {
		// credentials:'omit' is deliberate and load-bearing, see NOTE below.
		return fetch(cfg.restUrl + '/nonce?_=' + Date.now(), {
			credentials: 'omit',
			cache: 'no-store',
			headers: { 'Cache-Control': 'no-cache', 'Pragma': 'no-cache' },
		})
			.then(function (r) { return r.json(); })
			.then(function (d) { restNonce = d && d.nonce ? d.nonce : null; return restNonce; })
			.catch(function () { return null; });
	}
	var nonceReady = fetchNonce();

	// Redraw the panel after a reload. The model already remembers the conversation
	// (history is rebuilt server-side from the log), so without this the visitor sees an
	// empty box while the advisor answers as though the thread were still going.
	function loadHistory() {
		return fetch(cfg.restUrl + '/history?session_id=' + encodeURIComponent(sessionId), {
			credentials: 'omit',
			cache: 'no-store',
			headers: { 'X-WP-Nonce': restNonce || '' },
		})
			.then(function (r) { return r.ok ? r.json() : { turns: [] }; })
			.catch(function () { return { turns: [] }; });
	}
	var historyReady = nonceReady.then(loadHistory);

	var GREETING =
		"Hi, I'm the Vac2Go Equipment Advisor. Tell me about your job and I'll point you to the right truck category.\n\n" +
		"A few things help: what are you vacuuming, cleaning, or excavating? Roughly how much? And what are the site conditions?";

	// ---- DOM ----
	var root = document.createElement('div');
	root.className = 'va-advisor-root';
	root.innerHTML =
		'<button class="va-launcher" aria-label="Open equipment advisor chat" aria-expanded="false">' +
			'<span class="va-icon">' +
				'<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="currentColor" d="M12 3C6.5 3 2 6.6 2 11c0 2.4 1.3 4.6 3.5 6-.2 1-.8 2.3-1.8 3.4 1.7-.2 3.4-.8 4.8-1.8 1.1.3 2.3.4 3.5.4 5.5 0 10-3.6 10-8s-4.5-8-10-8z"/></svg>' +
				'<span class="va-dot"></span>' +
			'</span>' +
			'<span class="va-launcher-label">Ask the Equipment Advisor</span>' +
		'</button>' +
		'<div class="va-panel" role="dialog" aria-modal="true" aria-label="Vac2Go Equipment Advisor" hidden>' +
			'<div class="va-header">' +
				'<div class="va-title">Vac2Go Equipment Advisor</div>' +
				'<a class="va-call" href="' + escapeHtml(telHref(REP_PHONE)) + '" aria-label="Call a Vac2Go rep at ' + escapeHtml(REP_PHONE) + '" title="Call a Vac2Go rep: ' + escapeHtml(REP_PHONE) + '">' +
					'<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1A17 17 0 0 1 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1l-2.3 2.2z"/></svg>' +
				'</a>' +
				'<button class="va-new" aria-label="Start a new chat" title="Start a new chat">New chat</button>' +
				'<button class="va-close" aria-label="Close chat">&times;</button>' +
			'</div>' +
			'<div class="va-messages" aria-live="polite"></div>' +
			'<div class="va-lock" role="status" hidden></div>' +
			'<form class="va-inputbar">' +
				'<input type="text" name="website" class="va-hp" value="" tabindex="-1" autocomplete="off" aria-hidden="true">' +
				'<textarea class="va-input" rows="1" placeholder="Describe your job\u2026" maxlength="2000" aria-label="Your message"></textarea>' +
				'<button type="submit" class="va-send" aria-label="Send message">Send</button>' +
			'</form>' +
			'<div class="va-disclosure">' +
				'<a class="va-contact-link" href="' + escapeHtml(CONTACT_URL) + '" target="_blank" rel="noopener">Contact a Vac2Go rep</a>' +
				'<span class="va-disclosure-text">This chat is automated and logged for quality review. No pricing or booking here.</span>' +
			'</div>' +
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
	var newBtn = root.querySelector('.va-new');
	var messagesEl = root.querySelector('.va-messages');
	var form = root.querySelector('.va-inputbar');
	var input = root.querySelector('.va-input');
	var sendBtn = root.querySelector('.va-send');
	var hpField = root.querySelector('.va-hp');
	var lockBar = root.querySelector('.va-lock');

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
		root.classList.add('va-open');
		launcher.classList.add('va-hidden');
		launcher.setAttribute('aria-expanded', 'true');
		panel.addEventListener('keydown', trapKeydown);
		input.focus();
		if (!opened) {
			opened = true;
			addMessage('assistant', GREETING);
			restore();
		}
	}

	/**
	 * Redraw an earlier conversation, if this session has one.
	 *
	 * The input is held while this runs. Without that, a message sent during the fetch
	 * would be painted BEFORE the restored turns, leaving the transcript out of order.
	 * The indicator is delayed slightly so a fast restore, or a session with no history
	 * at all, does not flash a spinner for one frame.
	 */
	function restore() {
		restoring = true;
		syncInput();

		var indicator = null;
		var delay = setTimeout(function () { indicator = showTyping(); }, 180);

		historyReady
			.then(function (d) {
				var turns = (d && d.turns) || [];
				for (var i = 0; i < turns.length; i++) {
					addMessage('user', turns[i].question);
					addMessage('assistant', turns[i].answer);
				}
				if (turns.length) { scrollToBottom(true); }
			})
			.catch(function () { /* no history is not an error; leave the greeting */ })
			.finally(function () {
				clearTimeout(delay);
				if (indicator && indicator.parentNode) { indicator.remove(); }
				restoring = false;
				syncInput();
				if (!input.disabled) { input.focus(); }
			});
	}

	/**
	 * The one place that decides whether the visitor can type. Held while an earlier
	 * conversation is being redrawn, and while a rate limit is in force. NOT held while
	 * an answer is streaming: anything sent then is queued instead of lost.
	 */
	function syncInput() {
		var locked = isLocked();
		var off = restoring || locked;
		input.disabled = off;
		sendBtn.disabled = off;
		input.placeholder = restoring ? 'Loading your conversation\u2026'
			: lockKind === 'session' ? 'Start a new chat to keep going'
			: locked ? 'Paused for a moment\u2026'
			: 'Describe your job\u2026';
		newBtn.disabled = busy;
	}

	// Start over: a brand new session id, so the server has nothing to rebuild from and
	// the advisor genuinely forgets. The old conversation stays in the review queue.
	function newChat() {
		sessionId = uuid();
		try {
			sessionStorage.setItem('vaAdvisorSession', sessionId);
			sessionStorage.removeItem('vaAdvisorContactDone');
			sessionStorage.removeItem('vaAdvisorContactSent');
		} catch (e) { /* private mode: the in-memory id still works for this page */ }

		contactAsked = false;
		contactDone = false;
		contactSent = false;
		lastReplyEl = null;
		queue = [];
		historyReady = Promise.resolve({ turns: [] });

		// A full conversation is cured by a new one. A per-IP pause is not: it is about
		// the visitor, not the conversation, so it stays until it runs out.
		if (lockKind === 'session') { clearLock(); }

		messagesEl.innerHTML = '';
		stickBottom = true;
		restoring = false;
		syncInput();

		addMessage('assistant', GREETING);
		if (!input.disabled) { input.focus(); }
	}
	function closePanel() {
		panel.hidden = true;
		root.classList.remove('va-open');
		launcher.classList.remove('va-hidden');
		launcher.setAttribute('aria-expanded', 'false');
		panel.removeEventListener('keydown', trapKeydown);
		launcher.focus(); // focus returns to the launcher
	}

	launcher.addEventListener('click', openPanel);
	closeBtn.addEventListener('click', closePanel);
	newBtn.addEventListener('click', newChat);

	// ---- sticky scroll ----
	// Follow the newest text only while the reader is already at the bottom. The
	// moment they scroll up to re-read something, stop yanking them back down; when
	// they return to the bottom, start following again. Same behaviour as ChatGPT.
	var stickBottom = true;
	var lastTop = 0;
	var STICK_SLACK = 8; // px from the true bottom that still counts as "at the bottom"

	function atBottom() {
		return messagesEl.scrollHeight - messagesEl.scrollTop - messagesEl.clientHeight <= STICK_SLACK;
	}

	// Detach on DIRECTION, not on position. Judging by distance-from-bottom alone meant
	// a small scroll up left the reader inside the slack, still counted as "at the
	// bottom", and the next chunk yanked them back, so escaping took a hard flick.
	// Any upward movement now detaches, however slight; returning to the bottom
	// re-attaches. Comparing scrollTop covers wheel, trackpad, touch, keyboard and the
	// scrollbar in one place. scrollToBottom() updates lastTop itself, so its own
	// scrolling is never mistaken for the reader moving.
	messagesEl.addEventListener('scroll', function () {
		var top = messagesEl.scrollTop;
		if (top < lastTop - 1) {
			stickBottom = false;
		} else if (atBottom()) {
			stickBottom = true;
		}
		lastTop = top;
	}, { passive: true });

	// Called the first time an answer paints. If the reader wandered up while waiting,
	// bring them back once, so they see it start. Sticky rules resume immediately
	// after, so scrolling up DURING the answer still detaches as normal.
	function snapIfPending() {
		if (snapOnReply) {
			snapOnReply = false;
			scrollToBottom(true);
		}
	}

	// force: the reader just sent a message, so always take them to it.
	function scrollToBottom(force) {
		if (force) { stickBottom = true; }
		if (stickBottom) {
			messagesEl.scrollTop = messagesEl.scrollHeight;
			lastTop = messagesEl.scrollTop;
		}
	}

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

	// before: insert ahead of this node instead of at the end. A reply takes the typing
	// indicator's place, which keeps it above any messages queued while it was coming.
	function addMessage(role, text, before) {
		var el = document.createElement('div');
		el.className = 'va-msg va-msg-' + role;
		el.innerHTML = '<div class="va-bubble">' + renderText(text) + '</div>';
		if (before && before.parentNode === messagesEl) {
			messagesEl.insertBefore(el, before);
		} else {
			messagesEl.appendChild(el);
		}
		scrollToBottom(role === 'user');
		if (role === 'assistant') { lastReplyEl = el; }
		return el;
	}

	// Swap the typing indicator for the reply, in place.
	function replaceTyping(typing, text) {
		var el = addMessage('assistant', text, typing);
		if (typing && typing.parentNode) { typing.remove(); }
		return el;
	}

	function showTyping() {
		var el = document.createElement('div');
		el.className = 'va-msg va-msg-assistant va-typing';
		el.innerHTML = '<div class="va-bubble"><span></span><span></span><span></span></div>';
		messagesEl.appendChild(el);
		scrollToBottom();
		return el;
	}

	function postChat(payload) {
		return fetch(cfg.restUrl + '/chat', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': restNonce || '' },
			credentials: 'omit',
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
			credentials: 'omit',
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

	// Rendering is deliberately decoupled from arrival. The model sends text in
	// bursts, and the server releases a word group at a time, so painting each burst
	// as it lands looks like paragraphs thumping into place. Instead everything
	// received goes into a buffer, and an animation-frame loop reveals it at a steady
	// rate, so the reader always sees a smooth flow no matter how lumpy the network
	// was. Each revealed group fades in, which hides the discreteness of the steps.
	function readStream(reader, typing) {
		var decoder = new TextDecoder();
		var buf = '';
		var wrap = null;
		var bubbleEl = null;
		var full = '';        // everything received so far
		var shown = 0;        // characters actually painted
		var gotAny = false;
		var doneData = null;  // the server's 'done' event: limit and follow-up signals
		var ended = false;    // upstream finished
		var raf = null;
		var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

		function ensureBubble() {
			if (!wrap) {
				wrap = replaceTyping(typing, '');
				bubbleEl = wrap.querySelector('.va-bubble');
				bubbleEl.innerHTML = '';
				snapIfPending();
			}
		}

		function appendChunk(text) {
			var parts = text.split('\n');
			for (var i = 0; i < parts.length; i++) {
				if (i > 0) { bubbleEl.appendChild(document.createElement('br')); }
				if (parts[i]) {
					var s = document.createElement('span');
					if (!reduce) { s.className = 'va-tok'; }
					s.textContent = parts[i];
					bubbleEl.appendChild(s);
				}
			}
			scrollToBottom();
		}

		// Re-render once at the end so URLs become real links. Doing this per frame
		// would restart every fade animation.
		function finalize() {
			if (bubbleEl) {
				bubbleEl.innerHTML = renderText(full);
				scrollToBottom();
			}
		}

		function tick() {
			raf = null;
			var pending = full.length - shown;

			if (pending > 0) {
				ensureBubble();
				// Aim to clear the backlog over roughly half a second at 60fps, so a
				// big burst speeds up rather than falling behind, while a trickle
				// still reveals a character or two per frame.
				var step = Math.max(1, Math.min(20, Math.ceil(pending / 30)));
				appendChunk(full.slice(shown, shown + step));
				shown += step;
			}

			if (!ended || shown < full.length) {
				raf = requestAnimationFrame(tick);
			} else {
				finalize();
			}
		}

		function startTicking() {
			if (raf === null) { raf = requestAnimationFrame(tick); }
		}

		function frame(ev, dataStr) {
			var d;
			try { d = JSON.parse(dataStr); } catch (e) { return; }

			if (ev === 'delta') {
				if (d.text) {
					full += d.text;
					gotAny = true;
					startTicking();
				}
			} else if (ev === 'replace') {
				// A refusal replaces everything already shown. Show it at once rather
				// than typing it out: it is a correction, not part of the answer.
				full = d.text || '';
				shown = full.length;
				gotAny = true;
				ensureBubble();
				bubbleEl.innerHTML = renderText(full);
				scrollToBottom();
			} else if (ev === 'done') {
				doneData = d;
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
				if (res.done) {
					ended = true;
					startTicking();
					// Resolve only once every received character has been painted,
					// otherwise the input would re-enable mid-animation.
					return new Promise(function (resolve) {
						(function waitDrain() {
							if (shown >= full.length) {
								finalize();
								resolve({ streamed: true, gotAny: gotAny, done: doneData });
							} else {
								setTimeout(waitDrain, 40);
							}
						})();
					});
				}
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
				var reply =
					(data && data.reply) ||
					"Sorry, I couldn't get a response. Please reach a Vac2Go rep at " + CONTACT_URL + ".";
				snapIfPending();
				replaceTyping(typing, reply);
				return data;
			});
	}

	// painted: the user bubbles are already on screen (a flushed queue).
	function send(message, painted) {
		if (busy || restoring || isLocked() || !message.trim()) { return; }
		busy = true;
		snapOnReply = true;
		syncInput();
		if (!painted) { addMessage('user', message); }
		var typing = showTyping();
		var meta = null;

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
				if (res && res.streamed && res.gotAny) { return res.done; }
				// Streaming unsupported, buffered by the host, or it produced nothing.
				// Reusing the same request_id means the server replays a stored answer
				// instead of billing a second model call.
				return bufferedTurn(payload, typing);
			})
			.then(function (m) { meta = m || null; })
			.catch(function () {
				replaceTyping(
					typing,
					"Sorry, I'm having trouble connecting. Please reach a Vac2Go rep at " + CONTACT_URL + "."
				);
			})
			.finally(function () {
				busy = false;
				snapOnReply = false;
				if (meta && meta.limited) {
					applyLock(meta.limit, meta.retry_after);
				} else if (meta && meta.followup) {
					maybeAskContact(meta.followup, message);
				}
				syncInput();
				flushQueue();
			});
	}

	form.addEventListener('submit', function (e) {
		e.preventDefault();
		var v = input.value;
		if (!v.trim() || restoring || isLocked()) { return; }
		input.value = '';
		autoGrow();
		if (busy) {
			enqueue(v);
		} else {
			send(v);
		}
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

	// ---- queue: nothing typed mid-answer is lost ----
	function enqueue(text) {
		var el = addMessage('user', text);
		el.classList.add('va-queued');
		el.setAttribute('title', 'Sends when the current answer finishes');
		queue.push({ text: text, el: el });
	}

	// Everything queued goes out as ONE turn, so the advisor answers it all at once
	// (people often send a thought in two or three pieces). Capped at the server's
	// per-message limit; anything past it waits for the turn after.
	function flushQueue() {
		if (busy || restoring || isLocked() || !queue.length) { return; }
		var take = [queue.shift()];
		var len = take[0].text.length;
		while (queue.length && len + 2 + queue[0].text.length <= MAX_MESSAGE) {
			len += 2 + queue[0].text.length;
			take.push(queue.shift());
		}
		take.forEach(function (q) {
			q.el.classList.remove('va-queued');
			q.el.removeAttribute('title');
		});
		send(take.map(function (q) { return q.text; }).join('\n\n'), true);
	}

	// ---- rate-limit lock ----
	// The server already refuses every request from a limited visitor before any
	// model call, so no tokens are spent either way. This makes that visible: the
	// input locks, and the visitor sees exactly how long is left, or is offered a
	// new chat when it is the conversation itself that is full.
	function isLocked() {
		if (lockKind === 'session') { return true; }
		return lockKind === 'ip' && Date.now() < lockUntil;
	}

	function applyLock(kind, retryAfter) {
		if (kind === 'session') {
			lockKind = 'session';
			lockUntil = 0;
		} else if (retryAfter > 0) {
			lockKind = 'ip';
			lockUntil = Date.now() + retryAfter * 1000;
		} else {
			return;
		}
		try {
			sessionStorage.setItem('vaAdvisorLock', JSON.stringify({ kind: lockKind, until: lockUntil, session: sessionId }));
		} catch (e) { /* private mode: the in-memory lock still holds for this page */ }
		renderLock();
	}

	function clearLock() {
		lockKind = null;
		lockUntil = 0;
		if (lockTimer) { clearInterval(lockTimer); lockTimer = null; }
		try { sessionStorage.removeItem('vaAdvisorLock'); } catch (e) { /* ignore */ }
		lockBar.hidden = true;
		lockBar.innerHTML = '';
		syncInput();
	}

	function formatWait(ms) {
		var s = Math.max(0, Math.ceil(ms / 1000));
		var m = Math.floor(s / 60);
		var r = s % 60;
		return m + ':' + (r < 10 ? '0' : '') + r;
	}

	function renderLock() {
		if (lockTimer) { clearInterval(lockTimer); lockTimer = null; }
		lockBar.hidden = false;

		if (lockKind === 'session') {
			lockBar.innerHTML =
				'<span>This conversation has reached its limit.</span>' +
				'<button type="button" class="va-lock-new">Start a new chat</button>';
			lockBar.querySelector('.va-lock-new').addEventListener('click', newChat);
			syncInput();
			return;
		}

		lockBar.innerHTML = '<span>Paused. You can send again in <strong class="va-lock-time"></strong></span>';
		var timeEl = lockBar.querySelector('.va-lock-time');
		var tick = function () {
			var left = lockUntil - Date.now();
			if (left <= 0) {
				clearLock();
				if (!input.disabled && !panel.hidden) { input.focus(); }
				flushQueue();
				return;
			}
			timeEl.textContent = formatWait(left);
		};
		tick();
		if (lockKind) { lockTimer = setInterval(tick, 1000); }
		syncInput();
	}

	// A reload during a lock keeps it: the server would refuse anyway.
	(function resumeLock() {
		var saved = null;
		try { saved = JSON.parse(sessionStorage.getItem('vaAdvisorLock') || 'null'); } catch (e) { saved = null; }
		if (!saved) { return; }
		if (saved.kind === 'ip' && saved.until > Date.now()) {
			lockKind = 'ip';
			lockUntil = saved.until;
			renderLock();
		} else if (saved.kind === 'session' && saved.session === sessionId) {
			lockKind = 'session';
			renderLock();
		} else {
			try { sessionStorage.removeItem('vaAdvisorLock'); } catch (e) { /* ignore */ }
		}
	})();

	// ---- contact capture ----
	// Offered once per conversation, and only when the server marks the answer as a
	// natural moment for a rep: a category recommendation, a specific unit, a question
	// the advisor could not answer, or pricing, contracts or availability. It sits in
	// the conversation right under that answer, so it never covers what was just said.
	// reason: the server's follow-up tag. 'contact' means the visitor typed their own
	// details into the chat: they want a rep now, so the form comes back even if it was
	// shown or skipped earlier (but not once details were actually sent), pre-filled
	// with what they typed so a single click sends it.
	function maybeAskContact(reason, typed) {
		if ('contact' === reason) {
			if (contactSent) { return; }
			Array.prototype.forEach.call(messagesEl.querySelectorAll('.va-contact-card'), function (el) { el.remove(); });
			contactAsked = true;
			renderContactCard(typed);
			return;
		}
		if (contactAsked || contactDone) { return; }
		contactAsked = true;
		renderContactCard();
	}

	function renderContactCard(typed) {
		var mode = cfg.captureMode || 'email_only';
		var fields = '';
		// Name is its own toggle, so "just give me your email" is a real option.
		if (cfg.captureName !== false) {
			fields += '<input type="text" class="va-c-name" autocomplete="name" placeholder="Name" aria-label="Name">';
		}
		if (mode === 'email_only' || mode === 'email_or_phone' || mode === 'email_and_phone') {
			fields += '<input type="email" class="va-c-email" autocomplete="email" placeholder="Email" aria-label="Email">';
		}
		if (mode === 'phone_only' || mode === 'email_or_phone' || mode === 'email_and_phone') {
			fields += '<input type="tel" class="va-c-phone" autocomplete="tel" placeholder="Phone" aria-label="Phone">';
		}

		var card = document.createElement('div');
		card.className = 'va-contact-card';
		card.setAttribute('role', 'group');
		card.setAttribute('aria-label', 'Ask a rep to follow up');
		card.innerHTML =
			'<div class="va-contact-head">' +
				'<span class="va-contact-title">Want a rep to follow up?</span>' +
				'<button type="button" class="va-c-skip" aria-label="No thanks" title="No thanks">&times;</button>' +
			'</div>' +
			'<div class="va-contact-fields">' + fields +
				'<button type="button" class="va-c-submit">Send</button>' +
			'</div>' +
			'<div class="va-contact-status" aria-live="polite"></div>';

		if (lastReplyEl && lastReplyEl.parentNode === messagesEl) {
			messagesEl.insertBefore(card, lastReplyEl.nextSibling);
		} else {
			messagesEl.appendChild(card);
		}
		scrollToBottom();

		// Pre-fill from details typed into the chat (an email; a phone with area code).
		var text = String(typed || '');
		var email = text.match(/[A-Z0-9._%+-]+@[A-Z0-9-]+(\.[A-Z0-9-]+)+/i);
		var phone = text.match(/(\+?1[\s.-]?)?(\(\d{3}\)\s?|\b\d{3}[\s.-])\d{3}[\s.-]\d{4}\b/);
		if (email && card.querySelector('.va-c-email')) { card.querySelector('.va-c-email').value = email[0]; }
		if (phone && card.querySelector('.va-c-phone')) { card.querySelector('.va-c-phone').value = phone[0].trim(); }

		card.querySelector('.va-c-skip').addEventListener('click', function () {
			finishContact(card);
		});
		card.querySelector('.va-c-submit').addEventListener('click', function () {
			submitContact(card);
		});
	}

	function submitContact(card) {
		var mode = cfg.captureMode || 'email_only';
		var name = (card.querySelector('.va-c-name') || {}).value || '';
		var email = (card.querySelector('.va-c-email') || {}).value || '';
		var phone = (card.querySelector('.va-c-phone') || {}).value || '';
		var status = card.querySelector('.va-contact-status');

		var hasEmail = email.trim() !== '';
		var hasPhone = phone.trim() !== '';
		var needEmail = mode === 'email_only' || mode === 'email_and_phone';
		var needPhone = mode === 'phone_only' || mode === 'email_and_phone';
		var needEither = mode === 'email_or_phone';

		if ((needEmail && !hasEmail) || (needPhone && !hasPhone) || (needEither && !hasEmail && !hasPhone)) {
			status.textContent = mode === 'email_or_phone' ? 'Add an email or phone, or close this.'
				: 'Add your ' + (needEmail && needPhone ? 'email and phone' : needPhone ? 'phone' : 'email') + ', or close this.';
			status.style.color = '#b32d2e';
			return;
		}

		status.textContent = 'Sending\u2026';
		status.style.color = '';

		fetch(cfg.restUrl + '/contact', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': restNonce || '' },
			credentials: 'omit',
			body: JSON.stringify({ session_id: sessionId, name: name, email: email, phone: phone }),
		})
			.then(function () {
				contactSent = true;
				try { sessionStorage.setItem('vaAdvisorContactSent', '1'); } catch (e) { /* ignore */ }
				finishContact(card, 'Thanks, a rep can now follow up if needed.');
			})
			.catch(function () {
				finishContact(card);
			});
	}

	function finishContact(card, note) {
		contactDone = true;
		try { sessionStorage.setItem('vaAdvisorContactDone', '1'); } catch (e) { /* ignore */ }
		if (card && card.parentNode) {
			if (note) { addMessage('assistant', note, card); }
			card.remove();
		}
	}

	// The bootstrap sets vaAdvisorAutoOpen when the visitor clicked its launcher.
	if (window.vaAdvisorAutoOpen) {
		openPanel();
	}
})();
