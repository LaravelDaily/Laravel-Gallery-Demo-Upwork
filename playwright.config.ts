import { defineConfig, devices } from '@playwright/test';
import * as path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const STORAGE_STATE = path.resolve(__dirname, 'tests/e2e/.auth/admin.json');

/**
 * See https://playwright.dev/docs/test-configuration.
 */
export default defineConfig({
  testDir: './tests/e2e',
  /* Run tests serially to avoid database race conditions */
  fullyParallel: false,
  workers: 1,
  forbidOnly: !!process.env.CI,
  /* Allow retries to handle intermittent issues */
  retries: process.env.CI ? 2 : 1,
  reporter: [['html'], ['list']],
  timeout: 60000,

  /* Global setup to seed database and create auth session */
  globalSetup: './tests/e2e/global-setup.ts',

  use: {
    baseURL: process.env.APP_URL || 'http://laravel-gallery-demo-upwork.test',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
  },

  projects: [
    // Public tests (no authentication needed)
    {
      name: 'public',
      use: { ...devices['Desktop Chrome'] },
      testMatch: ['gallery.spec.ts', 'artwork-detail.spec.ts', 'responsive.spec.ts'],
      testIgnore: ['**/admin-*.spec.ts'],
    },
    // Admin tests (use stored authentication state)
    {
      name: 'admin',
      use: {
        ...devices['Desktop Chrome'],
        storageState: STORAGE_STATE,
      },
      testMatch: ['admin-*.spec.ts'],
    },
  ],
});
