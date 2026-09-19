<?php

namespace App\Filament\Resources\WeddingPackages\Pages;

use App\Filament\Resources\WeddingPackages\WeddingPackageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageWeddingPackages extends ManageRecords
{
    protected static string $resource = WeddingPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah paket'),
        ];
    }
}
