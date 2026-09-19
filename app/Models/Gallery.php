<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable(['title', 'image_path', 'description', 'sort_order', 'is_published'])]
class Gallery extends Model
{
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::updated(function (self $gallery): void {
            if ($gallery->wasChanged('image_path') && ($oldPath = $gallery->getOriginal('image_path'))) {
                Storage::disk('public')->delete($oldPath);
            }
        });

        static::deleted(function (self $gallery): void {
            if ($gallery->image_path) {
                Storage::disk('public')->delete($gallery->image_path);
            }
        });
    }
}
