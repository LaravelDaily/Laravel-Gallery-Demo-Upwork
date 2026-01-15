import { test as base, Page } from '@playwright/test';
import { adminCredentials } from '../helpers/test-utils';

/**
 * Extended test fixture with admin authentication
 */
export const test = base.extend<{ adminPage: Page }>({
  adminPage: async ({ page }, use) => {
    // Navigate to login
    await page.goto('/admin/login');

    // Fill in credentials
    await page.fill('input[type="email"]', adminCredentials.email);
    await page.fill('input[type="password"]', adminCredentials.password);

    // Submit the form
    await page.click('button[type="submit"]');

    // Wait for redirect to admin panel
    await page.waitForURL(/\/admin/);

    await use(page);
  },
});

export { expect } from '@playwright/test';
