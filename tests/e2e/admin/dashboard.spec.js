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

test('Activity history opens from the tab', async ({ page }) => {
    await page.goto(secrets.adminUrl + '/dashboard/');
    const activity = page.locator('#dashActivity');
    await activity.locator('#dashTabHistory').click();
    await expect(activity.locator('#dashTabHistory')).toHaveAttribute('aria-selected', 'true');
    await expect(activity.locator('.dash-history')).toBeVisible();
});

test('Log page lists entries', async ({ page }) => {
    await page.goto(secrets.adminUrl + '/dashboard/log/');
    await expect(page.locator('#dashLogList table')).toBeVisible();
});
