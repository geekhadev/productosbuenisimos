import type { TabledataListStandardDraft } from '@/components/custom/tabledata';
import type { PaginatedListFilters } from '@/types/list-filters';

export type ProductRow = {
    id: string;
    name: string;
    code: string;
    sku: string;
    width: string;
    length: string;
    height: string;
    volume: string;
    weight: string;
    minimum_stock: number;
    price: string;
    is_active: boolean;
    created_at: string | null;
    updated_at: string | null;
    can: {
        update: boolean;
        delete: boolean;
        deactivate: boolean;
    };
};

export const PRODUCTS_INDEX_MODULE_FILTER_KEYS = ['status'] as const;

export type ProductsIndexModuleFilterKey =
    (typeof PRODUCTS_INDEX_MODULE_FILTER_KEYS)[number];

export type ProductsIndexModuleFilters = {
    [K in ProductsIndexModuleFilterKey]: string;
};

export type ProductListFilters = PaginatedListFilters & {
    [K in ProductsIndexModuleFilterKey]?: string | null;
};

export type ProductsIndexFiltersDraftFull = ProductsIndexModuleFilters &
    TabledataListStandardDraft;

export type ProductFormRecord = {
    id: string;
    name: string;
    code: string;
    sku: string;
    width: string;
    length: string;
    height: string;
    volume: string;
    weight: string;
    minimum_stock: number;
    price: string;
    description: string | null;
    is_active: boolean;
};

export type ProductFormData = {
    name: string;
    code: string;
    sku: string;
    width: string;
    length: string;
    height: string;
    volume: string;
    weight: string;
    minimum_stock: string;
    price: string;
    description: string;
    is_active: boolean;
};

export type ProductsFormPageProps = {
    product: ProductFormRecord | null;
};
