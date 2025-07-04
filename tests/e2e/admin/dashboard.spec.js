const { test, expect } = require('@playwright/test');
const { loginAsAdmin } = require('../../helpers/login');
const secrets = require("../../../secrets.json");

test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
});

test('Admin Dashboard is available', async ({ page }) => {
    await page.goto(secrets.adminUrl + '/dashboard/');
    await expect(page.locator('text=Dashboard')).toBeVisible();
});