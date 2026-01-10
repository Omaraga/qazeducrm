import { test, expect } from '@playwright/test';
import { highlightElement, clearHighlights, annotateElements } from '../utils/highlight';

const outputDir = '../../web/images/docs/settings';

test.describe('Settings Screenshots', () => {

  test.beforeEach(async ({ page }) => {
    await clearHighlights(page);
  });

  test('settings-menu', async ({ page }) => {
    // Переходим на страницу настроек чтобы меню раскрылось
    await page.goto('/2/subject');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(500);

    // Скриншот с раскрытым меню настроек
    await page.screenshot({
      path: `${outputDir}/settings-menu.png`,
    });
  });

  test('subjects-list', async ({ page }) => {
    await page.goto('/2/subject');
    await page.waitForLoadState('networkidle');

    await page.screenshot({
      path: `${outputDir}/subjects-list.png`,
    });
  });

  test('subject-create', async ({ page }) => {
    await page.goto('/2/subject/create');
    await page.waitForLoadState('networkidle');

    await page.screenshot({
      path: `${outputDir}/subject-create.png`,
    });
  });

  test('tariffs-list', async ({ page }) => {
    await page.goto('/2/tariff');
    await page.waitForLoadState('networkidle');

    await page.screenshot({
      path: `${outputDir}/tariffs-list.png`,
    });
  });

  test('tariff-create', async ({ page }) => {
    await page.goto('/2/tariff/create');
    await page.waitForLoadState('networkidle');

    await page.screenshot({
      path: `${outputDir}/tariff-create.png`,
      fullPage: true,
    });
  });

  test('tariff-view', async ({ page }) => {
    // Переходим на список и открываем первый тариф
    await page.goto('/2/tariff');
    await page.waitForLoadState('networkidle');

    const viewLink = await page.$('table tbody tr:first-child a[href*="/tariff/view"]');
    if (viewLink) {
      await viewLink.click();
      await page.waitForLoadState('networkidle');

      await page.screenshot({
        path: `${outputDir}/tariff-view.png`,
        fullPage: true,
      });
    }
  });

  test('rooms-list', async ({ page }) => {
    await page.goto('/2/room');
    await page.waitForLoadState('networkidle');

    await page.screenshot({
      path: `${outputDir}/rooms-list.png`,
    });
  });

  test('room-create', async ({ page }) => {
    await page.goto('/2/room/create');
    await page.waitForLoadState('networkidle');

    await page.screenshot({
      path: `${outputDir}/room-create.png`,
    });
  });

  test('payment-methods-list', async ({ page }) => {
    await page.goto('/2/pay-method');
    await page.waitForLoadState('networkidle');

    await page.screenshot({
      path: `${outputDir}/payment-methods-list.png`,
    });
  });

  test('organization-settings', async ({ page }) => {
    await page.goto('/2/settings/access');
    await page.waitForLoadState('networkidle');

    await page.screenshot({
      path: `${outputDir}/organization-settings.png`,
      fullPage: true,
    });
  });

});
