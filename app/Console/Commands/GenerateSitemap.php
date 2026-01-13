<?php

namespace App\Console\Commands;

use App\Models\Artwork;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Generate the sitemap for the gallery';

    public function handle(): int
    {
        $this->info('Generating sitemap...');

        $sitemap = Sitemap::create()
            ->add(Url::create(route('gallery.index'))
                ->setLastModificationDate(now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                ->setPriority(1.0));

        Artwork::query()
            ->where('is_published', true)
            ->get()
            ->each(function (Artwork $artwork) use ($sitemap) {
                $sitemap->add(
                    Url::create(route('artworks.show', $artwork->slug))
                        ->setLastModificationDate($artwork->updated_at)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority(0.8)
                );
            });

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generated successfully!');

        return self::SUCCESS;
    }
}
