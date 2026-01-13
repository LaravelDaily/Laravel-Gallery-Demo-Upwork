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
