<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">Dashboard</h1>

    <div v-if="loading" class="text-gray-500">Loading...</div>
    <template v-else>
      <!-- Stats cards -->
      <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        <StatCard label="Today Sales" :value="'Rp ' + fmt(data.today_sales)" color="blue" />
        <StatCard label="Today Orders" :value="data.today_orders" color="green" />
        <StatCard label="Month Revenue" :value="'Rp ' + fmt(data.month_sales)" color="purple" />
        <StatCard label="Total Items" :value="data.total_items" color="yellow" />
        <StatCard label="Low Stock" :value="data.low_stock_items" color="red" />
        <StatCard label="Total Users" :value="data.total_users" color="gray" />
      </div>

      <!-- Recent orders -->
      <div class="bg-white rounded-xl shadow p-6">
        <h2 class="font-semibold text-gray-700 mb-4">Recent Orders</h2>
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-gray-500 border-b">
              <th class="pb-2">#</th>
              <th class="pb-2">Cashier</th>
              <th class="pb-2">Total</th>
              <th class="pb-2">Method</th>
              <th class="pb-2">Date</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="order in data.recent_orders" :key="order.id" class="border-b last:border-0">
              <td class="py-2">{{ order.id }}</td>
              <td class="py-2">{{ order.user?.name }}</td>
              <td class="py-2">Rp {{ fmt(order.total) }}</td>
              <td class="py-2 capitalize">{{ order.payment_method }}</td>
              <td class="py-2">{{ fmtDate(order.created_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '@/api';
import StatCard from '@/components/StatCard.vue';

const loading = ref(true);
const data    = ref({});

onMounted(async () => {
  const res = await api.get('/dashboard');
  data.value = res.data;
  loading.value = false;
});

const fmt     = (n) => Number(n || 0).toLocaleString('id-ID');
const fmtDate = (d) => new Date(d).toLocaleDateString('id-ID');
</script>
