import { User } from './user';

export interface ClosedPeriod {
    id?: number;
    no_closed: string;
    slug?: string;
    month: number;
    year: number;
    user_id: number;
    closed_date: string;
    user_ack_id: number | null;
    ack_date: string | null;
    user_reject_id: number | null;
    reject_date: string | null;
    status_period: string;
    status_confirm: string;
    status: boolean;
    created_at: string;
    user?: User | undefined;
    userAck?: User | undefined;
    userReject?: User | undefined;
}

export interface ClosedPeriodApproval {
    id?: number;
    slug: string;
    user_ack_id?: number | null;
    user_reject_id?: number | null;
    ack_date?: string | null;
    reject_date?: string | null;
    status_period: string | null;
    status_confirm: string | null;
}

export interface ClosedPeriodClose {
    id?: number;
    slug: string;
    status_period: string | null;
}

export interface PaginationLink {
    label: string | number;
    url: string | null;
    active: boolean;
}

export interface ClosedPeriodPagination {
    data: ClosedPeriod[];
    links: PaginationLink[];
    current_page: number;
    per_page: number;
    total: number;
}

export interface ClosedPeriodPageProps {
    title: string;
    desc: string;
    closed_periods: ClosedPeriodPagination;
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
