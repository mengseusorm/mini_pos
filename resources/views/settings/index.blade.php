@extends('layouts.app')

@section('title', 'Settings - Mini POS')
@section('breadcrumb', 'Settings')

@section('content')
<div x-data="settingsPage()" x-init="load()">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Settings</h1>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div x-show="loading" class="flex justify-center py-8">
            <svg class="animate-spin w-8 h-8 text-blue-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
        </div>

        <form @submit.prevent="save()" x-show="!loading" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Store Name</label>
                    <input type="text" x-model="form.store_name"
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Phone</label>
                    <input type="text" x-model="form.phone"
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
                <div class="md:col-span-2">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Address</label>
                    <textarea x-model="form.address" rows="2"
                              class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                </div>
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Currency</label>
                    <select x-model="form.currency"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="USD">USD ($)</option>
                        <option value="EUR">EUR (€)</option>
                        <option value="GBP">GBP (£)</option>
                        <option value="THB">THB (฿)</option>
                        <option value="JPY">JPY (¥)</option>
                    </select>
                </div>
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tax Rate (%)</label>
                    <input type="number" step="0.01" min="0" max="100" x-model="form.tax_rate"
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
                <div class="md:col-span-2">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Receipt Footer</label>
                    <textarea x-model="form.receipt_footer" rows="2" placeholder="Thank you for your purchase!"
                              class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                </div>
            </div>

            <div x-show="saveError" class="p-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-700 dark:text-red-400" x-text="saveError"></div>
            <div x-show="saveSuccess" class="p-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-700 dark:text-green-400">Settings saved successfully!</div>

            <div class="flex gap-3">
                <button type="submit" :disabled="saving"
                        class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 disabled:opacity-50">
                    <span x-show="saving">Saving...</span><span x-show="!saving">Save Settings</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function settingsPage() {
    return {
        form: { store_name: '', phone: '', address: '', currency: 'USD', tax_rate: 0, receipt_footer: '' },
        loading: true, saving: false, saveError: '', saveSuccess: false,

        async load() {
            this.loading = true;
            try {
                const res = await fetch('/api/settings', { headers: { 'Accept': 'application/json' } });
                if (res.ok) {
                    const data = await res.json();
                    const settings = data.data ?? data;
                    if (Array.isArray(settings)) {
                        settings.forEach(s => { if (s.key in this.form) this.form[s.key] = s.value; });
                    } else {
                        Object.assign(this.form, settings);
                    }
                }
            } catch (e) { console.error(e); }
            this.loading = false;
        },

        async save() {
            this.saving = true; this.saveError = ''; this.saveSuccess = false;
            try {
                const res = await fetch('/api/settings', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: JSON.stringify(this.form)
                });
                if (res.ok) { this.saveSuccess = true; setTimeout(() => this.saveSuccess = false, 3000); }
                else { const e = await res.json(); this.saveError = e.message ?? 'Error saving settings'; }
            } catch (e) { this.saveError = 'Network error'; }
            this.saving = false;
        }
    }
}
</script>
@endsection
