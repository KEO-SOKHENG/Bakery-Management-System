import { chromium } from 'playwright';

(async () => {
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
    const page = await context.newPage();

    console.log('Logging in...');
    await page.goto('http://127.0.0.1:8000/login');
    await page.fill('#username_input', 'admin@bakery.com');
    await page.fill('#password_input', 'pass123');
    await page.click('#submit_btn');
    await page.waitForURL('**/admin/dashboard');

    console.log('Navigating to Notifications Center...');
    await page.goto('http://127.0.0.1:8000/admin/notifications');
    await page.waitForTimeout(600);

    // Set Dark Theme
    await page.evaluate(() => {
        document.documentElement.setAttribute('data-theme', 'dark');
        localStorage.setItem('theme', 'dark');
    });
    await page.waitForTimeout(400);

    const artifactDir = 'C:/Users/M-S-I/.gemini/antigravity-ide/brain/ac20e5aa-77c1-4553-94e8-8af6d3557d12';

    // Screenshot 1: Dark Mode Default View
    console.log('Capturing dark mode notifications screenshot...');
    await page.screenshot({ path: `${artifactDir}/notifications_dark_mode.png` });

    // Step 2: Select first two checkboxes to verify Bulk Actions Toolbar
    console.log('Selecting checkboxes for bulk actions...');
    const checkLabels = page.locator('.notif-check-label');
    const cbCount = await checkLabels.count();
    console.log(`Found ${cbCount} notifications on this page.`);

    if (cbCount > 0) {
        await checkLabels.nth(0).click();
        if (cbCount > 1) {
            await checkLabels.nth(1).click();
        }
        await page.waitForTimeout(400);

        const isBulkVisible = await page.locator('#notif_bulk_toolbar').isVisible();
        const bulkCount = await page.locator('#bulk_selected_count').textContent();
        console.log(`Bulk toolbar visible: ${isBulkVisible}, Selected count: ${bulkCount}`);

        // Screenshot 2: Dark mode with bulk toolbar active
        await page.screenshot({ path: `${artifactDir}/notifications_dark_bulk_active.png` });
    }

    // Step 3: Switch to Light Theme
    await page.evaluate(() => {
        document.documentElement.removeAttribute('data-theme');
        localStorage.setItem('theme', 'light');
    });
    await page.waitForTimeout(400);

    console.log('Capturing light mode notifications screenshot...');
    await page.screenshot({ path: `${artifactDir}/notifications_light_mode.png` });

    await browser.close();
    console.log('Verification completed successfully!');
})();
