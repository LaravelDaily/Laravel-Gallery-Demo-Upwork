<?php

use App\Models\Artwork;
use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

test('can attach image to artwork', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::factory()->for($category)->create();

    $artwork->addMedia(UploadedFile::fake()->image('test.jpg', 800, 600))
        ->toMediaCollection('artworks');

    expect($artwork->getFirstMedia('artworks'))->not->toBeNull();
});

test('thumbnail conversion generated on upload', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::factory()->for($category)->create();

    $artwork->addMedia(UploadedFile::fake()->image('test.jpg', 800, 600))
        ->toMediaCollection('artworks');

    $artwork->registerMediaConversions();

    $conversions = $artwork->mediaConversions;
    $thumbnailConversion = collect($conversions)->first(fn ($c) => $c->getName() === 'thumbnail');

    expect($thumbnailConversion)->not->toBeNull();
    expect($thumbnailConversion->getName())->toBe('thumbnail');
});

test('medium conversion generated on upload', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::factory()->for($category)->create();

    $artwork->addMedia(UploadedFile::fake()->image('test.jpg', 1200, 900))
        ->toMediaCollection('artworks');

    $artwork->registerMediaConversions();

    $conversions = $artwork->mediaConversions;
    $mediumConversion = collect($conversions)->first(fn ($c) => $c->getName() === 'medium');

    expect($mediumConversion)->not->toBeNull();
    expect($mediumConversion->getName())->toBe('medium');
});

test('original image preserved', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::factory()->for($category)->create();

    $artwork->addMedia(UploadedFile::fake()->image('original.jpg', 1920, 1080))
        ->toMediaCollection('artworks');

    $media = $artwork->getFirstMedia('artworks');

    expect($media)->not->toBeNull();
    expect($media->file_name)->toBe('original.jpg');
});

test('can upload JPEG image', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::factory()->for($category)->create();

    $artwork->addMedia(UploadedFile::fake()->image('photo.jpg', 640, 480))
        ->toMediaCollection('artworks');

    expect($artwork->getFirstMedia('artworks'))->not->toBeNull();
    expect($artwork->getFirstMedia('artworks')->mime_type)->toBe('image/jpeg');
});

test('can upload PNG image', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::factory()->for($category)->create();

    $artwork->addMedia(UploadedFile::fake()->image('graphic.png', 640, 480))
        ->toMediaCollection('artworks');

    expect($artwork->getFirstMedia('artworks'))->not->toBeNull();
    expect($artwork->getFirstMedia('artworks')->mime_type)->toBe('image/png');
});

test('can upload WebP image', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::factory()->for($category)->create();

    $collection = $artwork->getRegisteredMediaCollections()->firstWhere('name', 'artworks');

    expect($collection->acceptsMimeTypes)->toContain('image/webp');
});

test('can replace existing artwork image', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::factory()->for($category)->create();

    $artwork->addMedia(UploadedFile::fake()->image('first.jpg', 640, 480))
        ->toMediaCollection('artworks');

    $firstMediaId = $artwork->getFirstMedia('artworks')->id;

    $artwork->clearMediaCollection('artworks');
    $artwork->addMedia(UploadedFile::fake()->image('second.jpg', 800, 600))
        ->toMediaCollection('artworks');

    $artwork->refresh();

    expect($artwork->getFirstMedia('artworks')->file_name)->toBe('second.jpg');
    expect($artwork->getMedia('artworks'))->toHaveCount(1);
});

test('deleting artwork removes associated media', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::factory()->for($category)->create();

    $artwork->addMedia(UploadedFile::fake()->image('test.jpg', 640, 480))
        ->toMediaCollection('artworks');

    $mediaPath = $artwork->getFirstMedia('artworks')->getPath();
    $mediaId = $artwork->getFirstMedia('artworks')->id;

    $artwork->delete();

    expect(\Spatie\MediaLibrary\MediaCollections\Models\Media::find($mediaId))->toBeNull();
});

test('artwork image uses artworks collection', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::factory()->for($category)->create();

    $artwork->addMedia(UploadedFile::fake()->image('test.jpg', 640, 480))
        ->toMediaCollection('artworks');

    $media = $artwork->getFirstMedia('artworks');

    expect($media->collection_name)->toBe('artworks');
});

test('rejects non-image file types', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::factory()->for($category)->create();

    $this->expectException(\Spatie\MediaLibrary\MediaCollections\Exceptions\FileUnacceptableForCollection::class);

    $artwork->addMedia(UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'))
        ->toMediaCollection('artworks');
});

test('rejects GIF images', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::factory()->for($category)->create();

    $this->expectException(\Spatie\MediaLibrary\MediaCollections\Exceptions\FileUnacceptableForCollection::class);

    $artwork->addMedia(UploadedFile::fake()->create('animation.gif', 100, 'image/gif'))
        ->toMediaCollection('artworks');
});

test('rejects SVG images', function () {
    $category = Category::factory()->create();
    $artwork = Artwork::factory()->for($category)->create();

    $this->expectException(\Spatie\MediaLibrary\MediaCollections\Exceptions\FileUnacceptableForCollection::class);

    $artwork->addMedia(UploadedFile::fake()->create('vector.svg', 100, 'image/svg+xml'))
        ->toMediaCollection('artworks');
});
