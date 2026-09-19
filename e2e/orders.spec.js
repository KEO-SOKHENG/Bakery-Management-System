import { test, expect } from '@playwright/test';

test.describe('Order Management & POS Integration End-to-End Suite', () => {

    test.beforeEach(async ({ page }) => {
        // Log in as Admin
        await page.goto('/login');
        await page.fill('#username_input', 'admin@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');
        await expect(page).toHaveURL(/\/admin\/dashboard/);
    });

    test('1. Order list loads with KPI summary cards, filter tabs, and search', async ({ page }) => {
        await page.goto('/admin/orders');
        await expect(page).toHaveURL(/\/admin\/orders/);

        // Verify KPI metrics cards
        await expect(page.locator('#kpi_total_orders')).toBeVisible();
        await expect(page.locator('#kpi_pending_orders')).toBeVisible();
        await expect(page.locator('#kpi_ready_orders')).toBeVisible();
        await expect(page.locator('#kpi_total_revenue')).toBeVisible();

        // Verify filter tabs bar
        await expect(page.locator('#order_status_tabs')).toBeVisible();
        await expect(page.locator('#order_status_tabs a.filter-btn').first()).toBeVisible();

        // Verify search input
        const searchInput = page.locator('#order_search_input');
        await expect(searchInput).toBeVisible();

        // Verify orders table
        const ordersTable = page.locator('.orders-table');
        await expect(ordersTable).toBeVisible();
    });

    test('2. Can create a Custom Cake Order with special instructions and view details', async ({ page }) => {
        await page.goto('/admin/orders');

        // Click "Create New Order" button
        const createBtn = page.locator('#btn_create_order');
        await expect(createBtn).toBeVisible();
        await createBtn.click();

        await expect(page).toHaveURL(/\/admin\/orders\/create/);

        // Fill customer name
        await page.fill('#customer_name', 'Chan Dara');

        // Check custom cake box
        const customCheck = page.locator('#is_custom');
        await customCheck.check();
        await expect(customCheck).toBeChecked();

        // Fill special instructions
        await page.fill('#special_instructions', '2 Tiers Vanilla with White Chocolate Fondant & Gold Leaves');

        // Select first product in items dropdown
        const productSelect = page.locator('.item-product-select').first();
        const inStockValue = await productSelect.locator('option').evaluateAll(options => {
            const match = options.find(o => {
                const m = o.text.match(/\[Stock:\s*(\d+)\]/);
                return o.value && m && parseInt(m[1]) >= 2;
            });
            return match ? match.value : (options[3] ? options[3].value : options[1]?.value);
        });
        await productSelect.selectOption(inStockValue);
        await productSelect.dispatchEvent('change');

        // Adjust quantity to 2
        const qtyInput = page.locator('.item-qty-input').first();
        await qtyInput.fill('2');
        await qtyInput.dispatchEvent('input');

        // Select payment method KHQR
        await page.selectOption('#payment_method', 'qr_code');

        // Submit order
        await page.click('#btn_submit_order');

        // Arrives at Order Details page
        await expect(page).toHaveURL(/\/admin\/orders\/\d+/);
        await expect(page.locator('.detail-card').first()).toBeVisible();
        await expect(page.locator('body')).toContainText('Chan Dara');
        await expect(page.locator('body')).toContainText('Custom Cake');
        await expect(page.locator('body')).toContainText('2 Tiers Vanilla with White Chocolate Fondant');

        // Verify Timeline Stepper is visible
        await expect(page.locator('.order-stepper')).toBeVisible();
    });

    test('3. Status workflow transitions: Pending -> Preparing -> Ready for Pickup -> Completed', async ({ page }) => {
        // Create an order via UI to transition
        await page.goto('/admin/orders/create');
        await page.fill('#customer_name', 'Workflow Tester');

        const productSelect = page.locator('.item-product-select').first();
        const inStockValue = await productSelect.locator('option').evaluateAll(options => {
            const match = options.find(o => o.value && !o.text.includes('Stock: 0'));
            return match ? match.value : (options[2] ? options[2].value : options[1]?.value);
        });
        await productSelect.selectOption(inStockValue);
        await productSelect.dispatchEvent('change');

        await page.selectOption('#order_status', 'pending');
        await page.click('#btn_submit_order');

        await expect(page).toHaveURL(/\/admin\/orders\/\d+/);

        // Step 1: Click "Start Preparing / Baking"
        const prepBtn = page.locator('#btn_status_preparing');
        await expect(prepBtn).toBeVisible();
        await prepBtn.click();
        await expect(page.locator('body')).toContainText(/status updated to Preparing/i);

        // Step 2: Click "Mark Ready for Pickup"
        const readyBtn = page.locator('#btn_status_ready');
        await expect(readyBtn).toBeVisible();
        await readyBtn.click();
        await expect(page.locator('body')).toContainText(/status updated to Ready For Pickup/i);

        // Step 3: Click "Complete Order"
        const completeBtn = page.locator('#btn_status_complete');
        await expect(completeBtn).toBeVisible();
        await completeBtn.click();
        await expect(page.locator('body')).toContainText(/status updated to Completed/i);
        await expect(page.locator('body')).toContainText('Order is Fully Completed');
    });

    test('4. Thermal Receipt modal opens and contains order summary', async ({ page }) => {
        await page.goto('/admin/orders');

        // Click first order view icon
        const viewLink = page.locator('.btn-icon-action[title="View Order Details"]').first();
        if (await viewLink.count() > 0) {
            await viewLink.click();
            await expect(page).toHaveURL(/\/admin\/orders\/\d+/);

            // Click Print Receipt
            const printReceiptBtn = page.locator('#btn_open_receipt_modal');
            await expect(printReceiptBtn).toBeVisible();
            await printReceiptBtn.click();

            // Verify receipt modal appears
            const receiptModal = page.locator('#order_receipt_modal');
            await expect(receiptModal).toHaveClass(/is-active/);
            await expect(page.locator('#thermal_slip_print_area')).toBeVisible();

            // Close modal
            await page.click('#btn_close_receipt_modal');
            await expect(receiptModal).not.toHaveClass(/is-active/);
        }
    });

    test('5. Orders created via POS terminal appear in Order Management', async ({ page }) => {
        // Go to POS Terminal
        await page.goto('/pos');
        await expect(page.locator('.pos-screen-layout')).toBeVisible();

        // Add first in-stock product
        const firstProduct = page.locator('.pos-item-card:not(.is-out-of-stock)').first();
        await expect(firstProduct).toBeVisible();
        await firstProduct.click();

        // Complete sale
        const completeSaleBtn = page.locator('#btn_complete_sale');
        await expect(completeSaleBtn).toBeEnabled();
        await completeSaleBtn.click();

        // Close thermal receipt modal
        const receiptCloseBtn = page.locator('#btn_receipt_close');
        await expect(receiptCloseBtn).toBeVisible();
        await receiptCloseBtn.click();

        // Navigate to Order Management
        await page.goto('/admin/orders');
        await expect(page).toHaveURL(/\/admin\/orders/);

        // Verify the latest completed order is at the top of the table
        const firstRowStatus = page.locator('.orders-table tbody tr').first().locator('.status-badge');
        await expect(firstRowStatus).toContainText(/Completed/i);
    });

    test('6. Clear All Orders button opens responsive easy delete modal with quick presets', async ({ page }) => {
        await page.goto('/admin/orders');
        await expect(page).toHaveURL(/\/admin\/orders/);

        const purgeBtn = page.locator('#btn_open_purge_modal');
        await expect(purgeBtn).toBeVisible();
        await expect(purgeBtn).toContainText('Clear All Orders');

        // Click to open purge modal
        await purgeBtn.click();
        const modal = page.locator('#purgeModal');
        await expect(modal).toBeVisible();

        // Check 1-click preset buttons are present
        const btnAll = page.locator('#btn_scope_all');
        const btn30 = page.locator('#btn_scope_30');
        const btn90 = page.locator('#btn_scope_90');
        const btnCancelled = page.locator('#btn_scope_cancelled');

        await expect(btnAll).toBeVisible();
        await expect(btn30).toBeVisible();
        await expect(btn90).toBeVisible();
        await expect(btnCancelled).toBeVisible();

        // Select 'Older than 30d' preset
        await btn30.click();
        await expect(page.locator('#form_purge_scope')).toHaveValue('days');
        await expect(page.locator('#form_purge_days')).toHaveValue('30');

        // Select 'All Orders' preset
        await btnAll.click();
        await expect(page.locator('#form_purge_scope')).toHaveValue('all');

        // Submit button is immediately ready to click (no typing required for easy user experience)
        const submitBtn = page.locator('#btn_submit_purge');
        await expect(submitBtn).toBeVisible();
        await expect(submitBtn).toBeEnabled();

        // Close modal
        await page.click('button[onclick="closePurgeModal()"]');
        await expect(modal).toBeHidden();
    });
});

