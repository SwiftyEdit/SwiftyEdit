const { test, expect } = require('@playwright/test');
const secrets = require('../../../secrets.json');

/**
 * The sidebar filter of a product list shows the number of matching products
 * per filter item. Once a filter is selected, the counts of the other groups
 * must take it into account, while the counts of the selected group itself
 * stay the same (items within a group are combined with OR).
 *
 * The url is optional in secrets.json ("shopUrl") because it depends on the
 * pages and products of the local installation.
 */

/**
 * Read the counts of all filter groups: [{ title, items: { name: count } }]
 * Range groups and the "All" option of radio groups carry no count.
 */
async function readFilterCounts(page) {
    return page.locator('.shop-sidebar .card').evaluateAll(cards => cards.map(card => {
        const items = {};
        card.querySelectorAll('a.filter-option').forEach(link => {
            const match = link.innerText.trim().match(/^(.*)\((\d+)\)$/s);
            if (match) {
                items[match[1].trim()] = parseInt(match[2], 10);
            }
        });
        return {
            title: card.querySelector('.card-header').innerText.trim(),
            items: items,
        };
    }).filter(group => Object.keys(group.items).length > 0));
}

test.describe('Shop filter counts', () => {

    test.skip(!secrets.shopUrl, '"shopUrl" is not set in secrets.json');

    test('counts follow the selected filter', async ({ page }) => {
        await page.goto(secrets.shopUrl);

        const before = await readFilterCounts(page);
        test.skip(before.length < 2, 'need at least two filter groups with counters');

        // pick the first item that has matching products
        const group = before.find(g => Object.values(g.items).some(cnt => cnt > 0));
        const itemName = Object.keys(group.items).find(name => group.items[name] > 0);

        await page.locator('.shop-sidebar .card')
            .filter({ has: page.locator('.card-header', { hasText: group.title }) })
            .locator('a.filter-option', { hasText: itemName })
            .first()
            .click();
        await page.waitForLoadState('load');

        // the count of the selected item matches the product list,
        // as long as all products fit on one page
        const paginated = await page.locator('a[href*="page="]').count() > 0;
        if (!paginated) {
            await expect(page.locator('.product-list-entry')).toHaveCount(group.items[itemName]);
        }

        const after = await readFilterCounts(page);

        // with one active filter, the count of any item of another group
        // must match the product list of both filters combined
        const otherGroup = after.find(g => g.title !== group.title);
        const otherName = Object.keys(otherGroup.items)[0];
        const otherCount = otherGroup.items[otherName];

        for (const groupBefore of before) {
            const groupAfter = after.find(g => g.title === groupBefore.title);
            expect(groupAfter, `group "${groupBefore.title}" is still shown`).toBeTruthy();

            for (const [name, cnt] of Object.entries(groupBefore.items)) {
                if (groupBefore.title === group.title) {
                    // own group ignores its own selection
                    expect(groupAfter.items[name], `"${name}" in the selected group`).toBe(cnt);
                } else {
                    // other groups can only shrink
                    expect(groupAfter.items[name], `"${name}" in "${groupBefore.title}"`).toBeLessThanOrEqual(cnt);
                }
            }
        }

        await page.locator('.shop-sidebar .card')
            .filter({ has: page.locator('.card-header', { hasText: otherGroup.title }) })
            .locator('a.filter-option', { hasText: otherName })
            .first()
            .click();
        await page.waitForLoadState('load');

        if (await page.locator('a[href*="page="]').count() === 0) {
            await expect(page.locator('.product-list-entry')).toHaveCount(otherCount);
        }
    });
});
