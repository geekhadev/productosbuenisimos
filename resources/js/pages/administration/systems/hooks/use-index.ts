/**
 * Hook mínimo del índice de sistemas: acciones fuera de `TabledataProvider` + Inertia list.
 */
import { router } from '@inertiajs/react';
import { useCallback } from 'react';
import { TABLEDATA_LIST_INERTIA_ONLY } from '@/components/custom/tabledata';
import { destroy } from '@/routes/administration/systems';
import type { System } from '../types';

export type { SystemsIndexPageProps } from '@/pages/administration/systems/config';

export function useSystemsIndex() {
    const deleteRow = useCallback((row: System) => {
        if (!window.confirm(`¿Eliminar el sistema «${row.name}»?`)) {
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
