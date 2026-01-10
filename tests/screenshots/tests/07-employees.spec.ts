import { test, expect } from '@playwright/test';
import { highlightElement, clearHighlights, annotateElements } from '../utils/highlight';

const outputDir = '../../web/images/docs/employees';

test.describe('Employees Screenshots', () => {

  test.beforeEach(async ({ page }) => {
    await clearHighlights(page);
  });

  test('employees-list', async ({ page }) => {
    await page.goto('/2/user');
    await page.waitForLoadState('networkidle');

    await page.screenshot({
      path: `${outputDir}/employees-list.png`,
    });
  });

  test('employee-create', async ({ page }) => {
    await page.goto('/2/user/create');
    await page.waitForLoadState('networkidle');

    await page.screenshot({
      path: `${outputDir}/employee-create.png`,
      fullPage: true,
    });
  });

  test('employee-view', async ({ page }) => {
    await page.goto('/2/user');
    await page.waitForLoadState('networkidle');

    // Ищем ссылку на просмотр сотрудника
    const viewLink = await page.$('a[href*="/user/view"], table tbody tr:first-child td:last-child a');
    if (viewLink) {
      await viewLink.click();
      await page.waitForLoadState('networkidle');

      await page.screenshot({
        path: `${outputDir}/employee-view.png`,
        fullPage: true,
      });
    } else {
      // Если нет сотрудников, делаем скриншот списка
      await page.screenshot({
        path: `${outputDir}/employee-view.png`,
        fullPage: true,
      });
    }
  });

});
