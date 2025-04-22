export interface Unit {
    code: string;
    slug: string;
    name: string;
    is_active: boolean;
    created_at: string;
}

export interface PaginationLink {
    label: string | number;
    url: string | null;
    active: boolean;
}

export interface UnitPagination {
    data: Unit[];
    links: PaginationLink[];
    current_page: number;
    per_page: number;
    total: number;
}

export interface UnitPageProps {
    title: string;
    desc: string;
    units: UnitPagination;
    flash?: {
        success?: string;
        error?: string;
        warning?: string;
    };
}
