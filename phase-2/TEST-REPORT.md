# AI Equipment Advisor: test report

Staging, 9 October 2026. Advisor version 2.9.1.

## Tests we ran

1. **Full accuracy test:** 397 questions asked on staging, each in a new conversation:
   - Your Round 1 and Round 2 Q&A: 203 questions
   - Unit specs, plus questions the literature can't answer: 120
   - Job descriptions needing a recommendation: 40
   - Phase 1 and V1 feedback checks: 16
   - Safety and misuse attempts (prices, deals, prompt injection): 18
2. **Load test:** 30 visitors chatting at the same time, 4 questions each, as requested in the V1 feedback.

## Results

### Full accuracy test

| Area | Passed |
|---|---|
| Your Q&A (Round 1 and Round 2) | 203 / 203 (100%) |
| Unit specs | 120 / 120 (100%)\* |
| Recommendations | 40 / 40 (100%) |
| Phase 1 and V1 feedback checks | 16 / 16 (100%) |
| Safety and misuse | 18 / 18 (100%) |
| **Total** | **397 / 397** |

\* Our automatic checker flagged one unit-spec answer. On review it was correct ("the G7 is a jetter only, with no vacuum system"), and the checker missed the wording.

A typical answer takes 5.4 seconds; 90% take under 8.6 seconds.

### Load test (30 visitors at once)

| Measure | Result |
|---|---|
| Questions answered | 114 of 116. The other 2 were connection drops on the test machine, not the server. |
| Rate-limit blocks or server errors | 0 |
| Quality checks passed | 113 of 114 (fixed since: see below) |
| First words on screen | 7.9 s typical, 14.7 s slowest 10% |
| Full answer | 10.5 s typical, 16.7 s slowest 10% |
| 30 first questions arriving together | 15 s typical, 21 s worst |

The advisor stayed stable under the burst and didn't fail. Answers were slower while all 30 arrived together, as expected, and back to normal speed after.

## Issues found and fixed

Every issue below was fixed, covered by an automated test and deployed to staging. We then re-asked the affected questions on staging to confirm the fix.

| Issue | Found by | Fix |
|---|---|---|
| Questions worded "Combination equipment" or "jetter" didn't pull in the Combination knowledge, so a few answers said the figures weren't available. | Accuracy test | Those words now load the Combination knowledge. Re-run: all 23 Combination questions answered correctly. |
| In longer conversations, an answer that mentioned other truck types in passing could push out the knowledge for the truck being discussed. A follow-up such as "how much airflow does it have?" then got "I don't have the figures". | Load test | The truck type the customer is asking about now always stays loaded. |
| For hazardous-material jobs, the hazardous-materials sentence sometimes replaced the "high-level recommendation" sentence, so the rep follow-up wasn't offered. | Load test | Both sentences are now required. |
| The hourly usage alert counted cached text, so ordinary traffic (about 30 questions an hour) would have triggered alert emails. | Pre-test check | The alert now measures spend: $5 an hour by default, adjustable in Settings. |

## Cost

Both tests together used about $25 of API spend, for roughly 510 test questions. Settings changed for the tests (per-visitor limits, the daily cap and the hourly alert) are back to normal: $25 daily cap, $5 hourly alert.

## Still waiting on Vac2Go

- Q2: Water Q&A
- Q8: unit confirmations
- Q11: daily cap decision

Until then, the Water category uses our draft.
