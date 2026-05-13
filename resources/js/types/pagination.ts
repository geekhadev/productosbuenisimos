export type PaginatedLinkItem = {
    url: string | null;
    label: string;
    active: boolean;
};

/**
 * Forma típica del paginador de Laravel (`LengthAwarePaginator`) serializado para Inertia.
 */
export type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: PaginatedLinkItem[];
};
