<template>
  <div class="flex h-full gap-0">
    <!-- Item grid -->
    <div class="flex-1 p-4 overflow-y-auto">
      <div class="flex gap-2 mb-4">
        <div class="relative flex-1">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
          <Input v-model="search" placeholder="Search item or barcode…" class="pl-9" />
        </div>
        <Select v-model="selectedCategory">
          <SelectTrigger class="w-48">
            <SelectValue placeholder="All Categories" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All Categories</SelectItem>
            <SelectItem v-for="cat in categories" :key="cat.id" :value="cat.id">
              {{ cat.name }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
        <button
          v-for="item in filteredItems"
          :key="item.id"
          @click="cart.addItem(item)"
          :disabled="item.stock === 0"
          class="group text-left rounded-xl border bg-card shadow-sm p-3 hover:shadow-md hover:border-primary/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <div class="font-medium text-sm truncate">{{ item.name }}</div>
          <div class="text-xs text-muted-foreground mt-0.5">{{ item.category?.name }}</div>
          <div class="text-primary font-bold text-sm mt-2">Rp {{ fmt(item.price) }}</div>
          <Badge
            :variant="item.stock <= 10 ? 'destructive' : 'secondary'"
            class="mt-1 text-[10px] px-1.5 py-0"
          >
            Stock: {{ item.stock }}
          </Badge>
        </button>
      </div>
    </div>

    <!-- Cart panel -->
    <div class="w-80 bg-card border-l flex flex-col">
      <div class="p-4 border-b flex items-center gap-2">
        <ShoppingCart class="h-4 w-4 text-muted-foreground" />
        <span class="font-semibold">Cart</span>
        <Badge variant="secondary" class="ml-auto">{{ cart.count }}</Badge>
      </div>

      <div class="flex-1 overflow-y-auto p-4 space-y-2">
        <div v-if="cart.items.length === 0" class="text-center text-muted-foreground text-sm mt-8">
          Cart is empty
        </div>
        <div
          v-for="item in cart.items"
          :key="item.id"
          class="flex items-center gap-2 rounded-lg border bg-background p-2"
        >
          <div class="flex-1 min-w-0">
            <div class="text-sm font-medium truncate">{{ item.name }}</div>
            <div class="text-xs text-muted-foreground">Rp {{ fmt(item.price) }}</div>
          </div>
          <div class="flex items-center gap-1">
            <Button size="icon" variant="outline" class="h-6 w-6"
              @click="cart.updateQty(item.id, item.quantity - 1)">
              <Minus class="h-3 w-3" />
            </Button>
            <span class="w-6 text-center text-sm font-medium">{{ item.quantity }}</span>
            <Button size="icon" variant="outline" class="h-6 w-6"
              @click="cart.updateQty(item.id, item.quantity + 1)">
              <Plus class="h-3 w-3" />
            </Button>
          </div>
          <Button size="icon" variant="ghost" class="h-6 w-6 text-muted-foreground hover:text-destructive"
            @click="cart.removeItem(item.id)">
            <X class="h-3 w-3" />
          </Button>
        </div>
      </div>

      <!-- Summary -->
      <div class="p-4 border-t space-y-3">
        <div class="flex justify-between text-sm">
          <span class="text-muted-foreground">Subtotal</span>
          <span class="font-medium">Rp {{ fmt(cart.total) }}</span>
        </div>
        <div class="flex items-center gap-2 text-sm">
          <Label class="text-muted-foreground w-20 shrink-0">Discount</Label>
          <Input v-model.number="discount" type="number" min="0" class="h-8 text-right" />
        </div>
        <div class="flex items-center gap-2 text-sm">
          <Label class="text-muted-foreground w-20 shrink-0">Tax (%)</Label>
          <Input v-model.number="taxRate" type="number" min="0" max="100" class="h-8 text-right" />
        </div>
        <Separator />
        <div class="flex justify-between font-bold text-base">
          <span>Total</span>
          <span>Rp {{ fmt(grandTotal) }}</span>
        </div>

        <div class="flex items-center gap-2 text-sm">
          <Label class="text-muted-foreground w-20 shrink-0">Payment</Label>
          <Select v-model="paymentMethod" class="flex-1">
            <SelectTrigger class="h-8">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="cash">Cash</SelectItem>
              <SelectItem value="card">Card</SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div v-if="paymentMethod === 'cash'" class="flex items-center gap-2 text-sm">
          <Label class="text-muted-foreground w-20 shrink-0">Cash</Label>
          <Input v-model.number="cashAmount" type="number" min="0" class="h-8 text-right" />
        </div>
        <div v-if="paymentMethod === 'cash' && cashAmount >= grandTotal" class="flex justify-between text-sm text-green-600 font-medium">
          <span>Change</span>
          <span>Rp {{ fmt(cashAmount - grandTotal) }}</span>
        </div>

        <Button class="w-full" @click="processOrder" :disabled="processing || cart.items.length === 0">
          <Loader2 v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
          <CheckCircle v-else class="mr-2 h-4 w-4" />
          {{ processing ? 'Processing…' : 'Process Order' }}
        </Button>
      </div>
    </div>
  </div>

  <!-- Receipt Dialog -->
  <Dialog :open="!!receipt" @update:open="val => { if (!val) { receipt = null; cart.clear() } }">
    <DialogContent class="max-w-sm max-h-[90vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle class="text-center">Mini POS — Receipt</DialogTitle>
        <p class="text-center text-xs text-muted-foreground">{{ receipt ? fmtDate(receipt.created_at) : '' }}</p>
      </DialogHeader>

      <Separator />
      <div class="space-y-1">
        <div v-for="item in receipt?.items" :key="item.id" class="flex justify-between text-sm">
          <span>{{ item.item?.name }} ×{{ item.quantity }}</span>
          <span>Rp {{ fmt(item.subtotal) }}</span>
        </div>
      </div>
      <Separator />
      <div class="space-y-1 text-sm">
        <div class="flex justify-between"><span class="text-muted-foreground">Subtotal</span><span>Rp {{ fmt(receipt?.subtotal) }}</span></div>
        <div v-if="receipt?.discount > 0" class="flex justify-between text-destructive"><span>Discount</span><span>− Rp {{ fmt(receipt?.discount) }}</span></div>
        <div v-if="receipt?.tax > 0" class="flex justify-between"><span class="text-muted-foreground">Tax</span><span>Rp {{ fmt(receipt?.tax) }}</span></div>
        <div class="flex justify-between font-bold text-base"><span>Total</span><span>Rp {{ fmt(receipt?.total) }}</span></div>
        <div class="flex justify-between"><span class="text-muted-foreground">Paid</span><span>Rp {{ fmt(receipt?.payment?.amount) }}</span></div>
        <div v-if="receipt?.payment?.change > 0" class="flex justify-between text-green-600"><span>Change</span><span>Rp {{ fmt(receipt?.payment?.change) }}</span></div>
      </div>
      <DialogFooter class="gap-2">
        <Button variant="outline" @click="printReceipt" class="flex-1">
          <Printer class="mr-2 h-4 w-4" /> Print
        </Button>
        <Button @click="receipt = null; cart.clear()" class="flex-1">Close</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import {
  Search, ShoppingCart, Minus, Plus, X, CheckCircle,
  Loader2, Printer,
} from 'lucide-vue-next';
import api from '@/api';
import { useCartStore } from '@/stores/cart';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';

const cart             = useCartStore();
const items            = ref([]);
const categories       = ref([]);
const search           = ref('');
const selectedCategory = ref('all');
const discount         = ref(0);
const taxRate          = ref(0);
const paymentMethod    = ref('cash');
const cashAmount       = ref(0);
const processing       = ref(false);
const receipt          = ref(null);

const grandTotal = computed(() => {
  const tax = cart.total * taxRate.value / 100;
  return Math.max(0, cart.total - discount.value + tax);
});

const filteredItems = computed(() =>
  items.value.filter(item => {
    const matchSearch = !search.value ||
      item.name.toLowerCase().includes(search.value.toLowerCase()) ||
      item.barcode === search.value;
    const matchCat = selectedCategory.value === 'all' || item.category_id === selectedCategory.value;
    return matchSearch && matchCat;
  })
);

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

function printReceipt() { window.print(); }

const fmt     = (n) => Number(n || 0).toLocaleString('id-ID');
const fmtDate = (d) => new Date(d).toLocaleDateString('id-ID');
</script>
