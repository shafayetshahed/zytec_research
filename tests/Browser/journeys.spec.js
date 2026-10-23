import { test, expect } from '@playwright/test';
import fs from 'node:fs';
import { latestOtpFor, syntheticPdf, screenshotDir, snapAllSizes } from './helpers.js';

test.describe.configure({ mode: 'serial' });

const stamp = Date.now();
const student = {
    name: 'E2E Synthetic Student',
    email: `e2e-student-${stamp}@example.test`,
    whatsapp: '+44 7700 900456',
    password: `Synthetic-pass-${stamp}`,
};
let reference;

test('student journey: register, verify OTP, submit brief, sign out and back in', async ({ page }) => {
    // 1. Homepage
    await page.goto('/');
    await expect(page.getByRole('heading', { level: 1 })).toContainText('Understand the brief.');

    // 2. Register with a synthetic account
    await page.getByRole('link', { name: 'Submit your brief' }).first().click();
    await expect(page).toHaveURL(/\/register$/);
    await page.getByLabel('Full name').fill(student.name);
    await page.getByLabel('Email address').fill(student.email);
    await page.getByLabel('WhatsApp number').fill(student.whatsapp);
    await page.getByLabel('Password', { exact: true }).fill(student.password);
    await page.getByLabel('Confirm password').fill(student.password);
    await page.getByRole('checkbox').check();
    await page.getByRole('button', { name: 'Create account' }).click();

    // 3–4. Retrieve OTP from the test mail transport and verify
    await expect(page).toHaveURL(/\/verify-email$/);
    await expect(page.getByRole('heading', { name: 'Check your email' })).toBeVisible();
    await snapAllSizes(page, 'otp', expect);
    const code = await latestOtpFor(student.email);
    expect(page.url()).not.toContain(code);
    await page.getByLabel('Verification code').fill(code);
    await page.getByRole('button', { name: 'Verify email' }).click();

    // 6. Dashboard
    await expect(page).toHaveURL(/\/student\/dashboard$/);
    await expect(page.getByRole('heading', { name: `Welcome, ${student.name}` })).toBeVisible();
    await expect(page.getByText('You have not submitted a brief yet.')).toBeVisible();
    await expect(page.getByText('+447700900456')).toBeVisible();
    await snapAllSizes(page, 'student-dashboard-empty', expect);

    // 7. Submit a synthetic brief
    await page.getByRole('link', { name: 'Submit a new brief' }).click();
    await snapAllSizes(page, 'submission-form', expect);
    await page.getByLabel('Assignment or project name').fill('E2E strategic analysis report');
    await page.getByLabel('Module code').fill('e2e101');
    await page.getByLabel('Subject area').selectOption('Business and management');
    await page.getByLabel('Country of study').selectOption('United Kingdom');
    await page.getByLabel('Assignment brief').setInputFiles(syntheticPdf());
    await expect(page.locator('#brief-selected')).toContainText('synthetic-brief.pdf');
    await page.getByRole('checkbox', { name: /WhatsApp number is correct/ }).check();
    await page.getByRole('checkbox', { name: /document-handling notice/ }).check();
    await page.getByRole('button', { name: 'Submit brief' }).click();

    // 8. Confirmation
    await expect(page.getByText('Your brief has been submitted.')).toBeVisible();
    reference = (await page.locator('main p.font-mono').first().textContent()).trim();
    expect(reference).toMatch(/^ZR-\d{4}-[A-Z0-9]{6}$/);
    await expect(page.getByRole('heading', { name: 'E2E strategic analysis report' })).toBeVisible();
    await expect(page.getByText('E2E101').first()).toBeVisible();
    await expect(page.getByRole('link', { name: 'synthetic-brief.pdf' })).toBeVisible();
    await snapAllSizes(page, 'submission-confirmation', expect);

    // Download own brief
    const download = page.waitForEvent('download');
    await page.getByRole('link', { name: 'synthetic-brief.pdf' }).click();
    expect((await download).suggestedFilename()).toBe('synthetic-brief.pdf');

    // 9–10. Sign out, sign back in, submission still listed
    await page.getByRole('button', { name: 'Sign out' }).click();
    await expect(page).toHaveURL(/\/$/);
    await page.goto('/login');
    await page.getByLabel('Email address').fill(student.email);
    await page.getByLabel('Password').fill(student.password);
    await page.getByRole('button', { name: 'Sign in' }).click();
    await expect(page).toHaveURL(/\/student\/dashboard$/);
    await expect(page.getByRole('link', { name: reference })).toBeVisible();
    await expect(page.getByRole('cell', { name: 'E2E strategic analysis report' })).toBeVisible();
});

test('admin journey: find submission, download, change status, add note, check notification', async ({ page }) => {
    expect(reference, 'student journey must run first').toBeTruthy();

    await page.goto('/login');
    await page.getByLabel('Email address').fill(process.env.E2E_ADMIN_EMAIL);
    await page.getByLabel('Password').fill(process.env.E2E_ADMIN_PASSWORD);
    await page.getByRole('button', { name: 'Sign in' }).click();
    await expect(page).toHaveURL(/\/admin$/);
    await expect(page.getByRole('heading', { name: 'Overview' })).toBeVisible();
    await snapAllSizes(page, 'admin-dashboard', expect);

    await page.getByRole('navigation', { name: 'Administration' }).getByRole('link', { name: 'Submissions' }).click();
    await page.getByLabel('Search').fill(student.email);
    await page.getByRole('button', { name: 'Apply filters' }).click();
    await expect(page.getByRole('cell', { name: reference, exact: true })).toBeVisible();
    await page.getByRole('link', { name: `Open ${reference}` }).click();

    // Metadata and contact details
    await expect(page.getByText(student.name).first()).toBeVisible();
    await expect(page.getByText(student.email).first()).toBeVisible();
    await expect(page.getByText('+447700900456').first()).toBeVisible();
    await expect(page.getByText('E2E101').first()).toBeVisible();

    // Download the brief
    const download = page.waitForEvent('download');
    await page.getByRole('link', { name: 'Download' }).first().click();
    expect((await download).suggestedFilename()).toBe('synthetic-brief.pdf');

    // Notification status (sent via the log transport in this environment)
    await expect(page.getByRole('region', { name: 'Email notification' }).getByText('Sent', { exact: true })).toBeVisible();

    // Change status with a note
    await page.getByLabel('Status', { exact: true }).selectOption('under_review');
    await page.getByLabel(/^Note/).fill('E2E: reviewing requirements');
    await page.getByRole('button', { name: 'Save status' }).click();
    await expect(page.getByText('Status changed to Under review.')).toBeVisible();

    // Separate internal note
    await page.getByLabel('New note').fill('E2E internal-only note');
    await page.getByRole('button', { name: 'Add note' }).click();
    await expect(page.getByText('Internal note added.')).toBeVisible();

    // Persisted after refresh
    await page.reload();
    await expect(page.getByRole('region', { name: 'Status history' }).getByText('Under review', { exact: true })).toBeVisible();
    await expect(page.getByText('E2E internal-only note')).toBeVisible();
    await expect(page.getByText('E2E: reviewing requirements')).toBeVisible();
    await snapAllSizes(page, 'admin-submission-detail', expect);
});

test('student cannot see internal notes and sees the new status', async ({ page }) => {
    await page.goto('/login');
    await page.getByLabel('Email address').fill(student.email);
    await page.getByLabel('Password').fill(student.password);
    await page.getByRole('button', { name: 'Sign in' }).click();
    await page.getByRole('link', { name: reference }).click();

    await expect(page.getByText('Under review').first()).toBeVisible();
    await expect(page.getByText('E2E internal-only note')).toHaveCount(0);
    await expect(page.getByText('E2E: reviewing requirements')).toHaveCount(0);

    // Students cannot open admin pages
    const response = await page.goto(`/admin/submissions/${reference}`);
    expect(response.status()).toBe(403);
});

test.afterAll(() => {
    fs.mkdirSync(screenshotDir, { recursive: true });
});
