@props(['artwork'])

<a href="{{ route('artworks.show', $artwork->slug) }}" class="group block bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md transition-shadow overflow-hidden">
    <!-- Artwork Image -->
    <div class="aspect-square bg-gray-200 dark:bg-gray-700 overflow-hidden">
        @if($artwork->hasMedia('artworks'))
            <img src="{{ $artwork->getFirstMediaUrl('artworks', 'thumbnail') }}"
                 srcset="{{ $artwork->getFirstMediaUrl('artworks', 'thumbnail') }} 400w,
                         {{ $artwork->getFirstMediaUrl('artworks', 'medium') }} 800w"
                 sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, (max-width: 1280px) 33vw, 25vw"
                 alt="{{ $artwork->title }} by {{ $artwork->artist_name }}"
                 loading="lazy"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
        @else
            <div class="w-full h-full flex items-center justify-center">
                <svg class="w-16 h-16 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        @endif
    </div>

    <!-- Artwork Info -->
    <div class="p-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors line-clamp-2">
            {{ $artwork->title }}
        </h3>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ $artwork->artist_name }}
        </p>
    </div>
</a>
