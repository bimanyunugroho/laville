<?php

namespace App\Enums;

enum ReferencesStockCardEnum: string
{
    case MASTER_BARU = 'MASTER_BARU';
    case PEMBELIAN_BARANG = 'PEMBELIAN_BARANG';
    case PENJUALAN = 'PENJUALAN';
    case PENERIMAAN_BARANG = 'PENERIMAAN_BARANG';
    case PENGELUARAN_BARANG = 'PENGELUARAN_BARANG';
    case STOCK_OPNAME = 'STOCK_OPNAME';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match($this) {
            self::MASTER_BARU => 'MASTER_BARU',
            self::PEMBELIAN_BARANG => 'PEMBELIAN_BARANG',
            self::PENJUALAN => 'PENJUALAN',
            self::PENERIMAAN_BARANG => 'PENERIMAAN_BARANG',
            self::PENGELUARAN_BARANG => 'PENGELUARAN_BARANG',
            self::STOCK_OPNAME => 'STOCK_OPNAME'
        };
    }
}
