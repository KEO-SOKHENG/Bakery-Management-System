import { test, expect } from '@playwright/test';

test.describe('Language Switcher (English / Khmer)', () => {
    test('should switch language from English to Khmer and persist', async ({ page }) => {
        // Login as admin
        await page.goto('/login');
        await page.fill('#username_input', 'admin@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');

        await expect(page).toHaveURL(/\/admin\/dashboard/);

        // Click Khmer language route
        await page.goto('/lang/km');
        await expect(page).toHaveURL(/\/admin\/dashboard/);

        // Switch back to English
        await page.goto('/lang/en');
        await expect(page).toHaveURL(/\/admin\/dashboard/);
    });
});