import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue(),
        tailwindcss(),
    ],
    clearScreen: false, // Не очищаем экран при перезапуске
    server: {
        host: '0.0.0.0', // Разрешаем доступ извне
        port: 5173,
        strictPort: true,
        watch: {
            usePolling: true,
        },
    },
    customLogger: {
        info: (msg) => {
            // Показываем только сообщения от Laravel plugin
            if (msg.includes('LARAVEL') || msg.includes('APP_URL')) {
                console.log(msg);
            }
        },
        warn: console.warn,
        error: console.error,
    },
});
