// S9: attacker-controlled text reaches admin screens. These need wp-admin credentials.
const { test, expect } = require('@playwright/test');
const fs = require('fs');
const path = require('path');

const USER = process.env.VA_ADMIN_USER;
const PASS = process.env.VA_ADMIN_PASS;

// A session captured by tests/auth-setup.js, for sites enforcing two-factor.
const STATE_FILE = path.join(__dirname, '.auth', 'admin.json');
const HAVE_STATE = fs.existsSync(STATE_FILE);

if (HAVE_STATE) {
	test.use({ storageState: STATE_FILE });
}

test.describe('admin safety', () => {
	test.skip(!HAVE_STATE && (!USER || !PASS), 'set VA_ADMIN_USER and VA_ADMIN_PASS, or run tests/auth-setup.js first');
	test.beforeEach(async ({ page }) => {
		// testInfo is not available to a describe-level skip callback, so gate here.
		test.skip(test.info().project.name === 'mobile', 'admin tests run on desktop only');

		// A captured session already carries the 2FA-completed cookies.
		if (HAVE_STATE) {
			await page.goto('/wp-admin/');
			await expect(page.locator('#wpadminbar')).toBeVisible({ timeout: 20000 });
			return;
		}

		await page.goto('/wp-login.php');

		// Solid Security's passwordless login hides the username/password fields
		// behind a magic-link flow. Switch to the password form when that link exists,
		// so the suite works on sites with or without the feature enabled.
		const usePassword = page.locator('.itsec-pwls-login-fallback__link', { hasText: /password/i }).first();
		if (await usePassword.isVisible().catch(() => false)) {
			await usePassword.click();
		}

		await page.locator('#user_login').waitFor({ state: 'visible', timeout: 15000 });
		await page.locator('#user_login').fill(USER);
		await page.locator('#user_pass').fill(PASS);
		await page.locator('#wp-submit').click();

		// A site enforcing two-factor cannot be driven from here. Skip with a clear
		// reason rather than timing out on a URL that will never arrive.
		await page.waitForTimeout(3000);
		const needs2fa = await page
			.getByText(/authentication code|backup authentication|two.factor/i)
			.first()
			.isVisible()
			.catch(() => false);
		test.skip(
			needs2fa,
			'Two-factor is enforced for this account, so the admin UI cannot be automated. ' +
			'Verify CSV escaping with tests/csv-injection check via WP-CLI, and the XSS ' +
			'rendering manually in the Review Queue. See readme.txt, Admin verification.'
		);

		await page.waitForURL(/wp-admin/, { timeout: 30000 });
	});

	// The two payload rows are seeded straight into the log table by
	// tests/seed-payloads.php (run via wp eval-file). Going through /chat would be
	// rate-limited, and a limited request is never logged, so there would be nothing
	// to render. What S9 actually tests is how the admin UI renders STORED attacker
	// text, not how that text arrived.
	test('a script payload in a question renders as text, never as HTML', async ({ page }) => {
		let fired = false;
		page.on('dialog', async (d) => { fired = true; await d.dismiss(); });

		await page.goto('/wp-admin/admin.php?page=va-advisor');

		// The literal text must be present...
		await expect(
			page.locator('.wp-list-table'),
			'no seeded XSS row found: run tests/seed-payloads.php on the server first'
		).toContainText('onerror');
		// ...and the injected img must NOT exist as a real element.
		expect(await page.locator('.wp-list-table img[src="x"]').count()).toBe(0);
		expect(await page.evaluate(() => window.__vaXss || null)).toBeNull();
		expect(fired).toBe(false);
	});

	test('CSV export neutralizes formula cells', async ({ page }) => {
		await page.goto('/wp-admin/admin.php?page=va-advisor');
		const [download] = await Promise.all([
			page.waitForEvent('download'),
			page.getByRole('link', { name: /export to csv/i }).click(),
		]);
		const stream = await download.createReadStream();
		let csv = '';
		for await (const chunk of stream) { csv += chunk.toString(); }

		expect(csv, 'no seeded formula row found: run tests/seed-payloads.php first').toContain('HYPERLINK');
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
