import { buildTabledataListInertiaForModuleStringKeys } from '@/components/custom/tabledata';
import type { TabledataListQueryValues } from '@/components/custom/tabledata';
import type {
    CustomerListFilters,
    CustomerRow,
    CustomersIndexFiltersDraftFull,
} from '@/pages/sales/customers/types';
import { dashboard } from '@/routes';
import { index as customersIndex } from '@/routes/sales/customers';
import type { BreadcrumbItem } from '@/types/navigation';
import type { Paginated } from '@/types/pagination';
import { CUSTOMERS_INDEX_MODULE_FILTER_KEYS } from './types';

export type CustomersIndexPageProps = {
    data: Paginated<CustomerRow>;
    filters: CustomersIndexFiltersDraftFull;
    can: {
        create: boolean;
    };
};

const PAGE = {
    title: 'Clientes',
    searchPlaceholder: 'Nombre o teléfono…',
} as const;

const ORDER = { sort: 'full_name', direction: 'asc' } as const;

export const CONFIG_TABLEDATA = {
    pageTitle: PAGE.title,
    searchPlaceholder: PAGE.searchPlaceholder,
    order: ORDER,
    breadcrumbs: {
        index: (): BreadcrumbItem[] => [
            { title: 'Panel', href: dashboard() },
            { title: PAGE.title, href: customersIndex() },
        ],
    },
    listInertia: buildTabledataListInertiaForModuleStringKeys<
        CustomerRow,
        CustomerListFilters,
        typeof CUSTOMERS_INDEX_MODULE_FILTER_KEYS
    >({
        moduleKeys: CUSTOMERS_INDEX_MODULE_FILTER_KEYS,
        indexUrl: (query: TabledataListQueryValues) =>
            customersIndex.url({ query }),
        moduleResetQuery: { ...ORDER },
        inertiaOnly: ['data', 'filters', 'can'],
    }),
};
