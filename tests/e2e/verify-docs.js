// @ts-check
const { chromium } = require('@playwright/test');

const BASE_URL = 'http://educrm.loc';

async function verifyDocs() {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: { width: 1400, height: 900 },
    locale: 'ru-RU'
  });
  const page = await context.newPage();

  console.log('Verifying documentation pages...\n');

  // Check docs main page
  console.log('1. Checking docs index page...');
  await page.goto(BASE_URL + '/docs');
  await page.waitForLoadState('networkidle');
  const docsTitle = await page.title();
  console.log('   Title:', docsTitle);

  // Check if "Новые функции" chapter exists
  const newFeaturesChapter = await page.locator('text=Новые функции').count();
  console.log('   "Новые функции" chapter found:', newFeaturesChapter > 0 ? 'YES' : 'NO');

  // Check Trial Lessons section
  console.log('\n2. Checking Trial Lessons section...');
  await page.goto(BASE_URL + '/docs/new-features/trial-lessons');
  await page.waitForLoadState('networkidle');
  const trialContent = await page.content();
  const hasTrialContent = trialContent.includes('Пробные занятия') && trialContent.includes('Обзор модуля');
  console.log('   Trial Lessons page loaded:', hasTrialContent ? 'YES' : 'NO');
  const hasTrialScreenshot = trialContent.includes('trial-index.png');
  console.log('   Screenshots referenced:', hasTrialScreenshot ? 'YES' : 'NO');

  // Check Homework section
  console.log('\n3. Checking Homework section...');
  await page.goto(BASE_URL + '/docs/new-features/homework');
  await page.waitForLoadState('networkidle');
  const homeworkContent = await page.content();
  const hasHomeworkContent = homeworkContent.includes('Домашние задания') && homeworkContent.includes('Создание задания');
  console.log('   Homework page loaded:', hasHomeworkContent ? 'YES' : 'NO');
  const hasHomeworkScreenshot = homeworkContent.includes('homework-index.png');
  console.log('   Screenshots referenced:', hasHomeworkScreenshot ? 'YES' : 'NO');

  // Check Analytics section
  console.log('\n4. Checking Analytics section...');
  await page.goto(BASE_URL + '/docs/new-features/analytics-kpi');
  await page.waitForLoadState('networkidle');
  const analyticsContent = await page.content();
  const hasAnalyticsContent = analyticsContent.includes('Аналитика и KPI') && analyticsContent.includes('LTV');
  console.log('   Analytics page loaded:', hasAnalyticsContent ? 'YES' : 'NO');
  const hasAnalyticsScreenshot = analyticsContent.includes('analytics-dashboard.png');
  console.log('   Screenshots referenced:', hasAnalyticsScreenshot ? 'YES' : 'NO');

  // Take screenshot of docs index showing new chapter
  console.log('\n5. Taking screenshot of docs index...');
  await page.goto(BASE_URL + '/docs');
  await page.waitForLoadState('networkidle');
  await page.screenshot({
    path: 'C:/xampp8/htdocs/qazeducrm/web/uploads/docs/docs-index-new-features.png',
    fullPage: true
  });
  console.log('   Screenshot saved: docs-index-new-features.png');

  // Take screenshot of new features chapter
  console.log('\n6. Taking screenshot of new features chapter...');
  await page.goto(BASE_URL + '/docs/new-features');
  await page.waitForLoadState('networkidle');
  await page.screenshot({
    path: 'C:/xampp8/htdocs/qazeducrm/web/uploads/docs/docs-new-features-chapter.png',
    fullPage: true
  });
  console.log('   Screenshot saved: docs-new-features-chapter.png');

  await browser.close();
  console.log('\n✅ Documentation verification complete!');
  console.log('   Visit http://educrm.loc/docs/new-features to see the new documentation');
}

verifyDocs().catch(console.error);
