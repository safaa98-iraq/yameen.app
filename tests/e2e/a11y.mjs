// Accessibility audit (WCAG 2.1 AA) with axe-core on the main pages.
// Usage: BASE_URL=http://127.0.0.1:8000 node tests/e2e/a11y.mjs
import { readFileSync } from 'node:fs';
import { createRequire } from 'node:module';

const require = createRequire(import.meta.url);
// Playwright may be installed globally (resolved through NODE_PATH) or locally.
const { chromium } = require('playwright');
const axe = readFileSync(require.resolve('axe-core/axe.min.js'), 'utf8');
const base = process.env.BASE_URL ?? 'http://127.0.0.1:8000';
const pages = ['/', '/en', '/fix', '/fix/photoshop', '/pdf-fix', '/garbled-text', '/clean', '/fonts', '/fonts/amiri',
    '/fonts/compare', '/fonts/pairing', '/export', '/batch', '/pricing', '/login', '/about', '/blog/choosing-an-arabic-font'];

const browser = await chromium.launch();
const page = await browser.newPage({ viewport: { width: 1280, height: 900 } });
let total = 0;
for (const path of pages) {
    await page.goto(base + path, { waitUntil: 'networkidle' });
    await page.addScriptTag({ content: axe });
    const res = await page.evaluate(() => axe.run(document, { runOnly: ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'] }));
    for (const v of res.violations) {
        total++;
        console.log(`${path}  [${v.impact}] ${v.id}: ${v.help}`);
        for (const n of v.nodes.slice(0, 3)) console.log('    ', n.target.join(' '), '|', (n.failureSummary ?? '').split('\n')[1] ?? '');
    }
}
await browser.close();
console.log(total ? `${total} violation types` : 'No violations');
process.exit(total ? 1 : 0);
