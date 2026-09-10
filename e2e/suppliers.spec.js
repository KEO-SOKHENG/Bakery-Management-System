import { test, expect } from '@playwright/test';

test.describe('Suppliers Management Page', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.fill('#username_input', 'admin@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');
        await expect(page).toHaveURL(/\/admin\/dashboard/);
        await page.goto('/admin/suppliers');
    });

    test('should load suppliers page correctly', async ({ page }) => {
        await expect(page).toHaveURL(/\/admin\/suppliers/);
        await expect(page.locator('body')).toContainText(/Suppliers/i);
    });

    test('should display supplier directory list', async ({ page }) => {
        const supplierElements = page.locator('table, .supplier-card, .suppliers-grid');
        await expect(supplierElements.first()).toBeVisible();
    });
});
