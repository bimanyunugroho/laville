<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PaginationData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\CustomerResource;
use App\Models\Customer;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $queryCustomers = Customer::query()
            ->when($request->filled('search'), function (Builder $query) use ($request) {
                $searchTerm = trim($request->input('search'));
                $query->where(function (Builder $query) use ($searchTerm) {
                    $query->where('name', 'ilike', "%{$searchTerm}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $dataQueryCustomers = $queryCustomers->map(function ($queryCustomer) {
            return new CustomerResource($queryCustomer);
        });

        return Inertia::render('transaksi/pelanggan/Index', [
            'title' => 'Pelanggan',
            'desc'  => 'Data Pelanggan',
            'customers' => [
                'data'  => $dataQueryCustomers,
                'links' => PaginationData::formatPaginationLinks($queryCustomers),
                'current_page'  => $queryCustomers->currentPage(),
                'per_page'  => $queryCustomers->perPage(),
                'total' => $queryCustomers->total()
            ]
        ]);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('transaksi/pelanggan/Create', [
            'title' => 'Pelanggan',
            'desc'  => 'Tambah Pelanggan'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        $dataCustomer = Customer::create($request->validated());
        return redirect()->route('admin.transaksi.customer.index')->with('success', 'Data Pelanggan Berhasil DiSimpan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        return Inertia::render('transaksi/pelanggan/Edit', [
            'title' => 'Pelanggan',
            'desc'  => 'Tambah Pelanggan',
            'customer'  => new CustomerResource($customer)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());
        return redirect()->route('admin.transaksi.customer.index')->with('success', 'Data Pelanggan Berhasil Diubah!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('admin.transaksi.customer.index')->with('success', 'Data Pelanggan Berhasil Dihapus!');
    }
}
