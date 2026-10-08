#!/usr/bin/env python3
"""
Build kb/categories/*.md from the client's Round 2 Knowledge Base Entries.

    python3 -I phase-2/tools/build_categories.py

Input:  phase-2/work/extracted-text/kb-round2.txt (text of the client's .docx; not in git)
Output: wp-content/plugins/vac2go-ai-advisor/kb/categories/<category>.md
        wp-content/plugins/vac2go-ai-advisor/kb/categories/EDITS.md

The client's questions and answers are carried over word for word. The only changes
are the EDITS below (each with a reason and the plan item it comes from) and the
advisor NOTES (pointers to unit literature where it disagrees with a category answer).
Every edit must match the source exactly once, or the build stops: the source text
cannot drift away from the log without someone noticing.
"""
import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
SRC = ROOT / "phase-2/work/extracted-text/kb-round2.txt"
OUT = ROOT / "wp-content/plugins/vac2go-ai-advisor/kb/categories"

SECTION_IDS = {
    "Hydro Excavator": "hydro-excavator",
    "Combination": "combination",
    "Liquid Vacuum": "liquid-vacuum",
    "Liquid Ring": "liquid-ring",
    "Trailer": "trailer",
    "Tanker": "tanker",
    "Roll-Off": "roll-off",
    "Tractor": "tractor",
}

# (category, exact text in the client's answer, replacement, reason, plan ref)
EDITS = [
    ("hydro-excavator",
     "Both sources agree that the customer is responsible",
     "The customer is responsible",
     "Editorial note left in the answer.", "K2"),
    ("hydro-excavator",
     "Heated water systems are boilers rated at 700,000 BTU.",
     "Heated water comes from an onboard boiler or water heater, and its rating varies by unit.",
     "Unit literature lists 400,000 to 800,000 BTU depending on the unit, not a single 700,000 BTU figure. Pending client confirmation.", "C17, Q8"),
    ("liquid-vacuum",
     " The liquid ring unit is also specified as intrinsically safe and has a Newsom gale system.",
     " The liquid ring unit is also specified as intrinsically safe.",
     "\"Newsom gale system\" is unclear; removed until the client clarifies it rather than repeat it to customers.", "K4, Q8"),
    ("liquid-vacuum",
     "three types of pump system: liquid ring vane pump,",
     "three types of pump system: liquid ring pump,",
     "\"Liquid ring vane pump\" mixes two pump types; a liquid ring unit uses a liquid ring pump. Pending client confirmation.", "K4, Q8"),
    ("trailer",
     "The advisor should ask about site access and volume constraints first, then direct the user to the correct trailer type.",
     "Vac2Go's trailer units are hydro excavation trailers (Bossvac Hydrovac Trailer, Kaiser Premier TerraVac), a jetter trailer (GapVax Combo G7 Trailer Jetter), a vacuum excavator trailer (Vermeer LP XDT) and the Two Box Roll-Off Trailers. Ask about site access and volume first, then point the customer to the right type.",
     "The answer was an instruction to the advisor with no list. Replaced with the trailer units on vac2go.com, keeping the instruction.", "K2, G8"),
    ("trailer",
     "Limited physical space on the job site and significantly lower rental costs compared to full-size trucks for small, localized projects.",
     "Limited physical space on the job site, and a smaller setup than a full-size truck for small, localized projects.",
     "Rental-cost comparison removed until the client allows rate statements; a rep handles rates.", "K3, Q9"),
    ("trailer",
     "It does not change the structural rental rate, but it eliminates daily travel wear and tear.",
     "It also eliminates daily travel wear and tear. Rental rates are a question for a Vac2Go rep.",
     "Rate statement removed until the client allows it.", "K3, Q9"),
    ("tanker",
     "Vac2Go carries 130 BBL and 70 BBL (4,000 gallons).",
     "Vac2Go's tankers are 130 BBL units.",
     "70 bbl is about 2,940 gallons, and vac2go.com lists only 130 BBL tankers; the fleet's 70 bbl unit is the Huber Dominator, a liquid vacuum truck. Pending client confirmation.", "C18, K1, Q8"),
    ("tanker",
     "Vac2Go doesn't require or make recommendations on what you do as a customer.",
     "Approval depends on each tank's rating and certification. Confirm compatibility for a specific product with a Vac2Go rep before the rental.",
     "The original line did not answer the question. Replacement follows the other tanker answers (\"if it's rated for it\"). Pending client confirmation.", "K4, Q8"),
    ("tanker",
     "Vac2Go enforces a strict \"clean for clean\" policy; any prior-product residue or heel left in the tank will result in substantial commercial cleaning surcharges billed back to the customer.",
     "A Vac2Go rep can explain the return-condition policy and any cleaning charges.",
     "Charge-back terms removed until the client allows policy statements; a rep handles terms.", "K3, Q9"),
    ("roll-off",
     "The advisor must clarify whether the customer needs the container, the hauling truck, or both.",
     "Ask whether the customer needs the container, the hauling truck, or both.",
     "Editorial phrasing; kept as an instruction.", "K2"),
]

# (category, start of the question, note) appended after that answer.
NOTES = [
    ("combination", "How is a combination truck different from a jetter-only trailer?",
     "Unit literature differs from these general figures: the GapVax MC1510 sheet lists 60 to 100 GPM, and the GapVax G7 Trailer Jetter brochure shows a 3/4\" reel and 500 or 700 gallon tanks. Use the unit's own figures when a specific unit is discussed."),
    ("combination", "What debris size can a combination unit vacuum",
     "Debris bodies vary by unit: the GapVax MC1510 is 10 cu yd and the Camel Max comes in 9, 12 and 16 yard versions. Use the unit's own figures when a specific unit is discussed."),
    ("trailer", "What is the water capacity on a jetter or hydro trailer",
     "Unit literature differs: the GapVax G7 Trailer Jetter brochure shows 500 or 700 gallon tanks. Use the unit's own figures when a specific unit is discussed."),
    ("tanker", "How is a tanker different from a liquid vacuum truck?",
     "These are general figures. For a specific unit use its own literature: Vac2Go's 130 BBL tankers are 5,460 gallons, the Huber Dominator is 3,000 to 3,300 gallons, and the Imperial unit is 5,000 gallons."),
]

SUMMARIES = {
    "hydro-excavator": "A hydro excavator digs with high-pressure water (or, on units with an onboard air compressor, an air knife) and vacuums the loosened soil into a debris body. It is used for potholing, daylighting and exposing underground utilities, trenching, and digging near live gas, electrical, fiber and water lines, where it is the standard non-destructive method. It breaks down compacted ground, clay, roots, gravel, frozen and rocky soil that an industrial vacuum cannot. Spoil is normally a wet slurry, and the customer arranges the water supply and disposal. It is the wrong choice for bulk dry material (fly ash pits, grain silos), plant cleanouts or tank cleaning, which suit an industrial vacuum. Hydro excavation trailers suit small jobs and tight access.",
    "combination": "A combination unit (combination sewer cleaner, sewer jetter, sewer combo) pairs a high-flow water jetter on a large hose reel with a vacuum system and debris body. It cleans sewer and storm lines, catch basins, culverts and lift stations: the jetter breaks up and flushes material while the vacuum recovers it, including grease (FOG), and roots with cutter nozzles. It is strictly a wet-work machine: no dry material and no hazardous products. Small potholing jobs are possible, but formal daylighting, deep or frozen-ground excavation and air excavation need a hydro excavator. Choose a combo when lines need jetting and the debris must be removed, a jetter-only unit when downstream flow carries the debris away, and a vacuum-only unit for static pits and basins.",
    "liquid-vacuum": "A liquid vacuum truck recovers and hauls liquids, slurries and pumpable waste, using a vane pump (LVT; LVTS with a stainless tank) or a positive displacement blower (LVPD) for deeper lift and thicker slurry. Typical jobs: septic and grease trap pump-outs, oil-water separators, industrial wastewater and sludge, catch basins and sumps, flood water, drilling mud and brine, and hydro excavation slurry. It handles no dry material. Corrosive liquids need a stainless or lined tank compatible with the product, hazardous liquids need a DOT 407 or DOT 412 code tank, and volatile flammables belong on a liquid ring unit. A tanker carries more over the highway; a liquid vacuum truck is the agile, self-contained choice for pulling material on site.",
    "liquid-ring": "A liquid ring vacuum truck uses a liquid ring pump: a water seal compresses gas with no metal-to-metal contact, so the pump creates no ignition source. That makes it the choice for volatile, flammable or toxic liquids and slurries: refinery turnarounds, crude tank cleaning, chemical vessel evacuations, airport fuel systems, and benzene or H2S environments. It is the only acceptable choice on safety grounds where the atmosphere exceeds 10% LEL or site rules ban vane or PD pumps. Configurations include an air mover type (heavy sludge, tank bottoms) and a liquid vac type (chemical transfers, volatile fuels). Dry material needs water or a unit with a baghouse. It is not for large-scale dry powder transport or long-distance hauling, and is often paired with scrubber trailers or combination/jetter units.",
    "trailer": "Trailer is a form factor, not a function. Vac2Go's trailer units are hydro excavation trailers, a jetter trailer, a vacuum excavator trailer and two-box roll-off trailers. Trailers suit tight access (alleys, residential areas, parking garages), small-volume and maintenance jobs, and projects where the unit stays parked on site for weeks. Compared with truck-mounted units they use smaller hoses (2 to 3 inch maximum particle size), carry less water and debris, and recovered material leaves by towing the trailer or by pumping into a staging tank. The customer supplies a suitable tow vehicle. Most rental trailers are not built for hazardous, regulated or flammable material. Move up to a truck when volume, hose distance or continuous production grows.",
    "tanker": "A tanker is a large semi-trailer barrel for high-volume liquid transport over public highways, either transport-only (loaded by a vacuum truck or plant pump) or self-contained with its own pump. Vac2Go's tankers are 130 BBL units. They carry pumpable liquid and light sludge, not heavy solids. Typical uses: large tank evacuations, pond remediation, drilling fluids and long-distance hazardous waste transport. On heavy products, legal road weight usually limits usable capacity before volume does. Tankers can be rented alone or with a tractor; the customer's driver needs the right CDL endorsements and the customer handles placarding and manifests. For on-site extraction of high-solids material a liquid vacuum truck fits better, often with tankers cycling to disposal.",
    "roll-off": "The roll-off category covers roll-off hoist trucks that haul boxes, the roll-off boxes themselves, and Vac2Go's two-box roll-off trailers. Box types include open-top boxes, sealed (gasketed) sludge boxes, vacuum-rated boxes a vacuum truck can load directly, and dewatering boxes. Roll-off staging lets a vacuum truck keep working on site while full boxes are hauled away separately, which helps when volume exceeds 20 cubic yards and the disposal site is far away. Free liquids need sealed boxes or dewatering first. Legal road weight, not box volume, usually limits a load. The customer arranges hauling and disposal of full boxes and all waste profiling.",
    "tractor": "Tractors are heavy-duty, over-the-road Class 8 semi-tractors for pulling tankers and trailers, rented on their own or bundled. They are customer-operated: the driver needs a Class A CDL with a tanker endorsement. A wet kit runs trailer-mounted pumps or blowers. Rent one when your own vehicles lack the Class 8 weight rating, a hydraulic wet kit or the emission permits a site requires, or to cover a breakdown or a seasonal peak. A tractor plus tanker suits large volumes of low-viscosity liquid over distance; a self-contained vacuum truck suits on-site extraction.",
}


def parse(text):
    """{category_id: [(group_title, group_note, [(question, answer_lines)])]}"""
    cats, cat, group = {}, None, None
    lines = [l.rstrip() for l in text.splitlines()]
    i = 0
    while i < len(lines):
        line = lines[i].strip()
        m = re.match(r"^\d\.\s+(.+)$", line)
        if m and m.group(1) in SECTION_IDS:
            cat = SECTION_IDS[m.group(1)]
            cats[cat] = []
            group = None
        elif cat and line.startswith("Group "):
            group = [line, "", []]
            cats[cat].append(group)
        elif cat and group is not None and re.search(r"\?\**$", line):
            q = line.strip("*").strip()
            ans = []
            j = i + 1
            while j < len(lines):
                nxt = lines[j].strip()
                if not nxt:
                    j += 1
                    continue
                if re.search(r"\?\**$", nxt) or nxt.startswith("Group ") or (re.match(r"^\d\.\s+(.+)$", nxt) and re.match(r"^\d\.\s+(.+)$", nxt).group(1) in SECTION_IDS):
                    break
                ans.append(re.sub(r"^\t?•\t?", "- ", lines[j].strip().replace("\t•\t", "- ")))
                j += 1
            group[2].append([q, ans])
            i = j
            continue
        elif cat and group is not None and line and not group[2]:
            group[1] = (group[1] + " " + line).strip()
        i += 1
    return cats


def main():
    if not SRC.exists():
        sys.exit(f"missing source: {SRC}")
    cats = parse(SRC.read_text(encoding="utf-8"))
    assert set(cats) == set(SECTION_IDS.values()), sorted(cats)
    total = sum(len(g[2]) for c in cats.values() for g in c)
    assert total == 173, f"expected 173 questions, parsed {total}"

    log = []
    for cat, old, new, why, ref in EDITS:
        hits = [(g, qa) for g in cats[cat] for qa in g[2] if any(old in a for a in qa[1])]
        assert len(hits) == 1, f"edit must match exactly once in {cat}: {old[:60]!r} (matched {len(hits)})"
        g, qa = hits[0]
        qa[1] = [a.replace(old, new) for a in qa[1]]
        log.append((cat, qa[0], old, new, why, ref))

    for cat, qstart, note in NOTES:
        hits = [qa for g in cats[cat] for qa in g[2] if qa[0].startswith(qstart)]
        assert len(hits) == 1, f"note target must match exactly once in {cat}: {qstart!r}"
        hits[0][1].append(f"Advisor note: {note}")

    OUT.mkdir(parents=True, exist_ok=True)
    names = {v: k for k, v in SECTION_IDS.items()}
    for cat, groups in cats.items():
        out = [f"# {names[cat]}", "", "## Summary", "", SUMMARIES[cat], "", "## Questions and answers", "",
               "From Vac2Go's Round 2 Knowledge Base Entries. Answer from these, keeping their meaning; where an answer gives a general figure and a unit card gives that unit's own figure, use the unit's figure for that unit.", ""]
        for title, gnote, qas in groups:
            out += [f"### {title}", ""]
            if gnote:
                out += [gnote, ""]
            for q, ans in qas:
                out += [f"**Q: {q}**", ""] + ans + [""]
        (OUT / f"{cat}.md").write_text("\n".join(out).rstrip() + "\n", encoding="utf-8")

    e = ["# Edits to the Round 2 Knowledge Base text", "",
         "Every change made to the client's wording when building these category files, generated by `phase-2/tools/build_categories.py`. Everything not listed here is word for word from the client's document. Items marked \"pending\" are on the client question list.", ""]
    for cat, q, old, new, why, ref in log:
        e += [f"## {names[cat]}: {q}", "", f"- **Was:** {old}", f"- **Now:** {new}", f"- **Why:** {why} ({ref})", ""]
    e += ["## Advisor notes added", "", "Notes appended after an answer, pointing to unit literature that differs from the general figure:", ""]
    for cat, qstart, note in NOTES:
        e += [f"- **{names[cat]}**, after \"{qstart}\": {note}"]
    (OUT / "EDITS.md").write_text("\n".join(e).rstrip() + "\n", encoding="utf-8")
    print(f"built {len(cats)} category files, {total} questions, {len(log)} edits, {len(NOTES)} notes")


if __name__ == "__main__":
    main()
