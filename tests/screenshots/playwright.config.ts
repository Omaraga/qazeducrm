import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './tests',
  outputDir: './output',

  // Полностью параллельный запуск
  fullyParallel: true,

  // Не перезапускать тесты при ошибке
  forbidOnly: !!process.env.CI,

  // Повторы в CI
  retries: process.env.CI ? 2 : 0,

  // Количество воркеров
  workers: process.env.CI ? 1 : undefined,

  // Репортер
  reporter: 'html',

  use: {
    // Base URL вашего локального сервера
    baseURL: 'http://educrm.loc',

    // Размер окна для скриншотов
    viewport: { width: 1440, height: 900 },

    // Русская локаль
    locale: 'ru-RU',

    // Таймзона
    timezoneId: 'Asia/Almaty',

    // Trace для отладки
    trace: 'on-first-retry',

    // Скриншоты
    screenshot: 'only-on-failure',
  },

  projects: [
    // Проект для авторизации
    {
      name: 'setup',
      testMatch: /.*\.setup\.ts/,
    },

    // Основной проект для скриншотов
    {
      name: 'screenshots',
      use: {
        ...devices['Desktop Chrome'],
        // Используем сохранённое состояние авторизации
        storageState: './auth.json',
      },
      dependencies: ['setup'],
    },
  ],
});
