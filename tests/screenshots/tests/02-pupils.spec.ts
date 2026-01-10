import { test, expect } from '@playwright/test';
import { highlightElement, clearHighlights, annotateElements } from '../utils/highlight';

const outputDir = '../../web/images/docs/pupils';

test.describe('Pupils Screenshots', () => {

  test.beforeEach(async ({ page }) => {
    await clearHighlights(page);
  });

  test('pupils-list', async ({ page }) => {
    await page.goto('/2/pupil');
    await page.waitForLoadState('networkidle');

    await page.screenshot({
      path: `${outputDir}/pupils-list.png`,
    });
  });

  test('pupils-list-with-annotations', async ({ page }) => {
    await page.goto('/2/pupil');
    await page.waitForLoadState('networkidle');

    // Аннотации для основных элементов
    await annotateElements(page, [
      { selector: '.btn-primary', label: '1. Добавить' },
      { selector: 'input[type="search"], .search-input, [name*="search"]', label: '2. Поиск' },
    ]);

    await page.screenshot({
      path: `${outputDir}/pupils-list-annotated.png`,
    });
  });

  test('pupil-create-form', async ({ page }) => {
    await page.goto('/2/pupil/create');
    await page.waitForLoadState('networkidle');

    await page.screenshot({
      path: `${outputDir}/pupil-create-form.png`,
      fullPage: true,
    });
  });

  test('pupil-view', async ({ page }) => {
    // Переходим на страницу списка и кликаем на первого ученика
    await page.goto('/2/pupil');
    await page.waitForLoadState('networkidle');

    // Пробуем найти ссылку на просмотр
    const viewLink = await page.$('a[href*="/pupil/view"], a[href*="/pupil/"][title*="Просмотр"], .view-link');
    if (viewLink) {
      await viewLink.click();
      await page.waitForLoadState('networkidle');

      await page.screenshot({
        path: `${outputDir}/pupil-view.png`,
      });
    }
  });

});
