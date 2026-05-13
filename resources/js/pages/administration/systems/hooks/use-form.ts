import { useMemo } from 'react';
import SystemsController from '@/actions/App/Http/Controllers/Administration/SystemsController';
import type { System } from '../types';

export function useSystemForm(system: System | null) {
    const isEdit = system !== null;

    const formProps = useMemo(() => {
        if (isEdit && system) {
            return SystemsController.update.form({ system: system.id });
        }

        return SystemsController.store.form();
    }, [isEdit, system]);

    const headTitle = isEdit ? 'Editar sistema' : 'Nuevo sistema';

    return {
        isEdit,
        formProps,
        headTitle,
    };
}
