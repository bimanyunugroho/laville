<?php

namespace App\Enums;

enum StatusNotesStockOutEnum: string
{
    case BARANG_RUSAK = 'BARANG_RUSAK';
    case PEMAKAIAN_INTERNAL = 'PEMAKAIAN_INTERNAL';
    case PEMAKAIAN_EKSTERNAL = 'PEMAKAIAN_EKSTERNAL';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match($this) {
            self::BARANG_RUSAK => 'BARANG_RUSAK',
            self::PEMAKAIAN_INTERNAL => 'PEMAKAIAN_INTERNAL',
            self::PEMAKAIAN_EKSTERNAL => 'PEMAKAIAN_EKSTERNAL'
        };
    }
}
