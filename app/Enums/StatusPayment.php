<?php

namespace App\Enums;

enum StatusPayment: string
{
    case PAID = 'PAID';
    case PARTIAL = 'PARTIAL';
    case PENDING = 'PENDING';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match($this) {
            self::PAID => 'PAID',
            self::PARTIAL => 'PARTIAL',
            self::PENDING => 'PENDING'
        };
    }
}
