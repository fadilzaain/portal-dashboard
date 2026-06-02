import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/portal/pelayananpasien.css',
                'resources/js/portal/pelayananpasien.js',
                'resources/css/portal/indikator-mutu.css',
                'resources/js/portal/indikator-mutu.js',
                'resources/css/portal/keuangan.css',
                'resources/js/portal/keuangan.js',
                'resources/css/portal/klaimbpjs.css',
                'resources/js/portal/klaimbpjs.js',
                'resources/css/portal/bor-modal.css',
                'resources/js/portal/bor-modal.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});