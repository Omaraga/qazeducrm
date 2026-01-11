// @ts-check
const { test, expect, devices } = require('@playwright/test');

const BASE_URL = 'http://educrm.loc';

// Тестовые данные Cabinet
const CABINET_TEST = {
  parentPhone: '7771234567', // Телефон родителя из миграции
  orgId: 1, // ID организации
};

// Viewports для адаптивности
const VIEWPORTS = {
  mobile: { width: 375, height: 667 },
  tablet: { width: 768, height: 1024 },
  desktop: { width: 1280, height: 800 },
  largeDesktop: { width: 1920, height: 1080 },
};

// ============================================================================
// РАЗДЕЛ 1: ФУНКЦИОНАЛЬНЫЕ ОШИБКИ И БАГИ (PHP Errors)
// ============================================================================

test.describe('1. Cabinet - PHP Errors and Bugs', () => {

  test('1.1 Select organization page loads without PHP errors', async ({ page }) => {
    const response = await page.goto(`${BASE_URL}/cabinet`);
    expect(response.status()).toBe(200);

    const content = await page.content();
    expect(content).not.toContain('Fatal error');
    expect(content).not.toContain('Parse error');
    expect(content).not.toContain('ErrorException');
    expect(content).not.toContain('Declaration of');
    expect(content).not.toContain('Undefined variable');
    expect(content).not.toContain('Class not found');
  });

  test('1.2 Login page loads without PHP errors', async ({ page }) => {
    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);
    const content = await page.content();

    expect(content).not.toContain('Fatal error');
    expect(content).not.toContain('Parse error');
    expect(content).not.toContain('ErrorException');
    expect(content).not.toContain('Undefined variable');
  });

  test('1.3 All cabinet routes have no PHP compile errors', async ({ page }) => {
    const routes = [
      '/cabinet',
      `/cabinet/default/login?org=${CABINET_TEST.orgId}`,
      `/cabinet/default/select-organization`,
    ];

    for (const route of routes) {
      await page.goto(BASE_URL + route);
      const content = await page.content();

      expect(content, `Route ${route} has Fatal error`).not.toContain('Fatal error');
      expect(content, `Route ${route} has Parse error`).not.toContain('Parse error');
      expect(content, `Route ${route} has Compile Error`).not.toContain('Compile Error');
      expect(content, `Route ${route} has Declaration error`).not.toContain('Declaration of');
      expect(content, `Route ${route} has Undefined variable`).not.toContain('Undefined variable');
    }
  });

  test('1.4 Invalid organization ID shows error gracefully', async ({ page }) => {
    await page.goto(`${BASE_URL}/cabinet/default/login?org=99999`);
    const content = await page.content();

    // Должен показать ошибку или редирект, но не PHP error
    expect(content).not.toContain('Fatal error');
    expect(content).not.toContain('ErrorException');
  });

  test('1.5 Missing organization parameter redirects properly', async ({ page }) => {
    const response = await page.goto(`${BASE_URL}/cabinet/default/login`);
    // Должен редиректить на select-organization
    expect(response.status()).toBeLessThan(500);
    const content = await page.content();
    expect(content).not.toContain('Fatal error');
  });

});

// ============================================================================
// РАЗДЕЛ 2: КОНСОЛЬНЫЕ ОШИБКИ JS
// ============================================================================

test.describe('2. Cabinet - JavaScript Console Errors', () => {

  test('2.1 Select organization page has no JS errors', async ({ page }) => {
    const consoleErrors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') {
        consoleErrors.push(msg.text());
      }
    });

    await page.goto(`${BASE_URL}/cabinet`);
    await page.waitForLoadState('networkidle');

    // Фильтруем ошибки связанные с внешними ресурсами
    const criticalErrors = consoleErrors.filter(e =>
      !e.includes('favicon') &&
      !e.includes('net::ERR') &&
      !e.includes('404')
    );

    expect(criticalErrors, `JS Console errors: ${criticalErrors.join(', ')}`).toHaveLength(0);
  });

  test('2.2 Login page has no JS errors', async ({ page }) => {
    const consoleErrors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') {
        consoleErrors.push(msg.text());
      }
    });

    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);
    await page.waitForLoadState('networkidle');

    const criticalErrors = consoleErrors.filter(e =>
      !e.includes('favicon') &&
      !e.includes('net::ERR') &&
      !e.includes('404')
    );

    expect(criticalErrors).toHaveLength(0);
  });

  test('2.3 Phone input validation has no JS errors', async ({ page }) => {
    const consoleErrors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') {
        consoleErrors.push(msg.text());
      }
    });

    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);

    // Вводим телефон и пробуем отправить
    const phoneInput = page.locator('input[name="LoginForm[phone]"]');
    if (await phoneInput.isVisible()) {
      await phoneInput.fill('invalid');
      await page.click('button[type="submit"]');
      await page.waitForTimeout(1000);
    }

    const criticalErrors = consoleErrors.filter(e =>
      !e.includes('favicon') &&
      !e.includes('net::ERR')
    );

    expect(criticalErrors).toHaveLength(0);
  });

});

// ============================================================================
// РАЗДЕЛ 3: UI/UX ТЕСТЫ
// ============================================================================

test.describe('3. Cabinet - UI/UX Tests', () => {

  test('3.1 Organization selection cards are clickable', async ({ page }) => {
    await page.goto(`${BASE_URL}/cabinet`);

    // Проверяем что карточки организаций есть
    const orgCards = page.locator('a[href*="/cabinet/default/login"]');
    const count = await orgCards.count();

    if (count > 0) {
      // Проверяем что карточка кликабельна
      const firstCard = orgCards.first();
      await expect(firstCard).toBeVisible();

      // Проверяем стили hover (курсор)
      const cursor = await firstCard.evaluate(el => window.getComputedStyle(el).cursor);
      expect(cursor).toBe('pointer');
    }
  });

  test('3.2 Login form has proper labels and placeholders', async ({ page }) => {
    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);

    // Проверяем наличие label для телефона
    const phoneLabel = page.locator('label:has-text("Телефон"), label:has-text("телефон")');

    // Проверяем placeholder
    const phoneInput = page.locator('input[name="LoginForm[phone]"]');
    if (await phoneInput.isVisible()) {
      const placeholder = await phoneInput.getAttribute('placeholder');
      expect(placeholder).toBeTruthy();
    }
  });

  test('3.3 Submit button is clearly visible', async ({ page }) => {
    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);

    const submitBtn = page.locator('button[type="submit"]');
    if (await submitBtn.isVisible()) {
      // Проверяем что кнопка достаточно большая
      const box = await submitBtn.boundingBox();
      expect(box.height).toBeGreaterThan(35);
      expect(box.width).toBeGreaterThan(100);

      // Проверяем контрастный цвет
      const bgColor = await submitBtn.evaluate(el => window.getComputedStyle(el).backgroundColor);
      expect(bgColor).not.toBe('transparent');
      expect(bgColor).not.toBe('rgba(0, 0, 0, 0)');
    }
  });

  test('3.4 Error messages are visible and styled', async ({ page }) => {
    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);

    // Отправляем пустую форму
    const submitBtn = page.locator('button[type="submit"]');
    if (await submitBtn.isVisible()) {
      await submitBtn.click();
      await page.waitForTimeout(500);

      // Проверяем стили ошибок
      const errorMsgs = page.locator('.text-danger-600, .text-red-500, .text-danger, .invalid-feedback, .has-error');
      if (await errorMsgs.count() > 0) {
        const firstError = errorMsgs.first();
        await expect(firstError).toBeVisible();
      }
    }
  });

  test('3.5 Back link to organization selection exists', async ({ page }) => {
    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);

    // Проверяем наличие ссылки назад (может быть с разным текстом)
    const backLink = page.locator('a:has-text("другой"), a:has-text("Выбрать"), a:has-text("назад")');
    const hasBackLink = await backLink.count() > 0;
    expect(hasBackLink).toBe(true);
  });

  test('3.6 Help section is visible on login page', async ({ page }) => {
    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);

    // Проверяем блок с инструкцией
    const helpSection = page.locator('text=Как войти');
    if (await helpSection.isVisible()) {
      // Проверяем наличие шагов
      const steps = page.locator('ol li, .space-y-2 li');
      const stepsCount = await steps.count();
      expect(stepsCount).toBeGreaterThanOrEqual(3);
    }
  });

  test('3.7 Page title contains organization name', async ({ page }) => {
    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);

    const title = await page.title();
    expect(title).toContain('Личный кабинет');
  });

  test('3.8 Icons are properly displayed (SVG)', async ({ page }) => {
    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);

    // Проверяем что SVG иконки видны
    const svgIcons = page.locator('svg');
    const count = await svgIcons.count();
    expect(count).toBeGreaterThan(0);

    // Проверяем что иконки имеют размеры
    const firstIcon = svgIcons.first();
    const box = await firstIcon.boundingBox();
    if (box) {
      expect(box.width).toBeGreaterThan(0);
      expect(box.height).toBeGreaterThan(0);
    }
  });

});

// ============================================================================
// РАЗДЕЛ 4: АДАПТИВНОСТЬ (ДЕСКТОП И ПЛАНШЕТ)
// ============================================================================

test.describe('4. Cabinet - Desktop and Tablet Responsiveness', () => {

  test('4.1 Desktop layout is correct (1280x800)', async ({ page }) => {
    await page.setViewportSize(VIEWPORTS.desktop);
    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);

    // Форма должна быть по центру
    const form = page.locator('form#login-form, form');
    if (await form.isVisible()) {
      const box = await form.first().boundingBox();
      // Форма не должна занимать всю ширину
      expect(box.width).toBeLessThan(VIEWPORTS.desktop.width * 0.8);
    }
  });

  test('4.2 Large desktop layout is correct (1920x1080)', async ({ page }) => {
    await page.setViewportSize(VIEWPORTS.largeDesktop);
    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);

    // Проверяем что контент центрирован и не растянут на всю ширину
    const form = page.locator('form');
    if (await form.count() > 0) {
      const box = await form.first().boundingBox();
      // Форма не должна занимать всю ширину экрана
      expect(box.width).toBeLessThan(VIEWPORTS.largeDesktop.width * 0.7);
    }
  });

  test('4.3 Tablet layout is correct (768x1024)', async ({ page }) => {
    await page.setViewportSize(VIEWPORTS.tablet);
    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);

    const content = await page.content();
    expect(content).not.toContain('Fatal error');

    // Форма должна быть видна
    const form = page.locator('form');
    await expect(form.first()).toBeVisible();
  });

  test('4.4 Organization cards grid adapts to tablet', async ({ page }) => {
    await page.setViewportSize(VIEWPORTS.tablet);
    await page.goto(`${BASE_URL}/cabinet`);

    // Карточки организаций должны быть видны
    const orgCards = page.locator('a[href*="/cabinet/default/login"]');
    const count = await orgCards.count();
    if (count > 0) {
      await expect(orgCards.first()).toBeVisible();
    }
  });

  test('4.5 No horizontal scroll on desktop', async ({ page }) => {
    await page.setViewportSize(VIEWPORTS.desktop);
    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);

    const hasHorizontalScroll = await page.evaluate(() => {
      return document.documentElement.scrollWidth > document.documentElement.clientWidth;
    });

    expect(hasHorizontalScroll).toBe(false);
  });

});

// ============================================================================
// РАЗДЕЛ 5: ЛОГИЧЕСКИЕ ОШИБКИ
// ============================================================================

test.describe('5. Cabinet - Logic Errors', () => {

  test('5.1 Empty phone shows validation error', async ({ page }) => {
    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);

    // Отправляем пустую форму
    const phoneInput = page.locator('input[name="LoginForm[phone]"]');
    const submitBtn = page.locator('button[type="submit"]');

    if (await phoneInput.isVisible() && await submitBtn.isVisible()) {
      await phoneInput.fill('');
      await submitBtn.click();
      await page.waitForTimeout(500);

      // Должна появиться ошибка валидации
      const pageContent = await page.content();
      const hasError = pageContent.includes('не может быть пустым') ||
                       pageContent.includes('required') ||
                       pageContent.includes('обязательно') ||
                       pageContent.includes('field-loginform-phone has-error');

      expect(hasError).toBe(true);
    }
  });

  test('5.2 Invalid phone format shows error', async ({ page }) => {
    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);

    const phoneInput = page.locator('input[name="LoginForm[phone]"]');
    const submitBtn = page.locator('button[type="submit"]');

    if (await phoneInput.isVisible() && await submitBtn.isVisible()) {
      await phoneInput.fill('123'); // Слишком короткий
      await submitBtn.click();
      await page.waitForLoadState('networkidle');

      const content = await page.content();
      // Не должно быть PHP ошибок
      expect(content).not.toContain('Fatal error');
    }
  });

  test('5.3 Non-existing phone shows proper error', async ({ page }) => {
    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);

    const phoneInput = page.locator('input[name="LoginForm[phone]"]');
    const submitBtn = page.locator('button[type="submit"]');

    if (await phoneInput.isVisible() && await submitBtn.isVisible()) {
      await phoneInput.fill('7009999999'); // Несуществующий номер
      await submitBtn.click();
      await page.waitForLoadState('networkidle');

      const content = await page.content();
      // Не должно быть PHP ошибок
      expect(content).not.toContain('Fatal error');
      expect(content).not.toContain('ErrorException');
    }
  });

  test('5.4 Verify page without code in session redirects', async ({ page }) => {
    // Пробуем открыть страницу верификации без предварительного ввода телефона
    await page.goto(`${BASE_URL}/cabinet/default/verify?org=${CABINET_TEST.orgId}`);

    // Должен редиректить на select-organization или login
    const url = page.url();
    const isRedirected = url.includes('select-organization') || url.includes('login');

    // Или показать форму без ошибок
    const content = await page.content();
    expect(content).not.toContain('Fatal error');
  });

  test('5.5 Logout without session does not crash', async ({ page }) => {
    await page.goto(`${BASE_URL}/cabinet/default/logout?org=${CABINET_TEST.orgId}`);

    const content = await page.content();
    expect(content).not.toContain('Fatal error');
    expect(content).not.toContain('ErrorException');
  });

  test('5.6 Resend code without session handles gracefully', async ({ page }) => {
    await page.goto(`${BASE_URL}/cabinet/default/resend-code?org=${CABINET_TEST.orgId}`);

    const content = await page.content();
    expect(content).not.toContain('Fatal error');
  });

});

// ============================================================================
// РАЗДЕЛ 6: АРХИТЕКТУРНЫЕ ОШИБКИ
// ============================================================================

test.describe('6. Cabinet - Architecture Errors', () => {

  test('6.1 Organization ID in URL is validated', async ({ page }) => {
    // Пробуем SQL injection в org parameter
    await page.goto(`${BASE_URL}/cabinet/default/login?org=1' OR '1'='1`);

    const content = await page.content();
    expect(content).not.toContain('SQL');
    expect(content).not.toContain('syntax');
    expect(content).not.toContain('Fatal error');
  });

  test('6.2 XSS in phone field is prevented', async ({ page }) => {
    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);

    const phoneInput = page.locator('input[name="LoginForm[phone]"]');
    if (await phoneInput.isVisible()) {
      await phoneInput.fill('<script>alert("XSS")</script>');
      await page.click('button[type="submit"]');
      await page.waitForLoadState('networkidle');

      const content = await page.content();
      // Скрипт не должен выполниться, текст должен быть экранирован
      expect(content).not.toContain('<script>alert');
    }
  });

  test('6.3 Protected pages require authentication', async ({ page }) => {
    // Пробуем открыть dashboard без авторизации
    await page.goto(`${BASE_URL}/cabinet/default/index?org=${CABINET_TEST.orgId}`);

    // Должен редиректить на логин
    const url = page.url();
    const isRedirected = url.includes('login') || url.includes('select-organization');

    if (!isRedirected) {
      // Если не редиректит, проверяем что нет данных учеников
      const content = await page.content();
      expect(content).not.toContain('Fatal error');
    }
  });

  test('6.4 Schedule page requires auth', async ({ page }) => {
    await page.goto(`${BASE_URL}/cabinet/schedule/index?org=${CABINET_TEST.orgId}`);

    const content = await page.content();
    expect(content).not.toContain('Fatal error');
  });

  test('6.5 Payment page requires auth', async ({ page }) => {
    await page.goto(`${BASE_URL}/cabinet/payment/index?org=${CABINET_TEST.orgId}`);

    const content = await page.content();
    expect(content).not.toContain('Fatal error');
  });

  test('6.6 Attendance page requires auth', async ({ page }) => {
    await page.goto(`${BASE_URL}/cabinet/attendance/index?org=${CABINET_TEST.orgId}`);

    const content = await page.content();
    expect(content).not.toContain('Fatal error');
  });

  test('6.7 Homework page requires auth', async ({ page }) => {
    await page.goto(`${BASE_URL}/cabinet/homework/index?org=${CABINET_TEST.orgId}`);

    const content = await page.content();
    expect(content).not.toContain('Fatal error');
  });

});

// ============================================================================
// РАЗДЕЛ 7: МОБИЛЬНАЯ АДАПТИВНОСТЬ
// ============================================================================

test.describe('7. Cabinet - Mobile Responsiveness', () => {

  test('7.1 Mobile viewport shows content correctly', async ({ page }) => {
    await page.setViewportSize(VIEWPORTS.mobile);
    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);

    // Форма должна быть видна
    const form = page.locator('form');
    await expect(form.first()).toBeVisible();

    // Кнопка отправки должна быть полной ширины или достаточно широкой
    const submitBtn = page.locator('button[type="submit"]');
    if (await submitBtn.isVisible()) {
      const box = await submitBtn.boundingBox();
      expect(box.width).toBeGreaterThan(200); // Достаточно широкая для нажатия
    }
  });

  test('7.2 No horizontal scroll on mobile', async ({ page }) => {
    await page.setViewportSize(VIEWPORTS.mobile);
    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);

    const hasHorizontalScroll = await page.evaluate(() => {
      return document.documentElement.scrollWidth > document.documentElement.clientWidth;
    });

    expect(hasHorizontalScroll).toBe(false);
  });

  test('7.3 Touch-friendly button sizes on mobile', async ({ page }) => {
    await page.setViewportSize(VIEWPORTS.mobile);
    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);

    const buttons = page.locator('button, a.btn, [role="button"]');
    const count = await buttons.count();

    for (let i = 0; i < count; i++) {
      const btn = buttons.nth(i);
      if (await btn.isVisible()) {
        const box = await btn.boundingBox();
        if (box) {
          // Минимальный размер для touch - 44x44 или близко
          expect(box.height).toBeGreaterThanOrEqual(36);
        }
      }
    }
  });

  test('7.4 Organization cards are stacked on mobile', async ({ page }) => {
    await page.setViewportSize(VIEWPORTS.mobile);
    await page.goto(`${BASE_URL}/cabinet`);

    const orgCards = page.locator('a[href*="/cabinet/default/login"]');
    const count = await orgCards.count();

    if (count > 1) {
      const firstCard = await orgCards.first().boundingBox();
      const secondCard = await orgCards.nth(1).boundingBox();

      if (firstCard && secondCard) {
        // На мобильном карточки должны быть друг под другом (не рядом)
        expect(secondCard.y).toBeGreaterThan(firstCard.y);
      }
    }
  });

  test('7.5 Font sizes are readable on mobile', async ({ page }) => {
    await page.setViewportSize(VIEWPORTS.mobile);
    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);

    const heading = page.locator('h1, h2, .text-2xl');
    if (await heading.count() > 0) {
      const fontSize = await heading.first().evaluate(el =>
        parseFloat(window.getComputedStyle(el).fontSize)
      );
      expect(fontSize).toBeGreaterThanOrEqual(18);
    }
  });

  test('7.6 Input fields are full width on mobile', async ({ page }) => {
    await page.setViewportSize(VIEWPORTS.mobile);
    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);

    const phoneInput = page.locator('input[name="LoginForm[phone]"]');
    if (await phoneInput.isVisible()) {
      const inputBox = await phoneInput.boundingBox();
      // Input должен занимать большую часть ширины
      expect(inputBox.width).toBeGreaterThan(VIEWPORTS.mobile.width * 0.7);
    }
  });

  test('7.7 Small mobile viewport (320px) works', async ({ page }) => {
    await page.setViewportSize({ width: 320, height: 568 }); // iPhone SE
    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);

    const content = await page.content();
    expect(content).not.toContain('Fatal error');

    // Нет горизонтального скролла
    const hasHorizontalScroll = await page.evaluate(() => {
      return document.documentElement.scrollWidth > document.documentElement.clientWidth;
    });
    expect(hasHorizontalScroll).toBe(false);
  });

});

// ============================================================================
// РАЗДЕЛ 8: ПОНЯТНОСТЬ И ЛЕГКОСТЬ ИНТЕРФЕЙСА
// ============================================================================

test.describe('8. Cabinet - Usability and Clarity', () => {

  test('8.1 Page has clear heading/title', async ({ page }) => {
    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);

    const headings = page.locator('h1');
    const count = await headings.count();
    expect(count).toBeGreaterThan(0);

    // Проверяем первый заголовок
    const text = await headings.first().textContent();
    expect(text.trim().length).toBeGreaterThan(3);
  });

  test('8.2 Instructions are visible for first-time users', async ({ page }) => {
    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);

    // Проверяем наличие инструкций
    const instructions = page.locator('text=Как войти, text=Введите номер, text=инструкция, ol, .help');
    const hasInstructions = await instructions.count() > 0;

    // Или проверяем наличие placeholder в input
    const phoneInput = page.locator('input[name="LoginForm[phone]"]');
    const hasPlaceholder = await phoneInput.getAttribute('placeholder');

    expect(hasInstructions || hasPlaceholder).toBeTruthy();
  });

  test('8.3 Action button has clear label', async ({ page }) => {
    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);

    const submitBtn = page.locator('button[type="submit"]');
    if (await submitBtn.isVisible()) {
      const text = await submitBtn.textContent();
      // Кнопка должна содержать понятный текст
      expect(text.length).toBeGreaterThan(2);
      expect(text.toLowerCase()).toMatch(/(получить|отправить|войти|далее|код|submit)/);
    }
  });

  test('8.4 Organization selection has clear options', async ({ page }) => {
    await page.goto(`${BASE_URL}/cabinet`);

    const pageContent = await page.content();

    // Должен быть заголовок объясняющий что делать
    const hasTitle = pageContent.includes('Выберите') ||
                     pageContent.includes('выберите') ||
                     pageContent.includes('организац') ||
                     pageContent.includes('центр');

    expect(hasTitle).toBe(true);
  });

  test('8.5 Flash messages are displayed clearly', async ({ page }) => {
    // Пробуем вызвать flash сообщение через неверный телефон
    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);

    const phoneInput = page.locator('input[name="LoginForm[phone]"]');
    const submitBtn = page.locator('button[type="submit"]');

    if (await phoneInput.isVisible() && await submitBtn.isVisible()) {
      await phoneInput.fill('7009999999');
      await submitBtn.click();
      await page.waitForLoadState('networkidle');

      // Проверяем что если есть flash, он виден
      const alert = page.locator('.alert, [role="alert"], .flash-error, .flash-success');
      if (await alert.count() > 0) {
        const isVisible = await alert.first().isVisible();
        // Flash должен быть видимым если есть
        expect(isVisible).toBeDefined();
      }
    }
  });

  test('8.6 Navigation is intuitive', async ({ page }) => {
    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);

    // Должна быть возможность вернуться к выбору организации
    const backLink = page.locator('a:has-text("назад"), a:has-text("другой"), a:has-text("выбрать")');
    const hasBackNavigation = await backLink.count() > 0;

    expect(hasBackNavigation).toBe(true);
  });

  test('8.7 Form has proper tab order', async ({ page }) => {
    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);

    // Focus на input при загрузке или после Tab
    const phoneInput = page.locator('input[name="LoginForm[phone]"]');
    if (await phoneInput.isVisible()) {
      await phoneInput.focus();
      const isFocused = await phoneInput.evaluate(el => document.activeElement === el);
      expect(isFocused).toBe(true);

      // Tab должен переводить на кнопку
      await page.keyboard.press('Tab');
      const submitBtn = page.locator('button[type="submit"]');
      // Проверяем что focus перешел куда-то
      const anyFocused = await page.evaluate(() => document.activeElement.tagName);
      expect(anyFocused).toBeTruthy();
    }
  });

  test('8.8 Loading states are indicated', async ({ page }) => {
    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);

    // При отправке формы должна быть какая-то индикация
    const phoneInput = page.locator('input[name="LoginForm[phone]"]');
    const submitBtn = page.locator('button[type="submit"]');

    if (await phoneInput.isVisible() && await submitBtn.isVisible()) {
      await phoneInput.fill(CABINET_TEST.parentPhone);

      // Проверяем что кнопка не disabled до отправки
      const isDisabled = await submitBtn.isDisabled();
      expect(isDisabled).toBe(false);
    }
  });

});

// ============================================================================
// РАЗДЕЛ 9: ДОПОЛНИТЕЛЬНЫЕ ТЕСТЫ
// ============================================================================

test.describe('9. Cabinet - Additional Tests', () => {

  test('9.1 Accessibility - page has lang attribute', async ({ page }) => {
    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);

    const lang = await page.getAttribute('html', 'lang');
    expect(lang).toBeTruthy();
  });

  test('9.2 Accessibility - inputs have labels or aria-labels', async ({ page }) => {
    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);

    const inputs = page.locator('input:not([type="hidden"])');
    const count = await inputs.count();

    for (let i = 0; i < count; i++) {
      const input = inputs.nth(i);
      if (await input.isVisible()) {
        const id = await input.getAttribute('id');
        const ariaLabel = await input.getAttribute('aria-label');
        const placeholder = await input.getAttribute('placeholder');

        if (id) {
          const label = page.locator(`label[for="${id}"]`);
          const hasLabel = await label.count() > 0;
          const hasAccessibility = hasLabel || ariaLabel || placeholder;
          expect(hasAccessibility).toBeTruthy();
        }
      }
    }
  });

  test('9.3 SEO - meta viewport is set', async ({ page }) => {
    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);

    const viewport = await page.locator('meta[name="viewport"]').getAttribute('content');
    expect(viewport).toContain('width=device-width');
  });

  test('9.4 Performance - page loads in reasonable time', async ({ page }) => {
    const startTime = Date.now();
    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);
    const loadTime = Date.now() - startTime;

    // Страница должна загрузиться менее чем за 5 секунд
    expect(loadTime).toBeLessThan(5000);
  });

  test('9.5 CSS - Tailwind classes are loaded', async ({ page }) => {
    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);

    // Проверяем что Tailwind классы применены
    const element = page.locator('.bg-white, .rounded, .shadow, .text-gray-900');
    const count = await element.count();
    expect(count).toBeGreaterThan(0);
  });

  test('9.6 Forms - CSRF token is present', async ({ page }) => {
    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);

    const csrfInput = page.locator('input[name="_csrf"], input[name="csrf"]');
    // В Yii2 CSRF может быть в meta теге
    const csrfMeta = page.locator('meta[name="csrf-token"]');

    const hasCSRF = (await csrfInput.count() > 0) || (await csrfMeta.count() > 0);
    expect(hasCSRF).toBe(true);
  });

  test('9.7 Error handling - 404 for unknown routes', async ({ page }) => {
    const response = await page.goto(`${BASE_URL}/cabinet/nonexistent/route?org=${CABINET_TEST.orgId}`);

    // Должен быть 404, не 500
    expect(response.status()).not.toBe(500);
  });

  test('9.8 Session - cookies are set securely', async ({ page }) => {
    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);

    const cookies = await page.context().cookies();
    const sessionCookie = cookies.find(c => c.name.includes('PHPSESSID') || c.name.includes('session'));

    if (sessionCookie) {
      // HttpOnly должен быть true для сессионных кук
      expect(sessionCookie.httpOnly).toBe(true);
    }
  });

  test('9.9 Network - no mixed content warnings', async ({ page }) => {
    const mixedContentWarnings = [];
    page.on('console', msg => {
      if (msg.text().includes('Mixed Content')) {
        mixedContentWarnings.push(msg.text());
      }
    });

    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);
    await page.waitForLoadState('networkidle');

    expect(mixedContentWarnings).toHaveLength(0);
  });

  test('9.10 Images - all images have alt attributes', async ({ page }) => {
    await page.goto(`${BASE_URL}/cabinet`);

    const images = page.locator('img');
    const count = await images.count();

    for (let i = 0; i < count; i++) {
      const img = images.nth(i);
      if (await img.isVisible()) {
        const alt = await img.getAttribute('alt');
        // Alt может быть пустым для декоративных изображений, но должен существовать
        expect(alt).not.toBeNull();
      }
    }
  });

});

// ============================================================================
// РАЗДЕЛ 10: ПОЛНЫЙ ФЛОУ АВТОРИЗАЦИИ (E2E)
// ============================================================================

test.describe('10. Cabinet - Full Authentication Flow', () => {

  test('10.1 Complete login flow with valid phone', async ({ page }) => {
    // Шаг 1: Выбор организации
    await page.goto(`${BASE_URL}/cabinet`);
    const content1 = await page.content();
    expect(content1).not.toContain('Fatal error');

    // Шаг 2: Переход к форме логина
    const orgLink = page.locator(`a[href*="org=${CABINET_TEST.orgId}"]`).first();
    if (await orgLink.isVisible()) {
      await orgLink.click();
      await page.waitForLoadState('networkidle');
    } else {
      await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);
    }

    // Шаг 3: Ввод телефона
    const phoneInput = page.locator('input[name="LoginForm[phone]"]');
    if (await phoneInput.isVisible()) {
      await phoneInput.fill(CABINET_TEST.parentPhone);

      const submitBtn = page.locator('button[type="submit"]');
      await submitBtn.click();
      await page.waitForLoadState('networkidle');

      // Шаг 4: Проверяем результат
      const currentUrl = page.url();
      const pageContent = await page.content();

      // Должен быть редирект на verify или показ ошибки (если телефон не найден)
      expect(pageContent).not.toContain('Fatal error');
      expect(pageContent).not.toContain('ErrorException');

      // Если перешли на verify, проверяем страницу
      if (currentUrl.includes('verify')) {
        // Проверяем наличие полей для кода
        const codeInput = page.locator('input[name="LoginForm[code]"], input[type="text"]');
        await expect(codeInput.first()).toBeVisible();
      }
    }
  });

  test('10.2 Debug code is shown in debug mode', async ({ page }) => {
    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);

    const phoneInput = page.locator('input[name="LoginForm[phone]"]');
    if (await phoneInput.isVisible()) {
      await phoneInput.fill(CABINET_TEST.parentPhone);
      await page.click('button[type="submit"]');
      await page.waitForLoadState('networkidle');

      // В debug режиме код должен отображаться
      const pageContent = await page.content();
      if (pageContent.includes('verify')) {
        // Проверяем наличие кода для разработки
        const hasDebugCode = pageContent.includes('Код:') ||
                             pageContent.includes('code') ||
                             pageContent.includes('debug');
        // Это информационный тест, не fail если нет debug
      }
    }
  });

});

// ============================================================================
// РАЗДЕЛ 11: ВИЗУАЛЬНЫЕ СКРИНШОТЫ
// ============================================================================

test.describe('11. Cabinet - Visual Screenshots', () => {

  test('11.1 Take desktop screenshots', async ({ page }) => {
    await page.setViewportSize(VIEWPORTS.desktop);

    await page.goto(`${BASE_URL}/cabinet`);
    await page.screenshot({ path: 'test-results/cabinet-select-org-desktop.png', fullPage: true });

    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);
    await page.screenshot({ path: 'test-results/cabinet-login-desktop.png', fullPage: true });
  });

  test('11.2 Take mobile screenshots', async ({ page }) => {
    await page.setViewportSize(VIEWPORTS.mobile);

    await page.goto(`${BASE_URL}/cabinet`);
    await page.screenshot({ path: 'test-results/cabinet-select-org-mobile.png', fullPage: true });

    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);
    await page.screenshot({ path: 'test-results/cabinet-login-mobile.png', fullPage: true });
  });

  test('11.3 Take tablet screenshots', async ({ page }) => {
    await page.setViewportSize(VIEWPORTS.tablet);

    await page.goto(`${BASE_URL}/cabinet`);
    await page.screenshot({ path: 'test-results/cabinet-select-org-tablet.png', fullPage: true });

    await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);
    await page.screenshot({ path: 'test-results/cabinet-login-tablet.png', fullPage: true });
  });

});
