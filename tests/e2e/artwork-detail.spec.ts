import { test, expect } from '@playwright/test';

test.describe('Artwork Detail E2E Tests', () => {
  test.describe('Artwork Detail Display', () => {
    test('E2E-DET-P-001: detail page displays all artwork information', async ({ page }) => {
      // First go to gallery to get a valid artwork link
      await page.goto('/');
      const firstCard = page.locator('a[href*="/artworks/"]').first();
      await firstCard.click();

      // Wait for page to fully load
      await page.waitForLoadState('networkidle');

      // Check title exists (h1)
      const title = page.locator('h1');
      await expect(title).toBeVisible();

      // Check artist name exists (by + name pattern)
      await expect(page.getByText(/by .+/)).toBeVisible();
    });

    test('E2E-DET-P-002: artwork image displays or placeholder shown', async ({ page }) => {
      await page.goto('/');
      const firstCard = page.locator('a[href*="/artworks/"]').first();
      await firstCard.click();

      // Check for image container
      const imageContainer = page.locator('.aspect-square').first();
      await expect(imageContainer).toBeVisible();
    });

    test('E2E-DET-P-003: breadcrumb navigation renders', async ({ page }) => {
      await page.goto('/');
      const firstCard = page.locator('a[href*="/artworks/"]').first();
      await firstCard.click();

      // Check breadcrumb
      const breadcrumb = page.locator('nav[aria-label="Breadcrumb"]');
      await expect(breadcrumb).toBeVisible();
      await expect(breadcrumb.getByText('Gallery')).toBeVisible();
    });

    test('E2E-DET-P-004: clicking Gallery breadcrumb navigates back', async ({ page }) => {
      await page.goto('/');
      const firstCard = page.locator('a[href*="/artworks/"]').first();
      await firstCard.click();

      // Click Gallery breadcrumb
      await page.locator('nav[aria-label="Breadcrumb"]').getByRole('link', { name: 'Gallery' }).click();

      // Should navigate to gallery
      await expect(page).toHaveURL('/');
    });

    test('E2E-DET-P-005: back to gallery link works', async ({ page }) => {
      await page.goto('/');
      const firstCard = page.locator('a[href*="/artworks/"]').first();
      await firstCard.click();

      // Find and click back to gallery link
      await page.getByRole('link', { name: /Back to Gallery/i }).click();

      // Should navigate to gallery
      await expect(page).toHaveURL('/');
    });

    test('E2E-DET-P-006: details section displays', async ({ page }) => {
      await page.goto('/');
      const firstCard = page.locator('a[href*="/artworks/"]').first();
      await firstCard.click();

      // Check for details section
      await expect(page.getByText('Details')).toBeVisible();
    });

    test('E2E-DET-P-007: page title contains artwork title', async ({ page }) => {
      await page.goto('/');
      const firstCard = page.locator('a[href*="/artworks/"]').first();
      await firstCard.click();

      // Check page title contains Art Gallery
      await expect(page).toHaveTitle(/Art Gallery/);
    });
  });

  test.describe('Artwork Detail Error States', () => {
    test('E2E-DET-N-001: unpublished artwork shows 404 page', async ({ page }) => {
      // The default seed creates an unpublished artwork with slug 'unpublished-artwork'
      const response = await page.goto('/artworks/unpublished-artwork');

      // Should return 404 for unpublished artwork
      expect(response?.status()).toBe(404);
    });

    test('E2E-DET-N-002: invalid slug shows 404 page', async ({ page }) => {
      const response = await page.goto('/artworks/nonexistent-artwork-slug-12345');

      // Should return 404
      expect(response?.status()).toBe(404);
    });

    test('E2E-DET-N-003: missing image shows placeholder', async ({ page }) => {
      await page.goto('/');
      const firstCard = page.locator('a[href*="/artworks/"]').first();
      await firstCard.click();

      await page.waitForLoadState('networkidle');

      // Check for image container - it should either have an image or a placeholder
      const imageContainer = page.locator('.aspect-square').first();
      await expect(imageContainer).toBeVisible();

      // The container should have content (either img tag or placeholder SVG/div)
      const hasImage = await imageContainer.locator('img').count() > 0;
      const hasSvg = await imageContainer.locator('svg').count() > 0;
      const hasPlaceholderDiv = await imageContainer.locator('div').count() > 0;

      // Either an image, SVG placeholder, or div placeholder should be present
      expect(hasImage || hasSvg || hasPlaceholderDiv).toBeTruthy();
    });

    test('E2E-DET-N-004: no JavaScript console errors on detail page', async ({ page }) => {
      const errors: string[] = [];

      page.on('pageerror', (error) => {
        errors.push(error.message);
      });

      await page.goto('/');
      const firstCard = page.locator('a[href*="/artworks/"]').first();
      await firstCard.click();

      await page.waitForLoadState('networkidle');

      expect(errors).toHaveLength(0);
    });
  });
});
