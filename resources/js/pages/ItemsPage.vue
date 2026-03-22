<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Items</h1>
      <button @click="openModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">
        + Add Item
      </button>
    </div>

    <!-- Filters -->
    <div class="flex gap-3 mb-4 flex-wrap">
      <input v-model="search" placeholder="Search..." @input="debouncedFetch"
        class="border border-gray-300 rounded-lg px-3 py-2 text-sm flex-1" />
      <select v-model="categoryFilter" @change="fetchItems"
        class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        <option value="">All Categories</option>
        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
      </select>
      <label class="flex items-center gap-2 text-sm cursor-pointer">
        <input v-model="lowStock" type="checkbox" @change="fetchItems" />
        Low stock only
      </label>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50">
          <tr class="text-left text-gray-500 border-b">
            <th class="px-4 py-3">Name</th>
            <th class="px-4 py-3">Category</th>
            <th class="px-4 py-3">Price</th>
            <th class="px-4 py-3">Stock</th>
            <th class="px-4 py-3">Barcode</th>
            <th class="px-4 py-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="6" class="text-center py-6 text-gray-400">Loading...</td></tr>
          <tr v-else v-for="item in items" :key="item.id" class="border-b last:border-0">
            <td class="px-4 py-3 font-medium">{{ item.name }}</td>
            <td class="px-4 py-3">{{ item.category?.name }}</td>
            <td class="px-4 py-3">Rp {{ fmt(item.price) }}</td>
            <td class="px-4 py-3">
              <span :class="item.stock <= 10 ? 'text-red-600 font-bold' : ''">{{ item.stock }}</span>
            </td>
            <td class="px-4 py-3 text-gray-400">{{ item.barcode || '-' }}</td>
            <td class="px-4 py-3 flex gap-2">
              <button @click="openModal(item)" class="text-blue-600 hover:underline text-xs">Edit</button>
              <button @click="deleteItem(item)" class="text-red-500 hover:underline text-xs">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h2 class="font-bold text-lg mb-4">{{ editing ? 'Edit' : 'Add' }} Item</h2>
        <form @submit.prevent="saveItem" class="space-y-3">
          <div>
            <label class="block text-sm font-medium mb-1">Name</label>
            <input v-model="form.name" required class="w-full border rounded-lg px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Category</label>
            <select v-model="form.category_id" required class="w-full border rounded-lg px-3 py-2 text-sm">
              <option value="" disabled>Select category</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Price</label>
            <input v-model.number="form.price" type="number" min="0" required class="w-full border rounded-lg px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Stock</label>
            <input v-model.number="form.stock" type="number" min="0" required class="w-full border rounded-lg px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Barcode (optional)</label>
            <input v-model="form.barcode" class="w-full border rounded-lg px-3 py-2 text-sm" />
          </div>
          <p v-if="formError" class="text-red-500 text-sm">{{ formError }}</p>
          <div class="flex gap-2 pt-2">
            <button type="button" @click="showModal = false" class="flex-1 border border-gray-300 rounded-lg py-2 text-sm hover:bg-gray-50">Cancel</button>
            <button type="submit" class="flex-1 bg-blue-600 text-white rounded-lg py-2 text-sm hover:bg-blue-700">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '@/api';

const items          = ref([]);
const categories     = ref([]);
const loading        = ref(true);
const search         = ref('');
const categoryFilter = ref('');
const lowStock       = ref(false);
const showModal      = ref(false);
const editing        = ref(null);
const formError      = ref('');
const form           = ref({ name: '', category_id: '', price: 0, stock: 0, barcode: '' });

let searchTimer = null;
function debouncedFetch() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(fetchItems, 300);
}

async function fetchItems() {
  loading.value = true;
  const params = { search: search.value, category_id: categoryFilter.value, low_stock: lowStock.value ? 1 : '' };
  const res = await api.get('/items', { params });
  items.value   = res.data.data;
  loading.value = false;
}

onMounted(async () => {
  const [, catRes] = await Promise.all([fetchItems(), api.get('/categories')]);
  categories.value = catRes.data;
});

function openModal(item = null) {
  editing.value    = item;
  formError.value  = '';
  form.value       = item
    ? { name: item.name, category_id: item.category_id, price: item.price, stock: item.stock, barcode: item.barcode || '' }
    : { name: '', category_id: '', price: 0, stock: 0, barcode: '' };
  showModal.value  = true;
}

async function saveItem() {
  formError.value = '';
  try {
    if (editing.value) {
      await api.put(`/items/${editing.value.id}`, form.value);
    } else {
      await api.post('/items', form.value);
    }
    showModal.value = false;
    fetchItems();
  } catch (e) {
    formError.value = Object.values(e.response?.data?.errors || {}).flat().join(' ') || 'Save failed.';
  }
}

async function deleteItem(item) {
  if (!confirm(`Delete "${item.name}"?`)) return;
  await api.delete(`/items/${item.id}`);
  fetchItems();
}

const fmt = (n) => Number(n || 0).toLocaleString('id-ID');
</script>
