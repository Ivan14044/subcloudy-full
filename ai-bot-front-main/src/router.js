import { createRouter, createWebHistory } from 'vue-router';
import LoginPage from './components/auth/LoginPage.vue';
import RegisterPage from './components/auth/RegisterPage.vue';
import AuthCallback from './components/auth/AuthCallback.vue';
import MainPage from './pages/MainPage.vue';
import ProfilePage from './pages/account/ProfilePage.vue';
import ServicePage from './pages/ServicePage.vue';
import SubscriptionsPage from './pages/account/SubscriptionsPage.vue';
import SessionStart from './pages/SessionStart.vue';
import ForgotPasswordPage from './components/auth/ForgotPasswordPage.vue';
import ResetPasswordPage from './components/auth/ResetPasswordPage.vue';
import CheckoutPage from './pages/CheckoutPage.vue';
import ContentPage from './pages/ContentPage.vue';
import NotFound from './pages/NotFound.vue';
import ArticlesAll from './pages/articles/ArticlesAll.vue';
import ArticleDetails from './pages/articles/ArticleDetails.vue';
import DownloadAppPage from './pages/DownloadAppPage.vue';
import { useAuthStore } from './stores/auth';
import { usePageStore } from './stores/pages';
import { updateWebPageSEO } from './utils/seo';
import i18n from './i18n';
import { getSeoStrings } from './utils/seoStrings';

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
        // 1) Back/forward — use browser-saved position
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

        // 2) Anchors — scroll to element
        if (to.hash) {
            return { el: to.hash, top: 0, left: 0, behavior: 'auto' };
        }

        // 3) Default — ensure layout is ready (Firefox quirk)
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
    // Синхронизация локали с URL (/ru/...)
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
        const slug = to.path.replace(/^\/|\/$/g, '');
        const pageStore = usePageStore();
        if (!pageStore.pages.length) {
            await pageStore.fetchData();
        }

        if (typeof pageStore.pages[slug] !== 'undefined') {
            pageStore.setPage(pageStore.pages[slug]);
            return next();
        } else {
            return next('/404');
        }
    }

    next();
});

// После перехода обновляем базовые мета-теги
router.afterEach((to) => {
    try {
        const segs = to.fullPath.split('/').filter(Boolean);
        const maybeLang = segs[0];
        const currentLocale = SUPPORTED_LOCALES.includes(maybeLang) ? maybeLang : ((i18n.global?.locale?.value) || 'ru');
        const key = to.meta?.seoKey;
        const dict = key ? getSeoStrings(currentLocale, key) : undefined;
        const baseTitle = dict?.title || to.meta?.title || 'SubCloudy';
        const baseDesc = dict?.description || to.meta?.description || 'SubCloudy помогает экономить на подписках и сервисах: аналитика расходов и рекомендации.';
        const locales = Object.keys((i18n.global)?.messages || {}).filter(l => SUPPORTED_LOCALES.includes(l));
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
            hreflangs
        });
    } catch (_) {}
});

export default router;
