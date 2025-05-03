<?php

namespace App\Enums;

enum MovementTypeStockCardEnum: string
{
    case MASTER_BARU = 'MASTER_BARU';
    case PROSESS = 'PROSESS';
    case MASUK = 'MASUK';
    case KELUAR = 'KELUAR';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match($this) {
            self::MASTER_BARU => 'MASTER BARU',
            self::PROSESS => 'PROSESS',
            self::MASUK => 'MASUK',
            self::KELUAR => 'KELUAR',
        };
    }
}
