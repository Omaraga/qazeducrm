const { chromium } = require('playwright');

(async () => {
    const browser = await chromium.launch();
    const context = await browser.newContext({ viewport: { width: 1400, height: 900 } });
    const page = await context.newPage();

    // Login
    await page.goto('http://educrm.loc/site/login');
    await page.fill('input[name="LoginForm[username]"]', 'admin@admin.kz');
    await page.fill('input[name="LoginForm[password]"]', '123456789');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);

    // Go to homework view page
    await page.goto('http://educrm.loc/2/homework/view/4');
    await page.waitForLoadState('networkidle');

    // Take screenshot
    await page.screenshot({ path: 'tests/e2e/homework-view-after.png', fullPage: true });
    console.log('Screenshot saved to tests/e2e/homework-view-after.png');

    await browser.close();
})();
