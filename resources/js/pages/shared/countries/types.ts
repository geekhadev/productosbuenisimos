import type { TabledataListStandardDraft } from '@/components/custom/tabledata';
import type { PaginatedListFilters } from '@/types/list-filters';

export type Country = {
    id: number;
    name: string;
    name_code: string;
    phone_code: string;
    currency_name: string;
    currency_symbol: string;
    created_at: string;
    updated_at: string;
};

/** Sin filtros de dominio en el índice; solo serialización / merge estándar de tabledata. */
export const COUNTRIES_INDEX_MODULE_FILTER_KEYS = [] as const;

export type CountryListFilters = PaginatedListFilters;

export type CountriesIndexFiltersDraftFull = TabledataListStandardDraft;
