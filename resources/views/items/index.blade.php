@extends('layouts.app')

@section('title', 'Items - Mini POS')
@section('breadcrumb', 'Items')

@section('content')
<div x-data="itemsPage()" x-init="load()">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Items</h1>
        <button @click="openCreateModal()" type="button"
                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
            + Add Item
        </button>
    </div>

    <!-- Filters -->
    <div class="flex flex-wrap gap-3 mb-4">
        <input type="text" x-model="search" @input.debounce.400ms="load()"
               placeholder="Search items..."
               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white w-full md:w-64">
        <select x-model="categoryFilter" @change="page=1; load()"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            <option value="">All Categories</option>
            <template x-for="cat in categories" :key="cat.id">
                <option :value="cat.id" x-text="cat.name"></option>
            </template>
        </select>
    </div>

    <!-- Table -->
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">Name</th>
                    <th scope="col" class="px-6 py-3">Category</th>
                    <th scope="col" class="px-6 py-3">Price</th>
                    <th scope="col" class="px-6 py-3">Stock</th>
                    <th scope="col" class="px-6 py-3">Status</th>
                    <th scope="col" class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                <template x-if="loading">
                    <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">Loading...</td></tr>
                </template>
                <template x-if="!loading && items.length === 0">
                    <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">No items found</td></tr>
                </template>
                <template x-for="item in items" :key="item.id">
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white" x-text="item.name"></td>
                        <td class="px-6 py-4" x-text="item.category?.name ?? '-'"></td>
                        <td class="px-6 py-4" x-text="'$' + parseFloat(item.price).toFixed(2)"></td>
                        <td class="px-6 py-4">
                            <span :class="item.stock_quantity <= (item.low_stock_threshold ?? 5) ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'"
                                  class="text-xs font-medium px-2.5 py-0.5 rounded-full" x-text="item.stock_quantity"></span>
                        </td>
                        <td class="px-6 py-4">
                            <span :class="item.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'"
                                  class="text-xs font-medium px-2.5 py-0.5 rounded-full" x-text="item.is_active ? 'Active' : 'Inactive'"></span>
                        </td>
                        <td class="px-6 py-4 flex gap-2">
                            <button @click="openEditModal(item)" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Edit</button>
                            <button @click="deleteItem(item.id)" class="font-medium text-red-600 dark:text-red-500 hover:underline">Delete</button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="flex items-center justify-between mt-4" x-show="meta.last_page > 1">
        <p class="text-sm text-gray-700 dark:text-gray-400">
            Page <span x-text="meta.current_page"></span> of <span x-text="meta.last_page"></span>
        </p>
        <div class="flex gap-2">
            <button @click="prevPage()" :disabled="page <= 1"
                    class="px-3 py-1 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 disabled:opacity-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400">Previous</button>
            <button @click="nextPage()" :disabled="page >= meta.last_page"
                    class="px-3 py-1 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 disabled:opacity-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400">Next</button>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div x-show="showModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-lg mx-4 max-h-screen overflow-y-auto" @click.stop>
            <div class="flex items-center justify-between p-4 border-b dark:border-gray-600">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white" x-text="editingId ? 'Edit Item' : 'Add Item'"></h3>
                <button @click="closeModal()" class="text-gray-400 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 14 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/></svg>
                </button>
            </div>
            <form @submit.prevent="saveItem()" class="p-4 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name *</label>
                        <input type="text" x-model="form.name" required
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Price *</label>
                        <input type="number" step="0.01" min="0" x-model="form.price" required
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Stock Qty</label>
                        <input type="number" min="0" x-model="form.stock_quantity"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                    </div>
                    <div class="col-span-2">
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Category</label>
                        <select x-model="form.category_id"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                            <option value="">No Category</option>
                            <template x-for="cat in categories" :key="cat.id">
                                <option :value="cat.id" x-text="cat.name"></option>
                            </template>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Description</label>
                        <textarea x-model="form.description" rows="2"
                                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"></textarea>
                    </div>
                    <div class="col-span-2 flex items-center">
                        <input id="is_active" type="checkbox" x-model="form.is_active"
                               class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        <label for="is_active" class="ms-2 text-sm font-medium text-gray-900 dark:text-white">Active</label>
                    </div>
                </div>
                <div x-show="formError" class="p-3 text-sm text-red-500 bg-red-50 rounded-lg" x-text="formError"></div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="closeModal()"
                            class="py-2.5 px-5 text-sm font-medium text-gray-900 bg-white rounded-lg border border-gray-200 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Cancel</button>
                    <button type="submit" :disabled="saving"
                            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 disabled:opacity-50">
                        <span x-show="saving">Saving...</span><span x-show="!saving">Save</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function itemsPage() {
    return {
        items: [], categories: [], meta: {}, search: '', categoryFilter: '', page: 1, loading: true,
        showModal: false, editingId: null, saving: false, formError: '',
        form: { name: '', price: '', stock_quantity: 0, category_id: '', description: '', is_active: true },

        async load() {
            this.loading = true;
            try {
                const params = new URLSearchParams({ page: this.page, per_page: 15 });
                if (this.search) params.append('search', this.search);
                if (this.categoryFilter) params.append('category_id', this.categoryFilter);
                const [itemsRes, catsRes] = await Promise.all([
                    fetch('/api/items?' + params, { headers: { 'Accept': 'application/json' } }),
                    this.categories.length ? Promise.resolve(null) : fetch('/api/categories?per_page=100', { headers: { 'Accept': 'application/json' } })
                ]);
                const data = await itemsRes.json();
                this.items = data.data ?? data;
                this.meta = data.meta ?? {};
                if (catsRes) { const cd = await catsRes.json(); this.categories = cd.data ?? cd; }
            } catch (e) { console.error(e); }
            this.loading = false;
        },

        openCreateModal() {
            this.editingId = null;
            this.form = { name: '', price: '', stock_quantity: 0, category_id: '', description: '', is_active: true };
            this.formError = ''; this.showModal = true;
        },

        openEditModal(item) {
            this.editingId = item.id;
            this.form = { name: item.name, price: item.price, stock_quantity: item.stock_quantity, category_id: item.category_id ?? '', description: item.description ?? '', is_active: item.is_active };
            this.formError = ''; this.showModal = true;
        },

        closeModal() { this.showModal = false; },

        async saveItem() {
            this.saving = true; this.formError = '';
            try {
                const url = this.editingId ? '/api/items/' + this.editingId : '/api/items';
                const method = this.editingId ? 'PUT' : 'POST';
                const res = await fetch(url, {
                    method, headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: JSON.stringify(this.form)
                });
                if (res.ok) { this.closeModal(); this.load(); }
                else { const e = await res.json(); this.formError = e.message ?? 'Error saving item'; }
            } catch (e) { this.formError = 'Network error'; }
            this.saving = false;
        },

        async deleteItem(id) {
            if (!confirm('Delete this item?')) return;
            await fetch('/api/items/' + id, { method: 'DELETE', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } });
            this.load();
        },

        prevPage() { if (this.page > 1) { this.page--; this.load(); } },
        nextPage() { if (this.page < this.meta.last_page) { this.page++; this.load(); } }
    }
}
</script>
@endsection
