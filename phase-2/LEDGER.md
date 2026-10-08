# Phase 2 ledger (plan: phase-2/PHASE-2-PLAN.md)

Branch: phase-2 (from stage @ f0284ec0). One line per finished step; every deviation from the plan is a Ruling line.

- WP0: real Fable 5.1 prices ($10/$50/$0.25, write 1.25x) as constants + defaults; old shipped defaults ($3/$15/$0.30) migrated only when unchanged; daily ceiling now USD; DB version 4. Tests: tests/spend-fixtures.php 13/13 (watched RED first: average turn priced $0.0096), signals 53/53, filter 64/64.
- WP0: Ruling: default daily ceiling $25 until the client answers Q11 — about 800 average turns; cost if wrong: one settings change.
- WP0: Ruling: the token ceiling is replaced, not kept alongside — a token count can't express cost once most tokens are cheap cache reads; cost if wrong: re-add one setting.
- WP1: kb/fleet.json (41 units, 10 categories, 3 off-list names) + VA_Fleet::resolve(). Tests: tests/fleet-fixtures.php 227/227 (watched RED first: class missing; then 1 real failure, "water lines" hit the Water category through its bare name, fixed by matching listed phrases only).
- WP1: Ruling: Huber Scrubber stays in Liquid Vacuum (website group) and is also listed under Liquid Ring — matches the site while Q13 is open; cost if wrong: move one field.
- WP1: Ruling: Two Box Roll-Off Trailers → Roll-Off, also listed under Trailer (plan D7 default); cost if wrong: swap two fields.
- WP1: Ruling: single common words are not unit aliases (knight, classic, guzzler, vactor, f4, plain "high dump") — they would fire on ordinary sentences; a customer typing only "the Knight" gets the category via other context or a clarifying question; cost if wrong: add an alias.
- WP1: Ruling: "70 bbl" resolves to the Huber Dominator (plan C18) and "Vactor 2100i" to the Vactor 2100 Plus (only brochure held); both are flagged to the client; cost if wrong: one alias each.
