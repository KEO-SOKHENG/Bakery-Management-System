import { test, expect } from '@playwright/test';

test.describe('Authentication Flow', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
    });

    test('should display login page correctly', async ({ page }) => {
        await expect(page).toHaveTitle(/Login/i);
        await expect(page.locator('#username_input')).toBeVisible();
        await expect(page.locator('#password_input')).toBeVisible();
        await expect(page.locator('.role-selector-tabs')).toBeVisible();
        await expect(page.locator('.remember-me-checkbox')).toBeVisible();
    });

    test('should switch role tabs on login form', async ({ page }) => {
        const managerTab = page.locator('.role-tab-btn[data-role="manager"]');
        await managerTab.click();
        await expect(managerTab).toHaveClass(/active/);
        await expect(page.locator('#selected_role')).toHaveValue('manager');
        await expect(page.locator('#submit_btn')).toContainText(/Sign In as Manager/i);
    });

    test('should fail login with invalid username', async ({ page }) => {
        await page.fill('#username_input', 'wrong@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');

        await expect(page.locator('.login-card-container')).toBeVisible();
    });

    test('should fail login with invalid password', async ({ page }) => {
        await page.fill('#username_input', 'admin@bakery.com');
        await page.fill('#password_input', 'wrongpass');
        await page.click('#submit_btn');

        await expect(page.locator('.login-card-container')).toBeVisible();
    });

    test('should login successfully as Admin', async ({ page }) => {
        await page.fill('#username_input', 'admin@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');

        await expect(page).toHaveURL(/\/admin\/dashboard/);
        await expect(page.locator('h1')).toContainText(/Welcome Back/i);
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
