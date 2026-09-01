/**
 * Capture SkySoft Weather screenshots for GitHub README.
 * Usage: npx --yes -p playwright node scripts/capture-screenshots.mjs
 */
import { chromium } from 'playwright';
import { mkdir } from 'fs/promises';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const OUT_DIR = path.join(__dirname, '..', 'docs', 'screenshots');
const BASE_URL = 'http://localhost/weather_forecast_system/';
const VIEWPORT = { width: 1280, height: 800 };

async function capture(page, filename) {
    const file = path.join(OUT_DIR, filename);
    await page.screenshot({ path: file, fullPage: false });
    console.log('Saved:', file);
}

async function main() {
    await mkdir(OUT_DIR, { recursive: true });

    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage({ viewport: VIEWPORT });

    try {
        await page.goto(BASE_URL, { waitUntil: 'networkidle', timeout: 30000 });
        await page.waitForTimeout(1500);
        await capture(page, 'home.png');

        await page.fill('#search-input', 'Kuala Lumpur');
        await page.waitForSelector('.suggestion-item', { timeout: 10000 });
        await page.waitForTimeout(800);
        await capture(page, 'search.png');
        await page.click('.suggestion-item');
        await page.waitForSelector('#weather-display:not(.hidden)', { timeout: 20000 });
        await page.waitForTimeout(2500);
        await capture(page, 'weather.png');

        await page.click('[data-tab="countries"]');
        await page.waitForTimeout(800);
        const letterM = page.locator('.letter-btn', { hasText: 'M' });
        await letterM.click();
        await page.waitForSelector('.country-item', { timeout: 10000 });
        await page.waitForTimeout(1200);
        await capture(page, 'countries.png');

        await page.click('[data-tab="favorites"]');
        await page.waitForTimeout(1000);
        await capture(page, 'favorites.png');

        await page.click('[data-tab="history"]');
        await page.waitForTimeout(1000);
        await capture(page, 'history.png');
    } catch (err) {
        console.error('Screenshot capture failed:', err.message);
        process.exitCode = 1;
    } finally {
        await browser.close();
    }
}

main();
