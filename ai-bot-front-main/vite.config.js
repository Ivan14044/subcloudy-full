import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import vueI18n from '@intlify/unplugin-vue-i18n/vite';
import path from 'node:path';
import { copyFileSync, mkdirSync } from 'fs';

export default defineConfig({
    plugins: [
        vue(),
        vueI18n({
            include: path.resolve(__dirname, './src/locales/**')
        }),
        // Плагин для копирования logo.webp в dist/img/
        {
            name: 'copy-logo',
            writeBundle() {
                const src = path.resolve(__dirname, 'src/assets/logo.webp');
                const destDir = path.resolve(__dirname, 'dist/img');
                const dest = path.resolve(destDir, 'logo.webp');
                try {
                    mkdirSync(destDir, { recursive: true });
                    copyFileSync(src, dest);
                    console.log('✓ Logo copied to dist/img/logo.webp');
                } catch (error) {
                    console.warn('Failed to copy logo.webp:', error);
                }
            }
        }
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'src')
        }
    },
    server: {
        proxy: {
            '/api': {
                target: 'http://127.0.0.1:8000',
                changeOrigin: true,
                secure: false
            }
        }
    },
    build: {
        outDir: 'dist',
        sourcemap: false,
        rollupOptions: {
            output: {
                manualChunks: {
                    'vendor-vue': ['vue', 'vue-router', 'pinia'],
                    'vendor-i18n': ['vue-i18n'],
                    'vendor-ui': ['lucide-vue-next']
                }
            }
        },
        cssCodeSplit: true,
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: false, // Оставляем console.log для отладки
                drop_debugger: true,
                pure_funcs: [] // Не удаляем никакие функции
            }
        }
    }
});
