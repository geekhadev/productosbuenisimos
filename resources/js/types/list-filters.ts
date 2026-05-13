/**
 * Campos de query comunes en listados paginados (extender por módulo con filtros propios).
 * Serialización estándar a query: `tabledataListFiltersToQuery` en `@/components/custom/tabledata/tabledata-list-query`.
 */
export type PaginatedListFilters = {
    search?: string | null;
    sort: string;
    direction: string;
    per_page: number;
};
