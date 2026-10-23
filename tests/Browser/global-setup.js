import { execFileSync } from 'node:child_process';
import crypto from 'node:crypto';
import fs from 'node:fs';
import { e2eEnv } from '../../playwright.config.js';

/**
 * Rebuilds the isolated E2E database and provisions a synthetic administrator
 * with a random password for this run only.
 */
export default async function globalSetup() {
    const env = { ...process.env, ...e2eEnv };

    fs.writeFileSync(e2eEnv.DB_DATABASE, '');
    execFileSync('php', ['artisan', 'migrate:fresh', '--force', '--no-interaction'], { env, stdio: 'inherit' });

    const password = crypto.randomBytes(12).toString('base64url') + '9a';
    execFileSync('php', ['artisan', 'tinker', '--execute', `
        $u = new App\\Models\\User;
        $u->fill(['name' => 'E2E Administrator', 'email' => 'e2e-admin@example.test', 'password' => getenv('E2E_ADMIN_PASSWORD')]);
        $u->role = App\\Enums\\UserRole::Admin;
        $u->status = App\\Enums\\AccountStatus::Active;
        $u->email_verified_at = now();
        $u->save();
    `], { env: { ...env, E2E_ADMIN_PASSWORD: password }, stdio: 'inherit' });

    process.env.E2E_ADMIN_EMAIL = 'e2e-admin@example.test';
    process.env.E2E_ADMIN_PASSWORD = password;
}
