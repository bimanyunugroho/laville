<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusClosedPeriod;
use App\Enums\StatusPOEnum;
use App\Events\ClosedPeriodApproved;
use App\Events\ClosedPeriodEvent;
use App\Helpers\PaginationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalClosedPeriodRequest;
use App\Http\Requests\ClosedPeriodFormRequest;
use App\Http\Resources\Admin\ClosedPeriodResource;
use App\Models\ClosedPeriod;
use App\Http\Requests\StoreClosedPeriodRequest;
use App\Http\Requests\UpdateClosedPeriodRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ClosedPeriodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $queryClosedPeriods = ClosedPeriod::query()
            ->with(['user', 'userAck', 'userReject'])
            ->when($request->filled('search') || $request->filled('month') || $request->filled('year'), function (Builder $query) use ($request) {
                $searchTerm = trim($request->input('search', ''));
                $searchMonthTerm = trim($request->input('month', ''));
                $searchYearTerm = trim($request->input('year', ''));

                $query->where(function (Builder $query) use ($searchTerm, $searchMonthTerm, $searchYearTerm) {
                    // Search by code
                    if ($searchTerm) {
                        $query->where('no_closed', 'ilike', "%{$searchTerm}%");
                    }

                    // Filter by month
                    if ($searchMonthTerm) {
                        $query->where('month', $searchMonthTerm);
                    }

                    // Filter by year
                    if ($searchYearTerm) {
                        $query->where('year', $searchYearTerm);
                    }
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $dataClosedPeriod = $queryClosedPeriods->map(function ($queryClosedPeriod) {
            return new ClosedPeriodResource($queryClosedPeriod);
        });

        return Inertia::render('account/closed_period/Index', [
            'title' => 'Setting Periode',
            'desc'  => 'Data Setting Periode',
            'closed_periods' => [
                'data'  => $dataClosedPeriod,
                'links' => PaginationData::formatPaginationLinks($queryClosedPeriods),
                'current_page'  => $queryClosedPeriods->currentPage(),
                'per_page'  => $queryClosedPeriods->perPage(),
                'total' => $queryClosedPeriods->total()
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $currentUser = Auth::user();

        return Inertia::render('account/closed_period/Create', [
            'title' => 'Setting Periode',
            'desc'  => 'Tambah Setting Periode',
            'no_closed'    => ClosedPeriod::generateClosedNumber(),
            'currentUser' => $currentUser
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClosedPeriodRequest $request)
    {
        DB::beginTransaction();

        try {
            $validatedData = $request->validated();
            $closedPeriod = ClosedPeriod::create($validatedData);

            DB::commit();
            return redirect()->route('admin.account.closed_period.index')->with('success', 'Data Tutup Periode Berhasil Ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.account.closed_period.index')->with('error', 'Data Tutup Periode Gagal Ditambahkan!');
        }
    }


    public function showApprovalForm(ClosedPeriod $closedPeriod)
    {
        $dataClosedPeriod = $closedPeriod->load([
            'user',
            'userAck',
            'userReject'
        ]);

        $status = collect(StatusPOEnum::cases())->map(function ($stat) {
            return [
                'label' => $stat->label(),
                'name' => $stat->value,
            ];
        });

        $currentUser = Auth::user();

        return Inertia::render('account/closed_period/Approvment', [
            'title' => 'Setting Periode',
            'desc'  => 'Approvment/Rejected Setting Periode',
            'closed_period'  => new ClosedPeriodResource($dataClosedPeriod),
            'closed_period_approval'  => new ClosedPeriodResource($dataClosedPeriod),
            'status'    => $status,
            'currentUser' => $currentUser
        ]);
    }

    public function submitApproval(ApprovalClosedPeriodRequest $request, ClosedPeriod $closedPeriod)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validated();
            $closedPeriod->update($validated);

            DB::commit();
            return redirect()->route('admin.account.closed_period.index')->with('success', 'Data Tutup Periode Berhasil DiApprove!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.account.closed_period.index')->with('error', 'Gagal DiApprove data Tutup Periode!');
        }
    }

    public function closedPeriod(ClosedPeriodFormRequest $request, ClosedPeriod $closedPeriod)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validated();
            $closedPeriod->update($validated);

            Event::dispatch(new ClosedPeriodEvent($closedPeriod));

            DB::commit();
            return redirect()->route('admin.account.closed_period.index')->with('success', 'Setting Peride Berhasil DiTutup!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.account.closed_period.index')->with('error', 'Setting Peride Gagal DiTutup!');
        }
    }
}
