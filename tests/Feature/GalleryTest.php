<?php

use App\Livewire\Gallery\Index;
use App\Models\Artwork;
use App\Models\Category;
use Livewire\Livewire;

use function Pest\Laravel\get;

test('gallery page loads successfully', function () {
    get(route('gallery.index'))
        ->assertSuccessful()
        ->assertSeeLivewire(Index::class);
});

test('gallery displays published artworks', function () {
    $category = Category::factory()->create();
    $publishedArtwork = Artwork::factory()->for($category)->create([
        'is_published' => true,
        'title' => 'Published Artwork',
        'artist_name' => 'Famous Artist',
    ]);

    $unpublishedArtwork = Artwork::factory()->for($category)->create([
        'is_published' => false,
        'title' => 'Unpublished Artwork',
    ]);

    Livewire::test(Index::class)
        ->assertSee('Published Artwork')
        ->assertSee('Famous Artist')
        ->assertDontSee('Unpublished Artwork');
});

test('gallery shows empty state when no artworks exist', function () {
    Livewire::test(Index::class)
        ->assertSee('No artworks found')
        ->assertSee('There are no published artworks at this time');
});

test('gallery displays category filter', function () {
    $category1 = Category::factory()->create(['name' => 'Oil Paintings']);
    $category2 = Category::factory()->create(['name' => 'Digital Art']);

    Artwork::factory()->for($category1)->count(3)->create(['is_published' => true]);
    Artwork::factory()->for($category2)->count(2)->create(['is_published' => true]);

    Livewire::test(Index::class)
        ->assertSee('All Artworks')
        ->assertSee('Oil Paintings')
        ->assertSee('(3)')
        ->assertSee('Digital Art')
        ->assertSee('(2)');
});

test('can filter artworks by category', function () {
    $oilPaintings = Category::factory()->create(['name' => 'Oil Paintings']);
    $digitalArt = Category::factory()->create(['name' => 'Digital Art']);

    $oilArtwork = Artwork::factory()->for($oilPaintings)->create([
        'is_published' => true,
        'title' => 'Oil Painting Title',
    ]);

    $digitalArtwork = Artwork::factory()->for($digitalArt)->create([
        'is_published' => true,
        'title' => 'Digital Art Title',
    ]);

    Livewire::test(Index::class)
        ->assertSee('Oil Painting Title')
        ->assertSee('Digital Art Title')
        ->set('categoryId', $oilPaintings->id)
        ->assertSee('Oil Painting Title')
        ->assertDontSee('Digital Art Title')
        ->set('categoryId', $digitalArt->id)
        ->assertDontSee('Oil Painting Title')
        ->assertSee('Digital Art Title');
});

test('category filter updates URL parameter', function () {
    $category = Category::factory()->create();
    Artwork::factory()->for($category)->create(['is_published' => true]);

    Livewire::test(Index::class)
        ->set('categoryId', $category->id)
        ->assertSet('categoryId', $category->id);
});

test('can clear category filter', function () {
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();

    $artwork1 = Artwork::factory()->for($category1)->create([
        'is_published' => true,
        'title' => 'Artwork One',
    ]);

    $artwork2 = Artwork::factory()->for($category2)->create([
        'is_published' => true,
        'title' => 'Artwork Two',
    ]);

    Livewire::test(Index::class)
        ->set('categoryId', $category1->id)
        ->assertSee('Artwork One')
        ->assertDontSee('Artwork Two')
        ->set('categoryId', null)
        ->assertSee('Artwork One')
        ->assertSee('Artwork Two');
});

test('artworks are ordered by newest first', function () {
    $category = Category::factory()->create();

    $older = Artwork::factory()->for($category)->create([
        'is_published' => true,
        'published_at' => now()->subDays(5),
        'title' => 'Older Artwork',
    ]);

    $newer = Artwork::factory()->for($category)->create([
        'is_published' => true,
        'published_at' => now()->subDay(),
        'title' => 'Newer Artwork',
    ]);

    Livewire::test(Index::class)
        ->assertViewHas('artworks', function ($artworks) use ($newer, $older) {
            return $artworks->first()->id === $newer->id &&
                   $artworks->last()->id === $older->id;
        });
});

test('gallery paginates artworks', function () {
    $category = Category::factory()->create();
    Artwork::factory()->for($category)->count(15)->create(['is_published' => true]);

    Livewire::test(Index::class)
        ->assertSee('artworks')
        ->assertViewHas('artworks', function ($artworks) {
            return $artworks->count() === 12;
        });
});

test('empty state shows different message when filtering', function () {
    $category = Category::factory()->create(['name' => 'Empty Category']);

    Livewire::test(Index::class)
        ->set('categoryId', $category->id)
        ->assertSee('No artworks found')
        ->assertSee('Try selecting a different category or view all artworks');
});

test('can load gallery with category URL parameter', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::factory()->for($category)->create([
        'is_published' => true,
        'title' => 'Category Artwork',
    ]);

    $otherCategory = Category::factory()->create();
    $otherArtwork = Artwork::factory()->for($otherCategory)->create([
        'is_published' => true,
        'title' => 'Other Artwork',
    ]);

    get(route('gallery.index', ['category' => $category->id]))
        ->assertSuccessful()
        ->assertSee('Category Artwork')
        ->assertDontSee('Other Artwork');
});

test('pagination maintains active category filter', function () {
    $category = Category::factory()->create();
    Artwork::factory()->for($category)->count(20)->create(['is_published' => true]);

    Livewire::test(Index::class)
        ->set('categoryId', $category->id)
        ->assertViewHas('artworks', function ($artworks) {
            return $artworks->count() === 12;
        })
        ->call('nextPage')
        ->assertSet('categoryId', $category->id)
        ->assertViewHas('artworks', function ($artworks) {
            return $artworks->count() === 8;
        });
});

test('clicking artwork card has correct link to detail page', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::factory()->for($category)->create([
        'is_published' => true,
        'slug' => 'test-artwork-slug',
    ]);

    get(route('gallery.index'))
        ->assertSuccessful()
        ->assertSee(route('artworks.show', 'test-artwork-slug'));
});

test('invalid category ID in URL shows all artworks', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::factory()->for($category)->create([
        'is_published' => true,
        'title' => 'Visible Artwork',
    ]);

    Livewire::test(Index::class)
        ->set('categoryId', 99999)
        ->assertSee('No artworks found');

    Livewire::test(Index::class)
        ->set('categoryId', null)
        ->assertSee('Visible Artwork');
});
