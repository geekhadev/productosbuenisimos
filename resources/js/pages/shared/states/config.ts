import { buildTabledataListInertiaForModuleStringKeys } from '@/components/custom/tabledata';
import type { TabledataListQueryValues } from '@/components/custom/tabledata';
import type {
    CountryOption,
    State,
    StateListFilters,
    StatesIndexFiltersDraftFull,
} from '@/pages/shared/states/types';
import { dashboard } from '@/routes';
import { index as statesIndex } from '@/routes/shared/states';
import type { BreadcrumbItem } from '@/types/navigation';
import type { Paginated } from '@/types/pagination';
import { STATES_INDEX_MODULE_FILTER_KEYS } from './types';

export type StatesIndexPageProps = {
    data: Paginated<State>;
    filters: StatesIndexFiltersDraftFull;
    countries: CountryOption[];
};

const PAGE = {
    storageKey: 'states-index',
    title: 'Estados',
    description: 'Catalogo compartido de estados, provincias y regiones.',
    searchPlaceholder: 'Nombre…',
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
            { title: PAGE.title, href: statesIndex() },
        ],
    },
    listInertia: buildTabledataListInertiaForModuleStringKeys<
        State,
        StateListFilters,
        typeof STATES_INDEX_MODULE_FILTER_KEYS
    >({
        moduleKeys: STATES_INDEX_MODULE_FILTER_KEYS,
        indexUrl: (query: TabledataListQueryValues) => statesIndex.url({ query }),
        moduleResetQuery: { ...ORDER },
    }),
};
