=== Vac2Go AI Equipment Advisor ===
Contributors: HighWater
Requires PHP: 8.1
Stable tag: 2.1.4
License: GPLv2 or later

Front-end AI equipment advisor for Vac2Go. Recommends a truck category from a plain-
language job description and answers GapVax HV-57 spec questions, grounded in a fixed
knowledge base with server-side guardrails, a deterministic output filter, full Q&A
logging, and a human review/correction workflow.

== Configuration ==
Define the Anthropic API key in wp-config.php:

    define('VA_ANTHROPIC_KEY', 'sk-ant-...');

Model: claude-fable-5-1 (constant VA_ADVISOR_MODEL in the main plugin file).
Judge/classifier model: claude-haiku-4-5-20251001 (VA_ADVISOR_JUDGE_MODEL).

Admin: Vac2Go Advisor -> Review Queue / Stats / Settings.
REST namespace: vac2go/v1 (/nonce, /chat, /chat/stream, /contact, /correction).

== Notes ==
- The output filter runs server-side on every model response regardless of what the
  model itself decided, so committal/pricing language is blocked even under prompt attack.
- Approved corrections are NOT auto-folded back into the system prompt in this beta;
  a human copies good corrections into Settings, System prompt. (v2 feedback loop.)
- Widget theme is matched to the live site's extracted brand tokens (red #e01f30,
  dark #383838, Open Sans / Poppins). The stylesheet is injected at runtime from JS so
  LiteSpeed's unused-CSS optimizer cannot purge the JS-rendered widget's selectors.

== Streaming ==
POST /vac2go/v1/chat/stream streams the answer over Server-Sent Events using raw cURL
(wp_remote_post buffers the whole body and cannot stream). Toggle: Settings -> Streaming.

Safety: text is never emitted the moment it arrives. It accumulates in a pending buffer
and is released only at a sentence boundary, and only after the deterministic filter
stages have run on the FULL cumulative answer including that segment. A hit sends a
'replace' event and aborts the upstream request. The end-of-answer judge runs on the
complete text and can also issue a 'replace'. Exactly one log row is written per turn.

Events: delta | replace | done | close. The widget falls back to the buffered /chat
endpoint (with the typing indicator) when the endpoint returns 501, when the response
is not text/event-stream, or when the stream yields no content. The fallback reuses the
same request_id, so the idempotency window prevents a second model call.

Flush padding. Some hosts ignore X-Accel-Buffering and release the response only when
their own buffer fills, which collapses streaming into a single lump at the end. The
fix is to pad every SSE event with an inert comment line (clients ignore lines starting
with ':') so each message crosses that threshold and forces a real flush. Setting:
Settings -> Streaming -> Flush padding, default 4096 bytes. Set 0 on a host that
streams correctly by itself.

NEXCESS STAGING (b5205c85ce.nxcli.io), measured 2026-09-03.
That host buffers by SIZE at roughly 4KB and ignores X-Accel-Buffering. Evidence, from
a bare PHP file with no WordPress involved:

    unpadded, one tick per second:
      script wrote:  10:11:24 :25 :26 :27 :28
      client got:    10:11:29   <- all five at once, after the script exited

    padded to 8KB per tick:
      script wrote:  10:20:57 :58 :59 :21:00 :01
      client got:    10:20:57 :58 :59 :21:00 :01   <- real time

Identical over the public URL and over http://127.0.0.1 with a Host header, which rules
out any CDN or edge layer and places the buffering on the server itself. Also ruled out:
PHP (output_buffering=0, implicit_flush=On, zlib.output_compression=Off), WordPress,
this plugin, and LiteSpeed (Server: nginx, X-Cache-Handler: cache-enabler-engine).

Padding costs roughly 4KB per sentence of answer, so tens of KB per reply. That is the
price of streaming on a host whose nginx config we cannot change. The cleaner fix is to
have the host disable response buffering for /wp-json/vac2go/v1/chat/stream, after
which va_stream_pad can go to 0.

== Data retention ==
Every Q&A turn is logged to {prefix}va_advisor_log indefinitely. There is NO automated
retention or purge job in this release. Deletion requests are handled manually: Review
Queue -> the session's row -> "Delete session data", which hard-deletes every row for
that session_id. Automated retention is a future item.

Logged per turn: question, answer, pre-filter model text (only when filtered), filter
stage/reason, error type, token counts, contact details if submitted, a keyed HMAC of
the visitor IP (not the IP itself), and the user agent.

== Tests ==
Playwright suite in tests/. From this plugin folder:

    npm install && npx playwright install chromium && npx playwright test

Point it at the site under test:

    VA_BASE_URL=https://staging.example.com npx playwright test

Covers cold-load launcher visibility with zero simulated interaction (desktop and
mobile), nonce recovery from a garbage nonce, idempotent resend, injected-history
rejection, XSS rendering as text, CSV export cell safety, accessibility (aria
attributes, Escape, focus return), em-dash absence, single AI disclosure, and live
guardrail scenarios. Model-semantic assertions auto-skip until VA_TEST_LIVE=1.
