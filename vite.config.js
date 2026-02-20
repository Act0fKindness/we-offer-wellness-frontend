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
                'resources/css/we-offer-wellness-base-styles.css',
                'resources/js/app.js',
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
