@extends('layouts.app')

@section('title', 'Orders - Mini POS')
@section('breadcrumb', 'Orders')

@section('content')
<div x-data="ordersPage()" x-init="load()">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Orders</h1>
    </div>

    <!-- Filters -->
    <div class="flex flex-wrap gap-3 mb-4">
        <input type="date" x-model="dateFrom" @change="page=1; load()"
               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
        <input type="date" x-model="dateTo" @change="page=1; load()"
               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
    </div>

    <!-- Table -->
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th class="px-6 py-3">Order #</th>
                    <th class="px-6 py-3">Cashier</th>
                    <th class="px-6 py-3">Items</th>
                    <th class="px-6 py-3">Total</th>
                    <th class="px-6 py-3">Payment</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Date</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                <template x-if="loading">
                    <tr><td colspan="8" class="px-6 py-8 text-center text-gray-400">Loading...</td></tr>
                </template>
                <template x-if="!loading && orders.length === 0">
                    <tr><td colspan="8" class="px-6 py-8 text-center text-gray-400">No orders found</td></tr>
                </template>
                <template x-for="order in orders" :key="order.id">
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white" x-text="'#' + order.id"></td>
                        <td class="px-6 py-4" x-text="order.user?.name ?? '-'"></td>
                        <td class="px-6 py-4" x-text="order.order_items?.length ?? '-'"></td>
                        <td class="px-6 py-4" x-text="'$' + parseFloat(order.total_amount).toFixed(2)"></td>
                        <td class="px-6 py-4" x-text="order.payment?.method ?? '-'"></td>
                        <td class="px-6 py-4">
                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full dark:bg-green-900 dark:text-green-300" x-text="order.status"></span>
                        </td>
                        <td class="px-6 py-4" x-text="new Date(order.created_at).toLocaleString()"></td>
                        <td class="px-6 py-4">
                            <button @click="viewOrder(order)" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">View</button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="flex items-center justify-between mt-4" x-show="meta.last_page > 1">
        <p class="text-sm text-gray-700 dark:text-gray-400">Page <span x-text="page"></span> of <span x-text="meta.last_page"></span></p>
        <div class="flex gap-2">
            <button @click="prevPage()" :disabled="page <= 1"
                    class="px-3 py-1 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 disabled:opacity-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400">Previous</button>
            <button @click="nextPage()" :disabled="page >= meta.last_page"
                    class="px-3 py-1 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 disabled:opacity-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400">Next</button>
        </div>
    </div>

    <!-- Order Detail Modal -->
    <div x-show="selectedOrder" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-lg mx-4 max-h-screen overflow-y-auto" @click.stop>
            <div class="flex items-center justify-between p-4 border-b dark:border-gray-600">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Order <span x-text="'#' + selectedOrder?.id"></span></h3>
                <button @click="selectedOrder = null" class="text-gray-400 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 inline-flex justify-center items-center">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 14 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/></svg>
                </button>
            </div>
            <div class="p-4" x-show="selectedOrder">
                <table class="w-full text-sm text-left text-gray-500 mb-4">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr><th class="px-4 py-2">Item</th><th class="px-4 py-2">Qty</th><th class="px-4 py-2">Price</th><th class="px-4 py-2">Subtotal</th></tr>
                    </thead>
                    <tbody>
                        <template x-for="item in (selectedOrder?.order_items ?? [])" :key="item.id">
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-2" x-text="item.item?.name ?? item.item_name"></td>
                                <td class="px-4 py-2" x-text="item.quantity"></td>
                                <td class="px-4 py-2" x-text="'$' + parseFloat(item.unit_price).toFixed(2)"></td>
                                <td class="px-4 py-2" x-text="'$' + parseFloat(item.subtotal).toFixed(2)"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <div class="flex justify-between font-semibold text-gray-900 dark:text-white">
                    <span>Total:</span>
                    <span x-text="'$' + parseFloat(selectedOrder?.total_amount ?? 0).toFixed(2)"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function ordersPage() {
    return {
        orders: [], meta: {}, page: 1, loading: true, dateFrom: '', dateTo: '', selectedOrder: null,

        async load() {
            this.loading = true;
            try {
                const params = new URLSearchParams({ page: this.page, per_page: 15 });
                if (this.dateFrom) params.append('date_from', this.dateFrom);
                if (this.dateTo) params.append('date_to', this.dateTo);
                const res = await fetch('/api/orders?' + params, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                this.orders = data.data ?? data;
                this.meta = data.meta ?? {};
            } catch (e) { console.error(e); }
            this.loading = false;
        },

        viewOrder(order) { this.selectedOrder = order; },
        prevPage() { if (this.page > 1) { this.page--; this.load(); } },
        nextPage() { if (this.page < this.meta.last_page) { this.page++; this.load(); } }
    }
}
</script>
@endsection
