import { Product } from "./produk";
import { Unit } from "./unit";

export interface UnitConversion {
    id: number;
    slug: string;
    product_id?: number | null;
    from_unit_id?: number | null;
    to_unit_id?: number | null;
    product: Product | null;
    fromUnit?: Unit | null;
    toUnit?: Unit | null;
    conversion_factor: number;
    created_at: string;
    updated_at: string;
}

export interface PaginationLink {
    label: string | number;
    url: string | null;
    active: boolean;
}

export interface UnitConversionPagination {
    data: UnitConversion[];
    links: PaginationLink[];
    current_page: number;
    per_page: number;
    total: number;
}

export interface UnitConversionPageProps {
    title: string;
    desc: string;
    unit_conversions: UnitConversionPagination;
    flash?: {
        success?: string;
        error?: string;
        warning?: string;
    }
}
