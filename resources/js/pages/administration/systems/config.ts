import { buildTabledataListInertiaForModuleStringKeys } from '@/components/custom/tabledata';
import type { TabledataListQueryValues } from '@/components/custom/tabledata';
import type { System, SystemListFilters } from '@/pages/administration/systems/types';
import { dashboard } from '@/routes';
import { index as systemsIndex } from '@/routes/administration/systems';
import type { BreadcrumbItem } from '@/types/navigation';
import type { Paginated } from '@/types/pagination';
import { SYSTEMS_INDEX_MODULE_FILTER_KEYS } from './types';

export type SystemsIndexPageProps = {
    data: Paginated<System>;
    filters: SystemListFilters;
};

const PAGE = {
    storageKey: 'systems-index',
    title: 'Sistemas',
    description: 'Macro categorías que agrupan módulos del proyecto.',
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
            { title: PAGE.title, href: systemsIndex() },
        ],
    },
    listInertia: buildTabledataListInertiaForModuleStringKeys<
        System,
        SystemListFilters,
        typeof SYSTEMS_INDEX_MODULE_FILTER_KEYS
    >({
        moduleKeys: SYSTEMS_INDEX_MODULE_FILTER_KEYS,
        indexUrl: (query: TabledataListQueryValues) =>
            systemsIndex.url({ query }),
        moduleResetQuery: { ...ORDER },
    }),
};
