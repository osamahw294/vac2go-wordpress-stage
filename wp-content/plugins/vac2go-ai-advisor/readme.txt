=== Vac2Go AI Equipment Advisor ===
Contributors: HighWater
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later

Front-end AI equipment advisor for Vac2Go. Recommends a truck category from a plain-
language job description and answers GapVax HV-57 spec questions, grounded in a fixed
knowledge base with server-side guardrails, a deterministic output filter, full Q&A
logging, and a human review/correction workflow.

== Configuration ==
Define the Anthropic API key in wp-config.php:

    define('VA_ANTHROPIC_KEY', 'sk-ant-...');

Model: claude-fable-5-1 (constant VA_ADVISOR_MODEL in the main plugin file).

Admin: Vac2Go Advisor → Review Queue / Settings.
REST namespace: vac2go/v1 (/chat, /contact, /correction).

== Notes ==
- The output filter runs server-side on every model response regardless of what the
  model itself decided, so committal/pricing language is blocked even under prompt attack.
- Approved corrections are NOT auto-folded back into the system prompt in this beta;
  a human copies good corrections into Settings, System prompt. (v2 feedback loop.)
- Streaming: shipped with a "typing" indicator + normal request/response (not SSE) for
  the beta. Streaming remains a v2 item.
- Widget theme is matched to the live site's extracted brand tokens (red #e01f30,
  dark #383838, Open Sans / Poppins). The stylesheet is injected at runtime from JS so
  LiteSpeed's unused-CSS optimizer cannot purge the JS-rendered widget's selectors.

== Tests ==
Playwright suite lives in tests/ (not deployed to the server). From this plugin folder:
    npm install && npx playwright test
Covers launcher positioning, panel sizing across viewports + the 480px breakpoint,
theme-color matching, em-dash absence, single AI-disclosure, and real conversation
exchanges (recommendation / spec / pricing-refusal). Model-semantic assertions
auto-skip while VA_ANTHROPIC_KEY is a placeholder and activate once a real key is set.
