import { buildTabledataListInertiaForModuleStringKeys } from '@/components/custom/tabledata';
import type { TabledataListQueryValues } from '@/components/custom/tabledata';
import type {
    ProductListFilters,
    ProductRow,
    ProductsIndexFiltersDraftFull,
} from '@/pages/stock/products/types';
import { dashboard } from '@/routes';
import { index as productsIndex } from '@/routes/stock/products';
import type { BreadcrumbItem } from '@/types/navigation';
import type { Paginated } from '@/types/pagination';
import { PRODUCTS_INDEX_MODULE_FILTER_KEYS } from './types';

export type ProductsIndexPageProps = {
    data: Paginated<ProductRow>;
    filters: ProductsIndexFiltersDraftFull;
    can: {
        create: boolean;
    };
};

const PAGE = {
    title: 'Productos',
    searchPlaceholder: 'Nombre, código o SKU…',
} as const;

const ORDER = { sort: 'name', direction: 'asc' } as const;

export const CONFIG_TABLEDATA = {
    pageTitle: PAGE.title,
    searchPlaceholder: PAGE.searchPlaceholder,
    order: ORDER,
    breadcrumbs: {
        index: (): BreadcrumbItem[] => [
            { title: 'Panel', href: dashboard() },
            { title: PAGE.title, href: productsIndex() },
        ],
    },
    listInertia: buildTabledataListInertiaForModuleStringKeys<
        ProductRow,
        ProductListFilters,
        typeof PRODUCTS_INDEX_MODULE_FILTER_KEYS
    >({
        moduleKeys: PRODUCTS_INDEX_MODULE_FILTER_KEYS,
        indexUrl: (query: TabledataListQueryValues) =>
            productsIndex.url({ query }),
        moduleResetQuery: { ...ORDER },
        inertiaOnly: ['data', 'filters', 'can'],
    }),
};
