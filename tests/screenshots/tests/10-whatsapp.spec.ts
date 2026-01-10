import { test, expect } from '@playwright/test';
import { highlightElement, clearHighlights, annotateElements } from '../utils/highlight';

const outputDir = '../../web/images/docs/whatsapp';

test.describe('WhatsApp Screenshots', () => {

  test.beforeEach(async ({ page }) => {
    await clearHighlights(page);
  });

  test('whatsapp-index', async ({ page }) => {
    await page.goto('/2/whatsapp');
    await page.waitForLoadState('networkidle');

    await page.screenshot({
      path: `${outputDir}/whatsapp-index.png`,
    });
  });

  test('whatsapp-chats', async ({ page }) => {
    await page.goto('/2/whatsapp/chats');
    await page.waitForLoadState('networkidle');

    await page.screenshot({
      path: `${outputDir}/whatsapp-chats.png`,
    });
  });

  test('whatsapp-qr', async ({ page }) => {
    await page.goto('/2/whatsapp');
    await page.waitForLoadState('networkidle');

    // Ищем QR код или кнопку подключения
    const qrElement = await page.$('.qr-code, [data-qr], canvas, img[alt*="QR"]');
    if (qrElement) {
      await highlightElement(page, '.qr-code, [data-qr], canvas', {
        label: 'Отсканируйте QR код',
        labelPosition: 'bottom',
      });

      await page.screenshot({
        path: `${outputDir}/whatsapp-qr.png`,
      });
    }
  });

});
