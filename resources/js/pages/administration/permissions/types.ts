import type { TabledataListStandardDraft } from '@/components/custom/tabledata';
import type { ModuleSystemRef } from '@/pages/administration/modules/types';
import type { PaginatedListFilters } from '@/types/list-filters';

export type ModuleOption = {
    id: string;
    name: string;
    slug: string;
    system_id: string;
    system?: ModuleSystemRef;
};

export type Permission = {
    id: string;
    name: string;
    slug: string;
    module_id: string;
    module?: {
        id: string;
        name: string;
        slug: string;
        system_id: string;
        system?: ModuleSystemRef;
    };
    created_at: string;
    updated_at: string;
};

/**
 * Claves de filtros propios del índice (serialización a query y merges de tabledata).
 */
export const PERMISSIONS_INDEX_MODULE_FILTER_KEYS = [
    'system_id',
    'module_id',
] as const;

export type PermissionsIndexModuleFilterKey =
    (typeof PERMISSIONS_INDEX_MODULE_FILTER_KEYS)[number];

export type PermissionsIndexModuleFilters = {
    [K in PermissionsIndexModuleFilterKey]: string;
};

export type PermissionListFilters = PaginatedListFilters & {
    system_id?: string | null;
    module_id?: string | null;
};

export type PermissionsIndexFiltersDraftFull = PermissionsIndexModuleFilters &
    TabledataListStandardDraft;

export type { SystemOption } from '@/pages/administration/modules/types';
