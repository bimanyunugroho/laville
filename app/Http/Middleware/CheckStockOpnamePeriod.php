<?php

namespace App\Http\Middleware;

use App\Models\ClosedPeriod;
use App\Models\StockOpname;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class CheckStockOpnamePeriod
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $basicAllowedRoutes = [
            'admin.dashboard',
            'admin.account.closed_period.index',
            'admin.account.closed_period.create',
            'admin.account.closed_period.store',
            'admin.account.closed_period.show',
            'admin.account.closed_period.edit',
            'admin.account.closed_period.update',
            'admin.account.closed_period.destroy',
            'admin.account.closed_period.approval.view',
            'admin.account.closed_period.approval.submit',
            'admin.account.stock_card.index',
            'admin.account.stock_card.create',
            'admin.account.stock_card.store',
            'admin.account.stock_card.show',
            'admin.account.stock_card.edit',
            'admin.account.stock_card.update',
            'admin.account.stock_card.destroy'
        ];

        $routeName = $request->route()->getName();

        try {
            if (Schema::hasTable('stock_opnames')) {
                // Check if there's an active period
                $activePeriod = ClosedPeriod::periodIsActive()->first();

                if (!$activePeriod) {
                    // No active period exists
                    if (!in_array($routeName, $basicAllowedRoutes)) {
                        return Inertia::render('Dashboard', [
                            'closedPeriodMessage' => 'Harap lakukan konfirmasi untuk periode berjalan',
                            'noPeriodExists' => true
                        ])->toResponse($request);
                    }
                } else {
                    // Active period exists, now check for active stock opname
                    $currentDate = Carbon::now();
                    $month = $activePeriod->month ?? $currentDate->month;
                    $year = $activePeriod->year ?? $currentDate->year;

                    // Use the scope to find active stock opname records
                    $activeStockOpname = StockOpname::activeByStockCard($month, $year)->first();

                    if ($activeStockOpname) {
                        // Active stock opname exists, restrict access to certain routes
                        if (!in_array($routeName, $basicAllowedRoutes)) {
                            return Inertia::render('Dashboard', [
                                'closedPeriodMessage' => 'Anda Sedang melakukan Stock Opname untuk periode ' . $month . '/' . $year,
                                'closedPeriod' => $activePeriod
                            ])->toResponse($request);
                        }
                    }
                }
            } else {
                Log::info('Tabel stock_opnames belum ada');
            }
        } catch (\Exception $e) {
            Log::error('Stock Opname middleware error: ' . $e->getMessage());
        }

        return $next($request);
    }
}
