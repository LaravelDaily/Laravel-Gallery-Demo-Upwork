<?php

use App\Filament\Resources\Artworks\ArtworkResource;
use App\Models\Artwork;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('can render artwork list page', function () {
    $this->get(ArtworkResource::getUrl('index'))
        ->assertSuccessful();
});

test('can list artworks', function () {
    $artworks = Artwork::factory()->count(10)->create();

    Livewire::test(\App\Filament\Resources\Artworks\Pages\ListArtworks::class)
        ->assertCanSeeTableRecords($artworks);
});

test('can render create artwork page', function () {
    $this->get(ArtworkResource::getUrl('create'))
        ->assertSuccessful();
});

test('can create artwork', function () {
    $category = Category::factory()->create();

    $livewire = Livewire::test(\App\Filament\Resources\Artworks\Pages\CreateArtwork::class)
        ->fillForm([
            'title' => 'Starry Night',
            'slug' => 'starry-night',
            'artist_name' => 'Vincent van Gogh',
            'description' => 'A famous painting',
            'medium' => 'Oil on canvas',
            'category_id' => $category->id,
            'is_published' => true,
        ])
        ->call('create')
        ->assertNotified();

    assertDatabaseHas(Artwork::class, [
        'title' => 'Starry Night',
        'slug' => 'starry-night',
        'artist_name' => 'Vincent van Gogh',
        'is_published' => true,
    ]);
});

test('can validate artwork input', function () {
    Livewire::test(\App\Filament\Resources\Artworks\Pages\CreateArtwork::class)
        ->fillForm([
            'title' => '',
        ])
        ->call('create')
        ->assertHasFormErrors(['title' => 'required']);
});

test('can validate unique slug', function () {
    $artwork = Artwork::factory()->create(['slug' => 'starry-night']);

    Livewire::test(\App\Filament\Resources\Artworks\Pages\CreateArtwork::class)
        ->fillForm([
            'title' => 'Starry Night',
            'slug' => 'starry-night',
            'artist_name' => 'Artist',
            'category_id' => $artwork->category_id,
        ])
        ->call('create')
        ->assertHasFormErrors(['slug' => 'unique']);
});

test('can render edit artwork page', function () {
    $artwork = Artwork::factory()->create();

    $this->get(ArtworkResource::getUrl('edit', ['record' => $artwork]))
        ->assertSuccessful();
});

test('can edit artwork', function () {
    $artwork = Artwork::factory()->create();
    $newCategory = Category::factory()->create();

    Livewire::test(\App\Filament\Resources\Artworks\Pages\EditArtwork::class, [
        'record' => $artwork->getRouteKey(),
    ])
        ->fillForm([
            'title' => 'Updated Title',
            'slug' => 'updated-title',
            'artist_name' => 'Updated Artist',
            'category_id' => $newCategory->id,
        ])
        ->call('save')
        ->assertNotified();

    expect($artwork->refresh())
        ->title->toBe('Updated Title')
        ->slug->toBe('updated-title')
        ->artist_name->toBe('Updated Artist')
        ->category_id->toBe($newCategory->id);
});

test('can delete artwork', function () {
    $artwork = Artwork::factory()->create();

    Livewire::test(\App\Filament\Resources\Artworks\Pages\ListArtworks::class)
        ->callTableAction('delete', $artwork);

    assertDatabaseMissing(Artwork::class, [
        'id' => $artwork->id,
    ]);
});

test('can search artworks by title', function () {
    $artworks = Artwork::factory()->count(10)->create();
    $firstArtwork = $artworks->first();

    Livewire::test(\App\Filament\Resources\Artworks\Pages\ListArtworks::class)
        ->searchTable($firstArtwork->title)
        ->assertCanSeeTableRecords([$firstArtwork])
        ->assertCanNotSeeTableRecords($artworks->skip(1));
});

test('can search artworks by artist', function () {
    $artworks = Artwork::factory()->count(10)->create();
    $firstArtwork = $artworks->first();

    Livewire::test(\App\Filament\Resources\Artworks\Pages\ListArtworks::class)
        ->searchTable($firstArtwork->artist_name)
        ->assertCanSeeTableRecords([$firstArtwork]);
});

test('can filter artworks by category', function () {
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();

    $artwork1 = Artwork::factory()->create(['category_id' => $category1->id]);
    $artwork2 = Artwork::factory()->create(['category_id' => $category2->id]);

    Livewire::test(\App\Filament\Resources\Artworks\Pages\ListArtworks::class)
        ->filterTable('category', $category1->id)
        ->assertCanSeeTableRecords([$artwork1])
        ->assertCanNotSeeTableRecords([$artwork2]);
});

test('can filter artworks by published status', function () {
    $published = Artwork::factory()->create(['is_published' => true]);
    $draft = Artwork::factory()->create(['is_published' => false]);

    Livewire::test(\App\Filament\Resources\Artworks\Pages\ListArtworks::class)
        ->filterTable('is_published', '1')
        ->assertCanSeeTableRecords([$published])
        ->assertCanNotSeeTableRecords([$draft]);
});

test('can sort artworks by title', function () {
    $artworks = Artwork::factory()->count(3)->create();

    Livewire::test(\App\Filament\Resources\Artworks\Pages\ListArtworks::class)
        ->sortTable('title')
        ->assertCanSeeTableRecords($artworks->sortBy('title'), inOrder: true)
        ->sortTable('title', 'desc')
        ->assertCanSeeTableRecords($artworks->sortByDesc('title'), inOrder: true);
});

test('can toggle publish status', function () {
    $artwork = Artwork::factory()->create(['is_published' => false]);

    Livewire::test(\App\Filament\Resources\Artworks\Pages\ListArtworks::class)
        ->callTableAction('toggle_publish', $artwork);

    expect($artwork->refresh()->is_published)->toBeTrue();
});

test('can toggle unpublish status', function () {
    $artwork = Artwork::factory()->create(['is_published' => true]);

    Livewire::test(\App\Filament\Resources\Artworks\Pages\ListArtworks::class)
        ->callTableAction('toggle_publish', $artwork);

    expect($artwork->refresh()->is_published)->toBeFalse();
});

test('can bulk delete artworks', function () {
    $artworks = Artwork::factory()->count(3)->create();

    Livewire::test(\App\Filament\Resources\Artworks\Pages\ListArtworks::class)
        ->callTableBulkAction('delete', $artworks);

    foreach ($artworks as $artwork) {
        assertDatabaseMissing(Artwork::class, [
            'id' => $artwork->id,
        ]);
    }
});
