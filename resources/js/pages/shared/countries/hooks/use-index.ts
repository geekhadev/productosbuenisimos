/**
 * Hook mínimo del índice de países: acciones fuera de `TabledataProvider` + Inertia list.
 */
import { router } from '@inertiajs/react';
import { useCallback } from 'react';
import { TABLEDATA_LIST_INERTIA_ONLY } from '@/components/custom/tabledata';
import { destroy } from '@/routes/shared/countries';
import type { Country } from '../types';

export type { CountriesIndexPageProps } from '@/pages/shared/countries/config';

export function useCountriesIndex() {
    const deleteRow = useCallback((row: Country) => {
        if (!window.confirm(`¿Eliminar el país «${row.name}»?`)) {
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
