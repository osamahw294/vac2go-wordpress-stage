#!/usr/bin/env python3
"""
Build the Round 1 knowledge (Industrial Vacuum) from the client's
"AI Equipment Advisor rev1.docx" (received 2026-10-09, answer to Q1).

    python3 -I phase-2/tools/build_round1.py

Input:  phase-2/work/extracted-text/kb-round1.txt (text of the client's .docx; not in git)
Output: wp-content/plugins/vac2go-ai-advisor/kb/categories/industrial-vacuum.md
            Groups A, D, E, F: the category's own knowledge, loaded as its pack.
        wp-content/plugins/vac2go-ai-advisor/kb/job-matching.md
            Groups B (job matching) and C (industries). They route jobs across the
            whole fleet (client answer to Q12), so they are sent on every turn.
        wp-content/plugins/vac2go-ai-advisor/kb/EDITS-ROUND1.md

As with Round 2, the client's questions and answers are carried over word for word,
apart from the EDITS below. Every edit must match the source exactly once, or the
build stops.
"""
import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
SRC = ROOT / "phase-2/work/extracted-text/kb-round1.txt"
KB = ROOT / "wp-content/plugins/vac2go-ai-advisor/kb"

CATEGORY_GROUPS = ("A", "D", "E", "F")
FLEET_GROUPS = ("B", "C")

# (exact text in an answer line, replacement or None to drop the line, reason)
EDITS = [
    ("Catalyst- COULD BE FLAMMABLE OR CONBUSTABLE", "Catalyst (could be flammable or combustible)",
     "Spelling and capitals; meaning unchanged."),
    ("Coal fines- COULD BE CONBUSTABLE", "Coal fines (could be combustible)",
     "Spelling and capitals; meaning unchanged."),
    ("If heavy debris removal is the primary task, an Liquid Vacuum Truck may also be appropriate.",
     "If heavy debris removal is the primary task, a liquid vacuum truck may also be appropriate.",
     "Grammar."),
    ("removing soil, WET, and debris", "removing soil, wet material and debris", "Capitals and wording."),
    ("Combo’s and Hydro’s excavation would be the better application for water or slurry materials",
     "For water or slurry, a combination unit or hydro excavator is the better choice.",
     "Wording; meaning unchanged."),
    ("Materials prohibited by local, state, or federal regulations",
     "- Materials prohibited by local, state, or federal regulations",
     "Restored as the last item of the list it belongs to (it lost its bullet in the document)."),
    ("Recommendation", None,
     "A note to Vac2Go, not an answer. Its advice (no hard limits unless tied to a unit) is kept as an advisor note and matches the rules (client answer to Q10)."),
    ("I would avoid publishing hard numbers", None, "Same note to Vac2Go; see above."),
    (" The HV-57 is one of the most common industrial vacuum truck configurations in North America.", "",
     "A market claim nothing in the literature supports."),
    ("Vac2Go should identify the primary chassis used in its fleet, for example:",
     "A Vac2Go rep can confirm the chassis on a specific unit.",
     "An instruction to Vac2Go, not an answer."),
    ("Peterbilt 567", None,
     "An example chassis from the instruction above; not confirmed for Vac2Go's fleet (the HV-57 card lists chassis as not in the literature)."),
    ("These values should come from the actual Vac2Go fleet specifications rather than the base manufacturer brochure.",
     "These come from Vac2Go's fleet records for the specific unit, not the manufacturer's brochure. A Vac2Go rep can confirm them.",
     "An instruction to Vac2Go, rewritten as the answer it implies."),
]

EDITS += [
    ("industrial facilities—not just sewer infrastructure", "industrial facilities, not just sewer infrastructure",
     "Punctuation (no em dashes in customer text)."),
    ("Possibly—but only", "Possibly, but only", "Punctuation (no em dashes in customer text)."),
]

# (group letter, start of the question, note) appended after that answer.
NOTES = [
    ("D", "What materials should never be vacuumed",
     "Do not give hard limits for particle size, temperature or lifting weight unless a unit card gives that unit's figure; these depend on the blower, hose size, lift height, vacuum level and material. Say it depends on the configuration."),
    ("F", "Is the HV-57 a particularly large",
     "For the HV-57's figures use its unit card, which gives them as ranges (airflow 5,200 to 5,250 CFM, vacuum 27 to 28\" Hg, debris capacity about 15 to 17 cu yd)."),
]

SUMMARY = (
    "An industrial vacuum truck (air mover, vacuum loader) is a heavy-duty vacuum system that removes, recovers "
    "and transports wet, dry and slurry material through large-diameter hoses into an onboard debris tank. Typical "
    "jobs: plant and refinery shutdowns, tank, silo and bin cleanouts, pit, sump and trench cleaning, spill cleanup, "
    "dry bulk loading, catalyst removal, ash and dust removal, ready-mix silt traps, micro trenching, roof ballast and "
    "blast media. It handles powders, fly ash, cement dust, sand, gravel, grain, pellets, sludge and slurry. Choose it "
    "when the goal is to remove or transfer material rather than excavate: a hydro excavator is better for daylighting "
    "and digging around utilities, a combination unit for jetting sewer and storm lines, and a liquid vacuum or liquid "
    "ring unit for large volumes of liquid, wet waste or flammable material. Hazardous, combustible, hot, acidic or "
    "corrosive material needs evaluation by Vac2Go before rental; radioactive material and asbestos are not suitable."
)

# Kept from the earlier draft: what Vac2Go's Round 2 answers say about industrial vacuums.
ROUND2_COMPARISONS = """## How it compares (from Vac2Go's Round 2 answers)

- **vs. hydro excavator:** a hydro excavator actively loosens and excavates the ground; a standard dry industrial vacuum can only recover material that is already loose, uncompacted, pre-broken, or can be picked up directly.
- **vs. combination unit:** combination units are strictly wet-work machines; for dry material recovery a dedicated industrial vacuum loader / air mover must be used. An industrial vacuum can become overwhelmed or leak fine mist with massive volumes of liquid-dominant stormwater runoff. For a catch basin packed with compressed, bone-dry sand or massive gravel beds, an industrial vacuum (air mover) or a heavy-duty liquid vacuum truck with a positive-displacement blower is more efficient than a combo.
- **vs. liquid vacuum:** liquid vacuums handle corrosive, acidic, hazardous and 100% liquid materials that would leak out of or ruin the dry filtration baghouses of an industrial air mover.
- **vs. liquid ring:** industrial vacuums use dry filtration baghouses that get blinded and ruined by heavy moisture, and can generate static sparks; liquid rings handle high-moisture, high-vapor streams and volatile gases safely. Choose an industrial vacuum for dry powders or damp debris.
- **with roll-off boxes:** an industrial vacuum truck must leave the site to dump once its tank fills; staging roll-off boxes (including vacuum-rated boxes it can load directly) keeps it working on site."""


def bullet(line):
    return re.sub(r"^\t?•\t?", "- ", line.strip())


def parse(text):
    """{letter: [title, [[heading, answer_lines, is_industry]]]}"""
    groups, g = {}, None
    lines = [l.rstrip() for l in text.splitlines()]
    nonblank = [i for i, l in enumerate(lines) if l.strip()]

    def next_line(i):
        later = [j for j in nonblank if j > i]
        return lines[later[0]].strip() if later else ""

    def starts_entry(i):
        s = lines[i].strip()
        if not s or s.startswith("•") or lines[i].startswith("\t"):
            return False
        if s.endswith("?"):
            return True
        return g is not None and g[0] == "C" and next_line(i) == "Common trucks used:"

    i = 0
    while i < len(lines):
        s = lines[i].strip()
        m = re.match(r"^Group ([A-F]) — (.+)$", s)
        if m:
            g = [m.group(1), m.group(2), []]
            groups[g[0]] = g
            i += 1
            continue
        if g is not None and starts_entry(i):
            is_industry = not s.endswith("?")
            ans, j = [], i + 1
            while j < len(lines):
                if re.match(r"^Group [A-F] — ", lines[j].strip()) or starts_entry(j):
                    break
                if lines[j].strip():
                    ans.append(bullet(lines[j]))
                j += 1
            g[2].append([s, ans, is_industry])
            i = j
            continue
        i += 1
    return groups


def render(groups, letters, intro):
    out = [intro, ""]
    for letter in letters:
        _, title, entries = groups[letter]
        out += [f"### Group {letter}: {title.replace(' — ', ': ')}", ""]
        for head, ans, is_industry in entries:
            head = head.replace(" — Typical applications", "").replace(" — ", ": ")
            out += [f"**Industry: {head}**" if is_industry else f"**Q: {head}**", ""] + ans + [""]
    return out


def main():
    if not SRC.exists():
        sys.exit(f"missing source: {SRC}")
    groups = parse(SRC.read_text(encoding="utf-8"))
    assert sorted(groups) == list("ABCDEF"), sorted(groups)
    counts = {k: sum(1 for e in v[2] if not e[2]) for k, v in groups.items()}
    industries = sum(1 for e in groups["C"][2] if e[2])
    assert counts == {"A": 11, "B": 19, "C": 1, "D": 10, "E": 5, "F": 4}, counts
    assert industries == 12, industries

    log = []
    for old, new, why in EDITS:
        hits = [(e, k) for g in groups.values() for e in g[2] for k, a in enumerate(e[1]) if a is not None and old in a]
        assert len(hits) == 1, f"edit must match exactly once: {old[:60]!r} (matched {len(hits)})"
        e, k = hits[0]
        if new is None:
            log.append((e[0], e[1][k], "(removed)", why))
            e[1][k] = None
        else:
            log.append((e[0], old, new or "(removed)", why))
            e[1][k] = e[1][k].replace(old, new)
    for g in groups.values():
        for e in g[2]:
            e[1] = [a for a in e[1] if a is not None]

    for letter, qstart, note in NOTES:
        hits = [e for e in groups[letter][2] if e[0].startswith(qstart)]
        assert len(hits) == 1, f"note target must match exactly once: {qstart!r}"
        hits[0][1].append(f"Advisor note: {note}")

    iv = ["# Industrial Vacuum", "", "## Summary", "", SUMMARY, "", "## Questions and answers", ""]
    iv += render(groups, CATEGORY_GROUPS,
                 "From Vac2Go's Round 1 Knowledge Base Entries. Answer from these, keeping their meaning; where an answer gives a general figure and a unit card gives that unit's own figure, use the unit's figure for that unit. Groups B (job matching) and C (industries) are in the job-matching knowledge, which is always available.")
    iv += [ROUND2_COMPARISONS]
    (KB / "categories/industrial-vacuum.md").write_text("\n".join(iv).rstrip() + "\n", encoding="utf-8")

    jm = ["== WHICH TRUCK FOR WHICH JOB (Vac2Go's own answers, covering the whole fleet) =="]
    jm += render(groups, FLEET_GROUPS,
                 "From Vac2Go's Round 1 Knowledge Base Entries, Groups B and C. Use these to match a job or an industry to a category; the category's own knowledge and unit cards give the detail.")
    (KB / "job-matching.md").write_text("\n".join(jm).rstrip() + "\n", encoding="utf-8")

    for path in (KB / "categories/industrial-vacuum.md", KB / "job-matching.md"):
        assert "—" not in path.read_text(encoding="utf-8"), f"em dash left in {path.name}"

    e = ["# Edits to the Round 1 Knowledge Base text", "",
         "Every change made to the client's wording in `AI Equipment Advisor rev1.docx`, generated by `phase-2/tools/build_round1.py`. Everything not listed here is word for word from the client's document, except that \" — \" in group titles and industry headings is written as \": \".", ""]
    for head, old, new, why in log:
        e += [f"## {head}", "", f"- **Was:** {old}", f"- **Now:** {new}", f"- **Why:** {why}", ""]
    e += ["## Advisor notes added", ""]
    for letter, qstart, note in NOTES:
        e += [f"- **Group {letter}**, after \"{qstart}\": {note}"]
    (KB / "EDITS-ROUND1.md").write_text("\n".join(e).rstrip() + "\n", encoding="utf-8")
    print(f"built Round 1: {sum(counts.values())} questions, {industries} industries, {len(log)} edits, {len(NOTES)} notes")


if __name__ == "__main__":
    main()
