import { Product } from "./produk";
import { Unit } from "./unit";
import { UnitConversion } from "./unit_conversion";

export interface StockCardDetail {
    id?: number;
    reference_id: number;
    reference_type: string;
    reference_status: string;
    unit_id: number;
    unit?: Unit | undefined;
    transaction_date: string;
    movement_type: string;
    quantity: number;
    base_quantity: number;
    balance_quantity: number;
    balance_base_quantity: number;
    notes: string | null;
    created_at: string;
}

export interface StockCard {
    id?: number;
    slug: string;
    product_id: number;
    beginning_balance: number;
    in_balance: number;
    out_balance: number;
    ending_balance: number;
    beginning_base_balance: number;
    in_base_balance: number;
    out_base_balance: number;
    ending_base_balance: number;
    month: number;
    year: number;
    status_running: string;
    stockCardDetails?: StockCardDetail[] | undefined;
    product?: Product | undefined;
    unitConversions?: UnitConversion[] | undefined;
}

export interface PaginationLink {
    label: string | number;
    url: string | null;
    active: boolean;
}

export interface StockCardPagination {
    data: StockCard[];
    links: PaginationLink[];
    current_page: number;
    per_page: number;
    total: number;
}

export interface StockCardPageProps {
    title: string;
    desc: string;
    stock_cards: StockCardPagination;
    flash?: {
        success?: string;
        error?: string;
        warning?: string;
    };
    filters?: {
        search?: string;
        month?: string;
        year?: string;
    };
}
