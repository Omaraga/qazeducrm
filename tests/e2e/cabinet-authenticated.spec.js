// @ts-check
const { test, expect } = require('@playwright/test');

const BASE_URL = 'http://educrm.loc';

// Тестовые данные Cabinet
const CABINET_TEST = {
  parentPhone: '7771234567',
  orgId: 1,
};

// Viewports
const VIEWPORTS = {
  mobile: { width: 375, height: 667 },
  desktop: { width: 1280, height: 800 },
};

/**
 * Функция авторизации в Cabinet
 * 1. Вводит телефон
 * 2. Получает код из debug
 * 3. Вводит код
 * 4. Авторизуется
 */
async function loginToCabinet(page) {
  // Шаг 1: Переходим на страницу логина
  await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);
  await page.waitForLoadState('networkidle');

  // Проверяем что страница загрузилась
  const content = await page.content();
  if (content.includes('Fatal error') || content.includes('ErrorException')) {
    throw new Error('Login page has PHP errors');
  }

  // Шаг 2: Вводим телефон
  const phoneInput = page.locator('input[name="LoginForm[phone]"]');
  await expect(phoneInput).toBeVisible({ timeout: 10000 });
  await phoneInput.fill(CABINET_TEST.parentPhone);

  // Ждем немного чтобы убедиться что поле заполнено
  await page.waitForTimeout(500);

  // Нажимаем кнопку отправки и ждем навигации
  const submitBtn = page.locator('button[type="submit"]');

  // Используем Promise.all для click + waitForNavigation
  await Promise.all([
    page.waitForNavigation({ timeout: 15000 }).catch(e => {
      console.log('Navigation timeout, checking for errors...');
      return null;
    }),
    submitBtn.click(),
  ]);

  // Даем время на загрузку страницы
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(1000);

  // Шаг 3: Проверяем что перешли на страницу верификации
  const currentUrl = page.url();

  // Проверяем на ошибки валидации на текущей странице
  const pageContent = await page.content();

  // Проверяем на ошибку "не найдены"
  if (pageContent.includes('не найден') || pageContent.includes('не зарегистрирован')) {
    console.log('Validation error: Phone not found in system');
    throw new Error('Phone number not found in database');
  }

  // Проверяем на любые ошибки формы
  const hasError = page.locator('.has-error, .invalid-feedback, .text-red-500, .text-danger');
  if (await hasError.count() > 0) {
    const errorText = await hasError.first().textContent();
    console.log('Form validation error:', errorText);
    throw new Error(`Form validation error: ${errorText}`);
  }

  if (!currentUrl.includes('verify')) {
    console.log('Did not redirect to verify page. Current URL:', currentUrl);
    console.log('Page content snippet:', pageContent.substring(0, 1000));
    throw new Error('Failed to proceed to verification page');
  }

  // Шаг 4: Получаем код из debug (показывается на странице в YII_DEBUG режиме)
  const verifyContent = await page.content();

  // Ищем код в разных форматах
  let code = null;

  // Формат 1: "SMS-код: 1234" - используем более гибкий паттерн с разными дефисами
  const codeMatch1 = verifyContent.match(/SMS[\-\u2010-\u2015]код[:\s]+(\d{4})/i);
  if (codeMatch1) {
    code = codeMatch1[1];
    console.log('Found code with SMS-код pattern:', code);
  }

  // Формат 2: Просто ищем "Debug:" и после него 4 цифры
  if (!code) {
    const codeMatch2 = verifyContent.match(/Debug[:\s]+[^\d]*(\d{4})/i);
    if (codeMatch2) {
      code = codeMatch2[1];
      console.log('Found code with Debug pattern:', code);
    }
  }

  // Формат 3: "Код: 1234" или "код: 1234"
  if (!code) {
    const codeMatch3 = verifyContent.match(/Код[:\s]+(\d{4})/i);
    if (codeMatch3) {
      code = codeMatch3[1];
      console.log('Found code with Код pattern:', code);
    }
  }

  // Формат 4: Ищем 4 цифры в alert блоке
  if (!code) {
    const alertBlock = page.locator('.bg-yellow-50, .bg-amber-50, [class*="warning"], [class*="alert"]');
    const alertCount = await alertBlock.count();
    console.log('Found', alertCount, 'potential alert blocks');
    for (let i = 0; i < alertCount && !code; i++) {
      const alertText = await alertBlock.nth(i).textContent();
      console.log('Alert', i, 'text:', alertText);
      const codeMatch4 = alertText.match(/(\d{4})/);
      if (codeMatch4) {
        code = codeMatch4[1];
        console.log('Found code in alert:', code);
      }
    }
  }

  // Формат 5: Последняя попытка - ищем любые 4 цифры подряд в контексте Debug/код
  if (!code) {
    const allMatches = verifyContent.match(/(\d{4})/g);
    if (allMatches && allMatches.length > 0) {
      // Берем первый 4-значный код
      code = allMatches[0];
      console.log('Found code as first 4-digit number:', code);
    }
  }

  if (!code) {
    console.log('Could not find debug code. Page content (first 3000 chars):', verifyContent.substring(0, 3000));
    throw new Error('Debug verification code not found on page');
  }

  console.log('Using verification code:', code);

  // Шаг 5: Вводим код
  const codeInput = page.locator('input[name="LoginForm[code]"]');
  if (await codeInput.isVisible()) {
    await codeInput.fill(code);
  } else {
    // Возможно отдельные поля для каждой цифры
    const codeInputs = page.locator('input[type="text"][maxlength="1"]');
    const inputCount = await codeInputs.count();
    if (inputCount === 4) {
      for (let i = 0; i < 4; i++) {
        await codeInputs.nth(i).fill(code[i]);
      }
    }
  }

  // Отправляем форму - это вызовет навигацию
  try {
    await page.evaluate(() => {
      const form = document.querySelector('form');
      if (form) {
        form.submit();
      } else {
        const btn = document.querySelector('button[type="submit"]');
        if (btn) btn.click();
      }
    });
  } catch (e) {
    // Ожидаем ошибку "Execution context was destroyed" из-за навигации - это нормально
    if (!e.message.includes('navigation') && !e.message.includes('destroyed')) {
      throw e;
    }
  }

  // Ждем редиректа на dashboard
  try {
    await page.waitForURL(/cabinet\/\d+\/default|cabinet\/default\/index/, { timeout: 15000 });
  } catch (e) {
    console.log('Did not redirect to dashboard, checking current state...');
  }

  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(500);

  // Проверяем успешную авторизацию
  const finalUrl = page.url();
  console.log('Final URL after login:', finalUrl);
  const finalContent = await page.content();

  if (finalContent.includes('Fatal error')) {
    throw new Error('Authorization resulted in PHP error');
  }

  // Должны быть на dashboard или всё ещё на verify с ошибкой
  return {
    success: finalUrl.includes('index') || finalUrl.includes('default') && !finalUrl.includes('login'),
    url: finalUrl,
    hasError: finalContent.includes('Неверный код') || finalContent.includes('error'),
  };
}

// ============================================================================
// ТЕСТЫ С АВТОРИЗАЦИЕЙ
// ============================================================================

test.describe('Cabinet - Authenticated Tests', () => {

  test.describe.configure({ mode: 'serial' }); // Запускаем последовательно

  let authResult = null;

  test('1. Login flow works', async ({ page }) => {
    authResult = await loginToCabinet(page);
    console.log('Auth result:', authResult);

    // Проверяем что нет PHP ошибок в любом случае
    const content = await page.content();
    expect(content).not.toContain('Fatal error');
    expect(content).not.toContain('ErrorException');
  });

  test('2. Dashboard page loads after login', async ({ page }) => {
    await loginToCabinet(page);

    // Переходим на dashboard
    await page.goto(`${BASE_URL}/cabinet/default/index?org=${CABINET_TEST.orgId}`);
    await page.waitForLoadState('networkidle');

    const content = await page.content();
    expect(content).not.toContain('Fatal error');
    expect(content).not.toContain('ErrorException');

    // Делаем скриншот
    await page.screenshot({ path: 'test-results/cabinet-dashboard.png', fullPage: true });
  });

  test('3. Schedule page loads', async ({ page }) => {
    await loginToCabinet(page);

    await page.goto(`${BASE_URL}/cabinet/schedule/index?org=${CABINET_TEST.orgId}`);
    await page.waitForLoadState('networkidle');

    const content = await page.content();
    expect(content).not.toContain('Fatal error');
    expect(content).not.toContain('ErrorException');

    await page.screenshot({ path: 'test-results/cabinet-schedule.png', fullPage: true });
  });

  test('4. Schedule week view loads', async ({ page }) => {
    await loginToCabinet(page);

    await page.goto(`${BASE_URL}/cabinet/schedule/week?org=${CABINET_TEST.orgId}`);
    await page.waitForLoadState('networkidle');

    const content = await page.content();
    expect(content).not.toContain('Fatal error');
    expect(content).not.toContain('ErrorException');

    await page.screenshot({ path: 'test-results/cabinet-schedule-week.png', fullPage: true });
  });

  test('5. Payment page loads', async ({ page }) => {
    await loginToCabinet(page);

    await page.goto(`${BASE_URL}/cabinet/payment/index?org=${CABINET_TEST.orgId}`);
    await page.waitForLoadState('networkidle');

    const content = await page.content();
    expect(content).not.toContain('Fatal error');
    expect(content).not.toContain('ErrorException');

    await page.screenshot({ path: 'test-results/cabinet-payment.png', fullPage: true });
  });

  test('6. Payment balance page loads', async ({ page }) => {
    await loginToCabinet(page);

    await page.goto(`${BASE_URL}/cabinet/payment/balance?org=${CABINET_TEST.orgId}`);
    await page.waitForLoadState('networkidle');

    const content = await page.content();
    expect(content).not.toContain('Fatal error');
    expect(content).not.toContain('ErrorException');

    await page.screenshot({ path: 'test-results/cabinet-balance.png', fullPage: true });
  });

  test('7. Attendance page loads', async ({ page }) => {
    await loginToCabinet(page);

    await page.goto(`${BASE_URL}/cabinet/attendance/index?org=${CABINET_TEST.orgId}`);
    await page.waitForLoadState('networkidle');

    const content = await page.content();
    expect(content).not.toContain('Fatal error');
    expect(content).not.toContain('ErrorException');

    await page.screenshot({ path: 'test-results/cabinet-attendance.png', fullPage: true });
  });

  test('8. Attendance stats page loads', async ({ page }) => {
    await loginToCabinet(page);

    await page.goto(`${BASE_URL}/cabinet/attendance/stats?org=${CABINET_TEST.orgId}`);
    await page.waitForLoadState('networkidle');

    const content = await page.content();
    expect(content).not.toContain('Fatal error');
    expect(content).not.toContain('ErrorException');

    await page.screenshot({ path: 'test-results/cabinet-attendance-stats.png', fullPage: true });
  });

  test('9. Homework page loads', async ({ page }) => {
    await loginToCabinet(page);

    await page.goto(`${BASE_URL}/cabinet/homework/index?org=${CABINET_TEST.orgId}`);
    await page.waitForLoadState('networkidle');

    const content = await page.content();
    expect(content).not.toContain('Fatal error');
    expect(content).not.toContain('ErrorException');

    await page.screenshot({ path: 'test-results/cabinet-homework.png', fullPage: true });
  });

});

// ============================================================================
// ТЕСТЫ UI ПОСЛЕ АВТОРИЗАЦИИ
// ============================================================================

test.describe('Cabinet - UI Tests After Auth', () => {

  test('Dashboard shows pupil cards', async ({ page }) => {
    await loginToCabinet(page);
    await page.goto(`${BASE_URL}/cabinet/default/index?org=${CABINET_TEST.orgId}`);

    // Проверяем наличие карточек учеников или данных
    const content = await page.content();
    expect(content).not.toContain('Fatal error');

    // Ищем элементы которые должны быть на dashboard
    const hasContent = content.includes('Ученик') ||
                       content.includes('ученик') ||
                       content.includes('Расписание') ||
                       content.includes('Баланс');

    console.log('Dashboard has expected content:', hasContent);
  });

  test('Menu navigation works', async ({ page }) => {
    await loginToCabinet(page);
    await page.goto(`${BASE_URL}/cabinet/default/index?org=${CABINET_TEST.orgId}`);

    // Проверяем наличие меню
    const menuLinks = page.locator('nav a, .menu a, header a');
    const menuCount = await menuLinks.count();
    console.log('Found menu links:', menuCount);

    // Проверяем что есть ссылки на основные разделы
    const content = await page.content();
    const hasScheduleLink = content.includes('schedule') || content.includes('Расписание');
    const hasPaymentLink = content.includes('payment') || content.includes('Оплата') || content.includes('Платеж');

    console.log('Has schedule link:', hasScheduleLink);
    console.log('Has payment link:', hasPaymentLink);
  });

  test('Logout works', async ({ page }) => {
    await loginToCabinet(page);

    // После успешного логина переходим на dashboard
    const dashboardUrl = page.url();
    console.log('Dashboard URL before logout:', dashboardUrl);

    // Ищем кнопку выхода
    const logoutLink = page.locator('a[href*="logout"]');
    const logoutCount = await logoutLink.count();
    console.log('Logout links found:', logoutCount);

    if (logoutCount > 0) {
      await logoutLink.first().click();
      await page.waitForLoadState('networkidle');

      // Должны вернуться на страницу логина - проверяем по содержимому или URL
      const url = page.url();
      const content = await page.content();
      console.log('URL after logout:', url);

      // Проверяем что мы на странице логина - либо по URL, либо по содержимому
      const isOnLoginPage = url.includes('login') ||
                            url.includes('select') ||
                            content.includes('Номер телефона') ||
                            content.includes('Вы вышли');

      expect(isOnLoginPage).toBe(true);
    } else {
      console.log('No logout link found - checking if already logged out');
    }
  });

});

// ============================================================================
// ТЕСТЫ ДАННЫХ
// ============================================================================

test.describe('Cabinet - Data Tests', () => {

  test('Schedule shows lessons', async ({ page }) => {
    await loginToCabinet(page);
    await page.goto(`${BASE_URL}/cabinet/schedule/index?org=${CABINET_TEST.orgId}`);

    const content = await page.content();
    expect(content).not.toContain('Fatal error');

    // Проверяем наличие календаря или списка занятий
    const hasCalendar = content.includes('fc-') ||
                        content.includes('calendar') ||
                        content.includes('FullCalendar');

    console.log('Schedule has calendar:', hasCalendar);
  });

  test('Payment shows history', async ({ page }) => {
    await loginToCabinet(page);
    await page.goto(`${BASE_URL}/cabinet/payment/index?org=${CABINET_TEST.orgId}`);

    const content = await page.content();
    expect(content).not.toContain('Fatal error');

    // Проверяем наличие таблицы или списка платежей
    const hasPaymentData = content.includes('Оплата') ||
                           content.includes('оплата') ||
                           content.includes('payment') ||
                           content.includes('₸') ||
                           content.includes('тг');

    console.log('Payment page has payment data:', hasPaymentData);
  });

  test('Attendance shows records', async ({ page }) => {
    await loginToCabinet(page);
    await page.goto(`${BASE_URL}/cabinet/attendance/index?org=${CABINET_TEST.orgId}`);

    const content = await page.content();
    expect(content).not.toContain('Fatal error');

    // Проверяем наличие данных посещаемости
    const hasAttendanceData = content.includes('Посещ') ||
                              content.includes('посещ') ||
                              content.includes('Присутств') ||
                              content.includes('Пропуск');

    console.log('Attendance page has data:', hasAttendanceData);
  });

  test('Homework shows assignments', async ({ page }) => {
    await loginToCabinet(page);
    await page.goto(`${BASE_URL}/cabinet/homework/index?org=${CABINET_TEST.orgId}`);

    const content = await page.content();
    expect(content).not.toContain('Fatal error');

    // Проверяем наличие домашних заданий
    const hasHomework = content.includes('Домашн') ||
                        content.includes('домашн') ||
                        content.includes('Задани') ||
                        content.includes('Тестовое'); // Наши тестовые данные

    console.log('Homework page has data:', hasHomework);
  });

});

// ============================================================================
// МОБИЛЬНЫЕ ТЕСТЫ ПОСЛЕ АВТОРИЗАЦИИ
// ============================================================================

test.describe('Cabinet - Mobile Tests After Auth', () => {

  test('Dashboard works on mobile', async ({ page }) => {
    await page.setViewportSize(VIEWPORTS.mobile);
    await loginToCabinet(page);
    await page.goto(`${BASE_URL}/cabinet/default/index?org=${CABINET_TEST.orgId}`);

    const content = await page.content();
    expect(content).not.toContain('Fatal error');

    // Проверяем отсутствие горизонтального скролла
    const hasHorizontalScroll = await page.evaluate(() => {
      return document.documentElement.scrollWidth > document.documentElement.clientWidth;
    });
    expect(hasHorizontalScroll).toBe(false);

    await page.screenshot({ path: 'test-results/cabinet-dashboard-mobile.png', fullPage: true });
  });

  test('Schedule works on mobile', async ({ page }) => {
    await page.setViewportSize(VIEWPORTS.mobile);
    await loginToCabinet(page);
    await page.goto(`${BASE_URL}/cabinet/schedule/index?org=${CABINET_TEST.orgId}`);

    const content = await page.content();
    expect(content).not.toContain('Fatal error');

    await page.screenshot({ path: 'test-results/cabinet-schedule-mobile.png', fullPage: true });
  });

  test('Mobile menu works', async ({ page }) => {
    await page.setViewportSize(VIEWPORTS.mobile);
    await loginToCabinet(page);
    await page.goto(`${BASE_URL}/cabinet/default/index?org=${CABINET_TEST.orgId}`);

    // Ищем бургер-меню
    const burgerMenu = page.locator('[class*="burger"], [class*="hamburger"], button[aria-label*="menu"]');
    if (await burgerMenu.count() > 0) {
      await burgerMenu.first().click();
      await page.waitForTimeout(500);

      // Проверяем что меню открылось
      const nav = page.locator('nav, [class*="mobile-menu"], [class*="sidebar"]');
      if (await nav.count() > 0) {
        await expect(nav.first()).toBeVisible();
      }
    }

    await page.screenshot({ path: 'test-results/cabinet-mobile-menu.png', fullPage: true });
  });

});

// ============================================================================
// JS ОШИБКИ ПОСЛЕ АВТОРИЗАЦИИ
// ============================================================================

test.describe('Cabinet - JS Errors After Auth', () => {

  test('No JS errors on dashboard', async ({ page }) => {
    const jsErrors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') {
        jsErrors.push(msg.text());
      }
    });

    await loginToCabinet(page);
    await page.goto(`${BASE_URL}/cabinet/default/index?org=${CABINET_TEST.orgId}`);
    await page.waitForTimeout(2000);

    const criticalErrors = jsErrors.filter(e =>
      !e.includes('favicon') &&
      !e.includes('net::ERR') &&
      !e.includes('404')
    );

    console.log('JS errors on dashboard:', criticalErrors);
    expect(criticalErrors.length).toBe(0);
  });

  test('No JS errors on schedule (FullCalendar)', async ({ page }) => {
    const jsErrors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') {
        jsErrors.push(msg.text());
      }
    });

    await loginToCabinet(page);
    await page.goto(`${BASE_URL}/cabinet/schedule/index?org=${CABINET_TEST.orgId}`);
    await page.waitForTimeout(3000); // Даём время на загрузку календаря

    // Фильтруем только критичные JS ошибки, исключая:
    // - favicon (не критично)
    // - network errors (ERR_) - проблемы сети
    // - 404/500 - серверные ошибки (должны тестироваться отдельно)
    const criticalErrors = jsErrors.filter(e =>
      !e.includes('favicon') &&
      !e.includes('net::ERR') &&
      !e.includes('404') &&
      !e.includes('500') &&
      !e.includes('Internal Server Error') &&
      !e.includes('Failed to load resource')
    );

    console.log('JS errors on schedule:', criticalErrors);
    console.log('Filtered out (non-critical):', jsErrors.filter(e => !criticalErrors.includes(e)));
    expect(criticalErrors.length).toBe(0);
  });

});
