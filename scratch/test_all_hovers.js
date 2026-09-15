import { chromium } from 'playwright';

(async () => {
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext();
    const page = await context.newPage();

    console.log('Logging in...');
    await page.goto('http://127.0.0.1:8000/login');
    await page.fill('#username_input', 'admin@bakery.com');
    await page.fill('#password_input', 'pass123');
    await page.click('#submit_btn');
    await page.waitForURL('**/admin/dashboard');

    // TEST 1: Desktop Expanded (1366x768)
    console.log('\n--- 1. Desktop Expanded (1366x768) ---');
    await page.setViewportSize({ width: 1366, height: 768 });
    await page.goto('http://127.0.0.1:8000/admin/dashboard');
    await page.waitForTimeout(400);

    // Hover on Inactive Item (POS Terminal)
    const pos = page.locator('a[href*="/pos"]');
    await pos.hover();
    await page.waitForTimeout(200);
    const posHoverData = await pos.evaluate(el => {
        const s = window.getComputedStyle(el);
        const span = el.querySelector('span');
        return { bg: s.backgroundColor, color: s.color, spanColor: span ? window.getComputedStyle(span).color : null };
    });
    console.log('Desktop POS Hover:', posHoverData);
    await page.screenshot({ path: 'C:/Users/M-S-I/.gemini/antigravity-ide/brain/bae14c9d-dcdd-414b-a697-6a4d55afe96d/scratch/fixed_desktop_pos_hover.png' });

    // Hover on Notifications (Check that red badge stays WHITE)
    const notif = page.locator('.sidebar-nav-item[href*="notifications"]');
    await notif.hover();
    await page.waitForTimeout(200);
    const badgeData = await notif.evaluate(el => {
        const badge = el.querySelector('.sidebar-badge');
        return {
            badgeBg: badge ? window.getComputedStyle(badge).backgroundColor : null,
            badgeColor: badge ? window.getComputedStyle(badge).color : null
        };
    });
    console.log('Notifications Hover Badge:', badgeData);
    await page.screenshot({ path: 'C:/Users/M-S-I/.gemini/antigravity-ide/brain/bae14c9d-dcdd-414b-a697-6a4d55afe96d/scratch/fixed_notif_badge_hover.png' });

    // Hover on Dropdown Chevron
    const dropBtn = page.locator('.sidebar-dropdown-btn').first();
    if (await dropBtn.isVisible()) {
        await dropBtn.hover();
        await page.waitForTimeout(200);
        console.log('Dropdown Btn Hovered');
    }

    // TEST 2: Desktop Collapsed Rail (Click sidebar_close_btn)
    console.log('\n--- 2. Desktop Collapsed Rail Tooltip ---');
    const closeBtn = page.locator('#sidebar_close_btn');
    if (await closeBtn.isVisible()) {
        await closeBtn.click();
        await page.waitForTimeout(350);
    }
    // Hover on Orders in Collapsed Rail
    const ordersItem = page.locator('#sidebar_nav .sidebar-nav-item[href*="/admin/orders"]');
    await ordersItem.hover();
    await page.waitForTimeout(300);
    const tooltipData = await ordersItem.evaluate(el => {
        const span = el.querySelector('span:not(.sidebar-badge):not(.sidebar-dropdown-btn)');
        if (!span) return null;
        const rect = span.getBoundingClientRect();
        const s = window.getComputedStyle(span);
        return {
            display: s.display,
            position: s.position,
            left: rect.left,
            top: rect.top,
            text: span.textContent.trim(),
            bg: s.backgroundColor,
            color: s.color,
            visible: rect.width > 0 && rect.height > 0
        };
    });
    console.log('Collapsed Rail Orders Tooltip:', tooltipData);
    await page.screenshot({ path: 'C:/Users/M-S-I/.gemini/antigravity-ide/brain/bae14c9d-dcdd-414b-a697-6a4d55afe96d/scratch/fixed_desktop_rail_tooltip.png' });

    // TEST 3: Tablet / Scaled Laptop (1024x768) Collapsed Rail Tooltip
    console.log('\n--- 3. Tablet (1024x768) Collapsed Rail Tooltip ---');
    await page.setViewportSize({ width: 1024, height: 768 });
    await page.goto('http://127.0.0.1:8000/admin/dashboard');
    await page.waitForTimeout(400);

    const tabletPos = page.locator('#sidebar_nav .sidebar-nav-item[href*="/pos"]');
    await tabletPos.hover();
    await page.waitForTimeout(300);
    const tabletTooltip = await tabletPos.evaluate(el => {
        const span = el.querySelector('span:not(.sidebar-badge):not(.sidebar-dropdown-btn)');
        if (!span) return null;
        const rect = span.getBoundingClientRect();
        const s = window.getComputedStyle(span);
        return {
            display: s.display,
            position: s.position,
            left: rect.left,
            top: rect.top,
            text: span.textContent.trim(),
            bg: s.backgroundColor,
            color: s.color,
            visible: rect.width > 0 && rect.height > 0
        };
    });
    console.log('Tablet 1024 POS Tooltip:', tabletTooltip);
    await page.screenshot({ path: 'C:/Users/M-S-I/.gemini/antigravity-ide/brain/bae14c9d-dcdd-414b-a697-6a4d55afe96d/scratch/fixed_tablet_rail_tooltip.png' });

    // TEST 4: Tablet Expanded Drawer
    console.log('\n--- 4. Tablet Drawer Expanded ---');
    const toggleBtn = page.locator('#sidebar_toggle_btn');
    if (await toggleBtn.isVisible()) {
        await toggleBtn.click();
        await page.waitForTimeout(350);
        // Hover on active Dashboard
        const dash = page.locator('.bakery-sidebar.sidebar-expanded a[href*="dashboard"].active');
        if (await dash.isVisible()) {
            await dash.hover();
            const dashStyle = await dash.evaluate(el => {
                const s = window.getComputedStyle(el);
                return { bg: s.backgroundColor, color: s.color };
            });
            console.log('Tablet Expanded Active Dashboard Hover:', dashStyle);
        }
        await page.screenshot({ path: 'C:/Users/M-S-I/.gemini/antigravity-ide/brain/bae14c9d-dcdd-414b-a697-6a4d55afe96d/scratch/fixed_tablet_expanded_hover.png' });
    }

    await browser.close();
    console.log('ALL TESTS COMPLETED SUCCESSFULLY!');
})();
