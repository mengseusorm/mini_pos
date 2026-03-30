@extends('layouts.app')

@section('title', 'Stock - Mini POS')
@section('breadcrumb', 'Stock Management')

@section('content')
<div x-data="stockPage()" x-init="load()">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Stock Management</h1>
        <button @click="openModal()" type="button"
                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
            + Stock Movement
        </button>
    </div>

    <!-- Table -->
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th class="px-6 py-3">Item</th>
                    <th class="px-6 py-3">Type</th>
                    <th class="px-6 py-3">Quantity</th>
                    <th class="px-6 py-3">Note</th>
                    <th class="px-6 py-3">User</th>
                    <th class="px-6 py-3">Date</th>
                </tr>
            </thead>
            <tbody>
                <template x-if="loading">
                    <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">Loading...</td></tr>
                </template>
                <template x-if="!loading && movements.length === 0">
                    <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">No stock movements found</td></tr>
                </template>
                <template x-for="m in movements" :key="m.id">
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white" x-text="m.item?.name ?? '-'"></td>
                        <td class="px-6 py-4">
                            <span :class="m.type === 'in' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'"
                                  class="text-xs font-medium px-2.5 py-0.5 rounded-full uppercase" x-text="m.type"></span>
                        </td>
                        <td class="px-6 py-4" x-text="m.quantity"></td>
                        <td class="px-6 py-4" x-text="m.note ?? '-'"></td>
                        <td class="px-6 py-4" x-text="m.user?.name ?? '-'"></td>
                        <td class="px-6 py-4" x-text="new Date(m.created_at).toLocaleString()"></td>
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

    <!-- Add Movement Modal -->
    <div x-show="showModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md mx-4" @click.stop>
            <div class="flex items-center justify-between p-4 border-b dark:border-gray-600">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Add Stock Movement</h3>
                <button @click="showModal = false" class="text-gray-400 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 inline-flex justify-center items-center">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 14 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/></svg>
                </button>
            </div>
            <form @submit.prevent="saveMovement()" class="p-4 space-y-4">
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Item *</label>
                    <select x-model="form.item_id" required
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                        <option value="">Select item...</option>
                        <template x-for="item in items" :key="item.id">
                            <option :value="item.id" x-text="item.name + ' (Stock: ' + item.stock_quantity + ')'"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Type *</label>
                    <select x-model="form.type" required
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                        <option value="in">Stock In</option>
                        <option value="out">Stock Out</option>
                        <option value="adjustment">Adjustment</option>
                    </select>
                </div>
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Quantity *</label>
                    <input type="number" min="1" x-model="form.quantity" required
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                </div>
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Note</label>
                    <input type="text" x-model="form.note"
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                </div>
                <div x-show="formError" class="p-3 text-sm text-red-500 bg-red-50 rounded-lg" x-text="formError"></div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showModal = false"
                            class="py-2.5 px-5 text-sm font-medium text-gray-900 bg-white rounded-lg border border-gray-200 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Cancel</button>
                    <button type="submit" :disabled="saving"
                            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 disabled:opacity-50">
                        <span x-show="saving">Saving...</span><span x-show="!saving">Save</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function stockPage() {
    return {
        movements: [], items: [], meta: {}, page: 1, loading: true,
        showModal: false, saving: false, formError: '',
        form: { item_id: '', type: 'in', quantity: 1, note: '' },

        async load() {
            this.loading = true;
            try {
                const [movRes, itemRes] = await Promise.all([
                    fetch('/api/stock-movements?page=' + this.page + '&per_page=15', { headers: { 'Accept': 'application/json' } }),
                    this.items.length ? Promise.resolve(null) : fetch('/api/items?per_page=200', { headers: { 'Accept': 'application/json' } })
                ]);
                const data = await movRes.json();
                this.movements = data.data ?? data;
                this.meta = data.meta ?? {};
                if (itemRes) { const d = await itemRes.json(); this.items = d.data ?? d; }
            } catch (e) { console.error(e); }
            this.loading = false;
        },

        openModal() { this.form = { item_id: '', type: 'in', quantity: 1, note: '' }; this.formError = ''; this.showModal = true; },

        async saveMovement() {
            this.saving = true; this.formError = '';
            try {
                const res = await fetch('/api/stock-movements', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: JSON.stringify(this.form)
                });
                if (res.ok) { this.showModal = false; this.load(); }
                else { const e = await res.json(); this.formError = e.message ?? 'Error saving'; }
            } catch (e) { this.formError = 'Network error'; }
            this.saving = false;
        },

        prevPage() { if (this.page > 1) { this.page--; this.load(); } },
        nextPage() { if (this.page < this.meta.last_page) { this.page++; this.load(); } }
    }
}
</script>
@endsection
