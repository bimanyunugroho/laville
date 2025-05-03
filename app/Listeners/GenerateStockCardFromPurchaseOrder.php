<?php

namespace App\Listeners;

use App\Enums\MovementTypeStockCardEnum;
use App\Enums\ReferencesStockCardEnum;
use App\Enums\StatusRunningCurrentStockEnum;
use App\Events\PurchaseOrderApproved;
use App\Models\StockCard;
use App\Models\CurrentStock;
use Carbon\Carbon;

class GenerateStockCardFromPurchaseOrder
{
    public function __construct()
    {
        //
    }

    public function handle(PurchaseOrderApproved $event): void
    {
        $purchaseOrder = $event->purchaseOrder;
        $noPurchaseOrder = $purchaseOrder->po_number;
        $transactionDate = $purchaseOrder->ack_date ?? now();
        $month = Carbon::parse($transactionDate)->month;
        $year = Carbon::parse($transactionDate)->year;

        foreach ($purchaseOrder->details as $detail) {
            $product = $detail->product;
            $unit = $detail->unit;

            // Cek dan buat StockCard jika belum ada
            $stockCard = StockCard::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'month' => $month,
                    'year' => $year,
                ],
                [
                    'beginning_balance' => 0,
                    'in_balance' => 0,
                    'out_balance' => 0,
                    'ending_balance' => 0,
                    'beginning_base_balance' => 0,
                    'in_base_balance' => 0,
                    'out_base_balance' => 0,
                    'ending_base_balance' => 0,
                    'status_running' => StatusRunningCurrentStockEnum::SEDANG_PROSES->value,
                ]
            );

            // Tambahkan detail ke StockCard
            $stockCard->stockCardDetails()->create([
                'reference_id' => $detail->id,
                'reference_type' => get_class($detail),
                'reference_status' => ReferencesStockCardEnum::PEMBELIAN_BARANG->value,
                'unit_id' => $unit->id,
                'transaction_date' => $transactionDate,
                'movement_type' => MovementTypeStockCardEnum::PROSESS->value,
                'quantity' => 0,
                'base_quantity' => 0,
                'balance_quantity' => 0,
                'balance_base_quantity' => 0,
                'notes' => 'PO @' . $noPurchaseOrder,
            ]);
        }
    }
}
