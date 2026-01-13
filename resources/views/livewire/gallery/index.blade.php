<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Art Gallery</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">Explore our curated collection of artworks</p>
    </div>

    <!-- Category Filter -->
    <div class="mb-8">
        <div class="flex flex-wrap gap-3">
            <button wire:click="$set('categoryId', null)"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $categoryId === null ? 'bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-300 dark:border-gray-600' }}">
                All Artworks
            </button>
            @foreach($categories as $category)
                <button wire:click="$set('categoryId', {{ $category->id }})"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $categoryId === $category->id ? 'bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-300 dark:border-gray-600' }}">
                    {{ $category->name }}
                    <span class="ml-1 text-xs opacity-75">({{ $category->artworks_count }})</span>
                </button>
            @endforeach
        </div>
    </div>

    <!-- Artworks Grid -->
    @if($artworks->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
            @foreach($artworks as $artwork)
                <x-artwork-card :artwork="$artwork" />
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $artworks->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-16">
            <svg class="mx-auto h-24 w-24 text-gray-400 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No artworks found</h3>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                @if($categoryId)
                    Try selecting a different category or view all artworks.
                @else
                    There are no published artworks at this time.
                @endif
            </p>
            @if($categoryId)
                <button wire:click="$set('categoryId', null)"
                        class="mt-4 px-4 py-2 bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 rounded-lg text-sm font-medium hover:bg-gray-800 dark:hover:bg-gray-200 transition-colors">
                    View All Artworks
                </button>
            @endif
        </div>
    @endif
</div>
