import { test, expect } from '@playwright/test';

test.describe('Sales & Revenue Analytics Page', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.fill('#username_input', 'admin@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');
        await expect(page).toHaveURL(/\/admin\/dashboard/);
        await page.goto('/admin/reports');
    });

    test('should load sales analytics page correctly', async ({ page }) => {
        await expect(page).toHaveURL(/\/admin\/reports/);
        await expect(page.locator('body')).toContainText(/Sales|Reports/i);
    });
});
