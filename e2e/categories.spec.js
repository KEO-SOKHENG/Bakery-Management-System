import { test, expect } from '@playwright/test';

test.describe('Categories Management Page', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.fill('#username_input', 'admin@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');
        await expect(page).toHaveURL(/\/admin\/dashboard/);
        await page.goto('/admin/categories');
    });

    test('should load categories page correctly', async ({ page }) => {
        await expect(page).toHaveURL(/\/admin\/categories/);
        await expect(page.locator('body')).toContainText(/Categories/i);
    });

    test('should display existing categories', async ({ page }) => {
        const categoryCards = page.locator('.category-card, table tr, .categories-grid');
        await expect(categoryCards.first()).toBeVisible();
    });
});
