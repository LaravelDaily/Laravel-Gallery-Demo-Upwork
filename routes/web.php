<?php

use App\Http\Controllers\ArtworkController;
use App\Livewire\Gallery\Index;
use Illuminate\Support\Facades\Route;

Route::get('/', Index::class)->name('gallery.index');

Route::get('/artworks/{artwork:slug}', [ArtworkController::class, 'show'])->name('artworks.show');
