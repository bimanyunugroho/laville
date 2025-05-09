<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusRunningCurrentStockEnum;
use App\Enums\StatusStockOpnameDetailEnum;
use App\Enums\StatusStockOpnameEnum;
use App\Events\StockOpnameApproved;
use App\Helpers\PaginationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalStockOpnameRequest;
use App\Models\StockOpname;
use App\Http\Requests\StoreStockOpnameRequest;
use App\Http\Requests\UpdateStockOpnameRequest;
use App\Http\Requests\ValidatorStockOpnameRequest;
use App\Http\Resources\Admin\StockOpnameResource;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\UnitCollection;
use App\Http\Resources\UnitConversionCollection;
use App\Models\Product;
use App\Models\Unit;
use App\Models\UnitConversion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Inertia\Inertia;

class StockOpnameController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $queryStockOpnames = StockOpname::query()
            ->with(['user', 'userValidator', 'userAck', 'userReject'])
            ->when($request->filled('search') || $request->filled('month') || $request->filled('year'), function (Builder $query) use ($request) {
                $searchTerm = trim($request->input('search', ''));
                $searchMonthTerm = trim($request->input('month', ''));
                $searchYearTerm = trim($request->input('year', ''));

                $query->where(function (Builder $query) use ($searchTerm, $searchMonthTerm, $searchYearTerm) {
                    // Filter by SO Number
                    if ($searchTerm) {
                        $query->where('so_number', 'ilike', "%{$searchTerm}%");
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

        $dataStockOpname = $queryStockOpnames->map(function ($queryStockOpname) {
            return new StockOpnameResource($queryStockOpname);
        });

        return Inertia::render('inventory/stock_opname/Index', [
            'title' => 'Stock Opname',
            'desc'  => 'Data Stock Opname',
            'stock_opnames' => [
                'data'  => $dataStockOpname,
                'links' => PaginationData::formatPaginationLinks($queryStockOpnames),
                'current_page'  => $queryStockOpnames->currentPage(),
                'per_page'  => $queryStockOpnames->perPage(),
                'total' => $queryStockOpnames->total()
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $dataProducts = Product::withoutTrashed()
            ->whereHas('unitConversions', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->with(['defaultUnit', 'unitConversions',
                'currentStocks' => function ($query) {
                    $query->where('status_running', '!=', StatusRunningCurrentStockEnum::SUDAH_BERAKHIR->value);
                }
            ])
            ->get();

        $dataUnitConversions = UnitConversion::withoutTrashed()
            ->whereHas('product', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->with(['product', 'fromUnit', 'toUnit'])
            ->get();

        $dataUnits = Unit::withoutTrashed()->get();

        $statusStockOpnameDetails = collect(StatusStockOpnameDetailEnum::cases())->map(function ($statusStockOpnameDetail) {
            return [
                'label' => $statusStockOpnameDetail->label(),
                'name'  => $statusStockOpnameDetail->value
            ];
        });

        $currentUser = Auth::user();

        return Inertia::render('inventory/stock_opname/Create', [
            'title' => 'Stock Opname',
            'desc'  => 'Tambah Stock Opname',
            'products'  => new ProductCollection($dataProducts),
            'unit_conversions' => new UnitConversionCollection($dataUnitConversions),
            'units' => new UnitCollection($dataUnits),
            'status_so_details'  => $statusStockOpnameDetails,
            'currentUser' => $currentUser,
            'so_number' => StockOpname::generateStockOpnameNumber()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStockOpnameRequest $request)
    {
        DB::beginTransaction();

        try {
            $validatedData = $request->validated();
            $stockOpnameData = StockOpname::create($validatedData);

            if ($stockOpnameData) {
                $stockOpnameData->details()->createMany($validatedData['details']);
            }

            DB::commit();
            return redirect()->route('admin.inventory.stock_opname.index')->with('success', 'Stock Opname berhasil dibuat!');
        } catch (\Exception $th) {
            DB::rollBack();
            return redirect()->route('admin.inventory.stock_opname.index')->with('error', 'Stock Opname gagal dibuat!');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(StockOpname $stockOpname)
    {
        $dataStockOpname = $stockOpname->load([
            'user',
            'userValidator',
            'userAck',
            'userReject',
            'details.product',
            'details.unit',
            'details.product.unitConversions.fromUnit',
            'details.product.unitConversions.toUnit'
        ]);

        return Inertia::render('inventory/stock_opname/Show', [
            'title' => 'Stock Opname',
            'desc'  => 'Detail Stock Opname',
            'stock_opname'  => new StockOpnameResource($dataStockOpname)
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StockOpname $stockOpname)
    {
        $dataStockOpname = $stockOpname->load([
            'user',
            'userValidator',
            'userAck',
            'userReject',
            'details.product',
            'details.unit',
            'details.product.unitConversions.fromUnit',
            'details.product.unitConversions.toUnit',
            'details.product.currentStocks' => function ($query) {
                $query->where('status_running', '!=', StatusRunningCurrentStockEnum::SUDAH_BERAKHIR->value);
            }
        ]);

        $dataProducts = Product::withoutTrashed()
            ->whereHas('unitConversions', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->with(['defaultUnit', 'unitConversions',
                'currentStocks' => function ($query) {
                    $query->where('status_running', '!=', StatusRunningCurrentStockEnum::SUDAH_BERAKHIR->value);
                }
            ])
            ->get();

        $dataUnitConversions = UnitConversion::withoutTrashed()
            ->whereHas('product', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->with(['product', 'fromUnit', 'toUnit'])
            ->get();

        $dataUnits = Unit::withoutTrashed()->get();

        $statusStockOpnameDetails = collect(StatusStockOpnameDetailEnum::cases())->map(function ($statusStockOpnameDetail) {
            return [
                'label' => $statusStockOpnameDetail->label(),
                'name'  => $statusStockOpnameDetail->value
            ];
        });

        $currentUser = Auth::user();

        return Inertia::render('inventory/stock_opname/Edit', [
            'title' => 'Stock Opname',
            'desc'  => 'Edit Stock Opname',
            'stock_opname'  => new StockOpnameResource($dataStockOpname),
            'products'  => new ProductCollection($dataProducts),
            'unit_conversions' => new UnitConversionCollection($dataUnitConversions),
            'units' => new UnitCollection($dataUnits),
            'status_so_details'  => $statusStockOpnameDetails,
            'currentUser' => $currentUser
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStockOpnameRequest $request, StockOpname $stockOpname)
    {
        DB::beginTransaction();

        try {
            $validatedData = $request->validated();
            $stockOpname->update($validatedData);

            if (isset($validatedData['details'])) {
                $stockOpname->details()->delete();
                $stockOpname->details()->createMany($validatedData['details']);
            }

            DB::commit();
            return redirect()->route('admin.inventory.stock_opname.index')->with('success', 'Stock Opname berhasil diubah');
        } catch (\Exception $th) {
            DB::rollBack();
            return redirect()->route('admin.inventory.stock_opname.index')->with('error', 'Stock Opname gagal diubah');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StockOpname $stockOpname)
    {
        $stockOpname->delete();
        return redirect()->route('admin.inventory.stock_opname.index')->with('success', 'Stock Opname berhasil dihapus!');
    }

    public function showValidatorForm(StockOpname $stockOpname)
    {
        $dataStockOpname = $stockOpname->load([
            'user',
            'userValidator',
            'userAck',
            'userReject',
            'details.product',
            'details.unit',
            'details.product.unitConversions.fromUnit',
            'details.product.unitConversions.toUnit',
            'details.product.currentStocks' => function ($query) {
                $query->where('status_running', '!=', StatusRunningCurrentStockEnum::SUDAH_BERAKHIR->value);
            }
        ]);

        $statusStockOpname = collect(StatusStockOpnameEnum::cases())->map(function ($statusStockOpnameDetail) {
            return [
                'label' => $statusStockOpnameDetail->label(),
                'name'  => $statusStockOpnameDetail->value
            ];
        });

        $currentUser = Auth::user();

        return Inertia::render('inventory/stock_opname/Validated', [
            'title' => 'Stock Opname',
            'desc'  => 'Validated Stock Opname',
            'stock_opname'  => new StockOpnameResource($dataStockOpname),
            'status_so'  => $statusStockOpname,
            'currentUser' => $currentUser
        ]);
    }

    public function submitValidator(ValidatorStockOpnameRequest $request, StockOpname $stockOpname)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validated();
            $stockOpname->update($validated);

            DB::commit();
            return redirect()->route('admin.inventory.stock_opname.index')->with('success', 'Data Stock Opname Berhasil Ter-validasi!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.inventory.stock_opname.index')->with('error', 'Gagal Ter-validasi data Stock Opname!');
        }
    }

    public function showApprovalForm(StockOpname $stockOpname)
    {
        $dataStockOpname = $stockOpname->load([
            'user',
            'userValidator',
            'userAck',
            'userReject',
            'details.product',
            'details.unit',
            'details.product.unitConversions.fromUnit',
            'details.product.unitConversions.toUnit',
            'details.product.currentStocks' => function ($query) {
                $query->where('status_running', '!=', StatusRunningCurrentStockEnum::SUDAH_BERAKHIR->value);
            }
        ]);

        $statusStockOpname = collect(StatusStockOpnameEnum::cases())->map(function ($statusStockOpnameDetail) {
            return [
                'label' => $statusStockOpnameDetail->label(),
                'name'  => $statusStockOpnameDetail->value
            ];
        });

        $currentUser = Auth::user();

        return Inertia::render('inventory/stock_opname/Approvment', [
            'title' => 'Stock Opname',
            'desc'  => 'Approval Stock Opname',
            'stock_opname'  => new StockOpnameResource($dataStockOpname),
            'status_so'  => $statusStockOpname,
            'currentUser' => $currentUser
        ]);
    }

    public function submitApproval(ApprovalStockOpnameRequest $request, StockOpname $stockOpname)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validated();
            $stockOpname->update($validated);

            if (
                !empty($validated['user_ack_id'])
            ) {
                Event::dispatch(new StockOpnameApproved($stockOpname));
            }

            DB::commit();
            return redirect()->route('admin.inventory.stock_opname.index')->with('success', 'Data Stock Opname Berhasil Ter-Approval!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.inventory.stock_opname.index')->with('error', 'Gagal Ter-Approval data Stock Opname!');
        }
    }
}
