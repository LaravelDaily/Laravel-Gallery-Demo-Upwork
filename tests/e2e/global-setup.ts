import { chromium, FullConfig } from '@playwright/test';
import { exec } from 'child_process';
import { promisify } from 'util';
import * as path from 'path';
import { fileURLToPath } from 'url';

const execAsync = promisify(exec);
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const PROJECT_ROOT = path.resolve(__dirname, '..', '..');

const STORAGE_STATE_PATH = path.resolve(__dirname, '.auth/admin.json');

async function globalSetup(config: FullConfig) {
  console.log('🔧 Global Setup: Preparing database for E2E tests...');

  try {
    // Run the default seed which includes admin user and sample data
    const { stdout } = await execAsync('php artisan e2e:seed default --fresh', {
      cwd: PROJECT_ROOT,
    });

    if (stdout) {
      console.log(stdout);
    }

    console.log('✅ Database seeded successfully');
  } catch (error: any) {
    console.error('❌ Failed to seed database:', error.message);
    throw error;
  }

  // Create authenticated session state for admin tests
  console.log('🔐 Creating authenticated session...');

  const baseURL = config.projects[0]?.use?.baseURL || 'http://laravel-gallery-demo-upwork.test';
  const browser = await chromium.launch();
  const context = await browser.newContext();
  const page = await context.newPage();

  try {
    // Navigate to login page
    await page.goto(`${baseURL}/admin/login`);
    await page.waitForLoadState('domcontentloaded');

    // Fill login form
    await page.locator('input[type="email"]').fill('admin@example.com');
    await page.locator('input[type="password"]').fill('password');

    // Click sign in
    await page.getByRole('button', { name: 'Sign in' }).click();

    // Wait for successful login
    await page.waitForURL(/\/admin(?!\/login)/, { timeout: 30000 });

    // Save authenticated state
    await context.storageState({ path: STORAGE_STATE_PATH });
    console.log('✅ Authenticated session saved');
  } catch (error: any) {
    console.error('❌ Failed to create authenticated session:', error.message);
    throw error;
  } finally {
    await browser.close();
  }
}

export default globalSetup;
