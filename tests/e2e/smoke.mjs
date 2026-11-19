// Smoke test: type → fix → copy; preview fonts; reach the daily limit.
// Usage: BASE_URL=http://127.0.0.1:8000 node tests/e2e/smoke.mjs  (fresh visitor, default limit 8)
import { createRequire } from 'node:module';
import assert from 'node:assert/strict';

const require = createRequire(import.meta.url);
const { chromium } = require('playwright');
const base = process.env.BASE_URL ?? 'http://127.0.0.1:8000';

const browser = await chromium.launch();
const ctx = await browser.newContext({ viewport: { width: 1280, height: 1000 }, permissions: ['clipboard-read', 'clipboard-write'] });
const page = await ctx.newPage();
const errors = [];
page.on('pageerror', (e) => errors.push(e.message));
const step = (name) => console.log('✓', name);

// 1. Type → fix → copy (home: click your app)
await page.goto(base + '/', { waitUntil: 'networkidle' });
await page.fill('#picker-input', 'مرحبا بكم');
await page.click('[data-app=photoshop]');
await page.waitForSelector('[data-result]:visible');
const copied = await page.evaluate(() => navigator.clipboard.readText());
assert.equal(copied, 'ﻦﻤﻛﺑ ﺎﺒﺣﺮﻣ'.split(' ').reverse().join(' ') === copied ? copied : copied);
assert.equal(copied, 'ﻢﻜﺑ ﺎﺒﺣﺮﻣ');
step('fix and copy for Photoshop');

// 2. Fonts preview with the shared text
await page.goto(base + '/fonts', { waitUntil: 'networkidle' });
assert.equal(await page.inputValue('#fonts-text'), 'مرحبا بكم');
await page.waitForFunction(() => [...document.fonts].some((f) => f.family.startsWith('yf-') && f.status === 'loaded'));
const specimen = await page.locator('article p[dir=auto]').first().textContent();
assert.equal(specimen.trim(), 'مرحبا بكم');
step('fonts preview the shared text');

// 3. Limit reached: distinct texts until the visitor limit runs out
await page.goto(base + '/', { waitUntil: 'networkidle' });
for (let i = 0; i < 12 && !(await page.isVisible('[data-limit-panel]')); i++) {
    await page.fill('#picker-input', `نص تجريبي رقم ${i}`);
    await page.click('[data-app=unity]');
    await page.waitForTimeout(300);
}
assert.ok(await page.isVisible('[data-limit-panel]'), 'limit panel visible');
assert.match(await page.textContent('[data-credits]'), /0/);
step('limit reached panel');

assert.deepEqual(errors, []);
await browser.close();
console.log('Smoke test passed');
