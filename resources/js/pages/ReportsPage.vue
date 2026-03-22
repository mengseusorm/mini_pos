<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">Reports</h1>

    <!-- Tab selector -->
    <div class="flex gap-2 mb-6">
      <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
        :class="activeTab === tab.id ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 border'"
        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        {{ tab.label }}
      </button>
    </div>

    <!-- Filters -->
    <div class="flex gap-3 mb-6 flex-wrap">
      <template v-if="activeTab !== 'monthly'">
        <input v-model="filters.date_from" type="date" class="border rounded-lg px-3 py-2 text-sm" />
        <input v-model="filters.date_to" type="date" class="border rounded-lg px-3 py-2 text-sm" />
      </template>
      <template v-else>
        <input v-model="filters.year" type="number" placeholder="Year" class="border rounded-lg px-3 py-2 text-sm w-28" />
      </template>
      <button @click="fetchReport" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">
        Load
      </button>
    </div>

    <div v-if="loading" class="text-gray-400 text-sm">Loading...</div>

    <!-- Daily / Top Products Table -->
    <div v-else class="bg-white rounded-xl shadow overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50">
          <tr class="text-left text-gray-500 border-b">
            <th v-for="col in columns" :key="col" class="px-4 py-3">{{ col }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="reportData.length === 0"><td :colspan="columns.length" class="text-center py-6 text-gray-400">No data.</td></tr>
          <tr v-else v-for="(row, i) in reportData" :key="i" class="border-b last:border-0">
            <td v-for="col in columnKeys" :key="col" class="px-4 py-3">
              {{ col.endsWith('revenue') ? 'Rp ' + fmt(row[col]) : row[col] }}
            </td>
          </tr>
        </tbody>
        <tfoot v-if="reportData.length">
          <tr class="border-t font-semibold bg-gray-50">
            <td class="px-4 py-3">Total</td>
            <td class="px-4 py-3" v-if="activeTab !== 'top'">{{ totalOrders }}</td>
            <td class="px-4 py-3">Rp {{ fmt(totalRevenue) }}</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '@/api';

const tabs = [
  { id: 'daily',   label: 'Daily' },
  { id: 'monthly', label: 'Monthly' },
  { id: 'top',     label: 'Top Products' },
];

const activeTab  = ref('daily');
const loading    = ref(false);
const reportData = ref([]);
const filters    = ref({ date_from: '', date_to: '', year: new Date().getFullYear() });

const columnMap = {
  daily:   { cols: ['Date', 'Orders', 'Revenue'], keys: ['date', 'total_orders', 'revenue'] },
  monthly: { cols: ['Month', 'Orders', 'Revenue'], keys: ['month', 'total_orders', 'revenue'] },
  top:     { cols: ['Item', 'Qty Sold', 'Revenue'], keys: ['name', 'total_qty', 'revenue'] },
};

const columns    = computed(() => columnMap[activeTab.value].cols);
const columnKeys = computed(() => columnMap[activeTab.value].keys);

const totalOrders  = computed(() => reportData.value.reduce((s, r) => s + (r.total_orders || 0), 0));
const totalRevenue = computed(() => reportData.value.reduce((s, r) => s + parseFloat(r.revenue || 0), 0));

async function fetchReport() {
  loading.value = true;
  const endpointMap = {
    daily:   '/reports/daily',
    monthly: '/reports/monthly',
    top:     '/reports/top-products',
  };
  const params = activeTab.value === 'monthly'
    ? { year: filters.value.year }
    : { date_from: filters.value.date_from, date_to: filters.value.date_to };
  const res = await api.get(endpointMap[activeTab.value], { params });
  reportData.value = res.data;
  loading.value    = false;
}

onMounted(fetchReport);

const fmt = (n) => Number(n || 0).toLocaleString('id-ID');
</script>
