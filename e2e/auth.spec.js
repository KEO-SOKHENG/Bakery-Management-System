import { test, expect } from '@playwright/test';

test.describe('Authentication Flow', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
    });

    test('should display login page correctly without role selector', async ({ page }) => {
        await expect(page).toHaveTitle(/Login/i);
        await expect(page.locator('#username_input')).toBeVisible();
        await expect(page.locator('#password_input')).toBeVisible();
        await expect(page.locator('.remember-me-checkbox')).toBeVisible();
        await expect(page.locator('.forgot-password-link')).toBeVisible();
        await expect(page.locator('#submit_btn')).toBeVisible();
        await expect(page.locator('#submit_btn')).toHaveText(/Sign In/i);

        // Role selector tabs and hidden input must NOT exist
        await expect(page.locator('.role-selector-tabs')).not.toBeAttached();
        await expect(page.locator('.role-tab-btn')).not.toBeAttached();
        await expect(page.locator('#selected_role')).not.toBeAttached();
    });

    test('should fail login with invalid username', async ({ page }) => {
        await page.fill('#username_input', 'wrong@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');

        await expect(page.locator('.login-card-container')).toBeVisible();
        await expect(page.locator('.login-alert-error')).toBeVisible();
    });

    test('should fail login with invalid password', async ({ page }) => {
        await page.fill('#username_input', 'admin@bakery.com');
        await page.fill('#password_input', 'wrongpass');
        await page.click('#submit_btn');

        await expect(page.locator('.login-card-container')).toBeVisible();
        await expect(page.locator('.login-alert-error')).toBeVisible();
    });

    test('should automatically login and redirect as Admin', async ({ page }) => {
        await page.fill('#username_input', 'admin@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');

        await expect(page).toHaveURL(/\/admin\/dashboard/);
        await expect(page.locator('.bakery-main-content')).toBeVisible();
    });

    test('should automatically login and redirect as Manager', async ({ page }) => {
        await page.fill('#username_input', 'manager@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');

        await expect(page).toHaveURL(/\/manager\/dashboard/);
        await expect(page.locator('.bakery-main-content')).toBeVisible();
    });

    test('should automatically login and redirect as Baker', async ({ page }) => {
        await page.fill('#username_input', 'baker@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');

        await expect(page).toHaveURL(/\/admin\/production/);
        await expect(page.locator('.bakery-main-content')).toBeVisible();
    });

    test('should automatically login and redirect as Cashier', async ({ page }) => {
        await page.fill('#username_input', 'cashier@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');

        await expect(page).toHaveURL(/\/cashier\/dashboard/);
        await expect(page.locator('.bakery-main-content')).toBeVisible();
    });

    test('should logout successfully', async ({ page }) => {
        await page.fill('#username_input', 'admin@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');
        await expect(page).toHaveURL(/\/admin\/dashboard/);

        await page.goto('/logout');
        await expect(page).toHaveURL(/\/login/);
    });
});
