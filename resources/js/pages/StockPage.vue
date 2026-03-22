<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Stock Movements</h1>
      <button @click="openModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">
        + Add Movement
      </button>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50">
          <tr class="text-left text-gray-500 border-b">
            <th class="px-4 py-3">Item</th>
            <th class="px-4 py-3">Type</th>
            <th class="px-4 py-3">Quantity</th>
            <th class="px-4 py-3">Note</th>
            <th class="px-4 py-3">By</th>
            <th class="px-4 py-3">Date</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="6" class="text-center py-6 text-gray-400">Loading...</td></tr>
          <tr v-else v-for="m in movements" :key="m.id" class="border-b last:border-0">
            <td class="px-4 py-3 font-medium">{{ m.item?.name }}</td>
            <td class="px-4 py-3">
              <span :class="m.type === 'in' ? 'text-green-600 bg-green-100' : 'text-red-600 bg-red-100'"
                class="px-2 py-0.5 rounded-full text-xs font-medium capitalize">
                {{ m.type }}
              </span>
            </td>
            <td class="px-4 py-3">{{ m.quantity }}</td>
            <td class="px-4 py-3 text-gray-500">{{ m.note || '-' }}</td>
            <td class="px-4 py-3">{{ m.user?.name }}</td>
            <td class="px-4 py-3">{{ fmtDate(m.created_at) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 w-full max-w-sm">
        <h2 class="font-bold text-lg mb-4">Add Stock Movement</h2>
        <form @submit.prevent="saveMovement" class="space-y-3">
          <div>
            <label class="block text-sm font-medium mb-1">Item</label>
            <select v-model="form.item_id" required class="w-full border rounded-lg px-3 py-2 text-sm">
              <option value="" disabled>Select item</option>
              <option v-for="item in allItems" :key="item.id" :value="item.id">{{ item.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Type</label>
            <select v-model="form.type" class="w-full border rounded-lg px-3 py-2 text-sm">
              <option value="in">Stock In</option>
              <option value="out">Stock Out</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Quantity</label>
            <input v-model.number="form.quantity" type="number" min="1" required class="w-full border rounded-lg px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Note (optional)</label>
            <input v-model="form.note" class="w-full border rounded-lg px-3 py-2 text-sm" />
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

const movements = ref([]);
const allItems  = ref([]);
const loading   = ref(true);
const showModal = ref(false);
const formError = ref('');
const form      = ref({ item_id: '', type: 'in', quantity: 1, note: '' });

async function fetchMovements() {
  loading.value = true;
  const res = await api.get('/stock-movements');
  movements.value = res.data.data;
  loading.value   = false;
}

onMounted(async () => {
  const [, itemsRes] = await Promise.all([fetchMovements(), api.get('/items?per_page=100')]);
  allItems.value = itemsRes.data.data;
});

function openModal() {
  formError.value = '';
  form.value      = { item_id: '', type: 'in', quantity: 1, note: '' };
  showModal.value = true;
}

async function saveMovement() {
  formError.value = '';
  try {
    await api.post('/stock-movements', form.value);
    showModal.value = false;
    fetchMovements();
  } catch (e) {
    formError.value = e.response?.data?.message || 'Save failed.';
  }
}

const fmtDate = (d) => new Date(d).toLocaleDateString('id-ID');
</script>
