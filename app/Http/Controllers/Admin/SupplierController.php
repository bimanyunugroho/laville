<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PaginationData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\SupplierResource;
use App\Models\Supplier;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $querySuppliers = Supplier::query()
            ->when($request->filled('search'), function (Builder $query) use ($request) {
                $searchTerm = trim($request->input('search'));
                $query->where(function (Builder $query) use ($searchTerm) {
                    $query->where('code', 'ilike', "%{$searchTerm}%")
                        ->orWhere('name', 'ilike', "%{$searchTerm}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $dataSuppliers = $querySuppliers->map(function ($querySupplier) {
            return new SupplierResource($querySupplier);
        });

        return Inertia::render('inventory/supplier/Index', [
            'title' => 'Master Supplier',
            'desc'  => 'Data Supplier',
            'suppliers' => [
                'data'  => $dataSuppliers,
                'links' => PaginationData::formatPaginationLinks($querySuppliers),
                'current_page'  => $querySuppliers->currentPage(),
                'per_page'  => $querySuppliers->perPage(),
                'total' => $querySuppliers->total()
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('inventory/supplier/Create', [
            'title' => 'Master Supplier',
            'desc'  => 'Tambah Data Supplier'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSupplierRequest $request)
    {
        $dataSupplier = Supplier::create($request->validated());
        return redirect()->route('admin.inventory.supplier.index')->with('success', 'Data Supplier Berhasil Ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
        return Inertia::render('inventory/supplier/Show', [
            'title' => 'Master Supplier',
            'desc'  => 'Tambah Data Supplier',
            'supplier'  => new SupplierResource($supplier)
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $supplier)
    {
        return Inertia::render('inventory/supplier/Edit', [
            'title' => 'Master Supplier',
            'desc'  => 'Tambah Data Supplier',
            'supplier'  => new SupplierResource($supplier)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        $supplier->update($request->validated());
        return redirect()->route('admin.inventory.supplier.index')->with('success', 'Data Supplier Berhasil Diubah!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('admin.inventory.supplier.index')->with('success', 'Data Supplier Berhasil Dihapus!');
    }
}
