import type { TabledataListStandardDraft } from '@/components/custom/tabledata';
import type { PaginatedListFilters } from '@/types/list-filters';

export type CustomerRow = {
    id: string;
    full_name: string;
    phone: string;
    created_at: string | null;
    updated_at: string | null;
    can: {
        update: boolean;
        delete: boolean;
    };
};

export const CUSTOMERS_INDEX_MODULE_FILTER_KEYS = [] as const;

export type CustomersIndexModuleFilterKey =
    (typeof CUSTOMERS_INDEX_MODULE_FILTER_KEYS)[number];

export type CustomersIndexModuleFilters = {
    [K in CustomersIndexModuleFilterKey]?: string | null;
};

export type CustomerListFilters = PaginatedListFilters & {
    [K in CustomersIndexModuleFilterKey]?: string | null;
};

export type CustomersIndexFiltersDraftFull = CustomersIndexModuleFilters &
    TabledataListStandardDraft;

/** Fila de dirección en formulario; `id` solo viene del servidor al editar. */
export type CustomerAddressLineFormData = {
    id: string | null;
    country_name: string;
    state_name: string;
    address: string;
};

export type CustomerFormRecord = {
    id: string;
    full_name: string;
    phone: string;
    addresses: CustomerAddressLineFormData[];
};

export type CustomerFormData = {
    full_name: string;
    phone: string;
    addresses: CustomerAddressLineFormData[];
};

export const MAX_CUSTOMER_ADDRESSES = 20;

export type CustomersFormPageProps = {
    customer: CustomerFormRecord | null;
};
