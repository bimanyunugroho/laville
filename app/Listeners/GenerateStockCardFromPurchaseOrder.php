<?php

namespace App\Listeners;

use App\Enums\MovementTypeStockCardEnum;
use App\Enums\ReferencesStockCardEnum;
use App\Enums\StatusRunningCurrentStockEnum;
use App\Events\PurchaseOrderApproved;
use App\Models\ClosedPeriod;
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
        $activePeriod = ClosedPeriod::periodIsActive()->select('month', 'year')->first();
        $month = $activePeriod->month;
        $year = $activePeriod->year;

        foreach ($purchaseOrder->details as $detail) {
            $product = $detail->product;
            $unit = $detail->unit;

            // Cek apakah StockCard sudah ada
            $stockCard = StockCard::where([
                'product_id' => $product->id,
                'month' => $month,
                'year' => $year,
            ])->first();

            if ($stockCard) {
                // Jika StockCard sudah ada dan status_running-nya MASTER_BARU
                if ($stockCard->status_running === StatusRunningCurrentStockEnum::MASTER_BARU->value) {
                    // Update hanya status_running menjadi SEDANG_PROSES
                    $stockCard->status_running = StatusRunningCurrentStockEnum::SEDANG_PROSES->value;
                    $stockCard->save();
                }
            } else {
                // Jika StockCard belum ada, buat baru
                $stockCard = StockCard::create([
                    'product_id' => $product->id,
                    'month' => $month,
                    'year' => $year,
                    'beginning_balance' => 0,
                    'in_balance' => 0,
                    'out_balance' => 0,
                    'ending_balance' => 0,
                    'beginning_base_balance' => 0,
                    'in_base_balance' => 0,
                    'out_base_balance' => 0,
                    'ending_base_balance' => 0,
                    'status_running' => StatusRunningCurrentStockEnum::SEDANG_PROSES->value,
                ]);
            }

            // Tambahkan detail StockCard (selalu ditambahkan)
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
                'notes' => $noPurchaseOrder,
            ]);

            // Cek apakah CurrentStock sudah ada
            $currentStock = CurrentStock::where([
                'product_id' => $product->id,
                'unit_id' => $unit->id,
                'month' => $month,
                'year' => $year,
            ])->first();

            if ($currentStock) {
                // Jika CurrentStock sudah ada dan status_running-nya MASTER_BARU
                if ($currentStock->status_running === StatusRunningCurrentStockEnum::MASTER_BARU->value) {
                    // Update hanya status_running menjadi SEDANG_PROSES
                    $currentStock->status_running = StatusRunningCurrentStockEnum::SEDANG_PROSES->value;
                    $currentStock->save();
                }
            } else {
                // Jika CurrentStock belum ada, buat baru
                $currentStock = CurrentStock::create([
                    'product_id' => $product->id,
                    'unit_id' => $unit->id,
                    'month' => $month,
                    'year' => $year,
                    'quantity' => 0,
                    'base_quantity' => 0,
                    'status_running' => StatusRunningCurrentStockEnum::SEDANG_PROSES->value,
                ]);
            }
        }
    }
}
