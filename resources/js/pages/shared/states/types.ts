import type { TabledataListStandardDraft } from '@/components/custom/tabledata';
import type { PaginatedListFilters } from '@/types/list-filters';

export type CountryOption = {
    id: number;
    name: string;
};

export type State = {
    id: string;
    country_id: number;
    country: CountryOption | null;
    name: string;
    created_at: string;
    updated_at: string;
};

export const STATES_INDEX_MODULE_FILTER_KEYS = ['country_id'] as const;

export type StatesIndexModuleFilterKey =
    (typeof STATES_INDEX_MODULE_FILTER_KEYS)[number];

export type StatesIndexModuleFilters = {
    [K in StatesIndexModuleFilterKey]: string;
};

export type StateListFilters = PaginatedListFilters & {
    [K in StatesIndexModuleFilterKey]?: string | null;
};

export type StatesIndexFiltersDraftFull =
    StatesIndexModuleFilters & TabledataListStandardDraft;
