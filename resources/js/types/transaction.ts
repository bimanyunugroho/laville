import { User } from ".";
import { Customer } from "./customer";
import { Product } from "./produk";
import { Unit } from "./unit";

export interface TransactionDetail {
    id?: number;
    product_id: number;
    unit_id: number;
    quantity: number;
    base_quantity: number;
    conversion_factor?: number;
    price: number;
    discount: number;
    subtotal: number;
    created_at?: string;
    product?: Product | undefined;
    unit?: Unit | undefined;
}

export interface TransactionPayment {
    id?: number;
    payment_date: string;
    payment_method: string;
    payment_reference: string;
    amount: number;
    status: string;
    created_at?: string;
}

export interface Transaction {
    id?: number;
    invoice_number: string;
    slug: string;
    customer_id: number | null;
    transaction_date: string;
    user_id: number | null;
    total: number;
    discount: number;
    subtotal: number;
    tax: number;
    tax_amount: number;
    total_amount: number;
    paid_amount: number;
    change_amount: number;
    status: string;
    source_transaction: string;
    notes: string;
    is_active: boolean;
    created_at: string;
    customer?: Customer | undefined;
    user?: User | undefined;
    details?: TransactionDetail[] | undefined;
    payments?: TransactionPayment[] | undefined;
}

export interface PaginationLink {
    label: string | number;
    url: string | null;
    active: boolean;
}

export interface TransactionPagination {
    data: Transaction[];
    links: PaginationLink[];
    current_page: number;
    per_page: number;
    total: number;
}

export interface TransactionPageProps {
    title: string;
    desc: string;
    transactions: TransactionPagination;
    flash?: {
        success?: string;
        error?: string;
        warning?: string;
    }
    filter?: {
        search?: string;
        date_from?: string;
        date_to?: string;
        status_transaction?: string;
    }
}
