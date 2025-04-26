export interface Customer {
    id: number;
    name: string;
    slug: string;
    phone: string;
    is_active: boolean;
}

export interface PaginationLink {
    label: string | number;
    url: string | null;
    active: boolean;
}

export interface CustomerPagination {
    data: Customer[];
    links: PaginationLink[];
    current_page: number;
    per_page: number;
    total: number;
}

export interface CustomerPageProps {
    title: string;
    desc: string;
    customers: CustomerPagination;
    flash?: {
        success?: string;
        error?: string;
        warning?: string;
    }
}
