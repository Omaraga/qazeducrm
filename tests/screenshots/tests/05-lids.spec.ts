import { test, expect } from '@playwright/test';
import { highlightElement, clearHighlights } from '../utils/highlight';

const outputDir = '../../web/images/docs/lids';

test.describe('Lids Screenshots', () => {

  test.beforeEach(async ({ page }) => {
    await clearHighlights(page);
  });

  test('lids-list', async ({ page }) => {
    await page.goto('/2/lids');
    await page.waitForLoadState('networkidle');

    await page.screenshot({
      path: `${outputDir}/lids-list.png`,
    });
  });

  test('lids-kanban', async ({ page }) => {
    await page.goto('/2/lids-funnel/kanban');
    await page.waitForLoadState('networkidle');

    // Ждём загрузки kanban доски
    await page.waitForTimeout(1000);

    await page.screenshot({
      path: `${outputDir}/lids-kanban.png`,
    });
  });

  test('lid-create', async ({ page }) => {
    await page.goto('/2/lids/create');
    await page.waitForLoadState('networkidle');

    await page.screenshot({
      path: `${outputDir}/lid-create.png`,
    });
  });

});
