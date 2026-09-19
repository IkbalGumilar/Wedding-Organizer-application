<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Unpaid = 'unpaid';
    case Deposit = 'deposit';
    case Paid = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => 'Belum Bayar',
            self::Deposit => 'DP',
            self::Paid => 'Lunas',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Unpaid => 'gray',
            self::Deposit => 'warning',
            self::Paid => 'success',
        };
    }
}
