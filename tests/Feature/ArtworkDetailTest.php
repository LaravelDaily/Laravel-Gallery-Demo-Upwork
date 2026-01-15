<?php

use App\Models\Artwork;
use App\Models\Category;

use function Pest\Laravel\get;

test('can view published artwork detail page', function () {
    $category = Category::factory()->create(['name' => 'Oil Paintings']);
    $artwork = Artwork::factory()->for($category)->create([
        'is_published' => true,
        'title' => 'Beautiful Sunset',
        'artist_name' => 'John Doe',
        'description' => 'A stunning sunset over the ocean',
        'medium' => 'Oil on Canvas',
    ]);

    get(route('artworks.show', $artwork->slug))
        ->assertSuccessful()
        ->assertSee('Beautiful Sunset')
        ->assertSee('John Doe')
        ->assertSee('A stunning sunset over the ocean')
        ->assertSee('Oil on Canvas')
        ->assertSee('Oil Paintings');
});

test('cannot view unpublished artwork', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::factory()->for($category)->create([
        'is_published' => false,
        'title' => 'Unpublished Artwork',
    ]);

    get(route('artworks.show', $artwork->slug))
        ->assertNotFound();
});

test('returns 404 for non-existent artwork', function () {
    get(route('artworks.show', 'non-existent-slug'))
        ->assertNotFound();
});

test('artwork detail page displays breadcrumb navigation', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::factory()->for($category)->create([
        'is_published' => true,
        'title' => 'Test Artwork',
    ]);

    get(route('artworks.show', $artwork->slug))
        ->assertSee('Gallery')
        ->assertSee('Test Artwork');
});

test('artwork detail page shows back to gallery link', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::factory()->for($category)->create([
        'is_published' => true,
    ]);

    get(route('artworks.show', $artwork->slug))
        ->assertSee('Back to Gallery')
        ->assertSee(route('gallery.index'));
});

test('artwork detail page displays published date when available', function () {
    $category = Category::factory()->create();
    $publishedDate = now()->subDays(5);
    $artwork = Artwork::factory()->for($category)->create([
        'is_published' => true,
        'published_at' => $publishedDate,
    ]);

    get(route('artworks.show', $artwork->slug))
        ->assertSee($publishedDate->format('M d, Y'));
});

test('artwork detail page handles missing description gracefully', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::factory()->for($category)->create([
        'is_published' => true,
        'description' => null,
    ]);

    get(route('artworks.show', $artwork->slug))
        ->assertSuccessful()
        ->assertDontSee('Description');
});

test('artwork detail page handles missing medium gracefully', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::factory()->for($category)->create([
        'is_published' => true,
        'medium' => null,
    ]);

    get(route('artworks.show', $artwork->slug))
        ->assertSuccessful();
});

test('artwork detail page shows placeholder when no image', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::factory()->for($category)->create([
        'is_published' => true,
    ]);

    get(route('artworks.show', $artwork->slug))
        ->assertSuccessful()
        ->assertDontSee('Click image to view full size');
});

test('artwork detail page meta tags include title', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::factory()->for($category)->create([
        'is_published' => true,
        'title' => 'Unique Artwork Title',
    ]);

    get(route('artworks.show', $artwork->slug))
        ->assertSee('Unique Artwork Title - Art Gallery', false)
        ->assertSuccessful();
});

test('artwork can be accessed by slug', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::factory()->for($category)->create([
        'is_published' => true,
        'title' => 'Test Artwork',
        'slug' => 'test-artwork-slug',
    ]);

    get(route('artworks.show', 'test-artwork-slug'))
        ->assertSuccessful()
        ->assertSee('Test Artwork');
});

test('numeric ID in URL returns 404', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::factory()->for($category)->create([
        'is_published' => true,
    ]);

    get('/artworks/'.$artwork->id)
        ->assertNotFound();
});
