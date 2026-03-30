@extends('layouts.app')

@section('title', 'Items - Mini POS')
@section('breadcrumb', 'Items')

@section('content')
<div x-data="{ showModal: false, editing: null }" class="space-y-4">

    {{-- Header + Search --}}
    <div class="flex flex-col sm:flex-row justify-between gap-3">
        <form method="GET" action="{{ route('items.index') }}" class="flex gap-2 flex-wrap">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Search name / barcode..."
                class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 w-56">
            <select name="category_id"
                class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm rounded-lg">
                Filter
            </button>
            @if(request('search') || request('category_id'))
            <a href="{{ route('items.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm rounded-lg">
                Clear
            </a>
            @endif
        </form>
        <button type="button" @click="editing = null; showModal = true"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg whitespace-nowrap">
            + Add Item
        </button>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600 dark:text-gray-300">
                <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-3">#</th>
                        <th class="px-6 py-3">Name</th>
                        <th class="px-6 py-3">Category</th>
                        <th class="px-6 py-3">Barcode</th>
                        <th class="px-6 py-3">Price</th>
                        <th class="px-6 py-3">Stock</th>
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($items as $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-3">{{ $loop->iteration }}</td>
                        <td class="px-6 py-3 font-medium">{{ $item->name }}</td>
                        <td class="px-6 py-3 text-gray-400">{{ $item->category?->name ?? '-' }}</td>
                        <td class="px-6 py-3 font-mono text-xs">{{ $item->barcode ?? '-' }}</td>
                        <td class="px-6 py-3 font-semibold">{{ $currency }} {{ number_format($item->price) }}</td>
                        <td class="px-6 py-3">
                            @if($item->stock <= 5)
                                <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full dark:bg-red-900 dark:text-red-200">{{ $item->stock }}</span>
                            @elseif($item->stock <= 20)
                                <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full dark:bg-yellow-900 dark:text-yellow-200">{{ $item->stock }}</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full dark:bg-green-900 dark:text-green-200">{{ $item->stock }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 flex gap-2">
                            <button type="button" @click="editing = {{ Js::from($item->only(['id','name','category_id','barcode','price','stock','description'])) }}; showModal = true"
                                class="px-3 py-1 text-xs bg-yellow-100 hover:bg-yellow-200 text-yellow-800 rounded dark:bg-yellow-900 dark:text-yellow-200">
                                Edit
                            </button>
                            <form method="POST" action="{{ route('items.destroy', $item) }}"
                                onsubmit="return confirm('Delete this item?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="px-3 py-1 text-xs bg-red-100 hover:bg-red-200 text-red-800 rounded dark:bg-red-900 dark:text-red-200">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-400">No items found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $items->withQueryString()->links() }}
        </div>
        @endif
    </div>

    {{-- Create / Edit Modal --}}
    @include('items._item-modal')
</div>
@endsection
