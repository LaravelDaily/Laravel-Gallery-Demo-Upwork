<?php

use App\Models\Artwork;
use App\Models\Category;

test('artwork belongs to category relationship', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::factory()->for($category)->create();

    expect($artwork->category)
        ->toBeInstanceOf(Category::class)
        ->id->toBe($category->id);
});

test('is_published cast to boolean', function () {
    $category = Category::factory()->create();

    $publishedArtwork = Artwork::factory()->for($category)->create(['is_published' => true]);
    $unpublishedArtwork = Artwork::factory()->for($category)->create(['is_published' => false]);

    expect($publishedArtwork->is_published)->toBeBool()->toBeTrue();
    expect($unpublishedArtwork->is_published)->toBeBool()->toBeFalse();
});

test('published_at cast to datetime', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::factory()->for($category)->create([
        'published_at' => now(),
    ]);

    expect($artwork->published_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('factory creates valid artwork', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::factory()->for($category)->create();

    expect($artwork)
        ->title->not->toBeNull()
        ->artist_name->not->toBeNull()
        ->category_id->toBe($category->id);
});

test('factory published state works', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::factory()->for($category)->published()->create();

    expect($artwork->is_published)->toBeTrue();
    expect($artwork->published_at)->not->toBeNull();
});

test('slug auto-generates from title on create', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::create([
        'title' => 'My Beautiful Artwork',
        'artist_name' => fake()->name(),
        'medium' => 'Oil on Canvas',
        'category_id' => $category->id,
        'is_published' => false,
    ]);

    expect($artwork->slug)->toBe('my-beautiful-artwork');
});

test('slug preserved when explicitly set', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::create([
        'title' => 'My Beautiful Artwork',
        'slug' => 'custom-slug-value',
        'artist_name' => fake()->name(),
        'medium' => 'Oil on Canvas',
        'category_id' => $category->id,
        'is_published' => false,
    ]);

    expect($artwork->slug)->toBe('custom-slug-value');
});

test('media collection artworks is registered', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::factory()->for($category)->create();

    $collections = $artwork->getRegisteredMediaCollections();
    $collectionNames = $collections->pluck('name')->toArray();

    expect($collectionNames)->toContain('artworks');
});

test('media collection accepts jpeg mime type', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::factory()->for($category)->create();

    $collection = $artwork->getRegisteredMediaCollections()->firstWhere('name', 'artworks');

    expect($collection->acceptsMimeTypes)->toContain('image/jpeg');
});

test('media collection accepts png mime type', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::factory()->for($category)->create();

    $collection = $artwork->getRegisteredMediaCollections()->firstWhere('name', 'artworks');

    expect($collection->acceptsMimeTypes)->toContain('image/png');
});

test('media collection accepts webp mime type', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::factory()->for($category)->create();

    $collection = $artwork->getRegisteredMediaCollections()->firstWhere('name', 'artworks');

    expect($collection->acceptsMimeTypes)->toContain('image/webp');
});

test('thumbnail conversion is registered', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::factory()->for($category)->create();

    // Trigger media conversion registration
    $artwork->registerMediaConversions();

    $conversions = $artwork->mediaConversions;
    $conversionNames = collect($conversions)->map(fn ($conversion) => $conversion->getName())->toArray();

    expect($conversionNames)->toContain('thumbnail');
});

test('medium conversion is registered', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::factory()->for($category)->create();

    // Trigger media conversion registration
    $artwork->registerMediaConversions();

    $conversions = $artwork->mediaConversions;
    $conversionNames = collect($conversions)->map(fn ($conversion) => $conversion->getName())->toArray();

    expect($conversionNames)->toContain('medium');
});
