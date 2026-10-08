# Phase 2 ledger (plan: phase-2/PHASE-2-PLAN.md)

Branch: phase-2 (from stage @ f0284ec0). One line per finished step; every deviation from the plan is a Ruling line.

- WP0: real Fable 5.1 prices ($10/$50/$0.25, write 1.25x) as constants + defaults; old shipped defaults ($3/$15/$0.30) migrated only when unchanged; daily ceiling now USD; DB version 4. Tests: tests/spend-fixtures.php 13/13 (watched RED first: average turn priced $0.0096), signals 53/53, filter 64/64.
- WP0: Ruling: default daily ceiling $25 until the client answers Q11 — about 800 average turns; cost if wrong: one settings change.
- WP0: Ruling: the token ceiling is replaced, not kept alongside — a token count can't express cost once most tokens are cheap cache reads; cost if wrong: re-add one setting.
