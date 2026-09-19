<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['name', 'slug', 'tagline', 'description', 'sections', 'price', 'is_active', 'image_path', 'sort_order'])]
class WeddingPackage extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'sections' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::updated(function (self $package): void {
            if ($package->wasChanged('image_path') && ($oldPath = $package->getOriginal('image_path'))) {
                Storage::disk('public')->delete($oldPath);
            }
        });

        static::deleted(function (self $package): void {
            if ($package->image_path) {
                Storage::disk('public')->delete($package->image_path);
            }
        });
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp '.number_format($this->price, 0, ',', '.');
    }

    public function termsSnapshot(): string
    {
        foreach ($this->sections ?? [] as $section) {
            if (! is_array($section)) {
                continue;
            }

            $title = trim((string) ($section['title'] ?? ''));

            if (! Str::contains(Str::lower($title), ['term', 'syarat', 'ketentuan'])) {
                continue;
            }

            $items = collect($section['items'] ?? [])
                ->filter(fn ($item): bool => filled($item))
                ->map(fn ($item): string => '- '.trim((string) $item))
                ->implode("\n");

            return filled($items) ? $title."\n{$items}" : '';
        }

        return '';
    }

    public function termsVersion(): string
    {
        return hash('sha256', $this->termsSnapshot());
    }

    public function snapshotVersion(): string
    {
        return hash('sha256', json_encode([
            'name' => $this->name,
            'description' => $this->description,
            'sections' => $this->sections ?? [],
            'price' => $this->price,
        ], JSON_THROW_ON_ERROR));
    }
}
