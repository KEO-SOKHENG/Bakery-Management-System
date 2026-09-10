import { test, expect } from '@playwright/test';

test.describe('Recipes Management Page', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.fill('#username_input', 'admin@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');
        await expect(page).toHaveURL(/\/admin\/dashboard/);
        await page.goto('/admin/recipes');
    });

    test('should load recipes page correctly', async ({ page }) => {
        await expect(page).toHaveURL(/\/admin\/recipes/);
        await expect(page.locator('body')).toContainText(/Recipes/i);
    });

    test('should display recipe cards or table', async ({ page }) => {
        const recipeElements = page.locator('table, .recipe-card, .recipes-grid');
        await expect(recipeElements.first()).toBeVisible();
    });
});
