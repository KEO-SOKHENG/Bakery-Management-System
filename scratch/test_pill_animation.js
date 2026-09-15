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

    console.log('\n--- Test 1: Desktop 1366x768 Initial Pill Alignment ---');
    await page.setViewportSize({ width: 1366, height: 768 });
    await page.goto('http://127.0.0.1:8000/admin/dashboard');
    await page.waitForTimeout(600);

    const initialPill = await page.evaluate(() => {
        const pill = document.getElementById('nav_pointer_pill');
        const activeItem = document.querySelector('.sidebar-nav-item.active');
        if (!pill || !activeItem) return null;

        const pillRect = pill.getBoundingClientRect();
        const itemRect = activeItem.getBoundingClientRect();
        const pillStyle = window.getComputedStyle(pill);
        const itemStyle = window.getComputedStyle(activeItem);

        return {
            pillExists: true,
            pillOpacity: pillStyle.opacity,
            pillTransform: pillStyle.transform,
            pillWidth: pillRect.width,
            pillHeight: pillRect.height,
            pillBg: pillStyle.backgroundColor,
            pillRadius: pillStyle.borderRadius,
            itemWidth: itemRect.width,
            itemHeight: itemRect.height,
            itemColor: itemStyle.color,
            pillAlignedWithItem: Math.abs(pillRect.top - itemRect.top) < 3 && Math.abs(pillRect.left - itemRect.left) < 3
        };
    });
    console.log('Initial Pill State on Dashboard:', initialPill);
    await page.screenshot({ path: 'C:/Users/M-S-I/.gemini/antigravity-ide/brain/bae14c9d-dcdd-414b-a697-6a4d55afe96d/scratch/pill_step1_dashboard.png' });

    console.log('\n--- Test 2: Hover over POS Terminal (Sliding & Recoil) ---');
    const posItem = page.locator('.sidebar-nav-item[href*="/pos"]');
    await posItem.hover();
    
    // Wait for recoil to settle (300ms)
    await page.waitForTimeout(350);

    const posHoverPill = await page.evaluate(() => {
        const pill = document.getElementById('nav_pointer_pill');
        const pos = document.querySelector('.sidebar-nav-item[href*="/pos"]');
        const dash = document.querySelector('.sidebar-nav-item.active');
        if (!pill || !pos) return null;

        const pillRect = pill.getBoundingClientRect();
        const posRect = pos.getBoundingClientRect();
        const posStyle = window.getComputedStyle(pos);
        const dashStyle = dash ? window.getComputedStyle(dash) : null;

        return {
            pillWidth: pillRect.width,
            pillHeight: pillRect.height,
            posHeight: posRect.height,
            posTextColor: posStyle.color,
            posIsHighlighted: pos.classList.contains('pill-highlighted'),
            dashTextColor: dashStyle ? dashStyle.color : null,
            pillAlignedWithPos: Math.abs(pillRect.top - posRect.top) < 3 && Math.abs(pillRect.left - posRect.left) < 3
        };
    });
    console.log('Pill State on POS Hover:', posHoverPill);
    await page.screenshot({ path: 'C:/Users/M-S-I/.gemini/antigravity-ide/brain/bae14c9d-dcdd-414b-a697-6a4d55afe96d/scratch/pill_step2_pos_hover.png' });

    console.log('\n--- Test 3: Hover over Orders ---');
    const ordersItem = page.locator('.sidebar-nav-item[href*="/admin/orders"]');
    await ordersItem.hover();
    await page.waitForTimeout(350);

    const ordersHoverPill = await page.evaluate(() => {
        const pill = document.getElementById('nav_pointer_pill');
        const orders = document.querySelector('.sidebar-nav-item[href*="/admin/orders"]');
        if (!pill || !orders) return null;

        const pillRect = pill.getBoundingClientRect();
        const ordersRect = orders.getBoundingClientRect();
        const ordersStyle = window.getComputedStyle(orders);

        return {
            ordersTextColor: ordersStyle.color,
            ordersIsHighlighted: orders.classList.contains('pill-highlighted'),
            pillAlignedWithOrders: Math.abs(pillRect.top - ordersRect.top) < 3 && Math.abs(pillRect.left - ordersRect.left) < 3
        };
    });
    console.log('Pill State on Orders Hover:', ordersHoverPill);
    await page.screenshot({ path: 'C:/Users/M-S-I/.gemini/antigravity-ide/brain/bae14c9d-dcdd-414b-a697-6a4d55afe96d/scratch/pill_step3_orders_hover.png' });

    console.log('\n--- Test 4: Mouse leaves nav -> pill returns to Dashboard ---');
    // Move mouse over to main content
    await page.locator('.header-greeting').hover();
    await page.waitForTimeout(400);

    const returnPill = await page.evaluate(() => {
        const pill = document.getElementById('nav_pointer_pill');
        const dash = document.querySelector('.sidebar-nav-item.active');
        if (!pill || !dash) return null;

        const pillRect = pill.getBoundingClientRect();
        const dashRect = dash.getBoundingClientRect();
        const dashStyle = window.getComputedStyle(dash);

        return {
            dashTextColor: dashStyle.color,
            dashIsHighlighted: dash.classList.contains('pill-highlighted'),
            pillReturnedToDash: Math.abs(pillRect.top - dashRect.top) < 3 && Math.abs(pillRect.left - dashRect.left) < 3
        };
    });
    console.log('Pill Return State on Mouseleave:', returnPill);
    await page.screenshot({ path: 'C:/Users/M-S-I/.gemini/antigravity-ide/brain/bae14c9d-dcdd-414b-a697-6a4d55afe96d/scratch/pill_step4_returned_to_dash.png' });

    console.log('\n--- Test 5: Dark Theme Hover Test ---');
    await page.evaluate(() => {
        document.documentElement.setAttribute('data-theme', 'dark');
        window.sidebarPillRefresh();
    });
    await page.waitForTimeout(300);

    await posItem.hover();
    await page.waitForTimeout(350);

    const darkPill = await page.evaluate(() => {
        const pill = document.getElementById('nav_pointer_pill');
        const pos = document.querySelector('.sidebar-nav-item[href*="/pos"]');
        const dash = document.querySelector('.sidebar-nav-item.active');
        const pillStyle = window.getComputedStyle(pill);
        const posStyle = window.getComputedStyle(pos);
        const dashStyle = dash ? window.getComputedStyle(dash) : null;

        return {
            pillBgImage: pillStyle.backgroundImage,
            posTextColor: posStyle.color,
            dashTextColor: dashStyle ? dashStyle.color : null
        };
    });
    console.log('Dark Theme Pill & Text State:', darkPill);
    await page.screenshot({ path: 'C:/Users/M-S-I/.gemini/antigravity-ide/brain/bae14c9d-dcdd-414b-a697-6a4d55afe96d/scratch/pill_step5_dark_theme_hover.png' });

    await browser.close();
    console.log('\nAll tests completed successfully!');
})();
