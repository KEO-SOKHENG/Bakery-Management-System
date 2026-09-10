import { test, expect } from '@playwright/test';

test.describe('Notification Management System End-to-End Suite', () => {

    test.beforeEach(async ({ page }) => {
        // Log in as Admin
        await page.goto('/login');
        await page.fill('#username_input', 'admin@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');
        await expect(page).toHaveURL(/\/admin\/dashboard/);
        await page.waitForLoadState('domcontentloaded');
    });

    test('1. Header notification bell opens dropdown and links to Notification Center', async ({ page }) => {
        const bellBtn = page.locator('#notification_bell_btn');
        await expect(bellBtn).toBeVisible();

        // Click bell to toggle popover
        await bellBtn.click();
        const dropdown = page.locator('#notification_dropdown');
        await expect(dropdown).toHaveClass(/active/);

        // Check footer link to Notification Center
        const viewAllLink = dropdown.locator('a.btn-notification-all');
        await expect(viewAllLink).toBeVisible();
        await viewAllLink.click();

        await expect(page).toHaveURL(/\/admin\/notifications/);
        await expect(page.locator('.notif-header-left h1')).toContainText(/Notification Center/i);
    });

    test('2. Notification Center loads KPI stats, filter pills, and feed', async ({ page }) => {
        await page.goto('/admin/notifications');
        await expect(page).toHaveURL(/\/admin\/notifications/);

        // Verify KPI stat cards
        await expect(page.locator('.notif-stats-grid')).toBeVisible();
        await expect(page.locator('.notif-stat-card').first()).toBeVisible();

        // Verify filter pills bar
        await expect(page.locator('.notif-filter-pills')).toBeVisible();
        const filterPills = page.locator('.filter-pill-btn');
        await expect(filterPills.first()).toBeVisible();

        // Verify feed container
        await expect(page.locator('.notif-feed-card')).toBeVisible();
    });

    test('3. Sidebar has Notifications link and navigates correctly', async ({ page }) => {
        await page.goto('/admin/dashboard');

        // Locate Notifications in sidebar
        const sidebarNotif = page.locator('aside.bakery-sidebar a[href*="/admin/notifications"]');
        await expect(sidebarNotif).toBeVisible();
        await sidebarNotif.click();

        await expect(page).toHaveURL(/\/admin\/notifications/);
    });

    test('4. Mark all as read AJAX interaction', async ({ page }) => {
        await page.goto('/admin/notifications');

        const markAllBtn = page.locator('#page_mark_all_read');
        if (await markAllBtn.isVisible()) {
            await markAllBtn.click();
            // Should hide the mark all button or set unread count to 0
            const unreadBadge = page.locator('#stat_unread_count');
            await expect(unreadBadge).toHaveText('0');
        }
    });

    test('5. Localization switcher switches language between EN and KM', async ({ page }) => {
        await page.goto('/admin/notifications');

        // Switch to Khmer
        const kmBtn = page.locator('#lang_switch_km');
        if (await kmBtn.isVisible()) {
            await kmBtn.click();
            await page.waitForTimeout(500);
            await expect(page.locator('.notif-header-left h1')).toContainText(/មជ្ឈមណ្ឌលជូនដំណឹង/);
        }

        // Switch back to English
        const enBtn = page.locator('#lang_switch_en');
        if (await enBtn.isVisible()) {
            await enBtn.click();
            await page.waitForTimeout(500);
            await expect(page.locator('.notif-header-left h1')).toContainText(/Notification Center/);
        }
    });
});
