import { exec } from 'child_process';
import { promisify } from 'util';
import * as path from 'path';
import { fileURLToPath } from 'url';

const execAsync = promisify(exec);

// Get the project root directory (3 levels up from this file)
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const PROJECT_ROOT = path.resolve(__dirname, '..', '..', '..');

/**
 * Run an artisan command
 */
export async function artisan(command: string): Promise<string> {
  try {
    const { stdout, stderr } = await execAsync(`php artisan ${command}`, {
      cwd: PROJECT_ROOT,
    });
    if (stderr && !stderr.includes('INFO')) {
      console.error('Artisan stderr:', stderr);
    }
    return stdout;
  } catch (error: any) {
    console.error('Artisan command failed:', error.message);
    throw error;
  }
}

/**
 * Seed the database with a specific test scenario
 */
export async function seedDatabase(scenario: string = 'default', fresh: boolean = true): Promise<void> {
  const freshFlag = fresh ? '--fresh' : '';
  await artisan(`e2e:seed ${scenario} ${freshFlag}`);
}

/**
 * Clear and reset the database
 */
export async function resetDatabase(): Promise<void> {
  await artisan('migrate:fresh');
}

/**
 * Admin credentials for E2E tests
 */
export const adminCredentials = {
  email: 'admin@example.com',
  password: 'password',
};

/**
 * Viewport sizes for responsive tests
 */
export const viewports = {
  desktop: { width: 1920, height: 1080 },
  laptop: { width: 1366, height: 768 },
  tablet: { width: 768, height: 1024 },
  mobile: { width: 375, height: 812 },
};
