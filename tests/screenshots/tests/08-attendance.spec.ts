import { test, expect } from '@playwright/test';
import { highlightElement, clearHighlights, annotateElements } from '../utils/highlight';

const outputDir = '../../web/images/docs/attendance';

test.describe('Attendance Screenshots', () => {

  test.beforeEach(async ({ page }) => {
    await clearHighlights(page);
  });

  test('attendance-page', async ({ page }) => {
    // Информационная страница о посещаемости
    await page.goto('/2/attendance');
    await page.waitForLoadState('networkidle');

    await page.screenshot({
      path: `${outputDir}/attendance-page.png`,
    });
  });

  test('attendance-lesson', async ({ page }) => {
    // Переходим на расписание
    await page.goto('/2/schedule');
    await page.waitForLoadState('networkidle');

    // Закрываем onboarding если есть
    await page.evaluate(() => {
      localStorage.setItem('schedule_onboarding_completed', 'true');
    });
    await page.reload();
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1500);

    // Ищем событие в календаре и кликаем
    const event = await page.$('.fc-event, .calendar-event, [data-event-id]');
    if (event) {
      await event.click();
      await page.waitForTimeout(1000);

      // Ждём появления модалки
      await page.waitForSelector('.modal, [role="dialog"], .fixed.inset-0', { timeout: 5000 }).catch(() => {});
      await page.waitForTimeout(500);

      // Убираем любые tooltip/popover элементы
      await page.evaluate(() => {
        document.querySelectorAll('[data-tippy-root], .tippy-box, .tooltip, .fc-popover').forEach(el => el.remove());
      });

      await page.screenshot({
        path: `${outputDir}/attendance-lesson.png`,
      });
    }
  });

  test('attendance-full-page', async ({ page }) => {
    // Находим занятие с учениками для полноценной страницы посещаемости
    await page.goto('/2/schedule');
    await page.waitForLoadState('networkidle');

    // Закрываем onboarding
    await page.evaluate(() => {
      localStorage.setItem('schedule_onboarding_completed', 'true');
    });
    await page.reload();
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1500);

    // Кликаем на событие
    const event = await page.$('.fc-event, .calendar-event, [data-event-id]');
    if (event) {
      await event.click();
      await page.waitForTimeout(1000);

      // Ждём модалку
      await page.waitForSelector('.modal, [role="dialog"], .fixed.inset-0', { timeout: 5000 }).catch(() => {});

      // Ищем ссылку "открыть в новой вкладке" или иконку внешней ссылки рядом с Посещаемость
      const externalLink = await page.$('a[href*="/attendance/lesson"], a[target="_blank"][href*="attendance"]');
      if (externalLink) {
        const href = await externalLink.getAttribute('href');
        if (href) {
          // Переходим на полную страницу посещаемости
          await page.goto(href.startsWith('/') ? href : `/${href}`);
          await page.waitForLoadState('networkidle');

          await page.screenshot({
            path: `${outputDir}/attendance-full-page.png`,
            fullPage: true,
          });
        }
      } else {
        // Если не нашли ссылку, пробуем напрямую
        // Получаем ID занятия из URL или модалки
        const lessonId = await page.evaluate(() => {
          const modal = document.querySelector('.modal, [role="dialog"]');
          if (modal) {
            const link = modal.querySelector('a[href*="lesson"]');
            if (link) {
              const match = link.getAttribute('href')?.match(/id=(\d+)/);
              return match ? match[1] : null;
            }
          }
          return null;
        });

        if (lessonId) {
          await page.goto(`/2/attendance/lesson?id=${lessonId}`);
          await page.waitForLoadState('networkidle');

          await page.screenshot({
            path: `${outputDir}/attendance-full-page.png`,
            fullPage: true,
          });
        }
      }
    }
  });

});
