<?php

namespace Database\Seeders;

use App\Models\Artwork;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ArtworkSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();

        foreach ($categories as $category) {
            Artwork::factory()
                ->count(5)
                ->published()
                ->create(['category_id' => $category->id]);

            Artwork::factory()
                ->count(2)
                ->create(['category_id' => $category->id]);
        }
    }
}
