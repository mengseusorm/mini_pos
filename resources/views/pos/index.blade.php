@extends('layouts.app')

@section('title', 'Point of Sale - Mini POS')
@section('breadcrumb', 'Point of Sale')

@section('content')
<div x-data="posApp()" class="flex flex-col lg:flex-row gap-4 h-full">

    {{-- Left: Product Grid --}}
    <div class="flex-1 space-y-4">
        {{-- Category Filter --}}
        <div class="flex gap-2 flex-wrap">
            <button @click="selectedCategory = null"
                :class="selectedCategory === null ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border dark:border-gray-600'"
                class="px-3 py-1.5 text-sm rounded-lg">
                All
            </button>
            @foreach($categories as $cat)
            <button @click="selectedCategory = {{ $cat->id }}"
                :class="selectedCategory === {{ $cat->id }} ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border dark:border-gray-600'"
                class="px-3 py-1.5 text-sm rounded-lg">
                {{ $cat->name }}
            </button>
            @endforeach
        </div>

        {{-- Search --}}
        <input type="text" x-model="search" placeholder="Search items..."
            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">

        {{-- Items Grid --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3">
            <template x-for="item in filteredItems" :key="item.id">
                <div @click="addToCart(item)"
                    class="bg-white dark:bg-gray-800 rounded-lg shadow p-3 cursor-pointer hover:shadow-md hover:border-blue-400 border border-transparent transition">
                    <p class="font-medium text-sm text-gray-800 dark:text-white truncate" x-text="item.name"></p>
                    <p class="text-xs text-gray-400 mt-0.5" x-text="item.category ? item.category.name : ''"></p>
                    <div class="flex justify-between items-center mt-2">
                        <span class="text-sm font-bold text-blue-600 dark:text-blue-400" x-text="'Rp ' + Number(item.price).toLocaleString('id-ID')"></span>
                        <span class="text-xs px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400" x-text="'Stk: ' + item.stock"></span>
                    </div>
                </div>
            </template>
            <template x-if="filteredItems.length === 0">
                <div class="col-span-4 py-12 text-center text-gray-400 text-sm">No items found</div>
            </template>
        </div>
    </div>

    {{-- Right: Cart --}}
    <div class="w-full lg:w-96 flex flex-col gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow flex flex-col" style="min-height:400px">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h2 class="font-semibold text-gray-800 dark:text-white">Cart</h2>
                <button @click="clearCart()" class="text-xs text-red-500 hover:text-red-700" x-show="cart.length > 0">Clear</button>
            </div>

            {{-- Cart Items --}}
            <div class="flex-1 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700 max-h-72">
                <template x-for="(line, idx) in cart" :key="idx">
                    <div class="px-4 py-2 flex items-center gap-2">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 dark:text-white truncate" x-text="line.name"></p>
                            <p class="text-xs text-gray-400" x-text="'Rp ' + Number(line.price).toLocaleString('id-ID')"></p>
                        </div>
                        <div class="flex items-center gap-1">
                            <button @click="decreaseQty(idx)" class="w-6 h-6 rounded bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-200 text-sm font-bold flex items-center justify-center hover:bg-gray-200">-</button>
                            <span class="w-6 text-center text-sm dark:text-white" x-text="line.quantity"></span>
                            <button @click="increaseQty(idx)" class="w-6 h-6 rounded bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-200 text-sm font-bold flex items-center justify-center hover:bg-gray-200">+</button>
                        </div>
                        <p class="text-sm font-semibold text-gray-800 dark:text-white w-20 text-right"
                            x-text="'Rp ' + Number(line.price * line.quantity).toLocaleString('id-ID')"></p>
                        <button @click="removeFromCart(idx)" class="text-gray-300 hover:text-red-500 ml-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                </template>
                <template x-if="cart.length === 0">
                    <div class="p-8 text-center text-gray-400 text-sm">Cart is empty — click items to add</div>
                </template>
            </div>

            {{-- Totals --}}
            <div class="p-4 border-t border-gray-200 dark:border-gray-700 space-y-2">
                <div class="flex justify-between text-sm text-gray-600 dark:text-gray-300">
                    <span>Subtotal</span>
                    <span x-text="'Rp ' + Number(subtotal).toLocaleString('id-ID')"></span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-600 dark:text-gray-300">Discount</span>
                    <input type="number" x-model.number="discount" min="0"
                        class="w-28 border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm text-right bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-600 dark:text-gray-300">Tax (%)</span>
                    <input type="number" x-model.number="taxPercent" min="0" max="100"
                        class="w-28 border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm text-right bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
                <div class="flex justify-between font-bold text-base text-gray-800 dark:text-white border-t border-gray-200 dark:border-gray-700 pt-2">
                    <span>Total</span>
                    <span x-text="'Rp ' + Number(total).toLocaleString('id-ID')"></span>
                </div>
            </div>

            {{-- Payment --}}
            <div class="p-4 border-t border-gray-200 dark:border-gray-700 space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Payment Method</label>
                    <select x-model="paymentMethod"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="transfer">Transfer</option>
                        <option value="qris">QRIS</option>
                    </select>
                </div>
                <div x-show="paymentMethod === 'cash'">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Amount Paid</label>
                    <input type="number" x-model.number="amountPaid" min="0"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-green-600 mt-1" x-show="amountPaid >= total && total > 0"
                        x-text="'Change: Rp ' + Number(amountPaid - total).toLocaleString('id-ID')"></p>
                </div>

                {{-- Error --}}
                <p x-show="errorMsg" x-text="errorMsg" class="text-xs text-red-500"></p>

                <button @click="checkout()"
                    :disabled="cart.length === 0 || processing"
                    class="w-full py-2.5 bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold rounded-lg text-sm">
                    <span x-show="!processing">Process Order</span>
                    <span x-show="processing">Processing...</span>
                </button>
            </div>
        </div>
    </div>

</div>

{{-- Success Receipt Modal --}}
<div x-show="receipt" x-transition.opacity
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-sm mx-4 p-6 text-center space-y-3">
        <div class="w-14 h-14 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center mx-auto">
            <svg class="w-8 h-8 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h3 class="text-lg font-bold text-gray-800 dark:text-white">Order Complete!</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400" x-text="receipt ? 'Order #' + receipt.id + ' — Rp ' + Number(receipt.total).toLocaleString('id-ID') : ''"></p>
        <div x-show="receipt && receipt.change > 0" class="text-sm text-green-600 font-semibold"
            x-text="'Change: Rp ' + (receipt ? Number(receipt.change).toLocaleString('id-ID') : '')"></div>
        <button @click="receipt = null; clearCart()"
            class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">
            New Order
        </button>
    </div>
</div>

<script>
function posApp() {
    return {
        allItems: @json($items),
        search: '',
        selectedCategory: null,
        cart: [],
        discount: 0,
        taxPercent: 0,
        paymentMethod: 'cash',
        amountPaid: 0,
        processing: false,
        errorMsg: '',
        receipt: null,

        get filteredItems() {
            return this.allItems.filter(item => {
                const matchCat = this.selectedCategory === null || item.category_id === this.selectedCategory;
                const matchSearch = !this.search || item.name.toLowerCase().includes(this.search.toLowerCase())
                    || (item.barcode && item.barcode.includes(this.search));
                return matchCat && matchSearch;
            });
        },

        get subtotal() {
            return this.cart.reduce((sum, l) => sum + l.price * l.quantity, 0);
        },
        get taxAmount() {
            return Math.round((this.subtotal - this.discount) * (this.taxPercent / 100));
        },
        get total() {
            return Math.max(0, this.subtotal - this.discount + this.taxAmount);
        },

        addToCart(item) {
            const existing = this.cart.find(l => l.id === item.id);
            if (existing) {
                if (existing.quantity < item.stock) existing.quantity++;
            } else {
                this.cart.push({ id: item.id, name: item.name, price: parseFloat(item.price), quantity: 1, stock: item.stock });
            }
        },
        increaseQty(idx) {
            const line = this.cart[idx];
            if (line.quantity < line.stock) line.quantity++;
        },
        decreaseQty(idx) {
            if (this.cart[idx].quantity > 1) this.cart[idx].quantity--;
            else this.removeFromCart(idx);
        },
        removeFromCart(idx) { this.cart.splice(idx, 1); },
        clearCart() { this.cart = []; this.discount = 0; this.taxPercent = 0; this.amountPaid = 0; this.errorMsg = ''; },

        async checkout() {
            this.errorMsg = '';
            if (this.cart.length === 0) return;
            if (this.paymentMethod === 'cash' && this.amountPaid < this.total) {
                this.errorMsg = 'Amount paid is less than total';
                return;
            }
            this.processing = true;
            try {
                const res = await fetch('/api/orders', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-XSRF-TOKEN': decodeURIComponent(document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] || ''),
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        items: this.cart.map(l => ({ item_id: l.id, quantity: l.quantity })),
                        payment_method: this.paymentMethod,
                        payment_amount: this.amountPaid || this.total,
                        discount: this.discount,
                        tax: this.taxAmount,
                    })
                });
                const data = await res.json();
                if (!res.ok) {
                    this.errorMsg = data.message || 'Order failed';
                } else {
                    const order = data.data ?? data;
                    this.receipt = { id: order.id, total: this.total, change: Math.max(0, this.amountPaid - this.total) };
                }
            } catch(e) {
                this.errorMsg = 'Network error, please try again';
            } finally {
                this.processing = false;
            }
        }
    };
}
</script>
@endsection
