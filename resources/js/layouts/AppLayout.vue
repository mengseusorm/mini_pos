<template>
  <div class="flex h-screen bg-background">
    <!-- Sidebar -->
    <aside class="w-64 bg-sidebar text-sidebar-foreground flex flex-col border-r border-sidebar-border">
      <!-- Logo -->
      <div class="flex items-center gap-2 p-5 border-b border-sidebar-border">
        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-sidebar-primary text-sidebar-primary-foreground text-sm font-bold">
          P
        </div>
        <span class="text-base font-semibold tracking-tight">Mini POS</span>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 overflow-y-auto p-3 space-y-0.5">
        <RouterLink
          v-for="item in navItems"
          :key="item.to"
          :to="item.to"
          class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors hover:bg-sidebar-accent hover:text-sidebar-accent-foreground text-sidebar-foreground/70"
          active-class="bg-sidebar-accent text-sidebar-accent-foreground"
        >
          <component :is="item.icon" class="h-4 w-4 shrink-0" />
          <span>{{ item.label }}</span>
        </RouterLink>
      </nav>

      <!-- User section -->
      <div class="p-3 border-t border-sidebar-border">
        <div class="flex items-center gap-3 px-3 py-2 rounded-lg mb-1">
          <div class="flex h-8 w-8 items-center justify-center rounded-full bg-sidebar-accent text-sidebar-accent-foreground text-xs font-semibold shrink-0">
            {{ auth.user?.name?.charAt(0)?.toUpperCase() }}
          </div>
          <div class="min-w-0 flex-1">
            <p class="text-sm font-medium truncate">{{ auth.user?.name }}</p>
            <p class="text-xs text-sidebar-foreground/50 capitalize">{{ auth.user?.role }}</p>
          </div>
        </div>
        <Button
          variant="ghost"
          class="w-full justify-start gap-3 text-sidebar-foreground/70 hover:text-destructive hover:bg-destructive/10 h-9 px-3"
          @click="handleLogout"
        >
          <LogOut class="h-4 w-4" />
          Logout
        </Button>
      </div>
    </aside>

    <!-- Main content -->
    <main class="flex-1 overflow-y-auto bg-muted/30">
      <RouterView />
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { RouterLink, RouterView, useRouter } from 'vue-router';
import {
  LayoutDashboard, ShoppingCart, FileText, Package,
  Tag, ClipboardList, BarChart3, Users, LogOut,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { useAuthStore } from '@/stores/auth';

const auth   = useAuthStore();
const router = useRouter();

const allNavItems = [
  { to: '/dashboard',  icon: LayoutDashboard, label: 'Dashboard' },
  { to: '/pos',        icon: ShoppingCart,    label: 'POS' },
  { to: '/orders',     icon: FileText,        label: 'Orders' },
  { to: '/items',      icon: Package,         label: 'Items' },
  { to: '/categories', icon: Tag,             label: 'Categories' },
  { to: '/stock',      icon: ClipboardList,   label: 'Stock' },
  { to: '/reports',    icon: BarChart3,       label: 'Reports' },
  { to: '/users',      icon: Users,           label: 'Users', adminOnly: true },
];

const navItems = computed(() =>
  allNavItems.filter(i => !i.adminOnly || auth.isAdmin)
);

async function handleLogout() {
  await auth.logout();
  router.push('/login');
}
</script>
