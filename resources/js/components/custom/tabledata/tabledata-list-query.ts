import { TABLEDATA_DEFAULT_PER_PAGE } from '@/components/custom/tabledata/config';
import type { PaginatedListFilters } from '@/types/list-filters';

export type TabledataListQueryValues = Record<string, string | number>;

/**
 * `page` y `per_page` al resetear un listado (común a todos los módulos).
 * Alinear con `InteractsWithPaginatedListQuery::prepareStandardListQuery` en el backend.
 */
export const TABLEDATA_LIST_RESET_QUERY = {
    page: 1,
    per_page: TABLEDATA_DEFAULT_PER_PAGE,
} as const;

/**
 * Serializa campos estándar de listados (search, sort, direction, per_page)
 * y campos string adicionales del módulo (fechas, facetas, etc.).
 */
export function tabledataListFiltersToQuery(
    filters: PaginatedListFilters,
    customStringFields: Record<string, string | null | undefined> = {},
    overrides: Record<string, unknown> = {},
): TabledataListQueryValues {
    const q: TabledataListQueryValues = {
        sort: filters.sort,
        direction: filters.direction,
        per_page: filters.per_page,
    };

    const search = filters.search?.trim();

    if (search) {
        q.search = search;
    }

    for (const [key, raw] of Object.entries(customStringFields)) {
        if (typeof raw === 'string' && raw.trim() !== '') {
            q[key] = raw.trim();
        }
    }

    for (const [k, v] of Object.entries(overrides)) {
        if (v === undefined || v === null || v === '') {
            delete q[k];
        } else {
            q[k] = v as string | number;
        }
    }

    return q;
}
