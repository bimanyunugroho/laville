<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PaginationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUnitConversionRequest;
use App\Http\Requests\UpdateUnitConversionRequest;
use App\Http\Resources\Admin\UnitConversionResource;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\UnitCollection;
use App\Models\Product;
use App\Models\Unit;
use App\Models\UnitConversion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UnitConversionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $queryUnitConversions = UnitConversion::query()
            ->with(['product', 'fromUnit', 'toUnit', 'product.defaultUnit'])
            ->when($request->input('search'), function ($query, $search) {
                $query->whereHas('product', function ($productQuery,) use ($search) {
                    $productQuery->where('code', 'ilike', "%{$search}%")
                        ->orWhere('name', 'ilike', "%{$search}%")
                        ->orWhere('variant_name', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $dataUnitConversions = $queryUnitConversions->map(function($queryUnitConversion) {
            return new UnitConversionResource($queryUnitConversion);
        });

        return Inertia::render('master/unit_conversion/Index', [
            'title' => 'Master Konversi Produk',
            'desc'  => 'Master Konversi Produk',
            'unit_conversions'  => [
                'data'  => $dataUnitConversions,
                'links' => PaginationData::formatPaginationLinks($queryUnitConversions),
                'current_page'  => $queryUnitConversions->currentPage(),
                'per_page'  => $queryUnitConversions->perPage(),
                'total' => $queryUnitConversions->total()
            ]
        ]);
    }

    public function create()
    {
        $dataProducts = Product::query()
            ->with('defaultUnit')
            ->get();
        $dataUnits = Unit::all();
        return Inertia::render('master/unit_conversion/Create', [
            'title' => 'Master Konversi Produk',
            'desc'  => 'Tambah Konversi Produk',
            'products'  => new ProductCollection($dataProducts),
            'from_units'  => new UnitCollection($dataUnits),
            'to_units'  => new UnitCollection($dataUnits)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUnitConversionRequest $request)
    {
        $unitConversionData = UnitConversion::create($request->validated());
        return redirect()->route('admin.master.unit_conversion.index')->with('success', 'Data Konversi Produk Berhasil Disimpan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(UnitConversion $unitConversion)
    {
        $dataUnitConversion = $unitConversion->load(['product', 'fromUnit', 'toUnit', 'product.defaultUnit']);
        return Inertia::render('master/unit_conversion/Show', [
            'title' => 'Master Konversi Produk',
            'desc'  => 'Detail Konversi Produk',
            'unit_conversion'   => new UnitConversionResource($dataUnitConversion)
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function edit(UnitConversion $unitConversion)
    {
        $dataUnitConversion = $unitConversion->load(['product', 'fromUnit', 'toUnit', 'product.defaultUnit']);
        $dataProducts = Product::query()
            ->with('defaultUnit')
            ->get();
        $dataUnits = Unit::all();
        return Inertia::render('master/unit_conversion/Edit', [
            'title' => 'Master Konversi Produk',
            'desc'  => 'Edit Konversi Produk',
            'unit_conversion'   => new UnitConversionResource($dataUnitConversion),
            'products'  => new ProductCollection($dataProducts),
            'from_units'  => new UnitCollection($dataUnits),
            'to_units'  => new UnitCollection($dataUnits)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUnitConversionRequest $request, UnitConversion $unitConversion)
    {
        $unitConversion->update($request->validated());
        return redirect()->route('admin.master.unit_conversion.index')->with('success', 'Data Konversi Produk Berhasil Diubah!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UnitConversion $unitConversion)
    {
        $unitConversion->delete();
        return redirect()->route('admin.master.unit_conversion.index')->with('success', 'Data Konversi Produk Berhasil Dihapus!');
    }
}
