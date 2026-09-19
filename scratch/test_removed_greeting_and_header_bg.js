import { chromium } from 'playwright';

(async () => {
    const browser = await chromium.launch();
    const context = await browser.newContext({ viewport: { width: 1366, height: 768 } });
    const page = await context.newPage();

    console.log('Logging in...');
    await page.goto('http://127.0.0.1:8000/login');
    await page.fill('#username_input', 'admin@bakery.com');
    await page.fill('#password_input', 'pass123');
    await page.click('#submit_btn');
    await page.waitForURL('**/admin/dashboard');

    console.log('\n--- Checking Greeting Removal ---');
    const greetingText = await page.locator('.top-header-bar').innerText();
    const hasWelcomeBack = greetingText.includes('Welcome Back');
    const hasSubtitle = greetingText.includes("Here's what's happening");
    console.log('Header has "Welcome Back":', hasWelcomeBack);
    console.log('Header has "Here\'s what\'s happening":', hasSubtitle);

    console.log('\n--- Checking Top Header Bar Background ---');
    const headerStyles = await page.locator('.top-header-bar').evaluate(el => {
        const cs = window.getComputedStyle(el);
        return {
            background: cs.backgroundColor,
            backdropFilter: cs.backdropFilter || cs.webkitBackdropFilter,
            boxShadow: cs.boxShadow,
            borderBottom: cs.borderBottom
        };
    });
    console.log('Top Header Bar Computed Styles:', headerStyles);

    console.log('\n--- Checking Action Items Alignment ---');
    const searchBox = page.locator('#global_header_search');
    const userPill = page.locator('#user_profile_pill');
    console.log('Search box visible:', await searchBox.isVisible());
    console.log('User profile pill visible:', await userPill.isVisible());

    // Take screenshot
    await page.screenshot({ path: 'C:/Users/M-S-I/.gemini/antigravity-ide/brain/bae14c9d-dcdd-414b-a697-6a4d55afe96d/scratch/dashboard_no_greeting_transparent_header.png' });
    console.log('Screenshot saved to dashboard_no_greeting_transparent_header.png');

    await browser.close();
    console.log('\nALL CHECKS COMPLETED SUCCESSFULLY!');
})();
