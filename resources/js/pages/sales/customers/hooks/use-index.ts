import { router } from '@inertiajs/react';
import { useCallback } from 'react';
import { TABLEDATA_LIST_INERTIA_ONLY } from '@/components/custom/tabledata';
import type { CustomerRow } from '@/pages/sales/customers/types';
import { destroy } from '@/routes/sales/customers';

export function useCustomersIndex() {
    const deleteRow = useCallback((row: CustomerRow) => {
        if (!row.can.delete) {
            return;
        }

        if (
            !window.confirm(
                `¿Dar de baja al cliente «${row.full_name}»? Podrá reutilizarse el teléfono con un cliente nuevo.`,
            )
        ) {
            return;
        }

        router.delete(destroy.url(row.id), {
            preserveScroll: true,
            only: [...TABLEDATA_LIST_INERTIA_ONLY, 'can'],
        });
    }, []);

    return { deleteRow };
}
