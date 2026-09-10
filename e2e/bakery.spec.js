import { test, expect } from '@playwright/test';

test('Bakery Management System opens successfully', async ({ page }) => {
    await page.goto('http://127.0.0.1:8000');

    await expect(page).toHaveTitle(/Bakery/i);
});