# AI Equipment Advisor: model comparison

Staging, 9 and 13 October 2026. Advisor version 2.10.1.

## Purpose

To check whether the advisor can run on a cheaper model without losing answer quality. We compared:

- **Claude Fable 5.1** (current)
- **Claude Opus 5.5**
- **Claude Sonnet 5.5**

## How we tested

Each model ran the same two tests used for the Phase 2 sign-off, on staging, with the same knowledge base, rules and safety filters. Only the model changed. All three ran at the same reasoning depth ("high" effort).

1. **Full accuracy test:** 397 questions, each in a new conversation:
   - Vac2Go's Round 1 and Round 2 Q&A: 203
   - Unit specs, plus questions the literature can't answer: 120
   - Job descriptions needing a recommendation: 40
   - Phase 1 and V1 feedback checks: 16
   - Safety and misuse attempts (prices, deals, prompt extraction): 18
2. **Load test:** 30 visitors chatting at the same time, 4 questions each.

Answers were scored two ways:
- **Automatic checks:** right category, figures only from the knowledge base, no banned wording, safety rules followed.
- **AI grading:** a separate model compared each answer with Vac2Go's own answer for the 203 Q&A questions.

## Results

### Accuracy

| | Fable 5.1 | Opus 5.5 | Sonnet 5.5 |
|---|---|---|---|
| Automatic checks (397 questions) | 396 / 397\* | 397 / 397 | 397 / 397 |
| AI grading vs Vac2Go's answers (203) | 196 / 203 | 198 / 203 | 200 / 203 |
| Safety and misuse (18) | 18 / 18 | 18 / 18 | 18 / 18 |

\* The one Fable miss is a correct answer worded in a way the checker didn't recognise.

**How to read the AI grading:** a second AI checked each answer against Vac2Go's own answer to that question, and marked it down if it said anything extra or different. Fable scores slightly lower because it writes fuller answers that add correct details from other parts of Vac2Go's material (for example CDL rules from the Tractor section), and the grader counts those as "not in the reference". A difference of 2–4 questions out of 203 is within normal run-to-run variation.

**All three models are equally accurate.**

### Speed

| | Fable 5.1 | Opus 5.5 | Sonnet 5.5 |
|---|---|---|---|
| Normal traffic: typical answer | 5.4 s | 5.8 s | **4.7 s** |
| Normal traffic: slowest 10% | 8.6 s | 8.9 s | **6.5 s** |
| 30 visitors at once: typical full answer | 10.5 s | 14.9 s | **8.9 s** |
| 30 visitors at once: slowest 10% | **16.7 s** | 21.1 s | 17.3 s |
| 30 visitors at once: first words on screen | 7.9 s | 13.0 s | **7.1 s** |

Sonnet 5.5 is the fastest at normal traffic. Under load it's as fast as Fable or faster. Opus 5.5 is about 4–5 seconds slower under load.

### Cost

| | Fable 5.1 | Opus 5.5 | Sonnet 5.5 |
|---|---|---|---|
| List price per million tokens (input / output) | $10 / $50 | $4 / $20 | $2 / $10 |
| Measured cost per question, accuracy test | $0.042 | $0.019 | **$0.011** |
| Measured cost per question, load test | $0.100 | $0.046 | **$0.020** |
| Cost compared with Fable | – | about 55% less | **about 75–80% less** |
| Questions covered by $25 a day (estimate) | 300–450 | 650–1,000 | **1,100–2,000** |

The costs are measured from the real token usage logged on staging, not estimated. The "$25 a day" row is an estimate: it scales Fable's measured figure by each model's measured cost ratio.

## Issues found

| Model | Issue | Impact | Fix |
|---|---|---|---|
| Opus 5.5 | Used bold markdown (`**text**`) in 104 of 397 answers. | The chat window shows the asterisks literally. | Strip markdown on the server, plus a formatting rule. Small change. |
| Sonnet 5.5 | Bold markdown in 5 of 397 answers. | Same, but rare. | Same fix. |
| Sonnet 5.5 | 1 empty answer out of about 500 under load. The visitor saw "having trouble reaching the advisor". | Rare. The likely cause is the model spending its answer-length allowance on reasoning before writing. | Raise the answer-length allowance and retry once automatically on an empty answer. Small change. |
| All | None of the models leaked internal instructions, quoted prices, or agreed to deals or reservations. | – | – |

During the tests, 5 load-test requests dropped on the test machine's own connection before reaching the server. They're excluded above as test-side, not advisor errors.

## Recommendation

**Sonnet 5.5** gives the same accuracy as Fable 5.1, is as fast or faster, and costs about **75–80% less**.

Before switching, we would:

1. Make the two small fixes above (markdown, and the empty-answer retry).
2. Re-run the accuracy test on Sonnet 5.5 to confirm (about $5).

The model is a setting (WP admin → Vac2Go Advisor → Settings → Model), so switching, or switching back, takes one click and needs no code change.

## Test cost

| | Accuracy test | Load test | Total |
|---|---|---|---|
| Fable 5.1 (9 Oct) | $17.84 | $11.64 | $29.48 |
| Opus 5.5 (13 Oct) | $7.54 | $5.39 | $12.93 |
| Sonnet 5.5 (13 Oct) | $4.41 | $2.11 | $6.52 |

Staging is back on Fable 5.1, with all normal settings restored.
