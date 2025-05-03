<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PaginationData;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\StockCardResource;
use App\Models\StockCard;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StockCardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $queryStockCards = StockCard::query()
            ->with(['product', 'stockCardDetails', 'product.defaultUnit'])
            ->when($request->filled('search') || $request->filled('month') || $request->filled('year'), function (Builder $query) use ($request) {
                $searchTerm = trim($request->input('search', ''));
                $searchMonthTerm = trim($request->input('month', ''));
                $searchYearTerm = trim($request->input('year', ''));

                $query->where(function (Builder $query) use ($searchTerm, $searchMonthTerm, $searchYearTerm) {
                    // Search by code or product name
                    if ($searchTerm) {
                        $query->whereHas('product', function ($productQuery) use ($searchTerm) {
                            $productQuery->where('code', 'ilike', "%{$searchTerm}%")
                                ->orWhere('name', 'ilike', "%{$searchTerm}%");
                        });
                    }

                    // Filter by month
                    if ($searchMonthTerm) {
                        $query->where('month', $searchMonthTerm);
                    }

                    // Filter by year
                    if ($searchYearTerm) {
                        $query->where('year', $searchYearTerm);
                    }
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $dataStockCards = $queryStockCards->map(function ($queryStockCard) {
            return new StockCardResource($queryStockCard);
        });

        return Inertia::render('inventory/stock_card/Index', [
            'title' => 'Kartu Stock',
            'desc'  => 'Data Kartu Stock',
            'stock_cards'   => [
                'data'  => $dataStockCards,
                'links'  => PaginationData::formatPaginationLinks($queryStockCards),
                'current_page'  => $queryStockCards->currentPage(),
                'per_page'      => $queryStockCards->perPage(),
                'total'         => $queryStockCards->total()
            ]
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(StockCard $stockCard)
    {
        $dataStockCard = $stockCard->load([
            'product',
            'stockCardDetails.unit',
            'product.defaultUnit',
            'product.currentStocks',
            'product.unitConversions'
        ]);

        return Inertia::render('inventory/stock_card/Show', [
            'title' => 'Kartu Stock',
            'desc'  => 'Detail Kartu Stock',
            'stock_card'  => new StockCardResource($dataStockCard)
        ]);
    }
}
