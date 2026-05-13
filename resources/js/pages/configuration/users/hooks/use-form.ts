import { useMemo } from 'react';
import { TABLEDATA_LIST_INERTIA_ONLY } from '@/components/custom/tabledata';
import type { UserListRow } from '@/pages/configuration/users/types';
import { store, update } from '@/routes/configuration/users';

export function useUserForm(editingUser: UserListRow | null) {
    const isEdit = editingUser !== null;

    const formProps = useMemo(() => {
        if (editingUser) {
            return update.form.patch({ user: editingUser.id });
        }

        return store.form();
    }, [editingUser]);

    const formOptions = useMemo(
        () => ({
            only: [...TABLEDATA_LIST_INERTIA_ONLY],
        }),
        [],
    );

    const formKey = editingUser ? `edit-${editingUser.id}` : 'create';

    const title = isEdit ? 'Editar usuario' : 'Nuevo usuario';
    const description = isEdit
        ? 'Modifica el nombre y el correo. El tipo de cuenta y la contraseña no se cambian desde aquí.'
        : 'Crea una cuenta de tipo usuario (operativo): nombre, correo y contraseña inicial. No se pueden crear titulares Owner ni cuentas Root desde aquí.';

    const defaults =
        isEdit && editingUser
            ? { name: editingUser.name, email: editingUser.email }
            : { name: '', email: '' };

    return {
        formProps,
        formOptions,
        formKey,
        title,
        description,
        isEdit,
        defaults,
    };
}
