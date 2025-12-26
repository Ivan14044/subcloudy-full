import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import vueI18n from '@intlify/unplugin-vue-i18n/vite';
import path from 'node:path';
import { copyFileSync, mkdirSync, existsSync, readFileSync, writeFileSync } from 'fs';

export default defineConfig({
    plugins: [
        vue(),
        {
            name: 'html-transform',
            transformIndexHtml(html) {
                // 1. Добавляем SEO ссылки (существующая логика)
                let transformedHtml = html.replace(
                    '</body>',
                    '<nav style="display:none;"><a href="/#services">Выберите свой сервис</a><a href="/articles">Все статьи</a><a href="/cookie-policy">Политика использования cookies</a></nav></body>'
                );

                // 2. Оптимизируем загрузку CSS
                // Оставляем основные стили блокирующими для предотвращения CLS, 
                // но добавляем preload для ускорения
                transformedHtml = transformedHtml.replace(
                    /<link rel="stylesheet"([^>]+)href="([^"]+)"([^>]*?)>/g,
                    (match, p1, href, p3) => {
                        return `<link rel="preload" href="${href}" as="style"><link rel="stylesheet" href="${href}"${p3}>`;
                    }
                );

                return transformedHtml;
            }
        },
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
        },
        // Плагин для добавления modulepreload для критических JS файлов
        {
            name: 'add-modulepreload',
            writeBundle(options, bundle) {
                const indexPath = path.resolve(__dirname, 'dist/index.html');
                
                if (!existsSync(indexPath)) {
                    return;
                }
                
                let html = readFileSync(indexPath, 'utf-8');
                
                // Найти entry JS файл (файл, который импортируется в index.html)
                const entryFile = Object.keys(bundle).find(file => 
                    bundle[file].type === 'chunk' && 
                    bundle[file].isEntry &&
                    file.endsWith('.js')
                );
                
                if (entryFile && bundle[entryFile].fileName) {
                    const entryFileName = bundle[entryFile].fileName;
                    const modulepreloadTag = `  <link rel="modulepreload" crossorigin href="/${entryFileName}">\n`;
                    
                    // Проверить, есть ли уже modulepreload для entry файла
                    const entryModulepreloadPattern = new RegExp(`modulepreload.*${entryFileName.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}`);
                    
                    if (!entryModulepreloadPattern.test(html)) {
                        // Найти тег script с entry файлом и добавить modulepreload перед ним
                        const scriptPattern = new RegExp(`(<script[^>]*src="/${entryFileName.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}"[^>]*>)`);
                        if (scriptPattern.test(html)) {
                            html = html.replace(scriptPattern, `${modulepreloadTag}$1`);
                            writeFileSync(indexPath, html, 'utf-8');
                            console.log(`✓ Added modulepreload for entry file ${entryFileName}`);
                        }
                    }
                }
            }
        }
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'src')
            // Убираем алиас для lottie-web, чтобы он загружался динамически
            // 'lottie-web': path.resolve(__dirname, 'node_modules/lottie-web/build/player/lottie_light.js')
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
                        // Критичные зависимости для первой отрисовки
                        if (id.includes('vue') && !id.includes('vue-router') && !id.includes('vue-i18n')) return 'vendor-vue';
                        if (id.includes('pinia')) return 'vendor-pinia';
                        if (id.includes('vue-router')) return 'vendor-router';
                        if (id.includes('vue-i18n')) return 'vendor-i18n';
                        // UI компоненты
                        if (id.includes('lucide')) return 'vendor-ui';
                        // Слайдер (загружается только на странице отзывов)
                        if (id.includes('swiper')) return 'vendor-swiper';
                        // HTTP клиент
                        if (id.includes('axios')) return 'vendor-axios';
                        // Модальные окна
                        if (id.includes('sweetalert2')) return 'vendor-swal';
                        // Уведомления
                        if (id.includes('vue-toastification')) return 'vendor-toast';
                        // Остальное не группируем - пусть остается в отдельных файлах
                    }
                },
                // Улучшаем кэширование и уменьшаем цепочки
                chunkFileNames: 'assets/[name]-[hash].js',
                entryFileNames: 'assets/[name]-[hash].js',
                assetFileNames: 'assets/[name]-[hash].[ext]'
            }
        },
        cssCodeSplit: true,
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true,
                drop_debugger: true,
                pure_funcs: ['console.log', 'console.info', 'console.debug']
            },
            mangle: true,
            format: {
                comments: false
            }
        }
    }
});
