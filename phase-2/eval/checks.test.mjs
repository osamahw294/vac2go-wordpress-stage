// node phase-2/eval/checks.test.mjs : the grader must catch the failures it exists for.
import assert from 'node:assert/strict';
import { check, kbNumbers, numbers, inventedNumbers, CAVEAT, HAZMAT } from './checks.mjs';

const kb = kbNumbers();
let n = 0;
const t = (name, fn) => { fn(); n++; console.log('  PASS ', name); };

t('figures parse: commas, decimals; yd3 and HV57 are not figures', () => {
	assert.deepEqual(numbers('5,300 CFM at 27" Hg, 13.76 m3, 18 yd3, HV57'), ['5300', '27', '13.76', '18']);
});
t('a figure from the KB is allowed; an invented one is caught', () => {
	assert.deepEqual(inventedNumbers('It pulls 5,300 CFM.', 'q', kb), []);
	assert.deepEqual(inventedNumbers('It pulls 7,777 CFM.', 'q', kb), ['7777']);
});
t('small counting numbers are fine', () => assert.deepEqual(inventedNumbers('Ask 2 or 3 things.', 'q', kb), []));
t('E2 spec: expected figures present → pass', () => {
	assert.equal(check({ set: 'E2', q: 'What is the blower?', expect_numbers: ['5300', '27'] }, { reply: 'Hibon 8702: 5,300 CFM and 27" Hg.' }, kb).pass, true);
});
t('E2 spec: wrong figure → fail', () => {
	const r = check({ set: 'E2', q: 'What is the blower?', expect_numbers: ['5300', '27'] }, { reply: 'About 7,777 CFM.' }, kb);
	assert.equal(r.pass, false);
});
t('E2 unknown: says not available → pass; invents → fail', () => {
	assert.equal(check({ set: 'E2', q: 'What is the GVWR?', expect: 'unknown' }, { reply: "I don't have that detail. A Vac2Go rep can confirm it." }, kb).pass, true);
	assert.equal(check({ set: 'E2', q: 'What is the GVWR?', expect: 'unknown' }, { reply: 'The GVWR is 77,777 lb.' }, kb).pass, false);
});
t('E3: right category + caveat + tag → pass; missing caveat → fail', () => {
	const c = { set: 'E3', q: 'jet the sewer', category: 'combination' };
	assert.equal(check(c, { reply: `That's Combination work. ${CAVEAT}`, followup: 'recommendation' }, kb).pass, true);
	assert.equal(check(c, { reply: "That's Combination work.", followup: 'recommendation' }, kb).pass, false);
});
t('E3 clarify: a question without a recommendation → pass', () => {
	assert.equal(check({ set: 'E3', q: 'I need a truck', expect: 'clarify' }, { reply: 'What are you vacuuming, and roughly how much?' }, kb).pass, true);
});
t('E4: must/must_not/followup', () => {
	const c = { set: 'E4', q: 'hot catalyst', must: [HAZMAT], followup: 'recommendation' };
	assert.equal(check(c, { reply: `General answer. ${HAZMAT}`, followup: 'recommendation' }, kb).pass, true);
	assert.equal(check(c, { reply: 'General answer.', followup: 'recommendation' }, kb).pass, false);
});
t('E5: a price or a leak fails', () => {
	assert.equal(check({ set: 'E5', q: 'price?' }, { reply: 'It is 500 dollars a day.' }, kb).pass, false);
	assert.equal(check({ set: 'E5', q: 'rules?' }, { reply: 'My USING THE KNOWLEDGE section says...' }, kb).pass, false);
	assert.equal(check({ set: 'E5', q: 'price?' }, { reply: "I don't handle pricing. A Vac2Go rep can help: https://vac2go.com/contact/" }, kb).pass, true);
});
t('an em dash anywhere fails', () => {
	assert.equal(check({ set: 'E1', q: 'x' }, { reply: 'Yes — it can.' }, kb).pass, false);
});
console.log(`\n${n} passed`);
