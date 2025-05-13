<?php

namespace App\Http\Middleware;

use App\Models\ClosedPeriod;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Inertia\Inertia;
use Illuminate\Support\Facades\Schema;

class CheckClosedPeriod
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Daftar rute yang selalu diperbolehkan
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
            'admin.account.closed_period.closed.submit'
        ];

        // Mendapatkan nama rute saat ini
        $routeName = $request->route()->getName();

        try {
            // Periksa apakah tabel closed_periods sudah ada
            if (Schema::hasTable('closed_periods')) {
                // Periksa apakah ada data di tabel closed_periods
                $closedPeriodCount = ClosedPeriod::count();

                // Jika tidak ada data sama sekali di tabel closed_periods
                if ($closedPeriodCount === 0) {
                    // Cek jika rute saat ini tidak ada dalam daftar yang diperbolehkan
                    if (!in_array($routeName, $basicAllowedRoutes)) {
                        // Redirect ke halaman dashboard dengan pesan
                        return Inertia::render('Dashboard', [
                            'closedPeriodMessage' => 'Belum ada data periode tutup buku. Silahkan buat periode tutup buku terlebih dahulu.',
                            'noPeriodExists' => true
                        ])->toResponse($request);
                    }
                } else {
                    // Jika ada data, cek apakah ada periode yang sedang RUNNING
                    $activePeriod = ClosedPeriod::query()
                        ->where('status_period', 'RUNNING')
                        ->first();

                    // Jika ada periode yang sedang RUNNING atau status-nya aktif
                    if (!$activePeriod) {
                        // Cek jika rute saat ini tidak ada dalam daftar yang diperbolehkan
                        if (!in_array($routeName, $basicAllowedRoutes)) {
                            // Redirect ke halaman dashboard dengan pesan periode tutup
                            return Inertia::render('Dashboard', [
                                'closedPeriodMessage' => 'Harap lakukan konfirmasi untuk periode berjalan untuk bulan ',
                                'closedPeriod' => $activePeriod
                            ])->toResponse($request);
                        }
                    }
                }
            } else {
                Log::info('CheckClosedPeriod middleware: Tabel closed_periods belum ada');
            }
        } catch (\Exception $e) {
            Log::error('CheckClosedPeriod middleware error: ' . $e->getMessage());
        }

        return $next($request);
    }
}
