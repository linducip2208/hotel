const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

const BASE_URL = 'http://127.0.0.1:8765';
const EMAIL = 'admin@demohotel.id';
const PASSWORD = 'password';
const OUTPUT_DIR = path.join(__dirname, '..', 'public', 'marketing', 'screens');

const PAGES = [
    // Public pages
    { url: '/', name: '01-landing-hero' },
    { url: '/rooms', name: '02-rooms-listing' },
    { url: '/docs', name: '03-documentation' },
    { url: '/login', name: '04-login' },

    // Panel pages (after login)
    { url: '/panel', name: '05-dashboard' },
    { url: '/panel/fo/reservations', name: '06-reservations' },
    { url: '/panel/fo/reservations/create', name: '07-reservation-create' },
    { url: '/panel/fo/folios/1', name: '08-folio' },
    { url: '/panel/hk', name: '09-housekeeping' },
    { url: '/panel/pos', name: '10-pos-orders' },
    { url: '/panel/channel/mapping', name: '11-channel-manager' },
    { url: '/panel/accounting/journal', name: '12-accounting' },
    { url: '/panel/reports/occupancy', name: '13-occupancy-report' },
    { url: '/panel/reports/cashier-shift', name: '14-cashier-report' },
    { url: '/panel/guests', name: '15-guests' },
    { url: '/panel/banquet/events', name: '16-banquet' },
    { url: '/panel/spa/appointments', name: '17-spa' },
    { url: '/panel/hr/employees', name: '18-hr' },
    { url: '/panel/settings/property', name: '19-settings' },
    { url: '/panel/pricing/calendar', name: '20-pricing' },
    { url: '/panel/rms/dashboard', name: '21-revenue-mgmt' },
    { url: '/panel/inventory', name: '22-inventory' },
    { url: '/panel/asset', name: '23-assets' },
    { url: '/panel/loyalty/members', name: '24-loyalty' },
    { url: '/panel/comm/inbox', name: '25-communication' },
];

(async () => {
    if (!fs.existsSync(OUTPUT_DIR)) {
        fs.mkdirSync(OUTPUT_DIR, { recursive: true });
    }

    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({
        viewport: { width: 1440, height: 900 },
        deviceScaleFactor: 1,
    });

    // --- Login first ---
    const page = await context.newPage();
    console.log('Logging in...');
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

    // --- Capture all pages ---
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
    console.log('\nAll screenshots captured!');
})();
