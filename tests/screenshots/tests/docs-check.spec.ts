import { test, expect } from '@playwright/test';

const outputDir = './output/docs-check';

test.describe('Documentation Page Check', () => {

  test('docs-index', async ({ page }) => {
    await page.goto('/docs');
    await page.waitForLoadState('networkidle');

    await page.screenshot({
      path: `${outputDir}/docs-index.png`,
      fullPage: true,
    });
  });

  test('docs-section-with-screenshots', async ({ page }) => {
    // Открываем секцию с контентом и скриншотами
    await page.goto('/docs/getting-started/first-login');
    await page.waitForLoadState('networkidle');

    await page.screenshot({
      path: `${outputDir}/docs-section.png`,
      fullPage: true,
    });
  });

  test('docs-pupils-section', async ({ page }) => {
    await page.goto('/docs/pupils/add-pupil');
    await page.waitForLoadState('networkidle');

    await page.screenshot({
      path: `${outputDir}/docs-pupils.png`,
      fullPage: true,
    });
  });

  test('docs-kanban-section', async ({ page }) => {
    await page.goto('/docs/leads/kanban-board');
    await page.waitForLoadState('networkidle');

    await page.screenshot({
      path: `${outputDir}/docs-kanban.png`,
      fullPage: true,
    });
  });

  test('docs-new-section-schedule', async ({ page }) => {
    await page.goto('/docs/schedule/create-lesson');
    await page.waitForLoadState('networkidle');

    await page.screenshot({
      path: `${outputDir}/docs-create-lesson.png`,
      fullPage: true,
    });
  });

  test('docs-new-section-salary', async ({ page }) => {
    await page.goto('/docs/salary/setup-rates');
    await page.waitForLoadState('networkidle');

    await page.screenshot({
      path: `${outputDir}/docs-salary-rates.png`,
      fullPage: true,
    });
  });

});
