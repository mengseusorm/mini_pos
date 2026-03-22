<template>
  <div class="flex h-full gap-0">
    <!-- Item grid -->
    <div class="flex-1 p-4 overflow-y-auto">
      <div class="flex gap-2 mb-4">
        <input v-model="search" placeholder="Search item or barcode..."
          class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
        <select v-model="selectedCategory"
          class="border border-gray-300 rounded-lg px-2 py-2 text-sm focus:outline-none">
          <option value="">All Categories</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
        </select>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
        <button v-for="item in filteredItems" :key="item.id"
          @click="cart.addItem(item)"
          :disabled="item.stock === 0"
          class="bg-white rounded-xl shadow p-3 text-left hover:shadow-md transition-shadow disabled:opacity-50 disabled:cursor-not-allowed">
          <div class="font-medium text-sm text-gray-800 truncate">{{ item.name }}</div>
          <div class="text-xs text-gray-500 mt-0.5">{{ item.category?.name }}</div>
          <div class="text-blue-600 font-bold text-sm mt-1">Rp {{ fmt(item.price) }}</div>
          <div :class="item.stock <= 10 ? 'text-red-500' : 'text-gray-400'" class="text-xs mt-0.5">
            Stock: {{ item.stock }}
          </div>
        </button>
      </div>
    </div>

    <!-- Cart panel -->
    <div class="w-80 bg-white border-l flex flex-col">
      <div class="p-4 border-b font-semibold text-gray-700">
        🛒 Cart ({{ cart.count }})
      </div>

      <div class="flex-1 overflow-y-auto p-4 space-y-3">
        <div v-if="cart.items.length === 0" class="text-center text-gray-400 text-sm mt-8">
          Cart is empty
        </div>
        <div v-for="item in cart.items" :key="item.id"
          class="flex items-center gap-2 bg-gray-50 rounded-lg p-2">
          <div class="flex-1">
            <div class="text-sm font-medium text-gray-800">{{ item.name }}</div>
            <div class="text-xs text-gray-500">Rp {{ fmt(item.price) }}</div>
          </div>
          <div class="flex items-center gap-1">
            <button @click="cart.updateQty(item.id, item.quantity - 1)"
              class="w-6 h-6 bg-gray-200 rounded text-sm hover:bg-gray-300">-</button>
            <span class="w-6 text-center text-sm">{{ item.quantity }}</span>
            <button @click="cart.updateQty(item.id, item.quantity + 1)"
              class="w-6 h-6 bg-gray-200 rounded text-sm hover:bg-gray-300">+</button>
          </div>
          <button @click="cart.removeItem(item.id)" class="text-red-400 hover:text-red-600">✕</button>
        </div>
      </div>

      <!-- Summary -->
      <div class="p-4 border-t space-y-2">
        <div class="flex justify-between text-sm">
          <span class="text-gray-600">Subtotal</span>
          <span>Rp {{ fmt(cart.total) }}</span>
        </div>
        <div class="flex items-center gap-2 text-sm">
          <span class="text-gray-600">Discount</span>
          <input v-model.number="discount" type="number" min="0"
            class="flex-1 border rounded px-2 py-1 text-right text-sm" />
        </div>
        <div class="flex items-center gap-2 text-sm">
          <span class="text-gray-600">Tax (%)</span>
          <input v-model.number="taxRate" type="number" min="0" max="100"
            class="flex-1 border rounded px-2 py-1 text-right text-sm" />
        </div>
        <div class="flex justify-between font-bold text-base border-t pt-2">
          <span>Total</span>
          <span>Rp {{ fmt(grandTotal) }}</span>
        </div>

        <div class="flex items-center gap-2 text-sm">
          <span class="text-gray-600">Payment</span>
          <select v-model="paymentMethod" class="flex-1 border rounded px-2 py-1 text-sm">
            <option value="cash">Cash</option>
            <option value="card">Card</option>
          </select>
        </div>

        <div v-if="paymentMethod === 'cash'" class="flex items-center gap-2 text-sm">
          <span class="text-gray-600">Cash</span>
          <input v-model.number="cashAmount" type="number" min="0"
            class="flex-1 border rounded px-2 py-1 text-right text-sm" />
        </div>
        <div v-if="paymentMethod === 'cash' && cashAmount >= grandTotal" class="flex justify-between text-sm text-green-600">
          <span>Change</span>
          <span>Rp {{ fmt(cashAmount - grandTotal) }}</span>
        </div>

        <button @click="processOrder" :disabled="processing || cart.items.length === 0"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-lg py-2 font-semibold transition-colors disabled:opacity-50 mt-2">
          {{ processing ? 'Processing...' : '✅ Process Order' }}
        </button>
      </div>
    </div>
  </div>

  <!-- Receipt Modal -->
  <div v-if="receipt" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-80 max-h-[90vh] overflow-y-auto">
      <h2 class="font-bold text-center text-lg mb-1">🏪 Mini POS</h2>
      <p class="text-center text-xs text-gray-500 mb-4">{{ fmtDate(receipt.created_at) }}</p>
      <div class="border-t border-dashed pt-3 space-y-1">
        <div v-for="item in receipt.items" :key="item.id" class="flex justify-between text-sm">
          <span>{{ item.item?.name }} x{{ item.quantity }}</span>
          <span>Rp {{ fmt(item.subtotal) }}</span>
        </div>
      </div>
      <div class="border-t border-dashed mt-3 pt-3 space-y-1 text-sm">
        <div class="flex justify-between"><span>Subtotal</span><span>Rp {{ fmt(receipt.subtotal) }}</span></div>
        <div v-if="receipt.discount > 0" class="flex justify-between text-red-500"><span>Discount</span><span>- Rp {{ fmt(receipt.discount) }}</span></div>
        <div v-if="receipt.tax > 0" class="flex justify-between"><span>Tax</span><span>Rp {{ fmt(receipt.tax) }}</span></div>
        <div class="flex justify-between font-bold text-base"><span>Total</span><span>Rp {{ fmt(receipt.total) }}</span></div>
        <div class="flex justify-between"><span>Payment</span><span>Rp {{ fmt(receipt.payment?.amount) }}</span></div>
        <div v-if="receipt.payment?.change > 0" class="flex justify-between text-green-600"><span>Change</span><span>Rp {{ fmt(receipt.payment?.change) }}</span></div>
      </div>
      <div class="flex gap-2 mt-4">
        <button @click="printReceipt" class="flex-1 border border-gray-300 rounded-lg py-2 text-sm hover:bg-gray-50">🖨️ Print</button>
        <button @click="receipt = null; cart.clear()" class="flex-1 bg-blue-600 text-white rounded-lg py-2 text-sm hover:bg-blue-700">Close</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '@/api';
import { useCartStore } from '@/stores/cart';

const cart           = useCartStore();
const items          = ref([]);
const categories     = ref([]);
const search         = ref('');
const selectedCategory = ref('');
const discount       = ref(0);
const taxRate        = ref(0);
const paymentMethod  = ref('cash');
const cashAmount     = ref(0);
const processing     = ref(false);
const receipt        = ref(null);

const grandTotal = computed(() => {
  const tax = cart.total * taxRate.value / 100;
  return Math.max(0, cart.total - discount.value + tax);
});

const filteredItems = computed(() => {
  return items.value.filter(item => {
    const matchSearch = !search.value ||
      item.name.toLowerCase().includes(search.value.toLowerCase()) ||
      item.barcode === search.value;
    const matchCat = !selectedCategory.value || item.category_id === selectedCategory.value;
    return matchSearch && matchCat;
  });
});

onMounted(async () => {
  const [itemRes, catRes] = await Promise.all([
    api.get('/items?per_page=100'),
    api.get('/categories'),
  ]);
  items.value      = itemRes.data.data;
  categories.value = catRes.data;
});

async function processOrder() {
  if (cart.items.length === 0) return;
  const payAmt = paymentMethod.value === 'cash' ? cashAmount.value : grandTotal.value;
  if (paymentMethod.value === 'cash' && payAmt < grandTotal.value) {
    alert('Cash amount is insufficient.');
    return;
  }
  processing.value = true;
  try {
    const res = await api.post('/orders', {
      items: cart.items.map(i => ({ item_id: i.id, quantity: i.quantity })),
      discount: discount.value,
      tax: taxRate.value,
      payment_method: paymentMethod.value,
      payment_amount: payAmt,
    });
    receipt.value = res.data;
    // Refresh stock in item list
    for (const orderItem of res.data.items) {
      const found = items.value.find(i => i.id === orderItem.item_id);
      if (found) found.stock -= orderItem.quantity;
    }
  } catch (e) {
    alert(e.response?.data?.message || 'Order failed.');
  } finally {
    processing.value = false;
  }
}

function printReceipt() {
  window.print();
}

const fmt     = (n) => Number(n || 0).toLocaleString('id-ID');
const fmtDate = (d) => new Date(d).toLocaleString('id-ID');
</script>
