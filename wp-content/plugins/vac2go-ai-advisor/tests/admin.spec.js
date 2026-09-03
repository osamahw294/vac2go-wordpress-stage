// S9: attacker-controlled text reaches admin screens. These need wp-admin credentials.
const { test, expect } = require('@playwright/test');
const { uuid, getNonce } = require('./helpers');

const USER = process.env.VA_ADMIN_USER;
const PASS = process.env.VA_ADMIN_PASS;

test.describe('admin safety', () => {
	test.skip(!USER || !PASS, 'set VA_ADMIN_USER and VA_ADMIN_PASS to run the admin tests');
	test.skip(({ browserName }, testInfo) => testInfo.project.name === 'mobile', 'admin tests run on desktop only');

	test.beforeEach(async ({ page }) => {
		await page.goto('/wp-login.php');
		await page.locator('#user_login').fill(USER);
		await page.locator('#user_pass').fill(PASS);
		await page.locator('#wp-submit').click();
		await page.waitForURL(/wp-admin/);
	});

	test('a script payload in a question renders as text, never as HTML', async ({ page, request }) => {
		const nonce = await getNonce(request);
		const marker = 'xss' + Date.now();
		const payload = `<img src=x onerror="window.__vaXss='${marker}'"> vacuum truck question`;

		await request.post('/wp-json/vac2go/v1/chat', {
			headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
			data: { session_id: uuid(), request_id: uuid(), message: payload, elapsed_ms: 9000 },
			failOnStatusCode: false,
		});

		let fired = false;
		page.on('dialog', async (d) => { fired = true; await d.dismiss(); });

		await page.goto('/wp-admin/admin.php?page=va-advisor');

		// The literal text must be present...
		await expect(page.locator('.wp-list-table')).toContainText('onerror');
		// ...and the injected img must NOT exist as a real element.
		expect(await page.locator('.wp-list-table img[src="x"]').count()).toBe(0);
		expect(await page.evaluate(() => window.__vaXss || null)).toBeNull();
		expect(fired).toBe(false);
	});

	test('CSV export neutralizes formula cells', async ({ page, request }) => {
		const nonce = await getNonce(request);
		await request.post('/wp-json/vac2go/v1/chat', {
			headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
			data: {
				session_id: uuid(), request_id: uuid(),
				message: '=HYPERLINK("http://evil","click") vacuum truck', elapsed_ms: 9000,
			},
			failOnStatusCode: false,
		});

		await page.goto('/wp-admin/admin.php?page=va-advisor');
		const [download] = await Promise.all([
			page.waitForEvent('download'),
			page.getByRole('link', { name: /export to csv/i }).click(),
		]);
		const stream = await download.createReadStream();
		let csv = '';
		for await (const chunk of stream) { csv += chunk.toString(); }

		expect(csv).toContain('HYPERLINK');
		// Every cell containing the payload must be quote-prefixed, so no cell may
		// start a field with a bare '='.
		const bare = csv.split('\n').some((line) => /(^|,)"?=/.test(line));
		expect(bare, 'a formula cell was exported without the leading apostrophe').toBe(false);
	});

	test('settings and stats pages load for an admin', async ({ page }) => {
		await page.goto('/wp-admin/admin.php?page=va-advisor-settings');
		await expect(page.locator('h1')).toContainText(/advisor settings/i);
		await expect(page.locator('textarea[name="va_system_prompt"]')).toBeVisible();

		await page.goto('/wp-admin/admin.php?page=va-advisor-stats');
		await expect(page.locator('h1')).toContainText(/stats/i);
	});

	test('no page source exposes the API key', async ({ page }) => {
		for (const p of ['va-advisor', 'va-advisor-settings', 'va-advisor-stats']) {
			await page.goto(`/wp-admin/admin.php?page=${p}`);
			expect(await page.content(), `${p} leaked the key`).not.toMatch(/sk-ant-/);
		}
	});

	test('CSV export is refused without a valid nonce', async ({ page }) => {
		const res = await page.goto('/wp-admin/admin-post.php?action=va_export_csv&va_filter=all');
		expect(res.status()).toBeGreaterThanOrEqual(400);
	});
});
