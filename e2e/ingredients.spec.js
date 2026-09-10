import { test, expect } from '@playwright/test';

test.describe('Ingredients & Stock Page', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.fill('#username_input', 'admin@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');
        await expect(page).toHaveURL(/\/admin\/dashboard/);
        await page.goto('/admin/ingredients');
    });

    test('should load ingredients page correctly', async ({ page }) => {
        await expect(page).toHaveURL(/\/admin\/ingredients/);
        await expect(page.locator('body')).toContainText(/Ingredients/i);
    });

    test('should display stock table or grid', async ({ page }) => {
        const stockElements = page.locator('table, .stock-table, .ingredients-grid');
        await expect(stockElements.first()).toBeVisible();
    });
});
