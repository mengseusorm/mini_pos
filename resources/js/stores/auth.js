import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '@/api';

export const useAuthStore = defineStore('auth', () => {
    const user  = ref(JSON.parse(localStorage.getItem('pos_user') || 'null'));
    const token = ref(localStorage.getItem('pos_token') || null);

    const isLoggedIn = computed(() => !!token.value);
    const isAdmin    = computed(() => user.value?.role === 'admin');

    async function login(email, password) {
        const res = await api.post('/login', { email, password });
        user.value  = res.data.user;
        token.value = res.data.token;
        localStorage.setItem('pos_user', JSON.stringify(res.data.user));
        localStorage.setItem('pos_token', res.data.token);
    }

    async function logout() {
        try { await api.post('/logout'); } catch {}
        user.value  = null;
        token.value = null;
        localStorage.removeItem('pos_user');
        localStorage.removeItem('pos_token');
    }

    return { user, token, isLoggedIn, isAdmin, login, logout };
});
