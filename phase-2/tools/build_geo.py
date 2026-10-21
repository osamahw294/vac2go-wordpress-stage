#!/usr/bin/env python3
"""
Build the offline location data for the nearest-branch lookup (change request of
20 October 2026, Change 1).

    python3 -I phase-2/tools/build_geo.py

Input (US Census, downloaded into phase-2/work/client-2026-10-21/census/; not in git):
  raw/2026_Gaz_zcta_national.txt    ZIP code areas (ZCTA) with centroid coordinates
  raw/2026_Gaz_place_national.txt   cities, towns and CDPs with coordinates
  sub-est2025.csv                   place populations (2025 estimates), for tiebreaks

Output (wp-content/plugins/vac2go-ai-advisor/kb/geo/):
  zcta.tsv     zip, lat, lon
  places.tsv   key, state, lat, lon, population, land area   (key: normalised name)
  states.tsv   abbreviation, name, lat, lon   (population-weighted centre of its places)

The ticket asks for "City, ST", bare city names, ZIP codes and bare states. Names are
normalised the same way here and in VA_Geo::key(): lower case, no punctuation,
"saint" -> "st", "sainte" -> "ste", "ft" -> "fort", "mt" -> "mount".
"""
import csv
import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
SRC = ROOT / "phase-2/work/client-2026-10-21/census"
OUT = ROOT / "wp-content/plugins/vac2go-ai-advisor/kb/geo"

STATES = {
    "AL": "Alabama", "AK": "Alaska", "AZ": "Arizona", "AR": "Arkansas", "CA": "California",
    "CO": "Colorado", "CT": "Connecticut", "DE": "Delaware", "DC": "District of Columbia",
    "FL": "Florida", "GA": "Georgia", "HI": "Hawaii", "ID": "Idaho", "IL": "Illinois",
    "IN": "Indiana", "IA": "Iowa", "KS": "Kansas", "KY": "Kentucky", "LA": "Louisiana",
    "ME": "Maine", "MD": "Maryland", "MA": "Massachusetts", "MI": "Michigan", "MN": "Minnesota",
    "MS": "Mississippi", "MO": "Missouri", "MT": "Montana", "NE": "Nebraska", "NV": "Nevada",
    "NH": "New Hampshire", "NJ": "New Jersey", "NM": "New Mexico", "NY": "New York",
    "NC": "North Carolina", "ND": "North Dakota", "OH": "Ohio", "OK": "Oklahoma", "OR": "Oregon",
    "PA": "Pennsylvania", "RI": "Rhode Island", "SC": "South Carolina", "SD": "South Dakota",
    "TN": "Tennessee", "TX": "Texas", "UT": "Utah", "VT": "Vermont", "VA": "Virginia",
    "WA": "Washington", "WV": "West Virginia", "WI": "Wisconsin", "WY": "Wyoming",
    "PR": "Puerto Rico",
}

# Census legal/statistical area descriptions appended to place names.
SUFFIX = re.compile(
    r"\s+(city and borough|consolidated government|unified government|metropolitan government|"
    r"metro government|urban county|municipality|borough|city|town|township|village|CDP|"
    r"comunidad|zona urbana|plantation|corporation)(\s+\(balance\))?$",
    re.I,
)
# Consolidated city-counties carry the county in the name: "Nashville-Davidson",
# "Louisville/Jefferson County". Index the city part too. Only for these forms, so
# ordinary hyphenated names like "Winston-Salem" are left alone.
CONSOLIDATED = re.compile(r"(metropolitan government|metro government|consolidated government|unified government|urban county|\(balance\))", re.I)


def key(name):
    k = name.lower()
    k = re.sub(r"[.'’]", "", k)
    k = re.sub(r"[^a-z0-9]+", " ", k).strip()
    k = re.sub(r"^saint ", "st ", k)
    k = re.sub(r" saint ", " st ", k)
    k = re.sub(r"^sainte ", "ste ", k)
    k = re.sub(r"^ft ", "fort ", k)
    k = re.sub(r"^mt ", "mount ", k)
    return k


def rows(path):
    with open(path, encoding="utf-8") as f:
        head = [h.strip() for h in f.readline().split("|")]
        for line in f:
            yield dict(zip(head, (c.strip() for c in line.split("|"))))


def main():
    zcta_src = SRC / "raw/2026_Gaz_zcta_national.txt"
    place_src = SRC / "raw/2026_Gaz_place_national.txt"
    pop_src = SRC / "sub-est2025.csv"
    for p in (zcta_src, place_src, pop_src):
        if not p.exists():
            sys.exit(f"missing source: {p}")
    OUT.mkdir(parents=True, exist_ok=True)

    # Populations by state FIPS + place FIPS (incorporated places and consolidated cities).
    pop = {}
    with open(pop_src, encoding="latin-1") as f:
        for r in csv.DictReader(f):
            if r["SUMLEV"] in ("162", "170", "172") and r["PLACE"] != "00000":
                pop[r["STATE"] + r["PLACE"]] = max(pop.get(r["STATE"] + r["PLACE"], 0), int(r["POPESTIMATE2025"] or 0))

    zips = []
    for r in rows(zcta_src):
        zips.append((r["GEOID"], float(r["INTPTLAT"]), float(r["INTPTLONG"])))
    zips.sort()
    with open(OUT / "zcta.tsv", "w", encoding="utf-8") as f:
        for z, lat, lon in zips:
            f.write(f"{z}\t{lat:.5f}\t{lon:.5f}\n")

    places, centre = [], {}
    for r in rows(place_src):
        st = r["USPS"]
        if st not in STATES:
            continue
        name = SUFFIX.sub("", r["NAME"]).strip()
        lat, lon = float(r["INTPTLAT"]), float(r["INTPTLONG"])
        p = pop.get(r["GEOID"], 0)
        aland = int(r["ALAND"])
        names = {name}
        if CONSOLIDATED.search(r["NAME"]):
            names.add(re.split(r"[-/]", name)[0].strip())
        for n in names:
            places.append((key(n), st, lat, lon, p, aland))
        c = centre.setdefault(st, [0.0, 0.0, 0])
        if p:
            c[0] += lat * p
            c[1] += lon * p
            c[2] += p
    places.sort()
    with open(OUT / "places.tsv", "w", encoding="utf-8") as f:
        for k, st, lat, lon, p, aland in places:
            f.write(f"{k}\t{st}\t{lat:.5f}\t{lon:.5f}\t{p}\t{aland}\n")

    with open(OUT / "states.tsv", "w", encoding="utf-8") as f:
        for st, name in sorted(STATES.items()):
            c = centre.get(st)
            if not c or not c[2]:
                continue
            f.write(f"{st}\t{name}\t{c[0] / c[2]:.5f}\t{c[1] / c[2]:.5f}\n")

    print(f"zcta {len(zips)}, places {len(places)}, states {len(centre)}, populations {len(pop)}")


if __name__ == "__main__":
    main()
