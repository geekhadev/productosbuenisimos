import { buildTabledataListInertiaForModuleStringKeys } from '@/components/custom/tabledata';
import type { TabledataListQueryValues } from '@/components/custom/tabledata';
import type {
    CountriesIndexFiltersDraftFull,
    Country,
    CountryListFilters,
} from '@/pages/shared/countries/types';
import { dashboard } from '@/routes';
import { index as countriesIndex } from '@/routes/shared/countries';
import type { BreadcrumbItem } from '@/types/navigation';
import type { Paginated } from '@/types/pagination';
import { COUNTRIES_INDEX_MODULE_FILTER_KEYS } from './types';

export type CountriesIndexPageProps = {
    data: Paginated<Country>;
    filters: CountriesIndexFiltersDraftFull;
};

const PAGE = {
    storageKey: 'countries-index',
    title: 'Países',
    description: 'Catálogo compartido de países, códigos y monedas.',
    searchPlaceholder: 'Nombre, código, teléfono o moneda…',
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
            { title: PAGE.title, href: countriesIndex() },
        ],
    },
    listInertia: buildTabledataListInertiaForModuleStringKeys<
        Country,
        CountryListFilters,
        typeof COUNTRIES_INDEX_MODULE_FILTER_KEYS
    >({
        moduleKeys: COUNTRIES_INDEX_MODULE_FILTER_KEYS,
        indexUrl: (query: TabledataListQueryValues) =>
            countriesIndex.url({ query }),
        moduleResetQuery: { ...ORDER },
    }),
};
