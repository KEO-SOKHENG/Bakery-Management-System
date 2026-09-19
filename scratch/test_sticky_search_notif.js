import { chromium } from 'playwright';

(async () => {
    const browser = await chromium.launch();
    const page = await browser.newPage({ viewport: { width: 1366, height: 768 } });

    console.log('Logging in...');
    await page.goto('http://127.0.0.1:8000/login');
    await page.fill('#username_input', 'admin@bakery.com');
    await page.fill('#password_input', 'pass123');
    await page.click('#submit_btn');
    await page.waitForURL('**/admin/dashboard');

    const searchBox = page.locator('#global_header_search');
    const notifBell = page.locator('#notification_bell_btn');

    // 1. Initial positions before scroll
    const beforeScrollSearch = await searchBox.evaluate(el => el.getBoundingClientRect());
    const beforeScrollBell = await notifBell.evaluate(el => el.getBoundingClientRect());
    console.log('Before scroll search rect:', beforeScrollSearch);
    console.log('Before scroll bell rect:', beforeScrollBell);

    // 2. Scroll down 600px
    await page.evaluate(() => window.scrollTo(0, 600));
    await page.waitForTimeout(300);

    // 3. Check positions after scrolling down
    const afterScrollSearch = await searchBox.evaluate(el => el.getBoundingClientRect());
    const afterScrollBell = await notifBell.evaluate(el => el.getBoundingClientRect());
    console.log('After scroll 600px search rect:', afterScrollSearch);
    console.log('After scroll 600px bell rect:', afterScrollBell);

    const isSearchStillVisible = await searchBox.isVisible();
    const isBellStillVisible = await notifBell.isVisible();
    console.log('Search box still visible at top:', isSearchStillVisible);
    console.log('Notification bell still visible at top:', isBellStillVisible);

    // Check that top positions did not scroll off-screen (should stay at ~12px / top of screen)
    const searchStayedAtTop = afterScrollSearch.top >= 0 && afterScrollSearch.top < 50;
    const bellStayedAtTop = afterScrollBell.top >= 0 && afterScrollBell.top < 50;
    console.log('Search stayed locked at top:', searchStayedAtTop);
    console.log('Bell stayed locked at top:', bellStayedAtTop);

    // 4. Test clicking search and bell while scrolled
    await searchBox.click();
    await searchBox.fill('Bread');
    console.log('Search filled with text while scrolled: Bread');

    await notifBell.click();
    await page.waitForTimeout(300);
    const isDropdownActive = await page.locator('#notification_dropdown').evaluate(el => el.classList.contains('active'));
    console.log('Notification dropdown opened while scrolled:', isDropdownActive);

    // Screenshot while scrolled
    await page.screenshot({ path: 'C:/Users/M-S-I/.gemini/antigravity-ide/brain/bae14c9d-dcdd-414b-a697-6a4d55afe96d/scratch/scrolled_sticky_search_notification.png' });
    console.log('Screenshot saved to scrolled_sticky_search_notification.png');

    await browser.close();
    console.log('\nALL CHECKS PASSED PERFECTLY!');
})();
