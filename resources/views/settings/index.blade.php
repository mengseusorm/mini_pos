@extends('layouts.app')

@section('title', 'Settings - Mini POS')
@section('breadcrumb', 'Settings')

@section('content')
<div class="max-w-2xl space-y-6">

    <h1 class="text-xl font-semibold text-gray-800 dark:text-white">Application Settings</h1>

    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data"
        class="bg-white dark:bg-gray-800 rounded-lg shadow divide-y divide-gray-200 dark:divide-gray-700">
        @csrf

        {{-- Site Info --}}
        <div class="p-6 space-y-4">
            <h2 class="font-semibold text-gray-700 dark:text-gray-200">Site Information</h2>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Site Name</label>
                <input type="text" name="site_name" value="{{ $settings['site_name'] ?? '' }}"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Site Description</label>
                <textarea name="site_description" rows="3"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">{{ $settings['site_description'] ?? '' }}</textarea>
            </div>
        </div>

        {{-- Appearance --}}
        <div class="p-6 space-y-4">
            <h2 class="font-semibold text-gray-700 dark:text-gray-200">Appearance</h2>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Theme Color</label>
                <select name="theme_color"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="blue" {{ ($settings['theme_color'] ?? 'blue') === 'blue' ? 'selected' : '' }}>Blue</option>
                    <option value="green" {{ ($settings['theme_color'] ?? '') === 'green' ? 'selected' : '' }}>Green</option>
                    <option value="purple" {{ ($settings['theme_color'] ?? '') === 'purple' ? 'selected' : '' }}>Purple</option>
                    <option value="red" {{ ($settings['theme_color'] ?? '') === 'red' ? 'selected' : '' }}>Red</option>
                </select>
            </div>

            {{-- Logo --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Logo</label>
                @if(!empty($logoUrl))
                <div class="mb-2">
                    <img src="{{ $logoUrl }}" alt="Site Logo" class="h-16 object-contain rounded border border-gray-200 dark:border-gray-600 p-1">
                    <p class="text-xs text-gray-400 mt-1">Current logo — upload below to replace</p>
                </div>
                @endif
                <input type="file" name="logo" accept="image/*"
                    class="w-full text-sm text-gray-600 dark:text-gray-300 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900 dark:file:text-blue-200">
            </div>
        </div>

        {{-- POS Settings --}}
        <div class="p-6 space-y-4">
            <h2 class="font-semibold text-gray-700 dark:text-gray-200">POS Settings</h2>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tax Rate (%)</label>
                    <input type="number" name="tax_rate" min="0" max="100" step="0.01"
                        value="{{ $settings['tax_rate'] ?? '0' }}"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Currency Symbol</label>
                    <input type="text" name="currency" value="{{ $settings['currency'] ?? 'Rp' }}"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        {{-- Save --}}
        <div class="px-6 py-4 flex justify-end">
            <button type="submit"
                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">
                Save Settings
            </button>
        </div>
    </form>

</div>
@endsection
