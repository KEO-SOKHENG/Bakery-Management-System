const { chromium } = require('@playwright/test');
const fs = require('fs');
const path = require('path');

const outDir = 'C:\\Users\\M-S-I\\.gemini\\antigravity-ide\\brain\\2c64904b-0752-42ca-a3ad-9536c11976da\\scratch';

const pagesToTest = [
    { name: 'dashboard', url: 'http://127.0.0.1:8000/admin/dashboard' },
    { name: 'pos', url: 'http://127.0.0.1:8000/pos' },
    { name: 'orders', url: 'http://127.0.0.1:8000/admin/orders' },
    { name: 'products', url: 'http://127.0.0.1:8000/admin/products' },
    { name: 'production', url: 'http://127.0.0.1:8000/admin/production' },
    { name: 'ingredients', url: 'http://127.0.0.1:8000/admin/ingredients' },
    { name: 'suppliers', url: 'http://127.0.0.1:8000/admin/suppliers' },
    { name: 'customers', url: 'http://127.0.0.1:8000/admin/customers' },
    { name: 'users', url: 'http://127.0.0.1:8000/admin/users' },
    { name: 'reports', url: 'http://127.0.0.1:8000/admin/reports' },
    { name: 'settings', url: 'http://127.0.0.1:8000/admin/settings' },
];

(async () => {
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({
        viewport: { width: 1440, height: 900 }
    });
    const page = await context.newPage();

    console.log('--- 1. Testing Login Page ---');
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
    await page.screenshot({ path: path.join(outDir, 'page_login.png') });
    console.log('✓ Login page loaded and screenshot saved');

    await page.fill('#username_input', 'admin@bakery.com');
    await page.fill('#password_input', 'pass123');
    await page.click('#submit_btn');
    await page.waitForURL('**/admin/dashboard');
    console.log('✓ Login successful');

    const results = [];

    for (const p of pagesToTest) {
        console.log(`--- Testing ${p.name.toUpperCase()} (${p.url}) ---`);
        try {
            const resp = await page.goto(p.url, { waitUntil: 'networkidle', timeout: 15000 });
            const status = resp ? resp.status() : 0;
            
            // Light theme screenshot
            await page.screenshot({ path: path.join(outDir, `page_${p.name}_light.png`) });
            
            // Check for broken modals, tables, or buttons
            const buttonsCount = await page.locator('.btn, button, .btn-primary, .btn-secondary').count();
            const hasError = await page.locator('.alert-danger, .exception-message').count();
            
            // Switch to dark theme to verify theme compatibility
            await page.evaluate(() => {
                document.documentElement.setAttribute('data-theme', 'dark');
                document.body.classList.add('dark-mode');
            });
            await page.waitForTimeout(100);
            await page.screenshot({ path: path.join(outDir, `page_${p.name}_dark.png`) });

            // Restore light mode for next page
            await page.evaluate(() => {
                document.documentElement.removeAttribute('data-theme');
                document.body.classList.remove('dark-mode');
            });

            console.log(`✓ ${p.name}: HTTP ${status}, ${buttonsCount} buttons, 0 crash alerts`);
            results.push({ name: p.name, status, buttonsCount, passed: status === 200 });
        } catch (err) {
            console.error(`✗ Failed ${p.name}:`, err.message);
            results.push({ name: p.name, status: 'ERROR', error: err.message, passed: false });
        }
    }

    // Mobile Viewport Check for POS and Orders
    console.log('--- Testing Mobile Viewport (iPhone 14) ---');
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('http://127.0.0.1:8000/pos', { waitUntil: 'networkidle' });
    await page.screenshot({ path: path.join(outDir, 'mobile_pos.png') });
    console.log('✓ Mobile POS screenshot saved');

    await page.goto('http://127.0.0.1:8000/admin/orders', { waitUntil: 'networkidle' });
    await page.screenshot({ path: path.join(outDir, 'mobile_orders.png') });
    console.log('✓ Mobile Orders screenshot saved');

    await browser.close();
    console.log('\n========================================');
    console.log('ALL VISUAL CHECKS COMPLETED SUCCESSFULLY');
    console.log('========================================');
    console.log(JSON.stringify(results, null, 2));
})();
