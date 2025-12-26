import { createRouter, createWebHistory } from 'vue-router';
// Главная страница загружается сразу (критический контент)
import MainPage from './pages/MainPage.vue';
// Остальные страницы загружаются динамически (lazy loading)
const LoginPage = () => import('./components/auth/LoginPage.vue');
const RegisterPage = () => import('./components/auth/RegisterPage.vue');
const AuthCallback = () => import('./components/auth/AuthCallback.vue');
const ProfilePage = () => import('./pages/account/ProfilePage.vue');
const ServicePage = () => import('./pages/ServicePage.vue');
const SubscriptionsPage = () => import('./pages/account/SubscriptionsPage.vue');
const SessionStart = () => import('./pages/SessionStart.vue');
const ForgotPasswordPage = () => import('./components/auth/ForgotPasswordPage.vue');
const ResetPasswordPage = () => import('./components/auth/ResetPasswordPage.vue');
const CheckoutPage = () => import('./pages/CheckoutPage.vue');
const ContentPage = () => import('./pages/ContentPage.vue');
const NotFound = () => import('./pages/NotFound.vue');
const ArticlesAll = () => import('./pages/articles/ArticlesAll.vue');
const ArticleDetails = () => import('./pages/articles/ArticleDetails.vue');
const DownloadAppPage = () => import('./pages/DownloadAppPage.vue');
import { useAuthStore } from './stores/auth';
import { usePageStore } from './stores/pages';
import { updateWebPageSEO } from './utils/seo';
import i18n from './i18n';
import { getSeoStrings } from './utils/seoStrings';
import { prefetchRoute } from './utils/prefetchUtils';

const SUPPORTED_LOCALES = ['ru', 'uk', 'en', 'es', 'zh'];

function replaceLocaleInPath(fullPath, nextLocale) {
	try {
		const url = new URL(fullPath, window.location.origin);
		const segments = url.pathname.split('/').filter(Boolean);
		if (segments.length && SUPPORTED_LOCALES.includes(segments[0])) {
			segments[0] = nextLocale;
		} else {
			segments.unshift(nextLocale);
		}
		url.pathname = '/' + segments.join('/');
		return url.pathname + url.search + url.hash;
	} catch {
		const parts = fullPath.split('#')[0].split('?')[0].split('/').filter(Boolean);
		if (parts.length && SUPPORTED_LOCALES.includes(parts[0])) {
			parts[0] = nextLocale;
		} else {
			parts.unshift(nextLocale);
		}
		return '/' + parts.join('/');
	}
}

const routes = [
    {
        path: '/',
        component: MainPage,
        meta: {
            seoKey: 'home'
        }
    },
    { path: '/login', component: LoginPage, meta: { requiresGuest: true, robots: 'noindex, nofollow' } },
    {
        path: '/register',
        component: RegisterPage,
        meta: { requiresGuest: true, robots: 'noindex, nofollow' }
    },
    {
        path: '/forgot-password',
        component: ForgotPasswordPage,
        meta: { requiresGuest: true, robots: 'noindex, nofollow' }
    },
    {
        path: '/reset-password/:token',
        component: ResetPasswordPage,
        meta: { requiresGuest: true, robots: 'noindex, nofollow' },
        props: true
    },
    {
        path: '/auth/callback',
        component: AuthCallback,
        meta: { robots: 'noindex, nofollow' }
    },
    {
        path: '/profile',
        component: ProfilePage,
        meta: { requiresAuth: true, robots: 'noindex, nofollow' }
    },
    {
        path: '/service/:id',
        component: ServicePage,
        alias: ['/service/:id/:slug?']
    },
    {
        path: '/articles',
        component: ArticlesAll,
        meta: {
            isArticlesList: true,
            seoKey: 'articles'
        }
    },
    {
        path: '/articles/page/:page',
        component: ArticlesAll,
        meta: {
            isArticlesList: true,
            seoKey: 'articles',
            robots: 'noindex, follow'
        }
    },
    {
        path: '/categories/:id',
        component: ArticlesAll,
        alias: ['/categories/:id/:slug?'],
        meta: {
            isArticlesList: true,
            seoKey: 'category'
        }
    },
    {
        path: '/categories/:id/page/:page',
        component: ArticlesAll,
        meta: {
            isArticlesList: true,
            seoKey: 'category',
            robots: 'noindex, follow'
        }
    },
    {
        path: '/articles/:id',
        component: ArticleDetails,
        alias: ['/articles/:id/:slug?']
    },
    {
        path: '/checkout',
        component: CheckoutPage,
        meta: { requiresAuth: true, robots: 'noindex, nofollow' }
    },
    {
        path: '/subscriptions',
        component: SubscriptionsPage,
        meta: { requiresAuth: true, robots: 'noindex, nofollow' }
    },
    {
        path: '/download-app',
        component: DownloadAppPage,
        meta: { requiresAuth: true, robots: 'noindex, nofollow' }
    },
    {
        path: '/session-start/:id?',
        component: SessionStart,
        meta: { requiresAuth: true, robots: 'noindex, nofollow' }
    },
    {
        path: '/:slug(.*)*',
        name: 'dynamic',
        component: ContentPage,
        meta: { isDynamic: true }
    },
    {
        path: '/404',
        component: NotFound
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition;
        }

        if (to.hash) {
            return { el: to.hash, top: 0, behavior: 'smooth' };
        }

        // Always scroll to top on route change
        return { top: 0, left: 0 };
    }
});

router.beforeEach(async (to, from, next) => {
    // Синхронизация локали с URL (/ru/...)
    try {
        
        const segs = to.path.split('/').filter(Boolean);
        const langCandidate = segs[0];
        if (SUPPORTED_LOCALES.includes(langCandidate)) {
            // Безопасное получение текущей локали
            let currentI18nLocale = 'ru';
            try {
                if (i18n && i18n.global && i18n.global.locale && i18n.global.locale.value) {
                    currentI18nLocale = i18n.global.locale.value;
                }
            } catch (i18nGetError) {
                console.error('[Router] Error getting i18n locale:', i18nGetError);
                // Пробуем получить из localStorage
                try {
                    const savedLang = localStorage.getItem('user-language');
                    if (savedLang && SUPPORTED_LOCALES.includes(savedLang)) {
                        currentI18nLocale = savedLang;
                    }
                } catch (e) {
                    // Игнорируем ошибки localStorage
                }
            }
            
            if (currentI18nLocale !== langCandidate) {
                try {
                    if (i18n && i18n.global && i18n.global.locale) {
                        i18n.global.locale.value = langCandidate;
                    }
                } catch (i18nSetError) {
                    console.error('[Router] Error setting i18n locale:', i18nSetError);
                }
                try { localStorage.setItem('user-language', langCandidate); } catch {}
            }
        }
    } catch (e) {
        console.error('[Router] Error in locale synchronization:', e);
    }
    const authStore = useAuthStore();
    if (!authStore.user && authStore.token) {
        await authStore.fetchUser();
    }

    if (to.meta.requiresAuth && !authStore.user) {
        return next({
            path: '/login',
            query: { redirect: to.fullPath }
        });
    } else if (to.meta.requiresGuest && authStore.user) {
        return next('/');
    }

    // Save the route user came from when first entering articles/categories list
    try {
        const wasInArticles = Boolean(from?.meta?.isArticlesList);
        const isGoingToArticles = Boolean(to?.meta?.isArticlesList);
        if (!wasInArticles && isGoingToArticles) {
            // store the entry point before entering the list
            sessionStorage.setItem('articlesEntryFrom', from?.fullPath || '/');
        }
    } catch (_) {
        // ignore storage errors (Safari private mode, etc.)
    }

    if (to.meta.isDynamic) {
        // Обертываем весь блок в try-catch для защиты от любых ошибок
        try {
            console.log('[Router] Dynamic route detected, path:', to.path);
            
            let pageStore;
            try {
                console.log('[Router] Starting page store initialization...');
                pageStore = usePageStore();
                console.log('[Router] Page store initialized');
            } catch (storeError) {
                console.error('[Router] Error initializing page store:', storeError);
                // Продолжаем без store, пусть ContentPage сам загрузит данные
                return next();
            }
            
            try {
            
            // Получаем локаль безопасным способом
            let currentLocale = 'ru'; // Значение по умолчанию
            try {
                // Пробуем получить локаль из i18n напрямую
                try {
                    if (i18n && i18n.global && i18n.global.locale && i18n.global.locale.value) {
                        currentLocale = i18n.global.locale.value;
                        console.log('[Router] Locale from i18n.global:', currentLocale);
                    } else {
                        // Пробуем получить из localStorage
                        try {
                            const savedLang = localStorage.getItem('user-language');
                            if (savedLang && SUPPORTED_LOCALES.includes(savedLang)) {
                                currentLocale = savedLang;
                                console.log('[Router] Locale from localStorage:', currentLocale);
                            }
                        } catch (e) {
                            console.warn('[Router] Could not read localStorage:', e);
                        }
                    }
                } catch (i18nAccessError) {
                    console.error('[Router] Error accessing i18n, using fallback:', i18nAccessError);
                    // Пробуем получить из localStorage как fallback
                    try {
                        const savedLang = localStorage.getItem('user-language');
                        if (savedLang && SUPPORTED_LOCALES.includes(savedLang)) {
                            currentLocale = savedLang;
                        }
                    } catch (e) {
                        // Игнорируем ошибки localStorage
                    }
                }
            } catch (i18nError) {
                console.error('[Router] Error getting locale, using default:', i18nError);
                // Пробуем получить из localStorage как fallback
                try {
                    const savedLang = localStorage.getItem('user-language');
                    if (savedLang && SUPPORTED_LOCALES.includes(savedLang)) {
                        currentLocale = savedLang;
                    }
                } catch (e) {
                    // Игнорируем ошибки localStorage
                }
            }
            console.log('[Router] Using locale:', currentLocale);
            
            // Извлекаем slug из пути, убирая локаль если она есть
            let slug = to.path.replace(/^\/|\/$/g, '');
            console.log('[Router] Initial slug:', slug);
            const segments = slug.split('/').filter(Boolean);
            console.log('[Router] Segments:', segments);
            
            // Если первый сегмент - это локаль, убираем его
            if (segments.length > 0 && SUPPORTED_LOCALES.includes(segments[0])) {
                console.log('[Router] Removing locale:', segments[0]);
                segments.shift();
                slug = segments.join('/');
            }
            console.log('[Router] Final slug:', slug);
            console.log('[Router] Current locale:', currentLocale);
            
            // Если slug пустой после удаления локали, это главная страница
            if (!slug || slug === '') {
                console.log('[Router] Empty slug, redirecting to home');
                return next('/');
            }
            
            // Загружаем данные с учетом текущей локали
            const hasPages = pageStore.pages && typeof pageStore.pages === 'object';
            const pagesCount = hasPages ? Object.keys(pageStore.pages).length : 0;
            const localeMatches = pageStore.currentLocale === currentLocale;
            const needsFetch = !hasPages || pagesCount === 0 || !localeMatches;
            
            console.log('[Router] Needs fetch:', needsFetch);
            console.log('[Router] Has pages:', hasPages);
            console.log('[Router] Pages count:', pagesCount);
            console.log('[Router] Current locale in store:', pageStore.currentLocale);
            console.log('[Router] Requested locale:', currentLocale);
            console.log('[Router] Locale matches:', localeMatches);
            
            if (needsFetch) {
                console.log('[Router] Fetching pages data...');
                await pageStore.fetchData(currentLocale);
                const fetchedSlugs = pageStore.pages && typeof pageStore.pages === 'object' ? Object.keys(pageStore.pages) : [];
                console.log('[Router] Pages fetched, available slugs:', fetchedSlugs);
                console.log('[Router] Fetched pages count:', fetchedSlugs.length);
            }

            // Функция для нормализации slug (приводим к нижнему регистру, убираем только пробелы)
            const normalizeSlug = (s) => {
                if (!s) return '';
                return s.toLowerCase().trim();
            };
            
            // Проверяем наличие страницы по slug
            console.log('[Router] Checking for slug:', slug);
            console.log('[Router] Pages object type:', typeof pageStore.pages);
            console.log('[Router] Pages is array:', Array.isArray(pageStore.pages));
            console.log('[Router] Pages keys count:', pageStore.pages ? Object.keys(pageStore.pages).length : 0);
            console.log('[Router] Page exists:', typeof pageStore.pages[slug] !== 'undefined');
            console.log('[Router] Page data:', pageStore.pages[slug]);
            console.log('[Router] All available slugs:', Object.keys(pageStore.pages));
            
            let foundPage = null;
            let foundSlug = null;
            
            // Проверяем, что pages - это объект и не пустой
            if (!pageStore.pages || typeof pageStore.pages !== 'object' || Array.isArray(pageStore.pages)) {
                console.error('[Router] Invalid pages data structure:', pageStore.pages);
                pageStore.setPage(null);
                return next();
            }
            
            // Сначала пробуем точное совпадение
            if (slug && typeof slug === 'string' && typeof pageStore.pages[slug] !== 'undefined' && pageStore.pages[slug] !== null) {
                foundPage = pageStore.pages[slug];
                foundSlug = slug;
                console.log('[Router] Found page by exact match:', slug);
                console.log('[Router] Found page data type:', typeof foundPage);
                console.log('[Router] Found page is object:', typeof foundPage === 'object');
            } else {
                // Если точного совпадения нет, пробуем найти по нормализованному slug
                const normalizedSlug = normalizeSlug(slug);
                console.log('[Router] Trying normalized slug:', normalizedSlug);
                
                for (const availableSlug of Object.keys(pageStore.pages)) {
                    const normalizedAvailable = normalizeSlug(availableSlug);
                    if (normalizedSlug === normalizedAvailable) {
                        foundPage = pageStore.pages[availableSlug];
                        foundSlug = availableSlug;
                        console.log('[Router] Found page by normalized slug:', availableSlug);
                        break;
                    }
                }
                
                // Если все еще не найдено, пробуем частичное совпадение
                if (!foundPage) {
                    for (const availableSlug of Object.keys(pageStore.pages)) {
                        const normalizedAvailable = normalizeSlug(availableSlug);
                        if (normalizedSlug.includes(normalizedAvailable) || normalizedAvailable.includes(normalizedSlug)) {
                            foundPage = pageStore.pages[availableSlug];
                            foundSlug = availableSlug;
                            console.log('[Router] Found page by partial match:', availableSlug);
                            break;
                        }
                    }
                }
            }
            
            if (foundPage && foundSlug && typeof foundPage === 'object' && foundPage !== null) {
                console.log('[Router] Setting page for slug:', foundSlug);
                console.log('[Router] Page data structure:', foundPage);
                console.log('[Router] Page data type:', typeof foundPage);
                console.log('[Router] Page data keys:', Object.keys(foundPage));
                console.log('[Router] Page data sample:', JSON.stringify(foundPage, null, 2).substring(0, 500));
                
                // Убеждаемся, что данные валидны перед установкой
                if (Object.keys(foundPage).length > 0) {
                    try {
                        pageStore.setPage(foundPage);
                        console.log('[Router] Page set successfully');
                        console.log('[Router] pageStore.page after set:', pageStore.page);
                        console.log('[Router] pageStore.page type:', typeof pageStore.page);
                        console.log('[Router] pageStore.page keys:', pageStore.page ? Object.keys(pageStore.page) : 'null');
                        return next();
                    } catch (e) {
                        console.error('[Router] Error setting page:', e);
                        pageStore.setPage(null);
                        return next();
                    }
                } else {
                    console.warn('[Router] Found page but it is empty object');
                }
            } else {
                // Если страница не найдена, пробуем загрузить данные еще раз и проверить
                console.warn(`[Router] Page not found for slug: "${slug}"`);
                console.warn(`[Router] Available pages:`, Object.keys(pageStore.pages));
                // Пробуем перезагрузить данные еще раз
                try {
                    await pageStore.fetchData(currentLocale, true);
                    // Повторяем поиск после перезагрузки
                    if (typeof pageStore.pages[slug] !== 'undefined' && pageStore.pages[slug] !== null) {
                        console.log('[Router] Page found after refetch, setting page');
                        pageStore.setPage(pageStore.pages[slug]);
                        return next();
                    } else {
                        // Пробуем найти по нормализованному slug после перезагрузки
                        const normalizedSlug = normalizeSlug(slug);
                        for (const availableSlug of Object.keys(pageStore.pages)) {
                            const normalizedAvailable = normalizeSlug(availableSlug);
                            if (normalizedSlug === normalizedAvailable) {
                                console.log('[Router] Page found after refetch by normalized slug:', availableSlug);
                                pageStore.setPage(pageStore.pages[availableSlug]);
                                return next();
                            }
                        }
                    }
            } catch (e) {
                console.error('[Router] Error refetching pages:', e);
            }
            // Не перенаправляем на 404, пусть ContentPage сам обработает отсутствие данных
            pageStore.setPage(null);
            return next();
            }
            } catch (innerError) {
                console.error('[Router] Error in dynamic route handler inner try:', innerError);
                console.error('[Router] Error stack:', innerError.stack);
                // Продолжаем навигацию, пусть ContentPage обработает отсутствие данных
                try {
                    if (pageStore) {
                        pageStore.setPage(null);
                    }
                } catch (e) {
                    console.error('[Router] Error setting page to null:', e);
                }
                return next();
            }
        } catch (outerError) {
            console.error('[Router] CRITICAL: Error in dynamic route handler outer catch:', outerError);
            console.error('[Router] Error stack:', outerError.stack);
            // Продолжаем навигацию в любом случае
            try {
                const pageStore = usePageStore();
                pageStore.setPage(null);
            } catch (e) {
                // Игнорируем ошибки
            }
            return next();
        }
    }

    next();
});

// После перехода обновляем базовые мета-теги и prefetch популярных маршрутов
router.afterEach((to) => {
    // Prefetch популярных маршрутов с задержкой (не блокирует навигацию)
    if (to.path === '/' || to.path.startsWith('/articles')) {
        setTimeout(() => {
            const prefetchRoutes = ['/articles', '/login', '/register'];
            prefetchRoutes.forEach(route => {
                if (route !== to.path) {
                    prefetchRoute(route);
                }
            });
        }, 2000);
    }

    try {
        const segs = to.fullPath.split('/').filter(Boolean);
        const maybeLang = segs[0];
        // Безопасное получение локали
        let currentLocale = 'ru';
        if (SUPPORTED_LOCALES.includes(maybeLang)) {
            currentLocale = maybeLang;
        } else {
            try {
                if (i18n && i18n.global && i18n.global.locale && i18n.global.locale.value) {
                    currentLocale = i18n.global.locale.value;
                } else {
                    try {
                        const savedLang = localStorage.getItem('user-language');
                        if (savedLang && SUPPORTED_LOCALES.includes(savedLang)) {
                            currentLocale = savedLang;
                        }
                    } catch (e) {
                        // Игнорируем ошибки localStorage
                    }
                }
            } catch (e) {
                console.error('[Router] Error getting locale in afterEach:', e);
                try {
                    const savedLang = localStorage.getItem('user-language');
                    if (savedLang && SUPPORTED_LOCALES.includes(savedLang)) {
                        currentLocale = savedLang;
                    }
                } catch (e2) {
                    // Игнорируем ошибки localStorage
                }
            }
        }
        
        const key = to.meta?.seoKey;
        const dict = key ? getSeoStrings(currentLocale, key) : undefined;
        const baseTitle = dict?.title || to.meta?.title || 'SubCloudy';
        const baseDesc = dict?.description || to.meta?.description || 'SubCloudy помогает экономить на подписках и сервисах: аналитика расходов и рекомендации.';
        
        // Безопасное получение списка локалей
        let locales = SUPPORTED_LOCALES;
        try {
            if (i18n && i18n.global && i18n.global.messages) {
                const availableLocales = Object.keys(i18n.global.messages);
                locales = availableLocales.filter(l => SUPPORTED_LOCALES.includes(l));
            }
        } catch (e) {
            console.error('[Router] Error getting locales from i18n:', e);
        }
        const hreflangs = Array.isArray(locales)
            ? locales.map(code => ({ href: replaceLocaleInPath(to.fullPath, code), lang: code }))
            : undefined;
        // переключим <html lang="...">
        try { document.documentElement.setAttribute('lang', currentLocale); } catch {}
        const canonical = replaceLocaleInPath(to.fullPath, currentLocale);
        updateWebPageSEO({
            title: baseTitle,
            description: baseDesc,
            keywords: to.meta?.keywords,
            canonical,
            hreflangs,
            robots: to.meta?.robots
        });
    } catch (_) {}
});

export default router;
