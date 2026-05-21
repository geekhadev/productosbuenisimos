import { router } from '@inertiajs/react';
import { useCallback } from 'react';
import { TABLEDATA_LIST_INERTIA_ONLY } from '@/components/custom/tabledata';
import type { LeadRow } from '@/pages/sales/leads/types';
import { destroy } from '@/routes/sales/leads';

export function useLeadsIndex() {
    const deleteRow = useCallback((row: LeadRow) => {
        if (!row.can.delete) {
            return;
        }

        if (
            !window.confirm(
                `¿Dar de baja al lead con teléfono «${row.phone}»?`,
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
