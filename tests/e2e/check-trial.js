const { chromium } = require('playwright');

(async () => {
    const browser = await chromium.launch();
    const page = await browser.newPage({ viewport: { width: 1400, height: 900 } });
    
    await page.goto('http://educrm.loc/site/login');
    await page.fill('input[name="LoginForm[username]"]', 'admin@admin.kz');
    await page.fill('input[name="LoginForm[password]"]', '123456789');
    await page.click('button[type="submit"]');
    await page.waitForTimeout(3000);
    
    await page.goto('http://educrm.loc/2/trial/create');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);
    
    await page.screenshot({ path: 'tests/e2e/trial-create.png', fullPage: true });
    console.log('Screenshot saved: tests/e2e/trial-create.png');
    
    // Кликаем на поле даты
    await page.click('.date-picker');
    await page.waitForTimeout(500);
    
    await page.screenshot({ path: 'tests/e2e/trial-create-datepicker.png', fullPage: false });
    console.log('Screenshot with datepicker saved');
    
    await browser.close();
})();
