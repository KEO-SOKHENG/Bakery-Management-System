import { test, expect } from '@playwright/test';

test.describe('Production Management & Baking Workflow Suite', () => {

    test('1. Admin can access Production Management, view stats, and inspect batches', async ({ page }) => {
        await page.goto('/login');
        await page.fill('#username_input', 'admin@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');
        await expect(page).toHaveURL(/\/admin\/dashboard/);

        // Navigate to Production
        await page.goto('/admin/production');
        await expect(page).toHaveURL(/\/admin\/production/);

        // Verify Hero & Stats
        await expect(page.locator('.production-hero-title h1')).toContainText(/Production/i);
        await expect(page.locator('.prod-stats-grid')).toBeVisible();
        await expect(page.locator('.prod-toolbar-card')).toBeVisible();
        await expect(page.locator('#production_batches_table')).toBeVisible();

        // Verify seeded batches exist
        const rows = page.locator('.prod-batch-row');
        await expect(rows.first()).toBeVisible();
    });

    test('2. Admin can schedule a new production batch with assigned Baker & live ingredient preview', async ({ page }) => {
        await page.goto('/login');
        await page.fill('#username_input', 'admin@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');
        await page.goto('/admin/production');

        // Open Schedule Batch Modal
        await page.click('#btn_open_prod_modal');
        const modal = page.locator('#modal_schedule_batch');
        await expect(modal).toBeVisible();

        // Select Product (e.g. Vanilla Cake or second option)
        const productSelect = page.locator('#prod_select_product');
        const options = await productSelect.locator('option').all();
        expect(options.length).toBeGreaterThan(1);
        await productSelect.selectOption({ index: 1 });

        // Verify Live Ingredient Preview Appears
        const previewContainer = page.locator('#preview_ingredients_container');
        await expect(previewContainer).toBeVisible();

        // Adjust quantity
        await page.fill('#prod_input_qty', '8');

        // Select Baker
        const bakerSelect = page.locator('#prod_select_baker');
        await bakerSelect.selectOption({ index: 0 });

        // Ensure status is scheduled
        await page.selectOption('#prod_select_status', 'scheduled');

        // Fill Notes
        await page.fill('#prod_textarea_notes', 'Automated E2E Test Batch');

        // Submit form
        await page.click('#btn_submit_production');

        // Verify success
        await expect(page).toHaveURL(/\/admin\/production/);
        await expect(page.locator('body')).toContainText(/created successfully/i);

        // Verify table has the newly created batch
        await expect(page.locator('#production_batches_table')).toBeVisible();
        const firstRow = page.locator('.prod-batch-row').first();
        await expect(firstRow).toBeVisible();
        await expect(firstRow.locator('.badge-status')).toContainText(/Scheduled/i);
    });

    test('3. Workflow transitions: Scheduled -> In Progress -> Completed and Detail Timeline view', async ({ page }) => {
        await page.goto('/login');
        await page.fill('#username_input', 'admin@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');
        await page.goto('/admin/production');

        // Schedule a fresh batch of 2 croissants
        await page.click('#btn_open_prod_modal');
        await page.selectOption('#prod_select_product', { index: 1 });
        await page.fill('#prod_input_qty', '2');
        await page.selectOption('#prod_select_status', 'scheduled');
        await page.fill('#prod_textarea_notes', 'Workflow Lifecycle Test');
        await page.click('#btn_submit_production');
        await expect(page).toHaveURL(/\/admin\/production/);

        // Filter to find the batch
        await page.fill('#prod_search_input', 'Workflow Lifecycle Test');
        await page.locator('#prod_filter_form').press('Enter');
        await page.waitForTimeout(500);

        const batchRow = page.locator('.prod-batch-row').first();
        await expect(batchRow).toBeVisible();
        await expect(batchRow.locator('.badge-status')).toContainText(/Scheduled/i);

        // 1. Transition Scheduled -> In Progress
        const startBtn = batchRow.locator('.btn-action-start');
        await expect(startBtn).toBeVisible();
        await startBtn.click();

        // Verify updated to In Progress
        await expect(page.locator('body')).toContainText(/In Progress/i);

        // Filter again to view row
        await page.fill('#prod_search_input', 'Workflow Lifecycle Test');
        await page.locator('#prod_filter_form').press('Enter');
        await page.waitForTimeout(500);

        const updatedRow = page.locator('.prod-batch-row').first();
        await expect(updatedRow.locator('.badge-status')).toContainText(/In Progress/i);

        // 2. Transition In Progress -> Completed
        const completeBtn = updatedRow.locator('.btn-action-complete');
        await expect(completeBtn).toBeVisible();
        await completeBtn.click();

        // Verify updated to Completed
        await expect(page.locator('body')).toContainText(/Completed/i);

        // 3. View Batch Detail View
        await page.fill('#prod_search_input', 'Workflow Lifecycle Test');
        await page.locator('#prod_filter_form').press('Enter');
        await page.waitForTimeout(500);

        const finalRow = page.locator('.prod-batch-row').first();
        await expect(finalRow.locator('.badge-status')).toContainText(/Completed/i);

        // Click detail link
        await finalRow.locator('.btn-action-view').click();
        await expect(page).toHaveURL(/\/admin\/production\/\d+/);

        // Inspect Detail Page
        await expect(page.locator('.prod-detail-banner')).toBeVisible();
        await expect(page.locator('.prod-timeline-row')).toBeVisible();
        await expect(page.locator('.prod-table')).toBeVisible();
    });

    test('4. Insufficient Stock Prevention: huge quantity rolls back and prevents negative stock', async ({ page }) => {
        await page.goto('/login');
        await page.fill('#username_input', 'admin@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');
        await page.goto('/admin/production');

        // Try to create batch directly as completed with 999,999 units
        await page.click('#btn_open_prod_modal');
        await page.selectOption('#prod_select_product', { index: 1 });
        await page.fill('#prod_input_qty', '999999');
        await page.selectOption('#prod_select_status', 'completed');
        await page.fill('#prod_textarea_notes', 'Stock Safety Overdraft Test');
        await page.click('#btn_submit_production');

        // Should return with error message
        await expect(page.locator('body')).toContainText(/Insufficient stock/i);
    });

    test('5. RBAC: Baker can log in & access production, Cashier is blocked', async ({ page }) => {
        // --- BAKER TEST ---
        await page.goto('/login');
        await page.fill('#username_input', 'baker@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');

        // Baker lands on /admin/production
        await expect(page).toHaveURL(/\/admin\/production/);
        await expect(page.locator('.prod-table-card')).toBeVisible();

        // Baker cannot access Users or Settings
        await page.goto('/admin/users');
        await expect(page).not.toHaveURL(/\/admin\/users/);

        await page.goto('/admin/settings');
        await expect(page).not.toHaveURL(/\/admin\/settings/);

        // Logout
        await page.goto('/logout');

        // --- CASHIER TEST ---
        await page.goto('/login');
        await page.fill('#username_input', 'cashier@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');
        await expect(page).toHaveURL(/\/cashier\/dashboard/);

        // Cashier cannot access Production
        await page.goto('/admin/production');
        await expect(page).not.toHaveURL(/\/admin\/production/);
    });

    test('6. Language Switcher (EN <-> KM) on Production Page', async ({ page }) => {
        await page.goto('/login');
        await page.fill('#username_input', 'admin@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');
        await page.goto('/admin/production');

        // Switch to Khmer
        await page.goto('/lang/km');
        await page.goto('/admin/production');
        await expect(page.locator('html')).toHaveAttribute('lang', 'km');
        await expect(page.locator('body')).toContainText(/ការផលិត/);

        // Switch back to English
        await page.goto('/lang/en');
        await page.goto('/admin/production');
        await expect(page.locator('html')).toHaveAttribute('lang', 'en');
        await expect(page.locator('body')).toContainText(/Production/);
    });

});
