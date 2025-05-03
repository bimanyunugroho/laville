import { Product } from "./produk";
import { Unit } from "./unit";

export interface CurrentStock {
    id: number;
    product_id: number;
    unit_id: number;
    product: Product | null;
    unit: Unit | null;
    quantity: number;
    base_quantity: number;
    slug: string;
    month: number;
    year: number;
    status_running: string;
}

export interface PaginationLink {
    label: string | number;
    url: string | null;
    active: boolean;
}

export interface CurrentStockPagination {
    data: CurrentStock[];
    links: PaginationLink[];
    current_page: number;
    per_page: number;
    total: number;
}

export interface CurrentStockPageProps {
    title: string;
    desc: string;
    current_stocks: CurrentStockPagination;
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
