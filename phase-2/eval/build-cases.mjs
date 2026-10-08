// Build phase-2/eval/cases.json from the knowledge base and handcases.json.
//
//   node phase-2/eval/build-cases.mjs
//
// E1: every client question (kb/categories/*.md), prefixed with its category so a
//     fresh conversation knows what "it" is, graded against the client's answer.
// E2: per unit, up to two spec questions built from the card's Key specs (lines with
//     one to three figures), plus one question the card can't answer.
// E3-E5: handcases.json.
import { readFileSync, writeFileSync, readdirSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const here = dirname(fileURLToPath(import.meta.url));
const plugin = join(here, '../../wp-content/plugins/vac2go-ai-advisor');
const fleet = JSON.parse(readFileSync(join(plugin, 'kb/fleet.json'), 'utf8'));
const hand = JSON.parse(readFileSync(join(here, 'handcases.json'), 'utf8'));

const stripSrc = (s) => s.replace(/[ \t]*\{src:[^}]*\}/g, '');
// Figures only: digits glued to a unit's letters ("yd3", "in3", "HV57") are not figures.
export const numbers = (s) => [...stripSrc(s).replace(/vac2go/gi, '').matchAll(/(?<![A-Za-z\d.])\d[\d,]*(?:\.\d+)?(?![A-Za-z]*\d)/g)].map((m) => m[0].replace(/,/g, ''));

// ---- E1 -------------------------------------------------------------------
const E1 = [];
for (const [cid, cat] of Object.entries(fleet.categories)) {
	const text = readFileSync(join(plugin, `kb/categories/${cid}.md`), 'utf8');
	const lines = text.split('\n');
	let n = 0;
	for (let i = 0; i < lines.length; i++) {
		const m = lines[i].match(/^\*\*Q: (.*)\*\*$/);
		if (!m) continue;
		const ans = [];
		for (let j = i + 1; j < lines.length && !lines[j].startsWith('**Q:') && !lines[j].startsWith('### '); j++) {
			if (lines[j].trim()) ans.push(lines[j].trim());
		}
		n++;
		E1.push({
			id: `E1-${cid}-${String(n).padStart(2, '0')}`,
			set: 'E1',
			category: cid,
			q: `Question about ${cat.name} equipment: ${m[1]}`,
			reference: ans.join('\n'),
		});
	}
}

// ---- E2 -------------------------------------------------------------------
const E2 = [];
for (const [uid, u] of Object.entries(fleet.units)) {
	const card = readFileSync(join(plugin, `kb/units/${uid}.md`), 'utf8');
	const specs = (card.match(/## Key specs\n([\s\S]*?)(\n## |$)/) || [, ''])[1].split('\n');
	let model = '';
	let picked = 0;
	for (const raw of specs) {
		const line = raw.trim();
		if (!line) continue;
		if (!line.startsWith('- ')) {
			// A per-model heading ("SVHX11 (midsize):"); any other prose line is not one.
			if (line.endsWith(':') && line.length < 40) model = line.replace(/:$/, '').replace(/\s*\(.*\)$/, '');
			continue;
		}
		const body = stripSrc(line.slice(2));
		const label = body.split(':')[0].trim();
		const want = [...new Set(numbers(body.split(':').slice(1).join(':')))];
		// Skip fractions ("1/4 in"): their digits would match almost any answer.
		if (!body.includes(':') || /\d\/\d/.test(body) || want.length < 1 || want.length > 3 || label.length > 60) continue;
		const subject = model ? `${u.name} (${model})` : u.name;
		E2.push({ id: `E2-${uid}-${++picked}`, set: 'E2', unit: uid, q: `What is the ${label.toLowerCase()} on the ${subject}?`, expect_numbers: want, source_line: body });
		if (picked >= 2) break;
	}
	const unknown = (card.match(/## Not in our literature\n- ([^\n]*)/) || [, ''])[1];
	const item = unknown.split(/[,.;]/)[0].replace(/^(Everything|Make|Exact)\b:?\s*/i, '').trim();
	if (item) {
		E2.push({ id: `E2-${uid}-unknown`, set: 'E2', unit: uid, q: `What is the ${item.toLowerCase()} of the ${u.name}?`, expect: 'unknown', source_line: unknown });
	}
}

// ---- E3-E5 ------------------------------------------------------------------
const E3 = hand.E3.map((c) => ({ ...c, set: 'E3' }));
const E4 = hand.E4.map((c) => ({ ...c, set: 'E4', q: c.q ?? c.turns.at(-1) }));
const E5 = hand.E5.map((c) => ({ ...c, set: 'E5' }));

const cases = [...E1, ...E2, ...E3, ...E4, ...E5];
writeFileSync(join(here, 'cases.json'), JSON.stringify(cases, null, '\t') + '\n');
console.log(`cases: ${cases.length} (E1 ${E1.length}, E2 ${E2.length}, E3 ${E3.length}, E4 ${E4.length}, E5 ${E5.length})`);
