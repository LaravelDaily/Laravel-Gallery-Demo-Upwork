<?php

use App\Models\Artwork;
use App\Models\Category;
use Illuminate\Support\Facades\File;

use function Pest\Laravel\artisan;

beforeEach(function () {
    // Clean up sitemap file before each test
    $sitemapPath = public_path('sitemap.xml');
    if (File::exists($sitemapPath)) {
        File::delete($sitemapPath);
    }
});

afterEach(function () {
    // Clean up sitemap file after each test
    $sitemapPath = public_path('sitemap.xml');
    if (File::exists($sitemapPath)) {
        File::delete($sitemapPath);
    }
});

test('sitemap command executes successfully', function () {
    $category = Category::factory()->create();
    Artwork::factory()->for($category)->count(3)->create(['is_published' => true]);

    artisan('sitemap:generate')
        ->assertSuccessful()
        ->assertExitCode(0);
});

test('sitemap file is created in public folder', function () {
    artisan('sitemap:generate')->assertSuccessful();

    expect(File::exists(public_path('sitemap.xml')))->toBeTrue();
});

test('sitemap includes gallery index URL', function () {
    artisan('sitemap:generate')->assertSuccessful();

    $sitemap = File::get(public_path('sitemap.xml'));

    expect($sitemap)->toContain(route('gallery.index'));
});

test('sitemap includes published artworks', function () {
    $category = Category::factory()->create();
    $artwork1 = Artwork::factory()->for($category)->create([
        'is_published' => true,
        'slug' => 'test-artwork-one',
    ]);
    $artwork2 = Artwork::factory()->for($category)->create([
        'is_published' => true,
        'slug' => 'test-artwork-two',
    ]);

    artisan('sitemap:generate')->assertSuccessful();

    $sitemap = File::get(public_path('sitemap.xml'));

    expect($sitemap)
        ->toContain(route('artworks.show', $artwork1->slug))
        ->toContain(route('artworks.show', $artwork2->slug));
});

test('sitemap excludes unpublished artworks', function () {
    $category = Category::factory()->create();
    $publishedArtwork = Artwork::factory()->for($category)->create([
        'is_published' => true,
        'slug' => 'published-artwork',
    ]);
    $unpublishedArtwork = Artwork::factory()->for($category)->create([
        'is_published' => false,
        'slug' => 'unpublished-artwork',
    ]);

    artisan('sitemap:generate')->assertSuccessful();

    $sitemap = File::get(public_path('sitemap.xml'));

    expect($sitemap)
        ->toContain(route('artworks.show', $publishedArtwork->slug))
        ->not->toContain(route('artworks.show', $unpublishedArtwork->slug));
});

test('sitemap uses correct artwork slugs', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::factory()->for($category)->create([
        'is_published' => true,
        'slug' => 'my-custom-slug',
    ]);

    artisan('sitemap:generate')->assertSuccessful();

    $sitemap = File::get(public_path('sitemap.xml'));

    expect($sitemap)->toContain('my-custom-slug');
});

test('command outputs progress messages', function () {
    artisan('sitemap:generate')
        ->expectsOutput('Generating sitemap...')
        ->expectsOutput('Sitemap generated successfully!')
        ->assertSuccessful();
});

test('sitemap sets correct change frequency for gallery', function () {
    artisan('sitemap:generate')->assertSuccessful();

    $sitemap = File::get(public_path('sitemap.xml'));

    expect($sitemap)->toContain('<changefreq>daily</changefreq>');
});

test('sitemap sets correct change frequency for artworks', function () {
    $category = Category::factory()->create();
    Artwork::factory()->for($category)->create(['is_published' => true]);

    artisan('sitemap:generate')->assertSuccessful();

    $sitemap = File::get(public_path('sitemap.xml'));

    expect($sitemap)->toContain('<changefreq>weekly</changefreq>');
});

test('sitemap sets correct priority for gallery', function () {
    artisan('sitemap:generate')->assertSuccessful();

    $sitemap = File::get(public_path('sitemap.xml'));

    expect($sitemap)->toContain('<priority>1.0</priority>');
});

test('sitemap sets correct priority for artworks', function () {
    $category = Category::factory()->create();
    Artwork::factory()->for($category)->create(['is_published' => true]);

    artisan('sitemap:generate')->assertSuccessful();

    $sitemap = File::get(public_path('sitemap.xml'));

    expect($sitemap)->toContain('<priority>0.8</priority>');
});

test('sitemap includes last modification dates', function () {
    $category = Category::factory()->create();
    Artwork::factory()->for($category)->create(['is_published' => true]);

    artisan('sitemap:generate')->assertSuccessful();

    $sitemap = File::get(public_path('sitemap.xml'));

    expect($sitemap)->toContain('<lastmod>');
});
