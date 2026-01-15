<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    protected static function booted(): void
    {
        static::creating(function (Category $category) {
            if (! $category->slug) {
                $category->slug = str($category->name)->slug()->toString();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function artworks(): HasMany
    {
        return $this->hasMany(Artwork::class);
    }
}
