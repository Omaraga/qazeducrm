// @ts-check
const { chromium } = require('@playwright/test');
const path = require('path');

const BASE_URL = 'http://educrm.loc';
const SCREENSHOTS_DIR = path.join(__dirname, '../../web/uploads/docs');

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

async function takeScreenshots() {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: { width: 1400, height: 900 },
    locale: 'ru-RU'
  });
  const page = await context.newPage();

  // Login first
  await login(page);
  console.log('Logged in successfully');

  // ===== TRIAL LESSONS MODULE =====
  console.log('Taking Trial Lessons screenshots...');

  // Trial index page
  await page.goto(BASE_URL + '/1/trial');
  await page.waitForLoadState('networkidle');
  await page.screenshot({
    path: path.join(SCREENSHOTS_DIR, 'trial-index.png'),
    fullPage: false
  });
  console.log('  - trial-index.png');

  // Trial create page
  await page.goto(BASE_URL + '/1/trial/create');
  await page.waitForLoadState('networkidle');
  await page.screenshot({
    path: path.join(SCREENSHOTS_DIR, 'trial-create.png'),
    fullPage: false
  });
  console.log('  - trial-create.png');

  // ===== HOMEWORK MODULE =====
  console.log('Taking Homework screenshots...');

  // Homework index page
  await page.goto(BASE_URL + '/1/homework');
  await page.waitForLoadState('networkidle');
  await page.screenshot({
    path: path.join(SCREENSHOTS_DIR, 'homework-index.png'),
    fullPage: false
  });
  console.log('  - homework-index.png');

  // Homework create page
  await page.goto(BASE_URL + '/1/homework/create');
  await page.waitForLoadState('networkidle');
  await page.screenshot({
    path: path.join(SCREENSHOTS_DIR, 'homework-create.png'),
    fullPage: false
  });
  console.log('  - homework-create.png');

  // ===== ANALYTICS DASHBOARD =====
  console.log('Taking Analytics screenshots...');

  // Analytics page
  await page.goto(BASE_URL + '/1/reports/analytics');
  await page.waitForLoadState('networkidle');
  await page.screenshot({
    path: path.join(SCREENSHOTS_DIR, 'analytics-dashboard.png'),
    fullPage: false
  });
  console.log('  - analytics-dashboard.png');

  // Full page analytics
  await page.screenshot({
    path: path.join(SCREENSHOTS_DIR, 'analytics-dashboard-full.png'),
    fullPage: true
  });
  console.log('  - analytics-dashboard-full.png');

  // ===== MENU SCREENSHOTS =====
  console.log('Taking Menu screenshots...');

  // Main menu with new items visible
  await page.goto(BASE_URL + '/1/default');
  await page.waitForLoadState('networkidle');
  // Take screenshot of sidebar
  await page.screenshot({
    path: path.join(SCREENSHOTS_DIR, 'menu-new-items.png'),
    fullPage: false
  });
  console.log('  - menu-new-items.png');

  await browser.close();
  console.log('\nAll screenshots saved to: ' + SCREENSHOTS_DIR);
}

takeScreenshots().catch(console.error);
