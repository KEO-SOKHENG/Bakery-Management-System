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

    test('should display recipe table', async ({ page }) => {
        const table = page.locator('#recipes_table');
        await expect(table).toBeVisible();
    });

    test('should follow exact orders-container full-height layout with internal scrolling table', async ({ page }) => {
        await page.setViewportSize({ width: 1440, height: 900 });

        // Enable dark mode
        await page.evaluate(() => {
            document.documentElement.setAttribute('data-theme', 'dark');
            document.body.classList.add('dark-mode');
        });

        // 4 KPI cards
        const statCards = page.locator('.dash-stat-row .stat-card');
        await expect(statCards).toHaveCount(4);

        // Filter toolbar
        const toolbar = page.locator('#recipes_toolbar');
        await expect(toolbar).toBeVisible();
        await expect(page.locator('#recipe_product_select')).toBeVisible();
        await expect(page.locator('#btn_open_recipe_modal')).toBeVisible();

        // Table card
        const card = page.locator('#recipes_table_card');
        const wrapper = page.locator('#recipes_table_wrapper');
        await expect(card).toBeVisible();
        await expect(wrapper).toBeVisible();

        const cardBox = await card.boundingBox();
        expect(cardBox).not.toBeNull();
        const cardBottom = cardBox.y + cardBox.height;
        console.log(`Recipes Card Bottom: ${cardBottom}`);
        expect(cardBottom).toBeGreaterThanOrEqual(860);
        expect(cardBottom).toBeLessThanOrEqual(905);

        // Verify table rows render
        const rows = page.locator('#recipes_table tbody tr');
        const count = await rows.count();
        expect(count).toBeGreaterThan(0);

        // Test Add Recipe Modal
        await page.locator('#btn_open_recipe_modal').click();
        const addModal = page.locator('#add_recipe_modal');
        await expect(addModal).toBeVisible();
        await page.locator('#btn_cancel_recipe').click();
        await expect(addModal).not.toBeVisible();

        // Test Edit Recipe Modal
        const editBtn = page.locator('.btn-edit-recipe').first();
        if (await editBtn.isVisible()) {
            await editBtn.click();
            const editModal = page.locator('#edit_recipe_modal');
            await expect(editModal).toBeVisible();
            await page.locator('#btn_cancel_edit_recipe').click();
            await expect(editModal).not.toBeVisible();
        }
    });
});
