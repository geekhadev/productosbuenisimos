import { buildTabledataListInertiaForModuleStringKeys } from '@/components/custom/tabledata';
import type { TabledataListQueryValues } from '@/components/custom/tabledata';
import type {
    CompanyOption,
    UserListFilters,
    UserListRow,
} from '@/pages/configuration/users/types';
import { dashboard } from '@/routes';
import { index as usersIndex } from '@/routes/configuration/users';
import type { BreadcrumbItem } from '@/types/navigation';
import type { Paginated } from '@/types/pagination';
import { USERS_INDEX_MODULE_FILTER_KEYS } from './types';

export type UsersIndexPageProps = {
    data: Paginated<UserListRow>;
    filters: UserListFilters;
    listMode: 'root' | 'owner';
    companies: CompanyOption[];
};

const PAGE = {
    storageKey: 'users-index',
    title: 'Usuarios',
    description:
        'Usuarios del sistema o de la empresa activa, según tu perfil.',
    searchPlaceholder: 'Nombre o correo…',
} as const;

const ORDER = { sort: 'created_at', direction: 'desc' } as const;

export const CONFIG_TABLEDATA = {
    storageKey: PAGE.storageKey,
    pageTitle: PAGE.title,
    pageDescription: PAGE.description,
    searchPlaceholder: PAGE.searchPlaceholder,
    order: ORDER,
    breadcrumbs: {
        index: (): BreadcrumbItem[] => [
            { title: 'Panel', href: dashboard() },
            { title: PAGE.title, href: usersIndex() },
        ],
    },
    listInertia: buildTabledataListInertiaForModuleStringKeys<
        UserListRow,
        UserListFilters,
        typeof USERS_INDEX_MODULE_FILTER_KEYS
    >({
        moduleKeys: USERS_INDEX_MODULE_FILTER_KEYS,
        indexUrl: (query: TabledataListQueryValues) =>
            usersIndex.url({ query }),
        moduleResetQuery: { ...ORDER, type: '', company_id: '' },
    }),
};
