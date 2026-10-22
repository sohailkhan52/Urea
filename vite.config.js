import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                    display: 'swap', // Add font-display: swap for better performance
                    optimizedFallbacks: false, // Disable fontaine requirement
                }),
            ],
        }),
        tailwindcss(),
    ],
    build: {
        // Vite v8 uses esbuild for minification by default (no terser needed)
        minify: 'esbuild',
        // Reduce chunk size warnings
        chunkSizeWarningLimit: 1000,
    },
    server: {
        cors: true,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
