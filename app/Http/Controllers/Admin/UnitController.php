<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PaginationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use App\Http\Resources\Admin\UnitResource;
use App\Models\Unit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $queryUnits = Unit::query()
            ->when($request->input('input'), function ($query, $search) {
                $query->where('code', 'ilike', "%{$search}%")
                    ->orWhere('name', 'ilike', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->withQueryString();

        $dataUnits = $queryUnits->map(function($unit) {
            return new UnitResource($unit);
        });

        return Inertia::render('master/unit/Index', [
            'title' => 'Master Satuan',
            'desc'  => 'Master Satuan Dasar',
            'units' => [
                'data'  => $dataUnits,
                'links'  => PaginationData::formatPaginationLinks($queryUnits),
                'current_page'  => $queryUnits->currentPage(),
                'per_page'  => $queryUnits->perPage(),
                'total' => $queryUnits->total()
            ]
        ]);
    }

    public function create()
    {
        return Inertia::render('master/unit/Create', [
            'title' => 'Master Satuan',
            'desc'  => 'Tambah Master Satuan'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUnitRequest $request)
    {
        $dataUnits = Unit::create($request->validated());
        return redirect()->route('admin.master.unit.index')->with('success', 'Data Unit Berhasil Disimpan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Unit $unit)
    {
        return Inertia::render('master/unit/Show', [
            'title' => 'Master Satuan',
            'desc'  => 'Detail Satuan',
            'unit'  => new UnitResource($unit)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUnitRequest $request, Unit $unit)
    {
        $unit->update($request->validated());
        return redirect()->route('admin.master.unit.index')->with('success', 'Data Unit Behasil Diubah!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Unit $unit)
    {
        $unit->delete();
        return redirect()->route('admin.master.unit.index')->with('success', 'Data Unit Berhasil Dihapus!');
    }
}
