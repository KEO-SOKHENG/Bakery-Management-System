import { chromium } from 'playwright';

(async () => {
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({ viewport: { width: 1366, height: 768 } });
    const page = await context.newPage();

    // Login
    await page.goto('http://127.0.0.1:8000/login');
    await page.fill('#username_input', 'admin@bakery.com');
    await page.fill('#password_input', 'pass123');
    await page.click('#submit_btn');
    await page.waitForURL('**/admin/dashboard');

    // Go to orders
    await page.goto('http://127.0.0.1:8000/admin/orders');
    await page.waitForLoadState('networkidle');

    // Screenshot of orders table with row delete buttons
    await page.screenshot({ path: 'C:/Users/M-S-I/.gemini/antigravity-ide/brain/bae14c9d-dcdd-414b-a697-6a4d55afe96d/scratch/easy_delete_table.png' });

    // Open purge modal
    await page.click('#btn_open_purge_modal');
    await page.waitForTimeout(500);

    // Screenshot of the new easy modal
    await page.screenshot({ path: 'C:/Users/M-S-I/.gemini/antigravity-ide/brain/bae14c9d-dcdd-414b-a697-6a4d55afe96d/scratch/easy_delete_modal_open.png' });

    // Click 'Older than 30d' preset
    await page.click('#btn_scope_30');
    await page.waitForTimeout(400);
    await page.screenshot({ path: 'C:/Users/M-S-I/.gemini/antigravity-ide/brain/bae14c9d-dcdd-414b-a697-6a4d55afe96d/scratch/easy_delete_modal_preset_30.png' });

    // Also test on mobile viewport (375x667)
    await page.setViewportSize({ width: 375, height: 667 });
    await page.waitForTimeout(300);
    await page.screenshot({ path: 'C:/Users/M-S-I/.gemini/antigravity-ide/brain/bae14c9d-dcdd-414b-a697-6a4d55afe96d/scratch/easy_delete_modal_mobile.png' });

    console.log('Screenshots taken successfully!');
    await browser.close();
})();
