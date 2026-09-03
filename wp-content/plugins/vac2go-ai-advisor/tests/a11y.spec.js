// S10: accessibility contract.
const { test, expect } = require('@playwright/test');
const { openWidget } = require('./helpers');

test.describe('accessibility', () => {
	test('launcher exposes aria-label and aria-expanded state', async ({ page }) => {
		const panel = await openWidget(page);
		const launcher = page.locator('.va-launcher');
		await expect(launcher).toHaveAttribute('aria-label', /equipment advisor/i);
		await expect(launcher).toHaveAttribute('aria-expanded', 'true');

		await page.locator('.va-close').click();
		await expect(panel).toBeHidden();
		await expect(launcher).toHaveAttribute('aria-expanded', 'false');
	});

	test('panel is a modal dialog with a live message region', async ({ page }) => {
		const panel = await openWidget(page);
		await expect(panel).toHaveAttribute('role', 'dialog');
		await expect(panel).toHaveAttribute('aria-modal', 'true');
		await expect(page.locator('.va-messages')).toHaveAttribute('aria-live', 'polite');
	});

	test('Escape closes the panel and focus returns to the launcher', async ({ page }) => {
		const panel = await openWidget(page);
		await page.locator('.va-input').focus();
		await page.keyboard.press('Escape');
		await expect(panel).toBeHidden();
		const focused = await page.evaluate(() => document.activeElement && document.activeElement.className);
		expect(focused).toContain('va-launcher');
	});

	test('Tab is trapped inside the open panel', async ({ page }) => {
		await openWidget(page);
		for (let i = 0; i < 12; i++) {
			await page.keyboard.press('Tab');
			const inside = await page.evaluate(() => {
				const p = document.querySelector('.va-panel');
				return !!(p && document.activeElement && p.contains(document.activeElement));
			});
			expect(inside, `focus escaped the panel on Tab #${i + 1}`).toBe(true);
		}
	});

	test('the honeypot is hidden from assistive tech and the tab order', async ({ page }) => {
		await openWidget(page);
		const hp = page.locator('.va-hp');
		await expect(hp).toHaveAttribute('tabindex', '-1');
		await expect(hp).toHaveAttribute('aria-hidden', 'true');
	});
});
