const { test, expect } = require('@playwright/test');
const { loginAsAdmin } = require('../../helpers/login');
const secrets = require("../../../secrets.json");

import examplePage from '../../data/example-page.json';

// Helper to switch Bootstrap tabs by clicking the tab button and waiting for activation
async function switchTab(page, tabSelector) {
    await page.click(tabSelector);
    await page.waitForSelector(`${tabSelector}.active`);
}

test('Admin can create a new page', async ({ page }) => {
    await loginAsAdmin(page);

    await page.goto(secrets.adminUrl + 'pages/new/');

    await switchTab(page, '#info');
    await page.fill('input[name="page_linkname"]', examplePage.link_name);
    await page.fill('input[name="page_permalink"]', examplePage.slug);

    await switchTab(page, '#metas');
    await page.fill('input[name="page_title"]', examplePage.title);
    await page.fill('textarea[name="page_meta_description"]', examplePage.meta.description);
    await page.fill('input[name="page_meta_author"]', examplePage.meta.author);

    await page.click('button[type="submit"][name="save_page"]');

    await expect(page.locator('button[name="delete_page"]')).toBeVisible();
});