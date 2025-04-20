<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PaginationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\Admin\ProductResource;
use App\Http\Resources\Admin\UnitResource;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $queryProducts = Product::query()
            ->with(['defaultUnit'])
            ->when($request->input('input'), function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'ilike', "%{$search}%")
                        ->orWhere('code', 'ilike', "%{$search}%")
                        ->orWhere('variant_name', 'ilike', "%{$search}%");
                })->orWhereRelation('defaultUnit', function ($defaultUnitQuery) use ($search) {
                    $defaultUnitQuery->where('code', 'ilike', "%{$search}%")
                        ->orWhere('name', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->paginate();

        $dataProducts = $queryProducts->map(function($queryProduct) {
            return new ProductResource($queryProduct);
        });

        return Inertia::render('master/product/Index', [
            'title' => 'Master Produk',
            'desc'  => 'Master Produk',
            'products'  => [
                'data'  => $dataProducts,
                'links' => PaginationData::formatPaginationLinks($queryProducts),
                'current_page'  => $queryProducts->currentPage(),
                'per_page'  => $queryProducts->perPage(),
                'total' => $queryProducts->total()
            ]
        ]);

    }

    public function create()
    {
        $dataUnits = Unit::withoutTrashed()->get();
        return Inertia::render('master/product/Create', [
            'title' => 'Master Produk',
            'desc'  => 'Tambah Master Produk',
            'units' => new UnitResource($dataUnits)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $dataProduk = Product::create($request->validated());
        return redirect()->route('admin.master.produk.index')->with('success', 'Data Produk Berhasil Disimpan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $productWithDefaultUnitData = $product->with(['defaultUnit']);
        return Inertia::render('', [
            'title' => 'Master Produk',
            'desc'  => 'Detail Master Produk',
            'product'   => new ProductResource($productWithDefaultUnitData)
        ]);
    }

    public function edit(Product $product)
    {
        $dataUnits = Unit::withoutTrashed()->get();
        $productWithDefaultUnitData = $product->with(['defaultUnit']);
        return Inertia::render('master/product/Edit', [
            'title' => 'Master Produk',
            'desc'  => 'Edit Master Produk',
            'product'   => new ProductResource($productWithDefaultUnitData),
            'units' => new UnitResource($dataUnits)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->validated());
        return redirect()->route('admin.master.produk.index')->with('success', 'Data Produk Berhasil Diubah!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.master.produk.index')->with('success', 'Data Produk Berhasil Dihapus!');
    }
}
