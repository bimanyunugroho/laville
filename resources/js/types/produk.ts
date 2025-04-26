import { Unit } from "./unit";

export interface Product {
    id: number;
    code: string;
    name: string;
    slug: string;
    variant_name: string;
    defaultUnit: Unit | null;
    default_unit_id?: number | null;
    purchase_price: number;
    selling_price: number;
    description: string;
    is_active: boolean;
}

export interface PaginationLink {
    label: string | number;
    url: string | null;
    active: boolean;
}

export interface ProductPagination {
    data: Product[];
    links: PaginationLink[];
    current_page: number;
    per_page: number;
    total: number;
}

export interface ProductPageProps {
    title: string;
    desc: string;
    products: ProductPagination;
    flash?: {
        success?: string;
        error?: string;
        warning?: string;
    }
}
