@extends('layouts.app')

@section('title', 'Categories - Mini POS')
@section('breadcrumb', 'Categories')

@section('content')
<div x-data="{ showModal: false, editing: null }" class="space-y-4">

    {{-- Header --}}
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold text-gray-800 dark:text-white">Categories</h1>
        <button @click="editing = null; showModal = true"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">
            + Add Category
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
                        <th class="px-6 py-3">Description</th>
                        <th class="px-6 py-3">Items</th>
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($categories as $category)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-3">{{ $loop->iteration }}</td>
                        <td class="px-6 py-3 font-medium">{{ $category->name }}</td>
                        <td class="px-6 py-3 text-gray-400">{{ $category->description ?? '-' }}</td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full dark:bg-blue-900 dark:text-blue-200">
                                {{ $category->items_count }} items
                            </span>
                        </td>
                        <td class="px-6 py-3 flex gap-2">
                            <button @click="editing = {{ Js::from($category) }}; showModal = true"
                                class="px-3 py-1 text-xs bg-yellow-100 hover:bg-yellow-200 text-yellow-800 rounded dark:bg-yellow-900 dark:text-yellow-200">
                                Edit
                            </button>
                            <form method="POST" action="{{ route('categories.destroy', $category) }}"
                                onsubmit="return confirm('Delete this category?')">
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
                        <td colspan="5" class="px-6 py-8 text-center text-gray-400">No categories found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($categories->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $categories->links() }}
        </div>
        @endif
    </div>

    {{-- Create / Edit Modal --}}
    @include('categories._category-modal')

                <button @click="showModal = false; editing = null"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>

            <form method="POST"
                :action="editing ? '/categories/' + editing.id : '{{ route('categories.store') }}'">
                @csrf
                <input type="hidden" name="_method" :value="editing ? 'PUT' : 'POST'">

                <div class="p-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" :value="editing ? editing.name : ''"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                        <textarea name="description" rows="3"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            x-effect="$el.value = editing ? (editing.description ?? '') : ''"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 p-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" @click="showModal = false; editing = null"
                        class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm text-white bg-blue-600 hover:bg-blue-700 rounded-lg">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
