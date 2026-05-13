import type { TabledataListStandardDraft } from '@/components/custom/tabledata';
import type { PaginatedListFilters } from '@/types/list-filters';

export type ModuleSystemRef = {
    id: string;
    name: string;
    slug: string;
};

export type Module = {
    id: string;
    name: string;
    slug: string;
    system_id: string;
    system?: ModuleSystemRef;
    created_at: string;
    updated_at: string;
};

/**
 * Claves de filtros propios del índice (serialización a query y merges de tabledata).
 */
export const MODULES_INDEX_MODULE_FILTER_KEYS = ['system_id'] as const;

export type ModulesIndexModuleFilterKey =
    (typeof MODULES_INDEX_MODULE_FILTER_KEYS)[number];

export type ModulesIndexModuleFilters = {
    [K in ModulesIndexModuleFilterKey]: string;
};

export type ModuleListFilters = PaginatedListFilters & {
    system_id?: string | null;
};

export type ModulesIndexFiltersDraftFull = ModulesIndexModuleFilters &
    TabledataListStandardDraft;

export type SystemOption = {
    id: string;
    name: string;
    slug: string;
};
