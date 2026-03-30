@extends('layouts.app')

@section('title', 'Reports - Mini POS')
@section('breadcrumb', 'Reports')

@section('content')
<div x-data="reportsPage()" x-init="loadDaily()">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Reports</h1>
    </div>

    <!-- Tabs -->
    <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
            <li class="me-2">
                <button @click="tab='daily'; loadDaily()"
                        :class="tab==='daily' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-600 hover:border-gray-300'"
                        class="inline-block p-4 border-b-2 rounded-t-lg">Daily</button>
            </li>
            <li class="me-2">
                <button @click="tab='monthly'; loadMonthly()"
                        :class="tab==='monthly' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-600 hover:border-gray-300'"
                        class="inline-block p-4 border-b-2 rounded-t-lg">Monthly</button>
            </li>
            <li class="me-2">
                <button @click="tab='top_products'; loadTopProducts()"
                        :class="tab==='top_products' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-600 hover:border-gray-300'"
                        class="inline-block p-4 border-b-2 rounded-t-lg">Top Products</button>
            </li>
        </ul>
    </div>

    <!-- Date Filters for Daily/Monthly -->
    <div class="flex flex-wrap gap-3 mb-4" x-show="tab !== 'top_products'">
        <div x-show="tab === 'daily'" class="flex items-center gap-2">
            <label class="text-sm text-gray-700 dark:text-gray-300">Date:</label>
            <input type="date" x-model="selectedDate" @change="loadDaily()"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
        </div>
        <div x-show="tab === 'monthly'" class="flex items-center gap-2">
            <label class="text-sm text-gray-700 dark:text-gray-300">Month:</label>
            <input type="month" x-model="selectedMonth" @change="loadMonthly()"
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
        </div>
        <div x-show="tab === 'top_products'" class="flex items-center gap-2">
            <label class="text-sm text-gray-700 dark:text-gray-300">Limit:</label>
            <select x-model="topLimit" @change="loadTopProducts()"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="5">Top 5</option>
                <option value="10">Top 10</option>
                <option value="20">Top 20</option>
            </select>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6" x-show="summary && tab !== 'top_products'">
        <div class="bg-white rounded-lg shadow dark:bg-gray-800 p-6">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Orders</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="summary?.total_orders ?? 0"></p>
        </div>
        <div class="bg-white rounded-lg shadow dark:bg-gray-800 p-6">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Revenue</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="'$' + parseFloat(summary?.total_revenue ?? 0).toFixed(2)"></p>
        </div>
        <div class="bg-white rounded-lg shadow dark:bg-gray-800 p-6">
            <p class="text-sm text-gray-500 dark:text-gray-400">Avg Order Value</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="'$' + parseFloat(summary?.average_order_value ?? 0).toFixed(2)"></p>
        </div>
    </div>

    <!-- Table -->
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg" x-show="!loading">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <!-- Daily -->
                <template x-if="tab === 'daily'">
                    <tr><th class="px-6 py-3">Hour</th><th class="px-6 py-3">Orders</th><th class="px-6 py-3">Revenue</th></tr>
                </template>
                <!-- Monthly -->
                <template x-if="tab === 'monthly'">
                    <tr><th class="px-6 py-3">Date</th><th class="px-6 py-3">Orders</th><th class="px-6 py-3">Revenue</th></tr>
                </template>
                <!-- Top Products -->
                <template x-if="tab === 'top_products'">
                    <tr><th class="px-6 py-3">Rank</th><th class="px-6 py-3">Product</th><th class="px-6 py-3">Qty Sold</th><th class="px-6 py-3">Revenue</th></tr>
                </template>
            </thead>
            <tbody>
                <template x-if="rows.length === 0">
                    <tr><td :colspan="tab === 'top_products' ? 4 : 3" class="px-6 py-8 text-center text-gray-400">No data available</td></tr>
                </template>
                <template x-for="(row, i) in rows" :key="i">
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <template x-if="tab === 'daily'">
                            <td class="px-6 py-4" x-text="row.hour + ':00'"></td>
                        </template>
                        <template x-if="tab === 'monthly'">
                            <td class="px-6 py-4" x-text="row.date"></td>
                        </template>
                        <template x-if="tab === 'top_products'">
                            <td class="px-6 py-4 font-medium" x-text="i + 1"></td>
                        </template>
                        <template x-if="tab === 'top_products'">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white" x-text="row.item_name ?? row.name"></td>
                        </template>
                        <template x-if="tab !== 'top_products'">
                            <td class="px-6 py-4" x-text="row.orders ?? row.total_orders"></td>
                        </template>
                        <template x-if="tab === 'top_products'">
                            <td class="px-6 py-4" x-text="row.total_quantity ?? row.quantity_sold"></td>
                        </template>
                        <td class="px-6 py-4" x-text="'$' + parseFloat(row.revenue ?? row.total_revenue ?? 0).toFixed(2)"></td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <div x-show="loading" class="flex justify-center py-8">
        <svg class="animate-spin w-8 h-8 text-blue-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
    </div>
</div>

<script>
function reportsPage() {
    return {
        tab: 'daily', rows: [], summary: null, loading: false,
        selectedDate: new Date().toISOString().split('T')[0],
        selectedMonth: new Date().toISOString().substring(0, 7),
        topLimit: 10,

        async loadDaily() {
            this.loading = true; this.tab = 'daily';
            try {
                const res = await fetch('/api/reports/daily?date=' + this.selectedDate, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                this.rows = data.hourly ?? data.data ?? [];
                this.summary = data.summary ?? data;
            } catch (e) { console.error(e); }
            this.loading = false;
        },

        async loadMonthly() {
            this.loading = true; this.tab = 'monthly';
            try {
                const [year, month] = this.selectedMonth.split('-');
                const res = await fetch('/api/reports/monthly?year=' + year + '&month=' + month, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                this.rows = data.daily ?? data.data ?? [];
                this.summary = data.summary ?? data;
            } catch (e) { console.error(e); }
            this.loading = false;
        },

        async loadTopProducts() {
            this.loading = true; this.tab = 'top_products';
            try {
                const res = await fetch('/api/reports/top-products?limit=' + this.topLimit, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                this.rows = data.data ?? data;
                this.summary = null;
            } catch (e) { console.error(e); }
            this.loading = false;
        }
    }
}
</script>
@endsection
