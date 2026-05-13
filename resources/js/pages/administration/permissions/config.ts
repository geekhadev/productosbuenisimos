import { buildTabledataListInertiaForModuleStringKeys } from '@/components/custom/tabledata';
import type { TabledataListQueryValues } from '@/components/custom/tabledata';
import type {
    ModuleOption,
    Permission,
    PermissionListFilters,
    SystemOption,
} from '@/pages/administration/permissions/types';
import { dashboard } from '@/routes';
import { index as permissionsIndex } from '@/routes/administration/permissions';
import type { BreadcrumbItem } from '@/types/navigation';
import type { Paginated } from '@/types/pagination';
import { PERMISSIONS_INDEX_MODULE_FILTER_KEYS } from './types';

export type PermissionsIndexPageProps = {
    data: Paginated<Permission>;
    filters: PermissionListFilters;
    systems: SystemOption[];
    modules: ModuleOption[];
};

const PAGE = {
    storageKey: 'permissions-index',
    title: 'Permisos',
    description: 'Permisos asociados a cada módulo del sistema.',
    searchPlaceholder: 'Nombre o slug…',
} as const;

const ORDER = { sort: 'name', direction: 'asc' } as const;

export const CONFIG_TABLEDATA = {
    storageKey: PAGE.storageKey,
    pageTitle: PAGE.title,
    pageDescription: PAGE.description,
    searchPlaceholder: PAGE.searchPlaceholder,
    order: ORDER,
    breadcrumbs: {
        index: (): BreadcrumbItem[] => [
            { title: 'Panel', href: dashboard() },
            { title: PAGE.title, href: permissionsIndex() },
        ],
    },
    listInertia: buildTabledataListInertiaForModuleStringKeys<
        Permission,
        PermissionListFilters,
        typeof PERMISSIONS_INDEX_MODULE_FILTER_KEYS
    >({
        moduleKeys: PERMISSIONS_INDEX_MODULE_FILTER_KEYS,
        indexUrl: (query: TabledataListQueryValues) =>
            permissionsIndex.url({ query }),
        moduleResetQuery: { ...ORDER, system_id: '', module_id: '' },
    }),
};
