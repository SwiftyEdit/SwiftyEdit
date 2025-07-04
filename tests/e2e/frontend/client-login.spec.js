const { test, expect } = require('@playwright/test');
const { loginAsUser } = require('../../helpers/login.js');

test.beforeEach(async ({ page }) => {
    await loginAsUser(page);
});

test('User can login', async ({ page }) => {
    await expect(page.locator('a.link-profile')).toBeVisible();
});