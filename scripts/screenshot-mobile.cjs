const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

const BASE_URL = 'http://127.0.0.1:8765';
const EMAIL = 'admin@demohotel.id';
const PASSWORD = 'password';
const OUTPUT_DIR = path.join(__dirname, '..', 'public', 'marketing', 'screens-mobile');

const PAGES = [
    { url: '/', name: 'mobile-01-landing' },
    { url: '/panel', name: 'mobile-02-dashboard' },
    { url: '/panel/fo/reservations', name: 'mobile-03-reservations' },
    { url: '/panel/hk', name: 'mobile-04-housekeeping' },
    { url: '/panel/pos', name: 'mobile-05-pos' },
];

(async () => {
    if (!fs.existsSync(OUTPUT_DIR)) {
        fs.mkdirSync(OUTPUT_DIR, { recursive: true });
    }

    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({
        viewport: { width: 414, height: 896 },
        deviceScaleFactor: 2,
        isMobile: true,
    });

    // --- Login first ---
    const page = await context.newPage();
    console.log('Logging in (mobile viewport)...');
    await page.goto(BASE_URL + '/login', { waitUntil: 'networkidle', timeout: 15000 });
    await page.waitForTimeout(1000);

    const emailInput = page.locator('input[type="email"]').first();
    await emailInput.fill('');
    await emailInput.type(EMAIL, { delay: 50 });

    const passwordInput = page.locator('input[type="password"]').first();
    await passwordInput.fill('');
    await passwordInput.type(PASSWORD, { delay: 50 });

    await page.waitForTimeout(500);
    await page.locator('button[type="submit"]').first().click();
    await page.waitForURL('**/panel**', { timeout: 10000 }).catch(() => {
        console.log('Login may have redirected elsewhere, continuing...');
    });
    await page.waitForTimeout(2000);

    // --- Capture mobile pages ---
    for (const { url, name } of PAGES) {
        try {
            console.log(`Capturing: ${name} (${url})`);
            await page.goto(BASE_URL + url, { waitUntil: 'networkidle', timeout: 15000 });
            await page.waitForTimeout(1500);
            await page.screenshot({
                path: path.join(OUTPUT_DIR, `${name}.png`),
                fullPage: false,
            });
            console.log(`  OK ${name}.png`);
        } catch (err) {
            console.log(`  FAILED ${name}: ${err.message}`);
        }
    }

    await browser.close();
    console.log('\nAll mobile screenshots captured!');
})();
