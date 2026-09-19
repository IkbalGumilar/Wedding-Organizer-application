<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Carbon\CarbonImmutable;

class BookingAvailabilityService
{
    /** @return list<array{date: string, used: int, remaining: int|null, status: string}> */
    public function forMonth(CarbonImmutable $month): array
    {
        $month = $month->startOfMonth();
        $start = $month->toDateString();
        $end = $month->endOfMonth()->toDateString();

        $counts = Booking::query()
            ->whereDate('event_date', '>=', $start)
            ->whereDate('event_date', '<=', $end)
            ->whereIn('status', $this->capacityStatusValues())
            ->get(['event_date'])
            ->countBy(fn (Booking $booking): string => $booking->event_date->toDateString())
            ->all();

        $days = [];

        for ($day = 1; $day <= $month->daysInMonth; $day++) {
            $date = $month->setDay($day);
            $dateString = $date->toDateString();
            $used = $counts[$dateString] ?? 0;
            $days[] = $this->statusFor($date, $used);
        }

        return $days;
    }

    /** @return array{date: string, used: int, remaining: int|null, status: string} */
    public function forDate(CarbonImmutable $date): array
    {
        return $this->statusFor($date, $this->countForDate($date));
    }

    public function countForDate(CarbonImmutable $date): int
    {
        return Booking::query()
            ->whereDate('event_date', $date->toDateString())
            ->whereIn('status', $this->capacityStatusValues())
            ->count();
    }

    /** @return list<string> */
    public function capacityStatusValues(): array
    {
        return array_map(
            static fn (BookingStatus $status): string => $status->value,
            BookingStatus::countedTowardsCapacity(),
        );
    }

    /** @return array{date: string, used: int, remaining: int|null, status: string} */
    private function statusFor(CarbonImmutable $date, int $used): array
    {
        if ($date->toDateString() < today()->toDateString()) {
            return [
                'date' => $date->toDateString(),
                'used' => $used,
                'remaining' => null,
                'status' => 'past',
            ];
        }

        $remaining = max(0, (int) config('booking.daily_capacity', 3) - $used);

        return [
            'date' => $date->toDateString(),
            'used' => $used,
            'remaining' => $remaining,
            'status' => match (true) {
                $remaining === 0 => 'full',
                $remaining === 1 => 'last_slot',
                default => 'available',
            },
        ];
    }
}
