<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusReceiptEnum;
use App\Events\GoodReceiptApproved;
use App\Helpers\ApiResponse;
use App\Helpers\PaginationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalGoodReceiptRequest;
use App\Http\Resources\Admin\GoodReceiptResource;
use App\Http\Resources\Admin\PurchaseOrderResource;
use App\Models\GoodReceipt;
use App\Http\Requests\StoreGoodReceiptRequest;
use App\Http\Requests\UpdateGoodReceiptRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\SupplierCollection;
use App\Http\Resources\UnitCollection;
use App\Http\Resources\UnitConversionCollection;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\UnitConversion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class GoodReceiptController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $queryGoodReceipts = GoodReceipt::query()
            ->with(['supplier'])
            ->when($request->filled('search'), function (Builder $query) use ($request) {
                $searchTerm = trim($request->input('search'));
                $query->where(function (Builder $query) use ($searchTerm) {
                    $query->where('receipt_number', 'ilike', "%{$searchTerm}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $dataGoodReceipt = $queryGoodReceipts->map(function ($queryGoodReceipt) {
            return new GoodReceiptResource($queryGoodReceipt);
        });

        return Inertia::render('inventory/good_receipt/Index', [
            'title' => 'Penerimaan Barang',
            'desc'  => 'Data Penerimaan Barang',
            'good_receipts' => [
                'data'  => $dataGoodReceipt,
                'links' => PaginationData::formatPaginationLinks($queryGoodReceipts),
                'current_page'  => $queryGoodReceipts->currentPage(),
                'per_page'  => $queryGoodReceipts->perPage(),
                'total' => $queryGoodReceipts->total()
            ]
        ]);
    }

    /**
     * Search purchase order by PO number.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search_purchase_order_by_no_po(Request $request)
    {
        try {
            // Validasi request
            $validator = Validator::make($request->all(), [
                'po_number' => 'required|string'
            ]);

            if ($validator->fails()) {
                return ApiResponse::validationError($validator->errors(), 'Invalid PO number format');
            }

            $poNumber = trim($request->po_number);
            $purchaseOrders = PurchaseOrder::with([
                    'supplier',
                    'user',
                    'userAck',
                    'userReject',
                    'details.product',
                    'details.unit',
                    'details.product.unitConversions.fromUnit',
                    'details.product.unitConversions.toUnit'
                ])
                ->where('po_number', 'like', "%{$poNumber}%")
                ->where('is_active', true)
                ->whereNotNull('user_ack_id')
                ->where('user_ack_id', 1)
                ->orderBy('created_at', 'desc')
                ->paginate(10)
                ->withQueryString();

            if ($purchaseOrders->isEmpty()) {
                return ApiResponse::noContent('Data PO Tidak Ditemukan');
            }

            $poIds = $purchaseOrders->pluck('id')->toArray();
            $existInGoodReceipt = GoodReceipt::whereIn('purchase_order_id', $poIds)
                ->whereNull('deleted_at')
                ->whereIn('status_receipt', ['PROSESS', 'RECEIVED'])
                ->exists();

            if ($existInGoodReceipt) {
                return ApiResponse::noContent('Data PO sedang dalam masa proses atau sudah diterima.');
            }

            return ApiResponse::success(
                PurchaseOrderResource::collection($purchaseOrders),
                'Purchase Order data retrieved successfully'
            );

        } catch (ModelNotFoundException $e) {
            return ApiResponse::notFound('Purchase Order not found');
        } catch (\Exception $e) {
            return ApiResponse::serverError('Failed to retrieve Purchase Order data', $e);
        }
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
            ->with(['defaultUnit', 'unitConversions'])
            ->get();
        $dataUnitConversions = UnitConversion::withoutTrashed()
            ->whereHas('product', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->with(['product', 'fromUnit', 'toUnit'])
            ->get();
        $dataUnits = Unit::withoutTrashed()->get();
        $statusReceiptOrders = collect(StatusReceiptEnum::cases())->map(function ($statusReceiptOrder) {
            return [
                'label' => $statusReceiptOrder->label(),
                'name'  => $statusReceiptOrder->value
            ];
        });

        // Tambahkan user login
        $currentUser = Auth::user();

        return Inertia::render('inventory/good_receipt/Create', [
            'title' => 'Penerimaan Barang',
            'desc'  => 'Tambah Penerimaan Barang',
            'suppliers' => new SupplierCollection($dataSuppliers),
            'products'  => new ProductCollection($dataProducts),
            'unit_conversions' => new UnitConversionCollection($dataUnitConversions),
            'units' => new UnitCollection($dataUnits),
            'status_receipts'    => $statusReceiptOrders,
            'currentUser' => $currentUser,
            'receipt_number'    => GoodReceipt::generateReceiptNumber()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGoodReceiptRequest $request)
    {
        DB::beginTransaction();

        try {
            $validatedData = $request->validated();
            $goodReceipt = GoodReceipt::create($validatedData);
            if ($goodReceipt) {
                $goodReceipt->details()->createMany($validatedData['details']);
            }

            DB::commit();
            return redirect()->route('admin.inventory.good_receipt.index')->with('success', 'Data Penerimaan Barang Berhasil DiSimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.inventory.good_receipt.index')->with('error', 'Gagal menyimpan data Penerimaan Barang!');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(GoodReceipt $goodReceipt)
    {
        $dataGoodReceipt = $goodReceipt->load([
            'supplier',
            'user',
            'userAck',
            'userReject',
            'purchaseOrder',
            'details.product',
            'details.unit',
            'details.product.unitConversions.fromUnit',
            'details.product.unitConversions.toUnit',
            'details.purchaseOrderDetail'
        ]);

        return Inertia::render('inventory/good_receipt/Show', [
            'title' => 'Penerimaan Barang',
            'desc'  => 'Detail Penerimaan Barang',
            'good_receipt'  => new GoodReceiptResource($dataGoodReceipt)
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GoodReceipt $goodReceipt)
    {
        $dataGoodReceipt = $goodReceipt->load([
            'supplier',
            'user',
            'userAck',
            'userReject',
            'purchaseOrder',
            'details.product',
            'details.unit',
            'details.product.unitConversions.fromUnit',
            'details.product.unitConversions.toUnit',
        ]);

        $dataSuppliers = Supplier::withoutTrashed()->get();
        $dataProducts = Product::withoutTrashed()
            ->whereHas('unitConversions', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->with(['defaultUnit', 'unitConversions'])
            ->get();
        $dataUnitConversions = UnitConversion::withoutTrashed()
            ->whereHas('product', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->with(['product', 'fromUnit', 'toUnit'])
            ->get();
        $dataUnits = Unit::withoutTrashed()->get();
        $statusReceiptOrders = collect(StatusReceiptEnum::cases())->map(function ($statusReceiptOrder) {
            return [
                'label' => $statusReceiptOrder->label(),
                'name'  => $statusReceiptOrder->value
            ];
        });

        // Tambahkan user login
        $currentUser = Auth::user();

        return Inertia::render('inventory/good_receipt/Edit', [
            'title' => 'Penerimaan Barang',
            'desc'  => 'Edit Penerimaan Barang',
            'good_receipt'  => new GoodReceiptResource($dataGoodReceipt),
            'suppliers' => new SupplierCollection($dataSuppliers),
            'products'  => new ProductCollection($dataProducts),
            'unit_conversions' => new UnitConversionCollection($dataUnitConversions),
            'units' => new UnitCollection($dataUnits),
            'status_receipts'    => $statusReceiptOrders,
            'currentUser' => $currentUser
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGoodReceiptRequest $request, GoodReceipt $goodReceipt)
    {
        DB::beginTransaction();

        try {
            $validatedData = $request->validated();
            $goodReceipt->update($validatedData);

            if (isset($validatedData['details'])) {
                $goodReceipt->details()->delete();
                $goodReceipt->details()->createMany($validatedData['details']);
            }

            DB::commit();
            return redirect()->route('admin.inventory.good_receipt.index')->with('success', 'Data Penerimaan Barang Berhasil Diubah!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.inventory.good_receipt.index')->with('error', 'Gagal mengubah data Penerimaan Barang!');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GoodReceipt $goodReceipt)
    {
        $goodReceipt->delete();
        return redirect()->route('admin.inventory.good_receipt.index')->with('success', 'Data Penerimaan Barang Berhasil Dihapus!');
    }

    public function showApprovalForm(GoodReceipt $goodReceipt)
    {
        $dataGoodReceipt = $goodReceipt->load([
            'supplier',
            'user',
            'userAck',
            'userReject',
            'purchaseOrder',
            'details.product',
            'details.unit',
            'details.product.unitConversions.fromUnit',
            'details.product.unitConversions.toUnit',
            'details.purchaseOrderDetail'
        ]);

        $statusReceiptOrders = collect(StatusReceiptEnum::cases())->map(function ($statusReceiptOrder) {
            return [
                'label' => $statusReceiptOrder->label(),
                'name'  => $statusReceiptOrder->value
            ];
        });

        $currentUser = Auth::user();

        return Inertia::render('inventory/good_receipt/Approvment', [
            'title' => 'Penerimaan Barang',
            'desc'  => 'Approvment/Rejected Penerimaan Barang',
            'good_receipt'  => new GoodReceiptResource($dataGoodReceipt),
            'good_receipt_approval'  => new GoodReceiptResource($dataGoodReceipt),
            'status'    => $statusReceiptOrders,
            'currentUser' => $currentUser
        ]);
    }

    public function submitApproval(ApprovalGoodReceiptRequest $request, GoodReceipt $goodReceipt)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validated();
            $goodReceipt->update($validated);

            if (
                !empty($validated['user_ack_id'])
            ) {
                Event::dispatch(new GoodReceiptApproved($goodReceipt));
            }

            DB::commit();
            return redirect()->route('admin.inventory.good_receipt.index')->with('success', 'Data Penerimaan Barang Berhasil DiApprove!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.inventory.good_receipt.index')->with('error', 'Gagal DiApprove data Penerimaan Barang!');
        }
    }
}
