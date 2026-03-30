@extends('layouts.app')

@section('title', 'Orders - Mini POS')
@section('breadcrumb', 'Orders')

@section('content')
<div x-data="{ showDetail: false, order: null, orderItems: [] }" class="space-y-4">

    {{-- Date Filter --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <form method="GET" action="{{ route('orders.index') }}" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg">
                Filter
            </button>
            @if(request('date_from') || request('date_to'))
            <a href="{{ route('orders.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm rounded-lg">
                Clear
            </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600 dark:text-gray-300">
                <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-3">#</th>
                        <th class="px-6 py-3">Cashier</th>
                        <th class="px-6 py-3">Items</th>
                        <th class="px-6 py-3">Subtotal</th>
                        <th class="px-6 py-3">Discount</th>
                        <th class="px-6 py-3">Tax</th>
                        <th class="px-6 py-3">Total</th>
                        <th class="px-6 py-3">Payment</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($orders as $o)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-3 font-medium">#{{ $o->id }}</td>
                        <td class="px-6 py-3">{{ $o->user?->name ?? 'Guest' }}</td>
                        <td class="px-6 py-3">{{ $o->items->count() }}</td>
                        <td class="px-6 py-3">Rp {{ number_format($o->subtotal) }}</td>
                        <td class="px-6 py-3 text-red-500">-Rp {{ number_format($o->discount) }}</td>
                        <td class="px-6 py-3 text-yellow-600">+Rp {{ number_format($o->tax) }}</td>
                        <td class="px-6 py-3 font-semibold">Rp {{ number_format($o->total) }}</td>
                        <td class="px-6 py-3 capitalize">{{ $o->payment_method }}</td>
                        <td class="px-6 py-3">
                            @if($o->status === 'completed')
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full dark:bg-green-900 dark:text-green-200">Completed</span>
                            @elseif($o->status === 'pending')
                                <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full dark:bg-yellow-900 dark:text-yellow-200">Pending</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full dark:bg-red-900 dark:text-red-200">{{ ucfirst($o->status) }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-gray-400 whitespace-nowrap">{{ $o->created_at->format('d M Y H:i') }}</td>
                        <td class="px-6 py-3">
                            <button type="button" @click="order = {{ Js::from($o->only(['id','user_id','subtotal','discount','tax','total','payment_method','status','created_at'])) }}; order.user = {{ Js::from($o->user ? ['name' => $o->user->name] : null) }}; orderItems = {{ Js::from($o->items->map(fn($line) => ['id' => $line->id, 'name' => $line->item?->name ?? 'Unknown', 'price' => $line->price, 'quantity' => $line->quantity, 'subtotal' => $line->subtotal])) }}; showDetail = true"
                                class="px-3 py-1 text-xs bg-blue-100 hover:bg-blue-200 text-blue-800 rounded dark:bg-blue-900 dark:text-blue-200">
                                View
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="px-6 py-8 text-center text-gray-400">No orders found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $orders->withQueryString()->links() }}
        </div>
        @endif
    </div>

    {{-- Order Detail Modal --}}
    <div x-show="showDetail" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        @keydown.escape.window="showDetail = false; order = null; orderItems = []">

        <div @click.stop class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                    Order Detail <span x-text="order ? '#' + order.id : ''"></span>
                </h3>
                <button @click="showDetail = false; order = null; orderItems = []"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>

            <template x-if="order">
                <div class="p-4 space-y-4">
                    {{-- Order Meta --}}
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div><span class="text-gray-500 dark:text-gray-400">Cashier:</span>
                            <span class="ml-1 font-medium dark:text-white" x-text="order.user ? order.user.name : 'Guest'"></span></div>
                        <div><span class="text-gray-500 dark:text-gray-400">Payment:</span>
                            <span class="ml-1 font-medium dark:text-white capitalize" x-text="order.payment_method"></span></div>
                        <div><span class="text-gray-500 dark:text-gray-400">Status:</span>
                            <span class="ml-1 font-medium dark:text-white capitalize" x-text="order.status"></span></div>
                        <div><span class="text-gray-500 dark:text-gray-400">Date:</span>
                            <span class="ml-1 font-medium dark:text-white" x-text="order.created_at"></span></div>
                    </div>

                    {{-- Items Table --}}
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-500 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-2 text-left">Item</th>
                                <th class="px-4 py-2 text-right">Price</th>
                                <th class="px-4 py-2 text-right">Qty</th>
                                <th class="px-4 py-2 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <template x-for="line in orderItems" :key="line.id">
                                <tr>
                                    <td class="px-4 py-2 dark:text-gray-300" x-text="line.name"></td>
                                    <td class="px-4 py-2 text-right dark:text-gray-300" x-text="'Rp ' + Number(line.price).toLocaleString('id-ID')"></td>
                                    <td class="px-4 py-2 text-right dark:text-gray-300" x-text="line.quantity"></td>
                                    <td class="px-4 py-2 text-right font-medium dark:text-white" x-text="'Rp ' + Number(line.subtotal).toLocaleString('id-ID')"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>

                    {{-- Totals --}}
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-3 space-y-1 text-sm">
                        <div class="flex justify-between dark:text-gray-300">
                            <span>Subtotal</span>
                            <span x-text="'Rp ' + Number(order.subtotal).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between text-red-500">
                            <span>Discount</span>
                            <span x-text="'-Rp ' + Number(order.discount).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between text-yellow-600">
                            <span>Tax</span>
                            <span x-text="'+Rp ' + Number(order.tax).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between font-bold text-base dark:text-white border-t border-gray-200 dark:border-gray-700 pt-1">
                            <span>Total</span>
                            <span x-text="'Rp ' + Number(order.total).toLocaleString('id-ID')"></span>
                        </div>
                    </div>
                </div>
            </template>

            <div class="flex justify-end p-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button" @click="showDetail = false; order = null; orderItems = []"
                    class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg">
                    Close
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
