import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    // extra add for network sharing image no show & css not working. only server part add.
    server: {
        host: '0.0.0.0',
        port: 5173,
        hmr: {
            // host: '192.168.0.174'
            host: 'localhost'
        }
    },

    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
