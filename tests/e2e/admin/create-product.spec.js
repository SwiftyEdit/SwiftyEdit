const { test, expect } = require('@playwright/test');
const { loginAsAdmin } = require('../../helpers/login');
const secrets = require("../../../secrets.json");

import exampleProduct from '../../data/example-product.json';

// Helper to switch Bootstrap tabs by clicking the tab link and waiting for activation.
// Unlike the pages form, the categories/shop forms put the target id on the
// tab-pane, not on the tab link itself, so the link is addressed via its href.
async function switchTab(page, tabSelector) {
    await page.click(`a[data-bs-toggle="tab"][href="${tabSelector}"]`);
    await page.waitForSelector(`${tabSelector}.active`);
}

test('Admin can create a new product', async ({ page }) => {
    await loginAsAdmin(page);

    await page.goto(secrets.adminUrl + 'shop/new/');

    // "intro" tab is active by default
    await page.fill('input[name="title"]', exampleProduct.title);
    await page.fill('input[name="link_name"]', exampleProduct.link_name);
    await page.fill('input[name="product_number"]', exampleProduct.product_number);

    await switchTab(page, '#prices_delivery');
    await page.fill('input[name="product_price_net"]', exampleProduct.price_net);

    await switchTab(page, '#seo');
    await page.fill('input[name="slug"]', exampleProduct.slug);
    await page.fill('textarea[name="meta_description"]', exampleProduct.meta.description);

    await page.click('button[type="submit"][name="save_product"]');

    // saving a new product redirects to its edit page, where a delete button becomes available
    await expect(page.locator('button[name="delete_product"]')).toBeVisible();
});
