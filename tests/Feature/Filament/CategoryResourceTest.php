<?php

use App\Filament\Resources\Categories\CategoryResource;
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

test('can render category list page', function () {
    $this->get(CategoryResource::getUrl('index'))
        ->assertSuccessful();
});

test('can list categories', function () {
    $categories = Category::factory()->count(10)->create();

    Livewire::test(\App\Filament\Resources\Categories\Pages\ListCategories::class)
        ->assertCanSeeTableRecords($categories);
});

test('can render create category page', function () {
    $this->get(CategoryResource::getUrl('create'))
        ->assertSuccessful();
});

test('can create category', function () {
    $livewire = Livewire::test(\App\Filament\Resources\Categories\Pages\CreateCategory::class)
        ->fillForm([
            'name' => 'Oil Paintings',
            'slug' => 'oil-paintings',
        ])
        ->call('create')
        ->assertNotified();

    assertDatabaseHas(Category::class, [
        'name' => 'Oil Paintings',
        'slug' => 'oil-paintings',
    ]);

    $category = Category::where('slug', 'oil-paintings')->first();
    $livewire->assertRedirect(CategoryResource::getUrl('edit', ['record' => $category]));
});

test('can validate category input', function () {
    Livewire::test(\App\Filament\Resources\Categories\Pages\CreateCategory::class)
        ->fillForm([
            'name' => '',
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);
});

test('can validate unique slug', function () {
    $category = Category::factory()->create(['slug' => 'oil-paintings']);

    Livewire::test(\App\Filament\Resources\Categories\Pages\CreateCategory::class)
        ->fillForm([
            'name' => 'Oil Paintings',
            'slug' => 'oil-paintings',
        ])
        ->call('create')
        ->assertHasFormErrors(['slug' => 'unique']);
});

test('can render edit category page', function () {
    $category = Category::factory()->create();

    $this->get(CategoryResource::getUrl('edit', ['record' => $category]))
        ->assertSuccessful();
});

test('can edit category', function () {
    $category = Category::factory()->create();

    Livewire::test(\App\Filament\Resources\Categories\Pages\EditCategory::class, [
        'record' => $category->getRouteKey(),
    ])
        ->fillForm([
            'name' => 'Updated Name',
            'slug' => 'updated-name',
        ])
        ->call('save')
        ->assertNotified();

    expect($category->refresh())
        ->name->toBe('Updated Name')
        ->slug->toBe('updated-name');
});

test('can delete category', function () {
    $category = Category::factory()->create();

    Livewire::test(\App\Filament\Resources\Categories\Pages\ListCategories::class)
        ->callTableAction('delete', $category);

    assertDatabaseMissing(Category::class, [
        'id' => $category->id,
    ]);
});

test('cannot delete category with artworks', function () {
    $category = Category::factory()
        ->has(Artwork::factory()->count(3))
        ->create();

    Livewire::test(\App\Filament\Resources\Categories\Pages\ListCategories::class)
        ->callTableAction('delete', $category);

    assertDatabaseHas(Category::class, [
        'id' => $category->id,
    ]);
});

test('can search categories by name', function () {
    $categories = Category::factory()->count(10)->create();
    $firstCategory = $categories->first();

    Livewire::test(\App\Filament\Resources\Categories\Pages\ListCategories::class)
        ->searchTable($firstCategory->name)
        ->assertCanSeeTableRecords([$firstCategory])
        ->assertCanNotSeeTableRecords($categories->skip(1));
});

test('can sort categories by name', function () {
    $categories = Category::factory()->count(3)->create();

    Livewire::test(\App\Filament\Resources\Categories\Pages\ListCategories::class)
        ->sortTable('name')
        ->assertCanSeeTableRecords($categories->sortBy('name'), inOrder: true)
        ->sortTable('name', 'desc')
        ->assertCanSeeTableRecords($categories->sortByDesc('name'), inOrder: true);
});

test('can display artworks count', function () {
    $categories = Category::factory()->count(2)->create();
    $categories->first()->artworks()->saveMany(Artwork::factory()->count(5)->make());

    Livewire::test(\App\Filament\Resources\Categories\Pages\ListCategories::class)
        ->assertCanSeeTableRecords($categories);
});

test('can sort categories by artworks count', function () {
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();
    $category3 = Category::factory()->create();

    Artwork::factory()->count(5)->for($category1)->create();
    Artwork::factory()->count(2)->for($category2)->create();
    Artwork::factory()->count(8)->for($category3)->create();

    Livewire::test(\App\Filament\Resources\Categories\Pages\ListCategories::class)
        ->sortTable('artworks_count')
        ->assertCanSeeTableRecords([$category2, $category1, $category3], inOrder: true)
        ->sortTable('artworks_count', 'desc')
        ->assertCanSeeTableRecords([$category3, $category1, $category2], inOrder: true);
});

test('new category appears in artwork form dropdown', function () {
    $category = Category::factory()->create(['name' => 'New Test Category']);

    Livewire::test(\App\Filament\Resources\Artworks\Pages\CreateArtwork::class)
        ->assertFormFieldExists('category_id')
        ->assertSuccessful();

    $this->get(\App\Filament\Resources\Artworks\ArtworkResource::getUrl('create'))
        ->assertSuccessful()
        ->assertSee('New Test Category');
});

test('cannot create category with name exceeding 255 chars', function () {
    Livewire::test(\App\Filament\Resources\Categories\Pages\CreateCategory::class)
        ->fillForm([
            'name' => str_repeat('a', 256),
            'slug' => 'test-slug',
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'max']);
});

test('unauthenticated user cannot access category resource', function () {
    auth()->logout();

    $this->get(CategoryResource::getUrl('index'))
        ->assertRedirect(route('filament.admin.auth.login'));
});
