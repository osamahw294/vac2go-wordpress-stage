/**
 * One-time admin login capture, for sites that enforce two-factor.
 *
 * Playwright cannot generate a TOTP code, so the admin suite cannot log itself in.
 * This script drives the login up to the 2FA prompt, waits for a human to drop the
 * current code into tests/.auth/otp.txt, then saves the authenticated browser state
 * to tests/.auth/admin.json. admin.spec.js reuses that state and skips logging in.
 *
 *   VA_BASE_URL=https://example.com VA_ADMIN_USER=u VA_ADMIN_PASS=p \
 *     node tests/auth-setup.js
 *
 * Then, in another terminal, while it waits:
 *   echo 123456 > tests/.auth/otp.txt
 *
 * The saved session is a real admin login. tests/.auth/ is gitignored; delete it when
 * you are done, and never commit it.
 */
const { chromium } = require('@playwright/test');
const fs = require('fs');
const path = require('path');

const BASE = process.env.VA_BASE_URL;
const USER = process.env.VA_ADMIN_USER;
const PASS = process.env.VA_ADMIN_PASS;

if (!BASE || !USER || !PASS) {
	console.error('Set VA_BASE_URL, VA_ADMIN_USER and VA_ADMIN_PASS.');
	process.exit(2);
}

const AUTH_DIR = path.join(__dirname, '.auth');
const OTP_FILE = path.join(AUTH_DIR, 'otp.txt');
const STATE_FILE = path.join(AUTH_DIR, 'admin.json');

fs.mkdirSync(AUTH_DIR, { recursive: true });
if (fs.existsSync(OTP_FILE)) { fs.unlinkSync(OTP_FILE); }

(async () => {
	const browser = await chromium.launch();
	const context = await browser.newContext({ ignoreHTTPSErrors: true });
	const page = await context.newPage();

	await page.goto(BASE + '/wp-login.php', { waitUntil: 'load' });

	// Solid Security's passwordless flow hides the password fields behind a link.
	const usePassword = page.locator('.itsec-pwls-login-fallback__link', { hasText: /password/i }).first();
	if (await usePassword.isVisible().catch(() => false)) {
		await usePassword.click();
	}

	await page.locator('#user_login').waitFor({ state: 'visible', timeout: 20000 });
	await page.locator('#user_login').fill(USER);
	await page.locator('#user_pass').fill(PASS);
	await page.locator('#wp-submit').click();
	await page.waitForTimeout(3000);

	if (/wp-admin/.test(page.url())) {
		await context.storageState({ path: STATE_FILE });
		console.log('LOGGED IN without 2FA. State saved to', STATE_FILE);
		await browser.close();
		return;
	}

	const codeField = page
		.locator('input#authcode, input[name="authcode"], input[autocomplete="one-time-code"], input[name="authentication_code"]')
		.first();

	if (!(await codeField.isVisible().catch(() => false))) {
		console.error('Neither wp-admin nor a 2FA prompt. Current URL:', page.url());
		console.error((await page.locator('body').innerText().catch(() => '')).slice(0, 400));
		await browser.close();
		process.exit(1);
	}

	console.log('WAITING_FOR_OTP');
	console.log('Write the current 6-digit code to: ' + OTP_FILE);

	const deadline = Date.now() + 5 * 60 * 1000;
	let code = null;
	while (Date.now() < deadline) {
		if (fs.existsSync(OTP_FILE)) {
			const raw = fs.readFileSync(OTP_FILE, 'utf8').trim();
			if (/^\d{6,8}$/.test(raw)) { code = raw; break; }
		}
		await new Promise((r) => setTimeout(r, 500));
	}

	if (!code) {
		console.error('No OTP supplied within 5 minutes.');
		await browser.close();
		process.exit(1);
	}
	fs.unlinkSync(OTP_FILE);

	await codeField.fill(code);
	await page.locator('#submit, #wp-submit, input[type="submit"], button[type="submit"]').first().click();
	await page.waitForTimeout(4000);

	if (!/wp-admin/.test(page.url())) {
		console.error('2FA rejected or expired. URL:', page.url());
		console.error((await page.locator('body').innerText().catch(() => '')).slice(0, 400));
		await browser.close();
		process.exit(1);
	}

	await context.storageState({ path: STATE_FILE });
	console.log('AUTH_OK state saved to', STATE_FILE);
	await browser.close();
})();
