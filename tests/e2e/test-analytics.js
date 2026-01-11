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

async function testAnalytics() {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: { width: 1400, height: 900 },
    locale: 'ru-RU'
  });
  const page = await context.newPage();

  console.log('Testing Analytics page...\n');

  // Login
  await login(page);
  console.log('✓ Logged in');

  // Go to analytics page
  console.log('\nNavigating to /1/reports/analytics...');
  const response = await page.goto(BASE_URL + '/1/reports/analytics');
  console.log('Response status:', response.status());

  await page.waitForLoadState('networkidle');

  // Get page content
  const content = await page.content();

  // Check for PHP errors
  const phpErrors = [
    'Fatal error',
    'Parse error',
    'ErrorException',
    'Undefined variable',
    'Undefined index',
    'Undefined array key',
    'Call to undefined',
    'Class not found',
    'Trying to access',
    'Cannot access'
  ];

  console.log('\nChecking for PHP errors...');
  for (const error of phpErrors) {
    if (content.includes(error)) {
      console.log('❌ Found error:', error);
      // Find the context around the error
      const index = content.indexOf(error);
      const start = Math.max(0, index - 100);
      const end = Math.min(content.length, index + 200);
      console.log('Context:', content.substring(start, end).replace(/<[^>]*>/g, ' ').substring(0, 300));
    }
  }

  // Check page title
  const title = await page.title();
  console.log('\nPage title:', title);

  // Check what's on the page
  console.log('\nChecking page content...');

  // Look for expected elements
  const hasAnalyticsTitle = content.includes('Аналитика') || content.includes('KPI') || content.includes('Analytics');
  console.log('Has Analytics/KPI title:', hasAnalyticsTitle ? '✓' : '❌');

  // Check for widgets/cards
  const cards = await page.locator('.card').count();
  console.log('Number of cards found:', cards);

  // Check for specific metrics
  const metricsToCheck = ['LTV', 'Конверсия', 'Посещаемость', 'Доход', 'Ученик'];
  for (const metric of metricsToCheck) {
    const found = content.includes(metric);
    console.log(`Metric "${metric}":`, found ? '✓' : '❌');
  }

  // Take screenshot
  await page.screenshot({
    path: 'C:/xampp8/htdocs/qazeducrm/test-results/analytics-test.png',
    fullPage: true
  });
  console.log('\nScreenshot saved to test-results/analytics-test.png');

  // Get text content to see what's displayed
  const bodyText = await page.locator('body').innerText();
  console.log('\n--- Page text content (first 2000 chars) ---');
  console.log(bodyText.substring(0, 2000));

  await browser.close();
}

testAnalytics().catch(console.error);
