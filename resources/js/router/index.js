import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const routes = [
    {
        path: '/login',
        name: 'login',
        component: () => import('@/pages/LoginPage.vue'),
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
                component: () => import('@/pages/DashboardPage.vue'),
            },
            {
                path: 'pos',
                name: 'pos',
                component: () => import('@/pages/PosPage.vue'),
            },
            {
                path: 'orders',
                name: 'orders',
                component: () => import('@/pages/OrdersPage.vue'),
            },
            {
                path: 'items',
                name: 'items',
                component: () => import('@/pages/ItemsPage.vue'),
            },
            {
                path: 'categories',
                name: 'categories',
                component: () => import('@/pages/CategoriesPage.vue'),
            },
            {
                path: 'stock',
                name: 'stock',
                component: () => import('@/pages/StockPage.vue'),
            },
            {
                path: 'reports',
                name: 'reports',
                component: () => import('@/pages/ReportsPage.vue'),
            },
            {
                path: 'users',
                name: 'users',
                component: () => import('@/pages/UsersPage.vue'),
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
