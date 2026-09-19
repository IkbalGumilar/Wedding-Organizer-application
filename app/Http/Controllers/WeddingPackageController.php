<?php

namespace App\Http\Controllers;

use App\Models\WeddingPackage;
use Illuminate\View\View;

class WeddingPackageController extends Controller
{
    public function index(): View
    {
        return view('public.packages.index', [
            'packages' => WeddingPackage::query()->where('is_active', true)->orderBy('sort_order')->orderBy('id')->paginate(9),
        ]);
    }

    public function show(WeddingPackage $weddingPackage): View
    {
        abort_unless($weddingPackage->is_active, 404);

        return view('public.packages.show', compact('weddingPackage'));
    }
}
