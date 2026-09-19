import { test, expect } from '@playwright/test';

test('Verify Laptop height 1366x620 with Inventory expanded and collapse toggle', async ({ page }) => {
    // Exact realistic laptop viewport (768 - browser UI)
    await page.setViewportSize({ width: 1366, height: 620 });
    await page.goto('/login');
    await page.fill('#username_input', 'admin@bakery.com');
    await page.fill('#password_input', 'pass123');
    await page.click('#submit_btn');
    await expect(page).toHaveURL(/\/admin\/dashboard/);

    // Expand Inventory
    const invBtn = page.locator('#group_inventory .sidebar-dropdown-btn');
    await expect(invBtn).toBeVisible();
    await invBtn.click();
    await page.waitForTimeout(400);

    // Check Settings (which encapsulates Notifications) & Logout are visible
    const settingsItem = page.locator('.bakery-sidebar a[href*="/admin/settings"]').first();
    const logoutBtn = page.locator('.logout-btn');

    expect(await settingsItem.isVisible()).toBe(true);
    expect(await logoutBtn.isVisible()).toBe(true);

    // Screenshot expanded sidebar
    const sidebar = page.locator('#sidebar_nav');
    await sidebar.screenshot({ path: 'C:/Users/M-S-I/.gemini/antigravity-ide/brain/bae14c9d-dcdd-414b-a697-6a4d55afe96d/scratch/laptop_620_with_inventory_fixed.png' });

    // Test Collapse Toggle Button in Header
    const collapseBtn = page.locator('#sidebar_close_btn');
    await expect(collapseBtn).toBeVisible();
    await collapseBtn.click();
    await page.waitForTimeout(400);

    // Check sidebar has collapsed
    await expect(sidebar).toHaveClass(/sidebar-collapsed/);
    await sidebar.screenshot({ path: 'C:/Users/M-S-I/.gemini/antigravity-ide/brain/bae14c9d-dcdd-414b-a697-6a4d55afe96d/scratch/laptop_620_collapsed_rail.png' });

    // Verify topbar toggle is hidden on desktop/laptop
    const topbarToggle = page.locator('#sidebar_toggle_btn');
    await expect(topbarToggle).toBeHidden();

    // Re-expand sidebar using the dual-function sidebar-header-toggle-btn on the rail
    await expect(collapseBtn).toBeVisible();
    await collapseBtn.click();
    await page.waitForTimeout(400);

    // Check sidebar is expanded again
    await expect(sidebar).not.toHaveClass(/sidebar-collapsed/);
    console.log('Successfully toggled between expanded and collapsed!');
});
