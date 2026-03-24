<template>
  <div class="p-6 space-y-6">
    <div>
      <h1 class="text-2xl font-bold tracking-tight">Reports</h1>
      <p class="text-muted-foreground text-sm">Analyze sales performance</p>
    </div>

    <!-- Tab selector -->
    <div class="flex gap-1 rounded-lg border bg-muted p-1 w-fit">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        @click="activeTab = tab.id"
        :class="[
          'inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-medium transition-colors',
          activeTab === tab.id
            ? 'bg-background text-foreground shadow-sm'
            : 'text-muted-foreground hover:text-foreground'
        ]"
      >
        <component :is="tab.icon" class="h-3.5 w-3.5" />
        {{ tab.label }}
      </button>
    </div>

    <!-- Filters -->
    <div class="flex gap-3 flex-wrap items-end">
      <template v-if="activeTab !== 'monthly'">
        <div class="space-y-1">
          <Label class="text-xs">From</Label>
          <Input v-model="filters.date_from" type="date" class="h-9" />
        </div>
        <div class="space-y-1">
          <Label class="text-xs">To</Label>
          <Input v-model="filters.date_to" type="date" class="h-9" />
        </div>
      </template>
      <template v-else>
        <div class="space-y-1">
          <Label class="text-xs">Year</Label>
          <Input v-model="filters.year" type="number" placeholder="Year" class="h-9 w-28" />
        </div>
      </template>
      <Button size="sm" @click="fetchReport">
        <RefreshCw class="mr-2 h-4 w-4" /> Load
      </Button>
    </div>

    <div v-if="loading" class="text-muted-foreground text-sm">Loading…</div>

    <Card v-else>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead v-for="col in columns" :key="col" class="first:pl-6 last:pr-6">{{ col }}</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-if="reportData.length === 0">
            <TableCell :colspan="columns.length" class="text-center py-8 text-muted-foreground">No data.</TableCell>
          </TableRow>
          <TableRow v-else v-for="(row, i) in reportData" :key="i">
            <TableCell
              v-for="(col, idx) in columnKeys"
              :key="col"
              :class="[idx === 0 ? 'pl-6 font-medium' : '', idx === columnKeys.length - 1 ? 'pr-6' : '']"
            >
              {{ col.endsWith('revenue') ? 'Rp ' + fmt(row[col]) : row[col] }}
            </TableCell>
          </TableRow>
        </TableBody>
        <TableFooter v-if="reportData.length">
          <TableRow>
            <TableCell class="pl-6 font-semibold">Total</TableCell>
            <TableCell v-if="activeTab !== 'top'" class="font-semibold">{{ totalOrders }}</TableCell>
            <TableCell class="pr-6 font-semibold">Rp {{ fmt(totalRevenue) }}</TableCell>
          </TableRow>
        </TableFooter>
      </Table>
    </Card>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { CalendarDays, CalendarRange, Trophy, RefreshCw } from 'lucide-vue-next';
import api from '@/api';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card } from '@/components/ui/card';
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell, TableFooter } from '@/components/ui/table';

const tabs = [
  { id: 'daily',   label: 'Daily',        icon: CalendarDays },
  { id: 'monthly', label: 'Monthly',      icon: CalendarRange },
  { id: 'top',     label: 'Top Products', icon: Trophy },
];

const activeTab  = ref('daily');
const loading    = ref(false);
const reportData = ref([]);
const filters    = ref({ date_from: '', date_to: '', year: new Date().getFullYear() });

const columnMap = {
  daily:   { cols: ['Date',  'Orders', 'Revenue'], keys: ['date',  'total_orders', 'revenue'] },
  monthly: { cols: ['Month', 'Orders', 'Revenue'], keys: ['month', 'total_orders', 'revenue'] },
  top:     { cols: ['Item',  'Qty Sold', 'Revenue'], keys: ['name', 'total_qty', 'revenue'] },
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
  const res    = await api.get(endpointMap[activeTab.value], { params });
  reportData.value = res.data;
  loading.value    = false;
}

onMounted(fetchReport);

const fmt = (n) => Number(n || 0).toLocaleString('id-ID');
</script>
