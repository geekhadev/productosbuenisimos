import { router } from '@inertiajs/react';
import { useCallback } from 'react';
import { TABLEDATA_LIST_INERTIA_ONLY } from '@/components/custom/tabledata';
import type { ProductRow } from '@/pages/stock/products/types';
import { deactivate, destroy } from '@/routes/stock/products';

export function useProductsIndex() {
    const deleteRow = useCallback((row: ProductRow) => {
        if (!row.can.delete) {
            return;
        }

        if (
            !window.confirm(
                `¿Eliminar el producto «${row.name}»? Solo los inactivos se pueden eliminar.`,
            )
        ) {
            return;
        }

        router.delete(destroy.url(row.id), {
            preserveScroll: true,
            only: [...TABLEDATA_LIST_INERTIA_ONLY, 'can'],
        });
    }, []);

    const deactivateRow = useCallback((row: ProductRow) => {
        if (!row.can.deactivate) {
            return;
        }

        if (!window.confirm(`¿Desactivar el producto «${row.name}»?`)) {
            return;
        }

        router.patch(
            deactivate.url(row.id),
            {},
            {
                preserveScroll: true,
                only: [...TABLEDATA_LIST_INERTIA_ONLY, 'can'],
            },
        );
    }, []);

    return {
        deleteRow,
        deactivateRow,
    };
}
