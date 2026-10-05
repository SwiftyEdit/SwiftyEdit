const { test, expect } = require('@playwright/test');
const secrets = require('../../../secrets.json');

/**
 * Blog and events pages share the same url scheme below their permalink:
 *   /<page>/                     list
 *   /<page>/<category>/          list filtered by category
 *   /<page>/p/<n>/               pagination
 *   /<page>/<slug>-<id>.html     single post / event
 * Everything else must be a 404 (no soft 404 with the list and status 200).
 *
 * The urls are optional in secrets.json ("blogUrl", "eventsUrl") because they
 * depend on the pages of the local installation.
 */
const sections = [
    { name: 'Blog', key: 'blogUrl', url: secrets.blogUrl },
    { name: 'Events', key: 'eventsUrl', url: secrets.eventsUrl },
];

for (const section of sections) {

    test.describe(section.name, () => {

        test.skip(!section.url, `"${section.key}" is not set in secrets.json`);

        test('list page returns 200', async ({ page }) => {
            const response = await page.goto(section.url);
            expect(response.status()).toBe(200);
        });

        test('first entry of the list returns 200', async ({ page }) => {
            await page.goto(section.url);
            const entryLink = page.locator('a.post-headline-link').first();
            test.skip(await entryLink.count() === 0, 'list has no entries');

            const href = await entryLink.getAttribute('href');
            const response = await page.goto(new URL(href, section.url).href);
            expect(response.status()).toBe(200);
        });

        test('unknown sub path returns 404 with noindex', async ({ page }) => {
            const response = await page.goto(new URL('does-not-exist-' + Date.now() + '/', section.url).href);
            expect(response.status()).toBe(404);
            await expect(page.locator('meta[name="robots"]')).toHaveAttribute('content', /noindex/);
        });

        test('unknown entry id returns 404 with noindex', async ({ page }) => {
            const response = await page.goto(new URL('does-not-exist-999999999.html', section.url).href);
            expect(response.status()).toBe(404);
            await expect(page.locator('meta[name="robots"]')).toHaveAttribute('content', /noindex/);
        });

        test('category page returns 200, unknown path behind it 404', async ({ page }) => {
            await page.goto(section.url);

            // category links look like /<page>/<category>/ - pagination (/p/)
            // and entries (.html) are excluded
            const listPath = new URL(section.url).pathname;
            const hrefs = await page.locator('a[href]').evaluateAll(links => links.map(a => a.getAttribute('href')));
            const categoryPattern = new RegExp('^' + listPath + '(?!p/)[^/.]+/$');
            const categoryHref = hrefs.find(href => categoryPattern.test(href));
            test.skip(!categoryHref, 'no category link found on the list page');

            const categoryUrl = new URL(categoryHref, section.url).href;
            const response = await page.goto(categoryUrl);
            expect(response.status()).toBe(200);

            const unknownResponse = await page.goto(new URL('does-not-exist/', categoryUrl).href);
            expect(unknownResponse.status()).toBe(404);
        });

        test('page beyond the last page renders no empty entry', async ({ page }) => {
            const response = await page.goto(new URL('p/9999/', section.url).href);
            expect(response.status()).toBe(200);
            // the counter-only row of se_get_*_entries() used to be rendered
            // as an entry with a broken link like /blog/-.html
            await expect(page.locator('a.post-headline-link')).toHaveCount(0);
            await expect(page.locator('a[href$="/-.html"]')).toHaveCount(0);
        });
    });
}
