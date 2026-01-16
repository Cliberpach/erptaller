import { defineConfig } from 'vite';
import laravel, { refreshPaths } from 'laravel-vite-plugin';

export default defineConfig({
    // build: {
    //     sourcemap: true,
    // },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/libs/filepond.js',
                'resources/js/libs/calendar.js',
                'resources/js/libs/lightgalery.js',

                'resources/js/notifications/main.js',
                'resources/js/sales/sales/main.js',
            ],
            refresh: [
                ...refreshPaths,
                'app/Livewire/**',
            ],
        }),
    ],
});
