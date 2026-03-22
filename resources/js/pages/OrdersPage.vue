<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">Orders</h1>

    <!-- Filters -->
    <div class="flex gap-3 mb-4 flex-wrap">
      <input v-model="filters.date_from" type="date"
        class="border border-gray-300 rounded-lg px-3 py-2 text-sm" />
      <input v-model="filters.date_to" type="date"
        class="border border-gray-300 rounded-lg px-3 py-2 text-sm" />
      <select v-model="filters.payment_method"
        class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        <option value="">All Methods</option>
        <option value="cash">Cash</option>
        <option value="card">Card</option>
      </select>
      <button @click="fetchOrders"
        class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">Filter</button>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50">
          <tr class="text-left text-gray-500 border-b">
            <th class="px-4 py-3">#</th>
            <th class="px-4 py-3">Cashier</th>
            <th class="px-4 py-3">Items</th>
            <th class="px-4 py-3">Total</th>
            <th class="px-4 py-3">Method</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Date</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="7" class="text-center py-6 text-gray-400">Loading...</td></tr>
          <tr v-else-if="orders.length === 0"><td colspan="7" class="text-center py-6 text-gray-400">No orders found.</td></tr>
          <tr v-else v-for="order in orders" :key="order.id"
            class="border-b last:border-0 hover:bg-gray-50 cursor-pointer"
            @click="selected = order">
            <td class="px-4 py-3">{{ order.id }}</td>
            <td class="px-4 py-3">{{ order.user?.name }}</td>
            <td class="px-4 py-3">{{ order.items?.length }}</td>
            <td class="px-4 py-3">Rp {{ fmt(order.total) }}</td>
            <td class="px-4 py-3 capitalize">{{ order.payment_method }}</td>
            <td class="px-4 py-3">
              <span :class="statusClass(order.status)" class="px-2 py-0.5 rounded-full text-xs">
                {{ order.status }}
              </span>
            </td>
            <td class="px-4 py-3">{{ fmtDate(order.created_at) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="pagination" class="flex justify-between items-center mt-4 text-sm">
      <span class="text-gray-500">Total: {{ pagination.total }}</span>
      <div class="flex gap-2">
        <button :disabled="pagination.current_page <= 1" @click="page--; fetchOrders()"
          class="px-3 py-1 border rounded disabled:opacity-40">Prev</button>
        <span>{{ pagination.current_page }} / {{ pagination.last_page }}</span>
        <button :disabled="pagination.current_page >= pagination.last_page" @click="page++; fetchOrders()"
          class="px-3 py-1 border rounded disabled:opacity-40">Next</button>
      </div>
    </div>

    <!-- Order detail modal -->
    <div v-if="selected" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
        <h2 class="font-bold text-lg mb-4">Order #{{ selected.id }}</h2>
        <table class="w-full text-sm mb-4">
          <thead><tr class="border-b"><th class="text-left pb-1">Item</th><th class="text-right pb-1">Qty</th><th class="text-right pb-1">Price</th></tr></thead>
          <tbody>
            <tr v-for="i in selected.items" :key="i.id" class="border-b">
              <td class="py-1">{{ i.item?.name }}</td>
              <td class="py-1 text-right">{{ i.quantity }}</td>
              <td class="py-1 text-right">Rp {{ fmt(i.subtotal) }}</td>
            </tr>
          </tbody>
        </table>
        <div class="space-y-1 text-sm">
          <div class="flex justify-between"><span>Subtotal</span><span>Rp {{ fmt(selected.subtotal) }}</span></div>
          <div v-if="selected.discount > 0" class="flex justify-between text-red-500"><span>Discount</span><span>- Rp {{ fmt(selected.discount) }}</span></div>
          <div v-if="selected.tax > 0" class="flex justify-between"><span>Tax</span><span>Rp {{ fmt(selected.tax) }}</span></div>
          <div class="flex justify-between font-bold"><span>Total</span><span>Rp {{ fmt(selected.total) }}</span></div>
        </div>
        <button @click="selected = null" class="mt-4 w-full bg-gray-100 hover:bg-gray-200 rounded-lg py-2 text-sm">Close</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '@/api';

const orders     = ref([]);
const loading    = ref(true);
const pagination = ref(null);
const selected   = ref(null);
const page       = ref(1);
const filters    = ref({ date_from: '', date_to: '', payment_method: '' });

async function fetchOrders() {
  loading.value = true;
  const params  = { page: page.value, ...filters.value };
  const res = await api.get('/orders', { params });
  orders.value     = res.data.data;
  pagination.value = res.data;
  loading.value    = false;
}

onMounted(fetchOrders);

const fmt = (n) => Number(n || 0).toLocaleString('id-ID');
const fmtDate = (d) => new Date(d).toLocaleDateString('id-ID');
const statusClass = (s) => ({
  completed: 'bg-green-100 text-green-700',
  pending:   'bg-yellow-100 text-yellow-700',
  cancelled: 'bg-red-100 text-red-700',
}[s] || '');
</script>
