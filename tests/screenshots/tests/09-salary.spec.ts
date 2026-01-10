import { test, expect } from '@playwright/test';
import { highlightElement, clearHighlights, annotateElements } from '../utils/highlight';

const outputDir = '../../web/images/docs/salary';

test.describe('Salary Screenshots', () => {

  test.beforeEach(async ({ page }) => {
    await clearHighlights(page);
  });

  test('salary-list', async ({ page }) => {
    await page.goto('/2/salary');
    await page.waitForLoadState('networkidle');

    await page.screenshot({
      path: `${outputDir}/salary-list.png`,
    });
  });

  test('salary-calculate', async ({ page }) => {
    await page.goto('/2/salary/calculate');
    await page.waitForLoadState('networkidle');

    await page.screenshot({
      path: `${outputDir}/salary-calculate.png`,
      fullPage: true,
    });
  });

  test('salary-history', async ({ page }) => {
    await page.goto('/2/salary');
    await page.waitForLoadState('networkidle');

    // Ищем историю выплат
    const historyLink = await page.$('a[href*="history"], a[href*="salary/view"]');
    if (historyLink) {
      await historyLink.click();
      await page.waitForLoadState('networkidle');

      await page.screenshot({
        path: `${outputDir}/salary-history.png`,
      });
    }
  });

});
