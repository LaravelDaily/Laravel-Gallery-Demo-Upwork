<?php

namespace App\Console\Commands;

use App\Models\Artwork;
use App\Models\Category;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SeedE2ETestData extends Command
{
    protected $signature = 'e2e:seed {scenario=default} {--fresh : Fresh migrate the database first}';

    protected $description = 'Seed test data for E2E tests';

    public function handle(): int
    {
        if ($this->option('fresh')) {
            $this->call('migrate:fresh');
        }

        $scenario = $this->argument('scenario');

        return match ($scenario) {
            'default' => $this->seedDefault(),
            'empty' => $this->seedEmpty(),
            'gallery' => $this->seedGallery(),
            'pagination' => $this->seedPagination(),
            'single-artwork' => $this->seedSingleArtwork(),
            'admin' => $this->seedAdmin(),
            default => $this->error("Unknown scenario: {$scenario}") ?? self::FAILURE,
        };
    }

    private function seedDefault(): int
    {
        $this->info('Seeding default test data...');

        $this->createAdminUser();

        $categories = [
            Category::create(['name' => 'Oil Paintings', 'slug' => 'oil-paintings']),
            Category::create(['name' => 'Digital Art', 'slug' => 'digital-art']),
            Category::create(['name' => 'Watercolors', 'slug' => 'watercolors']),
        ];

        foreach ($categories as $category) {
            Artwork::factory()
                ->for($category)
                ->count(4)
                ->published()
                ->create();
        }

        Artwork::factory()
            ->for($categories[0])
            ->create([
                'title' => 'Unpublished Artwork',
                'slug' => 'unpublished-artwork',
                'is_published' => false,
            ]);

        $this->info('Default test data seeded successfully.');

        return self::SUCCESS;
    }

    private function seedEmpty(): int
    {
        $this->info('Seeding empty database (admin user only)...');

        $this->createAdminUser();

        $this->info('Empty database seeded successfully.');

        return self::SUCCESS;
    }

    private function seedGallery(): int
    {
        $this->info('Seeding gallery test data...');

        $this->createAdminUser();

        $oilPaintings = Category::create(['name' => 'Oil Paintings', 'slug' => 'oil-paintings']);
        $digitalArt = Category::create(['name' => 'Digital Art', 'slug' => 'digital-art']);
        $emptyCategory = Category::create(['name' => 'Empty Category', 'slug' => 'empty-category']);

        Artwork::factory()->for($oilPaintings)->published()->create([
            'title' => 'Sunset Over Mountains',
            'slug' => 'sunset-over-mountains',
            'artist_name' => 'John Smith',
        ]);

        Artwork::factory()->for($oilPaintings)->published()->create([
            'title' => 'Ocean Waves',
            'slug' => 'ocean-waves',
            'artist_name' => 'Jane Doe',
        ]);

        Artwork::factory()->for($digitalArt)->published()->create([
            'title' => 'Cyber City',
            'slug' => 'cyber-city',
            'artist_name' => 'Alex Digital',
        ]);

        Artwork::factory()->for($oilPaintings)->create([
            'title' => 'Hidden Draft',
            'slug' => 'hidden-draft',
            'is_published' => false,
        ]);

        $this->info('Gallery test data seeded successfully.');

        return self::SUCCESS;
    }

    private function seedPagination(): int
    {
        $this->info('Seeding pagination test data...');

        $this->createAdminUser();

        $category = Category::create(['name' => 'Main Category', 'slug' => 'main-category']);

        Artwork::factory()
            ->for($category)
            ->count(20)
            ->published()
            ->sequence(fn ($sequence) => [
                'title' => 'Artwork '.str_pad($sequence->index + 1, 2, '0', STR_PAD_LEFT),
                'slug' => 'artwork-'.str_pad($sequence->index + 1, 2, '0', STR_PAD_LEFT),
                'published_at' => now()->subDays(20 - $sequence->index),
            ])
            ->create();

        $this->info('Pagination test data seeded successfully.');

        return self::SUCCESS;
    }

    private function seedSingleArtwork(): int
    {
        $this->info('Seeding single artwork test data...');

        $this->createAdminUser();

        $category = Category::create(['name' => 'Fine Art', 'slug' => 'fine-art']);

        Artwork::factory()->for($category)->published()->create([
            'title' => 'The Masterpiece',
            'slug' => 'the-masterpiece',
            'artist_name' => 'Master Artist',
            'description' => 'A beautiful artwork with rich colors and intricate details.',
            'medium' => 'Oil on Canvas',
            'published_at' => now()->subDays(30),
        ]);

        Artwork::factory()->for($category)->create([
            'title' => 'Draft Work',
            'slug' => 'draft-work',
            'is_published' => false,
        ]);

        $this->info('Single artwork test data seeded successfully.');

        return self::SUCCESS;
    }

    private function seedAdmin(): int
    {
        $this->info('Seeding admin test data...');

        $this->createAdminUser();

        $categories = [
            Category::create(['name' => 'Paintings', 'slug' => 'paintings']),
            Category::create(['name' => 'Sculptures', 'slug' => 'sculptures']),
            Category::create(['name' => 'Photography', 'slug' => 'photography']),
        ];

        foreach ($categories as $index => $category) {
            Artwork::factory()
                ->for($category)
                ->count($index + 1)
                ->published()
                ->create();
        }

        Category::create(['name' => 'Empty For Delete', 'slug' => 'empty-for-delete']);

        $this->info('Admin test data seeded successfully.');

        return self::SUCCESS;
    }

    private function createAdminUser(): User
    {
        return User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
            ]
        );
    }
}
