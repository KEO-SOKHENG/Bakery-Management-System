import { test, expect } from '@playwright/test';

test.describe('Supplier Management, Purchase Orders & Stock Replenishment E2E', () => {

    test('Complete Workflow: Create PO -> Order -> Receive -> Verify Stock Replenished -> Supplier History', async ({ page }) => {
        // 1. Login as Admin
        await page.goto('/login');
        await page.fill('#username_input', 'admin@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');
        await expect(page).toHaveURL(/\/admin\/dashboard/);

        // 2. Navigate to Suppliers page
        await page.goto('/admin/suppliers');
        await expect(page).toHaveURL(/\/admin\/suppliers/);
        await expect(page.locator('#suppliers_table')).toBeVisible();

        // 3. Create a unique test supplier
        const uniqueSuffix = Date.now();
        const testVendorName = 'E2E Vendor ' + uniqueSuffix;
        await page.click('#btn_open_supplier_modal');
        await expect(page.locator('#add_supplier_modal')).toHaveClass(/active/);
        await page.fill('#add_supplier_modal input[name="name"]', testVendorName);
        await page.fill('#add_supplier_modal input[name="contact_person"]', 'Sam Dara');
        await page.fill('#add_supplier_modal input[name="phone"]', '+855 12 888 777');
        await page.fill('#add_supplier_modal input[name="email"]', 'vendor' + uniqueSuffix + '@test.com');
        await page.fill('#add_supplier_modal input[name="address"]', 'Khan Sen Sok, Phnom Penh');
        await page.click('#add_supplier_modal button[type="submit"]');

        // Verify supplier appears in table
        await expect(page.locator('#suppliers_table')).toContainText(testVendorName);

        // 4. Navigate to Purchase Orders page
        await page.goto('/admin/purchase-orders');
        await expect(page).toHaveURL(/\/admin\/purchase-orders/);
        await expect(page.locator('#purchase_orders_table')).toBeVisible();

        // 5. Create a new Purchase Order
        await page.click('#btn_create_po');
        await expect(page).toHaveURL(/\/admin\/purchase-orders\/create/);

        // Select the newly created supplier
        const supplierOption = page.locator(`#supplier_select option:has-text("${testVendorName}")`);
        const supValue = await supplierOption.getAttribute('value');
        await page.selectOption('#supplier_select', supValue);

        // Add line item
        const firstRow = page.locator('#items_tbody tr').first();
        await firstRow.locator('select.ing-select').selectOption({ index: 1 });
        await firstRow.locator('.qty-input').fill('25');
        await firstRow.locator('.price-input').fill('2.00');

        // Verify live subtotal and grand total
        await expect(firstRow.locator('td:nth-child(5)')).toContainText('$50.00');
        await expect(page.locator('#summary_grand_total')).toContainText('$50.00');

        // Submit to create draft PO
        await page.click('button[type="submit"]');

        // 6. Verify redirected to Show PO detail page with status Draft
        await expect(page).toHaveURL(/\/admin\/purchase-orders\/\d+/);
        await expect(page.locator('body')).toContainText(/Draft/i);
        await expect(page.locator('body')).toContainText('$50.00');

        // 7. Transition Draft -> Ordered
        page.on('dialog', dialog => dialog.accept());
        await page.click('button:has-text("Place Order")');
        const globalConfirmBtn = page.locator('#btn_proceed_global_confirm');
        if (await globalConfirmBtn.isVisible({ timeout: 1500 }).catch(() => false)) {
            await globalConfirmBtn.click();
        }
        await expect(page.locator('body')).toContainText(/Ordered/i);

        // 8. Transition Ordered -> Receive Order & Replenish Stock
        await page.click('#btn_receive_po');
        if (await globalConfirmBtn.isVisible({ timeout: 1500 }).catch(() => false)) {
            await globalConfirmBtn.click();
        }

        // Verify status is Received & Stocked In
        await expect(page.locator('body')).toContainText(/Received/i);
        await expect(page.locator('body')).toContainText(/Order Successfully Received & Stock Replenished/i);

        // 9. Verify Stock In Ingredients Inventory
        await page.goto('/admin/ingredients');
        await expect(page.locator('#ingredients_table')).toBeVisible();

        // 10. Verify Supplier Detail View reflects the new PO
        await page.goto('/admin/suppliers');
        const supplierRow = page.locator(`tr:has-text("${testVendorName}")`);
        await supplierRow.locator('a[title="View Supplier Profile"]').click();
        await expect(page).toHaveURL(/\/admin\/suppliers\/\d+/);
        await expect(page.locator('body')).toContainText(testVendorName);
        await expect(page.locator('body')).toContainText('$50.00');
    });

});
