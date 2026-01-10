import { test, expect } from '@playwright/test';
import { highlightElement, clearHighlights } from '../utils/highlight';

const outputDir = '../../web/images/docs/payments';

test.describe('Payments Screenshots', () => {

  test.beforeEach(async ({ page }) => {
    await clearHighlights(page);
  });

  test('payments-list', async ({ page }) => {
    await page.goto('/2/payment');
    await page.waitForLoadState('networkidle');

    await page.screenshot({
      path: `${outputDir}/payments-list.png`,
    });
  });

  test('payment-create', async ({ page }) => {
    await page.goto('/2/payment/create');
    await page.waitForLoadState('networkidle');

    await page.screenshot({
      path: `${outputDir}/payment-create.png`,
    });
  });

});
