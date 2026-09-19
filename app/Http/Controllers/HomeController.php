<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\WeddingPackage;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('public.home', [
            'packages' => WeddingPackage::query()->where('is_active', true)->orderBy('sort_order')->orderBy('id')->limit(3)->get(),
            'galleries' => Gallery::query()->where('is_published', true)->orderBy('sort_order')->orderBy('id')->limit(6)->get(),
        ]);
    }
}
