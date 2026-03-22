<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Users</h1>
      <button @click="openModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">
        + Add User
      </button>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50">
          <tr class="text-left text-gray-500 border-b">
            <th class="px-4 py-3">#</th>
            <th class="px-4 py-3">Name</th>
            <th class="px-4 py-3">Email</th>
            <th class="px-4 py-3">Role</th>
            <th class="px-4 py-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="5" class="text-center py-6 text-gray-400">Loading...</td></tr>
          <tr v-else v-for="u in users" :key="u.id" class="border-b last:border-0">
            <td class="px-4 py-3">{{ u.id }}</td>
            <td class="px-4 py-3 font-medium">{{ u.name }}</td>
            <td class="px-4 py-3">{{ u.email }}</td>
            <td class="px-4 py-3">
              <span :class="u.role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'"
                class="px-2 py-0.5 rounded-full text-xs capitalize">{{ u.role }}</span>
            </td>
            <td class="px-4 py-3 flex gap-2">
              <button @click="openModal(u)" class="text-blue-600 hover:underline text-xs">Edit</button>
              <button @click="deleteUser(u)" class="text-red-500 hover:underline text-xs">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 w-full max-w-sm">
        <h2 class="font-bold text-lg mb-4">{{ editing ? 'Edit' : 'Add' }} User</h2>
        <form @submit.prevent="saveUser" class="space-y-3">
          <div>
            <label class="block text-sm font-medium mb-1">Name</label>
            <input v-model="form.name" required class="w-full border rounded-lg px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Email</label>
            <input v-model="form.email" type="email" required class="w-full border rounded-lg px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Password {{ editing ? '(leave blank to keep)' : '' }}</label>
            <input v-model="form.password" type="password" :required="!editing"
              class="w-full border rounded-lg px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Role</label>
            <select v-model="form.role" class="w-full border rounded-lg px-3 py-2 text-sm">
              <option value="cashier">Cashier</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <p v-if="formError" class="text-red-500 text-sm">{{ formError }}</p>
          <div class="flex gap-2 pt-2">
            <button type="button" @click="showModal = false" class="flex-1 border rounded-lg py-2 text-sm">Cancel</button>
            <button type="submit" class="flex-1 bg-blue-600 text-white rounded-lg py-2 text-sm">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '@/api';

const users     = ref([]);
const loading   = ref(true);
const showModal = ref(false);
const editing   = ref(null);
const formError = ref('');
const form      = ref({ name: '', email: '', password: '', role: 'cashier' });

async function fetchUsers() {
  loading.value = true;
  const res = await api.get('/users');
  users.value   = res.data;
  loading.value = false;
}

onMounted(fetchUsers);

function openModal(user = null) {
  editing.value   = user;
  formError.value = '';
  form.value      = user
    ? { name: user.name, email: user.email, password: '', role: user.role }
    : { name: '', email: '', password: '', role: 'cashier' };
  showModal.value = true;
}

async function saveUser() {
  formError.value = '';
  const payload = { ...form.value };
  if (editing.value && !payload.password) delete payload.password;
  try {
    if (editing.value) {
      await api.put(`/users/${editing.value.id}`, payload);
    } else {
      await api.post('/users', payload);
    }
    showModal.value = false;
    fetchUsers();
  } catch (e) {
    formError.value = Object.values(e.response?.data?.errors || {}).flat().join(' ') || 'Save failed.';
  }
}

async function deleteUser(user) {
  if (!confirm(`Delete user "${user.name}"?`)) return;
  await api.delete(`/users/${user.id}`);
  fetchUsers();
}
</script>
