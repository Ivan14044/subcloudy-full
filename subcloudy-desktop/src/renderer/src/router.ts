import { createRouter, createWebHashHistory, RouteRecordRaw } from 'vue-router';
import { useAuthStore } from './stores/auth';
import LoginPage from './pages/LoginPage.vue';
import ServicesPage from './pages/ServicesPage.vue';
import ProfilePage from './pages/ProfilePage.vue';
import SubscriptionsPage from './pages/SubscriptionsPage.vue';
import HistoryPage from './pages/HistoryPage.vue';

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    redirect: '/login'
  },
  {
    path: '/login',
    name: 'Login',
    component: LoginPage
  },
  {
    path: '/services',
    name: 'Services',
    component: ServicesPage,
    meta: { requiresAuth: true }
  },
  {
    path: '/profile',
    name: 'Profile',
    component: ProfilePage,
    meta: { requiresAuth: true }
  },
  {
    path: '/subscriptions',
    name: 'Subscriptions',
    component: SubscriptionsPage,
    meta: { requiresAuth: true }
  },
  {
    path: '/history',
    name: 'History',
    component: HistoryPage,
    meta: { requiresAuth: true }
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    redirect: '/login'
  }
];

const router = createRouter({
  history: createWebHashHistory(),
  routes
});

// Guard для проверки аутентификации
router.beforeEach(async (to, from, next) => {
  console.log('[Router] Navigating from', from.path, 'to', to.path);
  
  const authStore = useAuthStore();
  
  // Проверка аутентификации при первом запуске
  if (!authStore.initialized) {
    await authStore.checkAuth();
  }

  const requiresAuth = to.matched.some(record => record.meta.requiresAuth);
  const isAuthenticated = authStore.isAuthenticated;

  console.log('[Router] Auth required:', requiresAuth, 'Is authenticated:', isAuthenticated);

  if (requiresAuth && !isAuthenticated) {
    console.log('[Router] Redirecting to login');
    next({ name: 'Login' });
  } else if (to.name === 'Login' && isAuthenticated) {
    console.log('[Router] Already authenticated, redirecting to services');
    next({ name: 'Services' });
  } else {
    console.log('[Router] Allowing navigation');
    next();
  }
});

export default router;

