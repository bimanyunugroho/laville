import { Product } from "./produk";
import { Unit } from "./unit";
import { User } from "./user";

export interface StockOpnameDetail {
    id?: number;
    product_id: number;
    unit_id: number;
    system_stock: number;
    system_stock_base: number;
    physical_stock: number;
    physical_stock_base: number;
    difference_stock: number;
    difference_stock_base: number;
    price: number;
    subtotal: number;
    status: string;
    notes: string;
    created_at: string;
    product?: Product | undefined;
    unit?: Unit | undefined;
}

export interface StockOpname {
    id?: number;
    slug?: string;
    so_number: string;
    month: number;
    year: number;
    user_id: number;
    so_date: string;
    user_validator_id: number | null;
    validator_date: string | null;
    user_ack_id: number | null;
    ack_date: string | null;
    user_reject_id: number | null;
    reject_date: string | null;
    subtotal: number;
    tax: number;
    discount: number;
    total_net: number;
    status: string;
    notes: string;
    is_locked: boolean;
    is_active: boolean;
    user: User | undefined;
    userValidator?: User | undefined;
    userAck?: User | undefined;
    userReject?: User | undefined;
    details?: StockOpnameDetail[] | undefined;
}

export interface StockOpnameValidate {
    id?: number;
    slug: string;
    user_ack_id?: number | null;
    user_validator_id?: number | null;
    user_reject_id?: number | null;
    ack_date?: string | null;
    validator_date?: string | null;
    reject_date?: string | null;
    status: string | null;
    is_locked: boolean;
}

export interface StockOpnameApproval {
    id?: number;
    slug: string;
    user_ack_id?: number | null;
    user_validator_id?: number | null;
    user_reject_id?: number | null;
    ack_date?: string | null;
    validator_date?: string | null;
    reject_date?: string | null;
    status: string | null;
}

export interface PaginationLink {
    label: string | number;
    url: string | null;
    active: boolean;
}

export interface StockOpnamePagination {
    data: StockOpname[];
    links: PaginationLink[];
    current_page: number;
    per_page: number;
    total: number;
}

export interface StockOpnamePageProps {
    title: string;
    desc: string;
    stock_opnames: StockOpnamePagination;
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
