import { test, expect } from '@playwright/test';

test.describe('Delivery Management & Driver Dispatch Workflow Suite', () => {

    test('1. Admin can view Delivery Management with KPI cards, tabs, and table', async ({ page }) => {
        // Log in as Admin
        await page.goto('/login');
        await page.fill('#username_input', 'admin@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');
        await expect(page).toHaveURL(/\/admin\/dashboard/);

        // Navigate to Delivery Management
        await page.goto('/admin/deliveries');
        await expect(page).toHaveURL(/\/admin\/deliveries/);

        // Verify KPI metrics cards
        await expect(page.locator('#kpi_total_deliveries')).toBeVisible();
        await expect(page.locator('#kpi_pending_deliveries')).toBeVisible();
        await expect(page.locator('#kpi_in_transit_deliveries')).toBeVisible();
        await expect(page.locator('#kpi_delivered_deliveries')).toBeVisible();

        // Verify toolbar tabs & search
        await expect(page.locator('#delivery_status_tabs')).toBeVisible();
        await expect(page.locator('#delivery_search_input')).toBeVisible();

        // Verify deliveries table
        await expect(page.locator('#deliveries_table')).toBeVisible();
    });

    test('2. Admin can schedule a delivery via modal and view its details', async ({ page }) => {
        await page.goto('/login');
        await page.fill('#username_input', 'admin@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');
        await expect(page).toHaveURL(/\/admin\/dashboard/);

        await page.goto('/admin/deliveries');

        // Check if there are eligible orders in the modal dropdown
        const openModalBtn = page.locator('#btn_open_schedule_modal');
        if (await openModalBtn.isVisible()) {
            await openModalBtn.click();
            await expect(page.locator('#schedule_delivery_modal')).toHaveClass(/is-active/);

            const orderSelect = page.locator('#modal_order_id');
            const optionCount = await orderSelect.locator('option').count();

            if (optionCount > 1) {
                // Select second option (first real order)
                await orderSelect.selectOption({ index: 1 });

                // Fill address if empty
                const addrField = page.locator('#modal_delivery_address');
                await addrField.fill('St. 51 Pasteur, Boeung Keng Kang 1, Phnom Penh');

                // Submit
                await page.locator('#btn_submit_schedule').click();

                // Should land on show page
                await expect(page).toHaveURL(/\/admin\/deliveries\/\d+/);
                await expect(page.locator('text=Delivery Information')).toBeVisible();
            } else {
                // Dismiss modal
                await page.locator('#btn_close_schedule_modal').click();
            }
        }
    });

    test('3. Delivery staff can login and access delivery workflow', async ({ page }) => {
        await page.goto('/login');
        await page.fill('#username_input', 'delivery@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');

        // Delivery staff should land on deliveries page
        await expect(page).toHaveURL(/\/admin\/deliveries/);
        await expect(page.locator('#deliveries_table')).toBeVisible();
    });

    test('4. Baker is prevented from accessing Delivery Management', async ({ page }) => {
        await page.goto('/login');
        await page.fill('#username_input', 'baker@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');
        await expect(page).toHaveURL(/\/admin\/production/);

        // Attempt direct navigation to /admin/deliveries
        await page.goto('/admin/deliveries');
        // Baker should be redirected away
        await expect(page).not.toHaveURL(/\/admin\/deliveries$/);
    });
});
