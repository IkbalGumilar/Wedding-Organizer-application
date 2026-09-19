<?php

namespace App\Filament\Widgets;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\WeddingPackage;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BookingStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Menunggu', Booking::query()->where('status', BookingStatus::Pending)->count())
                ->description('Permintaan perlu ditinjau')
                ->color('warning'),
            Stat::make('Acara diterima', Booking::query()->where('status', BookingStatus::Accepted)->whereDate('event_date', '>=', today())->count())
                ->description('Acara mendatang')
                ->color('info'),
            Stat::make('Selesai', Booking::query()->where('status', BookingStatus::Completed)->count())
                ->color('success'),
            Stat::make('Paket aktif', WeddingPackage::query()->where('is_active', true)->count())
                ->color('gray'),
        ];
    }
}
