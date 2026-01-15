import { test, expect } from '@playwright/test';
import { viewports } from './helpers/test-utils';

// Note: Authentication is handled via storageState in playwright.config.ts
// No login needed - tests run with pre-authenticated session

test.describe('Admin Panel Responsiveness E2E Tests', () => {
  test('E2E-RES-P-009: admin panel loads on tablet', async ({ page }) => {
    await page.setViewportSize(viewports.tablet);
    await page.goto('/admin/artworks');

    // Page should load
    await page.waitForSelector('table', { timeout: 10000 });
  });

  test('E2E-RES-P-010: admin table visible on mobile', async ({ page }) => {
    await page.setViewportSize(viewports.mobile);
    await page.goto('/admin/artworks');

    // Table should be visible
    const table = page.locator('table');
    await expect(table).toBeVisible();
  });
});
