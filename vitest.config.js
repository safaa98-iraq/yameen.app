import { defineConfig } from 'vitest/config';

// Separate from vite.config.js: the Laravel plugin refuses to start in CI.
export default defineConfig({
    test: {
        include: ['resources/js/**/*.test.js'],
        environment: 'node',
    },
});
