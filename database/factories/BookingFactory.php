<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;
use App\Models\WeddingPackage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'wedding_package_id' => WeddingPackage::factory(),
            'event_date' => today()->addMonth(),
            'couple_name' => fn (array $attributes): string => User::findOrFail($attributes['user_id'])->name,
            'event_location' => 'Gedung Atha, Jalan Mawar No. 1',
            'status' => BookingStatus::Pending,
            'payment_status' => 'unpaid',
            'makeup' => null,
            'henna' => null,
            'photographer' => null,
            'mc' => null,
            'entertainment' => null,
            'traditional_ceremony' => null,
            'eo' => null,
            'videographer' => null,
            'wedding_content_creator' => null,
            'notes' => null,
            'cancellation_reason' => null,
            'package_name_snapshot' => fn (array $attributes): string => WeddingPackage::findOrFail($attributes['wedding_package_id'])->name,
            'package_description_snapshot' => fn (array $attributes): string => WeddingPackage::findOrFail($attributes['wedding_package_id'])->description,
            'package_sections_snapshot' => fn (array $attributes): array => WeddingPackage::findOrFail($attributes['wedding_package_id'])->sections ?? [],
            'terms_snapshot' => fn (array $attributes): string => WeddingPackage::findOrFail($attributes['wedding_package_id'])->termsSnapshot(),
            'terms_accepted_at' => now(),
            'terms_version' => fn (array $attributes): string => WeddingPackage::findOrFail($attributes['wedding_package_id'])->termsVersion(),
            'package_price_snapshot' => fn (array $attributes): int => WeddingPackage::findOrFail($attributes['wedding_package_id'])->price,
        ];
    }
}
