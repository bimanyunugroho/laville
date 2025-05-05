<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusNotesStockOutEnum;
use App\Enums\StatusStockOutEnum;
use App\Events\StockOutApproved;
use App\Helpers\PaginationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalStockOutRequest;
use App\Http\Resources\Admin\StockOutDetailResource;
use App\Models\StockOut;
use App\Http\Requests\StoreStockOutRequest;
use App\Http\Requests\UpdateStockOutRequest;
use App\Http\Resources\Admin\StockOutResource;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\SupplierCollection;
use App\Http\Resources\UnitCollection;
use App\Http\Resources\UnitConversionCollection;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\UnitConversion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class StockOutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $queryStockOuts = StockOut::query()
            ->with(['supplier', 'user', 'userAck', 'userReject'])
            ->when($request->filled('search'), function (Builder $query) use ($request) {
                $searchTerm = trim($request->input('search'));
                $query->where(function (Builder $query) use ($searchTerm) {
                    $query->where('stock_out_number', 'ilike', "%{$searchTerm}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $dataStockOuts = $queryStockOuts->map(function ($queryStockOut) {
            return new StockOutResource($queryStockOut);
        });

        return Inertia::render('inventory/stock_out/Index', [
            'title' => 'Pengeluaran Barang',
            'desc'  => 'Data Pengeluaran Barang',
            'stock_outs'  => [
                'data'  => $dataStockOuts,
                'links' => PaginationData::formatPaginationLinks($queryStockOuts),
                'current_page'  => $queryStockOuts->currentPage(),
                'per_page'  => $queryStockOuts->perPage(),
                'total' => $queryStockOuts->total()
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $dataSuppliers = Supplier::withoutTrashed()->get();
        $dataProducts = Product::withoutTrashed()
            ->whereHas('unitConversions', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->with(['defaultUnit', 'unitConversions', 'currentStocks'])
            ->get();
        $dataUnitConversions = UnitConversion::withoutTrashed()
            ->whereHas('product', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->with(['product', 'fromUnit', 'toUnit'])
            ->get();
        $dataUnits = Unit::withoutTrashed()->get();
        $statusnNotesStockOuts = collect(StatusNotesStockOutEnum::cases())->map(function ($statusnNotesStockOut) {
            return [
                'label' => $statusnNotesStockOut->label(),
                'name' => $statusnNotesStockOut->value,
            ];
        });

        // Tambahkan user login
        $currentUser = Auth::user();

        return Inertia::render('inventory/stock_out/Create', [
            'title' => 'Pengeluaran Barang',
            'desc'  => 'Tambah Pengeluaran Barang',
            'suppliers' => new SupplierCollection($dataSuppliers),
            'products'  => new ProductCollection($dataProducts),
            'unit_conversions' => new UnitConversionCollection($dataUnitConversions),
            'units' => new UnitCollection($dataUnits),
            'status_notes'    => $statusnNotesStockOuts,
            'currentUser' => $currentUser,
            'stock_out_number' => StockOut::generateStockOutNumber()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStockOutRequest $request)
    {
        DB::beginTransaction();

        try {
            $validatedData = $request->validated();
            $stockOutData = StockOut::create($validatedData);

            if (!$stockOutData) {
                return redirect()->route('admin.inventory.stock_out.index')->with('error', 'Gagal membuat data pengeluaran barang!');
            }
            $stockOutData->details()->createMany($validatedData['details']);

            DB::commit();
            return redirect()->route('admin.inventory.stock_out.index')->with('success', 'Berhasil menyimpan data pengeluaran barang!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.inventory.stock_out.index')->with('error', 'Gagal menyimpan data pengeluaran barang!');
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(StockOut $stockOut)
    {
        $dataStockOut = $stockOut->load([
            'supplier',
            'user',
            'userAck',
            'userReject',
            'details.product.currentStocks',
            'details.unit',
            'details.product.unitConversions.fromUnit',
            'details.product.unitConversions.toUnit'
        ]);

        return Inertia::render('inventory/stock_out/Show', [
            'title' => 'Pengeluran Barang',
            'desc'  => 'Detail Pengeluran Barang',
            'stock_out'  => new StockOutResource($dataStockOut)
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StockOut $stockOut)
    {
        $dataStockOut = $stockOut->load([
            'supplier',
            'user',
            'userAck',
            'userReject',
            'details.product.currentStocks',
            'details.unit',
            'details.product.unitConversions.fromUnit',
            'details.product.unitConversions.toUnit'
        ]);

        $dataSuppliers = Supplier::withoutTrashed()->get();
        $dataProducts = Product::withoutTrashed()
            ->whereHas('unitConversions', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->with(['defaultUnit', 'unitConversions', 'currentStocks'])
            ->get();
        $dataUnitConversions = UnitConversion::withoutTrashed()
            ->whereHas('product', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->with(['product', 'fromUnit', 'toUnit'])
            ->get();
        $dataUnits = Unit::withoutTrashed()->get();
        $statusnNotesStockOuts = collect(StatusNotesStockOutEnum::cases())->map(function ($statusnNotesStockOut) {
            return [
                'label' => $statusnNotesStockOut->label(),
                'name' => $statusnNotesStockOut->value,
            ];
        });

        // Tambahkan user login
        $currentUser = Auth::user();

        return Inertia::render('inventory/stock_out/Edit', [
            'title' => 'Pengeluaran Barang',
            'desc'  => 'Edit Pengeluaran Barang',
            'stock_out' => new StockOutResource($dataStockOut),
            'suppliers' => new SupplierCollection($dataSuppliers),
            'products'  => new ProductCollection($dataProducts),
            'unit_conversions' => new UnitConversionCollection($dataUnitConversions),
            'units' => new UnitCollection($dataUnits),
            'status_notes'    => $statusnNotesStockOuts,
            'currentUser' => $currentUser
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStockOutRequest $request, StockOut $stockOut)
    {
        DB::beginTransaction();

        try {
            $validatedData = $request->validated();
            $stockOut->update($validatedData);

            if (isset($validatedData['details'])) {
                $stockOut->details()->delete();
                $stockOut->details()->createMany($validatedData['details']);
            }
            
            DB::commit();

            return redirect()->route('admin.inventory.stock_out.index')->with('success', 'Data pengeluaran barang berhasil diubah!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.inventory.stock_out.index')->with('error', 'Gagal mengubah data pengeluaran barang!');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StockOut $stockOut)
    {
        $stockOut->delete();
        return redirect()->route('admin.inventory.stock_out.index')->with('error', 'Gagal menghapus data pengeluaran barang!');
    }

    public function showApprovalForm(StockOut $stockOut)
    {
        $dataStockOut = $stockOut->load([
            'supplier',
            'user',
            'userAck',
            'userReject',
            'details.product.currentStocks',
            'details.unit',
            'details.product.unitConversions.fromUnit',
            'details.product.unitConversions.toUnit'
        ]);

        $statusnStockOuts = collect(StatusStockOutEnum::cases())->map(function ($statusnStockOut) {
            return [
                'label' => $statusnStockOut->label(),
                'name' => $statusnStockOut->value,
            ];
        });

        $currentUser = Auth::user();

        return Inertia::render('inventory/stock_out/Approvment', [
            'title' => 'Pengeluaran Barang',
            'desc'  => 'Approval Pengeluaran Barang',
            'stock_out' => new StockOutResource($dataStockOut),
            'stock_out_approval' => new StockOutResource($dataStockOut),
            'status'    => $statusnStockOuts,
            'currentUser' => $currentUser
        ]);
    }

    public function submitApproval(ApprovalStockOutRequest $request, StockOut $stockOut)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validated();
            $stockOut->update($validated);

            if (!empty($validated['user_ack_id'])) {
                Event::dispatch(new StockOutApproved($stockOut));
            }

            DB::commit();
            return redirect()->route('admin.inventory.stock_out.index')->with('success', 'Data Pengeluaran Barang Berhasil DiApprove!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.inventory.stock_out.index')->with('error', 'Gagal DiApprove data Pengeluaran Barang!');
        }
    }
}
