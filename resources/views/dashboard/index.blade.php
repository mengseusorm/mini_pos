@extends('layouts.app')

@section('title', 'Dashboard - Mini POS')
@section('breadcrumb', 'Dashboard')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
    <p class="text-gray-500 dark:text-gray-400">Welcome back, {{ auth()->user()->name }}!</p>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6" id="dashboard-stats">
    <!-- Cards will be populated via Alpine.js + API -->
    <div x-data="dashboardStats()" x-init="load()" class="contents">
        <template x-if="loading">
            <template x-for="i in 4" :key="i">
                <div class="bg-white rounded-lg shadow dark:bg-gray-800 p-6 animate-pulse">
                    <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 w-1/2 mb-4"></div>
                    <div class="h-8 bg-gray-200 rounded dark:bg-gray-700 w-1/3"></div>
                </div>
            </template>
        </template>

        <template x-if="!loading">
            <div class="bg-white rounded-lg shadow dark:bg-gray-800 p-6">
                <div class="flex items-center">
                    <div class="inline-flex items-center justify-center p-3 bg-blue-100 rounded-full dark:bg-blue-900 me-4">
                        <svg class="w-6 h-6 text-blue-700 dark:text-blue-300" fill="currentColor" viewBox="0 0 20 20"><path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.077 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.077-2.354-1.253V5z" clip-rule="evenodd"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Today's Revenue</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="'$' + (stats.today_revenue ?? 0).toLocaleString()"></p>
                    </div>
                </div>
            </div>
        </template>

        <template x-if="!loading">
            <div class="bg-white rounded-lg shadow dark:bg-gray-800 p-6">
                <div class="flex items-center">
                    <div class="inline-flex items-center justify-center p-3 bg-green-100 rounded-full dark:bg-green-900 me-4">
                        <svg class="w-6 h-6 text-green-700 dark:text-green-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Today's Orders</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.today_orders ?? 0"></p>
                    </div>
                </div>
            </div>
        </template>

        <template x-if="!loading">
            <div class="bg-white rounded-lg shadow dark:bg-gray-800 p-6">
                <div class="flex items-center">
                    <div class="inline-flex items-center justify-center p-3 bg-yellow-100 rounded-full dark:bg-yellow-900 me-4">
                        <svg class="w-6 h-6 text-yellow-700 dark:text-yellow-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm2 10a1 1 0 10-2 0v3a1 1 0 102 0v-3zm2-3a1 1 0 011 1v5a1 1 0 11-2 0v-5a1 1 0 011-1zm4-1a1 1 0 10-2 0v7a1 1 0 102 0V8z" clip-rule="evenodd"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Items</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.total_items ?? 0"></p>
                    </div>
                </div>
            </div>
        </template>

        <template x-if="!loading">
            <div class="bg-white rounded-lg shadow dark:bg-gray-800 p-6">
                <div class="flex items-center">
                    <div class="inline-flex items-center justify-center p-3 bg-purple-100 rounded-full dark:bg-purple-900 me-4">
                        <svg class="w-6 h-6 text-purple-700 dark:text-purple-300" fill="currentColor" viewBox="0 0 20 20"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Customers</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.total_customers ?? 0"></p>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

<!-- Recent Orders -->
<div class="bg-white shadow rounded-lg dark:bg-gray-800 p-6" x-data="recentOrders()" x-init="load()">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Orders</h2>
        <a href="{{ route('orders.index') }}" class="text-sm font-medium text-blue-600 hover:underline dark:text-blue-500">View all</a>
    </div>
    <div class="relative overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">Order #</th>
                    <th scope="col" class="px-6 py-3">Items</th>
                    <th scope="col" class="px-6 py-3">Total</th>
                    <th scope="col" class="px-6 py-3">Status</th>
                    <th scope="col" class="px-6 py-3">Date</th>
                </tr>
            </thead>
            <tbody>
                <template x-if="loading">
                    <tr x-for="i in 5" :key="i">
                        <td colspan="5" class="px-6 py-4">
                            <div class="h-4 bg-gray-200 rounded dark:bg-gray-700 animate-pulse"></div>
                        </td>
                    </tr>
                </template>
                <template x-if="!loading && orders.length === 0">
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-400">No recent orders</td>
                    </tr>
                </template>
                <template x-for="order in orders" :key="order.id">
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white" x-text="'#' + order.id"></td>
                        <td class="px-6 py-4" x-text="order.order_items_count ?? '-'"></td>
                        <td class="px-6 py-4" x-text="'$' + parseFloat(order.total_amount).toFixed(2)"></td>
                        <td class="px-6 py-4">
                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full dark:bg-green-900 dark:text-green-300" x-text="order.status"></span>
                        </td>
                        <td class="px-6 py-4" x-text="new Date(order.created_at).toLocaleDateString()"></td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>

<script>
function dashboardStats() {
    return {
        stats: {},
        loading: true,
        async load() {
            try {
                const res = await fetch('/api/dashboard', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (res.ok) this.stats = await res.json();
            } catch (e) { console.error(e); }
            this.loading = false;
        }
    }
}

function recentOrders() {
    return {
        orders: [],
        loading: true,
        async load() {
            try {
                const res = await fetch('/api/orders?per_page=5', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (res.ok) {
                    const data = await res.json();
                    this.orders = data.data ?? data;
                }
            } catch (e) { console.error(e); }
            this.loading = false;
        }
    }
}
</script>
@endsection
