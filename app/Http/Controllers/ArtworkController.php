<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use Illuminate\View\View;

class ArtworkController extends Controller
{
    public function show(Artwork $artwork): View
    {
        abort_unless($artwork->is_published, 404);

        return view('artworks.show', compact('artwork'));
    }
}
