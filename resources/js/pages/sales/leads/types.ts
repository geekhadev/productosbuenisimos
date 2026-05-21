import type { TabledataListStandardDraft } from '@/components/custom/tabledata';
import type { PaginatedListFilters } from '@/types/list-filters';

export type LeadRow = {
    id: string;
    phone: string;
    source: string;
    status: string;
    customer_name: string | null;
    created_at: string | null;
    can: {
        update: boolean;
        delete: boolean;
    };
};

export const LEADS_INDEX_MODULE_FILTER_KEYS = ['status', 'source'] as const;

export type LeadsIndexModuleFilterKey =
    (typeof LEADS_INDEX_MODULE_FILTER_KEYS)[number];

export type LeadsIndexModuleFilters = {
    [K in LeadsIndexModuleFilterKey]: string;
};

export type LeadListFilters = PaginatedListFilters & {
    [K in LeadsIndexModuleFilterKey]?: string | null;
};

export type LeadsIndexFiltersDraftFull = LeadsIndexModuleFilters &
    TabledataListStandardDraft;

export type LeadFormRecord = {
    id: string;
    phone: string;
    source: string;
    status: string;
    customer_id: string | null;
};

export type LeadFormData = {
    phone: string;
    source: string;
    status: string;
    customer_id: string;
};

export type LeadCustomerOption = {
    id: string;
    label: string;
};

export type LeadsFormPageProps = {
    lead: LeadFormRecord | null;
    customers: LeadCustomerOption[];
};
