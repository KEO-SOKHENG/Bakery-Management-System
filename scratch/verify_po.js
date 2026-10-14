import { chromium } from 'playwright';

(async () => {
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
    const page = await context.newPage();

    // Login as Admin
    await page.goto('http://127.0.0.1:8000/login');
    await page.fill('#username_input', 'admin@bakery.com');
    await page.fill('#password_input', 'pass123');
    await page.click('#submit_btn');
    await page.waitForURL('**/admin/dashboard');

    // Go to Purchase Orders
    await page.goto('http://127.0.0.1:8000/admin/purchase-orders');
    await page.evaluate(() => {
        document.documentElement.setAttribute('data-theme', 'dark');
        localStorage.setItem('bakery_theme', 'dark');
        localStorage.setItem('theme', 'dark');
    });
    await page.waitForTimeout(600);

    const outPath = 'C:/Users/M-S-I/.gemini/antigravity-ide/brain/ac20e5aa-77c1-4553-94e8-8af6d3557d12/verified_purchase_orders_dark.png';
    await page.screenshot({ path: outPath, clip: { x: 260, y: 0, width: 1180, height: 420 } });
    console.log('Saved screenshot to:', outPath);

    await browser.close();
})();
