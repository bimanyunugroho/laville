<?php

namespace App\Enums;

enum StatusRunningCurrentStockEnum: string
{
    case MASTER_BARU = 'MASTER_BARU';
    case SEDANG_PROSES = 'SEDANG_PROSES';
    case SEDANG_BERJALAN = 'SEDANG_BERJALAN';
    case SUDAH_BERAKHIR = 'SUDAH_BERAKHIR';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match($this) {
            self::SEDANG_PROSES => 'SEDANG_PROSES',
            self::SEDANG_BERJALAN => 'SEDANG_BERJALAN',
            self::SUDAH_BERAKHIR => 'SUDAH_BERAKHIR',
        };
    }
}
