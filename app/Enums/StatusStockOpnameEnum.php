<?php

namespace App\Enums;

enum StatusStockOpnameEnum: string
{
    // Bisa semuanya
    case DRAFT = 'DRAFT';

    // Tidak bisa dihapus, dan diedit
    case VALIDATED = 'VALIDATED';

    // Bisa dilakaukan setelah validated. Tidak bisa dihapus, dan diedit. Dan hanya bisa dilihat saja
    case COMPLETED = 'COMPLETED';

    // Hanya bisa di lihat saja
    case CANCELED = 'CANCELED';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'DRAFT',
            self::VALIDATED => 'VALIDATED',
            self::COMPLETED => 'COMPLETED',
            self::CANCELED => 'CANCELED'
        };
    }
}
