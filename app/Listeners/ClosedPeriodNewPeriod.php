<?php

namespace App\Listeners;

use App\Enums\MovementTypeStockCardEnum;
use App\Enums\ReferencesStockCardEnum;
use App\Enums\StatusClosedPeriod;
use App\Enums\StatusReceiptEnum;
use App\Enums\StatusRunningCurrentStockEnum;
use App\Events\ClosedPeriodEvent;
use App\Models\ClosedPeriod;
use App\Models\CurrentStock;
use App\Models\StockCard;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ClosedPeriodNewPeriod
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event when a closed period is created.
     * This method creates a new period and transfers ending balances as beginning balances.
     */
    public function handle(ClosedPeriodEvent $event): void
    {
        try {
            $closedPeriod = $event->closedPeriod;

            // Create the next period first
            $newPeriod = $this->createNextPeriod($closedPeriod);

            // Then process stock transfers
            $this->processStockTransfer($closedPeriod, $newPeriod);

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Create the next accounting period based on the closed one
     *
     * @param ClosedPeriod $closedPeriod
     * @return ClosedPeriod
     */
    private function createNextPeriod(ClosedPeriod $closedPeriod): ClosedPeriod
    {
        // Calculate next month and year
        $nextMonth = $closedPeriod->month == 12 ? 1 : $closedPeriod->month + 1;
        $nextYear = $closedPeriod->month == 12 ? $closedPeriod->year + 1 : $closedPeriod->year;

        // Create next period with OPEN status
        $newPeriod = ClosedPeriod::create([
            'no_closed' => ClosedPeriod::generateClosedNumber(),
            'month' => $nextMonth,
            'year' => $nextYear,
            'user_id' => Auth::id() ?? $closedPeriod->user_id,
            'closed_date' => Carbon::now(),
            'status_period' => StatusClosedPeriod::OPEN->value,
            'status_confirm' => StatusReceiptEnum::PROSESS->value,
            'status' => true
        ]);

        return $newPeriod;
    }

    /**
     * Process stock transfer from closed period to new period
     * Handles stock cards and current stock updates
     */
    private function processStockTransfer(ClosedPeriod $closedPeriod, ClosedPeriod $newPeriod): void
    {
        // Get relevant date information from closed period
        $noClosedPeriod = $closedPeriod->no_closed;
        $transactionDate = $closedPeriod->closed_date ?? now();

        // Calculate current period
        $currentMonth = $closedPeriod->month;
        $currentYear = $closedPeriod->year;

        // Calculate next period
        $nextMonth = $newPeriod->month;
        $nextYear = $newPeriod->year;

        // Update the status of current period stock cards to SUDAH_BERAKHIR (FINISHED)
        StockCard::where('month', $currentMonth)
                ->where('year', $currentYear)
                ->update(['status_running' => StatusRunningCurrentStockEnum::SUDAH_BERAKHIR->value]);

        // Update current stock status to SUDAH_BERAKHIR
        CurrentStock::where('month', $currentMonth)
                    ->where('year', $currentYear)
                    ->update(['status_running' => StatusRunningCurrentStockEnum::SUDAH_BERAKHIR->value]);

        // Process all current stocks from closing period to create next period's beginning balances
        $this->processAllCurrentStocks($closedPeriod, $newPeriod);
    }

    /**
     * Process all current stocks to transfer ending balances to next period
     */
    private function processAllCurrentStocks(ClosedPeriod $closedPeriod, ClosedPeriod $newPeriod): void
    {
        $currentMonth = $closedPeriod->month;
        $currentYear = $closedPeriod->year;
        $nextMonth = $newPeriod->month;
        $nextYear = $newPeriod->year;
        $transactionDate = $closedPeriod->closed_date ?? now();
        $noClosedPeriod = $closedPeriod->no_closed;

        // Get all current stocks from current period
        $currentStocks = CurrentStock::where('month', $currentMonth)
                                    ->where('year', $currentYear)
                                    ->get();

        foreach ($currentStocks as $currentStock) {
            $productId = $currentStock->product_id;
            $unitId = $currentStock->unit_id;
            $product = $currentStock->product;
            $unit = $currentStock->unit;

            if (!$product || !$unit) {
                continue;
            }

            // Create new stock card for next period with beginning balance from current period
            $this->createNextPeriodStockCard(
                $productId,
                $unitId,
                $nextMonth,
                $nextYear,
                $currentStock->quantity,
                $currentStock->base_quantity,
                $transactionDate,
                $noClosedPeriod
            );
        }
    }

    /**
     * Create stock card and current stock for next period with beginning balances
     * transferred from current period's ending balances
     */
    private function createNextPeriodStockCard($productId, $unitId, $nextMonth, $nextYear, $endingQuantity, $endingBaseQuantity, $transactionDate, $noClosedPeriod): void
    {
        // Create stock card for next period with beginning balance from current period's ending balance
        $nextStockCard = StockCard::firstOrCreate(
            [
                'product_id' => $productId,
                'month' => $nextMonth,
                'year' => $nextYear,
            ],
            [
                'beginning_balance' => $endingQuantity,
                'in_balance' => 0,
                'out_balance' => 0,
                'ending_balance' => $endingQuantity, // Initially, ending = beginning
                'beginning_base_balance' => $endingBaseQuantity,
                'in_base_balance' => 0,
                'out_base_balance' => 0,
                'ending_base_balance' => $endingBaseQuantity, // Initially, ending = beginning
                'status_running' => StatusRunningCurrentStockEnum::SEDANG_PROSES->value,
            ]
        );

        // Create stock card detail for beginning balance
        $nextStockCard->stockCardDetails()->create([
            'stock_card_id' => $nextStockCard->id,
            'reference_id' => $nextStockCard->id,
            'reference_type' => get_class($nextStockCard),
            'reference_status' => ReferencesStockCardEnum::STOCK_AWAL->value,
            'unit_id' => $unitId,
            'transaction_date' => $transactionDate,
            'movement_type' => MovementTypeStockCardEnum::MASUK->value,
            'quantity' => $endingQuantity,
            'base_quantity' => $endingBaseQuantity,
            'balance_quantity' => $endingQuantity,
            'balance_base_quantity' => $endingBaseQuantity,
            'notes' => "@" . $noClosedPeriod
        ]);

        // Create current stock for next period
        $nextCurrentStock = CurrentStock::firstOrCreate(
            [
                'product_id' => $productId,
                'unit_id' => $unitId,
                'month' => $nextMonth,
                'year' => $nextYear,
            ],
            [
                'quantity' => $endingQuantity,
                'base_quantity' => $endingBaseQuantity,
                'status_running' => StatusRunningCurrentStockEnum::SEDANG_PROSES->value,
            ]
        );
    }
}
