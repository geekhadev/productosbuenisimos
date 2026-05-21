import { buildTabledataListInertiaForModuleStringKeys } from '@/components/custom/tabledata';
import type { TabledataListQueryValues } from '@/components/custom/tabledata';
import type {
    LeadListFilters,
    LeadRow,
    LeadsIndexFiltersDraftFull,
} from '@/pages/sales/leads/types';
import { dashboard } from '@/routes';
import { index as leadsIndex } from '@/routes/sales/leads';
import type { BreadcrumbItem } from '@/types/navigation';
import type { Paginated } from '@/types/pagination';
import { LEADS_INDEX_MODULE_FILTER_KEYS } from './types';

export type LeadsIndexPageProps = {
    data: Paginated<LeadRow>;
    filters: LeadsIndexFiltersDraftFull;
    can: {
        create: boolean;
    };
};

const PAGE = {
    title: 'Leads',
    searchPlaceholder: 'Teléfono…',
} as const;

const ORDER = { sort: 'created_at', direction: 'desc' } as const;

export const CONFIG_TABLEDATA = {
    pageTitle: PAGE.title,
    searchPlaceholder: PAGE.searchPlaceholder,
    order: ORDER,
    breadcrumbs: {
        index: (): BreadcrumbItem[] => [
            { title: 'Panel', href: dashboard() },
            { title: PAGE.title, href: leadsIndex() },
        ],
    },
    listInertia: buildTabledataListInertiaForModuleStringKeys<
        LeadRow,
        LeadListFilters,
        typeof LEADS_INDEX_MODULE_FILTER_KEYS
    >({
        moduleKeys: LEADS_INDEX_MODULE_FILTER_KEYS,
        indexUrl: (query: TabledataListQueryValues) => leadsIndex.url({ query }),
        moduleResetQuery: { ...ORDER },
        inertiaOnly: ['data', 'filters', 'can'],
    }),
};
