<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'wedding_package_id',
    'event_date',
    'couple_name',
    'event_location',
    'status',
    'payment_status',
    'makeup',
    'henna',
    'photographer',
    'mc',
    'entertainment',
    'traditional_ceremony',
    'eo',
    'videographer',
    'wedding_content_creator',
    'notes',
    'cancellation_reason',
    'package_name_snapshot',
    'package_description_snapshot',
    'package_sections_snapshot',
    'terms_snapshot',
    'terms_accepted_at',
    'terms_version',
    'package_price_snapshot',
])]
class Booking extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'status' => BookingStatus::class,
            'payment_status' => PaymentStatus::class,
            'package_sections_snapshot' => 'array',
            'terms_accepted_at' => 'datetime',
            'package_price_snapshot' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function weddingPackage(): BelongsTo
    {
        return $this->belongsTo(WeddingPackage::class);
    }

    public function canTransitionTo(BookingStatus $status): bool
    {
        return in_array($status, $this->status->transitions(), true);
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp '.number_format($this->package_price_snapshot, 0, ',', '.');
    }
}
