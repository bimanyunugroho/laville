<?php

namespace App\Enums;

enum StatusRunningCurrentStockEnum: string
{
    case MASTER_BARU = 'MASTER_BARU'; // Pada masa master baru
    case SEDANG_PROSES = 'SEDANG_PROSES'; // Hanya masa purchase order
    case SEDANG_BERJALAN = 'SEDANG_BERJALAN'; // Hanya masa Penerimaan barang dan pengeluaran barang
    case STOCK_OPNAME = 'STOCK_OPNAME'; // Hanya pada saat Stock Opname jadi sudah tidak bisa melakukan master baru, sedang proses maupun sedang berjalan nah yang cocok apa
    case SUDAH_BERAKHIR = 'SUDAH_BERAKHIR'; // Hanya saat tutup periode

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match($this) {
            self::SEDANG_PROSES => 'SEDANG_PROSES',
            self::SEDANG_BERJALAN => 'SEDANG_BERJALAN',
            self::STOCK_OPNAME => 'STOCK_OPNAME',
            self::SUDAH_BERAKHIR => 'SUDAH_BERAKHIR',
        };
    }
}
