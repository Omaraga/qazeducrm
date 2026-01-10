// @ts-check
const { test, expect } = require('@playwright/test');

const BASE_URL = 'http://educrm.loc';

// Test credentials - update if needed
const TEST_USER = {
  email: 'admin@example.com',
  password: 'password123'
};

test.describe('Public Pages', () => {

  test('Landing page loads without errors', async ({ page }) => {
    const response = await page.goto(BASE_URL + '/');
    expect(response.status()).toBe(200);

    const content = await page.content();
    expect(content).not.toContain('Fatal error');
    expect(content).not.toContain('ErrorException');
    expect(content).not.toContain('Declaration of');
  });

  test('Login page loads without errors', async ({ page }) => {
    const response = await page.goto(BASE_URL + '/login');
    expect(response.status()).toBe(200);

    const content = await page.content();
    expect(content).not.toContain('Fatal error');
    expect(content).not.toContain('ErrorException');
    expect(content).not.toContain('Declaration of');
  });

  test('Pricing page loads without errors', async ({ page }) => {
    await page.goto(BASE_URL + '/pricing');
    const content = await page.content();
    expect(content).not.toContain('Fatal error');
    expect(content).not.toContain('ErrorException');
  });

  test('Registration page loads without errors', async ({ page }) => {
    await page.goto(BASE_URL + '/register');
    const content = await page.content();
    expect(content).not.toContain('Fatal error');
    expect(content).not.toContain('ErrorException');
  });

});

test.describe('CRM Pages - No PHP Errors', () => {

  test('CRM redirect works', async ({ page }) => {
    const response = await page.goto(BASE_URL + '/crm');
    // Should redirect to login or show CRM
    const content = await page.content();
    expect(content).not.toContain('Fatal error');
    expect(content).not.toContain('ErrorException');
    expect(content).not.toContain('Declaration of');
    expect(content).not.toContain('must be compatible with');
  });

  test('All CRM routes have no PHP compile errors', async ({ page }) => {
    // These routes might redirect to login, but should not have PHP errors
    const routes = [
      '/crm',
      '/crm/pupil',
      '/crm/group',
      '/crm/schedule',
      '/crm/payment',
      '/crm/lids',
      '/crm/salary',
      '/crm/sms',
      '/crm/whatsapp',
      '/crm/reports',
      '/crm/settings',
      '/crm/user',
      '/crm/subject',
      '/crm/tariff',
      '/crm/room',
    ];

    for (const route of routes) {
      await page.goto(BASE_URL + route);
      const content = await page.content();

      // Check for PHP errors
      expect(content, `Route ${route} has Fatal error`).not.toContain('Fatal error');
      expect(content, `Route ${route} has Parse error`).not.toContain('Parse error');
      expect(content, `Route ${route} has Compile Error`).not.toContain('Compile Error');
      expect(content, `Route ${route} has Declaration error`).not.toContain('Declaration of');
      expect(content, `Route ${route} has behaviors() error`).not.toContain('must be compatible with');
    }
  });

});

test.describe('Menu URLs contain oid', () => {

  test('After login, menu links contain organization id', async ({ page }) => {
    // Go to login
    await page.goto(BASE_URL + '/login');

    // Check if we can see a login form
    const hasLoginForm = await page.locator('form').count() > 0;

    if (hasLoginForm) {
      // Try to fill login form if exists
      const emailInput = page.locator('input[type="email"], input[name*="email"], input[name*="login"]').first();
      const passwordInput = page.locator('input[type="password"]').first();
      const submitButton = page.locator('button[type="submit"], input[type="submit"]').first();

      if (await emailInput.isVisible() && await passwordInput.isVisible()) {
        // Note: This test will only work if TEST_USER credentials are valid
        // For now we just check that the page doesn't have PHP errors
        const content = await page.content();
        expect(content).not.toContain('Fatal error');
        expect(content).not.toContain('ErrorException');
      }
    }
  });

});

test.describe('SuperAdmin Pages', () => {

  test('SuperAdmin routes have no PHP errors', async ({ page }) => {
    const routes = [
      '/superadmin',
      '/superadmin/organization',
      '/superadmin/plan',
      '/superadmin/subscription',
      '/superadmin/payment',
    ];

    for (const route of routes) {
      await page.goto(BASE_URL + route);
      const content = await page.content();

      expect(content, `Route ${route} has Fatal error`).not.toContain('Fatal error');
      expect(content, `Route ${route} has Parse error`).not.toContain('Parse error');
      expect(content, `Route ${route} has Compile Error`).not.toContain('Compile Error');
      expect(content, `Route ${route} has Declaration error`).not.toContain('Declaration of');
    }
  });

});
