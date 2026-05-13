import { buildTabledataListInertiaForModuleStringKeys } from '@/components/custom/tabledata';
import type { TabledataListQueryValues } from '@/components/custom/tabledata';
import type {
    Module,
    ModuleListFilters,
    SystemOption,
} from '@/pages/administration/modules/types';
import { dashboard } from '@/routes';
import { index as modulesIndex } from '@/routes/administration/modules';
import type { BreadcrumbItem } from '@/types/navigation';
import type { Paginated } from '@/types/pagination';
import { MODULES_INDEX_MODULE_FILTER_KEYS } from './types';

export type ModulesIndexPageProps = {
    data: Paginated<Module>;
    filters: ModuleListFilters;
    systems: SystemOption[];
};

const PAGE = {
    storageKey: 'modules-index',
    title: 'Módulos',
    description: 'Unidades operativas agrupadas bajo un sistema.',
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
            { title: PAGE.title, href: modulesIndex() },
        ],
    },
    listInertia: buildTabledataListInertiaForModuleStringKeys<
        Module,
        ModuleListFilters,
        typeof MODULES_INDEX_MODULE_FILTER_KEYS
    >({
        moduleKeys: MODULES_INDEX_MODULE_FILTER_KEYS,
        indexUrl: (query: TabledataListQueryValues) =>
            modulesIndex.url({ query }),
        moduleResetQuery: { ...ORDER, system_id: '' },
    }),
};
