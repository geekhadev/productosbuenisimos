/**
 * Hook mínimo del índice de permisos: borrado parcial Inertia.
 */
import { router } from '@inertiajs/react';
import { useCallback } from 'react';
import { TABLEDATA_LIST_INERTIA_ONLY } from '@/components/custom/tabledata';
import { destroy } from '@/routes/administration/permissions';
import type { Permission } from '../types';

export type { PermissionsIndexPageProps } from '@/pages/administration/permissions/config';

export function usePermissionsIndex() {
    const deleteRow = useCallback((row: Permission) => {
        if (!window.confirm(`¿Eliminar el permiso «${row.name}»?`)) {
            return;
        }

        router.delete(destroy.url(row.id), {
            preserveScroll: true,
            only: [...TABLEDATA_LIST_INERTIA_ONLY],
        });
    }, []);

    return {
        deleteRow,
    };
}
