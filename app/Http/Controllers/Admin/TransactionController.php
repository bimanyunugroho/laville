<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusRunningCurrentStockEnum;
use App\Enums\TypePayment;
use App\Enums\TypeSourceTransaction;
use App\Events\TransactionEvent;
use App\Helpers\PaginationData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\TransactionResource;
use App\Http\Resources\CustomerCollection;
use App\Http\Resources\ProductCollection;
use App\Models\Transaction;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Inertia\Inertia;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $queryTransactions = Transaction::query()
            ->with(['customer', 'user', 'details', 'payments'])
            ->when($request->filled('search') || $request->filled('date_from') || $request->filled('date_to') || $request->filled('status'), function (Builder $query) use ($request) {
                $searchTerm = trim($request->input('search', ''));
                $searchDateFromTerm = trim($request->input('date_from', ''));
                $searchDateToTerm = trim($request->input('date_to', ''));
                $searchStatusTerm = trim($request->input('status', ''));

                $query->where(function (Builder $query) use ($searchTerm, $searchDateFromTerm, $searchDateToTerm, $searchStatusTerm) {
                    if ($searchTerm) {
                        $query->where('invoice_number', 'ilike', "%{$searchTerm}%");
                    }

                    if ($searchDateFromTerm) {
                        $query->where('transaction_date', '>=', $searchDateFromTerm);
                    }

                    if ($searchDateToTerm) {
                        $query->where('transaction_date', '<=', $searchDateToTerm);
                    }

                    if ($searchStatusTerm) {
                        $query->where('status', $searchStatusTerm);
                    }
                });
            })

            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $dataTransaction = $queryTransactions->map(function ($queryTransaction) {
            return new TransactionResource($queryTransaction);
        });

        return Inertia::render('transaksi/transaksi/Index', [
            'title' => 'Transaksi',
            'desc'  => 'Data Transaksi',
            'transactions'  => [
                'data'  => $dataTransaction,
                'links' => PaginationData::formatPaginationLinks($queryTransactions),
                'current_page'  => $queryTransactions->currentPage(),
                'per_page'  => $queryTransactions->perPage(),
                'total' => $queryTransactions->total()
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
            ->with([
                'defaultUnit',
                'unitConversions',
                'currentStocks' => function ($query) {
                    $query->where('status_running', '!=', StatusRunningCurrentStockEnum::SUDAH_BERAKHIR->value);
                }
            ])
            ->get();

        $dataCustomer = Customer::withoutTrashed()->get();

        // Source Transaction
        $sources = collect(TypeSourceTransaction::cases())->map(function ($source) {
            return [
                'label' => $source->label(),
                'name'  => $source->value
            ];
        });

        // Type payment
        $typePayments = collect(TypePayment::cases())->map(function ($typePayment) {
            return [
                'label' => $typePayment->label(),
                'name'  => $typePayment->value
            ];
        });

        $currentUser = Auth::user();

        return Inertia::render('transaksi/transaksi/Create', [
            'title' => 'Transaksi',
            'desc'  => 'Tambah Transaksi',
            'products'  => new ProductCollection($dataProducts),
            'customers' => new CustomerCollection($dataCustomer),
            'sources'    => $sources,
            'type_payments'  => $typePayments,
            'currentUser'   => $currentUser,
            'invoice_number'    => Transaction::generateTransactionInvoice()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTransactionRequest $request)
    {
        DB::beginTransaction();

        try {
            $validatedData = $request->validated();
            $transaction = Transaction::create($validatedData);

            if ($transaction) {
                $transaction->details()->createMany($validatedData['details']);
                $transaction->payments()->createMany($validatedData['payments']);
            }

            Event::dispatch(new TransactionEvent($transaction));

            DB::commit();
            return redirect()->route('admin.transaksi.transaction.index')->with('success', 'Selamat Transaksi Anda Berhasil!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.transaksi.transaction.index')->with('error', 'Maaf transaksi anda mengalami kegagalan');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        $dataTransaction = $transaction->load([
            'customer',
            'user',
            'details',
            'details.product',
            'details.product.unitConversions',
            'details.product.unitConversions.toUnit',
            'details.product.unitConversions.fromUnit',
            'payments']);

        return Inertia::render('transaksi/transaksi/Show', [
            'title' => 'Transaksi',
            'desc'  => 'Detail Transaksi ' . $transaction->invoice_number,
            'transaction'   => new TransactionResource($dataTransaction)
        ]);
    }
}
