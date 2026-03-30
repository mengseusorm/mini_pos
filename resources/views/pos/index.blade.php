@extends('layouts.app')

@section('title', 'Point of Sale - Mini POS')
@section('breadcrumb', 'Point of Sale')

@section('content')
<div x-data="posPage()" x-init="loadItems()" class="flex flex-col lg:flex-row gap-4 h-full">

    <!-- Items Panel -->
    <div class="flex-1">
        <!-- Search & Filters -->
        <div class="flex flex-wrap gap-3 mb-4">
            <input type="text" x-model="search" @input.debounce.400ms="loadItems()"
                   placeholder="Search items..."
                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5 w-full md:w-64 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            <select x-model="categoryFilter" @change="loadItems()"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="">All Categories</option>
                <template x-for="cat in categories" :key="cat.id">
                    <option :value="cat.id" x-text="cat.name"></option>
                </template>
            </select>
        </div>

        <!-- Items Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-3">
            <template x-if="loadingItems">
                <template x-for="i in 8" :key="i">
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 animate-pulse shadow">
                        <div class="h-24 bg-gray-200 dark:bg-gray-700 rounded mb-3"></div>
                        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded mb-2"></div>
                        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
                    </div>
                </template>
            </template>
            <template x-for="item in items" :key="item.id">
                <div @click="addToCart(item)"
                     :class="item.stock_quantity === 0 ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer hover:shadow-lg hover:border-blue-500'"
                     class="bg-white dark:bg-gray-800 rounded-lg p-3 shadow border-2 border-transparent transition-all">
                    <div class="bg-gray-100 dark:bg-gray-700 rounded-lg h-20 flex items-center justify-center mb-2 text-3xl">
                        🛍️
                    </div>
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white truncate" x-text="item.name"></h3>
                    <p class="text-blue-600 dark:text-blue-400 font-semibold text-sm" x-text="'$' + parseFloat(item.price).toFixed(2)"></p>
                    <p class="text-xs text-gray-400" x-text="'Stock: ' + item.stock_quantity"></p>
                </div>
            </template>
            <template x-if="!loadingItems && items.length === 0">
                <div class="col-span-full py-8 text-center text-gray-400">No items found</div>
            </template>
        </div>
    </div>

    <!-- Cart Panel -->
    <div class="w-full lg:w-80 bg-white dark:bg-gray-800 rounded-lg shadow flex flex-col">
        <div class="p-4 border-b dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Current Order</h2>
        </div>

        <!-- Cart Items -->
        <div class="flex-1 overflow-y-auto p-4 space-y-2 min-h-0 max-h-96 lg:max-h-full">
            <template x-if="cart.length === 0">
                <div class="py-8 text-center text-gray-400 text-sm">Cart is empty. Pick items to add.</div>
            </template>
            <template x-for="(cartItem, index) in cart" :key="cartItem.id">
                <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="flex-1 min-w-0 me-2">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate" x-text="cartItem.name"></p>
                        <p class="text-xs text-gray-400" x-text="'$' + parseFloat(cartItem.price).toFixed(2)"></p>
                    </div>
                    <div class="flex items-center gap-1">
                        <button @click="decreaseQty(index)" class="w-6 h-6 flex items-center justify-center text-gray-500 hover:text-red-500 bg-white dark:bg-gray-600 rounded border">-</button>
                        <span class="w-8 text-center text-sm font-medium text-gray-900 dark:text-white" x-text="cartItem.qty"></span>
                        <button @click="increaseQty(index)" class="w-6 h-6 flex items-center justify-center text-gray-500 hover:text-green-500 bg-white dark:bg-gray-600 rounded border">+</button>
                        <button @click="removeFromCart(index)" class="w-6 h-6 flex items-center justify-center text-red-400 hover:text-red-600 ms-1">✕</button>
                    </div>
                </div>
            </template>
        </div>

        <!-- Cart Footer -->
        <div class="p-4 border-t dark:border-gray-700 space-y-3">
            <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                <span>Subtotal</span>
                <span x-text="'$' + subtotal.toFixed(2)"></span>
            </div>
            <div class="flex justify-between text-lg font-bold text-gray-900 dark:text-white">
                <span>Total</span>
                <span x-text="'$' + subtotal.toFixed(2)"></span>
            </div>

            <!-- Payment Method -->
            <div>
                <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Payment Method</label>
                <select x-model="paymentMethod"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="cash">Cash</option>
                    <option value="card">Card</option>
                    <option value="qr">QR / Digital</option>
                </select>
            </div>

            <!-- Cash received (for cash payment) -->
            <div x-show="paymentMethod === 'cash'">
                <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Cash Received</label>
                <input type="number" step="0.01" x-model="cashReceived" min="0"
                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <p x-show="cashReceived >= subtotal && subtotal > 0" class="text-sm text-green-600 mt-1">
                    Change: $<span x-text="(cashReceived - subtotal).toFixed(2)"></span>
                </p>
            </div>

            <div x-show="orderError" class="p-2 text-sm text-red-500 bg-red-50 rounded" x-text="orderError"></div>

            <!-- Buttons -->
            <button @click="clearCart()" :disabled="cart.length === 0"
                    class="w-full py-2 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 disabled:opacity-50 dark:bg-gray-700 dark:text-red-400 dark:hover:bg-gray-600">
                Clear Cart
            </button>
            <button @click="placeOrder()" :disabled="cart.length === 0 || placing"
                    class="w-full py-2.5 text-sm font-medium text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 disabled:opacity-50">
                <span x-show="placing">Processing...</span>
                <span x-show="!placing">Place Order</span>
            </button>
        </div>
    </div>

    <!-- Success Modal -->
    <div x-show="orderSuccess" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-sm mx-4 p-6 text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Order Placed!</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-4">Order #<span x-text="lastOrderId"></span> created successfully.</p>
            <button @click="orderSuccess = false"
                    class="w-full py-2.5 text-sm font-medium text-white bg-blue-700 rounded-lg hover:bg-blue-800">
                New Order
            </button>
        </div>
    </div>
</div>

<script>
function posPage() {
    return {
        items: [], categories: [], cart: [], search: '', categoryFilter: '',
        loadingItems: true, placing: false, orderError: '', orderSuccess: false, lastOrderId: null,
        paymentMethod: 'cash', cashReceived: 0,

        get subtotal() { return this.cart.reduce((s, i) => s + i.price * i.qty, 0); },

        async loadItems() {
            this.loadingItems = true;
            try {
                const params = new URLSearchParams({ per_page: 50, is_active: 1 });
                if (this.search) params.append('search', this.search);
                if (this.categoryFilter) params.append('category_id', this.categoryFilter);
                const [itemsRes, catsRes] = await Promise.all([
                    fetch('/api/items?' + params, { headers: { 'Accept': 'application/json' } }),
                    this.categories.length ? Promise.resolve(null) : fetch('/api/categories?per_page=100', { headers: { 'Accept': 'application/json' } })
                ]);
                const data = await itemsRes.json();
                this.items = (data.data ?? data).filter(i => i.is_active);
                if (catsRes) { const cd = await catsRes.json(); this.categories = cd.data ?? cd; }
            } catch (e) { console.error(e); }
            this.loadingItems = false;
        },

        addToCart(item) {
            if (item.stock_quantity === 0) return;
            const existing = this.cart.find(c => c.id === item.id);
            if (existing) {
                if (existing.qty < item.stock_quantity) existing.qty++;
            } else {
                this.cart.push({ id: item.id, name: item.name, price: parseFloat(item.price), qty: 1, stock: item.stock_quantity });
            }
        },

        increaseQty(index) { const c = this.cart[index]; if (c.qty < c.stock) c.qty++; },
        decreaseQty(index) { if (this.cart[index].qty > 1) this.cart[index].qty--; else this.removeFromCart(index); },
        removeFromCart(index) { this.cart.splice(index, 1); },
        clearCart() { this.cart = []; this.orderError = ''; },

        async placeOrder() {
            if (this.cart.length === 0) return;
            this.placing = true; this.orderError = '';
            try {
                const payload = {
                    items: this.cart.map(c => ({ item_id: c.id, quantity: c.qty })),
                    payment_method: this.paymentMethod,
                    amount_paid: this.paymentMethod === 'cash' ? parseFloat(this.cashReceived) : this.subtotal
                };
                const res = await fetch('/api/orders', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: JSON.stringify(payload)
                });
                if (res.ok) {
                    const order = await res.json();
                    this.lastOrderId = order.id ?? order.data?.id;
                    this.clearCart();
                    this.orderSuccess = true;
                    this.cashReceived = 0;
                    this.loadItems();
                } else {
                    const e = await res.json();
                    this.orderError = e.message ?? 'Failed to place order';
                }
            } catch (e) { this.orderError = 'Network error'; }
            this.placing = false;
        }
    }
}
</script>
@endsection
