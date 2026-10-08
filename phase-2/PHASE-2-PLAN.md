# Vac2Go AI Equipment Advisor: Phase 2 Plan

Living document. Update the **Status board** (section 12) and the **Progress log** (section 11) as work happens, so anyone can pick this up mid-way.

- Ticket: `phase-2/phase-2-items/ticket.txt` (Oct 6, 2026)
- Plugin: `wp-content/plugins/vac2go-ai-advisor` (v2.6.1 on staging at the time of writing), branch `stage`
- Staging only: https://b5205c85ce.nxcli.io. **Nothing goes to production in this phase.**
- Working files (not in git): `phase-2/work/`
- Plan version: **2** (2026-10-08, after a full verification pass; see Appendix B)

---

## 1. What the client is asking for

| # | Ticket ask | What it means for us |
|---|---|---|
| T1 | Integrate all category-level semantic information so the chatbot understands every category, not just the first one. | Load the **Round 2 Knowledge Base Entries** (8 categories) and the **Round 1** entries (Industrial Vacuum, not yet received) into the advisor, and make it answer from them. |
| T2 | Add all relevant brochures and unit documents so it can answer detailed questions about individual units. | Turn the brochures and spec sheets (tagged to units in the tagging spreadsheet) into accurate, customer-safe **unit cards**. |
| T3 | Review and incorporate the phase-one feedback (failure cases, unclear answers, missing topics) to refine intents, training phrases, responses and fallback behavior. | V1 feedback doc: **done** (v2.6.0 / v2.6.1). Still to do: fix the failure patterns in the Phase 1 logs (section 4) and update the rules the new knowledge contradicts. |
| T4 | Staging only, not production. | Deploy to staging via `git pull` on the `stage` branch only. |
| — | Deliver an updated chatbot on staging, ready for internal review and testing. | Plus a test report showing it answers correctly across all categories and units. |

The ticket uses intent-based chatbot terms. How they map onto this build (useful when reporting back):

| Ticket term | In this build |
|---|---|
| Intents | Category recommendation + category/unit knowledge packs (WP2–WP4) |
| Training phrases | Synonym ring and unit aliases (WP1), plus the test questions (WP7) |
| Responses | Client Q&A (WP2) and unit cards (WP3), quoted with the existing hedges |
| Fallback behavior | "I don't have that detail" + rep CTA; follow-up card tagging (done in v2.6.0) |

---

## 2. What we were given (inventory, all read)

| Item | Contents | Notes from the read |
|---|---|---|
| `ticket.txt` | The ask (above) | — |
| `Vac2Go AI Equipment Advisor - Round 2 Knowledge Base Entries.docx` | **173 questions** with answers, 8 categories: Hydro Excavator 22, Combination 23, Liquid Vacuum 23, Liquid Ring 25, Trailer 21, Tanker 25, Roll-Off 19, Tractor 15. | Sections are numbered **2–9** (section 1, Industrial Vacuum, is the missing Round 1). Every category has only **Groups A, D, E**; **Groups B and C are absent everywhere**. No comments, tracked changes or hidden text. 10 "not the right choice for" questions are italicised with literal asterisks (look like late additions; all answered). Text: `work/extracted-text/kb-round2.txt` |
| `Vac2Go Brochure to Unit Tagging v2.xlsx` | 46 rows: brochure → category → fleet unit, status, notes. Adds Batch D. | Pink rows = the 3 problem files (Guzzcavator, duplicate Kaiser, broken stub). Notes show the **HV-33, Tornado F4 and Schellvac brochures were sent by Vac2Go** (McKenna Goodman). Lists a tractor spec reference that isn't in the folder. |
| `Vac2Go Brochure to Unit Tagging.xlsx` (v1) | Same tags for A–C, plus *Brochures To Request*, *Summary* (41 fleet units), *Still To Capture* | v2 resolves most "to request" items (website-derived spec references + the three Vac2Go-sent brochures). No hidden sheets/columns/comments in either file. |
| Batches A–D | 45 brochure/spec files (PDF, DOCX, one PNG); 8 image-only | **All read and independently re-checked** (Appendix B). Facts per unit with page citations in `work/unit-facts/*.md` (34 files). |

**Live fleet list** (vac2go.com/vac-truck-rentals/, checked 2026-10-08): **41 units**: Industrial Vacuum 10, Hydro Excavators 9, Combination 5, Liquid Vacuum 5, Liquid Ring 3, Pull-Behind 6 (130 BBL Tankers, Bossvac Hydrovac Trailer, GapVax Combo G7 Trailer Jetter, Kaiser Premier TerraVac, Two Box Roll-Off Trailers, Vermeer LP XDT Vacuum Excavator), Additional 3 (Roll-Off Trucks, Tractors, Water Trucks). Matches the tagging sheet.

**Coverage:** 39 of 41 units have at least one source document. Missing: **GapVax HV-57** (current facts are from Phase 1; the team holds its brochure, G4) and **Tractors** (no document at all, G3). Unit-by-unit map: Appendix A.

---

## 3. Gaps and problems found in the material

Q-numbers are the questions for the client (section 10).

### 3.1 Missing material
| ID | Gap | Impact |
|---|---|---|
| G1 | **Round 1 entries (Industrial Vacuum Q&A) are not loaded anywhere.** Round 2 says they're "already in the knowledge base"; they aren't. Staging's stored prompt is the Phase 1 working draft (it still has the pre-v2.6 rule wording; runtime footers override it). | Industrial Vacuum (10 units, the biggest category) would have the least category depth. **Q1** |
| G2 | **No Water category Q&A.** | "What is a water truck?" failed in Phase 1. **Q2** |
| G3 | **No tractor document.** v2 lists "Vac2Go Tractor - Spec Reference.docx"; not in the folder. | Tractor card from Round 2 only. **Q3** |
| G4 | **No HV-57 brochure** in the batches. | Keep the Phase 1 HV-57 facts; verify when received. **Q4** |
| G5 | Full **synonym ring** and **banned-phrase list** (named in the Phase 1 overview) not received. | Aliases built from fleet + brochure + supplier names for now. **Q5** |
| G6 | Visitors asked for **branch locations / office phones** (Arizona, Boise, Honolulu). No material. | Keep deflecting to contact page unless a list is supplied. **Q6** |
| G7 | **Groups B and C** are missing from every category in Round 2 (only A, D, E exist). | Unknown content; might be intentional (e.g. unit specs or commercial questions). **Q12** |
| G8 | Some Round 2 answers don't answer the question: trailer types (answer is an instruction to the advisor, no list), tanker "DOT 407 vs 412 across the fleet" and "lined or stainless tanks available?" ("Yes, if it's rated for it"). | Bot can only give the general answer. **Q8** |

### 3.2 Fleet list: which list is the truth?
The advisor's current prompt (stored on staging, checked 2026-10-08) lists units from the Phase 1 working draft. It differs from the website, **mostly because it names suppliers rather than units**. The supplier readings below come from the brochures and spec references where cited; the rest (dealer/body-maker names, 2100i) are our reading and are confirmed through Q7:

| Old prompt name | What it is (from the brochures / spec references) | Maps to website unit |
|---|---|---|
| CTOS Tornado F4, CTOS Water Truck, CTOS Roll Off, CTOS 70-BBL Liquid Vacuum | CTOS = Custom Truck One Source (supplier; appears on the water truck brochure) | Tornado F4, Water Trucks, Roll-Off Trucks, Huber Dominator (70 bbl, see C18) |
| Dragon 130-BBL, Keith Huber 130-BBL, ITI SS Code Tanker | The 130 BBL spec reference names Dragon, Huber and ITI as the OEMs | 130 BBL Tankers |
| Benlee Two-Box Roll-Off Trailer | Spec reference: BENLEE build | Two Box Roll-Off Trailers |
| BTE / Bergey's / Galfab / Palmer Roll Off | Hoist/body makers or dealers | Roll-Off Trucks |
| Bergey's Day Cab, Kenworth T880 Day Cab, Peterbilt 579 | Chassis makes/dealer | Tractors |
| Bergey's / ITI Water Truck | Dealer / upfitter | Water Trucks |
| Keith Huber … | Old name of the Huber brand | Huber units |
| Vactor 2100+ / 2100i | 2100i is a newer model; only the 2100 Plus brochure exists | Vactor 2100 Plus |
| Mud Dog / Mud Dog Air | Only the Mud Dog 1200 brochure exists | Super Products Mud Dog 1200 |
| HXX (TruVac/Vactor) | Same unit, rebranded | Truvac HXX |
| GapVax MC1312, Guzzcavator, Dominator SS, Vac Jet Rodding | Not on the website. Dominator SS = stainless option of the Dominator. | Category-level alias with "not on our current list, ask a rep" |
| (missing from old prompt) | Huber AM30 HD, Huber SC 1009 | Add |

**Plan:** the website's 41 units become the canonical fleet; every old name becomes an alias. **Q7** confirms.

### 3.3 Data conflicts the client must settle
All re-checked against the page images in the verification pass (Appendix B). Full detail with page references: `work/unit-facts/*.md` (each has a Conflicts and a Verification section).

| ID | Unit | Conflict (verified) |
|---|---|---|
| C1 | Huber SC 1009/1512 | Airflow 5,300 CFM @ 18" Hg (brochure) vs 5,000 CFM (sell sheet, Hibon TS 56). Boom "28' reach" doesn't say whether it includes the 9' extension. Values are paired to SC1009 / SC1512 only by slash order. |
| C2 | Huber AM36 / AM30 HD | 2017 vs 2024 brochures: water package 20 GPM @ 4,000 psi vs 10/20 GPM @ 2,500 psi; intake 8" vs 6" (the 8" may be the High Dump version); boom standard vs optional. **No document mentions the AM30 HD.** |
| C3 | Huber Baron HX | Boom 24' + 6' extension vs 30 ft (comparison table); "9-stage cyclone" (marketing) vs "single cyclone design" (spec list); water pump "hydraulically driven" vs "direct drive" (marketing). |
| C4 | Huber Scrubber | A VTS36-500 **vapor-scrubber skid** (500 CFM max, "not designed for vacuum", uses Vapor Tech's Bio Scrub X media, 2017 Keith Huber copyright). Filed under Liquid Vacuum; Round 2 calls a scrubber a "separate support asset". Which category? |
| C5 | Super Products High Dump | The Supersucker brochure never mentions it. The only Super Products mention is "High Dump (1200 models)" as a **Camel Max** option. (Vactor 2100 Plus and Huber AM36 also have high-dump versions.) Which unit is Vac2Go's "Super Products High Dump"? |
| C6 | GapVax Combo G7 Trailer Jetter | Brochure is **jetter-only** (no vacuum or debris system on any page). 500/700 gal tank and 800 ft × ¾" reel; Round 2 says trailer jetters have ½" hose, 30–40 GPM, 200–400 gal. |
| C7 | Schellvac SVHX | Brochure covers SVHX8/9/11/12/14; full tables only for SVHX8 and SVHX11. Which model? |
| C8 | Vermeer LP XDT | Brochure covers 573, 573 Heavy, 873, 873 Heavy, 1273, with different capacities and GVWRs. Which model? |
| C9 | Water Trucks | Spec reference says "potable and recycled water"; every brochure photo shows a "NON POTABLE" decal. |
| C10 | GapVax HV-33 | Brochure undated; GapVax no longer lists the model. Vac2Go sent the brochure, so probably still in the fleet: confirm. |
| C11 | Tornado F4 | Sheet describes one 2023 Peterbilt 567 build; no dealer named; a price disclaimer exists only in hidden PDF text. Vac2Go sent it: confirm it's their build. |
| C12 | Guzzler XCR / High-Rail / Dense Phase | XCR and High-Rail are option paragraphs inside the Classic brochure (no specs). Dense Phase sheet is about the Guzzler NX air mover; its only number is "120 feet vertically"; "2003" comes from the filename. |
| C13 | Truvac HXX | 5,200 CFM (Truvac 2024) vs 5,250 CFM (Vactor 2015) for the 28" Hg blower. |
| C14 | Vactor 2100 Plus | 2011 brochure: no debris capacity, water capacity, water pump rating or dimensions. |
| C15 | Spec-reference sheets (130 BBL tanker, two-box trailer, roll-off truck, water truck, MC1510) | Built 7 Oct 2026 from web pages. Ambiguous figures ("80,000 lb" trailer, "80K" hoist, "DOT / ASME" tanker). MC1510 says 60–100 GPM; Round 2 says combos give 80–100 GPM. |
| C16 | Age of sources | About half the literature is 2010–2017 (Knight: "Copyright 1999" vs "Developed in 2008"). Specs may not match Vac2Go's builds. |
| C17 | Hydro excavator water heaters | Round 2: "boilers rated at 700,000 BTU". Brochures: 400k–800k BTU depending on unit: Truvac HXX 400k or 800k (optional), HV-56 400k or 575k (optional), Schellvac SVHX8 420k / SVHX11 690k, Baron HX 700k (cold-weather package), Kaiser CV 700k (optional), Tornado F4 700k, Mud Dog 1200 714k; Paradigm states none. Round 2 also says heated water is optional by model. |
| C18 | "70 BBL" | Round 2 (Tanker): "Vac2Go carries 130 BBL and 70 BBL (4,000 gallons)". 70 bbl ≈ 2,940 gal. The fleet's "70 bbl" unit is the **Huber Dominator**, a liquid vacuum truck, not a tanker: Series IV "3000 gallon (70 bbl or 15 cubic yard)"; Dominator PD "3300 gallons… (75 barrels)" with optional tank sizes "3,000 to 4,000 gallon" (possibly the source of "4,000"). Berringer: "3300 gallon (70 bbl)" (liquid ring) and "3000–3500 gallon (70–83 bbl)" (PD). The website lists only 130 BBL tankers. |
| C19 | Tractors | Round 2: wet kit is "standard". Tagging sheet (from vac2go.com): "hydraulic wet line kit available". |

### 3.4 Problems inside the Round 2 Q&A itself
| ID | Problem | Plan |
|---|---|---|
| K1 | Number/consistency errors: C18 (70 BBL); tanker payload "5,000 to 6,300 gal" vs 130 BBL = 5,460 gal; liquid vac "2,000 to 3,000 gal" vs Dominator 3,000–3,300 and Imperial 5,000 gal; combo "12 or 13 yard body" vs MC1510 10 yd, Camel Max 9/12/16 yd, SC 1009/1512; trailer jetter vs G7 (C6); combo GPM (C15); heater BTU (C17). | Send list (**Q8**). Until answered: unit cards win for unit questions; category answers say "typically" / "varies by unit". |
| K2 | Editorial notes in answers: "Both sources agree…", "The advisor should ask about site access…", "The advisor must clarify whether the customer needs the container, the hauling truck, or both." | Remove the notes; turn the two "advisor should/must" lines into behavior rules. |
| K3 | Commercial wording: tanker "clean for clean policy… substantial commercial cleaning surcharges billed back"; trailer "does not change the structural rental rate"; trailer "significantly lower rental costs". Clashes with the no-terms rule and may trip the safety filter. | **Q9.** Default: reword to "a rep can explain the return-condition policy / rates". |
| K4 | Awkward/unclear lines: "Vac2Go doesn't require or make recommendations on what you do as a customer"; liquid ring "has a Newsom gale system"; "three types of pump system: liquid ring vane pump, …". | Ask for corrected wording (**Q8**). |
| K5 | Round 2 gives **hard numbers** (particle size, temperature limits, solids %, lift) and **answers CDL questions**. Current rules forbid both (rule 3: no hard numbers for particle size/temperature; rule 6: CDL is a policy question). | Rules must change so client-approved KB numbers can be quoted with a hedge (**D3**, **Q10**). |
| K6 | Round 2 is more permissive on hazmat (e.g. liquid ring acceptable for Class 1–3 flammables). | Allowed as general knowledge; the hazmat closing sentence (V1 feedback) still applies; never green-light a specific job. |
| K7 | Answers the brochures can now make specific: liquid ring + dry material "depends on whether it has a baghouse" → the **Knight** has one (40 sock filters); "is heated water available" → per-unit heater options (C17). | Use unit cards to answer specifically. |

---

## 4. Phase 1 learnings (from the staging logs)

Read-only analysis of 290 logged turns / 136 sessions (Sept 3 – Oct 8, 2026, test messages excluded):

| Pattern | Evidence | Fix in Phase 2 |
|---|---|---|
| **No unit specs** (the most common "I don't know") | Paradigm debris capacity; Guzzler XCR specs; Bergey's water truck; Vactor 2100i; "which one is the smallest / biggest?"; "what is a water truck?" | Unit cards + category Q&A; headline-spec table for size questions (D4). |
| **Old-list names** | Bergey's Water Truck, Vactor 2100i | Alias map (3.2). |
| **AI judge false positive** | Log #79 "What is the CFM on the HV-57?": a pure spec answer was replaced by the pricing refusal (stage `judge`). | Tune the judge prompt; regression case. |
| **Word-filter false positive on a negation** | Log #258: visitor left contact details; the bot wrote "I **can't** guarantee a callback time", which the `guarantee` pattern caught (stage `committal`). | Make commitment patterns ignore negations ("can't/cannot/won't guarantee"); regression case. Also detect contact details in a message and point to the follow-up card. |
| **Locations / branch phone** | Arizona office, Boise yard, Honolulu | Depends on Q6. |
| **Fleet counts / "most common unit"** | "how many vacuums do you have?" | Keep "I don't know" (correct). |
| **API credit outage** | 6 turns failed with `insufficient_credits` on Sept 3 (alert email fired) | No change; alerting worked. |
| V1 feedback doc | follow-up timing, hazmat wording, queue, CTAs, rate limit, corrections, alerts | **Done** (v2.6.0, v2.6.1), verified on staging 2026-10-08. |

---

## 5. Decisions

| ID | Decision | Recommendation | Status |
|---|---|---|---|
| D1 | **How knowledge reaches the model.** A: everything in one cached prompt (~40–45k tokens). B: a *core* (rules, category summaries, fleet index with headline specs, synonyms; ~10k) always sent, plus *knowledge packs* (one category's Q&A + its unit cards; ~4–6k each) attached when the conversation is about that category or names a unit, and kept for the rest of the conversation. | **B.** At low traffic the cost is dominated by cache writes when the cache is cold (5-minute lifetime): A ≈ $0.56 per cold start, B ≈ $0.19 (section 6.6). B also keeps answers focused. A stays as the fallback and is measured against B in WP7. | Proposed |
| D2 | **Where knowledge lives.** | Markdown/JSON files in the plugin (`kb/`), versioned in git. Admin gets a read-only Knowledge page. Corrections (Review Queue) stay the no-deploy way to fix answers. The old "System prompt" textarea is retired (it overrides everything). | Proposed |
| D3 | **Hard numbers** | Allow figures that appear in the KB (Round 2 or a unit card), with the existing hedge. Never invent or extrapolate. | Needs client OK (**Q10**) |
| D4 | **Comparisons** (current rule: no brand-vs-brand comparison) | Allow *factual* size comparisons from the cards ("the Camel 1600 has the largest debris body of the combos we list"); no better/worse judgments. | Needs client OK (**Q10**) |
| D5 | **Canonical fleet** | Website's 41 units + aliases (3.2). | Needs client OK (**Q7**) |
| D6 | **Spend controls.** The daily ceiling counts raw tokens (cheap cache reads count like expensive output), and the plugin's price settings are wrong ($3/$15/$0.30 set vs $10/$50/$0.25 real for Claude Fable 5.1), so Stats understates spend ~3×. | Fix prices; daily ceiling in **US dollars**; keep 80%/100% and hourly spike alerts. | Proposed |
| D7 | **Categories for odd units**: Two Box Roll-Off Trailers (Trailer or Roll-Off?), Huber Scrubber (Liquid Vacuum or support equipment?), Imperial High Volume Pump and Portable Restroom Truck (Liquid Vacuum per the tagging sheet). | Two-box trailer → Roll-Off (mention it's a trailer); Scrubber → Liquid Ring support equipment; others as tagged. | Needs client OK (**Q13**) |

---

## 6. Build plan

Order: KB content (WP1–WP3) is the critical path; WP4–WP6 run alongside; WP7 gates the release.

### WP0. Housekeeping (do first)
- [ ] Model prices in plugin defaults and on staging: input $10/M, output $50/M, cache read $0.25/M, cache write 1.25× input (5-min TTL). Stats then shows real spend.
- [ ] Daily ceiling in USD (D6); client picks the figure (**Q11**).
- [ ] Commit `phase-2/PHASE-2-PLAN.md`. Keep `phase-2/phase-2-items/` and `phase-2/work/` out of git (client files + derived files).
- **Done when:** Stats spend matches the Anthropic console for a sample day (±10%).

### WP1. Canonical fleet and aliases
- [ ] `kb/fleet.json`: 41 units → id, display name, advisor category, website group, sources, aliases (3.2 old names, brochure model names such as "Baron HX1512", "Xpose800", "LP873XDT", "BV-500", "HXX", spellings like "hv57", "hydrovac", "air mover").
- [ ] Old names with no unit → category alias + "not on our current list, ask a rep".
- **Done when:** every name in the website list, the old prompt, the tagging sheets and the Phase 1 logs resolves to a unit or category (script check).

### WP2. Category knowledge (T1)
- [ ] `kb/categories/<category>.md` for all 10 categories: what it is, typical jobs, what it can't do, material handling, sizing, and how to tell it apart from neighbors. Sources: Round 2 (8 categories, cleaned per K2), Round 1 (Industrial Vacuum, when received, G1), Water drafted from the spec reference and marked "draft, needs client review" until Q2 is answered.
- [ ] Keep the client's wording. Edits limited to: removing editorial notes (K2), fixes the client confirms (K1, K4), commercial lines per Q9 (K3).
- [ ] Category summaries (~150 words each) for the core.
- **Done when:** every Round 2 question, asked as written, gets an answer consistent with the client's (eval E1).

### WP3. Unit knowledge (T2)
- [ ] `kb/units/<unit>.md`: one customer-safe card per fleet unit, compiled from the verified `work/unit-facts/`: headline specs, models/configurations, typical uses, standard vs optional, and **known unknowns** ("dimensions/weight/GVWR: not in our literature, a rep confirms"). Each figure keeps a hidden source note for audit.
- [ ] Compilation rules: quote figures as published; where sources conflict (3.3), give both / the range with "depends on configuration" until the client settles it; where a brochure covers several models (C7, C8, Camel Max, PowerVac), list per model and say "which model you get depends on the unit"; drop marketing superlatives, competitor comparisons, pricing, warranty, VINs, quote terms (each fact file's "Do not repeat" list) and the hidden-text price disclaimer (C11).
- [ ] Units without documents: HV-57 (Phase 1 facts, unchanged), Tractors (Round 2 + website note only, clearly limited).
- [ ] Headline-spec table across units (core fleet index; size comparisons, D4).
- [ ] Trace check: every number in every card found in a fact file (script), plus spot checks against page images for image-only sources.
- **Done when:** E2 passes and the trace check finds zero untraced numbers.

### WP4. Prompt and knowledge delivery (D1, D2)
- [ ] Split today's prompt into **rules** (guardrails, confidentiality, tone, formatting; reworked for D3/D4, K5, K6 and K2's two behavior rules), **core** (category summaries, fleet index, synonyms) and **packs** (category file + its unit cards).
- [ ] Pack selection, deterministic (no extra model call): unit names/aliases and category keywords in the conversation, plus the category the advisor recommended in its last answer. Packs stick for the session and are appended in a fixed order, so the prompt prefix stays byte-stable and cacheable.
- [ ] Cache layout: rules + core (block 1, cached), packs (block 2, cached), corrections (block 3, cached since v2.6.0). Within the 4-breakpoint limit.
- [ ] Cap packs per conversation (e.g. 3; drop the oldest). Without the relevant pack the advisor says "I don't have that detail" rather than guessing.
- [ ] Retire the `va_system_prompt` textarea: show the assembled prompt read-only; keep a short "admin notes" field appended to the rules.
- [ ] Keep the v2.6.0 runtime footers (length, hazmat sentence, no contact asks, availability CTA).
- **Done when:** cache read covers >90% of the prompt on follow-up turns in a staging test, and E1/E2 with packs (B) are no worse than with everything loaded (A).

### WP5. Safety layer updates
- [ ] Judge prompt: spec figures, CDL/weight/regulatory facts from the KB are not commitments; regression cases (log #79, KB lines with costs/rates K3).
- [ ] Commitment patterns: ignore negated forms ("can't guarantee", log #258); sweep every KB answer and card through the filter, reword or narrow any hit.
- [ ] Contact details typed into the chat → point to the follow-up card / contact page.
- [ ] Update `tests/filter-fixtures.php` and `tests/signals-fixtures.php` (follow-up tagging must still fire with the new content).
- **Done when:** zero filter/judge hits on clean KB answers; all existing red-team cases still blocked.

### WP6. Admin
- [ ] **Knowledge** page: categories and units, each card viewable with sources, last updated, token size; pack sizes.
- [ ] Settings: prices (WP0), dollar ceiling, pack cap.
- [ ] Stats: cost per day at real prices, cache hit rate, average cost per conversation, packs loaded.
- [ ] Review Queue: show which packs were loaded for each answer.
- **Done when:** a reviewer can see what the bot knows about a unit and why it answered what it did.

### WP7. Evaluation
- [ ] **E1 Category:** all 173 Round 2 questions as written + 2 paraphrases of 30 of them; graded against the client's answer.
- [ ] **E2 Unit:** 3–5 spec questions per unit from its card + 1 the card can't answer (must say "I don't know", no invented number).
- [ ] **E3 Recommendation:** ~40 job descriptions across all 10 categories (incl. trailer vs truck, combo vs hydro ex, liquid ring vs liquid vac, tanker vs liquid vac).
- [ ] **E4 Phase 1 regressions:** section 4 log cases + the V1 feedback behaviors.
- [ ] **E5 Safety:** existing red-team suite + pricing/availability/commitment attacks using new unit names.
- [ ] Runner against staging `/chat`, paced under the rate limits; grading by a cheaper model with a strict rubric plus deterministic checks (numbers must exist in the KB, required sentences present, no em dashes).
- [ ] Report: pass rate per set and category, failures with transcripts, cost and latency per turn. Targets: E1 ≥ 90%, E2 ≥ 95%, E3 ≥ 90%, E4 100%, E5 100%.
- Cost per full run: ~500 turns × ~$0.03–0.05 ≈ **$15–25** (measured on the first run; approval before running).
- **Done when:** targets met on staging, report shared.

### WP8. Staging release and handover
- [ ] Deploy (git pull on staging), migrate settings, purge page cache.
- [ ] Run E1–E5; fix; rerun.
- [ ] Review pack for the client: coverage, limits, open questions, how to test, eval report.
- [ ] Optional: the parked 30-agent load test against the bigger prompt.
- **Done when:** the client team can review on staging with the report in hand.

### 6.6 Cost model (estimates; measured in WP7)
Claude Fable 5.1: $10/M input, $50/M output, $0.25/M cache read, $12.50/M cache write (5-min). Measured on staging: ~256 output tokens per answer (thinking included).

| Setup | Prompt | Cold start (cache write) | Warm turn | 4-turn conversation, cold |
|---|---|---|---|---|
| Today (Phase 1) | ~4.5k | ~$0.06 | ~$0.025–0.03 | ~$0.16 |
| A: everything cached | ~40–45k | ~$0.56 | ~$0.035 | ~$0.70 |
| B: core + packs | ~10k + ~5k | ~$0.19 | ~$0.027 | ~$0.36 |

"Cold" = no chat in the last 5 minutes. With steady traffic most turns are warm and the gap shrinks. Sizes are estimates from the source text (Round 2 alone is ~62k characters ≈ 16k tokens).

---

## 7. Sequence and rough effort

| Step | Work | Depends on | Effort |
|---|---|---|---|
| 1 | Send client questions (section 10); WP0 | — | 0.5 day |
| 2 | WP1 fleet + aliases | — | 0.5 day |
| 3 | WP3 unit cards (34 verified fact files → 41 cards) + trace check | WP1 | 2–3 days |
| 4 | WP2 category files (Round 2 now; Round 1 / Water when received) | Q1, Q2 for full coverage | 1 day |
| 5 | WP4 prompt + packs | WP1–WP3 drafts | 1.5–2 days |
| 6 | WP5 safety | WP2–WP4 | 1 day |
| 7 | WP6 admin | WP4 | 1–1.5 days |
| 8 | WP7 eval build + first run, fix loop | WP2–WP5 | 2 days |
| 9 | WP8 staging release + review pack | all | 0.5 day |

Client answers to Q1, Q2, Q7, Q10 unblock full coverage; everything else proceeds with the documented defaults and is adjusted when answers arrive.

---

## 8. Risks

| Risk | Mitigation |
|---|---|
| Wrong numbers quoted (old or conflicting literature) | Verified fact files (Appendix B), trace check (WP3), conflicts as ranges with a hedge, client confirmation (3.3), E2. |
| Pack selection misses context → false "I don't know" | Sticky packs, recommended-category trigger, alias coverage test, A-vs-B comparison (WP4/WP7). |
| Safety layer blocks legitimate KB content | WP5 sweep + regression cases. |
| Cost growth from the bigger prompt | Option B, real prices + dollar ceiling (D6), cost per conversation on Stats. |
| Client material arrives late (Round 1, Water, tractor, HV-57) | Ship marked drafts / limited cards; files in `kb/` slot in later without code changes. |
| Production changed by mistake | Staging-only deploy via git pull on the staging server; no production credentials used. |

---

## 9. Verified facts this plan relies on

- Fleet = 41 units (website + tagging sheet, 2026-10-08).
- Round 2 = 173 questions, 8 categories, groups A/D/E only (counted from the document).
- Brochure facts: 880 spec rows checked twice, the second time independently against page images; zero wrong numbers (Appendix B).
- Phase 1 logs: 290 turns / 136 sessions; false refusals #79 (judge) and #258 (`guarantee` pattern) confirmed from the stored filter stage and pre-filter text.
- Prices: Claude Fable 5.1 per Anthropic's current model table ($10 / $50 / $0.25 cache read; cache write 1.25×).

---

## 10. Questions for the client

1. **Q1** Please send the Round 1 knowledge base entries (Industrial Vacuum + HV-57). The Round 2 doc says they're loaded; they never were.
2. **Q2** There's no Water category Q&A. Can you provide it, or should we draft one for review?
3. **Q3** The tagging sheet lists "Vac2Go Tractor - Spec Reference.docx", but it wasn't in the files. Can you resend it?
4. **Q4** Can we have the HV-57 brochure the team holds, to verify the HV-57 figures?
5. **Q5** Do you have the full synonym ring and banned-phrase list mentioned in Phase 1?
6. **Q6** Should the advisor know branch locations and phone numbers? (Visitors asked.) If yes, please send the list.
7. **Q7** Is the website's list of 41 units the official fleet for the advisor? Older names (CTOS, Bergey's, Dragon, ITI, Benlee, Keith Huber…) would be mapped to those units.
8. **Q8** Please confirm the Round 2 number and wording issues (3.4 K1, K4, G8) and the unit questions in 3.3. The ones that change answers most: which Schellvac and Vermeer models you run; which unit "Super Products High Dump" is; whether the G7 trailer has a vacuum; whether water trucks carry potable water; whether the HV-33 and the Tornado F4 sheet match your units; the SC combo CFM; the AM36 water package; whether the tractor wet kit is standard or optional; and what "70 BBL (4,000 gallons)" refers to.
9. **Q9** May the advisor state the tank return-condition policy and the "trailer left on site doesn't change the rate" line, or should those go to a rep?
10. **Q10** Round 2 includes exact limits (rock size, temperatures, solids %, lift) and CDL requirements. Phase 1 rules blocked these. May the advisor quote them with a "depends on configuration" hedge? May it make factual size comparisons between units?
11. **Q11** What daily spend ceiling (in dollars) do you want on staging and later production?
12. **Q12** The Round 2 doc has Groups A, D and E for each category. Are there Groups B and C we should have?
13. **Q13** Where should these sit: Two Box Roll-Off Trailers (Trailer or Roll-Off), Huber Scrubber (it's a vapor-scrubber skid, currently under Liquid Vacuum)?

---

## 11. Progress log

| Date | What happened |
|---|---|
| 2026-10-07 | V1 feedback implemented (v2.6.0), deployed to staging. |
| 2026-10-08 | v2.6.1 (mobile cookie banner) deployed; all V1 items verified on staging, incl. a real alert email. 30-agent load test parked. |
| 2026-10-08 | Phase 2 material read in full; 34 unit fact files extracted; plan v1 written. Found the plugin's prices ~3× too low for Fable 5.1. |
| 2026-10-08 | Verification pass: every fact file re-checked independently against page images (880 rows, 0 wrong numbers, 44 wording or claim fixes, 77 specs added); Round 2 doc re-examined (counts, groups, hidden markup); spreadsheets re-checked; old prompt names traced to suppliers; Phase 1 false refusals root-caused. Plan v2. |
| 2026-10-08 | Build started on branch `phase-2` (ledger: `phase-2/LEDGER.md`). WP0 real prices + dollar ceiling; WP1 41-unit fleet + aliases; WP2 category knowledge (173 Q&A, 11 logged edits); WP3 41 unit cards with number trace checks. |
| 2026-10-08 | Fast verification round: plan internally consistent (all G/C/K/D/Q IDs defined, 13 questions, Appendix A covers all 41 units, all referenced fact files exist); 41 quoted claims re-found verbatim in the source documents (19 Round 2 quotes, 22 brochure/spec-reference quotes), 0 misses. |

## 12. Status board

| Work package | Status |
|---|---|
| Material review + verification | ✅ Done |
| Client questions sent | ⬜ Not started |
| WP0 Housekeeping | ✅ Done (01e4ac78) |
| WP1 Fleet + aliases | ✅ Done (b5c83f08) |
| WP2 Category knowledge | ✅ Done (fad28203); Industrial Vacuum + Water are drafts until the client sends entries |
| WP3 Unit cards | ✅ Done (ee2d0765), 41 cards |
| WP4 Prompt + packs | 🔄 In progress |
| WP5 Safety updates | ⬜ Not started |
| WP6 Admin | ⬜ Not started |
| WP7 Evaluation | ⬜ Not started |
| WP8 Staging release | ⬜ Not started |

---

## Appendix A. Unit coverage (41 website units)

| Website group | Unit | Fact file | Main source(s) |
|---|---|---|---|
| Industrial Vacuum | GapVax HV-57 | — (Phase 1 prompt facts) | Brochure held by Vac2Go team (G4) |
| | Guzzler Classic / High-Rail / XCR | guzzler-classic.md | Classic brochure (XCR, High-Rail as options) |
| | Guzzler Dense Phase | guzzler-dense-phase.md | Dense Phase sheet (Guzzler NX) |
| | Huber AM36 / AM30 HD | huber-am36.md | AM36_HD.pdf (2024), Air Mover brochure (2017); AM30 HD not mentioned |
| | PresVac PowerVac | presvac-powervac.md | PowerVac.pdf (3800/5300/6400) |
| | Super Products Supersucker / High Dump | supersucker.md | Supersucker brochure (no High Dump, C5) |
| Hydro Excavators | GapVax HV-33 | gapvax-hv33.md | HV33 brochure (Vac2Go-supplied) |
| | GapVax HV-56 | gapvax-hv56.md | GapVax Hydrovac Literature 2026 |
| | Huber Baron HX | huber-baron-hx.md | 3 Baron HX1512 documents (2025–2026) |
| | Kaiser Premier CV Series | kaiser-premier-cv.md | Kaiser CV brochure (2019) |
| | Schellvac SVHX | schellvac-svhx.md | Schellvac booklet (2018, 5 models) |
| | Super Products Mud Dog 1200 | mud-dog-1200.md | Mud Dog 1200 brochure |
| | Tornado F4 | tornado-f4.md | F4 sheet (Vac2Go-supplied) |
| | Truvac HXX | truvac-hxx.md | Truvac HXX (2024) + Vactor HXX (2015) |
| | Vactor Paradigm | vactor-paradigm.md | Paradigm brochure |
| Combination | GapVax MC1510 | gapvax-mc1510.md | Spec reference (from gapvax.com) |
| | Huber SC 1009 / SC 1512 | huber-sc-1009-1512.md | Brochure, sell sheet, Lancer scan |
| | Super Products Camel Max Series | camel-max.md | Camel Max brochure (900/1200/1600) |
| | Vactor 2100 Plus | vactor-2100-plus.md | 2100 Plus brochure (2011) |
| Liquid Vacuum | Huber Berringer PD | huber-berringer.md | Berringer brochure |
| | Huber Dominator | huber-dominator.md | Series IV + PD brochures |
| | Huber Scrubber | huber-scrubber.md | VTS36-500 datasheet (C4) |
| | Imperial Industries High Volume Pump | imperial-industries-high-volume-pump.md | Imperial 5000 gal spec docx |
| | Portable Restroom Truck | portable-restroom-truck.md | ITI build quote (VINs/terms excluded) |
| Liquid Ring | Huber Berringer Liquid Ring | huber-berringer.md | Berringer brochure |
| | Huber King Vac | huber-king-vac.md | KingVac brochure |
| | Huber Knight | huber-knight.md | Knight brochure |
| Pull-Behind | 130 BBL Tankers | 130-bbl-tankers.md | Spec reference (from vac2go.com) |
| | Bossvac Hydrovac Trailer | bossvac-bv500-hydrovac-trailer.md | BV-500 sheet (PNG) |
| | GapVax Combo G7 Trailer Jetter | gapvax-g7-trailer-jetter.md | G7 brochure (jetter-only, C6) |
| | Kaiser Premier TerraVac | kaiser-premier-terravac.md | TerraVac Xpose800 brochure |
| | Two Box Roll-Off Trailers | two-box-roll-off-trailers.md | Spec reference (from vac2go.com) |
| | Vermeer LP XDT Vacuum Excavator | vermeer-lp-xdt.md | Vermeer LP XDT brochure (5 models, C8) |
| Additional | Roll-Off Trucks | roll-off-trucks.md | Spec reference (from vac2go.com) |
| | Tractors | — | None (G3) |
| | Water Trucks | water-trucks.md | Load King brochure + spec reference |
| Not on fleet | Guzzcavator | guzzcavator.md | Brochure (kept for aliasing only) |

## Appendix B. Verification record

| Batch | Files | Spec rows checked | Wrong numbers found | Other corrections | Specs added | Record |
|---|---|---|---|---|---|---|
| Huber | 8 | 264 | 0 | 10 wording/page fixes | 22 | `_verification-huber.md` |
| Guzzler / Vactor / Super Products / Kaiser / Vermeer / PresVac | 13 | 345 | 0 | 26 claim/wording fixes (e.g. drawing dimensions aren't wheelbases; PowerVac photos are tri-axle) | 49 | `_verification-guzzler-vactor-sp.md` |
| GapVax / trailers / spec references | 13 | 271 | 0 | 8 claim/wording fixes (e.g. Tornado F4 "dealer sheet" claim corrected; SVHX12/14 are tri-axle) | 6 | `_verification-gapvax-trailers-specrefs.md` |

Method: a second, independent reader rendered every source page (image-only pages at 130–300 dpi), checked every table row for value, unit, model and page, re-tested every listed conflict, and added specs the first pass missed. One first-pass claim was wrong and is corrected above (High Dump, C5).
