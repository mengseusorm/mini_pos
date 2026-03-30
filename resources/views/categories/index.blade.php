@extends('layouts.app')

@section('title', 'Categories - Mini POS')
@section('breadcrumb', 'Categories')

@section('content')
<div x-data="categoriesPage()" x-init="load()">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Categories</h1>
        <button @click="openCreateModal()" type="button"
                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
            + Add Category
        </button>
    </div>

    <!-- Search -->
    <div class="mb-4">
        <input type="text" x-model="search" @input.debounce.400ms="load()"
               placeholder="Search categories..."
               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full md:w-64 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
    </div>

    <!-- Table -->
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">Name</th>
                    <th scope="col" class="px-6 py-3">Description</th>
                    <th scope="col" class="px-6 py-3">Items</th>
                    <th scope="col" class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                <template x-if="loading">
                    <tr><td colspan="4" class="px-6 py-8 text-center"><div class="inline-flex items-center gap-2 text-gray-500"><svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg> Loading...</div></td></tr>
                </template>
                <template x-if="!loading && categories.length === 0">
                    <tr><td colspan="4" class="px-6 py-8 text-center text-gray-400">No categories found</td></tr>
                </template>
                <template x-for="cat in categories" :key="cat.id">
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white" x-text="cat.name"></td>
                        <td class="px-6 py-4" x-text="cat.description ?? '-'"></td>
                        <td class="px-6 py-4" x-text="cat.items_count ?? 0"></td>
                        <td class="px-6 py-4 flex gap-2">
                            <button @click="openEditModal(cat)" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Edit</button>
                            <button @click="deleteCategory(cat.id)" class="font-medium text-red-600 dark:text-red-500 hover:underline">Delete</button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="flex items-center justify-between mt-4" x-show="meta.last_page > 1">
        <p class="text-sm text-gray-700 dark:text-gray-400">
            Showing <span x-text="meta.from"></span> to <span x-text="meta.to"></span> of <span x-text="meta.total"></span> results
        </p>
        <div class="flex gap-2">
            <button @click="prevPage()" :disabled="meta.current_page <= 1"
                    class="px-3 py-1 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 disabled:opacity-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400">
                Previous
            </button>
            <button @click="nextPage()" :disabled="meta.current_page >= meta.last_page"
                    class="px-3 py-1 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 disabled:opacity-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400">
                Next
            </button>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div x-show="showModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md mx-4" @click.stop>
            <div class="flex items-center justify-between p-4 border-b dark:border-gray-600">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white" x-text="editingId ? 'Edit Category' : 'Add Category'"></h3>
                <button @click="closeModal()" class="text-gray-400 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 14 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/></svg>
                </button>
            </div>
            <form @submit.prevent="saveCategory()" class="p-4 space-y-4">
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name *</label>
                    <input type="text" x-model="form.name" required
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                </div>
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Description</label>
                    <textarea x-model="form.description" rows="3"
                              class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"></textarea>
                </div>
                <div x-show="formError" class="p-3 text-sm text-red-500 bg-red-50 rounded-lg dark:bg-gray-700 dark:text-red-400" x-text="formError"></div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="closeModal()"
                            class="py-2.5 px-5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">
                        Cancel
                    </button>
                    <button type="submit" :disabled="saving"
                            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 disabled:opacity-50">
                        <span x-show="saving">Saving...</span>
                        <span x-show="!saving">Save</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function categoriesPage() {
    return {
        categories: [], meta: {}, search: '', page: 1, loading: true,
        showModal: false, editingId: null, saving: false, formError: '',
        form: { name: '', description: '' },

        async load() {
            this.loading = true;
            try {
                const params = new URLSearchParams({ page: this.page, per_page: 15 });
                if (this.search) params.append('search', this.search);
                const res = await fetch('/api/categories?' + params, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                this.categories = data.data ?? data;
                this.meta = data.meta ?? {};
            } catch (e) { console.error(e); }
            this.loading = false;
        },

        openCreateModal() {
            this.editingId = null;
            this.form = { name: '', description: '' };
            this.formError = '';
            this.showModal = true;
        },

        openEditModal(cat) {
            this.editingId = cat.id;
            this.form = { name: cat.name, description: cat.description ?? '' };
            this.formError = '';
            this.showModal = true;
        },

        closeModal() { this.showModal = false; },

        async saveCategory() {
            this.saving = true; this.formError = '';
            try {
                const url = this.editingId ? '/api/categories/' + this.editingId : '/api/categories';
                const method = this.editingId ? 'PUT' : 'POST';
                const res = await fetch(url, {
                    method,
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: JSON.stringify(this.form)
                });
                if (res.ok) { this.closeModal(); this.load(); }
                else { const e = await res.json(); this.formError = e.message ?? 'Error saving category'; }
            } catch (e) { this.formError = 'Network error'; }
            this.saving = false;
        },

        async deleteCategory(id) {
            if (!confirm('Delete this category?')) return;
            await fetch('/api/categories/' + id, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
            });
            this.load();
        },

        prevPage() { if (this.page > 1) { this.page--; this.load(); } },
        nextPage() { if (this.page < this.meta.last_page) { this.page++; this.load(); } }
    }
}
</script>
@endsection
