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

    test('should follow exact orders page container layout with full screen card and internal scrolling', async ({ page }) => {
        await page.setViewportSize({ width: 1440, height: 900 });

        // Enable dark mode to verify reference design
        await page.evaluate(() => {
            document.documentElement.setAttribute('data-theme', 'dark');
            document.body.classList.add('dark-mode');
        });

        const container = page.locator('.orders-container');
        await expect(container).toBeVisible();

        const card = page.locator('#ingredients_table_card');
        const wrapper = page.locator('#ingredients_table_wrapper');
        await expect(card).toBeVisible();
        await expect(wrapper).toBeVisible();

        const cardBox = await card.boundingBox();
        expect(cardBox).not.toBeNull();
        const cardBottom = cardBox.y + cardBox.height;
        console.log(`Viewport: 900, Card Y: ${cardBox.y}, Card Height: ${cardBox.height}, Card Bottom: ${cardBottom}`);
        expect(cardBottom).toBeGreaterThanOrEqual(860);
        expect(cardBottom).toBeLessThanOrEqual(905);

        // Verify action buttons are visible and clickable
        await expect(page.locator('#btn_open_ingredient_modal')).toBeVisible();
        await expect(page.locator('#btn_create_po')).toBeVisible();

        // Verify internal scrolling inside #ingredients_table_wrapper
        const initialScrollTop = await wrapper.evaluate(el => el.scrollTop);
        await wrapper.evaluate(el => el.scrollTop = 200);
        await page.waitForTimeout(200);
        const scrolledTop = await wrapper.evaluate(el => el.scrollTop);
        expect(scrolledTop).toBeGreaterThan(initialScrollTop);

        // Verify sticky table headers remain visible inside the card
        const firstTh = page.locator('#ingredients_table thead th').first();
        await expect(firstTh).toBeVisible();

        // Verify pagination is removed and all ingredients load into the internal scrolling table
        await expect(page.locator('.orders-pagination-wrapper')).not.toBeVisible();
        const rows = page.locator('#ingredients_table tbody tr');
        await expect(rows).toHaveCount(25);
    });
});
