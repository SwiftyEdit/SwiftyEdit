const { test, expect } = require('@playwright/test');
const secrets = require('../../../secrets.json');

test('Unknown URL returns 404 with noindex robots meta', async ({ page }) => {
    const url = new URL('doesnotexist-' + Date.now() + '/', secrets.frontendUrl).href;
    const response = await page.goto(url);

    expect(response.status()).toBe(404);
    await expect(page.locator('meta[name="robots"]')).toHaveAttribute('content', /noindex/);
});
