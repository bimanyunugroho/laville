<?php

namespace App\Enums;

enum StatusStockOpnameDetailEnum: string
{
    // Jumlah fisik = jumlah sistem ---> IN
    case MATCH = 'MATCH';

    // Jumlah fisik < jumlah sistem ---> OUT
    case SHORTAGE = 'SHORTAGE';

    // Jumlah fisik > jumlah sistem ---> IN
    case OVERSTOCK = 'OVERSTOCK';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match($this) {
            self::MATCH => 'MATCH',
            self::SHORTAGE => 'SHORTAGE',
            self::OVERSTOCK => 'OVERSTOCK'
        };
    }
}
