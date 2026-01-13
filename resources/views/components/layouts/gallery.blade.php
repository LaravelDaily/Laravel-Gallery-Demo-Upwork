<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Art Gallery' }}</title>
    @if(isset($description))
        <meta name="description" content="{{ $description }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full antialiased bg-gray-50 dark:bg-gray-900">
    <div class="min-h-full flex flex-col">
        <!-- Header -->
        <header class="bg-white dark:bg-gray-800 shadow-sm sticky top-0 z-40">
            <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between items-center">
                    <!-- Logo/Brand -->
                    <div class="flex-shrink-0">
                        <a href="/" class="text-xl font-bold text-gray-900 dark:text-white hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                            Art Gallery
                        </a>
                    </div>

                    <!-- Desktop Navigation -->
                    <div class="hidden sm:flex sm:items-center sm:gap-6">
                        <a href="/" class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                            Gallery
                        </a>
                    </div>

                    <!-- Mobile menu button -->
                    <div class="flex sm:hidden">
                        <button type="button"
                                x-data="{ open: false }"
                                @click="open = !open"
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <span class="sr-only">Open main menu</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Mobile menu -->
                <div x-data="{ open: false }" x-show="open" @click.away="open = false" class="sm:hidden">
                    <div class="space-y-1 pb-3 pt-2">
                        <a href="/" class="block px-3 py-2 text-base font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                            Gallery
                        </a>
                    </div>
                </div>
            </nav>
        </header>

        <!-- Main Content -->
        <main class="flex-1">
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-auto">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        &copy; {{ date('Y') }} Art Gallery. All rights reserved.
                    </p>
                    <div class="flex items-center gap-4">
                        <a href="mailto:contact@artgallery.com" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                            Contact Us
                        </a>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    @livewireScripts
</body>
</html>
