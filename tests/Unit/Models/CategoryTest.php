<?php

use App\Models\Artwork;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

test('category has many artworks relationship', function () {
    $category = Category::factory()
        ->has(Artwork::factory()->count(3))
        ->create();

    expect($category->artworks)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(3);
});

test('factory creates valid category', function () {
    $category = Category::factory()->create();

    expect($category)
        ->name->not->toBeNull()
        ->slug->not->toBeNull();
});

test('artworks count returns correct number', function () {
    $category = Category::factory()
        ->has(Artwork::factory()->count(5))
        ->create();

    $categoryWithCount = Category::withCount('artworks')->find($category->id);

    expect($categoryWithCount->artworks_count)->toBe(5);
});

test('slug auto-generates from name on create', function () {
    $category = Category::create([
        'name' => 'Oil Paintings',
    ]);

    expect($category->slug)->toBe('oil-paintings');
});

test('slug preserved when explicitly set', function () {
    $category = Category::create([
        'name' => 'Oil Paintings',
        'slug' => 'custom-category-slug',
    ]);

    expect($category->slug)->toBe('custom-category-slug');
});

test('deleting category cascades to delete artworks', function () {
    $category = Category::factory()
        ->has(Artwork::factory()->count(2))
        ->create();

    $artworkIds = $category->artworks->pluck('id')->toArray();

    expect(Artwork::whereIn('id', $artworkIds)->count())->toBe(2);

    $category->delete();

    expect(Artwork::whereIn('id', $artworkIds)->count())->toBe(0);
});

test('can delete category without artworks', function () {
    $category = Category::factory()->create();

    expect($category->delete())->toBeTrue();
    expect(Category::find($category->id))->toBeNull();
});

test('category artworks relationship is empty for new category', function () {
    $category = Category::factory()->create();

    expect($category->artworks)->toBeInstanceOf(Collection::class)->toBeEmpty();
});
