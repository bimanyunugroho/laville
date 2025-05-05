import { Product } from './produk';
import { Supplier } from './supplier';
import { Unit } from './unit';
import { User } from './user';

export interface StockOutDetail {
    id?: number;
    product_id: number;
    unit_id: number;
    quantity: number;
    base_quantity: number;
    price: number;
    subtotal: number;
    received_quantity: number;
    received_base_quantity: number;
    notes_detail: string;
    product?: Product | undefined;
    unit?: Unit | undefined;
}

export interface StockOut {
    id?: number;
    stock_out_number: string;
    slug?: string;
    supplier_id?: number | null;
    user_id: number | null;
    user_ack_id: number | null;
    out_date: string;
    user_reject_id: number | null;
    ack_date: string | null;
    reject_date: string | null;
    subtotal: number;
    tax: number;
    discount: number;
    total_net: number;
    status_notes: string;
    status: string;
    notes: string | null;
    is_active: boolean;
    created_at: string;
    details: StockOutDetail[] | undefined;
    supplier?: Supplier | undefined;
    user?: User | undefined;
    userAck?: User | undefined;
    userReject?: User | undefined;
}

export interface StockOutApproval {
    id?: number;
    slug: string;
    user_ack_id?: number | null;
    user_reject_id?: number | null;
    ack_date?: string | null;
    reject_date?: string | null;
    status: string | null;
}

export interface PaginationLink {
    label: string | number;
    url: string | null;
    active: boolean;
}

export interface StockOutPagination {
    data: StockOut[];
    links: PaginationLink[];
    current_page: number;
    per_page: number;
    total: number;
}

export interface StockOutPageProps {
    title: string;
    desc: string;
    stock_outs: StockOutPagination;
    flash?: {
        success?: string;
        error?: string;
        warning?: string;
    };
}
