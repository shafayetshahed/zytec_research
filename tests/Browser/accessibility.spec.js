import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';
import { latestOtpFor } from './helpers.js';

const publicPages = ['/', '/about', '/services', '/services/data-analysis', '/subjects', '/portfolio', '/how-it-works', '/why-choose-us', '/faq', '/contact', '/privacy-policy', '/terms', '/academic-integrity', '/register', '/login', '/forgot-password', '/does-not-exist'];

async function scan(page, label) {
    const results = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa']).analyze();
    const serious = results.violations.filter((v) => ['serious', 'critical'].includes(v.impact));
    const summary = results.violations.map((v) => `${label} [${v.impact}] ${v.id}: ${v.help} (${v.nodes.length}) e.g. ${v.nodes[0]?.target.join(' ')}`);
    if (summary.length) console.log(summary.join('\n'));
    expect(serious, serious.map((v) => `${v.id}: ${v.help}`).join('\n')).toEqual([]);
}

for (const path of publicPages) {
    test(`axe: ${path}`, async ({ page }) => {
        await page.goto(path);
        await scan(page, path);
    });
}

test('axe: OTP page, student dashboard, submission form and admin dashboard', async ({ page }) => {
    const email = `a11y-${Date.now()}@example.test`;
    await page.goto('/register');
    await page.getByLabel('Full name').fill('Accessibility Check');
    await page.getByLabel('Email address').fill(email);
    await page.getByLabel('WhatsApp number').fill('+61412345678');
    await page.getByLabel('Password', { exact: true }).fill('Accessible-pass-42');
    await page.getByLabel('Confirm password').fill('Accessible-pass-42');
    await page.getByRole('checkbox').check();
    await page.getByRole('button', { name: 'Create account' }).click();
    await scan(page, '/verify-email');

    await page.getByLabel('Verification code').fill(await latestOtpFor(email));
    await page.getByRole('button', { name: 'Verify email' }).click();
    await scan(page, '/student/dashboard');
    await page.goto('/student/submissions/create');
    await scan(page, '/student/submissions/create');

    // Error state of the form is also accessible
    await page.getByRole('button', { name: 'Submit brief' }).click();
    await expect(page.locator('#error-summary')).toBeVisible();
    await scan(page, '/student/submissions/create (errors)');

    await page.goto('/student/profile');
    await scan(page, '/student/profile');

    await page.getByRole('button', { name: 'Sign out' }).click();
    await page.goto('/login');
    await page.getByLabel('Email address').fill(process.env.E2E_ADMIN_EMAIL);
    await page.getByLabel('Password').fill(process.env.E2E_ADMIN_PASSWORD);
    await page.getByRole('button', { name: 'Sign in' }).click();
    for (const path of ['/admin', '/admin/submissions', '/admin/students', '/admin/notifications', '/admin/settings']) {
        await page.goto(path);
        await scan(page, path);
    }
});

test('keyboard: skip link and mobile menu are operable', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/');
    await page.keyboard.press('Tab');
    await expect(page.getByRole('link', { name: 'Skip to main content' })).toBeFocused();

    const menu = page.getByRole('button', { name: 'Menu' });
    await menu.focus();
    await page.keyboard.press('Enter');
    await expect(menu).toHaveAttribute('aria-expanded', 'true');
    await expect(page.locator('#mobile-nav')).toBeVisible();
    await page.keyboard.press('Escape');
    await expect(menu).toHaveAttribute('aria-expanded', 'false');
    await expect(menu).toBeFocused();
});
