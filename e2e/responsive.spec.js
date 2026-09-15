import { test, expect } from '@playwright/test';

const KEY_ROUTES = [
    { path: '/admin/dashboard', name: 'Dashboard' },
    { path: '/pos', name: 'POS Terminal' },
    { path: '/admin/orders', name: 'Orders' },
    { path: '/admin/products', name: 'Products' },
    { path: '/admin/ingredients', name: 'Inventory' },
    { path: '/admin/customers', name: 'Customers' },
    { path: '/admin/production', name: 'Production' },
    { path: '/admin/reports', name: 'Reports' },
    { path: '/admin/settings', name: 'Settings' },
];

test.describe('Responsive Design & Viewport Verification', () => {
    test.beforeEach(async ({ page }) => {
        // Log in as Admin
        await page.goto('/login');
        await page.fill('#username_input', 'admin@bakery.com');
        await page.fill('#password_input', 'pass123');
        await page.click('#submit_btn');
        await expect(page).toHaveURL(/\/admin\/dashboard/);
    });

    test('Zero horizontal window overflow across all key routes on Mobile & Tablet', async ({ page }) => {
        test.setTimeout(90000);
        const testViewports = [
            { width: 375, height: 812 },
            { width: 390, height: 844 },
            { width: 430, height: 932 },
            { width: 768, height: 1024 },
            { width: 1024, height: 768 },
            { width: 1280, height: 800 },
        ];

        for (const vp of testViewports) {
            await page.setViewportSize({ width: vp.width, height: vp.height });
            for (const route of KEY_ROUTES) {
                await page.goto(route.path);
                await page.waitForLoadState('domcontentloaded');
                await page.waitForTimeout(100);

                const overflow = await page.evaluate(() => {
                    const docWidth = document.documentElement.scrollWidth;
                    const winWidth = window.innerWidth;
                    return {
                        docWidth,
                        winWidth,
                        hasOverflow: docWidth > winWidth + 1, // allow 1px rounding
                    };
                });

                expect(
                    overflow.hasOverflow,
                    `Page ${route.name} (${route.path}) at ${vp.width}x${vp.height} has horizontal overflow: docWidth=${overflow.docWidth}, winWidth=${overflow.winWidth}`
                ).toBe(false);
            }
        }
    });

    test('Mobile Sidebar drawer toggle and click-outside backdrop dismissal', async ({ page }) => {
        await page.setViewportSize({ width: 390, height: 844 });
        await page.goto('/admin/dashboard');

        const sidebar = page.locator('#sidebar_nav');
        const toggleBtn = page.locator('#sidebar_toggle_btn');
        const backdrop = page.locator('#sidebar_mobile_backdrop');

        // Initially on mobile, sidebar should NOT have sidebar-expanded
        await expect(sidebar).not.toHaveClass(/sidebar-expanded/);
        await expect(backdrop).not.toBeVisible();

        // Click hamburger toggle to open
        await toggleBtn.click();
        await expect(sidebar).toHaveClass(/sidebar-expanded/);
        await expect(backdrop).toBeVisible();

        // Click backdrop to close
        await backdrop.click();
        await expect(sidebar).not.toHaveClass(/sidebar-expanded/);
        await expect(backdrop).not.toBeVisible();
    });

    test('Mobile Dashboard: 2-column KPI stat cards and 1-column quick actions', async ({ page }) => {
        await page.setViewportSize({ width: 375, height: 812 });
        await page.goto('/admin/dashboard');

        const statRow = page.locator('.dash-stat-row');
        await expect(statRow).toBeVisible();

        // Verify CSS grid columns computed value
        const statCols = await statRow.evaluate((el) => {
            return window.getComputedStyle(el).gridTemplateColumns.split(' ').length;
        });
        expect(statCols).toBe(2);

        const quickActions = page.locator('.quick-actions-grid');
        await expect(quickActions).toBeVisible();
        const qaCols = await quickActions.evaluate((el) => {
            return window.getComputedStyle(el).gridTemplateColumns.split(' ').length;
        });
        expect(qaCols).toBe(1);
    });

    test('Mobile POS: 2-column products, bottom cart trigger bar, and bottom-sheet drawer workflow', async ({ page }) => {
        await page.setViewportSize({ width: 390, height: 844 });
        await page.goto('/pos');

        // 1. Verify 2-column product grid
        const grid = page.locator('#pos_product_grid');
        await expect(grid).toBeVisible();
        const gridCols = await grid.evaluate((el) => {
            return window.getComputedStyle(el).gridTemplateColumns.split(' ').length;
        });
        expect(gridCols).toBe(2);

        // 2. Mobile bottom bar is visible
        const mobileBar = page.locator('#pos_mobile_cart_bar');
        await expect(mobileBar).toBeVisible();

        // 3. Cart panel initially hidden (not mobile-open)
        const cartPanel = page.locator('#pos_cart_panel');
        await expect(cartPanel).not.toHaveClass(/mobile-open/);

        // 4. Click a product to add to cart
        const productCard = page.locator('.pos-item-card:not(.is-out-of-stock)').first();
        await productCard.click();

        // Verify count updated in bottom bar
        const mobileCount = page.locator('#pos_mobile_cart_count');
        await expect(mobileCount).toHaveText('1');

        // 5. Open drawer via mobile bar click
        await mobileBar.click();
        await expect(cartPanel).toHaveClass(/mobile-open/);
        const cartBackdrop = page.locator('#pos_cart_backdrop');
        await expect(cartBackdrop).toBeVisible();

        // 6. Close drawer via close button
        const closeBtn = page.locator('#btn_close_cart_drawer');
        await closeBtn.click();
        await expect(cartPanel).not.toHaveClass(/mobile-open/);
        await expect(cartBackdrop).not.toBeVisible();
    });

    test('Tablet POS (768px-1199px): Side-by-side catalog and cart, no mobile bottom bar', async ({ page }) => {
        await page.setViewportSize({ width: 1024, height: 768 });
        await page.goto('/pos');

        const mobileBar = page.locator('#pos_mobile_cart_bar');
        await expect(mobileBar).toBeHidden();

        const cartPanel = page.locator('#pos_cart_panel');
        await expect(cartPanel).toBeVisible();

        const catalogPanel = page.locator('.pos-catalog-panel');
        await expect(catalogPanel).toBeVisible();
    });

    test('Desktop Preservation (>= 1200px): Desktop layout unchanged', async ({ page }) => {
        await page.setViewportSize({ width: 1440, height: 900 });
        await page.goto('/admin/dashboard');

        // Stat cards should have 4 columns
        const statRow = page.locator('.dash-stat-row');
        await expect(statRow).toBeVisible();
        const statCols = await statRow.evaluate((el) => {
            return window.getComputedStyle(el).gridTemplateColumns.split(' ').length;
        });
        expect(statCols).toBe(4);

        // Sidebar is part of desktop flow (not off-canvas)
        const sidebar = page.locator('#sidebar_nav');
        await expect(sidebar).toBeVisible();

        // POS on desktop
        await page.goto('/pos');
        const mobileBar = page.locator('#pos_mobile_cart_bar');
        await expect(mobileBar).toBeHidden();

        const cartPanel = page.locator('#pos_cart_panel');
        await expect(cartPanel).toBeVisible();
    });
});
