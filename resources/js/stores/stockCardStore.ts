import { PaginationLink, StockCard, StockCardPageProps, StockCardPagination } from '@/types/stock_card';
import { router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { defineStore } from 'pinia';

interface StockCardState {
    stock_cards: StockCardPagination;
    searchQuery: string;
    monthFilter: string;
    yearFilter: string;
    isLoading: boolean;
}

export const useStockCardStore = defineStore('stock_cards', {
    state: (): StockCardState => ({
        stock_cards: {
            data: [] as StockCard[],
            links: [] as PaginationLink[],
            current_page: 1,
            per_page: 10,
            total: 0,
        },
        searchQuery: '',
        monthFilter: '',
        yearFilter: '',
        isLoading: false,
    }),

    actions: {
        initializeFromProps(props: StockCardPageProps) {
            this.stock_cards = props.stock_cards;

            // Initialize filters from URL params if they exist
            const urlParams = new URL(window.location.href).searchParams;
            this.searchQuery = urlParams.get('search') || '';
            this.monthFilter = urlParams.get('month') || '';
            this.yearFilter = urlParams.get('year') || '';
        },

        setSearchQuery(query: string) {
            this.searchQuery = query;
            this.debouncedSearch();
        },

        setMonthFilter(month: string) {
            this.monthFilter = month;
            this.debouncedSearch();
        },

        setYearFilter(year: string) {
            this.yearFilter = year;
            this.debouncedSearch();
        },

        debouncedSearch: debounce(function(this: any) {
            this.isLoading = true;
            router.get(
                route('admin.inventory.stock_card.index'),
                {
                    search: this.searchQuery,
                    month: this.monthFilter,
                    year: this.yearFilter
                },
                {
                    preserveState: true,
                    preserveScroll: true,
                    onSuccess: () => {
                        this.isLoading = false;
                    },
                    onError: () => {
                        this.isLoading = false;
                    },
                },
            );
        }, 300),

        resetFilters() {
            this.searchQuery = '';
            this.monthFilter = '';
            this.yearFilter = '';
            this.debouncedSearch();
        },

        goToPage(url: string) {
            if (!url) return;

            this.isLoading = true;

            // Extract existing URL parameters and add our filters
            const urlObj = new URL(url);
            if (this.searchQuery) urlObj.searchParams.set('search', this.searchQuery);
            if (this.monthFilter) urlObj.searchParams.set('month', this.monthFilter);
            if (this.yearFilter) urlObj.searchParams.set('year', this.yearFilter);

            router.visit(urlObj.toString(), {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    this.isLoading = false;
                },
                onError: () => {
                    this.isLoading = false;
                },
            });
        },
    },
});
