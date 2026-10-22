#!/usr/bin/env python3
"""
Client feedback 2026-10-21: the advisor must never reveal the materials behind its
answers (brochures, literature, sheets, documents, their dates) or point out gaps in
them. The unit cards are what the model reads, so their wording must not hand it that
vocabulary. This rewrites every such phrase in kb/units/*.md once; the {src: ...}
audit tags (stripped before the model sees a card) keep the provenance.

    python3 -I phase-2/tools/neutralize_cards.py

Each edit must match exactly once, or the script stops. None as the replacement
deletes the whole line.
"""
import sys
from pathlib import Path

UNITS = Path(__file__).resolve().parents[2] / "wp-content/plugins/vac2go-ai-advisor/kb/units"

EDITS = [
    ("130-bbl-tankers", "Figures come from Vac2Go's own website, as recorded in a Vac2Go spec reference.", "Figures are as Vac2Go publishes them."),
    ("130-bbl-tankers", "- The source lists \"DOT / ASME\" but does not say every tanker in the fleet carries that certification.", "- \"DOT / ASME\" is listed, but it is not stated that every tanker in the fleet carries that certification."),
    ("bossvac-hydrovac-trailer", "- Hydro excavation. The sheet names no specific job types.", "- Hydro excavation."),
    ("bossvac-hydrovac-trailer", "- The sheet does not separate standard from optional equipment.", "- Standard and optional equipment are not separated."),
    ("bossvac-hydrovac-trailer", "- \"PINAL\" is printed that way on the sheet (likely meaning pintle);", "- The second hitch type is listed as \"PINAL\" (likely meaning pintle);"),
    ("bossvac-hydrovac-trailer", "and the sheet does not say whether 400 gallons is the total or per tank. Quote both as printed and do not interpret.", "and it is not stated whether 400 gallons is the total or per tank. Quote both as listed and do not interpret."),
    ("bossvac-hydrovac-trailer", "- The sheet is undated; a rep can confirm it matches Vac2Go's unit.", None),
    ("ctos-70-bbl-liquid-vacuum", "- No literature has been provided for this unit: pump or blower, tank material, certifications, dimensions and chassis.", "- Pump or blower, tank material, certifications, dimensions and chassis."),
    ("gapvax-combo-g7-trailer-jetter", "The G7 brochure describes a jetter-only trailer with no vacuum system and no debris tank.", "The G7 is a jetter-only trailer with no vacuum system and no debris tank."),
    ("gapvax-combo-g7-trailer-jetter", "- Any vacuum or debris capability (no vacuum system, debris tank, CFM or in Hg figure appears in the brochure).", "- Any vacuum or debris capability: it has no vacuum system or debris tank, and no CFM or in Hg figure."),
    ("gapvax-combo-g7-trailer-jetter", "- The fleet name says \"Combo\", but the literature describes a jetter only.", "- The fleet name says \"Combo\", but the unit is a jetter only."),
    ("gapvax-hv-33", "(the brochure calls it the HV33 HydroVax)", "(also called the HV33 HydroVax)"),
    ("gapvax-hv-33", "- Hydro excavation, wet or dry. The brochure names no specific job types.", "- Hydro excavation, wet or dry."),
    ("gapvax-hv-33", "- The only source is an older GapVax brochure, and the HV33 no longer appears in GapVax's current HV Series literature. Say the figures come from the manufacturer's brochure and that a rep can confirm the current unit's build.", "- The HV33 is an older GapVax model. Give the figures as stated, with the configuration note, and a rep can confirm the current unit's build."),
    ("gapvax-hv-56", "- HV56: the Vac2Go fleet unit. The same literature also covers the HV55, which is a different model and not this unit.", "- HV56: the Vac2Go fleet unit. The HV55 is a different model and not this unit."),
    ("gapvax-hv-56", "HV Series (series-level; the brochure does not confirm each item for the HV56 specifically):", "HV Series (series-level; not confirmed for the HV56 specifically):"),
    ("gapvax-hv-56", "- Keep HV56 figures and series-level figures apart. Say \"the HV Series literature lists\" for anything from the series-level list.", "- Keep HV56 figures and series-level figures apart. Present series-level figures as figures for the HV Series."),
    ("gapvax-hv-57", "- Chassis, dimensions and weight. These come from Vac2Go's fleet records, not the brochure.", "- Chassis, dimensions and weight."),
    ("gapvax-mc1312", "- No document for the MC1312 itself: debris body, water capacity, airflow, vacuum, pump, hose reel, boom, dimensions, weights and chassis.", "- Debris body, water capacity, airflow, vacuum, pump, hose reel, boom, dimensions, weights and chassis."),
    ("gapvax-mc1312", "the MC Series unit with a published spec sheet. Do not present", "the MC Series unit with its own figures. Do not present"),
    ("gapvax-mc1510", "Figures come from GapVax's MC Series web page, as recorded in a Vac2Go spec reference.", "Figures are GapVax's published MC Series figures."),
    ("gapvax-mc1510", "- The source does not separate standard from optional features.", "- Standard and optional features are not separated."),
    ("gapvax-mc1510", "- The source does not explain what \"180 degrees\" on the hose reel refers to; quote it as written.", "- What \"180 degrees\" on the hose reel refers to is not stated; quote it as written."),
    ("guzzcavator", "- The brochure dates from 2014, so specs may differ on the unit a customer gets.", "- Specs may differ on the unit a customer gets."),
    ("guzzcavator", "the brochure offers a choice of booms.", "there is a choice of booms."),
    ("guzzler-classic", "- Guzzler Classic (CL), the base loader described in the brochure.", "- Guzzler Classic (CL), the base loader."),
    ("guzzler-classic", "- The same brochure lists two offloading options built on the Classic, XCR and the High-Rail System.", "- Two offloading options are built on the Classic: XCR and the High-Rail System."),
    ("guzzler-classic", "- Industries the brochure names include", "- Industries served include"),
    ("guzzler-classic", "- The brochure dates from 2014 and says specifications are subject to change. Vac2Go units may differ in blower, options and chassis.", "- Specifications are subject to change. Vac2Go units may differ in blower, options and chassis."),
    ("guzzler-classic", "- The brochure does not say which blower (dual-lobe or tri-lobe) gives which end", "- It is not stated which blower (dual-lobe or tri-lobe) gives which end"),
    ("guzzler-classic", "- The brochure's side drawing has unlabelled dimension lines", "- A side drawing has unlabelled dimension lines"),
    ("guzzler-dense-phase", "Our literature for this unit is a single sheet about the Dense Phase Offloading system on the Guzzler NX air mover.", "The Guzzler Dense Phase is the Dense Phase Offloading system on the Guzzler NX air mover."),
    ("guzzler-dense-phase", "- Base unit named on the sheet: Guzzler NX air mover", "- Base unit: Guzzler NX air mover"),
    ("guzzler-dense-phase", "- The sheet says the NX \"is now available with\" the system. It does not say what is standard or optional.", "- What is standard or optional is not stated."),
    ("guzzler-dense-phase", "- The sheet's only figure is 120 feet vertically. Do not quote any other number for this unit.", "- The only figure is 120 feet vertically. Do not quote any other number for this unit."),
    ("guzzler-dense-phase", "- The sheet describes a Guzzler NX with dense phase offloading; it does not mention the Guzzler Classic.", "- The base truck is a Guzzler NX with dense phase offloading, not the Guzzler Classic."),
    ("guzzler-dense-phase", "- The sheet is undated and old (it calls the NX \"new\"). Do not describe the unit as new.", "- Do not describe the unit as new."),
    ("guzzler-high-rail", "The Guzzler High-Rail has no document of its own. Our only literature is one option paragraph in the Guzzler Classic brochure, which lists the High-Rail System as an offloading option of the Guzzler Classic (CL), for loading or driving over rails.", "The Guzzler High-Rail is the High-Rail System, an offloading option of the Guzzler Classic (CL), for loading or driving over rails."),
    ("guzzler-high-rail", "; the Classic brochure also names railroads among its industries.", "; railroads are among the Guzzler Classic's industries."),
    ("guzzler-high-rail", "- The brochure dates from 2014.", None),
    ("guzzler-xcr", "The Guzzler XCR has no document of its own. Our only literature is one option paragraph in the Guzzler Classic brochure, which lists XCR as an offloading option of the Guzzler Classic (CL).", "The Guzzler XCR is an offloading option of the Guzzler Classic (CL)."),
    ("guzzler-xcr", "- The brochure lists XCR under \"Offloading Options\" for the Guzzler Classic. No other XCR equipment is described.", "- XCR is one of the Guzzler Classic's offloading options."),
    ("guzzler-xcr", "- The brochure dates from 2014.", None),
    ("huber-am30-hd", "No manufacturer document we hold names the AM30 HD. Vac2Go treats it as essentially the same unit as the Huber AM36, so the AM36 literature is the reference below, with the same hedges.", "Vac2Go treats it as essentially the same unit as the Huber AM36, so the AM36 figures below apply, with the same hedges."),
    ("huber-am30-hd", "These are AM36 figures, not AM30 HD figures from any document:", "These are AM36 figures:"),
    ("huber-am30-hd", "the older brochure lists 6\" intake and discharge ports as standard and an 8\" intake as an option", "earlier builds list 6\" intake and discharge ports as standard and an 8\" intake as an option"),
    ("huber-am30-hd", "- Industrial vacuum work, wet or dry products (AM36 literature). No specific industries are named.", "- Industrial vacuum work, wet or dry products."),
    ("huber-am30-hd", "listed in the older brochure as 10 GPM @ 2500 psi and 20 GPM @ 4000 psi", "listed on earlier builds as 10 GPM @ 2500 psi and 20 GPM @ 4000 psi"),
    ("huber-am30-hd", "- Where the two AM36 documents differ (intake, water package pressure), give both and say it depends on configuration.", "- Where current and earlier builds differ (intake, water package pressure), give both and say it depends on configuration."),
    ("huber-am36", "The literature says it handles wet or dry products.", "It handles wet or dry products."),
    ("huber-am36", "- Tank build (older brochure):", "- Tank build (earlier builds):"),
    ("huber-am36", "; the older brochure calls it a centrifugal 9-stage cyclone", "; also described as a centrifugal 9-stage cyclone"),
    ("huber-am36", "; the older brochure lists 6\" intake and discharge ports as standard and an 8\" intake as an option", "; earlier builds list 6\" intake and discharge ports as standard and an 8\" intake as an option"),
    ("huber-am36", "- Industrial vacuum work, wet or dry products. The literature names no specific industries.", "- Industrial vacuum work, wet or dry products."),
    ("huber-am36", "; the older brochure lists 10 GPM @ 2500 psi and 20 GPM @ 4000 psi", "; earlier builds list 10 GPM @ 2500 psi and 20 GPM @ 4000 psi"),
    ("huber-am36", "- Two documents: AM36_HD (2024, newest) and the Huber Air Mover Brochure (2017). Where they differ (intake size, water package pressure), give both and say it depends on the unit's configuration.", "- Where current and earlier builds differ (intake size, water package pressure), give both and say it depends on the unit's configuration."),
    ("huber-am36", "- The document does not say what the 68\" in \"68\" HighDump\" measures.", "- What the 68\" in \"68\" HighDump\" measures is not stated."),
    ("huber-baron-hx", "- The three Huber documents (2025 brochure and sell sheet, 2026 combined brochure) agree on the main figures; the idle figures appear only in the 2026 document.", None),
    ("huber-baron-hx", "A table in the sell sheet lists 30 (ft.)", "A 30 (ft.) figure is also listed"),
    ("huber-baron-hx", "; the documents never say whether that is the 24' plus the extension.", "; it is not stated whether that is the 24' plus the extension."),
    ("huber-berringer-liquid-ring", "- Source is the 2016 Berringer product family brochure (the Berringer was introduced in 1990). It also covers the Berringer PD, whose PD blower figures do not apply here.", "- The Berringer PD is a different unit; its PD blower figures do not apply here."),
    ("huber-berringer-liquid-ring", "- Quote tank size in gallons only. The brochure prints a barrel figure beside the gallons that does not match it; do not convert.", "- Quote tank size in gallons only; do not convert to barrels."),
    ("huber-berringer-pd", "- Standard: the brochure's PD features list gives only the tank, blower and intake above.", "- Standard: the tank, blower and intake above."),
    ("huber-berringer-pd", "- The only source is the 2016 Berringer product family brochure, which covers the liquid ring Berringer and the Berringer PD together. The liquid ring pump figures in that brochure do not apply to the PD;", "- The liquid ring Berringer is a different unit; its liquid ring pump figures do not apply to the PD;"),
    ("huber-berringer-pd", "- Do not pair the blower CFM choices with the Robuschi blower models; the brochure does not.", "- Do not pair the blower CFM choices with the Robuschi blower models; the pairing is not stated."),
    ("huber-berringer-pd", "- The brochure does not say whether the standard Berringer's rear door, tank lift and lights also apply to the PD.", "- It is not stated whether the standard Berringer's rear door, tank lift and lights also apply to the PD."),
    ("huber-dominator-ss", "- Stainless option in the Dominator literature:", "- The Dominator's stainless option:"),
    ("huber-dominator-ss", "- No document describes the Dominator SS as its own model: its tank capacity, pump, certifications and dimensions.", "- Its own tank capacity, pump, certifications and dimensions."),
    ("huber-dominator", "- Both brochures date from 2016. Never quote a CFM for the PD; its brochure gives none.", "- Never quote a CFM for the PD; none is stated."),
    ("huber-king-vac", "- Single source: the King Vac brochure (copyright 1999-2017; the King Vac was introduced in 1986). The PDF is marked as a proof.", None),
    ("huber-king-vac", "- Quote tank size as the brochure states it; do not do your own gallon/barrel conversions.", "- Quote tank size as stated; do not do your own gallon/barrel conversions."),
    ("huber-king-vac", "- Do not count cyclones from photos; the brochure gives no number.", "- The number of cyclones is not stated; do not give one."),
    ("huber-knight", "- Single source: the Knight brochure. The Knight was developed in 2008, though the brochure footer carries an older copyright date.", None),
    ("huber-knight", "- The brochure says the Knight shares the King Vac's features, but its own figures differ", "- The Knight shares the King Vac's features, but its own figures differ"),
    ("huber-sc-1009", "; the brochure highlights state 5,300 CFM and 18\" Hg", "; 5,300 CFM and 18\" Hg is also stated"),
    ("huber-sc-1009", "- The literature covers the SC1009 and SC1512 together (\"Lancer SC1009 & SC1512\"). The tank, fresh water and dump height figures above are the first value of each paired listing, matched to the SC1009 only by the order the models are named; no table names the model next to each figure. Say so if a customer is relying on an exact figure, and offer a Vac2Go rep to confirm.", "- The SC1009 and SC1512 are listed together (\"Lancer SC1009 & SC1512\"), and the tank, fresh water and dump height figures above are matched to the SC1009 by listing order. If a customer is relying on an exact figure, say a Vac2Go rep can confirm it."),
    ("huber-sc-1009", "- Blower: the sell sheet (5,000 CFM) and brochure (5,300 CFM) share the same date. Give both and say it depends on configuration.", "- Blower: 5,000 CFM and 5,300 CFM are both listed. Give both and say it depends on configuration."),
    ("huber-sc-1009", "- Boom: the documents do not say whether the 28' includes the 9' extension.", "- Boom: it is not stated whether the 28' includes the 9' extension."),
    ("huber-sc-1512", "; the brochure highlights state 5,300 CFM and 18\" Hg", "; 5,300 CFM and 18\" Hg is also stated"),
    ("huber-sc-1512", "- The literature covers the SC1009 and SC1512 together (\"Lancer SC1009 & SC1512\"). The tank, fresh water and dump height figures above are the second value of each paired listing, matched to the SC1512 only by the order the models are named; no table names the model next to each figure. Say so if a customer is relying on an exact figure, and offer a Vac2Go rep to confirm.", "- The SC1009 and SC1512 are listed together (\"Lancer SC1009 & SC1512\"), and the tank, fresh water and dump height figures above are matched to the SC1512 by listing order. If a customer is relying on an exact figure, say a Vac2Go rep can confirm it."),
    ("huber-sc-1512", "- Blower: the sell sheet (5,000 CFM) and brochure (5,300 CFM) share the same date. Give both and say it depends on configuration.", "- Blower: 5,000 CFM and 5,300 CFM are both listed. Give both and say it depends on configuration."),
    ("huber-sc-1512", "- Boom: the documents do not say whether the 28' includes the 9' extension.", "- Boom: it is not stated whether the 28' includes the 9' extension."),
    ("huber-scrubber", "- Removal: the datasheet states vapors are removed", "- Removal: vapors are stated to be removed"),
    ("huber-scrubber", "- Quote removal figures only as \"the manufacturer's datasheet states\". Never present them as proof of regulatory compliance.", "- Quote removal figures as manufacturer-stated figures. Never present them as proof of regulatory compliance."),
    ("huber-scrubber", "- Do not say it treats H2S; the datasheet does not say so.", "- Do not say it treats H2S; that is not stated."),
    ("huber-scrubber", "- Source is a single 2017 datasheet.", None),
    ("imperial-industries-high-volume-pump", "Despite the name, our literature describes this unit as a 5,000 gallon", "Despite the name, this unit is a 5,000 gallon"),
    ("imperial-industries-high-volume-pump", "- The document names no applications. It describes a vacuum tank truck for liquid hauling.", "- Vacuum loading and hauling liquids."),
    ("imperial-industries-high-volume-pump", "- The document is a single build list and does not separate standard from optional. It also lists", "- Standard and optional are not separated. The build also includes"),
    ("imperial-industries-high-volume-pump", "unless Vac2Go says otherwise. The document describes no separate pump.", "unless Vac2Go says otherwise. No separate pump is stated."),
    ("imperial-industries-high-volume-pump", "- Do not state what \"5454\" means or name the tank material; the document does not.", "- Do not state what \"5454\" means or name the tank material; neither is stated."),
    ("imperial-industries-high-volume-pump", "- The document does not say what the \"Primary 12\" x 4\"\" is.", "- What the \"Primary 12\" x 4\"\" is, is not stated."),
    ("kaiser-premier-cv-series", "- Hydro excavation. The brochure names no specific job list.", "- Hydro excavation."),
    ("kaiser-premier-cv-series", "- The brochure dates from 2019.", None),
    ("kaiser-premier-terravac", "- Our literature covers the TerraVac Xpose800 only", "- The TerraVac Xpose800"),
    ("kaiser-premier-terravac", "- The brochure links the TerraVac to the CV Series, but its figures are much smaller.", "- The TerraVac is linked to the CV Series, but its figures are much smaller."),
    ("kaiser-premier-terravac", "- Specs come from the manufacturer brochure (footer code 12012020) and may have changed.", "- Specs may have changed on current units."),
    ("portable-restroom-truck", "- The source is a build specification, not a delivered-unit sheet; a rep can confirm the truck being rented matches it.", "- These figures are for one build; a rep can confirm the truck being rented matches it."),
    ("presvac-powervac", "The sheet shows three built units; which model you get depends on the unit", "Three built units are listed; which model you get depends on the unit"),
    ("presvac-powervac", "- Standard: the sheet does not list standard equipment beyond blower, transfer case, tank and filtration.", "- Standard: blower, transfer case, tank and filtration; nothing further is listed as standard."),
    ("presvac-powervac", "- The sheet says its specifications refer to the unit pictured, so each model block describes one built example.", "- Each model's figures describe one built example."),
    ("presvac-powervac", "- The sheet is undated.", None),
    ("roll-off-trucks", "Figures come from Vac2Go's own website, as recorded in a Vac2Go spec reference.", "Figures are as Vac2Go publishes them."),
    ("roll-off-trucks", "- Quote the hoist as \"80K\" as printed; the source gives no unit.", "- Quote the hoist as \"80K\" as written; no unit is stated."),
    ("schellvac-svhx", "Which model you get depends on the unit; the literature does not say which one Vac2Go runs.", "Which model you get depends on the unit; a rep can confirm which one Vac2Go runs."),
    ("schellvac-svhx", "- The booklet dates from 2018 and the SVHX8 and SVHX11 may no longer be current models, so specs may be outdated.", "- The SVHX8 and SVHX11 may no longer be current models."),
    ("schellvac-svhx", "- The booklet also covers SchellVac vacuum tank and portable toilet trucks (SVIT, SVPT, SVST). Those are not this unit;", "- SchellVac's vacuum tank and portable toilet trucks (SVIT, SVPT, SVST) are not this unit;"),
    ("super-products-camel-max-series", "- The blower range is the same for all three models; the brochure does not say which unit gets which cfm.", "- The blower range is the same for all three models; which unit gets which cfm is not stated."),
    ("super-products-camel-max-series", "- The brochure does not say whether a dump-configured 1200 can take Wastewater Recycle.", "- It is not stated whether a dump-configured 1200 can take Wastewater Recycle."),
    ("super-products-high-dump", "None of our manufacturer literature describes a unit by this name, so this card has no specs of its own. A Vac2Go rep confirms what the unit is and its specs.", "This card has no specs of its own. A Vac2Go rep confirms what the unit is and its specs."),
    ("super-products-high-dump", "- The Super Products Supersucker brochure does not mention a High Dump.", "- Do not apply Supersucker figures to the High Dump."),
    ("super-products-mud-dog-1200", "- Hydro excavation. The brochure names no specific job list.", "- Hydro excavation."),
    ("super-products-mud-dog-1200", "- The brochure dates from 2016; current units may differ.", "- Current units may differ."),
    ("super-products-mud-dog-1200", "Our literature is the Mud Dog 1200 brochure; a rep confirms the version a customer would get.", "The figures here are for the Mud Dog 1200; a rep confirms the version a customer would get."),
    ("super-products-supersucker", "- The brochure dates from 2012; current units may differ.", "- Current units may differ."),
    ("super-products-supersucker", "- The brochure does not mention a High Dump. For the Super Products High Dump,", "- For the Super Products High Dump,"),
    ("tornado-f4", "- Our literature is a spec sheet for one specific build: a 2023 Peterbilt 567 with the Tornado F4 Eco-Lite body. Vac2Go's unit may differ.", "- These figures are for one specific build: a 2023 Peterbilt 567 with the Tornado F4 Eco-Lite body. Vac2Go's unit may differ."),
    ("tornado-f4", "- The sheet lists one configured truck and does not separate standard from optional equipment. Also listed on that build:", "- Standard and optional equipment are not separated. Also on that build:"),
    ("tornado-f4", "- Describe the figures as \"the F4 spec sheet in our literature lists\" and say a rep can confirm the chassis, engine and equipment on the unit being rented.", "- Say these are the figures for a configured F4 build and that a rep can confirm the chassis, engine and equipment on the unit being rented."),
    ("tornado-f4", "- CARB / California use: the sheet notes CARB compliance was not included on that build.", "- CARB / California use: CARB compliance was not included on that build."),
    ("tractors", "Each tractor is spec'd per order, so these come from each tractor's build sheet, not a brochure.", "Each tractor is spec'd per order."),
    ("tractors", "- The category knowledge says the wet kit is standard; Vac2Go's tractor spec reference and website say it is available.", "- A wet kit is listed as both standard and available."),
    ("truvac-hxx", "Our literature is a 2024 Truvac sheet and a 2015 Vactor brochure.", "Figures are given for the current Truvac build and the earlier Vactor build."),
    ("truvac-hxx", "- Vacuum (2024 Truvac):", "- Vacuum (current Truvac build):"),
    ("truvac-hxx", "- Vacuum (2015 Vactor):", "- Vacuum (earlier Vactor build):"),
    ("truvac-hxx", "- Debris body (2015 Vactor):", "- Debris body (earlier Vactor build):"),
    ("truvac-hxx", "- Boom (2024 Truvac):", "- Boom (current Truvac build):"),
    ("truvac-hxx", "- Boom (2015 Vactor):", "- Boom (earlier Vactor build):"),
    ("truvac-hxx", "- Water pump (2024 Truvac):", "- Water pump (current Truvac build):"),
    ("truvac-hxx", "- Water pump (2015 Vactor):", "- Water pump (earlier Vactor build):"),
    ("truvac-hxx", "- Water tank (2015 Vactor only):", "- Water tank (earlier Vactor build only):"),
    ("truvac-hxx", "- Standard (2015 Vactor):", "- Standard (earlier Vactor build):"),
    ("truvac-hxx", "- Optional (2015 Vactor):", "- Optional (earlier Vactor build):"),
    ("truvac-hxx", "; water tank capacity on the 2024 sheet.", "; water tank capacity on the current build."),
    ("truvac-hxx", "- The 28 in Hg tri-lobe blower is listed at 5200 cfm on the 2024 Truvac sheet and 5250 cfm (Hibon 8702) in the 2015 Vactor brochure.", "- The 28 in Hg tri-lobe blower is listed at 5200 cfm on the current build and 5250 cfm (Hibon 8702) on the earlier build."),
    ("truvac-hxx", "- The 1200 gallon water tank comes only from the 2015 brochure.", "- The 1200 gallon water tank is listed only for the earlier Vactor build."),
    ("two-box-roll-off-trailers", "Figures come from Vac2Go's own website, as recorded in a Vac2Go spec reference.", "Figures are as Vac2Go publishes them."),
    ("two-box-roll-off-trailers", "- The source does not separate standard from optional equipment.", "- Standard and optional equipment are not separated."),
    ("two-box-roll-off-trailers", "The source does not define it:", "It is not defined:"),
    ("two-box-roll-off-trailers", "The source does not say whether the tonnage is per box or combined.", "It is not stated whether the tonnage is per box or combined."),
    ("vac-jet-rodding", "- No literature has been provided for this unit: configuration, capacities, pump, hose reel and dimensions.", "- Configuration, capacities, pump, hose reel and dimensions."),
    ("vactor-2100-plus", "Our only literature is a 2011 brochure, which leaves out many basic figures.", "Many basic figures are not available for this unit."),
    ("vactor-2100-plus", "- The brochure dates from 2011; current units likely differ. For any basic figure, say it is not in our literature and a rep can confirm.", "- Current units likely differ. For any basic figure not given here, use the wording for gaps."),
    ("vactor-2100-plus", "Our literature is the 2100 Plus brochure; a rep confirms which version a customer would get.", "The figures here are for the 2100 Plus; a rep confirms which version a customer would get."),
    ("vactor-paradigm", "- The brochure also mentions Vactor's wider vacuum excavator range; those capacities are not the Paradigm's.", "- Capacities of Vactor's wider vacuum excavator range are not the Paradigm's."),
    ("vactor-paradigm", "- The brochure dates from 2016/2017.", None),
    ("vermeer-lp-xdt-vacuum-excavator", "The sheet covers five models; which model you get depends on the unit", "Five models are listed; which model you get depends on the unit"),
    ("vermeer-lp-xdt-vacuum-excavator", "- Vacuum excavation. The sheet names no job list.", "- Vacuum excavation."),
    ("vermeer-lp-xdt-vacuum-excavator", "- The sheet dates from 2021.", None),
    ("water-trucks", "- The Vac2Go reference describes the hose reel as rear mounted and the nozzle as adjustable; the brochure says firemans nozzle. Both describe the same reel.", "- The hose reel is rear mounted, with an adjustable fireman's nozzle."),
]


def main():
    files = {}
    for unit, old, new in EDITS:
        path = UNITS / f"{unit}.md"
        text = files.get(unit) or path.read_text(encoding="utf-8")
        n = text.count(old)
        if n != 1:
            sys.exit(f"{unit}: edit must match exactly once ({n}): {old[:70]!r}")
        if new is None:
            lines = text.split("\n")
            hit = [i for i, l in enumerate(lines) if old in l]
            del lines[hit[0]]
            text = "\n".join(lines)
        else:
            text = text.replace(old, new)
        files[unit] = text
    # The "Not in our literature" heading hands the model the phrase itself.
    for path in sorted(UNITS.glob("*.md")):
        unit = path.stem
        text = files.get(unit) or path.read_text(encoding="utf-8")
        files[unit] = text.replace("## Not in our literature", "## Not covered")
    for unit, text in files.items():
        (UNITS / f"{unit}.md").write_text(text, encoding="utf-8")
    print(f"{len(EDITS)} edits; heading renamed in {len(files)} files")


if __name__ == "__main__":
    main()
