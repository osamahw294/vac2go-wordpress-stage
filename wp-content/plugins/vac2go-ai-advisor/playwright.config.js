// Playwright config for the Vac2Go AI Equipment Advisor.
//
//   VA_BASE_URL   site under test            (required, e.g. https://staging.vac2go.com)
//   VA_TEST_PATH  page the widget loads on   (default '/')
//   VA_TEST_LIVE  '1' enables assertions that spend real model tokens
//   VA_ADMIN_USER / VA_ADMIN_PASS            enable the wp-admin tests
const { defineConfig, devices } = require('@playwright/test');

const baseURL = process.env.VA_BASE_URL;
if (!baseURL) {
	throw new Error(
		'VA_BASE_URL is not set. Run e.g.\n' +
		'  VA_BASE_URL=https://staging.example.com npx playwright test'
	);
}

module.exports = defineConfig({
	testDir: './tests',
	timeout: 90 * 1000,
	expect: { timeout: 15 * 1000 },
	fullyParallel: false,
	workers: 1,
	retries: 0,
	reporter: [['list'], ['html', { open: 'never' }]],
	use: {
		baseURL,
		trace: 'retain-on-failure',
		screenshot: 'only-on-failure',
		ignoreHTTPSErrors: true,
	},
	projects: [
		{ name: 'desktop', use: { ...devices['Desktop Chrome'], viewport: { width: 1440, height: 900 } } },
		{ name: 'mobile', use: { ...devices['Pixel 7'] } },
	],
});
