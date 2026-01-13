<?php

namespace App\Livewire\Gallery;

use App\Models\Artwork;
use App\Models\Category;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.gallery')]
#[Title('Art Gallery - Explore Our Curated Collection')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'category')]
    public ?int $categoryId = null;

    public function updatedCategoryId(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $categories = Category::query()
            ->withCount('artworks')
            ->orderBy('name')
            ->get();

        $artworks = Artwork::query()
            ->with(['category', 'media'])
            ->where('is_published', true)
            ->when($this->categoryId, fn ($query) => $query->where('category_id', $this->categoryId))
            ->latest('published_at')
            ->latest('created_at')
            ->paginate(12);

        return view('livewire.gallery.index', [
            'categories' => $categories,
            'artworks' => $artworks,
            'description' => 'Browse our carefully curated collection of artworks including paintings, digital art, and sketches from talented artists.',
        ]);
    }
}
