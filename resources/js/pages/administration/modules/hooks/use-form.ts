import { useMemo } from 'react';
import ModulesController from '@/actions/App/Http/Controllers/Administration/ModulesController';
import type { Module } from '../types';

export function useModuleForm(module: Module | null) {
    const isEdit = module !== null;

    const formProps = useMemo(() => {
        if (isEdit && module) {
            return ModulesController.update.form({ module: module.id });
        }

        return ModulesController.store.form();
    }, [isEdit, module]);

    const headTitle = isEdit ? 'Editar módulo' : 'Nuevo módulo';

    return {
        isEdit,
        formProps,
        headTitle,
    };
}
