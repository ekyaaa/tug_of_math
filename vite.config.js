import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
    plugins: [
        tailwindcss(),
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                'resources/js/game/show.js',
                'resources/css/game/show.css',
                'resources/js/game/lobby.js',
                'resources/js/player/join.js',
                'resources/js/player/controller.js'
            ],
            refresh: true,
        }),
    ],
});
