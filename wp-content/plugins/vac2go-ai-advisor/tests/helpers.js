// Shared helpers.
const PATH = process.env.VA_TEST_PATH || '/';
const LIVE = process.env.VA_TEST_LIVE === '1';

function uuid() {
	return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
		const r = (Math.random() * 16) | 0;
		const v = c === 'x' ? r : (r & 0x3) | 0x8;
		return v.toString(16);
	});
}

async function getNonce(request) {
	const res = await request.get('/wp-json/vac2go/v1/nonce');
	const body = await res.json();
	return body.nonce;
}

// Open the widget panel the way a real visitor does: click the bootstrap launcher,
// which loads the full bundle, then click the real launcher it renders.
async function openWidget(page) {
	await page.goto(PATH, { waitUntil: 'domcontentloaded' });
	await page.locator('#va-boot-launcher').click();
	const launcher = page.locator('.va-launcher');
	await launcher.waitFor({ state: 'attached', timeout: 20000 });
	const panel = page.locator('.va-panel');
	if (await panel.isHidden()) {
		await launcher.click();
	}
	await panel.waitFor({ state: 'visible' });
	return panel;
}

async function sendMessage(page, text) {
	await page.locator('.va-input').fill(text);
	await page.locator('.va-send').click();
}

// The assistant bubbles, excluding the opening greeting.
function replies(page) {
	return page.locator('.va-msg-assistant:not(.va-typing) .va-bubble');
}

async function waitForReply(page, previousCount, timeout = 60000) {
	await page
		.locator('.va-msg-assistant:not(.va-typing)')
		.nth(previousCount)
		.waitFor({ state: 'visible', timeout });
	// Let a streamed answer settle.
	await page.waitForTimeout(1500);
	return (await replies(page).last().innerText()).trim();
}

module.exports = { PATH, LIVE, uuid, getNonce, openWidget, sendMessage, replies, waitForReply };
