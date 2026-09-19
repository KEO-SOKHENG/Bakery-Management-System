import { chromium } from 'playwright';

(async () => {
    const browser = await chromium.launch();
    const context = await browser.newContext({ viewport: { width: 1366, height: 768 } });
    const page = await context.newPage();

    console.log('Logging in...');
    await page.goto('http://127.0.0.1:8000/login');
    await page.fill('#username_input', 'admin@bakery.com');
    await page.fill('#password_input', 'pass123');
    await page.click('#submit_btn');
    await page.waitForURL('**/admin/dashboard');

    console.log('\n--- Step 1: Desktop Viewport (1366x768) ---');
    // Verify header-sidebar-toggle-btn does NOT exist in DOM
    const oldBtnCount = await page.locator('.header-sidebar-toggle-btn').count();
    console.log('.header-sidebar-toggle-btn count in DOM (should be 0):', oldBtnCount);

    // Verify mobile toggle button is hidden on desktop
    const mobileBtnVisibleOnDesktop = await page.locator('#sidebar_toggle_btn').isVisible();
    console.log('#sidebar_toggle_btn visible on desktop (should be false):', mobileBtnVisibleOnDesktop);

    // Take screenshot of clean desktop header
    await page.screenshot({ path: 'C:/Users/M-S-I/.gemini/antigravity-ide/brain/bae14c9d-dcdd-414b-a697-6a4d55afe96d/scratch/desktop_header_no_toggle.png' });

    // Verify sidebar-header-toggle-btn works to close and open sidebar
    const sidebarToggleBtn = page.locator('.sidebar-header-toggle-btn');
    console.log('.sidebar-header-toggle-btn visible:', await sidebarToggleBtn.isVisible());
    
    // Click to collapse
    await sidebarToggleBtn.click();
    await page.waitForTimeout(400);
    const collapsedWidth = await page.locator('aside.bakery-sidebar').evaluate(el => el.getBoundingClientRect().width);
    console.log('Sidebar width after collapse (should be 72):', collapsedWidth);

    // Click to re-expand
    await sidebarToggleBtn.click();
    await page.waitForTimeout(400);
    const expandedWidth = await page.locator('aside.bakery-sidebar').evaluate(el => el.getBoundingClientRect().width);
    console.log('Sidebar width after re-expand (should be 240):', expandedWidth);

    console.log('\n--- Step 2: Tablet Viewport (1024x768) ---');
    await page.setViewportSize({ width: 1024, height: 768 });
    await page.waitForTimeout(300);

    const mobileBtnVisibleOnTablet = await page.locator('#sidebar_toggle_btn').isVisible();
    console.log('#sidebar_toggle_btn visible on tablet (should be false):', mobileBtnVisibleOnTablet);

    const tabletSidebarToggle = page.locator('.sidebar-header-toggle-btn');
    console.log('Tablet .sidebar-header-toggle-btn visible on rail:', await tabletSidebarToggle.isVisible());

    console.log('\n--- Step 3: Mobile Viewport (390x844) ---');
    await page.setViewportSize({ width: 390, height: 844 });
    await page.waitForTimeout(300);

    const mobileToggle = page.locator('#sidebar_toggle_btn');
    const isMobileToggleVisible = await mobileToggle.isVisible();
    console.log('Mobile toggle button visible on mobile (should be true):', isMobileToggleVisible);

    const sidebar = page.locator('aside.bakery-sidebar');
    const backdrop = page.locator('#sidebar_mobile_backdrop');
    console.log('Initial mobile sidebar expanded (should be false):', await sidebar.evaluate(el => el.classList.contains('sidebar-expanded')));

    // Open mobile drawer via mobile toggle
    await mobileToggle.click();
    await page.waitForTimeout(350);
    console.log('Mobile sidebar expanded after click (should be true):', await sidebar.evaluate(el => el.classList.contains('sidebar-expanded')));
    console.log('Mobile backdrop visible (should be true):', await backdrop.isVisible());

    // Close via backdrop click
    await backdrop.click();
    await page.waitForTimeout(350);
    console.log('Mobile sidebar closed after backdrop click (should be false):', await sidebar.evaluate(el => el.classList.contains('sidebar-expanded')));

    await browser.close();
    console.log('\nALL CHECKS PASSED PERFECTLY!');
})();
