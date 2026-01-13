<x-layouts.gallery
    :title="$artwork->title . ' - Art Gallery'"
    :description="Str::limit($artwork->description, 160)"
    ogType="article"
    :ogImage="$artwork->hasMedia('artworks') ? $artwork->getFirstMediaUrl('artworks', 'medium') : null">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
                <!-- Breadcrumb -->
                <nav class="mb-8" aria-label="Breadcrumb">
                    <ol class="flex items-center gap-2 text-sm">
                        <li>
                            <a href="{{ route('gallery.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                                Gallery
                            </a>
                        </li>
                        <li>
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </li>
                        <li class="text-gray-900 dark:text-white font-medium">
                            {{ $artwork->title }}
                        </li>
                    </ol>
                </nav>

                <!-- Artwork Detail -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
                    <!-- Image Section -->
                    <div>
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden"
                             x-data="{ lightboxOpen: false }">
                            @if($artwork->hasMedia('artworks'))
                                <div class="aspect-square bg-gray-200 dark:bg-gray-700 cursor-pointer"
                                     @click="lightboxOpen = true">
                                    <img src="{{ $artwork->getFirstMediaUrl('artworks', 'medium') }}"
                                         srcset="{{ $artwork->getFirstMediaUrl('artworks', 'thumbnail') }} 400w,
                                                 {{ $artwork->getFirstMediaUrl('artworks', 'medium') }} 800w"
                                         sizes="(max-width: 1024px) 100vw, 50vw"
                                         alt="{{ $artwork->title }} by {{ $artwork->artist_name }}"
                                         loading="eager"
                                         class="w-full h-full object-contain hover:opacity-90 transition-opacity">
                                </div>

                                <!-- Lightbox -->
                                <div x-show="lightboxOpen"
                                     x-cloak
                                     @keydown.escape.window="lightboxOpen = false"
                                     class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-90 p-4"
                                     @click.self="lightboxOpen = false">
                                    <button @click="lightboxOpen = false"
                                            class="absolute top-4 right-4 text-white hover:text-gray-300 transition-colors">
                                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                    <img src="{{ $artwork->getFirstMediaUrl('artworks') }}"
                                         alt="{{ $artwork->title }} by {{ $artwork->artist_name }}"
                                         class="max-h-full max-w-full object-contain">
                                </div>
                            @else
                                <div class="aspect-square bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                    <svg class="w-24 h-24 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif

                            @if($artwork->hasMedia('artworks'))
                                <div class="p-4 bg-gray-50 dark:bg-gray-900">
                                    <p class="text-xs text-gray-600 dark:text-gray-400 flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                        </svg>
                                        Click image to view full size
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Details Section -->
                    <div>
                        <div class="space-y-6">
                            <div>
                                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                                    {{ $artwork->title }}
                                </h1>
                                <p class="text-xl text-gray-600 dark:text-gray-400">
                                    by {{ $artwork->artist_name }}
                                </p>
                            </div>

                            @if($artwork->description)
                                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-3">
                                        Description
                                    </h2>
                                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                                        {{ $artwork->description }}
                                    </p>
                                </div>
                            @endif

                            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                                <h2 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-3">
                                    Details
                                </h2>
                                <dl class="space-y-3">
                                    @if($artwork->medium)
                                        <div class="flex justify-between">
                                            <dt class="text-gray-600 dark:text-gray-400">Medium</dt>
                                            <dd class="text-gray-900 dark:text-white font-medium">{{ $artwork->medium }}</dd>
                                        </div>
                                    @endif
                                    @if($artwork->category)
                                        <div class="flex justify-between">
                                            <dt class="text-gray-600 dark:text-gray-400">Category</dt>
                                            <dd class="text-gray-900 dark:text-white font-medium">{{ $artwork->category->name }}</dd>
                                        </div>
                                    @endif
                                    @if($artwork->published_at)
                                        <div class="flex justify-between">
                                            <dt class="text-gray-600 dark:text-gray-400">Published</dt>
                                            <dd class="text-gray-900 dark:text-white font-medium">{{ $artwork->published_at->format('M d, Y') }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>

                            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                                <a href="{{ route('gallery.index') }}"
                                   class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                    Back to Gallery
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</x-layouts.gallery>
