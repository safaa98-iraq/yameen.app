import { defineConfig } from 'vitest/config';

// Accuracy benchmark for the garbled-text repair: CORPUS=dir npx vitest run --config vitest.bench.config.js
export default defineConfig({
    test: { include: ['scripts/bench/*.bench.test.js'], testTimeout: 600000 },
});
