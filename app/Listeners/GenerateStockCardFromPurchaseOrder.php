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
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PurchaseOrderApproved $event): void
    {
        $purchaseOrder = $event->purchaseOrder;
        $noPurchaseOrder = $purchaseOrder->po_number;
        $transactionDate = $purchaseOrder->po_date ?? now();
        $month = Carbon::parse($transactionDate)->month;
        $year = Carbon::parse($transactionDate)->year;

        foreach ($purchaseOrder->details as $detail) {
            $product = $detail->product;
            $unit = $detail->unit;

            // Simpan atau update data StockCard
            $stockCard = StockCard::updateOrCreate(
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
                    'status_running' => StatusRunningCurrentStockEnum::SEDANG_PROSES->value,
                ]
            );

            // Simpan data StockCardDetail
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
                'notes' => 'PO @' . $noPurchaseOrder . ' Approved',
            ]);

            // Simpan atau update data CurrentStock
            $currentStock = CurrentStock::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'unit_id' => $unit->id,
                    'month' => $month,
                    'year' => $year,
                ],
                [
                    'quantity' => 0,
                    'base_quantity' => 0,
                    'status_running' => StatusRunningCurrentStockEnum::SEDANG_PROSES->value,
                ]
            );

            // Menambahkan data untuk CurrentStock (menggunakan data yang relevan dari PO)
            $currentStock->update([
                'quantity' => $currentStock->quantity + 0,
                'base_quantity' => $currentStock->base_quantity + 0,
            ]);
        }
    }
}
