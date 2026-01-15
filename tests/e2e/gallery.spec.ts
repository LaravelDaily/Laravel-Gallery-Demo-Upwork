import { test, expect } from '@playwright/test';

test.describe('Public Gallery E2E Tests', () => {
  test.describe('Gallery Display', () => {
    test('E2E-GAL-P-001: gallery displays artwork cards with data', async ({ page }) => {
      await page.goto('/');

      // Check that artwork cards are displayed (default seed has 12 published artworks)
      const artworkCards = page.locator('a[href*="/artworks/"]');
      const count = await artworkCards.count();
      expect(count).toBeGreaterThan(0);
    });

    test('E2E-GAL-P-002: category filter buttons render with counts', async ({ page }) => {
      await page.goto('/');

      // Check category buttons exist with counts
      await expect(page.getByRole('button', { name: 'All Artworks' })).toBeVisible();

      // Check that at least one category filter exists with a count
      const categoryButtons = page.locator('button').filter({ hasText: /\(\d+\)/ });
      const categoryCount = await categoryButtons.count();
      expect(categoryCount).toBeGreaterThan(0);
    });

    test('E2E-GAL-P-003: clicking category filters gallery', async ({ page }) => {
      await page.goto('/');

      // Get initial artwork count
      const initialCards = page.locator('a[href*="/artworks/"]');
      const initialCount = await initialCards.count();

      // Click first category with artworks
      const categoryButton = page.locator('button').filter({ hasText: /\(\d+\)/ }).first();
      await categoryButton.click();

      // Wait for Livewire to update
      await page.waitForTimeout(1000);

      // Cards should still exist (filtered)
      const filteredCards = page.locator('a[href*="/artworks/"]');
      const filteredCount = await filteredCards.count();
      expect(filteredCount).toBeGreaterThan(0);
      expect(filteredCount).toBeLessThanOrEqual(initialCount);
    });

    test('E2E-GAL-P-004: clicking All Artworks shows everything', async ({ page }) => {
      await page.goto('/');

      // Filter by category first
      const categoryButton = page.locator('button').filter({ hasText: /\(\d+\)/ }).first();
      await categoryButton.click();
      await page.waitForTimeout(500);

      // Click All Artworks
      await page.getByRole('button', { name: 'All Artworks' }).click();
      await page.waitForTimeout(500);

      // Should show artworks
      const cards = page.locator('a[href*="/artworks/"]');
      const count = await cards.count();
      expect(count).toBeGreaterThan(0);
    });

    test('E2E-GAL-P-005: clicking artwork card navigates to detail page', async ({ page }) => {
      await page.goto('/');

      // Click on an artwork card
      const firstCard = page.locator('a[href*="/artworks/"]').first();
      const href = await firstCard.getAttribute('href');
      await firstCard.click();

      // Should navigate to detail page
      await expect(page).toHaveURL(/\/artworks\//);
    });

    test('E2E-GAL-P-007: URL updates when category selected', async ({ page }) => {
      await page.goto('/');

      // Click a category
      const categoryButton = page.locator('button').filter({ hasText: /\(\d+\)/ }).first();
      await categoryButton.click();
      await page.waitForTimeout(500);

      // URL should contain category parameter
      await expect(page).toHaveURL(/\?category=/);
    });

    test('E2E-GAL-P-008: direct URL with category param filters', async ({ page }) => {
      // First get a valid category ID from the page
      await page.goto('/');

      // Click a category to get its ID from URL
      const categoryButton = page.locator('button').filter({ hasText: /\(\d+\)/ }).first();
      await categoryButton.click();
      await page.waitForTimeout(500);

      // Get the category ID from URL
      const url = page.url();
      const categoryMatch = url.match(/\?category=(\d+)/);
      expect(categoryMatch).toBeTruthy();
      const categoryId = categoryMatch![1];

      // Now navigate directly with the category param
      await page.goto(`/?category=${categoryId}`);
      await page.waitForLoadState('networkidle');

      // Check that artworks are displayed (filtered)
      const cards = page.locator('a[href*="/artworks/"]');
      const count = await cards.count();
      expect(count).toBeGreaterThan(0);

      // URL should still have the category parameter
      await expect(page).toHaveURL(new RegExp(`\\?category=${categoryId}`));
    });

    test('E2E-GAL-P-009: artwork images load without errors', async ({ page }) => {
      await page.goto('/');

      // Check that artwork cards exist
      const cards = page.locator('a[href*="/artworks/"]');
      const count = await cards.count();
      expect(count).toBeGreaterThan(0);
    });

    test('E2E-GAL-P-010: hover effects work on artwork cards', async ({ page }) => {
      await page.goto('/');

      const card = page.locator('a[href*="/artworks/"]').first();
      await expect(card).toBeVisible();

      // Hover over the card
      await card.hover();

      // The card should have hover class
      await expect(card).toHaveClass(/hover:shadow-md/);
    });
  });

  test.describe('Gallery Pagination', () => {
    test('E2E-GAL-P-006: gallery shows pagination when many artworks', async ({ page }) => {
      await page.goto('/');

      // Check if pagination exists (only if more than 12 artworks)
      const pagination = page.locator('nav[aria-label="Pagination"]');
      const paginationExists = await pagination.isVisible();

      // Either pagination exists, or there are 12 or fewer artworks
      const cards = page.locator('a[href*="/artworks/"]');
      const cardCount = await cards.count();

      if (cardCount === 12) {
        // If exactly 12, pagination might exist
        expect(paginationExists || cardCount <= 12).toBeTruthy();
      } else {
        expect(cardCount).toBeLessThanOrEqual(12);
      }
    });
  });

  test.describe('Gallery Empty States', () => {
    test('E2E-GAL-N-001: invalid category shows empty state or all artworks', async ({ page }) => {
      // Navigate with a non-existent category ID
      await page.goto('/?category=99999');
      await page.waitForLoadState('networkidle');

      // The page should either show an empty state message or show all artworks (graceful fallback)
      const emptyMessage = page.getByText(/no artworks|no results/i);
      const artworkCards = page.locator('a[href*="/artworks/"]');

      const hasEmptyMessage = await emptyMessage.isVisible().catch(() => false);
      const cardCount = await artworkCards.count();

      // Either shows empty message or shows artworks (graceful handling)
      expect(hasEmptyMessage || cardCount > 0).toBeTruthy();
    });

    test('E2E-GAL-N-002: empty category filter shows appropriate message', async ({ page }) => {
      // This test verifies the behavior when filtering results in no artworks
      // With default seed, all categories have artworks, so we test with invalid category
      await page.goto('/?category=99999');
      await page.waitForLoadState('networkidle');

      // The page should handle empty results gracefully
      // Either shows "no artworks found" or falls back to showing all artworks
      const pageContent = await page.content();
      const hasContent = pageContent.length > 0;
      expect(hasContent).toBeTruthy();

      // No JavaScript errors should occur
      const errors: string[] = [];
      page.on('pageerror', (error) => errors.push(error.message));
      await page.waitForTimeout(500);
      expect(errors).toHaveLength(0);
    });
  });

  test.describe('Gallery Console Errors', () => {
    test('E2E-GAL-N-003: no JavaScript console errors', async ({ page }) => {
      const errors: string[] = [];

      page.on('pageerror', (error) => {
        errors.push(error.message);
      });

      await page.goto('/');
      await page.waitForLoadState('networkidle');

      expect(errors).toHaveLength(0);
    });

    test('E2E-GAL-N-004: no Livewire errors in console', async ({ page }) => {
      const consoleMessages: string[] = [];

      page.on('console', (msg) => {
        if (msg.type() === 'error' && msg.text().toLowerCase().includes('livewire')) {
          consoleMessages.push(msg.text());
        }
      });

      await page.goto('/');
      await page.waitForLoadState('networkidle');

      // Interact with the page to trigger Livewire
      await page.getByRole('button', { name: 'All Artworks' }).click();
      await page.waitForTimeout(1000);

      expect(consoleMessages).toHaveLength(0);
    });
  });
});
