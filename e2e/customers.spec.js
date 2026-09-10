import { test, expect } from '@playwright/test';

test.describe('Customer Management & POS Integration Suite', () => {

    test('1. Admin can access Customer Management, view stats, search, and CRUD customer', async ({ page }) => {
        // Login as Admin
        await page.goto('/login');
        await page.click('.role-tab-btn[data-role="admin"]');
        await page.fill('#username_input', 'admin@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');
        await expect(page).toHaveURL(/\/admin\/dashboard/);

        // Navigate to Customers via Sidebar
        await page.click('a.sidebar-nav-item[href*="/admin/customers"]');
        await expect(page).toHaveURL(/\/admin\/customers/);
        await expect(page.locator('#customers_table')).toBeVisible();

        // Check metric cards
        await expect(page.locator('.customer-stats-grid')).toBeVisible();

        // Add a new customer
        const testCustomerName = 'Test Patron ' + Date.now();
        const testCustomerPhone = '012' + Math.floor(100000 + Math.random() * 900000);
        const testCustomerEmail = 'patron' + Date.now() + '@example.com';

        await page.click('#btn_open_add_customer_modal');
        await expect(page.locator('#add_customer_modal')).toHaveClass(/is-active/);

        await page.fill('#add_cust_name', testCustomerName);
        await page.fill('#add_cust_phone', testCustomerPhone);
        await page.fill('#add_cust_email', testCustomerEmail);
        await page.fill('#add_cust_address', '123 Baker Street, Phnom Penh');
        await page.fill('#add_cust_points', '50');

        await page.click('#add_customer_modal button[type="submit"]');

        // Verify customer appears in list
        await expect(page.locator('#customers_table')).toContainText(testCustomerName);

        // Test Live Search Filter
        const searchInput = page.locator('#customer_search_input');
        await searchInput.fill(testCustomerName);
        await searchInput.press('Enter');
        await expect(page.locator('#customers_table')).toContainText(testCustomerName);

        // Edit Customer
        const editBtn = page.locator(`tr:has-text("${testCustomerName}") .btn-edit-customer`);
        await editBtn.click();
        await expect(page.locator('#edit_customer_modal')).toHaveClass(/is-active/);

        const updatedName = testCustomerName + ' Updated';
        await page.fill('#edit_cust_name', updatedName);
        await page.fill('#edit_cust_points', '120'); // Elevates to Silver tier
        await page.click('#edit_customer_modal button[type="submit"]');

        // Clear search to see updated customer
        await page.goto('/admin/customers');
        await expect(page.locator('#customers_table')).toContainText(updatedName);
        await expect(page.locator(`tr:has-text("${updatedName}")`)).toContainText('Silver');

        // Delete Customer
        page.on('dialog', dialog => dialog.accept());
        const deleteBtn = page.locator(`tr:has-text("${updatedName}") button.delete`);
        await deleteBtn.click();

        // Customer removed
        await page.waitForTimeout(500);
        await expect(page.locator('#customers_table')).not.toContainText(updatedName);
    });

    test('2. Customer Detail & Purchase History View', async ({ page }) => {
        // Login as Admin
        await page.goto('/login');
        await page.click('.role-tab-btn[data-role="admin"]');
        await page.fill('#username_input', 'admin@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');

        // Go to Customers
        await page.goto('/admin/customers');
        await expect(page.locator('#customers_table')).toBeVisible();

        // View first customer detail (e.g. John Doe)
        const viewLink = page.locator('#customers_table a.btn-icon-action').first();
        await viewLink.click();

        // Verify Customer Show Page
        await expect(page).toHaveURL(/\/admin\/customers\/\d+/);
        await expect(page.locator('.cust-profile-card')).toBeVisible();
        await expect(page.locator('.loyalty-progress-container')).toBeVisible();
        await expect(page.locator('.cust-history-area')).toBeVisible();
        await expect(page.locator('.cust-history-stats-grid')).toBeVisible();
    });

    test('3. Role Permissions: Cashier is blocked from admin customers, Manager can access', async ({ page }) => {
        // Login as Cashier
        await page.goto('/login');
        await page.click('.role-tab-btn[data-role="cashier"]');
        await page.fill('#username_input', 'cashier@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');
        await expect(page).toHaveURL(/\/cashier\/dashboard/);

        // Attempt accessing /admin/customers directly
        await page.goto('/admin/customers');
        await expect(page).not.toHaveURL(/\/admin\/customers/);

        // Logout
        await page.goto('/logout');

        // Login as Manager
        await page.goto('/login');
        await page.click('.role-tab-btn[data-role="manager"]');
        await page.fill('#username_input', 'manager@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');
        await expect(page).toHaveURL(/\/manager\/dashboard/);

        // Manager can view customers
        await page.goto('/admin/customers');
        await expect(page).toHaveURL(/\/admin\/customers/);
        await expect(page.locator('#customers_table')).toBeVisible();
    });

    test('4. POS Customer Integration: Search, Select, Remove, and Checkout with Customer', async ({ page }) => {
        // Login as Cashier
        await page.goto('/login');
        await page.click('.role-tab-btn[data-role="cashier"]');
        await page.fill('#username_input', 'cashier@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');
        await expect(page).toHaveURL(/\/cashier\/dashboard/);

        // Check Customer Search in POS
        const custSearchInput = page.locator('#pos_customer_search');
        await expect(custSearchInput).toBeVisible();

        // 4.1 Search Customer "John"
        await custSearchInput.fill('John');
        const dropdown = page.locator('#pos_cust_dropdown');
        await expect(dropdown).toBeVisible({ timeout: 4000 });
        const firstResult = dropdown.locator('.pos-cust-dropdown-item').first();
        await expect(firstResult).toContainText('John Doe');

        // 4.2 Select Customer
        await firstResult.click();

        // Selected card is visible
        const selectedCustCard = page.locator('#pos_selected_cust_card');
        await expect(selectedCustCard).toBeVisible();
        await expect(selectedCustCard).toContainText('John Doe');
        await expect(custSearchInput).not.toBeVisible();

        // 4.3 Test Removing Customer
        const removeBtn = page.locator('#btn_remove_selected_cust');
        await removeBtn.click();
        await expect(selectedCustCard).not.toBeVisible();
        await expect(custSearchInput).toBeVisible();

        // 4.4 Select Customer again for checkout
        await custSearchInput.fill('John');
        await expect(dropdown).toBeVisible({ timeout: 4000 });
        await dropdown.locator('.pos-cust-dropdown-item').first().click();
        await expect(selectedCustCard).toBeVisible();

        // 4.5 Add Product to Cart
        const firstProductCard = page.locator('.pos-item-card:not(.is-out-of-stock)').first();
        await firstProductCard.click();
        await expect(page.locator('.pos-cart-item').first()).toBeVisible();

        // 4.6 Complete Sale with Cash
        const completeSaleBtn = page.locator('#btn_complete_sale');
        await expect(completeSaleBtn).toBeEnabled();
        await completeSaleBtn.click();

        // 4.7 Verify Receipt Modal displays Customer info & Loyalty Points
        const receiptModal = page.locator('#pos_receipt_modal');
        await expect(receiptModal).toHaveClass(/is-active/);
        await expect(page.locator('#slip_customer')).toHaveText('John Doe');
        await expect(page.locator('#slip_loyalty_row')).toBeVisible();
        await expect(page.locator('#slip_loyalty_info')).toContainText('pts');

        // Close Receipt Modal
        await page.click('#btn_receipt_close');
        await expect(receiptModal).not.toHaveClass(/is-active/);

        // Cart & Customer reset to walk-in
        await expect(page.locator('#pos_cart_empty_state')).toBeVisible();
        await expect(custSearchInput).toBeVisible();
    });

    test('5. Language Switcher (EN <-> KM) on Customer Pages', async ({ page }) => {
        // Login as Admin
        await page.goto('/login');
        await page.click('.role-tab-btn[data-role="admin"]');
        await page.fill('#username_input', 'admin@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');

        // Go to Customers
        await page.goto('/admin/customers');

        // Switch to Khmer
        await page.goto('/lang/km');
        await page.goto('/admin/customers');
        await expect(page.locator('#btn_open_add_customer_modal')).toContainText('បន្ថែមអតិថិជន');

        // Switch back to English
        await page.goto('/lang/en');
        await page.goto('/admin/customers');
        await expect(page.locator('#btn_open_add_customer_modal')).toContainText('Add Customer');
    });
});
