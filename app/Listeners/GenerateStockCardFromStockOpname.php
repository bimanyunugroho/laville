<?php

namespace App\Listeners;

use App\Enums\MovementTypeStockCardEnum;
use App\Enums\ReferencesStockCardEnum;
use App\Enums\StatusStockOpnameDetailEnum;
use App\Enums\StatusRunningCurrentStockEnum;
use App\Events\StockOpnameApproved;
use App\Models\ClosedPeriod;
use App\Models\CurrentStock;
use App\Models\StockCard;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class GenerateStockCardFromStockOpname
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
    public function handle(StockOpnameApproved $event): void
    {
        $stockOpname = $event->stockOpname;
        $noStockOpnameOrder = $stockOpname->so_number;
        $transactionDate = $stockOpname->ack_date ?? now();
        $activePeriod = ClosedPeriod::periodIsActive()->select('month', 'year')->first();
        $month = $activePeriod->month;
        $year = $activePeriod->year;

        foreach ($stockOpname->details as $detail) {
            // Dapatkan data yang diperlukan
            $product = $detail->product;
            $unit = $detail->unit;

            // Mendapatkan nilai difference_stock dan difference_stock_base
            $differenceStock = $detail->difference_stock;
            $differenceStockBase = $detail->difference_stock_base;

            // Jika tidak ada perbedaan, tetap catat dengan movement_type TETAP
            $isZeroDifference = ($differenceStock == 0);

            $stockCardActiveByProduct = StockCard::activeByProduct($product->id)->first();

            if ($stockCardActiveByProduct) {
                // Cari atau buat StockCard
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
                        'status_running' => StatusRunningCurrentStockEnum::STOCK_OPNAME->value,
                    ]
                );

                // Tentukan tipe pergerakan berdasarkan status (MATCH, OVERSTOCK, atau SHORTAGE)
                $movementType = '';
                if ($isZeroDifference) {
                    // Jika physical_stock = system_stock (MATCH) - tidak ada perubahan stok
                    $movementType = MovementTypeStockCardEnum::MASUK->value; // Assuming you have this enum value
                } else if ($differenceStock > 0) {
                    // Jika physical_stock > system_stock (OVERSTOCK) - tambahkan stok
                    $movementType = MovementTypeStockCardEnum::MASUK->value;
                    $stockCard->in_balance += abs($differenceStock);
                    $stockCard->in_base_balance += abs($differenceStockBase);
                } else {
                    // Jika physical_stock < system_stock (SHORTAGE) - kurangi stok
                    $movementType = MovementTypeStockCardEnum::KELUAR->value;
                    $stockCard->out_balance += abs($differenceStock);
                    $stockCard->out_base_balance += abs($differenceStockBase);
                }

                // Hitung ulang ending balance
                $stockCard->ending_balance = $stockCard->beginning_balance + $stockCard->in_balance - $stockCard->out_balance;
                $stockCard->ending_base_balance = $stockCard->beginning_base_balance + $stockCard->in_base_balance - $stockCard->out_base_balance;
                $stockCard->status_running = StatusRunningCurrentStockEnum::STOCK_OPNAME->value;

                $stockCard->save();

                // Buat detail stock card
                $stockCard->stockCardDetails()->create([
                    'reference_id' => $detail->id,
                    'reference_type' => get_class($detail),
                    'reference_status' => ReferencesStockCardEnum::STOCK_OPNAME->value,
                    'unit_id' => $unit->id,
                    'transaction_date' => $transactionDate,
                    'movement_type' => $movementType,
                    'quantity' => abs($differenceStock),
                    'base_quantity' => abs($differenceStockBase),
                    'balance_quantity' => $stockCard->ending_balance,
                    'balance_base_quantity' => $stockCard->ending_base_balance,
                    'notes' => $noStockOpnameOrder . ' (' . $detail->status . ')',
                ]);
            }

            $currentStockActiveByProductCurrent = CurrentStock::activeByProductCurrent($product->id)->first();
            if ($currentStockActiveByProductCurrent) {
                // Update current stock
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
                        'status_running' => StatusRunningCurrentStockEnum::STOCK_OPNAME->value,
                    ]
                );

                // Sesuaikan stok saat ini berdasarkan selisih
                if ($isZeroDifference) {
                    // Tidak ada perubahan stok jika MATCH
                    // Namun tetap catat bahwa stock opname telah dilakukan
                } else if ($differenceStock > 0) {
                    // Tambahkan stok jika OVERSTOCK
                    $currentStock->quantity += abs($differenceStock);
                    $currentStock->base_quantity += abs($differenceStockBase);
                } else {
                    // Kurangi stok jika SHORTAGE
                    $currentStock->quantity -= abs($differenceStock);
                    $currentStock->base_quantity -= abs($differenceStockBase);
                }

                $currentStock->status_running = StatusRunningCurrentStockEnum::STOCK_OPNAME->value;
                $currentStock->save();
            }
        }
    }
}
