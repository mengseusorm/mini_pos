<template>
  <div class="flex h-screen bg-gray-100">
    <!-- Sidebar -->
    <aside class="w-64 bg-gray-900 text-white flex flex-col">
      <div class="p-4 text-xl font-bold border-b border-gray-700">
        🏪 Mini POS
      </div>
      <nav class="flex-1 p-4 space-y-1">
        <RouterLink v-for="item in navItems" :key="item.to"
          :to="item.to"
          class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-700 transition-colors"
          active-class="bg-gray-700 font-semibold"
        >
          <span>{{ item.icon }}</span>
          <span>{{ item.label }}</span>
        </RouterLink>
      </nav>
      <div class="p-4 border-t border-gray-700">
        <div class="text-sm text-gray-400 mb-2">{{ auth.user?.name }}</div>
        <div class="text-xs text-gray-500 capitalize mb-3">{{ auth.user?.role }}</div>
        <button @click="handleLogout"
          class="w-full text-left text-sm text-red-400 hover:text-red-300 transition-colors">
          🚪 Logout
        </button>
      </div>
    </aside>

    <!-- Main content -->
    <main class="flex-1 overflow-y-auto">
      <RouterView />
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { RouterLink, RouterView, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const router = useRouter();

const allNavItems = [
  { to: '/dashboard', icon: '📊', label: 'Dashboard' },
  { to: '/pos',       icon: '🛒', label: 'POS' },
  { to: '/orders',    icon: '📄', label: 'Orders' },
  { to: '/items',     icon: '📦', label: 'Items' },
  { to: '/categories',icon: '🏷️',  label: 'Categories' },
  { to: '/stock',     icon: '📋', label: 'Stock' },
  { to: '/reports',   icon: '📈', label: 'Reports' },
  { to: '/users',     icon: '👥', label: 'Users', adminOnly: true },
];

const navItems = computed(() =>
  allNavItems.filter(i => !i.adminOnly || auth.isAdmin)
);

async function handleLogout() {
  await auth.logout();
  router.push('/login');
}
</script>
