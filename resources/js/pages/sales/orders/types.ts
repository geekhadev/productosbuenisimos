import type { TabledataListStandardDraft } from '@/components/custom/tabledata';
import type { PaginatedListFilters } from '@/types/list-filters';

export type OrderRow = {
    id: string;
    name: string;
    customer_name: string;
    address_summary: string;
    total_amount: string;
    items_count: number;
    created_at: string | null;
    can: {
        update: boolean;
        delete: boolean;
    };
};

export const ORDERS_INDEX_MODULE_FILTER_KEYS = ['date_from', 'date_to'] as const;

export type OrdersIndexModuleFilterKey =
    (typeof ORDERS_INDEX_MODULE_FILTER_KEYS)[number];

export type OrdersIndexModuleFilters = {
    [K in OrdersIndexModuleFilterKey]: string;
};

export type OrderListFilters = PaginatedListFilters & {
    [K in OrdersIndexModuleFilterKey]?: string | null;
};

export type OrdersIndexFiltersDraftFull = OrdersIndexModuleFilters &
    TabledataListStandardDraft;

export type OrderItemRow = {
    id: string;
    product_id: string;
    product_name: string;
    product_code: string;
    product_sku: string;
    quantity: number;
    unit_price: string;
    line_total: string;
};

export type OrderFormRecord = {
    id: string;
    name: string;
    customer_id: string;
    address_id: string;
    total_amount: string;
    items: OrderItemRow[];
};

export type OrderCustomerOption = {
    id: string;
    full_name: string;
    phone: string;
    addresses: {
        id: string;
        label: string;
    }[];
};

export type OrderItemFormLine = {
    product_id: string;
    quantity: string;
    unit_price: string;
    product_name: string;
    product_code: string;
    product_sku: string;
};

export type OrderFormData = {
    name: string;
    customer_id: string;
    address_id: string;
    items: OrderItemFormLine[];
};

export type OrdersFormPageProps = {
    order: OrderFormRecord;
    customers: OrderCustomerOption[];
};
