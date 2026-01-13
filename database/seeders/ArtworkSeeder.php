<?php

namespace Database\Seeders;

use App\Models\Artwork;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class ArtworkSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();
        $imageCounter = 1;

        foreach ($categories as $category) {
            $publishedArtworks = Artwork::factory()
                ->count(5)
                ->published()
                ->create(['category_id' => $category->id]);

            foreach ($publishedArtworks as $artwork) {
                $this->attachRandomImage($artwork, $imageCounter++);
            }

            $unpublishedArtworks = Artwork::factory()
                ->count(2)
                ->create(['category_id' => $category->id]);

            foreach ($unpublishedArtworks as $artwork) {
                $this->attachRandomImage($artwork, $imageCounter++);
            }
        }
    }

    private function attachRandomImage(Artwork $artwork, int $seed): void
    {
        $width = random_int(800, 1200);
        $height = random_int(800, 1200);

        $imageUrl = "https://picsum.photos/seed/{$seed}/{$width}/{$height}";

        $response = Http::get($imageUrl);

        if ($response->successful()) {
            $tempPath = sys_get_temp_dir().'/artwork_'.$seed.'.jpg';
            file_put_contents($tempPath, $response->body());

            $artwork->addMedia($tempPath)
                ->preservingOriginal()
                ->toMediaCollection('artworks');

            @unlink($tempPath);
        }
    }
}
