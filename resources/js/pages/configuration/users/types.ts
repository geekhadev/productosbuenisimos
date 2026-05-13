import type { TabledataListStandardDraft } from '@/components/custom/tabledata';
import type { PaginatedListFilters } from '@/types/list-filters';

export type UserListMode = 'root' | 'owner';

export type UserListRowRoot = {
    id: string;
    name: string;
    email: string;
    type: string;
    created_at: string;
};

export type UserListRowOwner = {
    id: string;
    name: string;
    email: string;
    roles: string[];
};

export type UserListRow = UserListRowRoot | UserListRowOwner;

export const USERS_INDEX_MODULE_FILTER_KEYS = [
    'type',
    'company_id',
] as const;

export type UsersIndexModuleFilterKey =
    (typeof USERS_INDEX_MODULE_FILTER_KEYS)[number];

export type UsersIndexModuleFilters = {
    [K in UsersIndexModuleFilterKey]: string;
};

export type UserListFilters = PaginatedListFilters & {
    type?: string | null;
    company_id?: string | null;
};

export type UsersIndexFiltersDraftFull = UsersIndexModuleFilters &
    TabledataListStandardDraft;

export type CompanyOption = {
    id: string;
    name: string;
};

/** Campos del POST de creación (solo cuentas tipo usuario operativo). */
export type UserCreateFormFields = {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
};

/** Campos del PATCH de edición (nombre y correo únicamente). */
export type UserUpdateFormFields = {
    name: string;
    email: string;
};
