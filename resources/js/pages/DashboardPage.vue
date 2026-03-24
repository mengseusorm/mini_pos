<template>
  <div class="p-6 space-y-6">
    <div>
      <h1 class="text-2xl font-bold tracking-tight">Dashboard</h1>
      <p class="text-muted-foreground">Welcome back, {{ auth.user?.name }}</p>
    </div>

    <div v-if="loading" class="text-muted-foreground text-sm">Loading…</div>
    <template v-else>
      <!-- Stats cards -->
      <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
        <StatCard label="Today Sales"    :value="'Rp ' + fmt(data.today_sales)"  :icon="TrendingUp" />
        <StatCard label="Today Orders"   :value="data.today_orders"               :icon="ShoppingCart" />
        <StatCard label="Month Revenue"  :value="'Rp ' + fmt(data.month_sales)"  :icon="DollarSign" />
        <StatCard label="Total Items"    :value="data.total_items"                :icon="Package" />
        <StatCard label="Low Stock"      :value="data.low_stock_items"            :icon="AlertTriangle" description="Items below threshold" />
        <StatCard label="Total Users"    :value="data.total_users"                :icon="Users" />
      </div>

      <!-- Recent orders -->
      <Card>
        <CardHeader>
          <CardTitle>Recent Orders</CardTitle>
        </CardHeader>
        <CardContent class="p-0">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead class="pl-6">#</TableHead>
                <TableHead>Cashier</TableHead>
                <TableHead>Total</TableHead>
                <TableHead>Method</TableHead>
                <TableHead class="pr-6">Date</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="order in data.recent_orders" :key="order.id">
                <TableCell class="pl-6 font-medium">{{ order.id }}</TableCell>
                <TableCell>{{ order.user?.name }}</TableCell>
                <TableCell>Rp {{ fmt(order.total) }}</TableCell>
                <TableCell class="capitalize">{{ order.payment_method }}</TableCell>
                <TableCell class="pr-6 text-muted-foreground">{{ fmtDate(order.created_at) }}</TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { TrendingUp, ShoppingCart, DollarSign, Package, AlertTriangle, Users } from 'lucide-vue-next';
import api from '@/api';
import StatCard from '@/components/StatCard.vue';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from '@/components/ui/table';
import { useAuthStore } from '@/stores/auth';

const auth    = useAuthStore();
const loading = ref(true);
const data    = ref({});

onMounted(async () => {
  const res = await api.get('/dashboard');
  data.value    = res.data;
  loading.value = false;
});

const fmt     = (n) => Number(n || 0).toLocaleString('id-ID');
const fmtDate = (d) => new Date(d).toLocaleDateString('id-ID');
</script>
