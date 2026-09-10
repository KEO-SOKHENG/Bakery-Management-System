import { test, expect } from '@playwright/test';

test.describe('Products Catalog Page', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.fill('#username_input', 'admin@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');
        await expect(page).toHaveURL(/\/admin\/dashboard/);
        await page.goto('/admin/products');
    });

    test('should load products page correctly', async ({ page }) => {
        await expect(page).toHaveURL(/\/admin\/products/);
        await expect(page.locator('body')).toContainText(/Products/i);
    });

    test('should display product items grid or table', async ({ page }) => {
        const productElements = page.locator('.product-card, .products-grid, table');
        await expect(productElements.first()).toBeVisible();
    });
});
