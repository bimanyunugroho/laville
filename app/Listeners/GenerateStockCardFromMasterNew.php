<?php

namespace App\Listeners;

use App\Enums\MovementTypeStockCardEnum;
use App\Enums\ReferencesStockCardEnum;
use App\Enums\StatusRunningCurrentStockEnum;
use App\Events\MasterProductNew;
use App\Models\CurrentStock;
use App\Models\StockCard;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class GenerateStockCardFromMasterNew
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
    public function handle(MasterProductNew $event): void
    {
        $product = $event->product;
        $createdAtDate = $product->created_at ?? now();
        $month = Carbon::parse($createdAtDate)->month;
        $year = Carbon::parse($createdAtDate)->year;

        $unitId = $product->default_unit_id ?? 0;

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
                'beginning_base_balance' => 0,
                'in_base_balance' => 0,
                'out_base_balance' => 0,
                'ending_base_balance' => 0,
                'status_running' => StatusRunningCurrentStockEnum::MASTER_BARU->value,
            ]
        );

        // Simpan data StockCardDetail
        $stockCard->stockCardDetails()->create([
            'reference_id' => $product->id,
            'reference_type' => get_class($product),
            'reference_status' => ReferencesStockCardEnum::MASTER_BARU->value,
            'unit_id' => $unitId,
            'transaction_date' => $createdAtDate,
            'movement_type' => MovementTypeStockCardEnum::MASTER_BARU->value,
            'quantity' => 0,
            'base_quantity' => 0,
            'balance_quantity' => 0,
            'balance_base_quantity' => 0,
            'notes' => 'Pembuatan Master Produk Baru',
        ]);

        // Simpan atau update data CurrentStock
        $currentStock = CurrentStock::updateOrCreate(
            [
                'product_id' => $product->id,
                'unit_id' => $unitId,
                'month' => $month,
                'year' => $year,
            ],
            [
                'quantity' => 0,
                'base_quantity' => 0,
                'status_running' => StatusRunningCurrentStockEnum::MASTER_BARU->value,
            ]
        );

        $currentStock->update([
            'quantity' => $currentStock->quantity + 0,
            'base_quantity' => $currentStock->base_quantity + 0,
        ]);
    }
}
