import { defineConfig } from 'vite';

// Pro-only modules, built outside public/ and served by ProAssetController
// to Pro subscribers. Each file is self-contained (no shared chunks).
export default defineConfig({
    publicDir: false,
    build: {
        outDir: 'storage/app/pro',
        emptyOutDir: true,
        lib: {
            entry: { svg: 'resources/js/pro/svg.js', batch: 'resources/js/pro/batch.js' },
            formats: ['es'],
            fileName: (_format, name) => `${name}.js`,
        },
        rollupOptions: { output: { inlineDynamicImports: false } },
    },
});
