<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusPOEnum;
use App\Enums\StatusTransaction;
use App\Enums\TypeSourceTransaction;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period', 'daily');
        $allowedPeriods = ['daily', 'weekly', 'monthly', 'yearly'];

        if (!in_array($period, $allowedPeriods)) {
            $period = 'daily';
        }

        // Get sales stats data
        $salesStats = $this->getSalesStats($period);

        return Inertia::render('Dashboard', [
            'title' => 'Dashboard',
            'desc'  => 'Dashboard',
            'data'  => [
                'stats' => $salesStats,
                'transactions' => $this->transactionSalesData($period),
                'salesBySource' => $this->transactionSalesBySourceData($period),
                'period' => $period,
                'periods' => $allowedPeriods
            ]
        ]);
    }

    private function getSalesStats($period)
    {
        $startDate = $this->getStartDate($period);
        $endDate = Carbon::now();

        // Total Profit (subtotal - discount)
        $totalProfit = Transaction::whereBetween('transaction_date', [$startDate, $endDate])
            ->where('status', StatusTransaction::PAID->value)
            ->sum(DB::raw('paid_amount - change_amount'));

        // Periode sebelumnya untuk perbandingan
        $daysDiff = max(1, $startDate->diffInDays($endDate));
        $previousStartDate = (clone $startDate)->subDays($daysDiff)->startOfDay();
        $previousEndDate = (clone $startDate)->subDay()->endOfDay();


        $previousProfit = Transaction::whereBetween('transaction_date', [$previousStartDate, $previousEndDate])
            ->where('status', StatusTransaction::PAID->value)
            ->sum(DB::raw('paid_amount - change_amount'));

        $profitGrowth = $previousProfit > 0
            ? round((($totalProfit - $previousProfit) / $previousProfit) * 100, 1)
            : 0;

        // Hitung total transaksi dan transaksi selesai (order)
        $totalTransactions = Transaction::whereBetween('transaction_date', [$startDate, $endDate])->count();
        $completedTransactions = Transaction::whereBetween('transaction_date', [$startDate, $endDate])
            ->where('status', StatusTransaction::PAID->value)
            ->count();

        // Conversion rate
        $conversionRate = $totalTransactions > 0
            ? round(($completedTransactions / $totalTransactions) * 100)
            : 0;

        // Periode sebelumnya untuk transaksi
        $previousTotalTransactions = Transaction::whereBetween('transaction_date', [$previousStartDate, $previousEndDate])->count();
        $previousCompletedTransactions = Transaction::whereBetween('transaction_date', [$previousStartDate, $previousEndDate])
            ->where('status', StatusTransaction::PAID->value)
            ->count();

        $previousConversionRate = $previousTotalTransactions > 0
            ? ($previousCompletedTransactions / $previousTotalTransactions) * 100
            : 0;

        $conversionGrowth = $previousConversionRate > 0
            ? round(($conversionRate - $previousConversionRate) / $previousConversionRate * 100, 1)
            : 0;

        // Total Users (termasuk null)
        $totalUsers = Transaction::whereBetween('transaction_date', [$startDate, $endDate])
            ->whereNotNull('customer_id')
            ->distinct('customer_id')
            ->count('customer_id');

        $totalUsers += Transaction::whereBetween('transaction_date', [$startDate, $endDate])
            ->whereNull('customer_id')
            ->count();

        $previousUsers = Transaction::whereBetween('transaction_date', [$previousStartDate, $previousEndDate])
            ->whereNotNull('customer_id')
            ->distinct('customer_id')
            ->count('customer_id');

        $previousUsers += Transaction::whereBetween('transaction_date', [$previousStartDate, $previousEndDate])
            ->whereNull('customer_id')
            ->count();

        $userGrowth = $previousUsers > 0
            ? round((($totalUsers - $previousUsers) / $previousUsers) * 100, 1)
            : 0;

        // Hitung total order (jumlah transaksi) untuk periode ini dan sebelumnya
        $totalOrder = $totalTransactions;
        $totalOrderPrevious = $previousTotalTransactions;

        $orderGrowth = $totalOrderPrevious > 0
            ? round((($totalOrder - $totalOrderPrevious) / $totalOrderPrevious) * 100, 1)
            : 0;

        // Hitung Total Purchase Order (status RECEIVED dan total_net)
        $totalPurchaseOrder = PurchaseOrder::where('status', StatusPOEnum::RECEIVED->value)
            ->sum('total_net');

        // Hitung total purchase order periode sebelumnya
        $previousTotalPurchaseOrder = PurchaseOrder::where('status', StatusPOEnum::RECEIVED->value)
            ->whereBetween('po_date', [$previousStartDate, $previousEndDate])
            ->sum('total_net');

        // Hitung growth purchase order
        $purchaseOrderGrowth = $previousTotalPurchaseOrder > 0
            ? round((($totalPurchaseOrder - $previousTotalPurchaseOrder) / $previousTotalPurchaseOrder) * 100, 1)
            : 0;

        return [
            [
                'value' => $totalProfit,
                'label' => 'Total Profit',
                'growth' => $profitGrowth,
                'type' => 'rupiah',
            ],
            [
                'value' => $conversionRate,
                'label' => 'Conversion Rate',
                'growth' => $conversionGrowth,
                'type' => 'percent',
            ],
            [
                'value' => $totalOrder,
                'label' => 'Total Orders',
                'growth' => $orderGrowth,
                'type' => 'number',
            ],
            [
                'value' => $totalPurchaseOrder,
                'label' => 'Total Purchase Order',
                'growth' => $purchaseOrderGrowth,
                'type' => 'number',
            ],
        ];
    }

    private function getStartDate($period)
    {
        $now = Carbon::now();

        switch ($period) {
            case 'daily':
                return $now->copy()->startOfDay();
            case 'weekly':
                return $now->copy()->startOfWeek();
            case 'monthly':
                return $now->copy()->startOfMonth();
            case 'yearly':
                return $now->copy()->startOfYear();
            default:
                return $now->copy()->startOfDay();
        }
    }

    private function transactionSalesData($period)
    {
        $endDate = Carbon::now();
        $startDate = $this->getStartDate($period);

        $query = Transaction::select(
            DB::raw($this->getDateFormat($period) . ' as date'),
            DB::raw('SUM(total_amount) as value')
        )
            ->where('status', StatusTransaction::PAID->value)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->groupBy(DB::raw($this->getDateFormat($period)));

        $results = $query->get();

        // Complete the dataset with missing dates
        return $this->fillMissingDates($results, $startDate, $endDate, $period);
    }

    private function transactionSalesBySourceData($period)
    {
        $endDate = Carbon::now();
        $startDate = $this->getStartDate($period);

        $sources = [
            TypeSourceTransaction::OFFLINE->value,
            TypeSourceTransaction::SHOPEE->value,
            TypeSourceTransaction::TOKOPEDIA->value,
            TypeSourceTransaction::INSTAGRAM->value
        ];

        $results = [];

        foreach ($sources as $source) {
            $total = Transaction::where('status', StatusTransaction::PAID->value)
                ->where('source_transaction', $source)
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->sum('total_amount');

            $results[] = [
                'source' => $source,
                'value' => (int)$total
            ];
        }

        return $results;
    }

    /**
     * Get the SQL date format for grouping
     */
    private function getDateFormat($period)
    {
        switch ($period) {
            case 'daily':
                return "TO_CHAR(transaction_date, 'HH24:00')";
            case 'weekly':
                return "TO_CHAR(transaction_date, 'Dy')";
            case 'monthly':
                return "TO_CHAR(transaction_date, 'DD-MM')";
            case 'yearly':
                return "TO_CHAR(transaction_date, 'Mon')";
            default:
                return "TO_CHAR(transaction_date, 'HH24:00')";
        }
    }


    /**
     * Fill in any missing dates in the dataset
     */
    private function fillMissingDates($results, $startDate, $endDate, $period)
    {
        $resultArray = [];
        $format = $this->getDateDisplayFormat($period);
        $interval = $this->getDateInterval($period);
        $dateData = [];

        // Convert results to associative array
        foreach ($results as $row) {
            $dateData[$row->date] = $row->value;
        }

        // Create all the date points needed
        $current = clone $startDate;

        while ($current <= $endDate) {
            $key = $this->formatDateForDisplay($current, $period);

            $resultArray[] = [
                'date' => $key,
                'value' => isset($dateData[$key]) ? (int)$dateData[$key] : 0
            ];

            $current->add($interval);
        }

        return $resultArray;
    }

    /**
     * Get the date format for display
     */
    private function getDateDisplayFormat($period)
    {
        switch ($period) {
            case 'daily':
                return 'H:00';
            case 'weekly':
                return 'D';
            case 'monthly':
                return 'd-m';
            case 'yearly':
                return 'M';
            default:
                return 'H:00';
        }
    }

    /**
     * Get the date interval to use
     */
    private function getDateInterval($period)
    {
        switch ($period) {
            case 'daily':
                return new \DateInterval('PT1H');
            case 'weekly':
                return new \DateInterval('P1D');
            case 'monthly':
                return new \DateInterval('P1D');
            case 'yearly':
                return new \DateInterval('P1M');
            default:
                return new \DateInterval('PT1H');
        }
    }

    /**
     * Format a date for display
     */
    private function formatDateForDisplay($date, $period)
    {
        switch ($period) {
            case 'daily':
                return $date->format('H:00');
            case 'weekly':
                return $date->format('D');
            case 'monthly':
                return $date->format('d-m');
            case 'yearly':
                return $date->format('M');
            default:
                return $date->format('H:00');
        }
    }
}
