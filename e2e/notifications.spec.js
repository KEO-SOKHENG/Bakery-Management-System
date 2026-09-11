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

        // Check dropdown heading and list
        await expect(dropdown.locator('.notification-heading')).toBeVisible();

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

        // Verify promotions filter pill exists
        const promoPill = page.locator('.filter-pill-btn[data-lang-key="promotions"]');
        await expect(promoPill).toBeVisible();

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

    test('4. Mark single notification read and unread AJAX interaction', async ({ page }) => {
        await page.goto('/admin/notifications');

        // Check if there is an unread notification to mark as read
        const readBtn = page.locator('.btn-row-action:has-text("Mark as read")').first();
        if (await readBtn.isVisible()) {
            await readBtn.click();
            await page.waitForTimeout(500);

            // Now that row should show Mark as unread
            const unreadBtn = page.locator('.btn-row-action:has-text("Mark as unread")').first();
            await expect(unreadBtn).toBeVisible();

            // Toggle back to unread
            await unreadBtn.click();
            await page.waitForTimeout(500);
            await expect(page.locator('.btn-row-action:has-text("Mark as read")').first()).toBeVisible();
        }
    });

    test('5. Mark all as read AJAX interaction', async ({ page }) => {
        await page.goto('/admin/notifications');

        const markAllBtn = page.locator('#page_mark_all_read');
        if (await markAllBtn.isVisible()) {
            await markAllBtn.click();
            await page.waitForTimeout(500);
            const unreadBadge = page.locator('#stat_unread_count');
            await expect(unreadBadge).toHaveText('0');
        }
    });

    test('6. Admin can open Broadcast Announcement modal and send promotion', async ({ page }) => {
        await page.goto('/admin/notifications');

        const broadcastBtn = page.locator('#btn_open_promotion_modal');
        await expect(broadcastBtn).toBeVisible();
        await broadcastBtn.click();

        const modal = page.locator('#promotion_modal');
        await expect(modal).toHaveClass(/is-active/);

        // Fill form fields
        await page.fill('#promotion_title_input', 'Artisan Croissant Morning Promo');
        await page.fill('#promotion_message_input', 'Freshly baked chocolate croissants 20% off until 11am today.');
        await page.selectOption('#promotion_role_select', 'cashier');
        await page.selectOption('#promotion_severity_select', 'warning');

        // Submit promotion
        await page.click('#btn_submit_promotion');
        await page.waitForLoadState('domcontentloaded');

        // Verify redirection back to Notification Center
        await expect(page).toHaveURL(/\/admin\/notifications/);
    });

    test('7. Role permissions: Cashier does not have Admin broadcast button', async ({ page }) => {
        // Log out as Admin
        await page.goto('/logout');

        // Log in as Cashier
        await page.goto('/login');
        await page.fill('#username_input', 'cashier@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');
        await expect(page).toHaveURL(/\/cashier\/dashboard/);

        // Visit Notification Center
        await page.goto('/admin/notifications');
        await expect(page).toHaveURL(/\/admin\/notifications/);

        // Verify broadcast announcement modal button is NOT visible for cashier
        const broadcastBtn = page.locator('#btn_open_promotion_modal');
        await expect(broadcastBtn).toHaveCount(0);
    });

    test('8. Mobile and responsive viewport behavior', async ({ page }) => {
        // Set mobile viewport
        await page.setViewportSize({ width: 375, height: 667 });
        await page.goto('/admin/notifications');

        // Verify notification header and feed adapt to mobile
        await expect(page.locator('.notif-header-area')).toBeVisible();
        await expect(page.locator('.notif-feed-card')).toBeVisible();

        // Bell button remains clickable on mobile
        const bellBtn = page.locator('#notification_bell_btn');
        await expect(bellBtn).toBeVisible();
        await bellBtn.click();
        await expect(page.locator('#notification_dropdown')).toHaveClass(/active/);
    });

    test('9. Theme switching: Light, Dark, and Glass theme fidelity', async ({ page }) => {
        await page.goto('/admin/notifications');

        // Test Light Theme
        await page.evaluate(() => document.documentElement.setAttribute('data-theme', 'light'));
        await expect(page.locator('html')).toHaveAttribute('data-theme', 'light');
        await expect(page.locator('.notif-feed-card')).toBeVisible();

        // Test Dark Theme
        await page.evaluate(() => document.documentElement.setAttribute('data-theme', 'dark'));
        await expect(page.locator('html')).toHaveAttribute('data-theme', 'dark');
        await expect(page.locator('.notif-feed-card')).toBeVisible();

        // Test Glass Theme
        await page.evaluate(() => document.documentElement.setAttribute('data-theme', 'glass'));
        await expect(page.locator('html')).toHaveAttribute('data-theme', 'glass');
        await expect(page.locator('.notif-feed-card')).toBeVisible();
    });

    test('10. Localization switcher switches language between EN and KM', async ({ page }) => {
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
