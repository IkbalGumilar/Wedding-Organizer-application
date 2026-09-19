<?php

namespace App\Actions\Bookings;

use App\Enums\BookingStatus;
use App\Models\Booking;
use DomainException;
use Illuminate\Support\Facades\DB;

class ChangeBookingStatus
{
    public function handle(Booking $booking, BookingStatus $status, ?string $cancellationReason = null): Booking
    {
        return DB::transaction(function () use ($booking, $status, $cancellationReason): Booking {
            $lockedBooking = Booking::query()->lockForUpdate()->findOrFail($booking->id);

            if (! $lockedBooking->canTransitionTo($status)) {
                throw new DomainException('Transisi status booking tersebut tidak diizinkan.');
            }

            if ($status === BookingStatus::Completed && $lockedBooking->event_date->isFuture()) {
                throw new DomainException('Booking hanya dapat ditandai selesai setelah tanggal acara.');
            }

            if ($status === BookingStatus::Cancelled && blank($cancellationReason)) {
                throw new DomainException('Alasan pembatalan wajib diisi.');
            }

            $lockedBooking->forceFill([
                'status' => $status,
                'cancellation_reason' => $status === BookingStatus::Cancelled ? $cancellationReason : null,
            ])->save();

            return $lockedBooking->refresh();
        });
    }
}
