const { test, expect } = require('@playwright/test');
const { loginAsAdmin } = require('../../helpers/login');
const secrets = require("../../../secrets.json");

import exampleCategory from '../../data/example-category.json';

// Helper to switch Bootstrap tabs by clicking the tab link and waiting for activation.
// Unlike the pages form, the categories/shop forms put the target id on the
// tab-pane, not on the tab link itself, so the link is addressed via its href.
async function switchTab(page, tabSelector) {
    await page.click(`a[data-bs-toggle="tab"][href="${tabSelector}"]`);
    await page.waitForSelector(`${tabSelector}.active`);
}

test('Admin can create a new category', async ({ page }) => {
    await loginAsAdmin(page);

    await page.goto(secrets.adminUrl + 'categories/new/');

    await switchTab(page, '#info');
    await page.fill('input[name="cat_name"]', exampleCategory.name);
    await page.fill('input[name="cat_title"]', exampleCategory.title);

    await switchTab(page, '#metas');
    await page.fill('textarea[name="cat_description"]', exampleCategory.description);
    await page.fill('input[name="cat_name_clean"]', exampleCategory.slug);

    await page.click('button[type="submit"][name="save_category"]');

    // saving a new category redirects to its edit page
    await page.waitForURL(/\/admin\/categories\/edit\/\d+\/?/);
    await expect(page.locator('button[name="save_category"]')).toHaveAttribute('value', /^\d+$/);
});
