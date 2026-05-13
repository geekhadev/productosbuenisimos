import type { TabledataListStandardDraft } from '@/components/custom/tabledata';
import type { PaginatedListFilters } from '@/types/list-filters';

export type System = {
    id: string;
    name: string;
    slug: string;
    created_at: string;
    updated_at: string;
};

/** Sin filtros de dominio en el índice; solo serialización / merge estándar de tabledata. */
export const SYSTEMS_INDEX_MODULE_FILTER_KEYS = [] as const;

export type SystemListFilters = PaginatedListFilters;

export type SystemsIndexFiltersDraftFull = TabledataListStandardDraft;
