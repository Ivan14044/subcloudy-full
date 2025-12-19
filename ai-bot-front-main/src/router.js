import { createRouter, createWebHistory } from 'vue-router';
// ?????????????? ???????????????? ?????????????????????? ?????????? (?????????????????????? ??????????????)
import MainPage from './pages/MainPage.vue';
// ?????????????????? ???????????????? ?????????????????????? ?????????????????????? (lazy loading)
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
import { useI18n } from 'vue-i18n';
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
    { path: '/login', component: LoginPage, meta: { requiresGuest: true } },
    {
        path: '/register',
        component: RegisterPage,
        meta: { requiresGuest: true }
    },
    {
        path: '/forgot-password',
        component: ForgotPasswordPage,
        meta: { requiresGuest: true }
    },
    {
        path: '/reset-password/:token',
        component: ResetPasswordPage,
        meta: { requiresGuest: true },
        props: true
    },
    {
        path: '/auth/callback',
        component: AuthCallback
    },
    {
        path: '/profile',
        component: ProfilePage,
        meta: { requiresAuth: true }
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
            seoKey: 'articles'
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
            seoKey: 'category'
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
        meta: { requiresAuth: true }
    },
    {
        path: '/subscriptions',
        component: SubscriptionsPage,
        meta: { requiresAuth: true }
    },
    {
        path: '/download-app',
        component: DownloadAppPage,
        meta: { requiresAuth: true }
    },
    {
        path: '/session-start/:id?',
        component: SessionStart,
        meta: { requiresAuth: true }
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
        // 1) Back/forward ??? use browser-saved position
        if (savedPosition) {
            try {
                sessionStorage.setItem('articlesUsedSavedPosition', '1');
            } catch (_) {}
            return savedPosition;
        }

        // Defer scrolling for article/category list pages until data is loaded in component
        if (to.meta && to.meta.isArticlesList) {
            return false;
        }

        // 2) Anchors ??? scroll to element
        if (to.hash) {
            return { el: to.hash, top: 0, left: 0, behavior: 'auto' };
        }

        // 3) Default ??? ensure layout is ready (Firefox quirk)
        return new Promise(resolve => {
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    resolve({ top: 0, left: 0, behavior: 'auto' });
                });
            });
        });
    }
});

router.beforeEach(async (to, from, next) => {
    // ?????????????????????????? ???????????? ?? URL (/ru/...)
    try {
        const segs = to.path.split('/').filter(Boolean);
        const langCandidate = segs[0];
        if (SUPPORTED_LOCALES.includes(langCandidate)) {
            if ((i18n.global?.locale?.value) !== langCandidate) {
                i18n.global.locale.value = langCandidate;
                try { localStorage.setItem('user-language', langCandidate); } catch {}
            }
        }
    } catch {}
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
        console.log('[Router] Dynamic route detected, path:', to.path);
        const pageStore = usePageStore();
        const { locale } = useI18n();
        
        // ?????????????????? slug ???? ????????, ???????????? ???????????? ???????? ?????? ????????
        let slug = to.path.replace(/^\/|\/$/g, '');
        console.log('[Router] Initial slug:', slug);
        const segments = slug.split('/').filter(Boolean);
        console.log('[Router] Segments:', segments);
        
        // ???????? ???????????? ?????????????? - ?????? ????????????, ?????????????? ??????
        if (segments.length > 0 && SUPPORTED_LOCALES.includes(segments[0])) {
            console.log('[Router] Removing locale:', segments[0]);
            segments.shift();
            slug = segments.join('/');
        }
        console.log('[Router] Final slug:', slug);
        console.log('[Router] Current locale:', locale.value);
        
        // ???????? slug ???????????? ?????????? ???????????????? ????????????, ?????? ?????????????? ????????????????
        if (!slug || slug === '') {
            console.log('[Router] Empty slug, redirecting to home');
            return next('/');
        }
        
        // ?????????????????? ???????????? ?? ???????????? ?????????????? ????????????
        const needsFetch = !pageStore.pages || Object.keys(pageStore.pages).length === 0 || pageStore.currentLocale !== locale.value;
        console.log('[Router] Needs fetch:', needsFetch);
        console.log('[Router] Current pages:', pageStore.pages);
        console.log('[Router] Current locale in store:', pageStore.currentLocale);
        
        if (needsFetch) {
            console.log('[Router] Fetching pages data...');
            await pageStore.fetchData(locale.value);
            console.log('[Router] Pages fetched, available slugs:', Object.keys(pageStore.pages));
        }

        // ?????????????????? ?????????????? ???????????????? ???? slug
        console.log('[Router] Checking for slug:', slug);
        console.log('[Router] Page exists:', typeof pageStore.pages[slug] !== 'undefined');
        console.log('[Router] Page data:', pageStore.pages[slug]);
        
        if (typeof pageStore.pages[slug] !== 'undefined' && pageStore.pages[slug] !== null) {
            console.log('[Router] Setting page for slug:', slug);
            pageStore.setPage(pageStore.pages[slug]);
            console.log('[Router] Page set successfully');
            return next();
        } else {
            // ???????? ???????????????? ???? ??????????????, ?????????????? ?????????????????? ???????????? ?????? ?????? ?? ??????????????????
            console.warn(`[Router] Page not found for slug: "${slug}"`);
            console.warn(`[Router] Available pages:`, Object.keys(pageStore.pages));
            // ???? ???????????????????????????? ???? 404, ?????????? ContentPage ?????? ???????????????????? ???????????????????? ????????????
            pageStore.setPage(null);
            return next();
        }
    }

    next();
});

// ?????????? ???????????????? ?????????????????? ?????????????? ????????-???????? ?? prefetch ???????????????????? ??????????????????
router.afterEach((to) => {
    // Prefetch ???????????????????? ?????????????????? ?? ?????????????????? (???? ?????????????????? ??????????????????)
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
        const currentLocale = SUPPORTED_LOCALES.includes(maybeLang) ? maybeLang : ((i18n.global?.locale?.value) || 'ru');
        const key = to.meta?.seoKey;
        const dict = key ? getSeoStrings(currentLocale, key) : undefined;
        const baseTitle = dict?.title || to.meta?.title || 'SubCloudy';
        const baseDesc = dict?.description || to.meta?.description || 'SubCloudy ???????????????? ?????????????????? ???? ?????????????????? ?? ????????????????: ?????????????????? ???????????????? ?? ????????????????????????.';
        const locales = Object.keys((i18n.global)?.messages || {}).filter(l => SUPPORTED_LOCALES.includes(l));
        const hreflangs = Array.isArray(locales)
            ? locales.map(code => ({ href: replaceLocaleInPath(to.fullPath, code), lang: code }))
            : undefined;
        // ???????????????????? <html lang="...">
        try { document.documentElement.setAttribute('lang', currentLocale); } catch {}
        const canonical = replaceLocaleInPath(to.fullPath, currentLocale);
        updateWebPageSEO({
            title: baseTitle,
            description: baseDesc,
            keywords: to.meta?.keywords,
            canonical,
            hreflangs
        });
    } catch (_) {}
});

export default router;
