<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Categories</h1>
      <button @click="openModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">
        + Add Category
      </button>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50">
          <tr class="text-left text-gray-500 border-b">
            <th class="px-4 py-3">#</th>
            <th class="px-4 py-3">Name</th>
            <th class="px-4 py-3">Items</th>
            <th class="px-4 py-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="4" class="text-center py-6 text-gray-400">Loading...</td></tr>
          <tr v-else v-for="cat in categories" :key="cat.id" class="border-b last:border-0">
            <td class="px-4 py-3">{{ cat.id }}</td>
            <td class="px-4 py-3 font-medium">{{ cat.name }}</td>
            <td class="px-4 py-3">{{ cat.items_count }}</td>
            <td class="px-4 py-3 flex gap-2">
              <button @click="openModal(cat)" class="text-blue-600 hover:underline text-xs">Edit</button>
              <button @click="deleteCategory(cat)" class="text-red-500 hover:underline text-xs">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 w-full max-w-sm">
        <h2 class="font-bold text-lg mb-4">{{ editing ? 'Edit' : 'Add' }} Category</h2>
        <form @submit.prevent="saveCategory" class="space-y-3">
          <div>
            <label class="block text-sm font-medium mb-1">Name</label>
            <input v-model="form.name" required class="w-full border rounded-lg px-3 py-2 text-sm" />
          </div>
          <p v-if="formError" class="text-red-500 text-sm">{{ formError }}</p>
          <div class="flex gap-2 pt-2">
            <button type="button" @click="showModal = false" class="flex-1 border border-gray-300 rounded-lg py-2 text-sm">Cancel</button>
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

const categories = ref([]);
const loading    = ref(true);
const showModal  = ref(false);
const editing    = ref(null);
const formError  = ref('');
const form       = ref({ name: '' });

async function fetchCategories() {
  loading.value = true;
  const res = await api.get('/categories');
  categories.value = res.data;
  loading.value    = false;
}

onMounted(fetchCategories);

function openModal(cat = null) {
  editing.value   = cat;
  formError.value = '';
  form.value      = { name: cat?.name || '' };
  showModal.value = true;
}

async function saveCategory() {
  formError.value = '';
  try {
    if (editing.value) {
      await api.put(`/categories/${editing.value.id}`, form.value);
    } else {
      await api.post('/categories', form.value);
    }
    showModal.value = false;
    fetchCategories();
  } catch (e) {
    formError.value = e.response?.data?.message || 'Save failed.';
  }
}

async function deleteCategory(cat) {
  if (!confirm(`Delete "${cat.name}"?`)) return;
  await api.delete(`/categories/${cat.id}`);
  fetchCategories();
}
</script>
