// Load test: 30 visitors chatting with the staging advisor at the same time.
//
//   node phase-2/eval/load.mjs [--agents 30] [--turns 4] [--out load-results.jsonl]
//
// Asked for in the V1 feedback: "spinning up 30 agents at the same time on staging and
// having it ask questions ... monitor both speed and quality of responses".
//
// Each agent is one visitor: its own session, a job description from the E3 cases as
// the first question, then follow-ups a customer would ask, with 20 to 40 seconds of
// reading time between turns. All 30 first questions go out within 5 seconds of each
// other. Uses the streaming endpoint the widget uses, so time to first text is
// measured as a visitor sees it.
//
// Every request comes from this one machine's IP, so the per-IP limits (6 a minute,
// 30 an hour) must be raised on staging for the run; the global per-minute breaker
// (60) stays as it is and the pacing keeps under it.
import { readFileSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { randomUUID } from 'node:crypto';
import { check, universal, inventedNumbers, kbNumbers } from './checks.mjs';

const here = dirname(fileURLToPath(import.meta.url));
const BASE = process.env.VA_BASE_URL || 'https://b5205c85ce.nxcli.io';
const API = `${BASE}/wp-json/vac2go/v1`;
const arg = (name, def) => {
	const i = process.argv.indexOf(`--${name}`);
	return i > -1 ? process.argv[i + 1] : def;
};
const AGENTS = Number(arg('agents', '30'));
const TURNS = Number(arg('turns', '4'));
const out = join(here, arg('out', 'load-results.jsonl'));
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
const rand = (a, b) => a + Math.random() * (b - a);

const hand = JSON.parse(readFileSync(join(here, 'handcases.json'), 'utf8'));
const openers = hand.E3.filter((c) => c.category); // job descriptions with a known right category
const FOLLOWUPS = [
	'Which of your units would you suggest for that?',
	'What is the debris capacity on that one?',
	'How much vacuum and airflow does it have?',
	'Do I need a CDL to drive it?',
	'Can it handle hazardous material?',
	'What hose sizes does it come with?',
	'Is there anything it would not be a good fit for?',
	'How does that compare with a hydro excavator for this job?',
];
const kb = kbNumbers();

async function nonce() {
	for (let a = 1; ; a++) {
		try {
			return (await (await fetch(`${API}/nonce?_=${Date.now()}`, { cache: 'no-store' })).json()).nonce;
		} catch (e) {
			if (a >= 5) throw e;
			await sleep(5000);
		}
	}
}

// One streamed turn: time to first text, total time, final text, done payload.
async function turn(n, session, message) {
	const t0 = Date.now();
	let ttft = null;
	let text = '';
	let done = {};
	try {
		const r = await fetch(`${API}/chat/stream`, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': n, Accept: 'text/event-stream' },
			body: JSON.stringify({ session_id: session, request_id: randomUUID(), message, website: '', elapsed_ms: 15000 }),
		});
		if (!r.ok || !(r.headers.get('content-type') || '').includes('event-stream')) {
			const body = await r.json().catch(() => ({}));
			return { status: r.status, ms: Date.now() - t0, ttft, reply: body.reply || '', limited: !!body.limited, limit: body.limit || null, error: body.error ? body.code || true : null, followup: body.followup ?? null };
		}
		const dec = new TextDecoder();
		let buf = '';
		for await (const chunk of r.body) {
			buf += dec.decode(chunk, { stream: true });
			let k;
			while ((k = buf.indexOf('\n\n')) > -1) {
				const block = buf.slice(0, k);
				buf = buf.slice(k + 2);
				const ev = (block.match(/^event: (.+)$/m) || [])[1];
				const data = (block.match(/^data: (.+)$/m) || [])[1];
				if (!ev || !data) continue;
				const d = JSON.parse(data);
				if (ev === 'delta' && d.text) {
					if (ttft === null && d.text.trim()) ttft = Date.now() - t0;
					text += d.text;
				} else if (ev === 'replace') {
					text = d.text || '';
				} else if (ev === 'done') {
					done = d;
				}
			}
		}
		return { status: r.status, ms: Date.now() - t0, ttft, reply: text, limited: !!done.limited, limit: done.limit || null, error: done.error ? done.code || true : null, followup: done.followup ?? null };
	} catch (e) {
		return { status: 0, ms: Date.now() - t0, ttft, reply: text, error: `network: ${e.cause?.code || e.message}` };
	}
}

const results = [];
async function agent(i) {
	await sleep(rand(0, 5000));
	const n = await nonce();
	const session = randomUUID();
	const opener = openers[i % openers.length];
	const follow = [...FOLLOWUPS].sort(() => Math.random() - 0.5);
	for (let t = 0; t < TURNS; t++) {
		const q = t === 0 ? opener.q : follow[t - 1];
		const res = await turn(n, session, q);
		const row = { agent: i + 1, turn: t + 1, session, q, case: t === 0 ? opener.id : null, at: new Date().toISOString(), ...res };
		results.push(row);
		console.log(`agent ${String(i + 1).padStart(2)} turn ${t + 1}: ${res.status} first text ${res.ttft ?? '-'}ms, done ${res.ms}ms${res.limited ? ' LIMITED ' + res.limit : ''}${res.error ? ' ERROR ' + res.error : ''}`);
		if (res.limited || res.error) break;
		if (t < TURNS - 1) await sleep(rand(20000, 40000));
	}
}

const started = Date.now();
await Promise.all(Array.from({ length: AGENTS }, (_, i) => agent(i)));
writeFileSync(out, results.map((r) => JSON.stringify(r)).join('\n') + '\n');

// ---- report ---------------------------------------------------------------
const pct = (xs, p) => {
	const s = [...xs].sort((a, b) => a - b);
	return s.length ? s[Math.min(s.length - 1, Math.floor((p / 100) * s.length))] : null;
};
const ok = results.filter((r) => r.status === 200 && !r.limited && !r.error && r.reply);
const firstTurns = ok.filter((r) => r.turn === 1);
const lines = [];
lines.push(`# Load test: ${AGENTS} visitors at once`, '', `Run ${new Date(started).toISOString()}, ${Math.round((Date.now() - started) / 1000)} s, ${results.length} turns.`, '');
lines.push('## Speed', '', '| | p50 | p90 | max |', '|---|---|---|---|');
for (const [label, xs] of [['First text, all turns', ok.map((r) => r.ttft).filter((x) => x !== null)], ['Full answer, all turns', ok.map((r) => r.ms)], ['Full answer, the 30 simultaneous first questions', firstTurns.map((r) => r.ms)]]) {
	lines.push(`| ${label} | ${(pct(xs, 50) / 1000).toFixed(1)} s | ${(pct(xs, 90) / 1000).toFixed(1)} s | ${(Math.max(...xs) / 1000).toFixed(1)} s |`);
}
lines.push('', '## Reliability', '', `- Answered: ${ok.length} of ${results.length}`, `- Limited: ${results.filter((r) => r.limited).length}`, `- Errors: ${results.filter((r) => r.error || r.status !== 200).length}`, '');
const problems = [];
for (const r of ok) {
	const reasons = r.turn === 1 ? check({ ...openers.find((c) => c.id === r.case), set: 'E3' }, r, kb).reasons : [...universal(r.reply), ...inventedNumbers(r.reply, r.q, kb).map((x) => `figure not in the knowledge base: ${x}`)];
	if (reasons.length) problems.push({ r, reasons });
}
lines.push('## Quality', '', `Same checks as the eval: first questions must recommend the right category with the caveat; every answer is checked for banned wording and for figures not in the knowledge base.`, '', `- Passed: ${ok.length - problems.length} of ${ok.length}`, '');
for (const { r, reasons } of problems) lines.push(`- agent ${r.agent} turn ${r.turn} "${r.q}": ${reasons.join('; ')}`);
writeFileSync(join(here, 'load-report.md'), lines.join('\n') + '\n');
console.log('\n' + lines.join('\n'));
