<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['event_date'])]
class BookingDateLock extends Model
{
    protected function casts(): array
    {
        return [
            'event_date' => 'date',
        ];
    }
}
