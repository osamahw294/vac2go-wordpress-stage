// Run the eval cases against the staging advisor and record the replies.
//
//   node phase-2/eval/run.mjs [--sets E3,E4] [--limit N] [--out results.jsonl]
//
// Each case is a new conversation on the buffered /chat endpoint (same gates, filter
// and judge as streaming). Requests are paced under the per-IP limit (6 a minute) so
// the run never trips it; if one is limited anyway, it waits the server's own
// retry_after and asks again. Resumable: cases already in the output file are skipped.
import { readFileSync, appendFileSync, existsSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { randomUUID } from 'node:crypto';

const here = dirname(fileURLToPath(import.meta.url));
const BASE = process.env.VA_BASE_URL || 'https://b5205c85ce.nxcli.io';
const API = `${BASE}/wp-json/vac2go/v1`;
const arg = (name, def) => {
	const i = process.argv.indexOf(`--${name}`);
	return i > -1 ? process.argv[i + 1] : def;
};
const sets = arg('sets', 'E1,E2,E3,E4,E5').split(',');
const limit = Number(arg('limit', '100000'));
const out = join(here, arg('out', 'results.jsonl'));
const GAP_MS = 12500; // 4.8 requests a minute, under the 6-a-minute per-IP limit
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

const done = new Set(existsSync(out) ? readFileSync(out, 'utf8').trim().split('\n').filter(Boolean).map((l) => JSON.parse(l).id) : []);
const cases = JSON.parse(readFileSync(join(here, 'cases.json'), 'utf8')).filter((c) => sets.includes(c.set) && !done.has(c.id)).slice(0, limit);
console.log(`${cases.length} to run (${done.size} already done) → ${out}`);

let nonce = null;
async function freshNonce() {
	nonce = (await (await fetch(`${API}/nonce?_=${Date.now()}`, { cache: 'no-store' })).json()).nonce;
}

async function ask(c) {
	// Multi-turn case: earlier turns share the session (paced like everything else);
	// only the last reply is graded.
	if (c.turns) {
		const session = randomUUID();
		const replies = [];
		let last = null;
		for (const [k, q] of c.turns.entries()) {
			if (k > 0) await sleep(GAP_MS);
			last = await askOnce({ ...c, q }, session);
			replies.push(last.reply);
		}
		return { ...last, replies };
	}
	return askOnce(c, randomUUID());
}

async function askOnce(c, session) {
	for (let attempt = 1; attempt <= 4; attempt++) {
		const t0 = Date.now();
		const r = await fetch(`${API}/chat`, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
			body: JSON.stringify({ session_id: session, request_id: randomUUID(), message: c.q, website: '', elapsed_ms: 15000 }),
		});
		if (r.status === 403) { await freshNonce(); continue; }
		const body = await r.json().catch(() => ({}));
		if (body.limited) {
			const wait = (Number(body.retry_after) || 60) + 3;
			console.log(`  limited (${body.limit}); waiting ${wait}s`);
			await sleep(wait * 1000);
			continue;
		}
		return { id: c.id, set: c.set, session, status: r.status, ms: Date.now() - t0, reply: body.reply ?? null, followup: body.followup ?? null, filtered: !!body.filtered, error: body.error ? body.code || true : null, at: new Date().toISOString() };
	}
	return { id: c.id, set: c.set, session, status: 0, reply: null, error: 'gave up after 4 attempts', at: new Date().toISOString() };
}

await freshNonce();
let i = 0;
for (const c of cases) {
	const started = Date.now();
	const res = await ask(c);
	appendFileSync(out, JSON.stringify(res) + '\n');
	i++;
	console.log(`[${i}/${cases.length}] ${c.id} ${res.status} ${res.ms ?? '-'}ms${res.filtered ? ' FILTERED' : ''}${res.error ? ' ERROR ' + res.error : ''}`);
	await sleep(Math.max(0, GAP_MS - (Date.now() - started)));
}
console.log('done');
