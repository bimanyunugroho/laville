<?php

namespace App\Listeners;

use App\Enums\MovementTypeStockCardEnum;
use App\Enums\ReferencesStockCardEnum;
use App\Enums\StatusRunningCurrentStockEnum;
use App\Events\StockOutApproved;
use App\Models\CurrentStock;
use App\Models\StockCard;
use Carbon\Carbon;

class GenerateStockCardFromStockOut
{
    public function __construct()
    {
        //
    }

    public function handle(StockOutApproved $event): void
    {
        $stockOut = $event->stockOut;
        $noStockOutOrder = $stockOut->stock_out_number;
        $transactionDate = $stockOut->ack_date ?? now();
        $month = Carbon::parse($transactionDate)->month;
        $year = Carbon::parse($transactionDate)->year;

        foreach ($stockOut->details as $detail) {
            $product = $detail->product;
            $unit = $detail->unit;
            $quantity = $detail->received_quantity;
            $baseQuantity = $detail->received_base_quantity;

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
                    'status_running' => StatusRunningCurrentStockEnum::SEDANG_BERJALAN->value,
                ]
            );

            // Tambahkan ke kuantitas keluar (unit dan base)
            $stockCard->out_balance += $quantity;
            $stockCard->out_base_balance += $baseQuantity;

            // Hitung ulang ending balance (unit dan base)
            $stockCard->ending_balance = $stockCard->beginning_balance + $stockCard->in_balance - $stockCard->out_balance;
            $stockCard->ending_base_balance = $stockCard->beginning_base_balance + $stockCard->in_base_balance - $stockCard->out_base_balance;

            $stockCard->save();

            $stockCard->stockCardDetails()->create([
                'reference_id' => $detail->id,
                'reference_type' => get_class($detail),
                'reference_status' => ReferencesStockCardEnum::PENGELUARAN_BARANG->value,
                'unit_id' => $unit->id,
                'transaction_date' => $transactionDate,
                'movement_type' => MovementTypeStockCardEnum::KELUAR->value,
                'quantity' => $quantity,
                'base_quantity' => $baseQuantity,
                'balance_quantity' => $stockCard->ending_balance,
                'balance_base_quantity' => $stockCard->ending_base_balance,
                'notes' => 'GDN @' . $noStockOutOrder,
            ]);

            $currentStock = CurrentStock::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'unit_id' => $unit->id,
                    'month' => $month,
                    'year' => $year,
                ],
                [
                    'quantity' => 0,
                    'base_quantity' => 0,
                    'status_running' => StatusRunningCurrentStockEnum::SEDANG_BERJALAN->value,
                ]
            );

            $currentStock->quantity -= $quantity;
            $currentStock->base_quantity -= $baseQuantity;
            $currentStock->status_running = StatusRunningCurrentStockEnum::SEDANG_BERJALAN->value;
            $currentStock->save();
        }
    }
}
