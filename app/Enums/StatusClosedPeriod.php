<?php

namespace App\Enums;

enum StatusClosedPeriod: string
{
    case OPEN = 'OPEN';
    case RUNNING = 'RUNNING';
    case CLOSED = 'CLOSED';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match($this) {
            self::OPEN => 'OPEN',
            self::RUNNING => 'RUNNING',
            self::CLOSED => 'CLOSED'
        };
    }
}
