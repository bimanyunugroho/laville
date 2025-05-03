import { User } from ".";
import { Product } from "./produk";
import { PurchaseOrder, PurchaseOrderDetail } from "./purchase_order";
import { Supplier } from "./supplier";
import { Unit } from "./unit";

export interface GoodReceiptDetail {
    id?: number;
    good_receipt_id?: number | null;
    purchase_order_detail_id: number | null;
    product_id: number;
    unit_id: number;
    quantity: number;
    base_quantity: number;
    price: number;
    subtotal: number;
    received_quantity: number;
    received_base_quantity: number;
    created_at?: string | null;
    purchaseOrderDetail?: PurchaseOrderDetail | undefined;
    product?: Product | undefined;
    unit?: Unit | undefined;
}

export interface GoodReceipt {
    id?: number;
    receipt_number: string;
    slug: string;
    purchase_order_id: number | null;
    supplier_id: number | null;
    user_id: number | null;
    receipt_date: string;
    user_ack_id: number | null;
    ack_date: string | null;
    user_reject_id: number | null;
    reject_date: string | null;
    subtotal: number;
    tax: number;
    discount: number;
    total_net: number;
    status_receipt: string;
    notes: string | null;
    is_active: boolean;
    created_at: string;
    purchaseOrder?: PurchaseOrder | undefined;
    supplier?: Supplier | undefined;
    user?: User | undefined;
    userAck?: User | undefined;
    userReject?: User | undefined;
    details: GoodReceiptDetail[] | undefined;
}

export interface GoodReceiptApproval {
    id?: number;
    slug: string;
    user_ack_id?: number | null;
    user_reject_id?: number | null;
    ack_date?: string | null;
    reject_date?: string | null;
    status_receipt: string | null;
}

export interface PaginationLink {
    label: string | number;
    url: string | null;
    active: boolean;
}

export interface GoodReceiptPagination {
    data: GoodReceipt[];
    links: PaginationLink[];
    current_page: number;
    per_page: number;
    total: number;
}

export interface GoodReceiptPageProps {
    title: string;
    desc: string;
    good_receipts: GoodReceiptPagination;
    flash?: {
        success?: string;
        error?: string;
        warning?: string;
    }
}
