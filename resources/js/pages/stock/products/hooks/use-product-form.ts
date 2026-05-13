import { setLayoutProps, useForm } from '@inertiajs/react';
import { useEffect, useLayoutEffect, useMemo, useCallback } from 'react';
import type {
    ProductFormData,
    ProductFormRecord,
    ProductsFormPageProps,
} from '@/pages/stock/products/types';
import { dashboard } from '@/routes';
import {
    create as productsCreate,
    edit,
    index as productsIndex,
    store,
    update,
} from '@/routes/stock/products';

function buildFormDefaults(product: ProductFormRecord | null): ProductFormData {
    return {
        name: product?.name ?? '',
        code: product?.code ?? '',
        sku: product?.sku ?? '',
        width: product?.width ?? '0',
        length: product?.length ?? '0',
        height: product?.height ?? '0',
        volume: product?.volume ?? '0',
        weight: product?.weight ?? '0',
        minimum_stock: String(product?.minimum_stock ?? 0),
        price: product?.price ?? '0',
        description: product?.description ?? '',
        is_active: product?.is_active ?? true,
    };
}

export function useProductForm({ product }: ProductsFormPageProps) {
    const isEdit = product != null;
    const productId = product?.id;
    const productName = product?.name;

    const formDefaults = useMemo(() => buildFormDefaults(product), [product]);

    /** Content-based key so the sync effect does not depend on Inertia object identity or `reset`'s changing callback identity. */
    const formDefaultsSyncKey = useMemo(
        () => JSON.stringify(formDefaults),
        [formDefaults],
    );

    const form = useForm<ProductFormData>(formDefaults);
    const { setDefaults, reset, patch, post } = form;

    useEffect(() => {
        const defaults = JSON.parse(formDefaultsSyncKey) as ProductFormData;
        setDefaults(defaults);
        reset();
        // Only `formDefaultsSyncKey`: including Inertia `reset` would retrigger after each run because
        // its identity is tied to internal `defaults` state (`useFormState` in @inertiajs/react).
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [formDefaultsSyncKey]);

    const breadcrumbs = useMemo(
        () => [
            { title: 'Panel', href: dashboard() },
            { title: 'Productos', href: productsIndex() },
            {
                title: isEdit && productName != null ? productName : 'Nuevo',
                href:
                    isEdit && productId != null
                        ? edit.url(productId)
                        : productsCreate(),
            },
        ],
        [isEdit, productId, productName],
    );

    useLayoutEffect(() => {
        setLayoutProps({ breadcrumbs });
    }, [breadcrumbs]);

    const headTitle = isEdit ? 'Editar producto' : 'Nuevo producto';

    const submit = useCallback(
        (e: React.FormEvent) => {
            e.preventDefault();

            if (isEdit && productId != null) {
                patch(update.url(productId), { preserveScroll: true });

                return;
            }

            post(store.url(), { preserveScroll: true });
        },
        [isEdit, patch, post, productId],
    );

    return {
        form,
        submit,
        headTitle,
        isEdit,
    };
}
