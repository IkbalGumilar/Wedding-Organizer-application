<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use Laravel\Fortify\FortifyServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    App\Providers\FortifyServiceProvider::class,
    AdminPanelProvider::class,
];
