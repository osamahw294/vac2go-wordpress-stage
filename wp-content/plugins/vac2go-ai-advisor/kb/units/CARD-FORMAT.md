# Unit card format

One card per fleet unit, `kb/units/<unit-id>.md`, where `<unit-id>` is the key in `kb/fleet.json`. Cards are what the advisor reads to answer questions about a specific unit, so every line is customer-facing.

Cards are compiled from the verified fact files (`phase-2/work/unit-facts/`, not in git), which quote each manufacturer document with page references.

## Layout

```markdown
# <Unit name exactly as in fleet.json>

<One or two plain sentences: what it is and what it's for.>

## Models covered
- <Only when the literature covers more than one model or configuration. Otherwise omit this section.>

## Key specs
- <Spec>: <value exactly as published, with units> {src: <document> p.<page>}

## Typical uses
- <Applications the literature names.>

## Standard and optional
- Standard: <...>
- Optional: <...>

## Not in our literature
- <Things customers commonly ask that no document states: dimensions, weights/GVWR, chassis, fuel capacity, etc.> A Vac2Go rep can confirm these.

## Notes for the advisor
- <How to answer where the literature conflicts, covers several models, or is old.>
```

## Rules

1. **Quote, never compute.** Figures appear exactly as the source states them, with the source's units and model/configuration qualifier. No unit conversions, no rounding, no arithmetic (no gallons → barrels unless the document itself says so).
2. **Every figure carries a source tag** `{src: <document> p.<page>}` at the end of its line. Tags are stripped before the card reaches the model; they exist so every number can be audited.
3. **Conflicts are shown, not resolved.** Where documents disagree, give both figures and say the exact figure depends on the unit's configuration. Never pick one silently.
4. **Several models → list per model** and say which model you get depends on the unit.
5. **Leave out:** marketing superlatives and "patented" claims, competitor comparisons, prices, warranties, VINs, dealer quote terms, anything that reads like a commitment, and anything found only in a PDF's hidden text layer.
6. **Unknowns are listed**, so the advisor says "not in our literature" instead of guessing.
7. **Length:** about 350 words; up to about 500 for cards covering several models.
8. **No em dashes.** The advisor never uses them, and cards should not teach it to.
