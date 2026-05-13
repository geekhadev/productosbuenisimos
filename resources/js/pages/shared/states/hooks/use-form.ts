import { useMemo } from 'react';
import StatesController from '@/actions/App/Http/Controllers/Shared/StatesController';
import type { State } from '../types';

export function useStateForm(entity: State | null) {
    const isEdit = entity !== null;
    const headTitle = isEdit ? 'Editar estado' : 'Nuevo estado';
    const description = isEdit
        ? 'Modifica los datos del estado.'
        : 'Completa los datos para crear un nuevo estado.';

    const formProps = useMemo(() => {
        if (isEdit && entity) {
            return StatesController.update.form({ state: entity.id });
        }

        return StatesController.store.form();
    }, [isEdit, entity]);

    return {
        isEdit,
        headTitle,
        description,
        formProps,
    };
}
