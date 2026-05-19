import { router } from '@inertiajs/react';
import { useCallback } from 'react';
import { TABLEDATA_LIST_INERTIA_ONLY } from '@/components/custom/tabledata';
import type { OrderRow } from '@/pages/sales/orders/types';
import { destroy } from '@/routes/sales/orders';

export function useOrdersIndex() {
    const deleteRow = useCallback((row: OrderRow) => {
        if (!row.can.delete) {
            return;
        }

        if (
            !window.confirm(
                `¿Eliminar definitivamente el pedido «${row.name}»? Esta acción no se puede deshacer.`,
            )
        ) {
            return;
        }

        router.delete(destroy.url(row.id), {
            preserveScroll: true,
            only: [...TABLEDATA_LIST_INERTIA_ONLY],
        });
    }, []);

    return { deleteRow };
}
