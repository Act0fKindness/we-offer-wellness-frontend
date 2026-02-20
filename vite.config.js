import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    build: {
        minify: false,
        cssMinify: false,
        sourcemap: false,
        emptyOutDir: true,
        chunkSizeWarningLimit: 2000,
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                // Provide a direct CSS entry so Blade calls like
                // @vite('resources/css/we-offer-wellness-base-styles.css')
                // resolve in production manifests when present.
                'resources/css/we-offer-wellness-base-styles.css',
            ],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
});
