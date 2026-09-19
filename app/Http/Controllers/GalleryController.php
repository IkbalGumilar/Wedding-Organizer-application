<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(): View
    {
        return view('public.gallery', [
            'galleries' => Gallery::query()->where('is_published', true)->orderBy('sort_order')->orderBy('id')->paginate(12),
        ]);
    }
}
