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
        chunkSizeWarningLimit: 1000,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules')) {
                        if (id.includes('vue') || id.includes('pinia')) return 'vendor-vue';
                        if (id.includes('lucide')) return 'vendor-ui';
                        if (id.includes('lottie')) return 'vendor-lottie';
                        if (id.includes('axios')) return 'vendor-axios';
                        return 'vendor';
                    }
                }
            }
        },
        cssCodeSplit: true,
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: false, // Временно отключено для отладки
                drop_debugger: true,
                // pure_funcs: ['console.log', 'console.info', 'console.debug'] // Временно отключено
            },
            mangle: true,
            format: {
                comments: false
            }
        }
    }
});
