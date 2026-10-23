import { test, expect } from '@playwright/test';
import { screenshotDir } from './helpers.js';

const viewports = { desktop: { width: 1440, height: 900 }, tablet: { width: 820, height: 1180 }, mobile: { width: 390, height: 844 } };
const pages = { home: '/', services: '/services', service: '/services/research-proposals', portfolio: '/portfolio', subjects: '/subjects', contact: '/contact', register: '/register', login: '/login' };

for (const [device, viewport] of Object.entries(viewports)) {
    for (const [name, path] of Object.entries(pages)) {
        test(`${device} ${name}: no horizontal overflow, no broken images`, async ({ page }) => {
            await page.setViewportSize(viewport);
            await page.goto(path, { waitUntil: 'networkidle' });

            const overflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
            expect(overflow, 'horizontal overflow in px').toBeLessThanOrEqual(0);

            await page.evaluate(async () => {
                for (let y = 0; y < document.body.scrollHeight; y += 500) {
                    window.scrollTo(0, y);
                    await new Promise((r) => setTimeout(r, 30));
                }
                window.scrollTo(0, 0);
            });
            await page.waitForLoadState('networkidle');
            const broken = await page.evaluate(() => [...document.images].filter((img) => img.complete && img.naturalWidth === 0).map((img) => img.src));
            expect(broken).toEqual([]);

            await page.screenshot({ path: `${screenshotDir}/${device}-${name}.png`, fullPage: true });
        });
    }
}

test('public site exposes no private routes or data in navigation', async ({ page }) => {
    await page.goto('/');
    const hrefs = await page.$$eval('a[href]', (links) => links.map((a) => a.getAttribute('href')));
    expect(hrefs.filter((h) => /\/admin|\.xlsx|storage\/app/.test(h))).toEqual([]);
});
