import { existsSync } from 'node:fs';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

const appCss = 'resources/css/app.css';

export default defineConfig({
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
    plugins: [
        laravel({
            input: [
                ...(existsSync(new URL(appCss, import.meta.url)) ? [appCss] : []),
                'resources/js/app.js'
                
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});