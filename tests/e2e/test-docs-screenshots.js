// @ts-check
const { chromium } = require('@playwright/test');

const BASE_URL = 'http://educrm.loc';

async function testDocsScreenshots() {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: { width: 1400, height: 900 },
    locale: 'ru-RU'
  });
  const page = await context.newPage();

  console.log('Testing docs screenshots...\n');

  // Go to analytics-kpi docs page
  console.log('Navigating to /docs/new-features/analytics-kpi...');
  await page.goto(BASE_URL + '/docs/new-features/analytics-kpi');
  await page.waitForLoadState('networkidle');

  const content = await page.content();

  // Check for errors in the page
  if (content.includes('Error') || content.includes('Exception') || content.includes('not found')) {
    console.log('❌ Page contains error');
    const title = await page.title();
    console.log('Page title:', title);
  }

  // Find all img tags
  const images = await page.locator('img').all();
  console.log('\nFound', images.length, 'images on page\n');

  for (let i = 0; i < images.length; i++) {
    const img = images[i];
    const src = await img.getAttribute('src');
    const alt = await img.getAttribute('alt');
    console.log(`Image ${i + 1}:`);
    console.log('  src:', src);
    console.log('  alt:', alt);

    // Check if image loaded
    const isVisible = await img.isVisible();
    const naturalWidth = await img.evaluate((el) => el.naturalWidth);
    console.log('  visible:', isVisible);
    console.log('  naturalWidth:', naturalWidth);
    console.log('  loaded:', naturalWidth > 0 ? '✓' : '❌');
    console.log('');
  }

  // Take screenshot of the page
  await page.screenshot({
    path: 'C:/xampp8/htdocs/qazeducrm/test-results/docs-analytics-page.png',
    fullPage: true
  });
  console.log('Screenshot saved to test-results/docs-analytics-page.png');

  await browser.close();
}

testDocsScreenshots().catch(console.error);
