import { router } from '@inertiajs/react';
import { useCallback } from 'react';
import { TABLEDATA_LIST_INERTIA_ONLY } from '@/components/custom/tabledata';
import { destroy } from '@/routes/shared/states';
import type { State } from '../types';

export type { StatesIndexPageProps } from '@/pages/shared/states/config';

export function useStatesIndex() {
    const deleteRow = useCallback((row: State) => {
        if (!window.confirm(`¿Eliminar el estado «${row.name}»?`)) {
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
