// S3: the launcher must render on an untouched page, with ZERO simulated interaction.
// hw-delay-js-front rewrites every script tag to type="text/hwdelay" and only runs
// them after the visitor's first interaction, so this is the test that actually
// proves the split-bootstrap fix works rather than the earlier "simulate a click" one.
const { test, expect } = require('@playwright/test');
const { PATH } = require('./helpers');

test.describe('cold load, no interaction', () => {
	test('bootstrap launcher is visible and clickable with no prior interaction', async ({ page }) => {
		await page.goto(PATH, { waitUntil: 'load' });

		const boot = page.locator('#va-boot-launcher');
		await expect(boot).toBeVisible({ timeout: 15000 });
		await expect(boot).toBeEnabled();

		// It must be on screen, not parked off-viewport.
		const box = await boot.boundingBox();
		expect(box).not.toBeNull();
		expect(box.width).toBeGreaterThan(0);
		expect(box.height).toBeGreaterThan(0);

		const vp = page.viewportSize();
		expect(box.x).toBeGreaterThanOrEqual(0);
		expect(box.x + box.width).toBeLessThanOrEqual(vp.width + 1);
		expect(box.y + box.height).toBeLessThanOrEqual(vp.height + 1);
	});

	test('bootstrap script is exempt from the delay rewrite', async ({ page }) => {
		await page.goto(PATH, { waitUntil: 'load' });
		const type = await page.locator('#hw-delay-loader-va-boot').getAttribute('type');
		// Anything rewritten would carry type="text/hwdelay".
		expect(type).not.toBe('text/hwdelay');
	});

	test('heavy bundle is NOT loaded until the launcher is clicked', async ({ page }) => {
		const widgetRequests = [];
		page.on('request', (r) => {
			if (r.url().includes('/vac2go-ai-advisor/assets/widget.js')) { widgetRequests.push(r.url()); }
		});

		await page.goto(PATH, { waitUntil: 'load' });
		await page.waitForTimeout(2500);
		expect(widgetRequests, 'widget.js must stay off the critical path').toHaveLength(0);

		await page.locator('#va-boot-launcher').click();
		await page.waitForTimeout(3000);
		expect(widgetRequests.length, 'widget.js should load on click').toBeGreaterThan(0);
	});

	test('full widget replaces the bootstrap launcher and opens', async ({ page }) => {
		await page.goto(PATH, { waitUntil: 'load' });
		await page.locator('#va-boot-launcher').click();
		await expect(page.locator('.va-panel')).toBeVisible({ timeout: 20000 });
		await expect(page.locator('#va-boot-launcher')).toHaveCount(0);
	});
});
