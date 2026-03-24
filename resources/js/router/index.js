import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const routes = [
    {
        path: '/login',
        name: 'login',
        component: () => import('@/pages/login/LoginPage.vue'),
        meta: { guest: true },
    },
    {
        path: '/',
        component: () => import('@/layouts/AppLayout.vue'),
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                redirect: '/dashboard',
            },
            {
                path: 'dashboard',
                name: 'dashboard',
                component: () => import('@/pages/dashboard/DashboardPage.vue'),
            },
            {
                path: 'pos',
                name: 'pos',
                component: () => import('@/pages/pos/PosPage.vue'),
            },
            {
                path: 'orders',
                name: 'orders',
                component: () => import('@/pages/orders/OrderListComponent.vue'),
            },
            {
                path: 'items',
                name: 'items',
                component: () => import('@/pages/items/ItemListComponent.vue'),
            },
            {
                path: 'categories',
                name: 'categories',
                component: () => import('@/pages/categories/CategoryListComponent.vue'),
            },
            {
                path: 'stock',
                name: 'stock',
                component: () => import('@/pages/stock/StockListComponent.vue'),
            },
            {
                path: 'reports',
                name: 'reports',
                component: () => import('@/pages/reports/ReportsPage.vue'),
            },
            {
                path: 'users',
                name: 'users',
                component: () => import('@/pages/users/UserListComponent.vue'),
                meta: { requiresAdmin: true },
            },
            {
                path: 'settings',
                name: 'settings',
                component: () => import('@/pages/settings/SettingPage.vue'),
                meta: { requiresAdmin: true },
            },
        ],
    },
    {
        path: '/:pathMatch(.*)*',
        redirect: '/',
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to) => {
    const auth = useAuthStore();

    if (to.meta.requiresAuth && !auth.isLoggedIn) {
        return { name: 'login' };
    }
    if (to.meta.guest && auth.isLoggedIn) {
        return { name: 'dashboard' };
    }
    if (to.meta.requiresAdmin && !auth.isAdmin) {
        return { name: 'dashboard' };
    }
});

export default router;
