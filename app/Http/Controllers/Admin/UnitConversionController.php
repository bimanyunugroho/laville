<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PaginationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUnitConversionRequest;
use App\Http\Requests\UpdateUnitConversionRequest;
use App\Http\Resources\Admin\UnitConversionResource;
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
            ->with(['product', 'fromUnit', 'toUnit'])
            ->when($request->input('search'), function ($query, $search) {
                $query->whereHas('product', function ($productQuery,) use ($search) {
                    $productQuery->where('code', 'ilike', "%{$search}%")
                        ->orWhere('name', 'ilike', "%{$search}%")
                        ->orWhere('variant_name', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
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
        return Inertia::render('master/unit_conversion/Create', [
            'title' => 'Master Konversi Produk',
            'desc'  => 'Tambah Konversi Produk'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUnitConversionRequest $request)
    {
        $unitConversionData = UnitConversion::create($request->validated());
        return redirect()->route('admin.master.unit_konversi.index')->with('success', 'Data Konversi Produk Berhasil Disimpan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(UnitConversion $unitConversion)
    {
        return Inertia::render('master/unit_conversion/Show', [
            'title' => 'Master Konversi Produk',
            'desc'  => 'Detail Konversi Produk'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUnitConversionRequest $request, UnitConversion $unitConversion)
    {
        $unitConversion->update($request->validated());
        return redirect()->route('admin.master.unit_konversi.index')->with('success', 'Data Konversi Produk Berhasil Diubah!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UnitConversion $unitConversion)
    {
        $unitConversion->delete();
        return redirect()->route('admin.master.unit_konversi.index')->with('success', 'Data Konversi Produk Berhasil Dihapus!');
    }
}
