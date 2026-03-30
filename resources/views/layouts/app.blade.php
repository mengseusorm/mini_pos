<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Mini POS'))</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 dark:bg-gray-900" x-data="{ sidebarOpen: false }">

    <!-- Sidebar -->
    <aside
        id="sidebar"
        class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full sm:translate-x-0"
        aria-label="Sidebar"
        :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen }"
    >
        <div class="h-full px-3 py-4 overflow-y-auto bg-gray-800 dark:bg-gray-800 flex flex-col">
            <!-- Logo -->
            <div class="flex items-center ps-2.5 mb-6">
                <span class="self-center text-xl font-semibold whitespace-nowrap text-white">
                    🛒 Mini POS
                </span>
            </div>

            <!-- Navigation -->
            <ul class="space-y-2 font-medium flex-1">
                <li>
                    <a href="{{ route('dashboard') }}"
                       class="flex items-center p-2 rounded-lg text-white hover:bg-gray-700 group {{ request()->routeIs('dashboard') ? 'bg-gray-700' : '' }}">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-white {{ request()->routeIs('dashboard') ? 'text-white' : '' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"/><path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"/></svg>
                        <span class="ms-3">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('pos.index') }}"
                       class="flex items-center p-2 rounded-lg text-white hover:bg-gray-700 group {{ request()->routeIs('pos.*') ? 'bg-gray-700' : '' }}">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-white {{ request()->routeIs('pos.*') ? 'text-white' : '' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4zM2 9v9a2 2 0 002 2h12a2 2 0 002-2V9H2zm5 3a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1z"/></svg>
                        <span class="ms-3">Point of Sale</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('orders.index') }}"
                       class="flex items-center p-2 rounded-lg text-white hover:bg-gray-700 group {{ request()->routeIs('orders.*') ? 'bg-gray-700' : '' }}">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-white {{ request()->routeIs('orders.*') ? 'text-white' : '' }}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/></svg>
                        <span class="ms-3">Orders</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('items.index') }}"
                       class="flex items-center p-2 rounded-lg text-white hover:bg-gray-700 group {{ request()->routeIs('items.*') ? 'bg-gray-700' : '' }}">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-white {{ request()->routeIs('items.*') ? 'text-white' : '' }}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm2 10a1 1 0 10-2 0v3a1 1 0 102 0v-3zm2-3a1 1 0 011 1v5a1 1 0 11-2 0v-5a1 1 0 011-1zm4-1a1 1 0 10-2 0v7a1 1 0 102 0V8z" clip-rule="evenodd"/></svg>
                        <span class="ms-3">Items</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('categories.index') }}"
                       class="flex items-center p-2 rounded-lg text-white hover:bg-gray-700 group {{ request()->routeIs('categories.*') ? 'bg-gray-700' : '' }}">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-white {{ request()->routeIs('categories.*') ? 'text-white' : '' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M5 3a1 1 0 000 2c5.523 0 10 4.477 10 10a1 1 0 102 0C17 8.373 11.627 3 5 3z"/><path d="M4 9a1 1 0 011-1 7 7 0 017 7 1 1 0 11-2 0 5 5 0 00-5-5 1 1 0 01-1-1zM3 15a2 2 0 114 0 2 2 0 01-4 0z"/></svg>
                        <span class="ms-3">Categories</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('stock.index') }}"
                       class="flex items-center p-2 rounded-lg text-white hover:bg-gray-700 group {{ request()->routeIs('stock.*') ? 'bg-gray-700' : '' }}">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-white {{ request()->routeIs('stock.*') ? 'text-white' : '' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4zM3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/></svg>
                        <span class="ms-3">Stock</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('reports.index') }}"
                       class="flex items-center p-2 rounded-lg text-white hover:bg-gray-700 group {{ request()->routeIs('reports.*') ? 'bg-gray-700' : '' }}">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-white {{ request()->routeIs('reports.*') ? 'text-white' : '' }}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V4a2 2 0 00-2-2H6zm1 2a1 1 0 000 2h6a1 1 0 100-2H7zm6 7a1 1 0 011 1v3a1 1 0 11-2 0v-3a1 1 0 011-1zm-3 3a1 1 0 100 2h.01a1 1 0 100-2H10zm-4 1a1 1 0 011-1h.01a1 1 0 110 2H7a1 1 0 01-1-1z" clip-rule="evenodd"/></svg>
                        <span class="ms-3">Reports</span>
                    </a>
                </li>
            </ul>

            <!-- Bottom items (admin only) -->
            <ul class="space-y-2 font-medium border-t border-gray-700 pt-4 mt-4">
                <li>
                    <a href="{{ route('users.index') }}"
                       class="flex items-center p-2 rounded-lg text-white hover:bg-gray-700 group {{ request()->routeIs('users.*') ? 'bg-gray-700' : '' }}">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-white {{ request()->routeIs('users.*') ? 'text-white' : '' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>
                        <span class="ms-3">Users</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('settings.index') }}"
                       class="flex items-center p-2 rounded-lg text-white hover:bg-gray-700 group {{ request()->routeIs('settings.*') ? 'bg-gray-700' : '' }}">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-white {{ request()->routeIs('settings.*') ? 'text-white' : '' }}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/></svg>
                        <span class="ms-3">Settings</span>
                    </a>
                </li>
                <!-- Logout -->
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center p-2 rounded-lg text-white hover:bg-red-700 group">
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/></svg>
                            <span class="ms-3">Logout</span>
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </aside>

    <!-- Mobile sidebar overlay -->
    <div
        class="fixed inset-0 z-30 bg-gray-900 bg-opacity-50 sm:hidden"
        x-show="sidebarOpen"
        x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="sidebarOpen = false"
    ></div>

    <!-- Main content -->
    <div class="sm:ml-64 min-h-screen flex flex-col">

        <!-- Top Navbar -->
        <nav class="bg-white border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700 px-4 py-3 flex items-center justify-between">
            <div class="flex items-center">
                <!-- Mobile menu button -->
                <button
                    type="button"
                    class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600"
                    @click="sidebarOpen = !sidebarOpen"
                >
                    <span class="sr-only">Open sidebar</span>
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h6a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/></svg>
                </button>
                <span class="ms-2 text-sm font-medium text-gray-500 dark:text-gray-400">
                    @yield('breadcrumb', '')
                </span>
            </div>

            <!-- User info -->
            <div class="flex items-center gap-3">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ auth()->user()?->name ?? 'Guest' }}
                </span>
                <span class="inline-flex items-center bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full dark:bg-blue-900 dark:text-blue-300">
                    {{ ucfirst(auth()->user()?->role ?? '') }}
                </span>
            </div>
        </nav>

        <!-- Page content -->
        <main class="flex-1 p-4 md:p-6">
            @if (session('success'))
                <div id="alert-success" class="flex items-center p-4 mb-4 text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
                    <svg class="shrink-0 w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                    <span class="sr-only">Info</span>
                    <div class="ms-3 text-sm font-medium">{{ session('success') }}</div>
                    <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-green-50 text-green-500 rounded-lg focus:ring-2 focus:ring-green-400 p-1.5 hover:bg-green-200 inline-flex items-center justify-center h-8 w-8 dark:bg-gray-800 dark:text-green-400 dark:hover:bg-gray-700" data-dismiss-target="#alert-success" aria-label="Close">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 14 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/></svg>
                    </button>
                </div>
            @endif

            @if (session('error'))
                <div id="alert-error" class="flex items-center p-4 mb-4 text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
                    <svg class="shrink-0 w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                    <span class="sr-only">Error</span>
                    <div class="ms-3 text-sm font-medium">{{ session('error') }}</div>
                    <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-red-50 text-red-500 rounded-lg focus:ring-2 focus:ring-red-400 p-1.5 hover:bg-red-200 inline-flex items-center justify-center h-8 w-8 dark:bg-gray-800 dark:text-red-400 dark:hover:bg-gray-700" data-dismiss-target="#alert-error" aria-label="Close">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 14 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/></svg>
                    </button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Alpine.js -->
    <script src="//unpkg.com/alpinejs" defer></script>
    <!-- Flowbite JS -->
    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
</body>
</html>
