import { Product } from './produk';
import { Supplier } from './supplier';
import { Unit } from './unit';
import { User } from './user';

export interface PurchaseOrderDetail {
    id?: number;
    product_id: number;
    unit_id: number;
    quantity: number;
    base_quantity: number;
    price: number;
    subtotal: number;
    received_quantity: number;
    received_base_quantity: number;
    product?: Product | undefined;
    unit?: Unit | undefined;
}

export interface PurchaseOrder {
    id?: number;
    po_number: string;
    slug: string;
    supplier_id: number | null;
    user_id: number | null;
    user_ack_id: number | null;
    po_date: string;
    user_reject_id: number | null;
    expected_date: string;
    ack_date: string | null;
    reject_date: string | null;
    subtotal: number;
    tax: number;
    discount: number;
    total_net: number;
    status: string;
    notes: string | null;
    is_active: boolean;
    created_at: string;
    details: PurchaseOrderDetail[] | undefined;
    supplier?: Supplier | undefined;
    user?: User | undefined;
    userAck?: User | undefined;
    userReject?: User | undefined;
}

export interface PurchaseOrderApproval {
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

export interface PurchaseOrderPagination {
    data: PurchaseOrder[];
    links: PaginationLink[];
    current_page: number;
    per_page: number;
    total: number;
}

export interface PurchaseOrderPageProps {
    title: string;
    desc: string;
    purchase_orders: PurchaseOrderPagination;
    flash?: {
        success?: string;
        error?: string;
        warning?: string;
    };
}
