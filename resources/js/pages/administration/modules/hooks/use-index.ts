/**
 * Hook mínimo del índice de módulos: borrado parcial Inertia.
 */
import { router } from '@inertiajs/react';
import { useCallback } from 'react';
import { TABLEDATA_LIST_INERTIA_ONLY } from '@/components/custom/tabledata';
import { destroy } from '@/routes/administration/modules';
import type { Module } from '../types';

export type { ModulesIndexPageProps } from '@/pages/administration/modules/config';

export function useModulesIndex() {
    const deleteRow = useCallback((row: Module) => {
        if (!window.confirm(`¿Eliminar el módulo «${row.name}»?`)) {
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
