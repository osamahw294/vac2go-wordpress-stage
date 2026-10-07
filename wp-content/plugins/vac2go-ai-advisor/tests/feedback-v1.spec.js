// V1 feedback (2026-10-06): queueing, rate-limit lockout, when and how the rep
// follow-up card appears, and the always-visible contact CTAs.
//
// The chat endpoints are mocked with page.route, so these run against any site
// without spending tokens and without needing to trip a real rate limit. The
// server-side halves (when a turn is tagged for follow-up, the wait time, the
// escalating lock) are covered by `php tests/signals-fixtures.php`.
const { test, expect } = require('@playwright/test');
const { openWidget, sendMessage } = require('./helpers');

function sse(events) {
	return events.map(([ev, data]) => `event: ${ev}\ndata: ${JSON.stringify(data)}\n\n`).join('') + 'event: close\ndata: {}\n\n';
}

// Answer every streamed turn with `reply(message, n)`, after `delayMs`.
async function mockChat(page, reply, delayMs = 0) {
	const sent = [];
	await page.route('**/vac2go/v1/chat/stream', async (route) => {
		const body = JSON.parse(route.request().postData() || '{}');
		sent.push(body.message);
		const r = reply(body.message, sent.length);
		if (delayMs) { await new Promise((res) => setTimeout(res, delayMs)); }
		await route.fulfill({
			status: 200,
			headers: { 'Content-Type': 'text/event-stream' },
			body: sse([['delta', { text: r.text }], ['done', r.done || {}]]),
		});
	});
	return sent;
}

const idle = (page) =>
	page.waitForFunction(() => !document.querySelector('.va-typing') && !document.querySelector('.va-new').disabled);

test.describe('V1 feedback', () => {
	test('constant CTAs: call button in the header, contact link in the footer', async ({ page }) => {
		await openWidget(page);
		await expect(page.locator('.va-header .va-call')).toHaveAttribute('href', /^tel:\+?\d{10,}/);
		await expect(page.locator('.va-disclosure .va-contact-link')).toHaveAttribute('href', /\/contact\/?$/);
	});

	test('messages sent while the advisor is answering are queued, then sent together', async ({ page }) => {
		const sent = await mockChat(page, (m, n) => ({ text: `Answer ${n}` }), 2500);
		await openWidget(page);
		await sendMessage(page, 'first');
		await sendMessage(page, 'second');
		await sendMessage(page, 'third');
		await expect(page.locator('.va-msg-user.va-queued')).toHaveCount(2);
		await expect(page.locator('.va-msg-assistant .va-bubble', { hasText: 'Answer 2' })).toBeVisible({ timeout: 15000 });
		await idle(page);
		expect(sent).toEqual(['first', 'second\n\nthird']);
		await expect(page.locator('.va-queued')).toHaveCount(0);
	});

	test('follow-up card waits for a signal, is compact, and sits under the answer', async ({ page }) => {
		await mockChat(page, (m, n) =>
			n === 1
				? { text: 'What are you vacuuming, and roughly how much?' }
				: { text: 'Industrial Vacuum. This is a high-level recommendation. Confirm specifics with a Vac2Go rep.', done: { followup: 'recommendation' } }
		);
		await openWidget(page);
		await sendMessage(page, 'need a truck');
		await idle(page);
		await expect(page.locator('.va-contact-card')).toHaveCount(0);

		await sendMessage(page, 'fly ash from a hopper');
		const card = page.locator('.va-messages > .va-contact-card');
		await expect(card).toBeVisible();
		const box = await card.boundingBox();
		expect(box.height).toBeLessThan(120);
		expect(await card.evaluate((el) => el.previousElementSibling.classList.contains('va-msg-assistant'))).toBe(true);
	});

	test('a per-IP limit locks the input with a countdown, then lifts', async ({ page }) => {
		await mockChat(page, () => ({
			text: "You've sent a lot of messages in a short time, so I've paused this chat.",
			done: { early: true, limited: true, limit: 'ip', retry_after: 3 },
		}));
		await openWidget(page);
		await sendMessage(page, 'hello there');
		await expect(page.locator('.va-input')).toBeDisabled();
		await expect(page.locator('.va-lock')).toContainText(/send again in \d:\d\d/);
		await expect(page.locator('.va-input')).toBeEnabled({ timeout: 8000 });
		await expect(page.locator('.va-lock')).toBeHidden();
	});

	test('a full conversation offers a new chat', async ({ page }) => {
		await mockChat(page, () => ({
			text: "We've reached the length limit for this conversation.",
			done: { early: true, limited: true, limit: 'session', retry_after: 0 },
		}));
		await openWidget(page);
		await sendMessage(page, 'one more');
		await expect(page.locator('.va-lock-new')).toBeVisible();
		await expect(page.locator('.va-input')).toBeDisabled();
		await page.locator('.va-lock-new').click();
		await expect(page.locator('.va-input')).toBeEnabled();
	});
});
