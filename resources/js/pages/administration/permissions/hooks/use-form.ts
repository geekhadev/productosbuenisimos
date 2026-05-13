import { useMemo } from 'react';
import PermissionsController from '@/actions/App/Http/Controllers/Administration/PermissionsController';
import type { Permission } from '../types';

export function usePermissionForm(permission: Permission | null) {
    const isEdit = permission !== null;

    const formProps = useMemo(() => {
        if (isEdit && permission) {
            return PermissionsController.update.form({
                permission: permission.id,
            });
        }

        return PermissionsController.store.form();
    }, [isEdit, permission]);

    const headTitle = isEdit ? 'Editar permiso' : 'Nuevo permiso';

    return {
        isEdit,
        formProps,
        headTitle,
    };
}
