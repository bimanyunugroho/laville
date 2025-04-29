<?php

namespace App\Enums;

enum StatusPOEnum: string
{
    case PROSESS = 'PROSESS';
    case RECEIVED = 'RECEIVED';
    case CANCELED = 'CANCELED';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match($this) {
            self::PROSESS => 'PROSESS',
            self::RECEIVED => 'RECEIVED',
            self::CANCELED => 'CANCELED'
        };
    }
}
