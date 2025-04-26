export interface Supplier {
    id: number;
    code: string;
    slug: string;
    name: string;
    phone: string;
    email: string;
    address: string;
    is_active: boolean;
    created_at: string;
}

export interface PaginationLink {
    label: string | number;
    url: string | null;
    active: boolean;
}

export interface SupplierPagination {
    data: Supplier[];
    links: PaginationLink[];
    current_page: number;
    per_page: number;
    total: number;
}

export interface SupplierPageProps {
    title: string;
    desc: string;
    suppliers: SupplierPagination;
    flash?: {
        success?: string;
        error?: string;
        warning?: string;
    }
}
