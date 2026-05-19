import { buildTabledataListInertiaForModuleStringKeys } from '@/components/custom/tabledata';
import type { TabledataListQueryValues } from '@/components/custom/tabledata';
import type {
    OrderListFilters,
    OrderRow,
    OrdersIndexFiltersDraftFull,
} from '@/pages/sales/orders/types';
import { dashboard } from '@/routes';
import { index as ordersIndex } from '@/routes/sales/orders';
import type { BreadcrumbItem } from '@/types/navigation';
import type { Paginated } from '@/types/pagination';
import { ORDERS_INDEX_MODULE_FILTER_KEYS } from './types';

export type OrdersIndexPageProps = {
    data: Paginated<OrderRow>;
    filters: OrdersIndexFiltersDraftFull;
};

const PAGE = {
    title: 'Pedidos',
    searchPlaceholder: 'Nombre o referencia del pedido…',
} as const;

const ORDER = { sort: 'created_at', direction: 'desc' } as const;

export const CONFIG_TABLEDATA = {
    pageTitle: PAGE.title,
    searchPlaceholder: PAGE.searchPlaceholder,
    order: ORDER,
    breadcrumbs: {
        index: (): BreadcrumbItem[] => [
            { title: 'Panel', href: dashboard() },
            { title: PAGE.title, href: ordersIndex() },
        ],
    },
    listInertia: buildTabledataListInertiaForModuleStringKeys<
        OrderRow,
        OrderListFilters,
        typeof ORDERS_INDEX_MODULE_FILTER_KEYS
    >({
        moduleKeys: ORDERS_INDEX_MODULE_FILTER_KEYS,
        indexUrl: (query: TabledataListQueryValues) => ordersIndex.url({ query }),
        moduleResetQuery: { ...ORDER },
        inertiaOnly: ['data', 'filters'],
    }),
};
