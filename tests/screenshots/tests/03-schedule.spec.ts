import { test, expect } from '@playwright/test';
import { highlightElement, clearHighlights } from '../utils/highlight';

const outputDir = '../../web/images/docs/schedule';

test.describe('Schedule Screenshots', () => {

  test.beforeEach(async ({ page }) => {
    await clearHighlights(page);
  });

  test('schedule-calendar', async ({ page }) => {
    await page.goto('/2/schedule');
    await page.waitForLoadState('networkidle');

    // Закрываем onboarding если есть
    await page.evaluate(() => {
      localStorage.setItem('schedule_onboarding_completed', 'true');
    });
    await page.reload();
    await page.waitForLoadState('networkidle');

    // Ждём загрузки календаря
    await page.waitForTimeout(2000);

    await page.screenshot({
      path: `${outputDir}/schedule-calendar.png`,
    });
  });

  test('schedule-create-lesson', async ({ page }) => {
    await page.goto('/2/schedule/create');
    await page.waitForLoadState('networkidle');

    await page.screenshot({
      path: `${outputDir}/schedule-create-lesson.png`,
    });
  });

});
