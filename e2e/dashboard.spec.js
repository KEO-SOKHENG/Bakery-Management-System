import { test, expect } from '@playwright/test';

test.describe('Admin Dashboard Page', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.fill('#username_input', 'admin@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');
        await expect(page).toHaveURL(/\/admin\/dashboard/);
    });

    test('should display 8 real summary stat cards', async ({ page }) => {
        await expect(page.locator('.stat-card')).toHaveCount(8);
        await expect(page.locator('.stat-card').nth(0)).toContainText(/Today's Orders/i);
        await expect(page.locator('.stat-card').nth(1)).toContainText(/Today's Revenue/i);
        await expect(page.locator('.stat-card').nth(2)).toContainText(/Pending Orders/i);
        await expect(page.locator('.stat-card').nth(3)).toContainText(/Low Stock Items/i);
        await expect(page.locator('.stat-card').nth(4)).toContainText(/Total Customers/i);
        await expect(page.locator('.stat-card').nth(5)).toContainText(/Active Products/i);
        await expect(page.locator('.stat-card').nth(6)).toContainText(/Active Productions/i);
        await expect(page.locator('.stat-card').nth(7)).toContainText(/Pending Purchase Orders/i);
    });

    test('should render timeframe filter toolbar and switch period', async ({ page }) => {
        const filterBar = page.locator('.dash-filter-bar');
        await expect(filterBar).toBeVisible();

        const todayPill = filterBar.locator('a.period-pill', { hasText: 'Today' });
        await expect(todayPill).toBeVisible();
        await todayPill.click();

        await expect(page).toHaveURL(/period=today/);
        await expect(page.locator('.dash-stat-row')).toBeVisible();
    });

    test('should render Sales & Revenue visual analytics chart card', async ({ page }) => {
        const chartCard = page.locator('.chart-card');
        await expect(chartCard).toBeVisible();
        await expect(chartCard.locator('svg')).toBeVisible();
        await expect(chartCard).toContainText(/Total Revenue/i);
        await expect(chartCard).toContainText(/Average Order Value/i);
    });

    test('should render Order Status Distribution and Best Selling Products', async ({ page }) => {
        const distBoxes = page.locator('.status-dist-box');
        await expect(distBoxes.first()).toBeVisible();

        const bestSellerCard = page.locator('.bakery-card', { hasText: /Best Selling Products/i });
        await expect(bestSellerCard).toBeVisible();
    });

    test('should render Quick Actions section', async ({ page }) => {
        const quickActions = page.locator('.quick-actions-grid');
        await expect(quickActions).toBeVisible();
        await expect(quickActions.locator('a')).toHaveCount(3);
    });

    test('should load Recent Orders table', async ({ page }) => {
        const ordersTable = page.locator('.recent-orders-table');
        await expect(ordersTable).toBeVisible();
        await expect(ordersTable.locator('th').nth(0)).toContainText(/Order ID/i);
    });
});
