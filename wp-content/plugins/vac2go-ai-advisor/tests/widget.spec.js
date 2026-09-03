// Widget presentation contract: layout, disclosure, and the em-dash backstop in the UI.
const { test, expect } = require('@playwright/test');
const { openWidget } = require('./helpers');

test.describe('widget presentation', () => {
	test('exactly one AI disclosure is shown', async ({ page }) => {
		await openWidget(page);
		await expect(page.locator('.va-disclosure')).toHaveCount(1);
		await expect(page.locator('.va-disclosure')).toContainText(/automated/i);
	});

	test('the panel fits inside the viewport at this breakpoint', async ({ page }) => {
		const panel = await openWidget(page);
		const box = await panel.boundingBox();
		const vp = page.viewportSize();

		expect(box).not.toBeNull();
		expect(box.width).toBeLessThanOrEqual(vp.width + 1);
		expect(box.height).toBeLessThanOrEqual(vp.height + 1);
		expect(box.x).toBeGreaterThanOrEqual(-1);
		expect(box.y).toBeGreaterThanOrEqual(-1);
	});

	test('the launcher uses the brand red', async ({ page }) => {
		await page.goto(process.env.VA_TEST_PATH || '/', { waitUntil: 'load' });
		const bg = await page.locator('#va-boot-launcher').evaluate((el) => getComputedStyle(el).backgroundColor);
		expect(bg).toBe('rgb(224, 31, 48)'); // #e01f30
	});

	test('the greeting contains no em dash', async ({ page }) => {
		await openWidget(page);
		const text = await page.locator('.va-messages').innerText();
		expect(text).not.toContain('—');
	});

	test('below 480px the panel goes full width', async ({ page }, testInfo) => {
		test.skip(testInfo.project.name !== 'mobile', 'breakpoint check runs on the mobile project');
		await page.setViewportSize({ width: 400, height: 780 });
		const panel = await openWidget(page);
		const box = await panel.boundingBox();
		expect(box.width).toBeGreaterThan(400 * 0.9);
	});
});
