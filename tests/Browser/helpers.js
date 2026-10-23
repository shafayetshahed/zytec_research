import fs from 'node:fs';
import path from 'node:path';

const mailLog = path.resolve('storage/logs/mail.log');

/**
 * Reads the latest verification code sent to an address from the local
 * "log" mail transport. Only used against the isolated E2E environment.
 */
export async function latestOtpFor(email, attempts = 20) {
    for (let i = 0; i < attempts; i++) {
        if (fs.existsSync(mailLog)) {
            const log = fs.readFileSync(mailLog, 'utf8').replace(/=\r?\n/g, '');
            const start = log.lastIndexOf(`To: ${email}`);
            if (start !== -1) {
                const match = log.slice(start).match(/letter-spacing: 8px; font-family: monospace;">(\d{6})</);
                if (match) return match[1];
            }
        }
        await new Promise((resolve) => setTimeout(resolve, 250));
    }
    throw new Error(`No verification code found for ${email}`);
}

/** A tiny but genuine PDF for upload tests. */
export function syntheticPdf() {
    return {
        name: 'synthetic-brief.pdf',
        mimeType: 'application/pdf',
        buffer: Buffer.from('%PDF-1.4\n1 0 obj << /Type /Catalog >> endobj\n% Synthetic E2E brief - no real student data\n%%EOF\n'),
    };
}

export const screenshotDir = path.resolve('storage/framework/testing/screenshots');

const sizes = { desktop: { width: 1440, height: 900 }, tablet: { width: 820, height: 1180 }, mobile: { width: 390, height: 844 } };

/**
 * Captures the current page at desktop, tablet and mobile widths, failing on
 * horizontal overflow, then restores the original viewport.
 */
export async function snapAllSizes(page, name, expect) {
    fs.mkdirSync(screenshotDir, { recursive: true });
    const original = page.viewportSize();
    for (const [device, size] of Object.entries(sizes)) {
        await page.setViewportSize(size);
        const overflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
        expect(overflow, `${name} overflows horizontally on ${device}`).toBeLessThanOrEqual(0);
        await page.screenshot({ path: `${screenshotDir}/${device}-${name}.png`, fullPage: true });
    }
    await page.setViewportSize(original);
}
