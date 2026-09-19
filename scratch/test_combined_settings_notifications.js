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

    console.log('\n--- Step 1: Check Sidebar on Dashboard ---');
    await page.setViewportSize({ width: 1366, height: 768 });
    await page.goto('http://127.0.0.1:8000/admin/dashboard');
    await page.waitForTimeout(600);

    // 1. Ensure standalone notifications nav item is gone
    const standaloneNotif = page.locator('.sidebar-nav > a.sidebar-nav-item[href*="notifications"]');
    const standaloneCount = await standaloneNotif.count();
    console.log('Standalone Notifications Nav Item Count (should be 0):', standaloneCount);

    // 2. Locate Settings group and its dropdown
    const settingsGroup = page.locator('#group_settings');
    const settingsParent = settingsGroup.locator('> a.sidebar-nav-item');
    const settingsDropBtn = settingsParent.locator('.sidebar-dropdown-btn');
    const settingsBadge = settingsParent.locator('.sidebar-badge');

    console.log('Settings Group Exists:', await settingsGroup.count() > 0);
    console.log('Settings Dropdown Btn Visible:', await settingsDropBtn.isVisible());
    if (await settingsBadge.count() > 0) {
        console.log('Settings Badge Text:', (await settingsBadge.textContent()).trim());
    }

    // 3. Click Settings dropdown button to expand submenu
    console.log('\n--- Step 2: Open Settings Dropdown ---');
    await settingsDropBtn.click();
    await page.waitForTimeout(400);

    const submenuNotif = settingsGroup.locator('.sidebar-submenu a[href*="notifications"]');
    const submenuUsers = settingsGroup.locator('.sidebar-submenu a[href*="users"]');

    console.log('Submenu Notifications Item Visible:', await submenuNotif.isVisible());
    console.log('Submenu Users & Staff Item Visible:', await submenuUsers.isVisible());
    await page.screenshot({ path: 'C:/Users/M-S-I/.gemini/antigravity-ide/brain/bae14c9d-dcdd-414b-a697-6a4d55afe96d/scratch/settings_combined_notifications_open.png' });

    // 4. Click Notifications link in submenu
    console.log('\n--- Step 3: Navigate to Notifications via Settings Submenu ---');
    await submenuNotif.click();
    await page.waitForURL('**/admin/notifications');
    await page.waitForTimeout(500);

    console.log('Current URL:', page.url());
    const isSettingsOpenOnNotifPage = await page.locator('#group_settings').evaluate(el => el.classList.contains('is-open'));
    const isSettingsParentActive = await page.locator('#group_settings > a.sidebar-nav-item').evaluate(el => el.classList.contains('active'));
    const isSubmenuNotifActive = await page.locator('#group_settings .sidebar-submenu a[href*="notifications"]').evaluate(el => el.classList.contains('active'));

    console.log('Settings Group is-open on Notifications Page:', isSettingsOpenOnNotifPage);
    console.log('Settings Parent has .active:', isSettingsParentActive);
    console.log('Notifications Submenu Item has .active:', isSubmenuNotifActive);
    await page.screenshot({ path: 'C:/Users/M-S-I/.gemini/antigravity-ide/brain/bae14c9d-dcdd-414b-a697-6a4d55afe96d/scratch/notifications_page_settings_active.png' });

    // 5. Test 72px Collapsed Rail
    console.log('\n--- Step 4: Collapsed Rail Test with Combined Settings ---');
    const toggleBtn = page.locator('.sidebar-header-toggle-btn');
    await toggleBtn.click();
    await page.waitForTimeout(400);

    const sidebarWidth = await page.locator('.bakery-sidebar').evaluate(el => el.getBoundingClientRect().width);
    console.log('Sidebar Collapsed Width:', sidebarWidth);
    await page.screenshot({ path: 'C:/Users/M-S-I/.gemini/antigravity-ide/brain/bae14c9d-dcdd-414b-a697-6a4d55afe96d/scratch/settings_collapsed_rail.png' });

    // Expand back
    await toggleBtn.click();
    await page.waitForTimeout(400);

    await browser.close();
    console.log('\nAll tests completed successfully!');
})();
