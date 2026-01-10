import { test, expect } from '@playwright/test';
import { highlightElement, clearHighlights, annotateElements } from '../utils/highlight';

const outputDir = '../../web/images/docs/getting-started';

// Тесты для публичных страниц (без авторизации)
test.describe('Getting Started - Public Pages', () => {
  // Не используем сохранённую авторизацию для публичных страниц
  test.use({ storageState: { cookies: [], origins: [] } });

  test('login-page', async ({ page }) => {
    await page.goto('/login');
    await page.waitForLoadState('networkidle');

    await page.screenshot({
      path: `${outputDir}/login-page.png`,
    });
  });

  test('login-form-highlighted', async ({ page }) => {
    await page.goto('/login');
    await page.waitForLoadState('networkidle');

    // Выделяем поля формы
    await annotateElements(page, [
      { selector: '[name="LoginForm[username]"]', label: '1. Email' },
      { selector: '[name="LoginForm[password]"]', label: '2. Пароль' },
      { selector: 'button[type="submit"]', label: '3. Войти' },
    ]);

    await page.screenshot({
      path: `${outputDir}/login-form-highlighted.png`,
    });
  });

  test('registration-page', async ({ page }) => {
    await page.goto('/register');
    await page.waitForLoadState('networkidle');

    await page.screenshot({
      path: `${outputDir}/registration-page.png`,
      fullPage: true,
    });
  });
});

// Тесты для авторизованных страниц
test.describe('Getting Started - Authenticated Pages', () => {

  test.beforeEach(async ({ page }) => {
    // Очищаем выделения перед каждым тестом
    await clearHighlights(page);
  });

  test('dashboard', async ({ page }) => {
    await page.goto('/2/default');
    await page.waitForLoadState('networkidle');

    await page.screenshot({
      path: `${outputDir}/dashboard.png`,
    });
  });

  test('dashboard-sidebar-highlighted', async ({ page }) => {
    await page.goto('/2/default');
    await page.waitForLoadState('networkidle');

    // Выделяем боковое меню
    await highlightElement(page, 'aside', {
      label: 'Главное меню',
      labelPosition: 'right',
    });

    await page.screenshot({
      path: `${outputDir}/dashboard-sidebar.png`,
    });
  });

});
