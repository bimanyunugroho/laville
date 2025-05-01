<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusPOEnum;
use App\Events\PurchaseOrderApproved;
use App\Helpers\PaginationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApprovalPurchaseOrderRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\SupplierCollection;
use App\Http\Resources\UnitCollection;
use App\Models\PurchaseOrder;
use App\Http\Requests\StorePurchaseOrderRequest;
use App\Http\Requests\UpdatePurchaseOrderRequest;
use App\Http\Resources\Admin\PurchaseOrderResource;
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
use Inertia\Inertia;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $queryPurchaseOrders = PurchaseOrder::query()
            ->with(['supplier', 'user', 'userAck', 'userReject'])
            ->when($request->filled('search'), function (Builder $query) use ($request) {
                $searchTerm = trim($request->input('search'));
                $query->where(function (Builder $query) use ($searchTerm) {
                    $query->where('po_number', 'ilike', "%{$searchTerm}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $dataPurchaseOrders = $queryPurchaseOrders->map(function ($queryPurchaseOrder) {
            return new PurchaseOrderResource($queryPurchaseOrder);
        });

        return Inertia::render('inventory/purchase_order/Index', [
            'title' => 'Purchase Order',
            'desc'  => 'Data Purchase Order',
            'purchase_orders' => [
                'data'  => $dataPurchaseOrders,
                'links' => PaginationData::formatPaginationLinks($queryPurchaseOrders),
                'current_page'  => $queryPurchaseOrders->currentPage(),
                'per_page'  => $queryPurchaseOrders->perPage(),
                'total' => $queryPurchaseOrders->total()
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
            ->with(['defaultUnit', 'unitConversions'])
            ->get();
        $dataUnitConversions = UnitConversion::withoutTrashed()
            ->whereHas('product', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->with(['product', 'fromUnit', 'toUnit'])
            ->get();
        $dataUnits = Unit::withoutTrashed()->get();
        $statusPurchaseOrders = collect(StatusPOEnum::cases())->map(function ($statusPurchaseOrder) {
            return [
                'label' => $statusPurchaseOrder->label(),
                'name' => $statusPurchaseOrder->value,
            ];
        });

        // Tambahkan user login
        $currentUser = Auth::user();

        return Inertia::render('inventory/purchase_order/Create', [
            'title' => 'Purchase Order',
            'desc'  => 'Tambah Purchase Order',
            'suppliers' => new SupplierCollection($dataSuppliers),
            'products'  => new ProductCollection($dataProducts),
            'unit_conversions' => new UnitConversionCollection($dataUnitConversions),
            'units' => new UnitCollection($dataUnits),
            'status'    => $statusPurchaseOrders,
            'currentUser' => $currentUser,
            'po_number' => PurchaseOrder::generatePoNumber()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePurchaseOrderRequest $request)
    {
        DB::beginTransaction();

        try {
            $validatedData = $request->validated();
            $purchaseOrder = PurchaseOrder::create($validatedData);
            if ($purchaseOrder) {
                $purchaseOrder->details()->createMany($validatedData['details']);
            }

            DB::commit();
            return redirect()->route('admin.inventory.purchase_order.index')->with('success', 'Data Purchase Order Berhasil DiSimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.inventory.purchase_order.index')->with('error', 'Gagal menyimpan data Purchase Order!');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        $dataPurchaseOrder = $purchaseOrder->load([
            'supplier',
            'user',
            'userAck',
            'userReject',
            'details.product',
            'details.unit',
            'details.product.unitConversions.fromUnit',
            'details.product.unitConversions.toUnit',
        ]);

        return Inertia::render('inventory/purchase_order/Show', [
            'title' => 'Purchase Order',
            'desc'  => 'Detail Purchase Order',
            'purchase_order'  => new PurchaseOrderResource($dataPurchaseOrder)
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PurchaseOrder $purchaseOrder)
    {
        $dataPurchaseOrder = $purchaseOrder->load([
            'supplier',
            'user',
            'userAck',
            'details.product',
            'details.unit',
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
        $statusPurchaseOrders = collect(StatusPOEnum::cases())->map(function ($statusPurchaseOrder) {
            return [
                'label' => $statusPurchaseOrder->label(),
                'name' => $statusPurchaseOrder->value,
            ];
        });

        $currentUser = Auth::user();

        return Inertia::render('inventory/purchase_order/Edit', [
            'title' => 'Purchase Order',
            'desc'  => 'Edit Purchase Order',
            'purchase_order'  => new PurchaseOrderResource($dataPurchaseOrder),
            'suppliers' => new SupplierCollection($dataSuppliers),
            'products'  => new ProductCollection($dataProducts),
            'unit_conversions' => new UnitConversionCollection($dataUnitConversions),
            'units' => new UnitCollection($dataUnits),
            'status'    => $statusPurchaseOrders,
            'currentUser' => $currentUser
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder)
    {
        DB::beginTransaction();

        try {
            $validatedData = $request->validated();
            $purchaseOrder->update($validatedData);

            if (isset($validatedData['details'])) {
                $purchaseOrder->details()->delete();
                $purchaseOrder->details()->createMany($validatedData['details']);
            }

            DB::commit();
            return redirect()->route('admin.inventory.purchase_order.index')->with('success', 'Data Purchase Order Berhasil Diubah!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.inventory.purchase_order.index')->with('error', 'Gagal mengubah data Purchase Order!');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->delete();
        return redirect()->route('admin.inventory.purchase_order.index')->with('success', 'Data Purchase Order Berhasil DiHapus!');
    }

    public function showApprovalForm(PurchaseOrder $purchaseOrder)
    {
        $dataPurchaseOrder = $purchaseOrder->load([
            'supplier',
            'user',
            'userAck',
            'userReject',
            'details.product',
            'details.unit',
            'details.product.unitConversions.fromUnit',
            'details.product.unitConversions.toUnit',
        ]);

        $statusPurchaseOrders = collect(StatusPOEnum::cases())->map(function ($statusPurchaseOrder) {
            return [
                'label' => $statusPurchaseOrder->label(),
                'name' => $statusPurchaseOrder->value,
            ];
        });

        $currentUser = Auth::user();

        return Inertia::render('inventory/purchase_order/Approvment', [
            'title' => 'Purchase Order',
            'desc'  => 'Approvment/Rejected Purchase Order',
            'purchase_order'  => new PurchaseOrderResource($dataPurchaseOrder),
            'purchase_order_approval'  => new PurchaseOrderResource($dataPurchaseOrder),
            'status'    => $statusPurchaseOrders,
            'currentUser' => $currentUser
        ]);
    }

    public function submitApproval(ApprovalPurchaseOrderRequest $request, PurchaseOrder $purchaseOrder)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validated();
            $purchaseOrder->update($validated);

            if (
                !empty($validated['user_ack_id']) &&
                !empty($validated['ack_date']) &&
                ($validated['status'] ?? null) === StatusPOEnum::RECEIVED
            ) {
                Event::dispatch(new PurchaseOrderApproved($purchaseOrder));
            }

            DB::commit();
            return redirect()->route('admin.inventory.purchase_order.index')->with('success', 'Data Purchase Order Berhasil DiApprove!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.inventory.purchase_order.index')->with('error', 'Gagal DiApprove data Purchase Order!');
        }
    }
}
