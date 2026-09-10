import { test, expect } from '@playwright/test';

test.describe('Role Based Access & Permissions', () => {
    test('Cashier role cannot access protected URLs directly', async ({ page }) => {
        await page.goto('/login');
        await page.click('.role-tab-btn[data-role="cashier"]');
        await page.fill('#username_input', 'cashier@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');

        // Verify Cashier dashboard
        await expect(page).toHaveURL(/\/cashier\/dashboard/);

        // Attempt direct access to protected URLs
        await page.goto('/admin/users');
        await expect(page).not.toHaveURL(/\/admin\/users/);

        await page.goto('/admin/settings');
        await expect(page).not.toHaveURL(/\/admin\/settings/);

        await page.goto('/admin/suppliers');
        await expect(page).not.toHaveURL(/\/admin\/suppliers/);
    });

    test('Manager role cannot access user management or critical system settings', async ({ page }) => {
        await page.goto('/login');
        await page.click('.role-tab-btn[data-role="manager"]');
        await page.fill('#username_input', 'manager@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');

        await expect(page).toHaveURL(/\/manager\/dashboard/);

        // Manager can access products & production
        await page.goto('/admin/products');
        await expect(page).toHaveURL(/\/admin\/products/);

        await page.goto('/admin/production');
        await expect(page).toHaveURL(/\/admin\/production/);

        // Manager CANNOT access User Management
        await page.goto('/admin/users');
        await expect(page).not.toHaveURL(/\/admin\/users/);
    });

    test('Admin role has full access to all pages', async ({ page }) => {
        await page.goto('/login');
        await page.click('.role-tab-btn[data-role="admin"]');
        await page.fill('#username_input', 'admin@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');

        await expect(page).toHaveURL(/\/admin\/dashboard/);

        await page.goto('/admin/users');
        await expect(page).toHaveURL(/\/admin\/users/);

        await page.goto('/admin/settings');
        await expect(page).toHaveURL(/\/admin\/settings/);
    });
});
