import { test, expect } from '@playwright/test';
import { highlightElement, clearHighlights, annotateElements } from '../utils/highlight';

const outputDir = '../../web/images/docs/groups';

test.describe('Groups Screenshots', () => {

  test.beforeEach(async ({ page }) => {
    await clearHighlights(page);
  });

  test('groups-list', async ({ page }) => {
    await page.goto('/2/group');
    await page.waitForLoadState('networkidle');

    await page.screenshot({
      path: `${outputDir}/groups-list.png`,
    });
  });

  test('groups-list-annotated', async ({ page }) => {
    await page.goto('/2/group');
    await page.waitForLoadState('networkidle');

    await annotateElements(page, [
      { selector: '.btn-primary, a[href*="create"]', label: '1. Добавить группу' },
    ]);

    await page.screenshot({
      path: `${outputDir}/groups-list-annotated.png`,
    });
  });

  test('group-create', async ({ page }) => {
    await page.goto('/2/group/create');
    await page.waitForLoadState('networkidle');

    await page.screenshot({
      path: `${outputDir}/group-create.png`,
      fullPage: true,
    });
  });

  test('group-view', async ({ page }) => {
    await page.goto('/2/group');
    await page.waitForLoadState('networkidle');

    // Кликаем на первую группу
    const viewLink = await page.$('a[href*="/group/view"], a[href*="/group/"][title*="Просмотр"], .view-link, table tbody tr:first-child a');
    if (viewLink) {
      await viewLink.click();
      await page.waitForLoadState('networkidle');

      await page.screenshot({
        path: `${outputDir}/group-view.png`,
        fullPage: true,
      });
    }
  });

});
