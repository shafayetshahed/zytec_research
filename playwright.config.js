import { defineConfig, devices } from '@playwright/test';
import path from 'node:path';

const port = 8001;
const root = path.resolve('.');

/**
 * Browser tests run against an isolated SQLite database (database/e2e.sqlite)
 * on port 8001, so they never touch development or production data.
 */
export const e2eEnv = {
    APP_ENV: 'local',
    APP_URL: `http://127.0.0.1:${port}`,
    DB_CONNECTION: 'sqlite',
    DB_DATABASE: path.join(root, 'database', 'e2e.sqlite'),
    MAIL_MAILER: 'log',
    MAIL_LOG_CHANNEL: 'mail',
    QUEUE_CONNECTION: 'sync',
    SESSION_DRIVER: 'database',
    CACHE_STORE: 'database',
    BOOST_BROWSER_LOGS_WATCHER: 'false',
};

export default defineConfig({
    testDir: './tests/Browser',
    outputDir: './storage/framework/testing/playwright-results',
    fullyParallel: false,
    workers: 1,
    retries: 0,
    reporter: [['list'], ['html', { outputFolder: 'storage/framework/testing/playwright-report', open: 'never' }]],
    globalSetup: './tests/Browser/global-setup.js',
    use: {
        baseURL: `http://127.0.0.1:${port}`,
        trace: 'retain-on-failure',
        screenshot: 'only-on-failure',
    },
    projects: [
        { name: 'chromium', use: { ...devices['Desktop Chrome'] } },
    ],
    webServer: {
        command: `sh -c "touch database/e2e.sqlite && cd public && exec php -S 127.0.0.1:${port} ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php"`,
        url: `http://127.0.0.1:${port}/up`,
        reuseExistingServer: false,
        env: e2eEnv,
        timeout: 60_000,
    },
});
