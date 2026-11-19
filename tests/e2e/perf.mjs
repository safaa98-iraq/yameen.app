// Mobile performance check: LCP and CLS with 4x CPU slowdown and a Slow-4G-like
// network, plus JS transferred on first load. Budgets: LCP < 2.5 s, CLS < 0.1, JS < 80 KB gz.
// Usage: BASE_URL=http://127.0.0.1:8000 node tests/e2e/perf.mjs
import { createRequire } from 'node:module';

const require = createRequire(import.meta.url);
const { chromium, devices } = require('playwright');
const base = process.env.BASE_URL ?? 'http://127.0.0.1:8000';
const pages = ['/', '/fix/photoshop', '/fonts', '/fonts/amiri', '/garbled-text', '/en'];

const browser = await chromium.launch();
let failed = false;
for (const path of pages) {
    const ctx = await browser.newContext({ ...devices['Pixel 7'] });
    const page = await ctx.newPage();
    const cdp = await ctx.newCDPSession(page);
    await cdp.send('Network.enable');
    await cdp.send('Network.emulateNetworkConditions', { offline: false, latency: 150, downloadThroughput: (1.6 * 1024 * 1024) / 8, uploadThroughput: (750 * 1024) / 8 });
    await cdp.send('Emulation.setCPUThrottlingRate', { rate: 4 });
    let js = 0;
    page.on('response', async (r) => {
        if (r.request().resourceType() === 'script') js += Number((await r.headerValue('content-length')) ?? (await r.body().catch(() => Buffer.alloc(0))).length);
    });
    await page.addInitScript(() => {
        window.__lcp = 0;
        window.__cls = 0;
        new PerformanceObserver((l) => l.getEntries().forEach((e) => (window.__lcp = e.startTime))).observe({ type: 'largest-contentful-paint', buffered: true });
        new PerformanceObserver((l) => l.getEntries().forEach((e) => { if (!e.hadRecentInput) window.__cls += e.value; })).observe({ type: 'layout-shift', buffered: true });
    });
    await page.goto(base + path, { waitUntil: 'networkidle' });
    await page.waitForTimeout(1500);
    const { lcp, cls } = await page.evaluate(() => ({ lcp: window.__lcp, cls: window.__cls }));
    const ok = lcp < 2500 && cls < 0.1;
    failed ||= !ok;
    console.log(`${ok ? 'ok  ' : 'FAIL'} ${path.padEnd(16)} LCP ${Math.round(lcp)} ms  CLS ${cls.toFixed(3)}  JS ${Math.round(js / 1024)} KB (uncompressed)`);
    await ctx.close();
}
await browser.close();
process.exit(failed ? 1 : 0);
