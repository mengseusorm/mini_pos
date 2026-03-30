{{-- Add Movement Modal --}}
<div x-show="showModal" x-transition.opacity
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    @keydown.escape.window="showModal = false">

    <div @click.stop class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="flex justify-between items-center p-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Record Stock Movement</h3>
            <button @click="showModal = false"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('stock.store') }}">
            @csrf
            <div class="p-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Item <span class="text-red-500">*</span></label>
                    <select name="item_id" required
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select item</option>
                        @foreach($items as $item)
                        <option value="{{ $item->id }}">{{ $item->name }} (Stock: {{ $item->stock }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type <span class="text-red-500">*</span></label>
                        <select name="type" required
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="in">Stock In</option>
                            <option value="out">Stock Out</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quantity <span class="text-red-500">*</span></label>
                        <input type="number" name="quantity" min="1" required
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Note</label>
                    <textarea name="note" rows="2"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
            </div>

            <div class="flex justify-end gap-3 p-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button" @click="showModal = false"
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
