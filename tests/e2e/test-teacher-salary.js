// @ts-check
const { chromium } = require('@playwright/test');

const BASE_URL = 'http://educrm.loc';

const TEST_USER = {
  email: 'admin@admin.kz',
  password: '123456789'
};

async function login(page) {
  await page.goto(BASE_URL + '/login');
  await page.fill('input[name="LoginForm[username]"]', TEST_USER.email);
  await page.fill('input[name="LoginForm[password]"]', TEST_USER.password);
  await page.click('button[name="login-button"]');
  await page.waitForLoadState('networkidle');
}

async function testTeacherSalary() {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: { width: 1400, height: 900 },
    locale: 'ru-RU'
  });
  const page = await context.newPage();

  console.log('Testing Teacher Salary Report...\n');

  await login(page);
  console.log('✓ Logged in');

  // Test organization 2
  console.log('\nNavigating to /2/reports/view?type=teachers-salary...');
  const response = await page.goto(BASE_URL + '/2/reports/view?type=teachers-salary');
  console.log('Response status:', response.status());

  await page.waitForLoadState('networkidle');

  const content = await page.content();

  // Check for PHP errors
  const hasError = content.includes('Class') && content.includes('not found');
  const hasFatalError = content.includes('Fatal error');
  const hasException = content.includes('Exception');

  if (hasError || hasFatalError) {
    console.log('❌ PHP Error found!');
    const title = await page.title();
    console.log('Page title:', title);
  } else if (hasException && response.status() >= 400) {
    console.log('❌ Exception found!');
    const title = await page.title();
    console.log('Page title:', title);
  } else {
    console.log('✓ Page loaded without class errors');
  }

  // Check page title
  const title = await page.title();
  console.log('Page title:', title);

  await browser.close();
}

testTeacherSalary().catch(console.error);
