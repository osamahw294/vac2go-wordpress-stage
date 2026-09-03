=== Vac2Go AI Equipment Advisor ===
Contributors: HighWater
Requires PHP: 8.1
Stable tag: 2.1.0
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

If the host buffers the response despite Content-Type: text/event-stream,
X-Accel-Buffering: no, X-LiteSpeed-Cache-Control: no-cache and a 2KB comment pad, turn
the setting off; behaviour then matches the pre-2.1 buffered build exactly.

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
