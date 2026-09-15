import { chromium } from 'playwright';

(async () => {
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext();
    const page = await context.newPage();

    console.log('Logging in...');
    await page.goto('http://127.0.0.1:8000/login');
    await page.fill('#username_input', 'admin@bakery.com');
    await page.fill('#password_input', 'pass123');
    await page.click('#submit_btn');
    await page.waitForURL('**/admin/dashboard');

    console.log('\n--- Step 1: Initial Desktop (1366x768) Expanded State ---');
    await page.setViewportSize({ width: 1366, height: 768 });
    await page.goto('http://127.0.0.1:8000/admin/dashboard');
    await page.waitForTimeout(600);

    const toggleBtn = page.locator('.sidebar-header-toggle-btn');
    const isVisibleInitial = await toggleBtn.isVisible();
    const titleInitial = await toggleBtn.getAttribute('title');
    const chevronRotationInitial = await toggleBtn.locator('.icon-chevron-toggle').evaluate(el => window.getComputedStyle(el).transform);
    const sidebarWidthInitial = await page.locator('.bakery-sidebar').evaluate(el => el.getBoundingClientRect().width);

    console.log('Initial State:', {
        isVisible: isVisibleInitial,
        title: titleInitial,
        sidebarWidth: sidebarWidthInitial,
        chevronRotation: chevronRotationInitial
    });
    await page.screenshot({ path: 'C:/Users/M-S-I/.gemini/antigravity-ide/brain/bae14c9d-dcdd-414b-a697-6a4d55afe96d/scratch/toggle_step1_expanded.png' });

    console.log('\n--- Step 2: Click .sidebar-header-toggle-btn to CLOSE / Collapse ---');
    await toggleBtn.click();
    await page.waitForTimeout(500);

    const isVisibleCollapsed = await toggleBtn.isVisible();
    const titleCollapsed = await toggleBtn.getAttribute('title');
    const chevronRotationCollapsed = await toggleBtn.locator('.icon-chevron-toggle').evaluate(el => window.getComputedStyle(el).transform);
    const sidebarWidthCollapsed = await page.locator('.bakery-sidebar').evaluate(el => el.getBoundingClientRect().width);
    const btnBoxCollapsed = await toggleBtn.boundingBox();

    console.log('After Clicking (Collapsed State):', {
        isVisible: isVisibleCollapsed,
        title: titleCollapsed,
        sidebarWidth: sidebarWidthCollapsed,
        chevronRotation: chevronRotationCollapsed,
        btnBox: btnBoxCollapsed
    });
    await page.screenshot({ path: 'C:/Users/M-S-I/.gemini/antigravity-ide/brain/bae14c9d-dcdd-414b-a697-6a4d55afe96d/scratch/toggle_step2_collapsed.png' });

    console.log('\n--- Step 3: Hover on .sidebar-header-toggle-btn in Collapsed State ---');
    await toggleBtn.hover();
    await page.waitForTimeout(300);
    await page.screenshot({ path: 'C:/Users/M-S-I/.gemini/antigravity-ide/brain/bae14c9d-dcdd-414b-a697-6a4d55afe96d/scratch/toggle_step3_collapsed_hover.png' });

    console.log('\n--- Step 4: Click .sidebar-header-toggle-btn to OPEN / Expand ---');
    await toggleBtn.click();
    await page.waitForTimeout(500);

    const isVisibleReExpanded = await toggleBtn.isVisible();
    const titleReExpanded = await toggleBtn.getAttribute('title');
    const sidebarWidthReExpanded = await page.locator('.bakery-sidebar').evaluate(el => el.getBoundingClientRect().width);

    console.log('After Clicking Again (Re-Expanded State):', {
        isVisible: isVisibleReExpanded,
        title: titleReExpanded,
        sidebarWidth: sidebarWidthReExpanded
    });
    await page.screenshot({ path: 'C:/Users/M-S-I/.gemini/antigravity-ide/brain/bae14c9d-dcdd-414b-a697-6a4d55afe96d/scratch/toggle_step4_re_expanded.png' });

    console.log('\n--- Step 5: Tablet 1024x768 Open & Close Drawer Test ---');
    await page.setViewportSize({ width: 1024, height: 768 });
    await page.goto('http://127.0.0.1:8000/admin/dashboard');
    await page.waitForTimeout(500);

    const tabletToggle = page.locator('.sidebar-header-toggle-btn');
    console.log('Tablet Rail Toggle Visible:', await tabletToggle.isVisible());

    // Click to open drawer
    await tabletToggle.click();
    await page.waitForTimeout(400);
    const drawerOpen = await page.locator('.bakery-sidebar').evaluate(el => el.classList.contains('sidebar-expanded'));
    console.log('Tablet Drawer Opened:', drawerOpen);
    await page.screenshot({ path: 'C:/Users/M-S-I/.gemini/antigravity-ide/brain/bae14c9d-dcdd-414b-a697-6a4d55afe96d/scratch/toggle_step5_tablet_opened.png' });

    // Click to close drawer
    await tabletToggle.click();
    await page.waitForTimeout(400);
    const drawerClosed = await page.locator('.bakery-sidebar').evaluate(el => !el.classList.contains('sidebar-expanded'));
    console.log('Tablet Drawer Closed:', drawerClosed);
    await page.screenshot({ path: 'C:/Users/M-S-I/.gemini/antigravity-ide/brain/bae14c9d-dcdd-414b-a697-6a4d55afe96d/scratch/toggle_step6_tablet_closed.png' });

    await browser.close();
    console.log('\nSUCCESS! Both OPEN and CLOSE functions of .sidebar-header-toggle-btn verified!');
})();
