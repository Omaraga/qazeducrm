// @ts-check
const { test, expect } = require('@playwright/test');

const BASE_URL = 'http://educrm.loc';
const CABINET_TEST = {
  parentPhone: '7771234567',
  orgId: 1,
};

test('Debug Cabinet Login Flow', async ({ page }) => {
  // Шаг 1: Открываем страницу логина
  console.log('Step 1: Opening login page...');
  await page.goto(`${BASE_URL}/cabinet/default/login?org=${CABINET_TEST.orgId}`);
  await page.waitForLoadState('networkidle');
  await page.screenshot({ path: 'test-results/debug-step1-login-page.png' });

  // Проверяем контент страницы
  const content1 = await page.content();
  console.log('Page loaded, checking for errors...');
  if (content1.includes('Fatal error')) {
    console.log('FATAL ERROR on login page!');
    return;
  }

  // Шаг 2: Находим и заполняем поле телефона
  console.log('Step 2: Filling phone number...');
  const phoneInput = page.locator('input[name="LoginForm[phone]"]');
  const phoneVisible = await phoneInput.isVisible();
  console.log('Phone input visible:', phoneVisible);

  if (!phoneVisible) {
    console.log('Phone input not found!');
    await page.screenshot({ path: 'test-results/debug-step2-no-phone-input.png' });
    return;
  }

  await phoneInput.fill(CABINET_TEST.parentPhone);
  await page.screenshot({ path: 'test-results/debug-step2-phone-filled.png' });

  // Шаг 3: Находим кнопку submit
  console.log('Step 3: Finding submit button...');
  const submitBtn = page.locator('button[type="submit"]');
  const btnVisible = await submitBtn.isVisible();
  console.log('Submit button visible:', btnVisible);

  if (!btnVisible) {
    console.log('Submit button not found!');
    return;
  }

  const btnText = await submitBtn.textContent();
  console.log('Submit button text:', btnText);

  // Шаг 4: Кликаем на кнопку и ждем ответа
  console.log('Step 4: Clicking submit button...');

  // Устанавливаем слушатель на ответы
  const responsePromise = page.waitForResponse(response => {
    console.log('Response:', response.url(), response.status());
    return response.url().includes('cabinet');
  }, { timeout: 10000 }).catch(e => {
    console.log('No response caught:', e.message);
    return null;
  });

  await submitBtn.click();
  console.log('Button clicked, waiting for navigation...');

  // Ждем навигации или изменения страницы
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(2000);

  await page.screenshot({ path: 'test-results/debug-step4-after-submit.png' });

  // Шаг 5: Проверяем результат
  console.log('Step 5: Checking result...');
  const currentUrl = page.url();
  console.log('Current URL after submit:', currentUrl);

  const content2 = await page.content();

  // Проверяем на ошибки валидации
  if (content2.includes('не найдены') || content2.includes('не найден')) {
    console.log('VALIDATION ERROR: Pupils not found!');
    console.log('This means the phone validation failed.');
  }

  if (content2.includes('field-loginform-phone') && content2.includes('has-error')) {
    console.log('FORM ERROR: Phone field has error!');
  }

  // Ищем сообщение об ошибке
  const errorMsg = page.locator('.text-danger-600, .text-red-500, .invalid-feedback, .help-block-error');
  const errorCount = await errorMsg.count();
  console.log('Error messages count:', errorCount);

  if (errorCount > 0) {
    const errorText = await errorMsg.first().textContent();
    console.log('Error text:', errorText);
  }

  // Проверяем наличие flash сообщений
  const flashMsg = page.locator('.alert, [role="alert"]');
  const flashCount = await flashMsg.count();
  console.log('Flash messages count:', flashCount);

  if (flashCount > 0) {
    const flashText = await flashMsg.first().textContent();
    console.log('Flash text:', flashText);
  }

  // Проверяем редирект на verify
  if (currentUrl.includes('verify')) {
    console.log('SUCCESS: Redirected to verify page!');

    // Ищем код в debug режиме
    const verifyContent = await page.content();

    // Ищем код разными способами
    const codePatterns = [
      /Код[:\s]+(\d{4})/i,
      /code[:\s="']+(\d{4})/i,
      /debug[_-]?code[:\s="']+(\d{4})/i,
    ];

    let code = null;
    for (const pattern of codePatterns) {
      const match = verifyContent.match(pattern);
      if (match) {
        code = match[1];
        console.log('Found code with pattern:', pattern, '-> Code:', code);
        break;
      }
    }

    if (!code) {
      console.log('Code not found in page content. Looking in alerts...');
      const alerts = page.locator('.alert, [role="alert"]');
      if (await alerts.count() > 0) {
        const alertText = await alerts.first().textContent();
        console.log('Alert text:', alertText);
        const codeMatch = alertText.match(/(\d{4})/);
        if (codeMatch) {
          code = codeMatch[1];
          console.log('Found code in alert:', code);
        }
      }
    }

    await page.screenshot({ path: 'test-results/debug-step5-verify-page.png' });

    if (code) {
      // Шаг 6: Вводим код
      console.log('Step 6: Entering verification code:', code);

      const codeInput = page.locator('input[name="LoginForm[code]"]');
      if (await codeInput.isVisible()) {
        await codeInput.fill(code);
        await page.screenshot({ path: 'test-results/debug-step6-code-filled.png' });

        // Отправляем код
        const verifyBtn = page.locator('button[type="submit"]');
        await verifyBtn.click();
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(1000);

        await page.screenshot({ path: 'test-results/debug-step7-after-verify.png' });

        const finalUrl = page.url();
        console.log('Final URL:', finalUrl);

        if (finalUrl.includes('index') || finalUrl.includes('default')) {
          console.log('SUCCESS: Logged in to dashboard!');
        }
      }
    }
  } else {
    console.log('FAILED: Did not redirect to verify page');
    console.log('URL:', currentUrl);

    // Выводим часть контента для диагностики
    console.log('Page content (first 1000 chars):', content2.substring(0, 1000));
  }
});
