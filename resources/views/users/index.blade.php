@extends('layouts.app')

@section('title', 'Users - Mini POS')
@section('breadcrumb', 'Users')

@section('content')
<div x-data="usersPage()" x-init="load()">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Users</h1>
        <button @click="openCreateModal()" type="button"
                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
            + Add User
        </button>
    </div>

    <!-- Table -->
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3">Role</th>
                    <th class="px-6 py-3">Created</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                <template x-if="loading">
                    <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">Loading...</td></tr>
                </template>
                <template x-if="!loading && users.length === 0">
                    <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">No users found</td></tr>
                </template>
                <template x-for="user in users" :key="user.id">
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-bold" x-text="user.name.charAt(0).toUpperCase()"></div>
                            <span x-text="user.name"></span>
                        </td>
                        <td class="px-6 py-4" x-text="user.email"></td>
                        <td class="px-6 py-4">
                            <span :class="user.role === 'admin' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300'"
                                  class="text-xs font-medium px-2.5 py-0.5 rounded-full capitalize" x-text="user.role"></span>
                        </td>
                        <td class="px-6 py-4" x-text="new Date(user.created_at).toLocaleDateString()"></td>
                        <td class="px-6 py-4 flex gap-2">
                            <button @click="openEditModal(user)" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Edit</button>
                            <button @click="deleteUser(user.id)" class="font-medium text-red-600 dark:text-red-500 hover:underline">Delete</button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Create/Edit Modal -->
    <div x-show="showModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md mx-4" @click.stop>
            <div class="flex items-center justify-between p-4 border-b dark:border-gray-600">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white" x-text="editingId ? 'Edit User' : 'Add User'"></h3>
                <button @click="showModal = false" class="text-gray-400 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 inline-flex justify-center items-center">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 14 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/></svg>
                </button>
            </div>
            <form @submit.prevent="saveUser()" class="p-4 space-y-4">
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name *</label>
                    <input type="text" x-model="form.name" required
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                </div>
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email *</label>
                    <input type="email" x-model="form.email" required
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                </div>
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Password <span x-text="editingId ? '(leave blank to keep)' : '*'"></span></label>
                    <input type="password" x-model="form.password" :required="!editingId"
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                </div>
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Role *</label>
                    <select x-model="form.role" required
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                        <option value="cashier">Cashier</option>
                        <option value="admin">Admin</option>
                    </select>
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
function usersPage() {
    return {
        users: [], loading: true, showModal: false, editingId: null, saving: false, formError: '',
        form: { name: '', email: '', password: '', role: 'cashier' },

        async load() {
            this.loading = true;
            try {
                const res = await fetch('/api/users', { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                this.users = data.data ?? data;
            } catch (e) { console.error(e); }
            this.loading = false;
        },

        openCreateModal() {
            this.editingId = null;
            this.form = { name: '', email: '', password: '', role: 'cashier' };
            this.formError = ''; this.showModal = true;
        },

        openEditModal(user) {
            this.editingId = user.id;
            this.form = { name: user.name, email: user.email, password: '', role: user.role };
            this.formError = ''; this.showModal = true;
        },

        async saveUser() {
            this.saving = true; this.formError = '';
            try {
                const payload = { ...this.form };
                if (!payload.password) delete payload.password;
                const url = this.editingId ? '/api/users/' + this.editingId : '/api/users';
                const method = this.editingId ? 'PUT' : 'POST';
                const res = await fetch(url, {
                    method,
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: JSON.stringify(payload)
                });
                if (res.ok) { this.showModal = false; this.load(); }
                else { const e = await res.json(); this.formError = e.message ?? 'Error saving user'; }
            } catch (e) { this.formError = 'Network error'; }
            this.saving = false;
        },

        async deleteUser(id) {
            if (!confirm('Delete this user?')) return;
            await fetch('/api/users/' + id, { method: 'DELETE', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } });
            this.load();
        }
    }
}
</script>
@endsection
