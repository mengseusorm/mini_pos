@extends('layouts.app')

@section('title', 'Reports - Mini POS')
@section('breadcrumb', 'Reports')

@section('content')
<div class="space-y-4">

    {{-- Filter --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Report Type</label>
                <select name="type"
                    class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="daily" {{ $type === 'daily' ? 'selected' : '' }}>Daily Sales</option>
                    <option value="monthly" {{ $type === 'monthly' ? 'selected' : '' }}>Monthly Sales</option>
                    <option value="top_products" {{ $type === 'top_products' ? 'selected' : '' }}>Top Products</option>
                </select>
            </div>
            @if($type !== 'top_products')
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
            @endif
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg">
                Generate
            </button>
        </form>
    </div>

    {{-- Summary Cards (Daily / Monthly only) --}}
    @if($summary)
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Orders</p>
            <p class="text-3xl font-bold text-gray-800 dark:text-white">{{ $summary['total_orders'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Revenue</p>
            <p class="text-3xl font-bold text-gray-800 dark:text-white">{{ $currency }} {{ number_format($summary['revenue']) }}</p>
        </div>
    </div>
    @endif

    {{-- Report Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="font-semibold text-gray-800 dark:text-white">
                @if($type === 'daily') Daily Sales Report
                @elseif($type === 'monthly') Monthly Sales Report
                @else Top Products
                @endif
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600 dark:text-gray-300">
                <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-500 dark:text-gray-400">
                    <tr>
                        @if($type === 'daily')
                            <th class="px-6 py-3">Date</th>
                            <th class="px-6 py-3">Orders</th>
                            <th class="px-6 py-3">Revenue</th>
                        @elseif($type === 'monthly')
                            <th class="px-6 py-3">Month</th>
                            <th class="px-6 py-3">Orders</th>
                            <th class="px-6 py-3">Revenue</th>
                        @else
                            <th class="px-6 py-3">Product</th>
                            <th class="px-6 py-3">Qty Sold</th>
                            <th class="px-6 py-3">Revenue</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($data as $row)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        @if($type === 'daily')
                            <td class="px-6 py-3 font-medium">{{ $row->date }}</td>
                            <td class="px-6 py-3">{{ $row->total_orders }}</td>
                            <td class="px-6 py-3 font-semibold">{{ $currency }} {{ number_format($row->revenue) }}</td>
                        @elseif($type === 'monthly')
                            <td class="px-6 py-3 font-medium">{{ $row->month }}</td>
                            <td class="px-6 py-3">{{ $row->total_orders }}</td>
                            <td class="px-6 py-3 font-semibold">{{ $currency }} {{ number_format($row->revenue) }}</td>
                        @else
                            <td class="px-6 py-3 font-medium">{{ $row->name }}</td>
                            <td class="px-6 py-3">{{ $row->total_qty }}</td>
                            <td class="px-6 py-3 font-semibold">{{ $currency }} {{ number_format($row->total_revenue) }}</td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-gray-400">No data for selected period</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
