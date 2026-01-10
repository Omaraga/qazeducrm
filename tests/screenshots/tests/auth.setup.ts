import { test as setup, expect } from '@playwright/test';

const authFile = './auth.json';

setup('authenticate', async ({ page }) => {
  // Переходим на страницу входа
  await page.goto('/login');

  // Ждём загрузки формы
  await page.waitForSelector('form');

  // Заполняем форму входа (организация)
  await page.fill('[name="LoginForm[username]"]', 'admin@admin.kz');
  await page.fill('[name="LoginForm[password]"]', '123456789');

  // Нажимаем кнопку входа
  await page.click('button[type="submit"]');

  // Ждём редиректа в CRM
  await page.waitForURL(/\/\d+\//, { timeout: 10000 });

  // Сохраняем состояние авторизации
  await page.context().storageState({ path: authFile });
});
