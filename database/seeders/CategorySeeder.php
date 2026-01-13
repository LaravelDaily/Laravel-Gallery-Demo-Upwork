<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Oil Paintings'],
            ['name' => 'Digital'],
            ['name' => 'Sketches'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
