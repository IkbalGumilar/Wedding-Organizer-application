<?php

namespace App\Actions\Bookings;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingDateLock;
use App\Models\User;
use App\Models\WeddingPackage;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Support\Facades\DB;

class CreateBooking
{
    public function handle(User $user, array $review): Booking
    {
        return DB::transaction(function () use ($user, $review): Booking {
            if (blank($user->phone)) {
                throw new DomainException('Nomor WhatsApp diperlukan untuk melanjutkan booking dan komunikasi terkait acara.');
            }

            $package = WeddingPackage::query()
                ->whereKey($review['wedding_package_id'])
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if (! $package) {
                throw new DomainException('Paket sudah tidak tersedia. Silakan pilih paket lain.');
            }

            if (
                (int) $review['package_price'] !== $package->price
                || $review['package_version'] !== $package->snapshotVersion()
                || $review['terms_version'] !== $package->termsVersion()
            ) {
                throw new DomainException('Harga atau ketentuan paket berubah. Silakan review ulang booking.');
            }

            $eventDate = CarbonImmutable::createFromFormat('Y-m-d', $review['event_date'], config('app.timezone'));

            if ($eventDate->toDateString() < today()->toDateString()) {
                throw new DomainException('Tanggal acara tidak boleh di masa lalu.');
            }

            $dateLock = BookingDateLock::query()
                ->whereDate('event_date', $eventDate->toDateString())
                ->lockForUpdate()
                ->first();

            if (! $dateLock) {
                BookingDateLock::query()->insertOrIgnore([
                    'event_date' => $eventDate->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $dateLock = BookingDateLock::query()
                    ->whereDate('event_date', $eventDate->toDateString())
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            $usedSlots = Booking::query()
                ->whereDate('event_date', $eventDate->toDateString())
                ->whereIn('status', array_map(
                    static fn (BookingStatus $status): string => $status->value,
                    BookingStatus::countedTowardsCapacity(),
                ))
                ->count();

            if ($usedSlots >= (int) config('booking.daily_capacity', 3)) {
                throw new DomainException('Tanggal ini sudah penuh. Silakan pilih tanggal lain.');
            }

            $termsSnapshot = $package->termsSnapshot();

            return $user->bookings()->create([
                'wedding_package_id' => $package->id,
                'event_date' => $eventDate->toDateString(),
                'couple_name' => $review['couple_name'],
                'event_location' => $review['event_location'],
                'status' => BookingStatus::Pending,
                'payment_status' => 'unpaid',
                'notes' => $review['notes'] ?? null,
                'package_name_snapshot' => $package->name,
                'package_description_snapshot' => $package->description,
                'package_sections_snapshot' => $package->sections ?? [],
                'package_price_snapshot' => $package->price,
                'terms_snapshot' => $termsSnapshot,
                'terms_accepted_at' => now(),
                'terms_version' => $package->termsVersion(),
            ]);
        });
    }
}
